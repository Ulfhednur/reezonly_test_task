<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%items}}`.
 */
class m231023_184010_create_items_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%items}}', [
            'id' => $this->integer()->unsigned()->notNull(),
            'published' => $this->boolean()->notNull()->defaultValue(true),
            'price' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_by' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_date' => $this->dateTime()->notNull(),
            'modified_by' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'modified_date' => $this->dateTime()->notNull(),
        ]);

        $this->addPrimaryKey(
            '{{%items_pk}}',
            '{{%items}}',
            'id'
        );

        $this->alterColumn('{{%items}}', 'id', 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');

        /**
         * Внешние ключи устанавливаем только для не prod-окружения
         * Они сильно замедляют UPDATE/INSERT/DELETE и создают излишнюю нагрузку на базу
         * Консистентность данных контролируем на уровне приложения.
         */
        if (Yii::$app->params['environment'] !== 'prod') {
            $this->createIndex(
                '{{%items_created_by}}',
                '{{%items}}',
                'created_by'
            );

            $this->addForeignKey(
                '{{%items_created_by_fk}}',
                '{{%items}}',
                'created_by',
                '{{%users}}',
                'id',
                'RESTRICT'
            );

            $this->createIndex(
                '{{%items_modified_by}}',
                '{{%items}}',
                'modified_by'
            );

            $this->addForeignKey(
                '{{%items_modified_by_fk}}',
                '{{%items}}',
                'modified_by',
                '{{%users}}',
                'id',
                'RESTRICT'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%items}}');
    }
}
