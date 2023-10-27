<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%item_description}}`.
 */
class m231023_184011_create_item_description_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%item_description}}', [
            'id' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'title' => $this->string(255)->notNull(),
            'short_description' => $this->text()->notNull()->defaultValue(''),
            'description' => $this->text()->notNull()->defaultValue('')
        ]);

        $this->addPrimaryKey(
            '{{%item_description_pk}}',
            '{{%item_description}}',
            'id'
        );

        if (Yii::$app->params['environment'] !== 'prod') {
            $this->addForeignKey(
                '{{%item_description_id_fk}}',
                '{{%item_description}}',
                'id',
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
        $this->dropTable('{{%item_description}}');
    }
}
