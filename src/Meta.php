<?php

namespace Amirax\SeoTools;

use yii;
use yii\web\View;
use yii\base\Component;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use Amirax\SeoTools\models\SeoMeta;


/**
 * Amirax SEO Tools: Meta tags
 *
 * @author Max Voronov <v0id@list.ru>
 *
 * @link http://www.amirax.ru/
 * @link https://github.com/amirax/yii2-seo-tools
 * @license https://github.com/amirax/yii2-seo-tools/blob/master/LICENSE.md
 */
class Meta extends Component
{

    protected $_view = null;
    protected $_routeMetaData = [];
    protected $_paramsMetaData = [];
    public $_defaultMetaData = [];
    protected $_userMetaData = [];
    protected $_variables = [];

    /**
     * Init component
     */
    public function init()
    {


//        var_dump($this);
  //      exit;
        Yii::$app->view->on(View::EVENT_BEGIN_PAGE, [$this, '_applyMeta']);
    }


    /**
     * Set variables for autoreplace
     *
     * @param string|array $name
     * @param string $value
     * @return $this
     */
    public function setVar($name, $value = '')
    {
        if (!empty($name)) {
            if (is_array($name)) {
                foreach ($name AS $varName => $value) {
                    $this->_variables['%' . $varName . '%'] = $value;
                }
            } else {
                $this->_variables['%' . $name . '%'] = $value;
            }
        }
        return $this;
    }


   /**
     * Set model with meta data
     *
     * @param string|array $name
     * @param string $value
     * @return $this
     */
    public function set($model,$useOnlySEO = false)
    {

        
        if(!is_object($model) && is_array($model)){
            $model =(object) $model;
            $model->meta_title = $model->title;
            $model->meta_description = $model->description;
        }
        
        $meta_title = (isset($model->meta_title)?$model->meta_title:(isset($model->seo_title)?$model->seo_title:(isset($model->page_title)?$model->page_title:(isset($model->PR_META_TITLE)?$model->PR_META_TITLE:null))));

        $meta_description = (isset($model->meta_description)?$model->meta_description:(isset($model->seo_description)?$model->seo_description:(isset($model->page_description)?$model->page_description:(isset($model->PR_META_DESCRIPTION)?$model->PR_META_DESCRIPTION:null))));

        $meta_keys = (isset($model->meta_keys)?$model->meta_keys:(isset($model->seo_keys)?$model->seo_keys:(isset($model->page_keywords)?$model->page_keywords:null)));

        $image = isset($model->params) && is_array($model->params) && !empty($model->params['image'])?$model->params['image']:null;

        if(!$useOnlySEO){

            if(!$meta_title && (!empty($model->attributes['nazwa']) || !empty($model->attributes['tytul']) || !empty($model->attributes['tyt']) || !empty($model->attributes['title']) || !empty($model->attributes['name']) || !empty($model->attributes['PR_TYTUL']))){
                $meta_title = ($model->attributes['nazwa']?$model->attributes['nazwa']:($model->attributes['tytul']?$model->attributes['tytul']:($model->attributes['tyt']?$model->attributes['tyt']:($model->attributes['title']?$model->attributes['title']:($model->attributes['name']?$model->attributes['name']:$model->attributes['PR_TYTUL'])))));
            }

            if(!$meta_description && (!empty($model->attributes['opis']) || !empty($model->attributes['html']) || !empty($model->attributes['lead']) || !empty($model->attributes['content']) || !empty($model->attributes['tresc']) || !empty($model->attributes['txt']))){
                $meta_description = ($model->attributes['opis']?$model->attributes['opis']:($model->attributes['html']?$model->attributes['html']:($model->attributes['lead']?$model->attributes['lead']:($model->attributes['tresc']?$model->attributes['tresc']:($model->attributes['content']?$model->attributes['content']:$model->attributes['txt'])))));

                $meta_description = preg_replace("/{block [a-zA-Z0-9-_]*}/", "", $meta_description);
            }

            if(!$image)
                $image = (isset($model->miniatura)?$model->miniatura:(isset($model->banner)?$model->banner:(isset($model->baner)?$model->baner:(isset($model->thumb)?$model->thumb:null))));
 
        }

        if($meta_title){
             $this->title = $this->tags["og:title"] = $meta_title.(Yii::$app->params['metatitle_suffix']?" - ".Yii::$app->params['metatitle_suffix']:"");
             Yii::$app->controller->view->title = $meta_title;
        } 

        if($meta_description){
            $this->metadesc = $this->tags["og:description"] = \yii\helpers\StringHelper::truncate(strip_tags($meta_description),150);
        } 

        if($meta_keys){
            $this->metakeys = strip_tags($meta_keys);
        }         

        if($image) {
            $this->tags["og:image"] = $image;
            if(substr($this->tags["og:image"], 0,4) != 'http') $this->tags["og:image"] = \yii\helpers\Url::to(str_replace("//","/",Yii::getAlias("@web/".$this->tags["og:image"])),true);
        }                 
        
    }

