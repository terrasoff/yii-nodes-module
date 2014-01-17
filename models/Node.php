<?php

/**
 * This is the model class for table "category".
 *
 * The followings are the available columns in table 'category':
 * @property NodeContent $content
 *
 * @property integer $id
 * @property NodeContent $currentContent
 * @property string $name
 */
class Node extends CActiveRecord
{

    const ITEMS_PER_QUERY = 5;
    const DEFAULT_THUMB = '/images/pages/default.png';
    const CUTTER = "<cut/>";
    const MAX_CUTTER_LENGTH = 1900;
    const COMMERCIAL_TOTAL = 3;
    const COMMERCIAL_CATEGORY_ID = 18;

    const TYPE_CONTAINER = 1; // категории
    const TYPE_CONTENT = 2; // страницы

    public $currentContent = null;

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'content'=>array(self::HAS_MANY,'NodeContent','idNode','together'=>true),
            'parent'=>array(self::BELONGS_TO,'Node','parent_id'),
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('type','in','range'=>NodesModule::$types),
            array('parent_id','safe'),
        );
    }

    public function init(){
        // добавляем контент на всех языках для новой записи
        if ($this->isNewRecord) {
            $items = array();
            foreach (Yii::app()->params['languages'] as $language) {
                $items[] = new NodeContent($language);
            }
            $this->content = $items;
        }

        parent::init();
    }

    public function save($attritutes = array(), $contents = array(), $runValidation = true)
    {
        $this->attributes = $attritutes;
        if (!parent::save($runValidation)) {
            return false;
        }
        else {
            // сохраняем так же и все NodeContent
            foreach ($contents as $lang=>$content) {
                $item = null;
                if (!empty($content['idNodeContent']))
                    $item = NodeContent::model()->findByPk((int)$content['idNodeContent']);

                if (!$item) {
                    $item = new NodeContent($lang);
                    $item->idNode = $this->idNode; // не забываем про ссылку на родителя
                } else {
                    $item->setIsNewRecord(false);
                }

                $item->isPublished = false;
                $item->attributes = $content;

                if (!$item->save()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function beforeFind() {
        $dependency = new CDbCacheDependency('SELECT AVG(updated) FROM `'.$this->tableName().'`');
        $dependency->reuseDependentData = true;
        $this->cache(1000, $dependency);
        parent::beforeFind();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CategoryDB the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'Node';
	}

    public function defaultScope(){
        return array(
            'with'=>array(
                'content'=>array(
                    'condition'=>'content.language=:language AND isVisibled=1',
                    'params'=>array(
                        'language'=>Yii::app()->language,
                    ),
                ),
            ),
        );
    }

    public function type($type){
        $this->getDbCriteria()->mergeWith(array(
            'condition'=>'type=:type',
            'params'=>array(':type'=>$type),
        ));
        return $this;
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
		);
	}

    //in model
    public function behaviors()
    {
        return array(
            'TreeBehavior' => array(
                'class' => 'ext.TreeBehavior.TreeBehavior',
                'idAttribute'=>'idNode',
                'criteria'=>array(
                    'condition'=>'type=:type',
                    'params'=>array(':type'=>self::TYPE_CONTAINER),
                ),
            ),
            'CTimestampBehavior' => array(
                'class'=>'zii.behaviors.CTimestampBehavior',
                'createAttribute'=>'created',
                'updateAttribute'=>'updated',
                'setUpdateOnCreate'=>true,
            ),
        );
    }

    /**
     * Ищем узел по имени
     * @param string $name
     * @return Node
     */
    public function getByName($name){
        $criteria = new CDbCriteria();
        $criteria->addCondition('content.name=:name');
        $criteria->params[':name'] = $name;

        return $this->find($criteria);
    }

    /**
     * @desc листаем узлы
     * @param Node $parent
     * @param array $params
     * @return string html
     */
    public function getNodes($parent = null) {
        // параметры
        $criteria = $this->getDbCriteria();
        if ($parent) {
            $criteria->addCondition('parent_id = :parent_id');
            $criteria->params[':parent_id'] = $parent->idNode;
        } else {
            $criteria->addCondition('parent_id = 0');
        }
        $total = $this->type(self::TYPE_CONTENT)->count($criteria);
        $items = $this->type(self::TYPE_CONTENT)->findAll($criteria);

        return array(
            'total'=>$total,
            'items'=>$items,
        );
    }

    public function deleteContent() {
        $criteria = new CDbCriteria();
        $criteria->addCondition('type=:type');
        $criteria->addCondition('parent_id=:parent_id');
        $criteria->params = array(
            'type'=>self::TYPE_CONTENT,
            'parent_id'=>$this->getId(),
        );
        
        $items = $this->findAll($criteria);
        foreach ($items as $item)
            if (!$item->delete())
                return false;

        return true;
    }

    public function getId(){
        return $this->idNode;
    }

    public function getIndex() {
        return $this->getTree();
    }

    public function isContent(){
        return (int)$this->type === self::TYPE_CONTENT;
    }

    public function isContainer(){
        return (int)$this->type === self::TYPE_CONTAINER;
    }

    public function getDate() {
        return $this->updated;
    }

    public function getUrl($parent = null) {

        if ($parent && $this->isContent()) {
            $params = array(
                'container'=>$parent->getName(),
                'content'=>$this->getName(),
            );
        }
        else {
            $params = array(
                'container'=>$this->getName(),
            );
        }

        return Yii::app()->createUrl('/nodes/nodes/view',$params);
    }

    public function getSeo() {
        return $this->getContent()->seo;
    }

    public function getText() {
        return $this->getContent()->text;
    }

    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->getContent()->name;
    }

    public function getCut() {
        return preg_replace('/\s+\r\n/','',$this->getContent()->cut);
    }

    // картинка для статьи
    public function getThumb() {
        $obj = $this;
        if ($obj->pic)
            if (file_exists(THelper::getPath($obj->pic,false,true))) return $obj->pic;
        return self::DEFAULT_THUMB;
    }

    /**
     * ищем контент, соотв.выбранному языку
     * @param string $language
     * @return NodeContent
     * @throws CException
     */
    public function getContent($language = null)
    {
        if (!$this->currentContent) {
            if (!$language) $language = Yii::app()->language;

            $found = false;
            foreach ($this->content as $i=>$c) {
                if ($c->language === $language) {
                    $this->currentContent = $this->content[$i];
                    $found = true;
                }
            }
            if (!$found)
                throw new CException('Контент не определен!',500);
        }
        return $this->currentContent;
    }
    
    public function toArray($children = null){
        return array(
            'idNode'=>$this->idNode,
            'children'=>$children,
            'content'=>$this->getContent()->attributes,

            'text'=>$this->getName(),
            'url'=>$this->getUrl(),
        );
    }
}