<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_description}}`.
 */
class m231023_184021_create_category_description_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_description}}', [
            'id' => $this->integer()->unsigned()->notNull(),
            'title' => $this->string(255)->notNull(),
            'short_description' => $this->text()->notNull()->defaultValue(''),
            'description' => $this->text()->notNull()->defaultValue('')
        ]);

        $this->addPrimaryKey(
            '{{%category_description_pk}}',
            '{{%category_description}}',
            'id'
        );

        if (Yii::$app->params['environment'] !== 'prod') {
            $this->createIndex(
                '{{%category_description_id}}',
                '{{%category_description}}',
                'id'
            );

            $this->addForeignKey(
                '{{%category_description_id_fk}}',
                '{{%category_description}}',
                'id',
                '{{%category}}',
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
        $this->dropTable('{{%category_description}}');
    }
}
