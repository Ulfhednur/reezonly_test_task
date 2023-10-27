<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category}}`.
 */
class m231023_184020_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->integer()->unsigned()->notNull(),
            'parent_id' => $this->integer()->unsigned()->defaultValue(null),
            'published' => $this->boolean()->notNull()->defaultValue(true),
            'created_by' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_date' => $this->dateTime()->notNull(),
            'modified_by' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'modified_date' => $this->dateTime()->notNull(),
        ]);

        $this->addPrimaryKey(
            '{{%category_pk}}',
            '{{%category}}',
            'id'
        );

        $this->alterColumn('{{%category}}', 'id', 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT');

        /**
         * ключ нужен для построения дерева категорий
         * само поле nullable для возможности задать категорию корневой, т.е. без родителя при наличии
         * foreign key
         */
        $this->createIndex(
            '{{%category_parent_id}}',
            '{{%category}}',
            'parent_id'
        );

        /**
         * Внешние ключи устанавливаем только для не prod-окружения
         * Они сильно замедляют UPDATE/INSERT/DELETE и создают излишнюю нагрузку на базу
         * Консистентность данных контролируем на уровне приложения.
         */
        if (Yii::$app->params['environment'] !== 'prod') {
            $this->addForeignKey(
                '{{%category_parent_id_fk}}',
                '{{%category}}',
                'parent_id',
                '{{%category}}',
                'id',
                'RESTRICT'
            );

            $this->createIndex(
                '{{%category_created_by}}',
                '{{%category}}',
                'created_by'
            );

            $this->addForeignKey(
                '{{%category_created_by_fk}}',
                '{{%category}}',
                'created_by',
                '{{%users}}',
                'id',
                'RESTRICT'
            );

            $this->createIndex(
                '{{%category_modified_by}}',
                '{{%category}}',
                'modified_by'
            );

            $this->addForeignKey(
                '{{%category_modified_by_fk}}',
                '{{%category}}',
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
        $this->dropTable('{{%category}}');
    }
}
