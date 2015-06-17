<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Exportpaypal extends MY_Controller {
    var $data = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model("exportpaypal_m");
        $this->load->model("client_m");
        $this->load->library('va_excel');
    }

    public function index()
    {
        $this->data['content'] = "list_v.php";
        $this->data['pageTitle'] = "Export Paypal";
        $this->data['breadcrumb'] = array("Paypal" => "", "Export Paypal" => "exportpaypal");

        $this->exportpaypal_m->clearCurrentFilter();

        $this->load->library("va_list");
        $this->va_list->setListName("Export Paypal")->disableAddPlugin()
            ->setHeadingTitle(array("Record #", "Client Name"))
            ->setHeadingWidth(array(2, 10));

        $this->va_list->setDropdownFilter(1, array("name" => $this->exportpaypal_m->filters['id'], "option" => $this->client_m->getClientCodeList(TRUE)));

        $this->data['script'] = $this->load->view("script/sortingtool_list", array("ajaxSource" => site_url("exportpaypal/exportPaypalList")), true);
        $this->load->view("template", $this->data);
    }

    public function exportPaypalList(){
        $data = $this->exportpaypal_m->getClientPaypal();
        echo json_encode($data);
    }

    public function export($client){
        $this->va_excel->setActiveSheetIndex(0);

        $this->va_excel->getActiveSheet()->setTitle('Paypal Order');
        $this->va_excel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);
        $this->va_excel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
        $this->va_excel->getActiveSheet()->freezePane('H3');

        $this->va_excel->getActiveSheet()->mergeCells('A1:C1');

        $this->va_excel->getActiveSheet()->setCellValue('A2', 'Order Number')->getColumnDimension('A')->setWidth(10);
        $this->va_excel->getActiveSheet()->setCellValue('B2', 'Grand Total')->getColumnDimension('B')->setWidth(10);
        $this->va_excel->getActiveSheet()->setCellValue('C2', 'Total Items')->getColumnDimension('C')->setWidth(10);
        $this->va_excel->getActiveSheet()->setCellValue('D2', 'Status')->getColumnDimension('C')->setWidth(10);
        $this->va_excel->getActiveSheet()->setCellValue('E2', 'Order date')->getColumnDimension('C')->setWidth(10);

        $result = $this->exportpaypal_m->getOrderPaypal($client);
        $statList= array(
            0 =>array("Pending Payment"),
            1 => array("Processing"),
            2 => array("Complete"),
            3 => array("Fraud"),
            4 => array("Payment_Review"),
            5 => array("Canceled"),
            6 => array("Closed"),
            7 => array("Waiting_payment")
        );

        $lup = 3;
        foreach($result as $item){
            $status=$statList[$item['status']][0];
            $items=json_decode($item['items']);
            for($i=0; $i < count($items); $i++){
            }
            $sum=0;
            for($b=0; $b < count($items); $b++){
                $sum+=ceil($items[$b]->qty);
            }
            $this->va_excel->getActiveSheet()->setCellValue('A'.$lup, $item['order_number']);
            $this->va_excel->getActiveSheet()->setCellValue('B'.$lup, $item['amount']);
            $this->va_excel->getActiveSheet()->setCellValue('C'.$lup, $sum);
            $this->va_excel->getActiveSheet()->setCellValue('D'.$lup, $status);
            $this->va_excel->getActiveSheet()->setCellValue('E'.$lup, $item['created_at']);
            $lup++;
        }
        $this->va_excel->getActiveSheet()->setCellValue('A1', 'Paypal Order '.$item['client_code'].'');
        $filename='Paypal Order '.$item['client_code'].'.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->va_excel, 'Excel5');
        $objWriter->save('php://output');
    }
}