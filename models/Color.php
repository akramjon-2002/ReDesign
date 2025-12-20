<?php

namespace app\models;

    use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "colors".
 *
 * @property int $id
 * @property string $title
 * @property string $hex
 * @property int|null $sort_order
 * @property string $created_at
 *
 * @property Request[] $requests
 */
class Color extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'colors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'hex'], 'required'],
            [['sort_order'], 'integer'],
            [['created_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['hex'], 'string', 'max' => 7],
            [['hex'], 'match', 'pattern' => '/^#[0-9A-Fa-f]{6}$/', 'message' => 'HEX must be in format #RRGGBB'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'hex' => Yii::t('app', 'Hex'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * Gets query for [[Requests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRequests()
    {
        return $this->hasMany(Request::class, ['color_id' => 'id']);
    }
}
