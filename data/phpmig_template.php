<?= "<?php ";?>

use Phpmig\Migration\Migration;

class <?= $className ?> extends Migration
{
    public $tableName = ''; // Table name
    public $db;

    /**
    * Do the migration
    */
    public function up()
    {
        $this->db->create($this->tableName, function($table) {
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