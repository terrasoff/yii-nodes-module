<?php

/**
 * @author Тарасов Константин
 * @desc управление страницами
 */
class NodeContent extends CActiveRecord
{
    const ITEMS_PER_QUERY = 5;
    const DEFAULT_THUMB = '/images/pages/default.png';
    const DEFAULT_CATEGOTY = 1;
    const CACHE_ID = 'page';
    const REG_CATEGORY = '[\pL|\pZs]+';
    const REG_TAG = '[\pL|\pZs]+';

    // категория
    public $category_id = null;
    // данные для сохранения страницы
    public $data = null;
    // выбранный язык для перевода
    public $language = null;


    public $table = '{{NodeContent}}';
    public static function model($className=__CLASS__) {return parent::model($className);}
    public function tableName() { return $this->table;}

    public function __construct($language = null)
    {
        $this->language = $language;
    }

    public function getAttributeName($attributeName) {
        return get_class($this).'['.$this->language.']'.'['.$attributeName.']';
    }

    public function getTitle() {
        return $this->name;
    }

    /**
     * @desc получить id категории по ее названию
     * @param $name название категории
     * @return int id категории
     */
    public function getCategoryByName($name) {
        $category = $this->findByAttributes(array(
            'type' => PagesModule::$types['category'],
            'name' => $name,
        ));
        return $category ? $category : false;
    }

    public static function resetCache() {
        // кешируем
        $cache = Yii::app()->cache;
        $cache_id = THelper::getCacheId(array(self::CACHE_ID,false,null),true); $cache->delete($cache_id);
        $cache_id = THelper::getCacheId(array(self::CACHE_ID,true,null),true); $cache->delete($cache_id);
    }

    /**
     * @desc распечатываем категории
     * @param bool $showpages печатаем вместе с файлами
     * @param int $depth до определенного уровня
     * @return string|array в зависимости от выбранного режима $mode)
     */
    public static function toString($showpages = false,$depth = null,$mode = 'list') {
        // кешируем
        $cache = Yii::app()->cache;
        $cache_id = THelper::getCacheId(array(self::CACHE_ID,$showpages,$depth,$mode),true);
        $html = $cache->get($cache_id); $html = null;
        // если в кеше нет, то формируем
        if (!$html) {
            $root = Page::model()->findByPk(1);
            $nodes = $root->descendants($depth)->findAll();
            if ($mode == 'list') {
                $html = '';
                foreach ($nodes as $node) {
                    if (!$showpages && $node->type == 2) continue; // страницы не показываем
                    $link = $node->type == 1 ?
                        '/pages/'.$node->name:
                        '/page/'.$node->id;
                    $html.= '<li class="level'.$node->nlevel.'"><a href="'.$link.'">'.PagesModule::t($node->name).'</a></li>';
                }
                return $html;
            }
            else if ($mode == 'selectlist') {
                $data = array();
                foreach ($nodes as $node) {
                    if (!$showpages && $node->type == 2) continue; // страницы не показываем
                    $data[$node->id] = PagesModule::t($node->name);
                }
                return $data;
            }
            $cache->set($cache_id,$html);
        }
    }

    public function isCurrent(){
        return $this->language === Yii::app()->language;
    }

    public function getByAlias($alias, $language = null)
    {
        return $this
            ->byPablished()
            ->findByAttributes([
                'alias'=>$alias,
                'language'=>$language ? $language : Yii::app()->language,
            ]);
    }

    public function getByName($name, $language = null)
    {
        return $this
            ->byPablished()
            ->findByAttributes([
                'name'=>$name,
                'language'=>$language ? $language : Yii::app()->language
        ]);
    }

    public function byPablished()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('t.isPublished = 1');
        $this->getDbCriteria()->mergeWith($criteria);

        return $this;
    }

    public function rules()
    {
        return array(
            array('language','in','range'=>Yii::app()->params['languages']),
            array('name, alias','length','min' => 3),
            array('name, alias, cut, description, keywords, seo', 'length', 'max' => 255),
            array('body, isPublished', 'safe'),

        );
    }

}