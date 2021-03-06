<?php

namespace app\migrations\schema;

use yii\db\Migration;

class m171219_230853_create_table_data_UserConfig extends Migration
{
  public function safeUp()
  {
    $tableOptions = null;
    if ($this->db->driverName === 'mysql') {
      $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
    }

    $this->createTable('{{%data_UserConfig}}', [
      'id' => $this->integer(11)->notNull()->append('AUTO_INCREMENT PRIMARY KEY'),
      'value' => $this->string(255),
      'created' => $this->timestamp(),
      'modified' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
      'UserId' => $this->integer(11),
      'ConfigId' => $this->integer(11),
    ], $tableOptions);
    return true;
  }

  public function safeDown()
  {
    $this->dropTable('{{%data_UserConfig}}');
    return true;
  }
}