    /**
     * Apply metatags to page
     *
     * @param $event
     */
    protected function _applyMeta($event)
    {
        if(Yii::$app->request->isAjax) return false;

        $this->_view = $event->sender;

        $this->_getMetaData(Yii::$app->requestedRoute, Yii::$app->requestedParams);
        $data = ArrayHelper::merge(
            $this->_defaultMetaData,
            $this->_routeMetaData,
            $this->_paramsMetaData,
            $this->_userMetaData
        );
        $this->_prepareVars()
            ->_setTitle($data)
            ->_setH1($data)
            ->_setMeta($data)
            ->_setRobots($data)
            ->_setTags($data);
    }

    protected function _applyH1($event)
    {
        $this->_view = $event->sender;

        $this->_getMetaData(Yii::$app->requestedRoute, Yii::$app->requestedParams);
        $data = ArrayHelper::merge(
            $this->_defaultMetaData,
            $this->_routeMetaData,
            $this->_paramsMetaData,
            $this->_userMetaData
        );
        $this->_prepareVars()
            ->_setH1($data);
    }


    /**
     * Init default variables for autoreplace
     *
     * @return $this
     */
    protected function _prepareVars()
    {
        $this->setVar([
            'HOME_URL' => Url::home(true),
            'CANONICAL_URL' => Url::canonical(),
            'LOCALE' => Yii::$app->formatter->locale
        ]);
        return $this;
    }


    /**
     * Set tag <title>
     *
     * @param array $data
     * @return $this
     */
    protected function _setTitle($data)
    {
        
        $data['title'] = str_replace(array_keys($this->_variables), $this->_variables, trim($data['title']));
        $this->setVar('SEO_TITLE', $data['title']);
        $this->_view->title = $data['title'];
        return $this;
    }



    protected function _setH1($data)
    {
        $data['h1'] = str_replace(array_keys($this->_variables), $this->_variables, trim($data['h1']));
        $this->setVar('SEO_H1', $data['h1']);
        $this->_view->params['h1'] = $data['h1'];
        
        return $this;
    }


    /**
     * Set meta keywords and meta description tags
     *
     * @param array $data
     * @return $this
     */
    protected function _setMeta($data)
    {
        $data['metakeys'] = str_replace(array_keys($this->_variables), $this->_variables, trim($data['metakeys']));
        $data['metakeys'] = preg_replace('|,( )+|', ',', $data['metakeys']);
        $this->_view->registerMetaTag(['name' => 'keywords', 'content' => $data['metakeys']]);
        $this->setVar('SEO_METAKEYS', $data['metakeys']);

        $data['metadesc'] = str_replace(array_keys($this->_variables), $this->_variables, trim($data['metadesc']));
        $this->_view->registerMetaTag(['name' => 'description', 'content' => $data['metadesc']]);
        $this->setVar('SEO_METADESC', $data['metadesc']);

        return $this;
    }


    /**
     * Set meta robots tag
     *
     * @param array $data
     * @return $this
     */
    protected function _setRobots($data)
    {
        if ($data['robots'] > 0) {
            $robots = new Robots();
            if ($robots->idExists($data['robots'])) {
                $this->_view->registerMetaTag(['name' => 'robots', 'content' => $robots->getPropById($data['robots'])]);
            }
        }
        return $this;
    }


    /**
     * Set other meta tags
     * For example, OpenGraph tags
     *
     * @param array $data
     * @return $this
     */
    protected function _setTags($data)
    {
        $tags = ArrayHelper::merge(
            array_key_exists('tags', $this->_defaultMetaData) ? $this->_defaultMetaData['tags'] : [],
             array_key_exists('tags', $data) ? $data['tags']: []
        );
        if (!empty($tags)) {
            foreach ($tags AS $tagName => $tagProp) {
                if (!empty($tagProp) && is_string($tagProp))
                    $tagProp = str_replace(array_keys($this->_variables), $this->_variables, $tagProp);
                $this->_view->registerMetaTag(['property' => $tagName, 'content' => $tagProp]);
            }
        }
        return $this;
    }


    /**
     * Get data from database
     *
     * @param string $route
     * @param array $params
     */
    protected function _getMetaData($route, $params = null)
    {
            $params = json_encode($params);
            $model = SeoMeta::find()
                ->where(['route' => '-'])
                ->orWhere(
                    ['and', 'route=:route', ['or', 'params IS NULL', 'params=:params', 'params=""']],
                    [':route' => $route, ':params' => $params]
                )->asArray()
                ->all();

            foreach ($model AS $item) {
                $item = array_filter($item, 'strlen');
                if (!empty($item['tags'])) $item['tags'] = (array)json_decode($item['tags']);
                if ($item['route'] == '-') $this->_defaultMetaData = $item;

                elseif ($item['route'] != '-' && empty($item['params'])) $this->_routeMetaData = $item;
                elseif ($item['route'] != '-' && !empty($item['params'])) $this->_paramsMetaData = $item;
            }
    }


    public function &__get($prop)
    {
        return $this->_userMetaData[$prop];
    }


    public function __set($prop, $value)
    {
        if (empty($prop)) return;
        $this->_userMetaData[$prop] = &$value;
    }

}