<?php
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(215, 100), true, 'UTF-8', false);

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
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
} */

// ---------------------------------------------------------

$pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');

// set font
$pdf->SetFont('calibri', '', 9);


/** start loop **/
$pdf->AddPage('L');
// create some HTML content
$html = <<<EOF
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="25%"></td>
		<td width="25%"></td>
		<td width="20%"></td>
		<td width="35%">Sylviana Hamdani</td>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="25%"></th>
		<th height="41" width="33%" colspan="2" style="font-size:8pt;">Jl. Setiabudi Tengah no. 7, sekitar dua
rumah dari restoran Mbah Jingkrak,
Karet Selatan
- Setiabudi, Jakarta Selatan</th>
	</tr>
	<tr>
		<th width="42%"></th>
		<td width="25%"></td>
		<td width="22%">Kab. Tangerang</td>
		<td width="11%">15143</td>
	</tr>
	<tr>
		<th width="45%"></th>
		<th width="26%"></th>
		<th width="18%">Banten</th>
		<th width="11%">Indonesia</th>
	</tr>
	<tr>
		<th width="45%"></th>
		<th width="26%"></th>
		<th width="18%"></th>
		<th width="13%">0812377483</th>
	</tr>
		<tr>
		<th width="45%"></th>
		<th width="25%"></th>
		<th width="17%"></th>
		<th width="13%"></th>
	</tr>
	<tr>
		<th width="42%"></th>
		<th width="15%"></th>
		<th width="5%"></th>
		<th width="38%"></th>
	</tr>
		<tr>
		<th width="62%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; X</th>
		<th width="15%">&nbsp;&nbsp;&nbsp; SKU</th>
		<th width="5%">1</th>
		<th width="18%">1</th>
	</tr>
	<tr>
		<th width="62%"></th>
		<th width="15%">&nbsp;&nbsp;&nbsp; SKU</th>
		<th width="5%">1 </th>
		<th width="18%">1</th>
	</tr>
	<tr>
		<th width="62%"><table><tr><td width="40%"></td><td>BLO10000013 - Blow Shoes</td></tr></table></th>
		<th width="15%">&nbsp;&nbsp;&nbsp; SKU</th>
		<th width="5%">1</th>
		<th width="18%">1</th>
	</tr>
	<tr>
		<th width="62%"></th>
		<th width="15%">&nbsp;&nbsp;&nbsp; SKU</th>
		<th width="5%">1</th>
		<th width="18%">1</th>
	</tr>
	<tr>
		<th width="42%"></th>
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
		<th width="20%"></th>
		<th width="22%"></th>
		<th width="20%"></th>
		<th width="35%"></th>
	</tr>
		<tr>
		<th width="20%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; X</th>
		<th width="22%"></th>
		<th width="20%">16 01 2015</th>
		<th width="38%"></th>
	</tr>
	<tr>
		<th width="50%"></th>
		<th width="15%"></th>
		<th width="15%"></th>
		<th width="20%"></th>
	</tr>
	<tr>
		<th width="50%"></th>
		<th width="15%"></th>
		<th width="15%"></th>
		<th width="20%">200.000</th>
	</tr>
	<tr>
		<th width="50%"></th>
		<th width="15%"></th>
		<th width="15%"></th>
		<th width="20%"></th>
	</tr>
</table>
EOF;
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();
/*** end loop ***/

$pdf->Output('example_028.pdf', 'I');