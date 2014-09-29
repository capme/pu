<?php
/**
 * 
 * @author Ferry Ardhana <ferry.ardhana@velaasia.com>
 * @property CI_Migration $migration
 */
class Migrate extends CI_Controller {
	public function __construct()
	{
		// taruh sebelum parent::__construct();
		//$this->allow('perm_dashboard_view', 'client');
		//$this->allow_perm('perm_dashboard_view');

		parent::__construct();
		$this->load->database();
		$this->load->library("migration");
		require_once APPPATH.'migrations/Base_migration.php';

		#$this->load->model('jqplot_m');
	}
	
	public function index() {
		echo "<a href='".site_url("migrate/run/".time())."'>RUN</a>";
	}
	
	public function run() {
		$this->migration->current();
		redirect("migrate");
	}
	
	public function down() {
		/* $this->load->config("migration", TRUE);
		$vTarget = $this->config->item("migration")['migration_version'] - 1;
		
		$this->migration->version($vTarget); */
	}
	
}
?>