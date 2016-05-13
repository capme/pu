<?php
/**
 * Library for magento API with v1 SOAP
 * @author Ferry Ardhana <tech@velaasia.com>
 *
 */
class Mageapi {
	private $mageUrl = null;
	private $mageUser = null;
	private $magePass = null;
	private $soapClient = null;
	private $soapSession = null;
	
	const METHOD_INVOICE_LIST = "sales_order_invoice.list";
	const METHOD_INVOICE_INFO = "sales_order_invoice.info";
	const METHOD_ORDER_LIST = "sales_order.list";
	const METHOD_ORDER_INFO = "sales_order.info";
	const METHOD_CREDITMEMO_LIST = "sales_order_creditmemo.list";
	const METHOD_CREDITMEMO_INFO = "sales_order_creditmemo.info";
	const METHOD_CATEGORY_TREE = "catalog_category.tree";
    const METHOD_CATEGORY_INFO = "catalog_category.info";
    const METHOD_CATEGORY_ASSIGNED_PRODUCTS = "catalog_category.assignedProducts";
    const METHOD_CATEGORY_UPDATE_PRODUCT = "catalog_category.updateProduct";
    const METHOD_STORE_LIST = "store.list";
	const METHOD_PRODUCT_LIST = "catalog_product.list";
	const METHOD_PRODUCT_INFO = "catalog_product.info";
	const METHOD_CATLOGINVENTORY = "cataloginventory_stock_item.list";
	const METHOD_ATTRIBUTE_LIST = "product_attribute.list";
	const METHOD_ATTRIBUTE_INFO = "product_attribute.info";
	const METHOD_PRODUCT_CREATE = "vela_baymax_catalog.importItem";
	const METHOD_VELA_SHIPMENT = "vela_shipment.create";
	const METHOD_VELA_RETURN_NEW = "vela_return.new";
	const METHOD_VELA_RETURN_EXPORTED = "vela_return.exported";
	const METHOD_VELA_CONFIRMATION_NEW = "vela_confirmation.new";
	const METHOD_VELA_CONFIRMATION_EXPORTED = "vela_confirmation.exported";
	const METHOD_VELA_CONFIRMATION_APPROVE = "vela_confirmation.approve";
    const METHOD_VELA_CONFIRMATION_CANCEL = "vela_confirmation.cancel";
	const METHOD_VELA_COD_APPROVE = "vela_cod.verify";
	const METHOD_VELA_COD_CANCEL ="vela_cod.cancel";
	const METHOD_VELA_COD_RECEIVED = "vela_cod.payment";
	const METHOD_VELA_COD_NEW = "vela_cod.new";
	const METHOD_VELA_COD_EXPORTED = "vela_cod.exported";
	const METHOD_VELA_BAYMAX_CREDIT_CARD = "vela_baymax_creditcard.fetch";
	const METHOD_VELA_BAYMAX_PAYPAL = "vela_baymax_paypal.fetch";
	const METHOD_VELA_BAYMAX_PAYPAL_APPROVE="vela_baymax_paypal.approve";
    const METHOD_ATTRIBUTE_SET_LIST = "product_attribute_set.list";
    const METHOD_VELA_BAYMAX_BBM_MONEY = "vela_baymax_bbmmoney.fetch";

	public function __construct( $config = array() ) {
		if(!empty($config)) {
			$this->initSoap($config);
			
			return $this;
		}
		
		
		return $this;
	}
	
