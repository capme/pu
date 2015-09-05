<?php
class Paymentconfirmation_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table = 'bank_confirmation';
	var $sorts = array(1 => "id");
	var $pkField = "id";
	var $status=array("cancel"=>2,"approve"=>1);
    var $statusAWB=array("printed"=>1,"new request"=>0);
	var $tableClient ='client';
    var $tableAwb = 'awb_queue_printing';
    var $expired ='expired_order';
	
	function __construct()
	{
		parent::__construct();
        $this->load->model("autocancel_m");
        $this->load->library("va_list");
		$this->db = $this->load->database('mysql', TRUE);

        $user=$this->session->userdata('group');
		if ($user == 4) {
            $this->relation = array(
                array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField} "),
                array("type" => "left", "table" => $this->tableAwb, "link" => "{$this->table}.order_number  = {$this->tableAwb}.ordernr  where receipt_url=''")
            );
        }
        else {
            $this->relation = array(
                array("type" => "inner", "table" => $this->tableClient, "link" => "{$this->table}.client_id  = {$this->tableClient}.{$this->pkField}"),
                array("type" => "left", "table" => $this->tableAwb, "link" => "{$this->table}.order_number  = {$this->tableAwb}.ordernr")
            );
        }
		$this->select = array(
                            "{$this->table}.{$this->pkField}",
                            "{$this->table}.order_number",
                            "{$this->table}.created_at",
                            "{$this->table}.origin_bank",
                            "{$this->table}.dest_bank",
                            "{$this->table}.transaction_method",
                            "{$this->table}.amount `".$this->table.".amount`",
                            "{$this->table}.name",
                            "{$this->table}.transaction_date",
                            "{$this->table}.updated_at",
                            "{$this->table}.status `".$this->table.".status`",
                            "{$this->table}.receipt_url",
                            "{$this->table}.updated_by",
                            "{$this->tableClient}.client_code",
                            "{$this->tableAwb}.status `".$this->tableAwb.".status`"
                        );
		
		$this->filters = array(
                            $this->table.".status"=>$this->table."_status",
                            "order_number"=>"order_number",
                            $this->table.".client_id"=>$this->table."_client_id",
                            $this->table.".amount"=>$this->table."_amount",
                            $this->tableAwb.".status"=>$this->tableAwb."_status",
                            "name"=>"name",
							"created_at"=>"created_at"
                        );

        $this->listWhere['equal'] = array();
        $this->listWhere['like'] = array("order_number", "name");
		$this->daterange=$this->table.".created_at";

	}
	
	public function getPaymentConfirmationList() 
	{
		$this->db = $this->load->database('mysql', TRUE); 
		$iTotalRecords = $this->_doGetTotalRow();
		$iDisplayLength = intval($this->input->post('iDisplayLength'));
		$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
		$iDisplayStart = intval($this->input->post('iDisplayStart'));
		$sEcho = intval($this->input->post('sEcho'));
	
		$records = array();
		$records["aaData"] = array();
		
		$grup=$this->users_m->userList();
		$opsiarray=array();
		foreach($grup as $pkUserId=>$row)
		{
		$opsiarray[$row['pkUserId']]=$row['username'];
		}
		
		$statList= array(
				0 =>array("New Request", "warning"),
				1 =>array("Approve", "success"),
				2 =>array("Cancel","danger")
		);

        $statListAWB= array(
            0 =>array("New Request", "warning"),
            1 =>array("Printed", "success"),
            3 =>array("Cron Process", "danger")
        );

        $end = $iDisplayStart + $iDisplayLength;
		$end = $end > $iTotalRecords ? $iTotalRecords : $end;
	
		$_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
		$no=0;
		foreach($_row->result() as $_result) {
            $_resultArr = (array)$_result;
			//$status=$statList[$_result->status];
            $status=$statList[$_resultArr['bank_confirmation.status']];
            //$statusAWB=$statListAWB[$_result->statusAwb];
            if(!isset($statListAWB[$_resultArr['awb_queue_printing.status']])){
                $statusAWB = "New Request";
            }else {
                $statusAWB = $statListAWB[$_resultArr['awb_queue_printing.status']];
            }
			//if ($_result->status==0)
            if ($_resultArr['bank_confirmation.status']==0)
			{
				$action='<a href="'.site_url("paymentconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>|<a href="'.site_url("paymentconfirmation/approve/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-check" ></i> Approve</a>|
				<a href="'.site_url("paymentconfirmation/cancel/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-times" ></i> Cancel</a>';
			}else{
				$action='<a href="'.site_url("paymentconfirmation/view/".$_result->id).'"  enabled="enabled" class="btn btn-xs default"><i class="fa fa-search" ></i> View</a>';
			}
            $user=$this->session->userdata('pkUserId');

            $date = explode(' ', $_result->created_at);
			$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$date[0],
					$_result->client_code,
					$_result->order_number,
					$_result->name,
					$_result->origin_bank . ($_result->origin_bank ? '<br /> (<a '.anchor($_result->receipt_url, 'Receipt', 'style="font-size:12px;" target="_blank" enabled="enabled" class="fa fa-search btn btn-xs default"').'</a>)' : ''),
					"Rp. ".number_format($_resultArr['bank_confirmation.amount']),
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
                    '<span class="label label-sm label-'.($statusAWB[1]).'">'.($statusAWB[0]).'</span>',
					$action
			);
		}
	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;
		return $records;
	}

    public function getNewOrder(){
        $order = $this->db->query("SELECT id,order_number,client_id,created_at, WEEKDAY(created_at) as weekday FROM bank_confirmation where status = 0 and client_id != 6 ")->result_array();
        for($a=0; $a < count($order); $a++){
            $counter = 1;
            $orderdate=$order[$a]['created_at'];
            do {
                do {
                    $temp = $this->db->query("select date_add('$orderdate', INTERVAL 1 DAY) as cancelday")->row_array();
                    $orderdate = $temp['cancelday'];
                } while ($this->isWeekEnd($orderdate) || $this->isHoliday($orderdate));
                $counter++;
            }
            while($counter < 3);
            $available = $this->autocancel_m->cekOrder($order[$a]['order_number'], $order[$a]['client_id']);
            if(empty($available)){
                $data = array("id" => $order[$a]['id'], "status"=>0, "created_date"=>$order[$a]['created_at'],"order_method" => "bank", "client_id" => $order[$a]['client_id'], "order_number" => $order[$a]['order_number'], "expired_date" => $orderdate);
                $this->db->insert($this->expired, $data);
            }
        }
    }

    public function getPlouOrder(){
        $order = $this->db->query("SELECT id,order_number,client_id,created_at, WEEKDAY(created_at) as weekday FROM bank_confirmation where status = 0 and client_id = 6")->result_array();
        for ($a = 0; $a < count($order); $a++) {
            $orderdate = $order[$a]['created_at'];
            do {
                $cektime =  $this->db->query("select hour('$orderdate') as hour")->row_array();
                $hour = $cektime['hour'];
                if($hour > 14 ){
                    $tempex = explode(" ",$orderdate );
                    $tempdate = $tempex[0]." 09:00:00";
                    $temporderdate = $this->db->query("select date_add('$tempdate', INTERVAL 1 DAY) as temp")->row_array();
                    $time = $temporderdate['temp'];
                    $temp= $this->db->query("select date_add('$time', INTERVAL 12 HOUR) as cancelday")->row_array();
                    $orderdate= $temp['cancelday'];
                } elseif ($hour < 9){
                    $tempex = explode(" ",$orderdate );
                    $tempdate = $tempex[0]." 09:00:00";
                    $temp= $this->db->query("select date_add('$tempdate', INTERVAL 12 HOUR) as cancelday")->row_array();
                    $orderdate= $temp['cancelday'];
                }
                else{
                    $temp= $this->db->query("select date_add('$orderdate', INTERVAL 12 HOUR) as cancelday")->row_array();
                    $orderdate= $temp['cancelday'];

                }
                    $available = $this->autocancel_m->cekOrder($order[$a]['order_number'], $order[$a]['client_id']);
                    if(empty($available)){
                        $data = array("id" => $order[$a]['id'], "status"=>0, "created_date"=>$order[$a]['created_at'],"order_method" => "bank", "client_id" => $order[$a]['client_id'], "order_number" => $order[$a]['order_number'], "expired_date" => $orderdate);
                        $this->db->insert($this->expired, $data);
                    }

            } while($this->isWeekEnd($orderdate) || $this->isHoliday($orderdate));
        }

    }

    public function getPopOrder(){
        $order = $this->db->query("SELECT id,order_number,client_id,created_at, WEEKDAY(created_at) as weekday FROM bank_confirmation where status = 0 and client_id = 6")->result_array();
        for ($a = 0; $a < count($order); $a++) {
            $orderdate = $order[$a]['created_at'];
            do {
                $cektime =  $this->db->query("select hour('$orderdate') as hour")->row_array();
                $hour = $cektime['hour'];
                if($hour > 16 ){
                    $tempex = explode(" ",$orderdate );
                    $tempdate = $tempex[0]." 09:00:00";
                    $temporderdate = $this->db->query("select date_add('$tempdate', INTERVAL 1 DAY) as temp")->row_array();
                    $time = $temporderdate['temp'];
                    $temp= $this->db->query("select date_add('$time', INTERVAL 2 HOUR) as cancelday")->row_array();
                    $orderdate= $temp['cancelday'];
                }
                elseif($hour < 9){
                    $tempex = explode(" ",$orderdate );
                    $tempdate = $tempex[0]." 09:00:00";
                    $temp= $this->db->query("select date_add('$tempdate', INTERVAL 2 HOUR) as cancelday")->row_array();
                    $orderdate= $temp['cancelday'];
                }else {
                    $temp= $this->db->query("select date_add('$orderdate', INTERVAL 2 HOUR) as cancelday")->row_array();
                    $orderdate= $temp['cancelday'];
                }
            } while($this->isWeekEnd($orderdate) || $this->isHoliday($orderdate));
        }

        foreach ($order as $result) {
            $available = $this->autocancel_m->cekOrder($result['order_number'], $result['client_id']);
            if(empty($available)){
                $data = array("id" => $result['id'], "status"=>0, "created_date"=>$result['created_at'],"order_method" => "bank", "client_id" => $result['client_id'], "order_number" => $result['order_number'], "expired_date" => $orderdate);
                $this->db->insert($this->expired, $data);
            }
        }
    }

    public function isHoliday($orderdate){
        $holiday = $this->db->query("select date(date) as holiday from holiday ")->result_array();
        $date = $this->db->query("select date('$orderdate') as date")->row_array();
        $is_holiday = false;

        for($hol = 0; $hol < count($holiday); $hol++){
            if ($holiday[$hol]['holiday'] == $date['date']){
                $is_holiday = $is_holiday || true;
            }else{
                $is_holiday = $is_holiday || false;
            }
        }
        return $is_holiday;
    }

    public function isWeekEnd($orderdate){
        $day1 = $this->db->query("SELECT WEEKDAY('$orderdate') as day1")->result();
        if (($day1[0]->day1 == 5) || ($day1[0]->day1 == 6)) {
            return true;
        }else{
            return false;
        }
    }

    public function cancelOrder($ordernr){
        $this->db->where('order_number', $ordernr);
        $this->db->update($this->table, array('status'=>2));
    }

    public function setHistory($setHistory){
        $this->db->insert('order_history', $setHistory);
    }
	
	public function getConfirmationById($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$this->db->select('*, bank_confirmation.client_id, bank_confirmation.status as status, order_history.status as status_history, bank_confirmation.created_at, bank_confirmation.id, bank_confirmation.updated_at, auth_users.username, order_history.type, order_history.created_at as history_date');
		$this->db->from($this->table);
		$this->db->join('client','client.id=bank_confirmation.client_id');
        $this->db->join('order_history', 'order_history.order_id=bank_confirmation.id and type=2','left');
        $this->db->join('auth_users', 'auth_users.pkUserId=order_history.created_by','left');
        $this->db->join('awb_queue_printing', 'bank_confirmation.order_number=awb_queue_printing.ordernr','LEFT');
        $this->db->where('bank_confirmation.id', $id);
        $this->db->order_by('order_history.id','desc');
        return $this->db->get();
	}

	public function Approve ($id)
	{
		$this->db = $this->load->database('mysql', TRUE);
		$user=$this->session->userdata('pkUserId');	
		$time=date('Y-m-d H:i:s', now());
		
		$data['status'] = $this->status['approve'];
		$data['updated_by']=$user;
		$data['updated_at']= $time;

        $history['created_by']=$user;
        $history['status']=$this->status['approve'];
		$history['order_id']=$id;
        $history['created_at']= $time;
        $history['type']=2;

		$this->db->where($this->pkField, $id);
		$this->db->update($this->table, $data);

        $this->db->where('id',$id);
        $this->db->where('order_method', 'bank');
        $this->db->update($this->expired, array('status'=>$data['status']));

        $this->db->insert('order_history', $history);
		return $id;		
	}
	
	public function Reason($post) 
	{
        $this->load->model("client_m");
        $this->load->library("mageapi");

		$msg = array();			
		$user=$this->session->userdata('pkUserId');		
		$time=date('Y-m-d H:i:s', now());
		
		if(!empty($post['reason'])) {

			$history['note'] = $post['reason'];
			$history['type']=2;
            $history['order_id']=$post['id'];
            $history['created_by']=$user;
            $history['created_at']=$time;
            $history['status']=$this->status['cancel'];

            $data['status'] = $this->status['cancel'];
			$data['updated_by']=$user;
			$data['updated_at']=$time;	
			
			$this->db->where($this->pkField, $post['id']);			
			$this->db->update($this->table, $data);

            $this->db->where('id',$post['id']);
            $this->db->where('order_method', 'bank');
            $this->db->update($this->expired, array('status'=>$data['status']));

            $this->db->insert('order_history', $history);

            $client = $this->client_m->getClientById($post['client_id'])->row_array();
            $config = array(
                "auth" => $client['mage_auth'],
                "url" => $client['mage_wsdl']
            );

            if( $this->mageapi->initSoap($config) ) {
                $this->mageapi->cancelPayment($post['order_number'], $post['reason']);
            }

			return $post['id'];
		} 
		else {
			return $msg;
		}	
	}
	
	public function add($client, $payments) {
		$this->db = $this->load->database('mysql', TRUE);
		
		$this->db->trans_start();
		$insertedIds = array();
		foreach($payments as $payment) {
			$exist = $this->db->get_where($this->table, array("order_number" => $payment['order_number'], "client_id" => $client['id']))->row_array();
			if(isset($exist['id']) && $exist['id']) {
				$this->db->where(array("order_number" => $payment['order_number'], "client_id" => $client['id']));
				$this->db->update(
					$this->table, 
					array("client_id" => $client['id'], "order_number" => $payment['order_number'], "origin_bank" => $payment['origin_bank'], "dest_bank" => $payment['dest_bank'], "transaction_method" => $payment['transaction_method'], "name" => $payment['name'], "transaction_date" => $payment['transaction_date'], "amount" => $payment['amount'], "receipt_url" => $payment['receipt_url'], "updated_by" => "2", "updated_at" => $payment['created_at'] )
				);
			} else {
				$this->db->insert( 
					$this->table, 
					array("client_id" => $client['id'], "order_number" => $payment['order_number'], "origin_bank" => $payment['origin_bank'], "dest_bank" => $payment['dest_bank'], "transaction_method" => $payment['transaction_method'], "name" => $payment['name'], "transaction_date" => $payment['transaction_date'], "amount" => $payment['amount'], "receipt_url" => $payment['receipt_url'], "updated_by" => "2", "created_at" => $payment['created_at'] )
				);
			}
			
			$insertedIds[] = $payment['id'];
		}
		$this->db->trans_complete();
		
		return $insertedIds;
	}
	
}
?>
