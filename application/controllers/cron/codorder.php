<?php
/**
 *
 * @property Client_m $client_m
 * @property Mageapi $mageapi
 * @property Codconfirmation_m $codconfirmation_m
 *
 */

class Codorder extends CI_Controller {

    public function fetch() {
        $this->load->model( array("client_m", "codconfirmation_m"));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();

        foreach($clients as $client) {
            if(!$client['mage_auth'] && !$client['mage_wsdl']) {
                continue;
            }

            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

            if( $this->mageapi->initSoap($config) ) {
                $orders = $this->mageapi->getUnexportedCodOrder();

                log_message('debug', '[codorder.fetch]: data '.print_r($orders,true));

                if(isset($orders['data']) && !empty($orders['data'])) {
                    $addedIds = $this->codconfirmation_m->add($client, $orders['data']);
                    if (!empty($addedIds)) {
                        $this->mageapi->setCodOrderAsExported($addedIds);
                    }
                } elseif( isset($orders['error']) && $orders['error']){
                    log_message('error', "[codorder.fetch]: ". $client['name'] . ' >> ' . $orders['msg']);
                }
            }
        }
    }
}