	public function initSoap( $config ) {
		$this->mageUrl = $config['url'];
		list($this->mageUser, $this->magePass) = explode(":", $config['auth']);
		
		try{
			$this->soapClient = new SoapClient($this->mageUrl);
			$this->soapSession = $this->soapClient->login($this->mageUser, $this->magePass);

			return $this;
		} catch (Exception $e) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function createOrderShipment($shipmentDetail) {
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_SHIPMENT, $shipmentDetail);
				
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getOrderList($status, $createdRange) {
		/* $filter = array('complex_filter'=> 
				array(
					array('key'=>'created_at', 'value' => array('key' =>'from', 'value' => $createdRange['from'])),
					array('key'=>'created_at', 'value' => array('key' =>'to', 'value' => $createdRange['to'])),
					array('key'=>'status', 'value' => array('key' => 'in', 'value' => $status)),
		)); */
		
		$filter = array(
			array(
				"created_at" => array("from" => $createdRange['from'], "to" => $createdRange['to'])
			)
		);
		
		$data = array();
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_INVOICE_LIST, $filter);
			
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getCreditMemo($createdRange) {
		$filter = array(
			array(
				"created_at" => array("from" => $createdRange['from'], "to" => $createdRange['to'])
			)
		);
		
		$data = array();
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_CREDITMEMO_LIST, $filter);
				
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getCreditMemoDetail($creditMemo, $source) {
		$calls = array();
		
		foreach($creditMemo as $cMemo) {
			$calls[] = array(self::METHOD_CREDITMEMO_INFO, $cMemo['increment_id'] );
		}
		
		log_message("debug", "calls credit memo info: " . print_r($calls, true));
		
		$data = array();
		try {
			$data = $this->soapClient->multiCall($this->soapSession, $calls);
		
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getOrderInfo($invoiceDatas = array()) {
		$data = array();
		$callsInvoice = array();
		foreach($invoiceDatas as $invoiceData) {
			//$callsInvoice[] = array(self::METHOD_INVOICE_INFO, $invoiceData['invoice_id']);
			$callsOrder[] = array(self::METHOD_ORDER_LIST, array(array("order_id" => $invoiceData['order_id'], "status" => array("in" => array("closed", "complete")) )) );
			
		}
		try {
			$dataOrder = $this->soapClient->multiCall($this->soapSession, $callsOrder);
			
			$callsOrderInfo = array();
			$completeOrder = array();
			foreach($dataOrder as $orderList) {
				if(!empty($orderList)) {
					$completeOrder[] = $orderList[0];
					$callsOrderInfo[] = array(self::METHOD_ORDER_INFO, $orderList[0]['increment_id'] );
				}
			}
			
			$dataOrderInfo = $this->soapClient->multiCall($this->soapSession, $callsOrderInfo);
			
			return array("info" => $dataOrderInfo, "order" => $completeOrder);
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getOrderAmount($orders = array()) {
		$data = array();
	
		try {
			foreach($orders as $order) {
				$callsOrder[] = array(self::METHOD_ORDER_LIST, array(array("increment_id" => $order['ordernr'])) );
			}
				
			$dataOrder = $this->soapClient->multiCall($this->soapSession, $callsOrder);
				
			foreach($dataOrder as $orderList) {
				if(!empty($orderList)) {
					$data[] = array("ordernr" => $orderList[0]["increment_id"], "amount" => $orderList[0]["grand_total"]);
				}
			}
				
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getOrderItems($orders = array()) {
		try {
			foreach($orders as $order) {
				if(!empty($order)) {
					$callsOrderInfo[] = array(self::METHOD_ORDER_INFO, $order['ordernr'] );
				}
			}
				
			$dataOrderInfo = $this->soapClient->multiCall($this->soapSession, $callsOrderInfo);
			return array("info" => $dataOrderInfo);
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
			
	}
	
	public function getCategoryList() {
//		$catalogCategory = $this->soapClient->call($this->soapSession, self::METHOD_CATEGORY_TREE , 2);
        $catalogCategory = $this->soapClient->call($this->soapSession, self::METHOD_CATEGORY_TREE);

		$catList = array();
		$this->_extractCategoryData(array($catalogCategory), $catList);
		
		return $catList;
	}

    public function getDetailCategory(){
        $categories = $this->getCategoryList();
        $detailCategory = array();
        foreach($categories as $_id => $_name) {
            try{
                $detail = $this->soapClient->call($this->soapSession, self::METHOD_CATEGORY_INFO, $_id);
                $detailCategory[] = $detail;
            } catch( Exception $e ) {
                log_message('error', "getDetailCategory(".$_name.") MAGEAPI ==> ". $e->getMessage()." ### ".self::METHOD_CATEGORY_INFO);
            }
        }
        return $detailCategory;
    }

    /**
     * getCategoryProduct : get list of 'catalog_category.assignedProduct'
     * @param null $store
     * @param null $category
     * @return array
     */
    public function getCategoryProduct($store=null, $category=null){

        if($store){
            $stores[]['store_id'] = $store;
        } else {
            $stores = $this->soapClient->call($this->soapSession, self::METHOD_STORE_LIST);
        }

        if($category){
            $categories[$category] = '';
        } else {
            $categories = $this->getCategoryList();
        }

        $listProduct = array();

        foreach( $categories as $_id => $_name){
            foreach($stores as $store){
//                print "category : @$_id , # store : ".@$store['store_id'].",".@$store['name']." \n";

                try{
                    $product= $this->soapClient->call($this->soapSession, self::METHOD_CATEGORY_ASSIGNED_PRODUCTS, array($_id,$store['store_id']));
                    $listProduct[$_id][$store['store_id']] = $product;
                    log_message('debug', "getCategoryProduct (".$_id.",".$store['store_id'].") success ==> ". count($product)." ### ".self::METHOD_CATEGORY_ASSIGNED_PRODUCTS);
                } catch( Exception $e ) {
                    log_message('error', "getCategoryProduct (".$_id.",".$store['store_id'].") MAGEAPI ==> ". $e->getMessage()." ### ".self::METHOD_CATEGORY_ASSIGNED_PRODUCTS);
                }
            }
        }
        return $listProduct;
    }

    /**
     * updateCategoryProductPosition : buat update default sorting position catalog category product
     * note : on magento configuration # catalog >  Product Listing Sort by > best value
     * @param $categoryId
     * @param $productId
     * @param $position
     * @return bool
     */
    public function updateCategoryProductPosition($categoryId, $productId, $position){
        try{
            $this->soapClient->call($this->soapSession, self::METHOD_CATEGORY_UPDATE_PRODUCT, array('categoryId'=>$categoryId, 'product'=>$productId, 'position'=>$position));
            log_message('debug', "updateCategoryProductPosition (".$categoryId.",".$productId.",".$position.") ");

            return true;
        } catch( Exception $e ) {
            log_message('error', "updateCategoryProductPosition (".$categoryId.",".$productId.",".$position.") MAGEAPI ==> ". $e->getMessage()." ### ".self::METHOD_CATEGORY_UPDATE_PRODUCT);

            return false;
        }
    }

    /**
     * bulkUpdateCategoryProductPosition : update position using multicall
     * @param array $datas
     * @return array|bool
     */
    public function bulkUpdateCategoryProductPosition($datas = array()) {
        try {
            $newPosition = array();
            $sum = array('success'=>0, 'error'=>0);
            foreach($datas as $_data) {
                if(!empty($_data)) {
                    $newPosition[] = array(self::METHOD_CATEGORY_UPDATE_PRODUCT,array('categoryId'=>$_data['category_id'],'product'=>$_data['product_id'],'position'=>$_data['result_index']) );
                }
            }

            $updatePosition = $this->soapClient->multiCall($this->soapSession, $newPosition);

            foreach($newPosition as $k => $data){
                $newPosition[$k]['mage_result'] = $updatePosition[$k];
                if(!is_array($updatePosition[$k]) && $updatePosition[$k] == 1) $sum['success']++;
                else $sum['error'];
            }
            log_message('debug','Mageapi.updateCategoryProductPosition bulk result : '.print_r($newPosition,true));

            return array('data'=>$newPosition,'success'=>$sum['success'],'problem'=>$sum['error']);
        } catch( Exception $e ) {
            log_message('error', "updateCategoryProductPosition bulk (".count($datas).") MAGEAPI ==> ". $e->getMessage()." ### ".self::METHOD_CATEGORY_UPDATE_PRODUCT);

            return false;
        }

    }

	public function getCatalog() {
		$productList = $this->soapClient->call($this->soapSession, self::METHOD_PRODUCT_LIST);
		$productsInfo = array();
		
		// get all product info
		foreach($productList as $product) {
			$productsInfo[] = array(self::METHOD_PRODUCT_INFO, $product['sku']);
			$productsInfo[] = array(self::METHOD_CATLOGINVENTORY, $product['sku']);
		}
		
		$productsInfo = $this->soapClient->multiCall($this->soapSession, $productsInfo);
		
		$productsSet = array();
		foreach($productsInfo as $x => $pInfo) {
			if($x % 2 == 1) {
				continue;
			}
			
			if(!array_key_exists($pInfo['set'], $productsSet)) {
				$productsSet[$pInfo['set']] = array(self::METHOD_ATTRIBUTE_LIST, $pInfo['set']);
			}
		}
		
		// get used attribute set
		$data = $this->soapClient->multiCall($this->soapSession, $productsSet);
		$aLists = array();
		
		foreach($data as $key => $attributes) {
			//$attributeOfSet[$aSetLists[$key]] = $attributes;
			foreach($attributes as $attr) {
				if(!array_key_exists($attr['attribute_id'], $aLists)) {
					$aLists[$attr['attribute_id']] = array(self::METHOD_ATTRIBUTE_INFO, $attr['attribute_id']);				
				}
			}
		}
		
		// get used attribute
		$data = $this->soapClient->multiCall($this->soapSession, $aLists);
		
		return array("pInfo" => $productsInfo, "attribute" => $data);
	}
	
	public function getUnexportedReturnItem() {
		$data = array();
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_RETURN_NEW);
		
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function setReturnItemAsExported($ids) {
		try {
			$calls = array();
			foreach($ids as $id) {
				$calls[] = array(self::METHOD_VELA_RETURN_EXPORTED, $id );
			}
				
			$this->soapClient->multiCall($this->soapSession, $calls);
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function getUnexportedConfirmations() {
		$data = array();
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_CONFIRMATION_NEW);
	
			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}

	public function setConfirmationsAsExported($ids) {
		try {
			$calls = array();
			foreach($ids as $id) {
				$calls[] = array(self::METHOD_VELA_CONFIRMATION_EXPORTED, $id );
			}
	
			$this->soapClient->multiCall($this->soapSession, $calls);
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}

    public function sendNotifBrand($client, $orderNr, $paymentType){
        try {
            //get order info
            $CI =& get_instance();
            $CI->load->model("inbounddocument_m");
            $CI->load->model("clientoptions_m");
            $CI->load->model("notification_m");
            $calls = array(array(self::METHOD_ORDER_INFO, $orderNr ));
            $listOrderInfo = $this->soapClient->multiCall($this->soapSession, $calls);

            $listOrderInfoItems = $listOrderInfo[0]['items'];
            $arrListBrandCode = array();
            foreach($listOrderInfoItems as $_listOrderInfoItems){
                if($_listOrderInfoItems['product_type'] == "simple"){
                    $sku = $_listOrderInfoItems['sku'];
                    $dataInvItems = $CI->inbounddocument_m->getInvItems($client, $sku);
                    $temp = explode(",", $dataInvItems[0]['sku_description']);
                    $arrListBrandCode[$temp[0]][] = $sku;
                }
            }
            $dataClientOptionsInboundType = $CI->clientoptions_m->gets($client, "inbound_type");
            $dataClientOptionsBrandName = $CI->clientoptions_m->gets($client, "brand_code");
            $msgNotif = "Order : ".$orderNr."<br>";
            foreach($arrListBrandCode as $key => $item){
                if(isset($dataClientOptionsInboundType['inbound_type'][$key])){
                    $msgNotif .= "<br>";
                    $msgNotif .= "Brand name : ".$dataClientOptionsBrandName['brand_code'][$key]."<br>";
                    $msgNotif .= "Inbound Type : ".$dataClientOptionsInboundType['inbound_type'][$key]."<br>";
                    $msgNotif .= "List SKU : <br>";
                    foreach($item as $subItem){
                        $msgNotif .= "- ".$subItem."<br>";
                    }
                }
            }
            $from=2;
            $to=1;
            if($paymentType == "banktransfer"){
                $url="paymentconfirmation";
            }elseif($paymentType == "cod"){
                $url="codpaymentconfirmation";
            }elseif($paymentType == "cc"){
                $url="creditcardorder";
            }elseif($paymentType == "bbm"){
                $url="bbmmoneyorder";
            }
            $message=$msgNotif;
            $CI->notification_m->add($from, $to, $url, $message);
            //print_r($arrListBrandCode);die();
            return true;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI ==> ". $e->getMessage());
            return false;
        }
    }

	public function processOrder($orderNr) {
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_CONFIRMATION_APPROVE, $orderNr);
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	private function _extractCategoryData($cat, &$catList) {
		foreach($cat as $d) {
			$catList[$d['category_id']] = $d['name'];
			if(!empty($d['children'])) {
				$this->_extractCategoryData($d['children'], $catList);
			}
		}
	}
	
	public function setOrderToVerified($ordernr, $comment){
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_COD_APPROVE, array('orderincrementId'=>$ordernr, 'comment'=>$comment));
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function setOrderToCancel($ordernr, $comment){
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_COD_CANCEL, array('orderincrementId'=>$ordernr, 'comment'=>$comment));
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
	
	public function setOrderToReceived($ordernr){
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_COD_RECEIVED, array('orderincrementId'=>$ordernr));
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}

	public function getUnexportedCodOrder() {
		$data = array();
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_COD_NEW);

			return $data;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}

	public function setCodOrderAsExported($ids) {
		try {
			$calls = array();
			foreach($ids as $id) {
				$calls[] = array(self::METHOD_VELA_COD_EXPORTED, $id );
			}

			$this->soapClient->multiCall($this->soapSession, $calls);
			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}

    public function cancelCod($orderNr, $comment){
        try {
            $this->soapClient->call($this->soapSession, self::METHOD_VELA_COD_CANCEL, array('order_id'=>$orderNr, 'comment'=>$comment));
            return true;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI ==> ". $e->getMessage());
            return false;
        }
    }

    public function cancelPayment($orderNr, $comment){
        try {
            $this->soapClient->call($this->soapSession, self::METHOD_VELA_CONFIRMATION_CANCEL, array('order_id'=>$orderNr, 'comment'=>$comment));
            return true;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI ==> ". $e->getMessage());
            return false;
        }
    }
    
    public function getCreditCardOrder($fromDate, $toDate){
        try {
            $rangeDate = array($fromDate, $toDate);
            $data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_BAYMAX_CREDIT_CARD, $rangeDate);

            return $data;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI creditcard ==> ". $e->getMessage());
            return false;
        }
    }

    public function getBbmMoneyOrder($fromDate, $toDate){
        try {
            $rangeDate = array($fromDate, $toDate);
            $data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_BAYMAX_BBM_MONEY, $rangeDate);

            return $data;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI creditcard ==> ". $e->getMessage());
            return false;
        }
    }

    public function getPaypalOrder($fromDate, $toDate){
        try {
            $rangeDate = array($fromDate, $toDate);
            $data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_BAYMAX_PAYPAL, $rangeDate);

            return $data;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI paypal ==> ". $e->getMessage());
            return false;
        }
    }
		
	/**
	 * create mage's items for each client
	 * @param array $ItemsToCreate
	 * @return array created items
	 */
	public function inboundCreateItem($params){
		try {
			$calls = array();

            foreach($params as $param) {
                log_message('debug', print_r($param, true));
				$calls[] = array(self::METHOD_PRODUCT_CREATE, array($param));
			}
			$returns = $this->soapClient->multiCall($this->soapSession, $calls);

			return $returns;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
		
	}

    public function getProductAttributeSet(){

        try {
            $attributeSet = array();
            $data = $this->soapClient->call($this->soapSession, self::METHOD_ATTRIBUTE_SET_LIST);

            if(!empty($data)){
                foreach($data as $d){
                    if(isset($d['set_id']) && isset($d['name'])){
                        $attributeSet[$d['set_id']] = $d['name'];
                    }
                }
            }

            return $attributeSet;
        } catch( Exception $e ) {
            log_message('error', "MAGEAPI ==> ". $e->getMessage());
            return false;
        }
    }
	
	public function paypalApprove($orderNr){
		try {
			$data = $this->soapClient->call($this->soapSession, self::METHOD_VELA_BAYMAX_PAYPAL_APPROVE, $orderNr);

			return true;
		} catch( Exception $e ) {
			log_message('error', "MAGEAPI ==> ". $e->getMessage());
			return false;
		}
	}
    
}
