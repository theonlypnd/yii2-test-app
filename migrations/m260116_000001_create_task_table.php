<?php

use yii\db\Migration;

class m260116_000001_create_task_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%task}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'text' => $this->string(1000)->notNull(),
            'image' => $this->string(),
            'is_done' => $this->boolean()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx_tasks_username', '{{%task}}', 'username');
        $this->createIndex('idx_tasks_email', '{{%task}}', 'email');
        $this->createIndex('idx_tasks_is_done', '{{%task}}', 'is_done');
    }

    public function safeDown()
    {
        $this->dropTable('{{%task}}');
    }
}
