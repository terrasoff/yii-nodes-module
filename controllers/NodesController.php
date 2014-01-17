<?php

/**
 * @desc управляем страницами
 * @author Тарасов Константин <kosta@onego.ru>
 */
class NodesController extends CController
{

    public $defaltAction = 'view';

    public $categoryTree = null;

    public function beforeAction($event) {
        $module = Yii::app()->getModule('nodes');
        $this->layout = $module->layout;

        $cache = Yii::app()->cache;
        $cacheKey = 'Category';
        $data = $cache->get($cacheKey);

        if (!$data) {
            $data = Category::model()->getTree();
            $cache->set($cacheKey,$data,$cache->time,
                $cache->getDependency('MAX(idCategory)','Category')
            );

        }
        $this->categoryTree = $data;

        Yii::app()->clientScript->registerCssFile(Yii::app()->assetManager->publish(
            Yii::getPathOfAlias('application.modules.nodes.assets').'/editor.css'
        ));

        return parent::beforeAction($event);
    }

    public function actionEdit() {
        $id = Yii::app()->request->getParam('id',null);
        $node = Node::model()->resetScope()->findByPk($id);

        $Node = Yii::app()->request->getParam('Node',null);
        $NodeContent = Yii::app()->request->getParam('NodeContent',null);

        $errors = null;

        if ($Node && $NodeContent) {
            // сохраняем каркас
            if (!$node)
                $node = new Node();

            /** @var $transaction CDbTransaction */
            $transaction = Yii::app()->db->beginTransaction();
            if ($node->save($Node,$NodeContent)) {
                $transaction->commit();
                // если добавляли, то после сохранения переходим на страницу редактирования узла
                // (чтобы больше не добавлять)
                if (!$id)
                    $this->redirect(Yii::app()->createUrl(NodesModule::ACTION_EDIT,array('id'=>$node->idNode)));
            } else {
                $transaction->rollback();
                $errors = array(
                    $node->getErrors(),
                    $node->content->getErrors(),
                );
            }
        } else {
            if (!$node)
                $node = new Node();
        }

        $node->getRelated('parent');
        $node->getRelated('content');

        $criteria = new CDbCriteria();
        // добавляем только в контейнеры и не добавляем в самого себя
        if (!$node->isNewRecord && $node->isContainer()) {
            $criteria->addCondition('t.idNode<>'.$node->idNode);
            // родительский контейнер нельзя добавлять в дочерний
            $list = $node->flattern();
            $criteria->addNotInCondition('t.idNode',$list);
        }
        $criteria->addCondition('t.type = '.NodesModule::TYPE_CONTAINER);

        $nodes = Node::model()->findAll($criteria);
        // формируем список контейнеров для выпадающего списка
        $list = array('null'=>'');
        foreach($nodes as $n) $list[$n->idNode] = $n->getName();

        // стили и скрипты
        $dir = Yii::getPathOfAlias('application.modules.nodes.assets');
        Yii::app()->assetManager->publish($dir, true);
        $url = Yii::app()->assetManager->getPublishedUrl($dir);

        Yii::app()->clientScript->registerCssFile($url.'/editor.css');
        Yii::app()->clientScript->registerScriptFile($url.'/init.js',CClientScript::POS_END);
        Yii::app()->clientScript->registerScriptFile($url.'/EditorForm.js');

        $this->render('/form', array(
            'nodes'=>$list,
            'model'=>$node,
            'errors'=>$errors,
        ));
    }

