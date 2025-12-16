<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "textures".
 *
 * @property int $id
 * @property string $title
 * @property string|null $prompt_suffix
 * @property string $image_path
 * @property string|null $type
 * @property string $created_at
 *
 * @property Request[] $requests
 */
class Texture extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'textures';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['image_path'], 'required', 'when' => function ($model) {
                return $model->isNewRecord;
            }, 'whenClient' => "function (attribute, value) { return true; }", 'message' => 'Image is required'],
            [['created_at'], 'safe'],
            [['title', 'prompt_suffix', 'image_path', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'prompt_suffix' => 'Prompt Suffix (optional)',
            'image_path' => 'Texture Image',
            'type' => 'Type',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Requests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequests()
    {
        return $this->hasMany(Request::class, ['texture_id' => 'id']);
    }
}
