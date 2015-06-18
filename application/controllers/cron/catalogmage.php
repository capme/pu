<?php

/**
 * Class CatalogMage
 * @property Mageapi $mageapi
 * @property Catalog_m $catalog_m

 */
class CatalogMage extends CI_Controller {

    var $isParaplouOnly = 1;

    public function all($code = ""){
        $this->category($code);
        $this->categoryProduct($code);
        $this->sorting($code);
        $this->updatePositionCategoryProduct($code);
//        $this->bulkUpdatePositionCategoryProduct($code);
    }


    public function category($code = "") {
        $this->load->model( array('client_m', 'catalog_m'));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();
        log_message('debug','[CatalogMage.category] start : '.date('Y-m-d H:i:s'));

        foreach($clients as $client) {
            if($code && $client['client_code'] != $code){
                continue;
            }

            // khusus untuk PARAPLOU dulu
            if($this->isParaplouOnly && $client['client_code'] != 'PARAPLOU'){
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
                log_message('debug','== CatalogMage.category  : '.$client['client_code']);
            }
        }
        log_message('debug','[CatalogMage.category] end : '.date('Y-m-d H:i:s'));
    }

    /**
     * categoryProduct : get category.assignedProduct
     * @param string $code : Client['client_code'] (kalo mau running specific client)
     * @param null $category : categoryId ((kalo mau running specific category)
     * @param null $store : storeId (kalo mau running specific store
     */
    public function categoryProduct($code = "", $category = null , $store = null) {
        log_message('debug','[CatalogMage.categoryProduct] start : '.date('Y-m-d H:i:s'));

        $this->load->model( array('client_m', 'catalog_m'));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();

        foreach($clients as $client) {
            if($code && $client['client_code'] != $code){
                continue;
            }

            // khusus untuk PARAPLOU dulu
            if($this->isParaplouOnly && $client['client_code'] != 'PARAPLOU'){
                continue;
            }

            if(!$client['mage_auth'] && !$client['mage_wsdl']) {
                continue;
            }

            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

//            print_r($config);

            $categories = $this->catalog_m->getCategory($client);
            foreach($categories as $_category){
                if($category && $_category['category_id'] != $category){
                    continue;
                }
                if( $this->mageapi->initSoap($config) ) {
                    $products = $this->mageapi->getCategoryProduct($store, $_category['category_id']);
                    $this->catalog_m->insertCategoryProduct($products, $client);
                    log_message('debug','== CatalogMage.categoryProduct : '.$client['client_code'].'#'.$_category['category_id'],'#',$store.'#'.count($products));
                }
            }
        }

        log_message('debug','[CatalogMage.categoryProduct] end : '.date('Y-m-d H:i:s'));
    }


    /**
     * updatePositionCategoryProduct : buat update position
     * @param string $code : Client['client_code'] (kalo mau running spesific client)
     * @param null $category : categoryId ((kalo mau running spesific category)
     */
    public function updatePositionCategoryProduct($code = "", $category = null) {
        log_message('debug','[CatalogMage.updatePositionCategoryProduct] start : '.date('Y-m-d H:i:s'));

        $this->load->model( array('client_m', 'catalog_m'));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();

        foreach($clients as $client) {
            if($code && $client['client_code'] != $code){
                continue;
            }

            // khusus untuk PARAPLOU dulu
            if($this->isParaplouOnly && $client['client_code'] != 'PARAPLOU'){
                continue;
            }

            if(!$client['mage_auth'] && !$client['mage_wsdl']) {
                continue;
            }

            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

            $categories = $this->catalog_m->getCategory($client);

            foreach($categories as $_category) {
                if ($category && $_category['category_id'] != $category) {
                    continue;
                }

                $filters['groupby'] = "product_id";
                $categoryProducts = $this->catalog_m->getCatalogCategoryProduct($client, $_category['category_id'], $filters);

                if ($this->mageapi->initSoap($config)) {
                    foreach ($categoryProducts as $_product) {
                        if (!empty($_product['result_index'])) {
                            $update = $this->mageapi->updateCategoryProductPosition($_product['category_id'], $_product['product_id'], $_product['result_index']);
                            $result[] = array('category_id' => $_product['category_id'], 'product_id' => $_product['product_id'], 'updated' => $update);
                        }
                    }
                    log_message('debug','== CatalogMage.updatePositionCategoryProduct : '.$client['client_code'].'#'.$_category['category_id'].'#'.count($result));
                }
            }
        }

//        print_r($result);
        log_message('debug','[CatalogMage.updatePositionCategoryProduct] end : '.date('Y-m-d H:i:s'));
    }


