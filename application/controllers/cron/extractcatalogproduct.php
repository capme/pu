<?php 
/*
 * cron for extract xls uploaded doc into table inb_inventory_item(client_id)   
 */

class Extractcatalogproduct extends CI_Controller {

	function __construct()
    {
        parent::__construct();
		$this->load->library('va_excel');
		$this->load->model( array("client_m", "inbounddocument_m","notification_m"));
		$this->load->helper('path');
    }
	
	public function run() {
		$path_file = $this->inbounddocument_m->path;
		$clients = $this->client_m->getClients();
		
		foreach($clients as $client) {
			$inbound = $this->inbounddocument_m->getInboundDocumentInfo($client["id"]);
			if(!empty($inbound)){
				$id = $inbound['id'];
				$doc_number = $inbound['doc_number'];
				$client_id = $inbound['client_id'];
				$note = $inbound['note'];
				$type = $inbound['type'];
				$status = $inbound['status'];
				$created_at = $inbound['created_at'];
				$updated_at = $inbound['updated_at'];
				$created_by = $inbound['created_by'];
				$filename = $inbound['filename'];
				
				if($status == "0"){
					$objPHPExcel = PHPExcel_IOFactory::load($path_file."/".$filename);
					
					$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
					
					foreach ($cell_collection as $cell) {
					    $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
					    $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
					    $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
					
				        $arr_data[$row][$column] = $data_value;
					}
					
					try {
						$return = $this->inbounddocument_m->saveToInboundInventory($client_id, $doc_number, $created_by, $arr_data);
						echo "import inbound document for client ".$client_id."<br>";
						$return = $this->inbounddocument_m->updateStatusInboundDocumentList($id);
                        
                        $from=$this->session->userdata('pkUserId');	
                        $to=1;
                        $url="listinbounddoc";
                        $message="Inbound document was imported";
                        $this->notification_m->add($from, $to, $url, $message);
                        
					} catch( Exception $e ) {
						echo $e->getMessage();	
					}
					
				}					
			}else{
				echo "no available inbound document need to imported for client ".$client["id"]."<br>";
			}
		}
	}
}
?>