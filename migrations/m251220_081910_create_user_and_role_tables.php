<?php

use yii\db\Migration;

class m251220_081910_create_user_and_role_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%roles}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
        ]);

        $this->batchInsert('{{%roles}}', ['name'], [
            ['admin'],
            ['user'],
        ]);

        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'role_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('NOW()'),
            'updated_at' => $this->timestamp()->defaultExpression('NOW()'),
        ]);

        $this->addForeignKey(
            'fk-users-role_id',
            '{{%users}}',
            'role_id',
            '{{%roles}}',
            'id',
            'CASCADE'
        );

        $adminRoleId = (new \yii\db\Query())->select(['id'])->from('{{%roles}}')->where(['name' => 'admin'])->scalar();

        $this->insert('{{%users}}', [
            'username' => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'role_id' => $adminRoleId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-users-role_id', '{{%users}}');
        $this->dropTable('{{%users}}');
        $this->dropTable('{{%roles}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251220_081910_create_user_and_role_tables cannot be reverted.\n";

        return false;
    }
    */
}
