<?php

/**
 * @author Тарасов Константин
 * @desc управление переводом страниц
 */
class Pages extends CActiveRecord
{
    const ITEMS_PER_QUERY = 5;
    const DEFAULT_THUMB = '/images/pages/default.png';
    const CUTTER = "<cut/>";
    const MAX_CUTTER_LENGTH = 1900;
    const COMMERCIAL_TOTAL = 3;
    const COMMERCIAL_CATEGORY_ID = 18;

    // обновился кат
    public $updated_cut = false;
    public $obj = null;

    public $table = '{{pages_i18n}}';
    public static function model($className=__CLASS__) {return parent::model($className);}
    public function tableName() { return $this->table;}


    /**
     * @desc формируем превью страницы в виде HTML
     * @return string
     */
    public function toItemString() {
        $url = Yii::app()->createAbsoluteUrl(PagesModule::ACTION_PAGE).'/'.$this->id;
        return '';

    }

    // дата в представлении в нужном формате
    function getDate() {
        return Yii::app()->dateFormatter->format('d MMMM y',$this->created);
    }

    // мета в нужном формате
    function getSeo() {
        return $this->seo;
    }

    /**
     * @desc идентификатор страницы в кеше
     * @param int $id
     * @return string
     */
    public static function getPageCacheKey($id = null) {
        $cache_id = 'Yii.COutputCache.yw0..realty/realty/page..a:1:{s:2:"id";s:3:"'.$id.'";}..';
        return $cache_id;
    }

    /**
     * @desc удаляем страницу из кеша
     * @param int $id идентификатор страницы
     * @return bool
     */
    public static function clearPageCache($id) {
        $cache = Yii::app()->getComponent('cache');
        if (!$cache) return false;
        $cache->delete(self::getPageCacheKey($id));
        return true;
    }

    // кат (огрызок) для страницы
    public function setCut() {
        // кат уже определен
        if ($this->cut) return $this->cut;
        // определяем символ ката (по которому будем обрезать)
        $cutter = html_entity_decode(self::CUTTER);
        // позиция обрезалки
        $text = html_entity_decode($this->text);
        // текст слишком короткий, чтобы обрезать
        if (strlen($text) < self::MAX_CUTTER_LENGTH) $cut = strip_tags($text);
        else {
            $pos = strpos($text,$cutter);
            // если найдена обрезалка
            if ($pos && $pos < self::MAX_CUTTER_LENGTH) $cut = strip_tags(substr($text,0,$pos)); // теги не нужны
            // иначе просто обрезаем начало статьи по MAX_CUTTER_LENGTH-символ (без учета тегов)
            else $cut = substr(strip_tags($text),0,self::MAX_CUTTER_LENGTH-4).'...'; // минус нулевой символ и троеточие в конце
            // убираем лишние пробельные символы
            $cut = trim(preg_replace('/\p{Zs}+/',' ',$cut),' ');
        }
        $this->cut = $cut;
        // обновился огрызок - запоминаем
        $this->updated_cut = true;
    }

    // картинка для статьи
    public function getThumb() {
        if ($this->pic)
            if (file_exists(THelper::getPath($this->pic,false,true))) return $this->pic;
        return self::DEFAULT_THUMB;
    }

    public function getTags() {
        // вырезаем теги (без начальных и конечных пробелов)
        $tags = preg_split('/\p{Zs}*,\p{Zs}*/',$this->tags);
        // формируем html
        $html = '';
        foreach($tags as $tag) {
            // пустые значения пропускаем
            if (!$tag) continue;
            $html .= '<a href="'.PagesModule::ACTION_TAGS.$tag.'">'.RealtyModule::t($tag,'tags').'</a>';
        }
        return $html;
    }

    public function save($validate = true, $attributes = null) {
        // дата создания и изменения
        $now = date(RealtyModule::TIMEFORMAT);
        if ($this->isNewRecord) $this->created = $now;
        else $this->created = date(RealtyModule::TIMEFORMAT,strtotime($this->created));
        $this->updated = $now;

        // язык обязательно задан
        if (empty($this->language)) $this->language = Yii::app()->language;
        // добавляем огрызок
        if (!$this->cut) $this->setCut();
        if (parent::save($validate)) {
            Page::resetCache(); // сбрасываем кеширование
            // определяем заголовок каркаса страницы
            $obj = Page::model()->findByPk($this->page_id);
            if ($obj) {
                // берем за основу русский вариант
                if ($this->language == 'ru') $obj->name = $this->title;
                else {
                    $pages = Pages::model()->findAllByAttributes(array('page_id'=>$obj->id));
                    $name = PagesModule::t('default page name');
                    // название странице предпочтительно на русском языке (если нет - то любой)
                    foreach($pages as $page) {
                        $name = $page->title;
                        if ($page->language == 'ru') break;
                    }
                    // задаем имя каркасу
                    echo $obj->name = $name;
                }
                $obj->saveNode();
            }
            return true;
        }
        var_dump($this->getErrors());
        // не удалось сохранить страницу
        return false;
    }

    public function rules()
    {
        return array(
            array('cut,title,seo,meta','length','max' => 255),
            array('language','in','range'=>Yii::app()->params['languages']),
            array('tags','match','pattern'=>THelper::REG_CSV,'message'=>PagesModule::t('tags','errors')),
            array('author,visible', 'numerical', 'integerOnly'=>true),
            array('created,updated','date','format'=>'yyyy.M.d H:m:s','message'=>$this->updated),
            array('text','safe'),
        );
    }
}