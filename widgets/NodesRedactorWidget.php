<?php
/**
 * ImperaviRedactorWidget class file.
 */
class NodesRedactorWidget extends ImperaviRedactorWidget
{
    /**
     * Assets package ID.
     */
    const PACKAGE_ID = 'imperavi-redactor';

    /**
     * @var array {@link http://imperavi.com/redactor/docs/ redactor options}.
     */
    public $options = array();

    /**
     * @var string|null Selector pointing to textarea to initialize redactor for.
     * Defaults to null meaning that textarea does not exist yet and will be
     * rendered by this widget.
     */
    public $selector;

    /**
     * @var array
     */
    public $package = array();

    /**
     * @var array
     */
    public $plugins = array(
        'fullscreen'=>array(
            'package'=>array('js'=>array('fullscreen.js'))
        )
    );

    /**
     * Init widget.
     */
    public function init()
    {
        parent::init();

        if ($this->selector === null) {
            list($this->name, $this->id) = $this->resolveNameID();
            $this->htmlOptions['id'] = $this->getId();
            $this->selector = '#' . $this->getId();

            if ($this->hasModel()) {
                echo CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
            } else {
                echo CHtml::textArea($this->name, $this->value, $this->htmlOptions);
            }
        }

        $this->registerClientScript();
    }

    /**
     * Register CSS and Script.
     */
    protected function registerClientScript()
    {
        // Prepare script package.
        $this->package = array_merge(array(
            'baseUrl' => $this->getAssetsUrl(),
            'js' => array(
                YII_DEBUG ? 'redactor.js' : 'redactor.min.js',
            ),
            'css' => array(
                'redactor.css',
            ),
            'depends' => array(
                'jquery',
            ),
            ), $this->package);

        // Append language file to script package.
        if (isset($this->options['lang']) && $this->options['lang'] !== 'en') {
            $this->package['js'][] = 'lang/' . $this->options['lang'] . '.js';
        }

        // Add assets url to relative css.
        if (isset($this->options['css'])) {
            if (!is_array($this->options['css'])) {
                $this->options['css'] = array($this->options['css']);
            }
            foreach ($this->options['css'] as $i => $css) {
                if (strpos($css, '/') === false) {
                    $this->options['css'][$i] = $this->getAssetsUrl() . '/' . $css;
                }
            }
        }

        $clientScript = Yii::app()->getClientScript();
        $selector = CJavaScript::encode($this->selector);
        $options = CJavaScript::encode($this->options);


        $pluginsPath = $this->getAssetsUrl().DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR;
        foreach ($this->getPlugins() as $id=>$plugin) {
            // Insert plugins in options
            $this->options['plugins'][] = $id;
            // Add packages for plugins
            if (isset($plugin['package'])) {
                $plugin['package']['baseUrl'] = $pluginsPath.$id.DIRECTORY_SEPARATOR;
                $idPackage = self::PACKAGE_ID.'-'.$id;
                $clientScript
                    ->addPackage($idPackage, $plugin['package'])
                    ->registerPackage($idPackage);
            }
        }

        $clientScript
            ->addPackage(self::PACKAGE_ID, $this->package)
            ->registerPackage(self::PACKAGE_ID)
            ->registerScript(
                $this->id,
                'jQuery(' . $selector . ').redactor(' . $options . ');',
                CClientScript::POS_READY
            );
    }

    /**
     * Get the assets path.
     * @return string
     */
    public function getAssetsPath()
    {
        $reflector = new ReflectionClass("ImperaviRedactorWidget");
        $file = $reflector->getFileName();
        return  dirname($file) . '/assets';
    }

    /**
     * Publish assets and return url.
     * @return string
     */
    public function getAssetsUrl()
    {
        return Yii::app()->getAssetManager()->publish($this->getAssetsPath());
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}