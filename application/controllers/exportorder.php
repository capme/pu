<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Exportorder extends MY_Controller {
    var $data = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model("exportorder_m");
        $this->load->model("client_m");
        $this->load->library('va_excel');
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Export Order";
        $this->data['breadcrumb'] = array("Export Order" => "exportorder");

        $this->exportorder_m->clearCurrentFilter();
        $this->load->library("va_list");
        $this->va_list->setListName("Export Order")->disableAddPlugin()
            ->setHeadingTitle(array("Record #", "Client Name"))
            ->setHeadingWidth(array(10,10));

        $this->va_list->setDropdownFilter(1, array("name" => $this->exportorder_m->filters['id'], "option" => $this->client_m->getClientCodeList(TRUE)));

        $this->data['script'] = $this->load->view("script/sortingtool_list", array("ajaxSource" => site_url("exportorder/clientOrderList")), true);
        $this->load->view("template", $this->data);
    }

    public function clientOrderList(){
        $data = $this->exportorder_m->getClient();
        echo json_encode($data);
    }

    public function export($client){
        $this->data['content'] = "form_v.php";
        $this->data['pageTitle'] = "Period Export Order";
        $this->data['breadcrumb'] = array("Export Order"=> "exportorder", "Period Export Order" => "");
        $this->data['formTitle'] = "Period Export Order";

        $this->load->library("va_input", array("group" => "exportorder"));
        $value=array();
        $clientname=$this->client_m->getClientById($client)->row_array();

        $this->va_input->addHidden( array("name" => "method", "value" => "update"));
        $this->va_input->addHidden( array("name" => "client_id", "value" => $client));
        $this->va_input->addInput( array("name" => "client_code", "placeholder" => "Client name", "help" => "Client Name", "label" => "Client Name", "value"=>$clientname['client_code'],"disabled"=>"disabled"));
        $this->va_input->addCustomField( array("name" =>"options", "placeholder" => "Input Period", "label" => "Input Period", "value" =>$value, "view"=>"form/customPeriod"));
        $this->data['script'] = $this->load->view("script/client_add", array(), true);
        $this->load->view('template', $this->data);
    }

    private function header(){
        $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
        $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
        $this->va_excel->getActiveSheet()->freezePane('K3');
        $this->va_excel->getActiveSheet()->mergeCells('A1:C1');

        $this->va_excel->getActiveSheet()->setCellValue('A2', 'No')->getColumnDimension('A')->setWidth(4);
        $this->va_excel->getActiveSheet()->setCellValue('B2', 'Order Number')->getColumnDimension('B')->setWidth(17);
        $this->va_excel->getActiveSheet()->setCellValue('C2', 'Grand Total')->getColumnDimension('C')->setWidth(17);
        $this->va_excel->getActiveSheet()->setCellValue('D2', 'Total Items')->getColumnDimension('D')->setWidth(12);
        $this->va_excel->getActiveSheet()->setCellValue('E2', 'Status')->getColumnDimension('E')->setWidth(10);
        $this->va_excel->getActiveSheet()->setCellValue('F2', 'Order date')->getColumnDimension('F')->setWidth(18);
        $this->va_excel->getActiveSheet()->setCellValue('G2', 'SKU')->getColumnDimension('G')->setWidth(20);
        $this->va_excel->getActiveSheet()->setCellValue('H2', 'SKU Description')->getColumnDimension('H')->setWidth(30);
        $this->va_excel->getActiveSheet()->setCellValue('I2', 'Approved Date')->getColumnDimension('I')->setWidth(18);
        $this->va_excel->getActiveSheet()->setCellValue('J2', 'Approved By')->getColumnDimension('J')->setWidth(18);
    }

    public function save(){
        $post = $this->input->post("exportorder");
        $result = $this->exportorder_m->getData($post['client_id'], $post['period1'], $post['period2']);

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font'  => array(
                'size'  => 10,
                'name'  => 'Verdana'
            ));

        if(!empty ($result[0]) || !empty($result[1]) || !empty( $result[2]) || !empty( $result[3])) {
            $this->va_excel->setActiveSheetIndex(0);
            $this->va_excel->getActiveSheet()->setTitle('Bank Transfer Order');

            $this->header();
            $lup = 3;
            $no=1;

            if (!empty($result[0])) {
                $statList = array(
                    0 => array("New Request", "warning"),
                    1 => array("Approve", "success"),
                    2 => array("Cancel", "danger")
                );

                foreach ($result[0] as $item) {
                    $status = $statList[$item['status']][0];
                    $items = json_decode($item['items']);

                    if (!empty($items)) {
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]->qty);
                        }
                    }else if (unserialize($item['items']) !== false){
                        $items = unserialize($item['items']);
                        for ($i = 0; $i < count($items); $i++) {
                            if(count($items) > 1){
                                $lup =$lup+$i;
                                $sku=trim($items[$i]['name']);
                                $skuDesc = $this->exportorder_m->getDescription($item['client_id'], $sku);
                                $this->va_excel->getActiveSheet()->setCellValue('G' . $lup, $sku)->getDefaultStyle()->applyFromArray($style);
                                $this->va_excel->getActiveSheet()->setCellValue('H' . $lup, $skuDesc['sku_description'])->getDefaultStyle()->applyFromArray($style);
                            }else{
                                $sku=trim($items[$i]['name']);
                                $skuDesc = $this->exportorder_m->getDescription($item['client_id'], $sku);
                            }
                        }

                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]['qty']);
                        }
                    }else {
                        $sum=0;
                    }

                    $this->va_excel->getActiveSheet()->setCellValue('A' . $lup, $no++)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('B' . $lup, $item['order_number'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('C' . $lup, $item['amount'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('D' . $lup, $sum)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('E' . $lup, $status)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('F' . $lup, $item['created_at'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('G' . $lup, $sku)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('H' . $lup, $skuDesc['sku_description'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('I' . $lup, $item['updated_at'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('J' . $lup, $item['fullname'])->getDefaultStyle()->applyFromArray($style);
                    $lup++;
                }
                $this->va_excel->getActiveSheet()->setCellValue('A1', 'Bank Transfer Order ' . $item['client_code'] . '');
            }

            $this->va_excel->createSheet();
            $this->va_excel->setActiveSheetIndex(1);
            $this->va_excel->getActiveSheet()->setTitle('COD Order');

            $this->header();

            if (!empty($result[1])) {
                $statList = array(
                    0 => array("New Request", "warning"),
                    1 => array("Approve", "success"),
                    2 => array("Order Cancel", "danger"),
                    3 => array("Received", "success"),
                    4 => array("Cancel", "danger")
                );

                foreach ($result[1] as $item) {
                    $status = $statList[$item['status']][0];
                    $items = json_decode($item['items']);
                    if (!empty($items)) {
                        for ($i = 0; $i < count($items); $i++) {
                            if(count($items > 1)){
                                $lup =$lup+$i;
                                $exSku = explode('(',$items[$i]->item);
                                $sku=trim(rtrim($exSku[1],')'));
                                $skuDesc = $this->exportorder_m->getDescription($item['client_id'], $sku);
                                $this->va_excel->getActiveSheet()->setCellValue('G' . $lup, $sku)->getDefaultStyle()->applyFromArray($style);
                                $this->va_excel->getActiveSheet()->setCellValue('H' . $lup, $skuDesc['sku_description'])->getDefaultStyle()->applyFromArray($style);
                            }else{
                                $exSku = explode('(',$items[$i]->item);
                                $sku=trim(rtrim($exSku[1],')'));
                                $skuDesc = $this->exportorder_m->getDescription($item['client_id'], $sku);
                            }
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]->qty);
                        }
                    } else {
                        $items = unserialize($item['items']);
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]['qty']);
                        }
                    }

                    $this->va_excel->getActiveSheet()->setCellValue('A' . $lup, $no++)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('B' . $lup, $item['order_number'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('C' . $lup, $item['amount'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('D' . $lup, $sum)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('E' . $lup, $status)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('F' . $lup, $item['created_at'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('G' . $lup, $sku)->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('H' . $lup, $skuDesc['sku_description'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('I' . $lup, $item['updated_at'])->getDefaultStyle()->applyFromArray($style);
                    $this->va_excel->getActiveSheet()->setCellValue('J' . $lup, $item['fullname'])->getDefaultStyle()->applyFromArray($style);
                    $lup++;
                }
                $this->va_excel->getActiveSheet()->setCellValue('A1', 'COD Order ' . $item['client_code'] . '');
            }

            $this->va_excel->createSheet();
            $this->va_excel->setActiveSheetIndex(2);
            $this->va_excel->getActiveSheet()->setTitle('Paypal Order');
            $this->header();

            if (!empty($result[2])) {
                $statList = array(
                    0 => array("Pending Payment"),
                    1 => array("Processing"),
                    2 => array("Complete"),
                    3 => array("Fraud"),
                    4 => array("Payment_Review"),
                    5 => array("Canceled"),
                    6 => array("Closed"),
                    7 => array("Waiting_payment")
                );

                $lup = 3;
                foreach ($result[2] as $item) {
                    $status = $statList[$item['status']][0];
                    $items = json_decode($item['items']);
                    if (!empty($items)) {
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]->qty);
                        }
                    } else {
                        $items = unserialize($item['items']);
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]['qty']);
                        }
                    }
                    $this->va_excel->getActiveSheet()->mergeCells('E' . $lup . ':F' . $lup . '');
                    $this->va_excel->getActiveSheet()->setCellValue('A' . $lup, $item['order_number']);
                    $this->va_excel->getActiveSheet()->setCellValue('B' . $lup, $item['amount']);
                    $this->va_excel->getActiveSheet()->setCellValue('C' . $lup, $sum);
                    $this->va_excel->getActiveSheet()->setCellValue('D' . $lup, $status);
                    $this->va_excel->getActiveSheet()->setCellValue('E' . $lup, $item['created_at']);
                    $lup++;
                }
                $this->va_excel->getActiveSheet()->setCellValue('A1', 'Paypal Order ' . $item['client_code'] . '');
            }

            $this->va_excel->createSheet();
            $this->va_excel->setActiveSheetIndex(3);

            $this->va_excel->getActiveSheet()->setTitle('Credit Card Order');
            $this->header();

            if (!empty($result[3])) {
                $statList = array(
                    0 => array("Pending", "info"),
                    1 => array("Processing", "success"),
                    2 => array("Complete", "primary"),
                    3 => array("Fraud", "default"),
                    4 => array("Payment_Review", "warning"),
                    5 => array("Canceled", "danger"),
                    6 => array("Closed", "danger"),
                    7 => array("Waiting_payment", "info")
                );

                $lup = 3;
                foreach ($result[3] as $item) {
                    $status = $statList[$item['status']][0];
                    $items = json_decode($item['items']);
                    if (!empty($items)) {
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]->qty);
                        }
                    } else {
                        $items = unserialize($item['items']);
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]['qty']);
                        }
                    }
                    $this->va_excel->getActiveSheet()->mergeCells('E' . $lup . ':F' . $lup . '');
                    $this->va_excel->getActiveSheet()->setCellValue('A' . $lup, $item['order_number']);
                    $this->va_excel->getActiveSheet()->setCellValue('B' . $lup, $item['amount']);
                    $this->va_excel->getActiveSheet()->setCellValue('C' . $lup, $sum);
                    $this->va_excel->getActiveSheet()->setCellValue('D' . $lup, $status);
                    $this->va_excel->getActiveSheet()->setCellValue('E' . $lup, $item['created_at']);
                    $lup++;
                }
                $this->va_excel->getActiveSheet()->setCellValue('A1', 'Credit Card Order ' . $item['client_code'] . '');
            }
            $filename = 'Export Order Order ' . $item['client_code'] . '.xls';
        }
        else{
            $filename='Export Order.xls';
            $this->va_excel->getActiveSheet()->setCellValue('A1','Data Not Found');
        }

        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Content-Type: application/vnd.ms-excel');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->va_excel, 'Excel5');
        $objWriter->save('php://output');
    }
}