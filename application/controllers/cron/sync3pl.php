<?php 
/*
 * cron for sync 3pl with table inv_items_(client_id)   
 */

/**
 * Class Sync3pl
 * @property Inbounddocument_m $inbounddocument_m
 * @property Client_m $client_m
 */
class Sync3pl extends CI_Controller {

	function __construct()
    {
        parent::__construct();
		$this->load->add_package_path(APPPATH."third_party/threepl/");
		$this->load->library("inbound_threepl", null, "inbound_threepl");
		$this->load->model( array("client_m", "inbounddocument_m") );
    }
	
	public function run() {
		$this->load->add_package_path(APPPATH."third_party/threepl/");
		$this->load->library("inbound_threepl", null, "inbound_threepl");
		
		$clients = $this->client_m->getClients();
		foreach($clients as $itemClient){
			$id = $itemClient['id'];
			$client_code = $itemClient['client_code'];
			$threepl_user = $itemClient['threepl_user'];
			$threepl_pass = $itemClient['threepl_pass'];
			
			$c['threepluser'] = $threepl_user;
			$c['threeplpass'] = $threepl_pass;
			$client = $id;
			
			$this->inbound_threepl->setConfig( array("username" => $c['threepluser'], "password" => $c['threeplpass']) );
			$return = $this->inbound_threepl->getItems();
			if(is_array($return)){
				//insert ignore into table inv_items
				$this->inbounddocument_m->saveToInvItems($client, $return);
				echo "Client ".$client_code." sync ".count($return)." items<br>";
			}else{
				echo "Client ".$client_code." sync failed.<br>";
				echo $return;
			}
			echo "<br><br>";
		}
	}
}