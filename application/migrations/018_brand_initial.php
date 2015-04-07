<?php 
/*
 * @property Client_m $client_m
 */
class Migration_Brand_Initial extends Base_Migration {
    public function up() {
        parent::up();
        $this->load->model('client_m');
        $this->load->model('clientoptions_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
        
        foreach($clients as $client) {
        	
        	$id = $client['id'];
        	$client_code = $client['client_code'];
        	if(stristr($client_code, "blow")){
        		//blowshoes
        		$bInitial = "BS";
        	}elseif(stristr($client_code, "g2000")){
        		//g2000
        		$bInitial = "G2";
        	}elseif(stristr($client_code, "jobb")){
        		//jobb
        		$bInitial = "Jobb";        		
        	}elseif(stristr($client_code, "farish")){
        		//farishfs
        		$bInitial = "FS";
        	}elseif(stristr($client_code, "agrapana")){
        		//batik agrapana
        		$bInitial = "BA";
        	}elseif(stristr($client_code, "bonds")){
        		//bonds
        		$bInitial = "BO";
        	}elseif(stristr($client_code, "nicklaus")){
        		//jack nicklaus
        		$bInitial = "JN";
        	}elseif(stristr($client_code, "ekretek")){
        		//ekretek
        		$bInitial = "EK";
        	}elseif(stristr($client_code, "universo")){
        		//universo
        		$bInitial = "SF";
        	}elseif(stristr($client_code, "leecooper")){
        		//leecooper
        		$bInitial = "LC";
        	}
        	
        	$this->clientoptions_m->save($id, "brand_initial", $bInitial);
        	
        }
        $this->db->trans_complete();
    }
    public function down() {
        parent::down();
        $this->load->model('client_m');
        $clients = $this->client_m->getClients();
        $this->db->trans_start();
		$this->db->query("DELETE FROM client_options where option_name='brand_initial'");
        $this->db->trans_complete();
    }
}
?>