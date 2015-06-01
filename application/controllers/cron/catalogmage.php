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
        log_message('debug','[CatalogMage.category] start : '.date('Y-m-d H:i:s'));

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
                $categories = $this->mageapi->getDetailCategory();
                $this->catalog_m->saveCategory($categories, $client);
            }
        }
        log_message('debug','[CatalogMage.category] end : '.date('Y-m-d H:i:s'));
    }

    public function productLink($code = "", $category = null , $store = null) {
        log_message('debug','[CatalogMage.productLink] start : '.date('Y-m-d H:i:s'));

        $this->load->model( array('client_m', 'catalog_m'));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();

        log_message('debug','[CatalogMage.category] start : '.date('Y-m-d H:i:s'));

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
                log_message('debug','[CatalogMage.productLink] : '.$client['client_code'].'#'.$category,'#',$store);
                $products = $this->mageapi->getCategoryProduct($store, $category);
                $this->catalog_m->saveCategoryProductLink($products, $client);
            }
        }

        log_message('debug','[CatalogMage.productLink] end : '.date('Y-m-d H:i:s'));
    }
}