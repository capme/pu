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

/* // set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(APPPATH.'libraries/tcpdf/example/lang/eng.php');
	$pdf->setLanguageArray($l);
} */

// ---------------------------------------------------------

$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

// set font
$pdf->SetFont('calibri', '', 9);

$pdf->AddPage('L');
// create some HTML content
$html = <<<EOF
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td></td>
		<td></td>
		<td colspan="2">1 Sylviana Hamdani 1</td>
	</tr>
	<tr>
		<th width="25%"></th>
		<th width="25%"></th>
		<th height="42" width="33%" style="font-size:8pt;">Jl. Setiabudi Tengah no. 7, sekitar dua
rumah dari restoran Mbah Jingkrak,
Karet Selatan
- Setiabudi, Jakarta Selatan</th>
		<th></th>
	</tr>
	<tr>
		<th width="45%"></th>
		<td width="25%">Kab. Tangerang</td>
		<td width="17%">15143</td>
		<td width="13%"></td>
	</tr>
	<tr>
		<th width="50%"></th>
		<th width="20%">Banten</th>
		<th width="17%">Indonesia</th>
		<th width="13%">X</th>
	</tr>
	<tr>
		<th width="25%"></th>
		<th width="25%"></th>
		<th width="20%"></th>
		<th width="35%">081478324698</th>
	</tr>
	<tr>
		<th width="45%"></th>
		<th width="25%"></th>
		<th width="17%"></th>
		<th width="13%">X</th>
	</tr>
		<tr>
		<th width="45%"></th>
		<th width="25%"></th>
		<th width="17%"></th>
		<th width="13%">X</th>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="15%">BLO-19-MAR-39</th>
		<th width="5%">1</th>
		<th width="38%">1</th>
	</tr>
		<tr>
		<th width="42%"></th>
		<th width="15%"></th>
		<th width="5%"></th>
		<th width="38%"></th>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="15%"></th>
		<th width="5%"></th>
		<th width="38%"></th>
	</tr>
	<tr>
		<th width="42%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; BLO1000000062 - Blow Shoes</th>
		<th width="15%"></th>
		<th width="5%"></th>
		<th width="38%"></th>
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
		<th width="25%">15 01 2015</th>
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
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();

$pdf->Output('awb_jne_'.time().'.pdf', 'I');
?>