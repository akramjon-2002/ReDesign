<?php

use yii\db\Migration;

/**
 * Handles adding aspect_ratio to table `requests`.
 */
class m251222_111000_add_aspect_ratio_to_requests extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%requests}}', 'aspect_ratio', $this->string(20)->null()->after('color_hex'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%requests}}', 'aspect_ratio');
    }
}
