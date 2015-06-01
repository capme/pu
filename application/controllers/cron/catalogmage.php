<?php

/**
 * Class CatalogMage
 * @property Mageapi $mageapi
 * @property Catalog_m $catalog_m

 */
class CatalogMage extends CI_Controller {

    public function category($code = "") {
        $this->load->model( array('client_m', 'catalog_m'));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();

        foreach($clients as $client) {
            if($code && $client['client_code'] != $code){
                continue;
            }

            if(!$client['mage_auth'] && !$client['mage_wsdl']) {
                continue;
            }

            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

            if( $this->mageapi->initSoap($config) ) {
                print_r($config);
                $categories = $this->mageapi->getDetailCategory();
                print_r($categories);
                $this->catalog_m->saveCategory($categories, $client);
            }
        }
    }


}