    public function actionDelete() {
        $id = Yii::app()->request->getParam('id',null);
        $node = Node::model()->findByPk($id);

        if ($node) {
            if ($node->isContent()) {
                $node->delete();
            } else
            if ($node->isContainer()) {
                /** @var $transaction CDbTransaction */
                $transaction = Yii::app()->db->beginTransaction();
                $list = $node->flattern();

                $criteria = new CDbCriteria();
                $criteria->addInCondition('t.idNode',$list);
                $children = Node::model()->findAll($criteria);

                foreach ($children as $child) {
                    if (!$child->deleteContent() || !$child->delete()) {
                        $transaction->rollback();
                        break;
                    }
                }

                if ($transaction->getActive()) { // если все дочерние элементы были удалены
                    if ($node->delete()) // то удаляем и сам узел
                        $transaction->commit();
                    else
                        $transaction->rollback();
                }
            }
        }

        $this->redirect(Yii::app()->createUrl(NodesModule::ACTION_PAGES));
    }

    public function actiontest(){
    }

    // статьи
    public function actionAdmin()
    {

        // родитель
        $parent = null;
        // смотрим, может определена категория?
        $container = Yii::app()->request->getParam('container',null);
        $content = Yii::app()->request->getParam('content',null);

        // пробуем найти категорию по имени
        if ($container) {
            if ($content) {
                $node = Node::model()->getByName($content);
                $parent = $node->getRelated('parent');

                $this->render('/node_inner',array(
                    'index'=>Node::model()->getIndex(),
                    'model'=>$node,
                    'parent'=>$node->parent,
                    'backlink'=>Yii::app()->createUrl(NodesModule::ACTION_PAGES),
                ));
                return;
            } else
                $parent = Node::model()->getByName($container);
        }

        // ищем статьи из категории
        $data = Node::model()->getNodes($parent);

        // скрипты
        $cs = Yii::app()->clientScript;
        $ap = Yii::getPathOfAlias('pages.assets');
        // формируем данные для страницы
        $module = Yii::app()->getModule('nodes');
        $data = array(
            'parent' => $parent,
            'total' => $data['total'],
            'items' => $data['items'],
            'backlink' => NodesModule::ACTION_ADMIN,
            'index'=>$module->getIndex(),
        );

        $this->render('/nodes/admin', $data);
    }

    // статьи
    public function actionTags()
    {
        // смотрим, может определена категория?
        $tag = Yii::app()->request->getParam('tag',null);
        $criteria = new CDbCriteria();
        $criteria->compare('tags',$tag);
        $items = Pages::model()->findAll($criteria);
        if ($items) {
            $data = array(
                'backlink' => PagesModule::ACTION_PAGES,
                'items' => $items,
            );
            $this->render('/pages', $data);
        } else $this->redirect(PagesModule::ACTION_PAGES);
    }

    public function actionUpload()
    {
        $module = Yii::app()->getModule('nodes');
        $url = $module->image_path;
        $dir = Yii::getPathOfAlias('webroot').$url;

        if (!file_exists($dir))
            mkdir($dir);

        $_FILES['file']['type'] = strtolower($_FILES['file']['type']);

        if ($_FILES['file']['type'] == 'image/png'
            || $_FILES['file']['type'] == 'image/jpg'
            || $_FILES['file']['type'] == 'image/gif'
            || $_FILES['file']['type'] == 'image/jpeg'
            || $_FILES['file']['type'] == 'image/pjpeg')
        {
            // setting file's mysterious name
            $name = md5(date('YmdHis')).'.jpg';
            $file = $dir.$name;

            // copying
            move_uploaded_file($_FILES['file']['tmp_name'], $file);

            // displaying file
            $array = array(
                'filelink' => $url.$name
            );

            echo stripslashes(json_encode($array));
        }
    }

    public function filters()
    {
        return array(
            'accessControl',
//            array(
//                'COutputCache + page',
//                'duration' => 5000,
//                'varyByParam' => array('id',Yii::app()->language),
//            ),
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'roles' => array('admin','moderator'),
                'actions' => array(
                    'admin',
                    'add',
                    'edit',
                    'upload',
                    'delete',
                ),
            ),
            array('allow',
                'roles' => array('*'),
                'actions' => array(
                    'node',
                    'category',
                    'tags',
                ),
            ),
        );
    }

}
