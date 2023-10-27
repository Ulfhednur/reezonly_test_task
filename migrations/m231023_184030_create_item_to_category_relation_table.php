<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%item_to_category_relation}}`.
 */
class m231023_184030_create_item_to_category_relation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%item_to_category_relation}}', [
            'category_id' => $this->integer()->unsigned()->notNull(),
            'item_id' => $this->integer()->unsigned()->notNull(),
        ]);

        $this->addPrimaryKey(
            '{{%item_to_category_relation_pk}}',
            '{{%item_to_category_relation}}',
            ['category_id', 'item_id']
        );

        /**
         * Для поиска категорий объекта в админке
         */
        $this->createIndex(
            '{{%item_to_category_relation_uk}}',
            '{{%item_to_category_relation}}',
            ['item_id', 'category_id'],
            true
        );

        if (Yii::$app->params['environment'] !== 'prod') {
            $this->createIndex(
                '{{%item_to_category_relation_category_id}}',
                '{{%item_to_category_relation}}',
                'category_id'
            );
            $this->addForeignKey(
                '{{%item_to_category_relation_category_id_fk}}',
                '{{%item_to_category_relation}}',
                'category_id',
                '{{%category}}',
                'id',
                'RESTRICT'
            );

            $this->createIndex(
                '{{%item_to_category_relation_item_id}}',
                '{{%item_to_category_relation}}',
                'item_id'
            );
            $this->addForeignKey(
                '{{%item_to_category_relation_item_id_fk}}',
                '{{%item_to_category_relation}}',
                'item_id',
                '{{%items}}',
                'id',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%item_to_category_relation}}');
    }
}
