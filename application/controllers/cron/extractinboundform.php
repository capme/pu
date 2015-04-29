<?php 
/*
 * cron for extract xls uploaded doc into table inb_inventory_stock_<client_id>   
 */

class Extractinboundform extends CI_Controller {

	function __construct()
    {
        parent::__construct();
		$this->load->library('va_excel');
		$this->load->model( array("client_m", "inbounddocument_m","notification_m"));
		$this->load->helper('path');
    }
	
	public function run() {
		$path_file = $this->inbounddocument_m->pathInboundForm;
		$clients = $this->client_m->getClients();

		foreach($clients as $client) {
			$inbound = $this->inbounddocument_m->getInboundDocumentInfo($client["id"],2);
			if($inbound->num_rows>0){
				foreach($inbound->result_array() as $rows ){
					if(!empty($rows)){
						$id = $rows['id'];
						$doc_number = $rows['doc_number'];
						$client_id = $rows['client_id'];
						$note = $rows['note'];
						$type = $rows['type'];
						$status = $rows['status'];
						$created_at = $rows['created_at'];
						$updated_at = $rows['updated_at'];
						$created_by = $rows['created_by'];
						$filename = $rows['filename'];
						$reference_id = $rows['reference_id'];
						
						if($status == "0"){
                            try {
                                $objPHPExcel = PHPExcel_IOFactory::load($path_file."/".$filename);
                            } catch (Exception $e) {
                                // Use PCLZip rather than ZipArchive to read the Excel2007 OfficeOpenXML file
                                PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
                                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                                $objReader->setReadDataOnly(true);
                                $objPHPExcel = $objReader->load($path_file."/".$filename);
                            }
							
							$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
							
							foreach ($cell_collection as $cell) {
							    $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
							    $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
							    $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
							
						        $arr_data[$row][$column] = $data_value;
							}
							
							try {
								//$this->inbounddocument_m->changeStatusExtract();
								$doc_number = $id;
								$return = $this->inbounddocument_m->saveToInboundInventoryStock($client_id, $doc_number, $created_by, $arr_data, $reference_id);
								echo "import inbound form for client ".$client_id." doc number ".$doc_number."<br>";
								//updating inbound form record into 1
								$return = $this->inbounddocument_m->updateStatusInboundDocumentList($id,1);
								//updating inbound document record into 3
								$return = $this->inbounddocument_m->updateStatusInboundDocumentList($reference_id,3);
		                        
		                        $from=2;	
		                        $to=1;
		                        $url="listinbounddoc";
		                        $message="Inbound form was imported";
		                        $this->notification_m->add($from, $to, $url, $message);
		                        
							} catch( Exception $e ) {
								$this->inbounddocument_m->changeStatusFormInbounding();
								echo $e->getMessage();	
							}
							
						}					
					}
				}
			}else{
				echo "no available inbound form need to imported for client ".$client["id"]."<br>";
			}
		}
	}
}
?>