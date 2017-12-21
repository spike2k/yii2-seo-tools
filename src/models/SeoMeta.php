<?php

namespace Amirax\SeoTools\models;

use Yii;

/**
 * This is the model class for table "seo_meta".
 *
 * @property string $id
 * @property string $route
 * @property string $params
 * @property string $title
 * @property string $metakeys
 * @property string $metadesc
 * @property string $tags
 * @property string $h1
 * @property integer $robots
 */
class SeoMeta extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'seo_meta';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tags'], 'safe'],
            [['h1'], 'required'],
            [['robots'], 'integer'],
            [['route', 'params'], 'string', 'max' => 200],
            [['title', 'metakeys', 'metadesc'], 'string', 'max' => 255],
            [['h1'], 'string', 'max' => 500],
            [['route', 'params'], 'unique', 'targetAttribute' => ['route', 'params'], 'message' => 'The combination of Route and Params has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'route' => Yii::t('app', 'Route'),
            'params' => Yii::t('app', 'Params'),
            'title' => Yii::t('app', 'Title'),
            'metakeys' => Yii::t('app', 'Metakeys'),
            'metadesc' => Yii::t('app', 'Metadesc'),
            'tags' => Yii::t('app', 'Tags'),
            'h1' => Yii::t('app', 'H1'),
            'robots' => Yii::t('app', 'Robots'),
        ];
    }

 
     
     
     
     
     
     
     
     
         
}
