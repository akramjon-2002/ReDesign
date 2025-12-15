<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "requests".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $texture_id
 * @property string|null $input_image_path
 * @property string|null $output_image_path
 * @property string|null $replicate_id
 * @property string|null $status
 * @property string $created_at
 *
 * @property Texture $texture
 */
class Request extends ActiveRecord
{
    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requests';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'texture_id'], 'integer'],
            [['created_at'], 'safe'],
            [['input_image_path', 'output_image_path', 'replicate_id', 'status'], 'string', 'max' => 255],
            [['texture_id'], 'exist', 'skipOnError' => true, 'targetClass' => Texture::class, 'targetAttribute' => ['texture_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'texture_id' => 'Texture ID',
            'input_image_path' => 'Input Image',
            'output_image_path' => 'Output Image',
            'replicate_id' => 'Replicate ID',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Texture]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTexture()
    {
        return $this->hasOne(Texture::class, ['id' => 'texture_id']);
    }
}
