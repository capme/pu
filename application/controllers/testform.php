<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Testform extends MY_Controller {
	var $data = array();
	public function __construct()
	{
		parent::__construct();
	}

	public function index() 
	{
		
		$this->_page1();		

	}
	
	private function _page1(){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "test ajah form custom";
		$this->data['formTitle'] = "test ajah form custom";
		$this->data['breadcrumb'] = array("Test Form"=> "", "View Test Form" => "");
		$this->load->library("va_input", array("group" => "testform"));
		$arrayObject =
			array(
				0 => array(
						"objectname" => "div",
						"id" => "div1",
						"class" => "panel panel-default",
						"style" => "width:50%",
						"sub" =>
								array(
									0 => array(
												"objectname" => "table",
												"id" => "tab1e1",
												"class" => "table",
												"sub" => 
													array(
														0 => array(
																"objectname" => "tr",
																"id" => "tr11",
																"sub" => 
																		array(
																			0 => array(
																					"objectname" => "td",
																					"setText" => "ITEM (SKU)"
																				),
																			1 => array(
																					"objectname" => "td",
																					"setText" => "QTY"
																				)
																		
																		)
															),
														1 => array(
																"objectname" => "tr",
																"id" => "tr2",
																"sub" => 
																		array(
																			0 => array(
																					"objectname" => "td",
																					"setText" => "data item sku 1"
																				),
																			1 => array(
																					"objectname" => "td",
																					"setText" => "data qty 1"
																				)
																		
																		)
															)															
													)
											
										)
								)
					)
			
			);

		$this->va_input->addCustomForm( $arrayObject );
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("1"=>"Processing","3" => "Receive","4"=>"Cancel"), "value" => "isi value", "msg" => "isi msg"));	
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
	}
	
	private function _page(){
		$this->data['content'] = "form_v.php";
		$this->data['pageTitle'] = "test ajah form custom";
		$this->data['formTitle'] = "test ajah form custom";
		$this->data['breadcrumb'] = array("AWB Printing"=> "", "View AWB Printing" => "");
		
		$this->load->library("va_input", array("group" => "returnorder"));
		//$this->va_input->setJustView();
		$this->va_input->setGroupedForm(TRUE)->setGroupName( array(0 => "Group 1", 1 => "Group 2") )->setActiveGroup(0);
				
		  
		$arrayObject =  
			array(			
				0 => array(
						"objectname" => "table",
						"id" => "table1",
						"sub" =>
						array(
							0 =>
								array(
									"objectname" => "tr",
									"id" => "tr1",
									"sub" =>
									array(
										0 =>
											array(
												"objectname" => "td",
												"id" => "td11",
												"sub" => 
												array(
													0 =>
														array(
															"objectname" => "input",
															"id" => "input11",
															"value" => "isi text 11"
														)
												)
											),
										1 =>
											array(
												"objectname" => "td",
												"id" => "td12",
												"sub" => 
												array(
													0 =>
														array(
															"objectname" => "input",
															"id" => "input12",
															"value" => "isi text 12"
														)
												)
											)
									)
								),
							1 =>
								array(
									"objectname" => "tr",
									"id" => "tr2",
									"sub" =>
									array(
										0 =>
											array(
												"objectname" => "td",
												"id" => "td21",
												"sub" => 
												array(
													0 =>
														array(
															"objectname" => "input",
															"id" => "input21",
															"value" => "isi text 21"
														)
												)
											),
											array(
												"objectname" => "td",
												"id" => "td22",
												"sub" => 
												array(
													0 =>
														array(
															"objectname" => "input",
															"id" => "input22",
															"value" => "isi text 22"
														)
												)
											)
									)
								),
							2 =>
								array(
									"objectname" => "tr",
									"id" => "tr2",
									"sub" =>
									array(
										0 =>
											array(
												"objectname" => "td",
												"id" => "td21",
												"colspan"=> "2",
												"align"=> "center",
												"sub" => 
												array(
													0 =>
														array(
															"objectname" => "input",
															"id" => "input21",
															"value" => "isi text 21"
														)
												)
											)
									)
								)
								
						)		
					)
			);
	
		$this->va_input->addCustomForm( $arrayObject );
		
		$this->va_input->commitForm(0);
		
		$arrayObject = array(
							0 => array(
								"objectname" => "span",
								"id" => "span1",
								"setText" => "isi text span1"
							)		
				);
		$this->va_input->addCustomForm( $arrayObject );
		$arrayObject = array(
							0 => array(
								"objectname" => "br"
							)		
				);
		$this->va_input->addCustomForm( $arrayObject );
		$arrayObject = array(
							0 => array(
								"objectname" => "span",
								"id" => "span2",
								"setText" => "isi text span2"
							)		
				);
		$this->va_input->addCustomForm( $arrayObject );
		$this->va_input->addSelect( array("name" => "status", "label" => "Status *", "list" => array("1"=>"Processing","3" => "Receive","4"=>"Cancel"), "value" => "isi value", "msg" => "isi msg"));	
				
		$this->va_input->commitForm(1);
		
		$this->data['script'] = $this->load->view("script/client_add", array(), true);
		$this->load->view('template', $this->data);
		
	}
	
}
?>