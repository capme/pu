<?php
/**
 * 
 * @author Ferry Ardhana <ferry.ardhana@velaasia.com>
 * @package dart
 * 
 * This is base file for dart's migration script
 * Just contain with two method up() to upgrade and down() to downgrade 1 version behind
 */
class Base_migration extends CI_Migration {
	
	public function __construct($config = array()) {
		parent::__construct($config);
	}
	
	public function up() {
		$obj = new ReflectionClass($this);
		$migrationFile = dirname(__FILE__)."/schema/".basename($obj->getFileName(), ".php")."_up.sql";
		if(is_file($migrationFile)) {
			$this->db->trans_start();
			$sqls = explode("\n", file_get_contents($migrationFile));
			foreach($sqls as $sql) {
				$this->db->query($sql);
			}
			$this->db->trans_complete();
		}
	}
	
	public function down() {
		$obj = new ReflectionClass($this);
		$migrationFile = dirname(__FILE__)."/schema/".basename($obj->getFileName(), ".php")."_down.sql";
		if(is_file($migrationFile)) {
			$this->db->trans_start();
			$sqls = explode("\n", file_get_contents($migrationFile));
			foreach($sqls as $sql) {
				$this->db->query($sql);
			}
			$this->db->trans_complete();
		}
	}
}
?>