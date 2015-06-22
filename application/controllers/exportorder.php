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

    public function save(){
        $post = $this->input->post("exportorder");
        $result = $this->exportorder_m->getData($post['client_id'], $post['period1'], $post['period2']);

        if(!empty ($result[0]) || !empty($result[1]) || !empty( $result[2]) || !empty( $result[3])) {
            $this->va_excel->setActiveSheetIndex(0);
            $this->va_excel->getActiveSheet()->setTitle('Bank Transfer Order');

            $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->freezePane('H3');
            $this->va_excel->getActiveSheet()->mergeCells('A1:C1');
            $this->va_excel->getActiveSheet()->mergeCells('E2:F2');
            $this->va_excel->getActiveSheet()->setCellValue('A2', 'Order Number')->getColumnDimension('A')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('B2', 'Grand Total')->getColumnDimension('B')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('C2', 'Total Items')->getColumnDimension('C')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('D2', 'Status')->getColumnDimension('C')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('E2', 'Order date')->getColumnDimension('C')->setWidth(10);

            if (!empty($result[0])) {
                $statList = array(
                    0 => array("New Request", "warning"),
                    1 => array("Complete", "success"),
                    2 => array("Cancel", "danger")
                );
                $lup = 3;
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
                    }
                    else if (unserialize($item['items']) !== false)
                    {
                        $items = unserialize($item['items']);
                        for ($i = 0; $i < count($items); $i++) {
                        }
                        $sum = 0;
                        for ($b = 0; $b < count($items); $b++) {
                            $sum += ceil($items[$b]['qty']);
                        }
                    }
                    else {
                        $sum=0;
                    }
                    $this->va_excel->getActiveSheet()->mergeCells('E' . $lup . ':F' . $lup . '');
                    $this->va_excel->getActiveSheet()->setCellValue('A' . $lup, $item['order_number']);
                    $this->va_excel->getActiveSheet()->setCellValue('B' . $lup, $item['amount']);
                    $this->va_excel->getActiveSheet()->setCellValue('C' . $lup, $sum);
                    $this->va_excel->getActiveSheet()->setCellValue('D' . $lup, $status);
                    $this->va_excel->getActiveSheet()->setCellValue('E' . $lup, $item['created_at']);
                    $lup++;
                }
                $this->va_excel->getActiveSheet()->setCellValue('A1', 'Bank Transfer Order ' . $item['client_code'] . '');
            }

            $this->va_excel->createSheet();
            $this->va_excel->setActiveSheetIndex(1);
            $this->va_excel->getActiveSheet()->setTitle('COD Order');

            $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->freezePane('H3');
            $this->va_excel->getActiveSheet()->mergeCells('A1:C1');
            $this->va_excel->getActiveSheet()->mergeCells('E2:F2');
            $this->va_excel->getActiveSheet()->setCellValue('A2', 'Order Number')->getColumnDimension('A')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('B2', 'Grand Total')->getColumnDimension('B')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('C2', 'Total Items')->getColumnDimension('C')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('D2', 'Status')->getColumnDimension('C')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('E2', 'Order date')->getColumnDimension('C')->setWidth(10);

            if (!empty($result[1])) {
                $statList = array(
                    0 => array("New Request", "warning"),
                    1 => array("Approve", "success"),
                    2 => array("Order Cancel", "danger"),
                    3 => array("Complete", "success"),
                    4 => array("Cancel", "danger")
                );

                $lup = 3;
                foreach ($result[1] as $item) {
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
                $this->va_excel->getActiveSheet()->setCellValue('A1', 'COD Order ' . $item['client_code'] . '');
            }

            $this->va_excel->createSheet();
            $this->va_excel->setActiveSheetIndex(2);
            $this->va_excel->getActiveSheet()->setTitle('Paypal Order');

            $this->va_excel->getActiveSheet()->setTitle('Paypal Order');
            $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->freezePane('H3');

            $this->va_excel->getActiveSheet()->mergeCells('A1:C1');
            $this->va_excel->getActiveSheet()->mergeCells('E2:F2');
            $this->va_excel->getActiveSheet()->setCellValue('A2', 'Order Number')->getColumnDimension('A')->setWidth(20);
            $this->va_excel->getActiveSheet()->setCellValue('B2', 'Grand Total')->getColumnDimension('B')->setWidth(20);
            $this->va_excel->getActiveSheet()->setCellValue('C2', 'Total Items')->getColumnDimension('C')->setWidth(20);
            $this->va_excel->getActiveSheet()->setCellValue('D2', 'Status')->getColumnDimension('C')->setWidth(20);
            $this->va_excel->getActiveSheet()->setCellValue('E2', 'Order date')->getColumnDimension('C')->setWidth(20);

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
            $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
            $this->va_excel->getActiveSheet()->freezePane('H3');

            $this->va_excel->getActiveSheet()->mergeCells('A1:C1');
            $this->va_excel->getActiveSheet()->mergeCells('E2:F2');
            $this->va_excel->getActiveSheet()->setCellValue('A2', 'Order Number')->getColumnDimension('A')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('B2', 'Grand Total')->getColumnDimension('B')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('C2', 'Total Items')->getColumnDimension('C')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('D2', 'Status')->getColumnDimension('C')->setWidth(10);
            $this->va_excel->getActiveSheet()->setCellValue('E2', 'Order date')->getColumnDimension('C')->setWidth(10);

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