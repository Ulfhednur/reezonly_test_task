<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m231023_184000_create_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /**
         * SchemaBuilderTrait::primaryKey() не используем, по тому, что он создаёт signed поле
         * Вы хоть раз видели отрицательный первичный ключ с AI?
         * Я видел: у ВК пользователи имеют id > 0, а группы (паблики) id < 0. Но это настолько далеко от SOLID...
         */
        $this->createTable('{{%users}}', [
            'id' => $this->integer()->unsigned()->notNull(),
            'username' => $this->string(255)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(255)->notNull()->defaultValue(''),
            'access_token' => $this->string(1000)->notNull()->defaultValue(''),
            'blocked' => $this->boolean()->notNull()->defaultValue(false),
            'valid_until' => $this->dateTime()->defaultValue(null),
        ]);

        $this->addPrimaryKey(
            '{{%users_pk}}',
            '{{%users}}',
            'id'
        );

        /**
         * включаем AUTO_INCREMENT
         */
        $this->alterColumn('{{%users}}', 'id', 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }
}
