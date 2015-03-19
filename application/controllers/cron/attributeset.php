<?php
/**
 * @property Client_m $client_m
 * @property Mageapi $mageapi
 * @property Clientoptions_m $clientoptions_m
 */

class Attributeset extends CI_Controller {

    const OPTION_NAME_ATTRIBUTE_SET = 'attribute_set';

    public function fetch(){
        $this->load->model(array('client_m','clientoptions_m'));
        $this->load->library('mageapi');

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
                $attributes = $this->mageapi->getProductAttributeSet();

                if( !empty($attributes)){
                    $this->clientoptions_m->save($client['id'],self::OPTION_NAME_ATTRIBUTE_SET,json_encode($attributes));
                }

            }

            log_message('debug','[cron/attributeset.fetch] : '.$client['client_code']);

        }
    }

}