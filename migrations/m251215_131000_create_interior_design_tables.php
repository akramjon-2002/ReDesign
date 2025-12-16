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
            'prompt_suffix' => $this->string()->null(), // optional, e.g. "matte finish"
            'image_path' => $this->string()->notNull(), // Texture image (required for Gemini reference)
            'type' => $this->string()->defaultValue('texture'),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);

        // Table: colors (краски)
        $this->createTable('{{%colors}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(), // e.g. "White", "Beige"
            'hex' => $this->string(7)->notNull(), // e.g. "#FFFFFF"
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);

        // Table: requests
        $this->createTable('{{%requests}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->bigInteger()->notNull(), // Telegram User ID
            'texture_id' => $this->integer()->null(),
            'color_id' => $this->integer()->null(),
            'color_hex' => $this->string(7)->null(), // Custom color if not from palette
            'input_image_path' => $this->string(),
            'output_image_path' => $this->string(),
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

        // Foreign Key: requests.color_id -> colors.id
        $this->addForeignKey(
            '{{%fk-requests-color_id}}',
            '{{%requests}}',
            'color_id',
            '{{%colors}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Insert default colors
        $this->batchInsert('{{%colors}}', ['title', 'hex', 'sort_order'], [
            ['White', '#FFFFFF', 1],
            ['Beige', '#F5F5DC', 2],
            ['Light Gray', '#D3D3D3', 3],
            ['Gray', '#808080', 4],
            ['Black', '#000000', 5],
            ['Dark Red', '#8B0000', 6],
            ['Red', '#FF0000', 7],
            ['Orange', '#FFA500', 8],
            ['Gold', '#FFD700', 9],
            ['Yellow', '#FFFF00', 10],
            ['Light Green', '#90EE90', 11],
            ['Green', '#008000', 12],
            ['Dark Green', '#006400', 13],
            ['Light Blue', '#ADD8E6', 14],
            ['Blue', '#0000FF', 15],
            ['Dark Blue', '#00008B', 16],
            ['Purple', '#800080', 17],
            ['Pink', '#FFC0CB', 18],
            ['Brown', '#A52A2A', 19],
            ['Chocolate', '#D2691E', 20],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-requests-color_id}}', '{{%requests}}');
        $this->dropForeignKey('{{%fk-requests-texture_id}}', '{{%requests}}');
        $this->dropIndex('{{%idx-requests-user_id}}', '{{%requests}}');
        $this->dropTable('{{%requests}}');
        $this->dropTable('{{%colors}}');
        $this->dropTable('{{%textures}}');
    }
}
