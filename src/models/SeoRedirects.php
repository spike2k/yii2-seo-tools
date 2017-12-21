<?php

namespace Amirax\SeoTools\models;

use Yii;

/**
 * This is the model class for table "seo_redirects".
 *
 * @property string $id
 * @property string $old_url
 * @property string $new_url
 * @property string $status
 */
class SeoRedirects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'seo_redirects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_url'], 'required'],
            [['status'], 'string'],
            [['old_url', 'new_url'], 'string', 'max' => 255],
            [['old_url'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'old_url' => Yii::t('app', 'Old Url'),
            'new_url' => Yii::t('app', 'New Url'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

 
     
     
     
         
}
