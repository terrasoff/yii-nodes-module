<?php

/**
 * @desc модуль управления вложенными элементами
 * @author Тарасов Константин
 * @link https://github.com/terrasoff/yii-nodes-module
 */

class NodesModule extends CWebModule
{
    public $action_view = '/nodes/nodes/view';
    public $image_path = '/img/nodes/';
    public $cacheDuration = 1000;
    public $layout = 'admin';
    public $layout_path = 'application.views.layouts';

    public $defaultController = 'nodes';

    const TYPE_CONTAINER = 1; // like folder
    const TYPE_NODE = 2; // like file (page)

    static $types = array(
        'container'=>self::TYPE_CONTAINER,
        'node'=>self::TYPE_NODE,
    );

    const ACTION_ADD = '/nodes/nodes/add';
    const ACTION_DELETE = '/nodes/nodes/delete';
    const ACTION_EDIT = '/nodes/nodes/edit';
    const ACTION_ADMIN = '/nodes/nodes/admin';
    const ACTION_PAGE = '/page/';
    const ACTION_TAGS = '/pages/tags/';

    /* i18n */
    public static function t($str='', $lang = null, $dic='core', $params = null) {
        if (!$lang) $lang = Yii::app()->language;
        return Yii::t("NodesModule.".$dic, $str, $params, null, $lang);
    }

    public function init() {
        $this->setImport(array(
            'application.modules.nodes.models.*',
            'application.modules.nodes.controllers.*',
        ));
        $this->setLayoutPath(Yii::getPathOfAlias($this->layout_path));
    }

    /**
     * Оглавление
     * @return mixed
     */
    public function getIndex(){
        $model = new Node();
        return $model->getIndex();
    }

}