    /**
     * bulkUpdatePositionCategoryProduct : using multicall to update to magento
     * @param string $code
     * @param null $category
     */
    public function bulkUpdatePositionCategoryProduct($code = "", $category = null) {
        log_message('debug','[CatalogMage.bulkUpdatePositionCategoryProduct] start : '.date('Y-m-d H:i:s'));

        $this->load->model( array('client_m', 'catalog_m'));
        $this->load->library("mageapi");

        $clients = $this->client_m->getClients();

        foreach($clients as $client) {
            if($code && $client['client_code'] != $code){
                continue;
            }

            // khusus untuk PARAPLOU dulu
            if($this->isParaplouOnly && $client['client_code'] != 'PARAPLOU'){
                continue;
            }

            if(!$client['mage_auth'] && !$client['mage_wsdl']) {
                continue;
            }

            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

            $categories = $this->catalog_m->getCategory($client);
            log_message('debug','[CatalogMage.bulkUpdatePositionCategoryProduct] '.$client['client_code']);

            foreach($categories as $_category) {
                if ($category && $_category['category_id'] != $category) {
                    continue;
                }

                $filters['groupby'] = "product_id";
                $categoryProducts = $this->catalog_m->getCatalogCategoryProduct($client, $_category['category_id'], $filters);

                if ($this->mageapi->initSoap($config)) {
                    $newPost = array();
                    foreach ($categoryProducts as $_product) {
                        if (!empty($_product['result_index'])) {
                            $newPost[] = $_product;
                        }
                    }
                    if(!empty($newPost)){
                        $update = $this->mageapi->bulkUpdateCategoryProductPosition($newPost);
                        if(!$update){
                            log_message("debug","== CatalogMage.bulkUpdatePositionCategoryProduct] failed : client[".$code."]  categoryId[".$_category['category_id']."] countCategoryProduct[".count($categoryProducts)."] attemptUpdate[".count($newPost)."]");
                        } else {
                            log_message("debug","== CatalogMage.bulkUpdatePositionCategoryProduct] : client[".$code."]  categoryId[".$_category['category_id']."] countCategoryProduct[".count($categoryProducts)."] attemptUpdate[".count($newPost)."] update[data:".count(@$update['data']).", success:".@$update['success'].", error:".@$update['error']."]");
                        }
                    }
                }
            }
        }

//        print_r($result);
        log_message('debug','[CatalogMage.bulkUpdatePositionCategoryProduct] end : '.date('Y-m-d H:i:s'));
    }


    public function sorting( $code = "", $category = null ){
        log_message('debug','[CatalogMage.sorting] start : '.date('Y-m-d H:i:s'));

        $this->load->model( array('client_m', 'catalog_m') );

        $clients = $this->client_m->getClients();

        foreach($clients as $client) {
            if($code && $client['client_code'] != $code){
                continue;
            }

            $categories = $this->catalog_m->getCategory($client);

            foreach($categories as $_category){
                if($category && $_category['category_id'] != $category){
                    continue;
                }

                $filters['groupby'] = "product_id";
                $categoryProducts = $this->catalog_m->getCatalogCategoryProduct($client, $_category['category_id'], $filters);

                $this->catalog_m->updateSorting($client, $categoryProducts);
                log_message('debug','== CatalogMage.sorting : '.$client['client_code'].'#'.$_category['category_id']);

            }
        }
        log_message('debug','[CatalogMage.sorting] end : '.date('Y-m-d H:i:s'));
    }
}