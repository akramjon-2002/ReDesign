<?php

use yii\db\Migration;

/**
 * Class m251215_131000_create_interior_design_tables
 */
class m251215_131000_create_interior_design_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Table: textures
        $this->createTable('{{%textures}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'prompt_suffix' => $this->string()->notNull(), // e.g. "modern style, wooden floor"
            'image_path' => $this->string()->null(), // Preview image of the style
            'type' => $this->string()->defaultValue('texture'), // e.g. 'paint', 'texture', 'architecture_style'
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);

        // Table: requests
        $this->createTable('{{%requests}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger()->notNull(), // Telegram User ID
            'texture_id' => $this->integer(),
            'input_image_path' => $this->string(),
            'output_image_path' => $this->string(),
            'replicate_id' => $this->string(), // ID from Replicate API
            'status' => $this->string()->defaultValue('new'), // new, processing, completed, failed
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);

        // Index for user_id (Telegram ID)
        $this->createIndex(
            '{{%idx-requests-user_id}}',
            '{{%requests}}',
            'user_id'
        );

        // Foreign Key: requests.texture_id -> textures.id
        $this->addForeignKey(
            '{{%fk-requests-texture_id}}',
            '{{%requests}}',
            'texture_id',
            '{{%textures}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-requests-texture_id}}', '{{%requests}}');
        $this->dropIndex('{{%idx-requests-user_id}}', '{{%requests}}');
        $this->dropTable('{{%requests}}');
        $this->dropTable('{{%textures}}');
    }
}
