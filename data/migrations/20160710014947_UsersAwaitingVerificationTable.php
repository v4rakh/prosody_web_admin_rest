<?php 
use Phpmig\Migration\Migration;

class UsersAwaitingVerificationTable extends Migration
{
    public $tableName = 'users_awaiting_verification'; // Table name
    public $db;

    /**
    * Do the migration
    */
    public function up()
    {
        $this->db->create($this->tableName, function($table) {
            $table->increments('id');
            $table->string('username');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('verification_code');
            $table->timestamps();
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