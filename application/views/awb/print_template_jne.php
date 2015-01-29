<?php 
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(205, 100), true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Vela Asia');
$pdf->SetTitle('JNE AirWayBill - '.date("Y-m-d"));
$pdf->SetSubject('JNE AirWayBill');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont('calibri');

// set margins
$pdf->SetMargins(0, 20, 0);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 0);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

 // set some language-dependent strings (optional)
/* if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(APPPATH.'libraries/tcpdf/example/lang/eng.php');
	$pdf->setLanguageArray($l);
}  */

// ---------------------------------------------------------

$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

// set font
$pdf->SetFont('calibri', '', 9);

$pdf->AddPage('L');
// create some HTML content
	
$grup=$this->client_m->getClients();
$opsi=array();
foreach($grup as $id=>$row){
	$opsi[$row['id']] = $row['client_code'];
	}
	
$date= date("d m Y", time());

foreach ($list->result() as $hasil => $data):
$client=$opsi[$data->client_id];
$data->company = trim($data->company);
$company = '';
if( $data->company && $data->company != '-' ) {
	$company = '('.$data->company.')';
}

$addr = explode("\n", $data->address);
	if(sizeof($addr) > 3) {
		$data->address = implode(" ", $addr);
	} else {
		$data->address = implode("<br />", $addr);
	}
	
$itemLists = unserialize($data->itemlist);
$items = array(array('', '', ''), array('', '', ''), array('', '', ''), array('', '', ''));
if(is_array($itemLists)) {
	foreach($itemLists as $l => $item) {
		$i = $item;
		$items[$l] = array($i['name'], intval($i['qty']), $i['weight']);
	}
}

if ($data->shipping_type == "YES" )
	{$yes ="X";}
	else{$yes ="";}
$reg ="";
if ($data->shipping_type !="YES")
	{$reg="X";}
	else{$reg;}
		
$htm =<<<EOF
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td></td>
		<td></td>
		<td colspan="2">$data->receiver $company</td>
	</tr>
	<tr>
		<th width="25%"></th>
		<th width="25%"></th>
		<th height="42" width="33%" style="font-size:8pt;">$data->address</th>
		<th></th>
	</tr>
	<tr>
		<th width="45%"></th>
		<td width="25%">$data->city</td>
		<td width="17%">$data->zipcode</td>
		<td width="13%"></td>
	</tr>
	<tr>
		<th width="50%"></th>
		<th width="20%">$data->province</th>
		<th width="17%">$data->country</th>
		<th width="13%">$yes</th>
	</tr>
	<tr>
		<th width="50%"></th>
		<th width="20%"></th>
		<th width="17%">$data->phone</th>
		<th width="13%">$reg</th>
	</tr>
	<tr>
		<th width="45%"></th>
		<th width="25%"></th>
		<th width="17%"></th>
		<th width="13%"></th>
	</tr>
		<tr>
		<th width="45%"></th>
		<th width="25%"></th>
		<th width="17%"></th>
		<th width="13%"></th>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="15%">{$items[0][0]}</th>
		<th width="5%">{$items[0][1]}</th>
		<th width="38%">{$items[0][2]}</th>
	</tr>
		<tr>
		<th width="42%"></th>
		<th width="15%">{$items[1][0]}</th>
		<th width="5%">{$items[1][1]}</th>
		<th width="38%">{$items[1][2]}</th>
	</tr>
	<tr>
		<th width="42%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $data->ordernr - $client</th>
		<th width="15%">{$items[2][0]}</th>
		<th width="5%">{$items[2][1]}</th>
		<th width="38%">{$items[2][2]}</th>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="15%">{$items[3][0]}</th>
		<th width="5%">{$items[3][1]}</th>
		<th width="38%">{$items[3][2]}</th>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="15%"></th>
		<th width="5%"></th>
		<th width="38%"></th>
	</tr>
	<tr>
		<th></th>
		<th width="25%"></th>
		<th width="20%"></th>
		<th width="35%"></th>
	</tr>
	<tr>
		<th></th>
		<th width="25%"></th>
		<th width="20%"></th>
		<th width="35%">X</th>
	</tr>
	
		<tr>
		<th width="20%"></th>
		<th width="25%">$date</th>
		<th width="20%"></th>
		<th width="35%"></th>
	</tr>
		<tr>
		<th></th>
		<th width="25%"></th>
		<th width="20%"></th>
		<th width="35%"></th>
	</tr>
		<tr>
		<th></th>
		<th width="25%"></th>
		<th width="20%"></th>
		<th width="35%"></th>
	</tr>
		<tr>
		<th></th>
		<th width="25%"></th>
		<th width="20%"></th>
		<th width="35%"></th>
	</tr>
</table>
EOF;
	
$pdf->writeHTML($htm, true, false, true, false, '');
endforeach;

// output the HTML content
$pdf->lastPage();
$pdf->Output('awb_jne_'.time().'.pdf', 'I');

?>
