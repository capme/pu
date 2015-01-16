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
	const METHOD_PRODUCT_LIST = "catalog_product.list";
	const METHOD_PRODUCT_INFO = "catalog_product.info";
	const METHOD_CATLOGINVENTORY = "cataloginventory_stock_item.list";
	const METHOD_ATTRIBUTE_LIST = "product_attribute.list";
	const METHOD_ATTRIBUTE_INFO = "product_attribute.info";
	const METHOD_VELA_SHIPMENT = "vela_shipment.create";
	const METHOD_VELA_RETURN_NEW = "vela_return.new";
	const METHOD_VELA_RETURN_EXPORTED = "vela_return.exported";
	const METHOD_VELA_CONFIRMATION_NEW = "vela_confirmation.new";
	const METHOD_VELA_CONFIRMATION_EXPORTED = "vela_confirmation.exported";
	const METHOD_VELA_CONFIRMATION_APPROVE = "vela_confirmation.approve";
	const METHOD_VELA_COD_APPROVE = "vela_cod.verify";
	const METHOD_VELA_COD_CANCEL ="vela_cod.cancel";
	const METHOD_VELA_COD_RECEIVED = "vela_cod.payment";
	
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
	
	public function getCategoryList() {
		$catalogCategory = $this->soapClient->call($this->soapSession, self::METHOD_CATEGORY_TREE , 2);
		$catList = array();
		$this->_extractCategoryData(array($catalogCategory), $catList);
		
		return $catList;
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
}
