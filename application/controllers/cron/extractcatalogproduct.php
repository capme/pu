<?php 
/*
 * cron for extract xls uploaded doc into table inb_inventory_item(client_id)   
 */

/**
 * Class Extractcatalogproduct
 * @property Inbounddocument_m $inbounddocument_m
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
			$inbound = $this->inbounddocument_m->getInboundDocumentInfo($client["id"],1);
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
						
						if($status == "0"){					
							
                            $ext = explode('.', $filename);
                            if( end($ext) == 'xlsx' ){
                                // Use PCLZip rather than ZipArchive to read the Excel2007 OfficeOpenXML file
                                PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
                                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                                $objReader->setReadDataOnly(true);
                                $objPHPExcel = $objReader->load($path_file."/".$filename);
                            } else {
                                $objPHPExcel = PHPExcel_IOFactory::load($path_file."/".$filename);
                            }

							$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
							
							foreach ($cell_collection as $cell) {
							    $column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
							    $row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
							    $data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
							
						        $arr_data[$row][$column] = $data_value;
							}
							
							try {															
								$realDocNumber = $doc_number; 
								$doc_number = $id;
								$this->inbounddocument_m->changeStatusExtract($doc_number, 1);								
								$return = $this->inbounddocument_m->saveToInboundInventory($client_id, $doc_number, $created_by, $arr_data);								
								//compose HTML report
								if(isset($return['problem']) or isset($return['problemskuconfig'])){
									$this->inbounddocument_m->changeStatusPending($doc_number, 1);
									if(isset($return['problem'])){
										//list problems
										$client = $this->client_m->getClientById($client_id)->row_array();
										$clientCode = $client['client_code']; 
										$strProblem = "<table border='1' cellpadding='2' cellspacing='2'>";
										$strProblem .= "<tr><td colspan='3'>".$clientCode." (".$realDocNumber.")</td></tr>";
										$strProblem .= "<tr>";
	                                    $strProblem .= "<td>SKU Simple</td>";
										$strProblem .= "<td>Type in File</td>";
	                                    $strProblem .= "<td>Type in System</td>";
										$strProblem .= "</tr>";
	
										foreach($return['problem'] as $itemProblem){
											$strProblem .= "<tr>";
	                                        $strProblem .= "<td>";
	                                        $strProblem .= $itemProblem['sku_simple'];
	                                        $strProblem .= "</td>";
											$strProblem .= "<td>";
	                                        $strProblem .= $itemProblem['poTypeInFile'];
											$strProblem .= "</td>";
	                                        $strProblem .= "<td>";
	                                        $strProblem .= $itemProblem['poTypeInSys'];
	                                        $strProblem .= "</td>";
											$strProblem .= "</tr>";
										}
										$strProblem .= "</table>";
									
		                                $from = USER_CRON;
				                        $to = 'GROUP_MERCH';
		                                $url="inbounds";
				                        $message=$strProblem;
				                        $this->notification_m->add($from, $to, $url, $message);
				                        
				                        echo "PO type exception for client ".$client_id." doc number ".$doc_number."<br>";
									}
									
									if(isset($return['problemskuconfig'])){
										//list problems SKU Config
										$client = $this->client_m->getClientById($client_id)->row_array();
										$clientCode = $client['client_code']; 
										$strProblem = "<table border='1' cellpadding='2' cellspacing='2'>";
										$strProblem .= "<tr><td colspan='3'>".$clientCode." (".$realDocNumber.")</td></tr>";
										$strProblem .= "<tr>";
	                                    $strProblem .= "<td>Product Name</td>";
										$strProblem .= "<td>Color Name</td>";
	                                    $strProblem .= "<td>List Different SKU</td>";
										$strProblem .= "</tr>";
										
										foreach($return['problemskuconfig'] as $keyProblemSkuConfig => $itemProblemSkuConfig){
											$tmpKeyProblemSkuConfig = explode("-", $keyProblemSkuConfig);
												$itemProductName = $tmpKeyProblemSkuConfig[0];
												$itemColorName = $tmpKeyProblemSkuConfig[1];
											
											$strListSku = "<table cellpadding='2' cellspacing='2'>";
											foreach($itemProblemSkuConfig as $partItemProblemSkuConfig){
												$strListSku .= "<tr><td>".$partItemProblemSkuConfig."</td></tr>";
											}
											$strListSku .= "</table>";
											
											$strProblem .= "<tr>";
	                                        $strProblem .= "<td>";
	                                        $strProblem .= $itemProductName;
	                                        $strProblem .= "</td>";
											$strProblem .= "<td>";
	                                        $strProblem .= $itemColorName;
											$strProblem .= "</td>";
	                                        $strProblem .= "<td>";
	                                        $strProblem .= $strListSku;
	                                        $strProblem .= "</td>";
											$strProblem .= "</tr>";
										}
										$strProblem .= "</table>";
									
		                                $from = USER_CRON;
				                        $to =GROUP_MERCH;
		                                $url="inbounds";
				                        $message=$strProblem;
				                        $this->notification_m->add($from, $to, $url, $message);
				                        
				                        echo "SKU Config exception for client ".$client_id." doc number ".$doc_number."<br>";
									}
									
								} else {
									echo "import inbound document for client ".$client_id." doc number ".$doc_number."<br>";
                                    $return = $this->inbounddocument_m->updateStatusInboundDocumentList($id,1);

                                    $from = USER_CRON;
                                    $to = GROUP_OPERATION;
                                    foreach($inbound->result_array() as $rows ){
                                        $id = $rows['id'];
                                        $url="listinbounddoc/updateAttr?client=".$client_id."&doc=".$id."&id=".$id."";
                                        $message="Catalog product (".$rows['doc_number'].") was imported";
                                        $this->notification_m->add($from, $to, $url, $message);
                                    }
                                }
								

							} catch( Exception $e ) {								
								echo $e->getMessage();	
							}
							
						}
					}				
				}
			}else{				
				echo "no available inbound document need to imported for client ".$client["id"]."<br>";
			}

		}
	}
}
?>