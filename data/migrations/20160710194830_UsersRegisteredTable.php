<?php 
use Phpmig\Migration\Migration;

class UsersRegisteredTable extends Migration
{
    public $tableName = 'users_registered'; // Table name
    public $db;

    /**
    * Do the migration
    */
    public function up()
    {
        $this->db->create($this->tableName, function($table) {
            $table->string('username')->unique()->primary();
            $table->string('delete_code', 64);
        });
    }

    /**
    * Undo the migration
    */
    public function down()
    {
        $this->db->dropIfExists($this->tableName);
    }

    /**
    * Init the migration
    */
    public function init()
    {
        $this->db = $this->container['schema'];
    }
}