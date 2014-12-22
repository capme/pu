<style>
@font-face {
  font-family: 'FXMatrix105MonoEliteExpDbl';
  src: url('<?php echo base_static()?>/FXMatrix105MonoEliteExpDblRegular.woff') format('woff');
}
body .Firefox{
	margin: 0px;
	padding: 0px;
}

body .Chrome{
	margin: 0px;
	padding: 0px;
}

.Firefox .area{
	font-family: FXMatrix105MonoEliteExpDbl; 
	font-size: 6px; 0cm; 
	border: 1px solid white; 
	width: 19cm; 
	height: 9.3cm;  
	position: relative; 
	letter-spacing:.5; 
	margin-bottom:0.7cm; 
	margin-top:14px; 
	margin-left: -15px;
}

.Chrome .area{
	font-family: FXMatrix105MonoEliteExpDbl; 
	font-size: 6px; 0cm; 
	border: 1px solid white; 
	width: 19cm; 
	height: 5cm;  
	position: relative; 
	letter-spacing:.5; 
	margin-bottom:0.7cm; 
	margin-top:14px; 
	margin-left: -15px;
}
.Firefox .area > div {
	border-right: 1px solid blue;
    width: 1cm;
    height: 0.5cm;
    display: inline-block;
}

.Chrome .area > div {
	border-right: 1px solid blue;
    width: 1cm;
    height: 0.5cm;
    display: inline-block;
}


.Firefox .container{position: absolute; border:1px solid gray !important;}
*, .Firefox .container {border: none !important;}
.Firefox .container.delivery-ins{left: 7cm;  top: 5.2cm; width: 7cm; height: 0.6cm; font-size: 11px;}
.Firefox .container.print-date{left:13.6cm; top: 6.9cm; height: 0.5cm; width:4cm; font-size: 11px;}
.Firefox .container.package-type{left: 0.3cm; top: 7.1cm; width: .5cm; height: 0.3cm; font-size:12px;}
.Firefox .container.shipping-type{left: 0.3cm; top: 3.4cm; width: .5cm; height: 0.5cm; font-size:12px;}
.Firefox .container.receiver{left: 22cm;  top: 1.3cm; width: 5.5cm; height: 0.2cm; font-size: 11px;}
.Firefox .container.company{left: 13.3cm; top: 1.1cm; width: 6cm; height: 0.2cm; font-size: 9px;}
.Firefox .container.addr2{left: 29.4cm; top: 2.5cm; width: 2cm; height: 0.8cm; font-size:9px;}
.Firefox .container.city{left: 21.9cm;  top: 2.5cm; width: 5cm; height: 0.12cm; font-size: 9px;}
.Firefox .container.prov{left: 22.8cm; top: 2.8cm; width: 5cm; height: 0.2cm; font-size: 9px;}
.Firefox .container.addr1{left: 21.9cm; top: 1.7cm; width: 10cm; height: 0.6cm; font-size: 11px;}
.Firefox .container.nilai{left: 25.2cm; top: 7.7cm; width: 6cm; height: 0.2cm; font-size: 11px;}
.Firefox .container.items{left: 21.1cm; top: 4.2cm; width: 7cm; height: 1cm; font-size: 11px;}
.Firefox .items span{display:inline-block; vertical-align: top;}
.Firefox .items .name{font-size: 9px; width: 3.6cm; }
.Firefox .items .qty{font-size: 9px;width: 1.2cm; }

.Chrome .container{position: absolute; border:1px solid gray !important;}
*, .Chrome .container {border: none !important;}
.Chrome .container.delivery-ins{left: 5.5cm; top: 3.2cm; width: 7cm; height: 0.5cm;}
.Chrome .container.print-date{left:8.1cm; top: 4.1cm; height: 0.4cm; width:2cm;}
.Chrome .container.package-type{left: 0.7cm; top: 3.8cm; width: .5cm; height: 0.3cm; font-size:11px;}
.Chrome .container.shipping-type{left: 0.7cm; top: 1.6cm; width: .5cm; height: 0.5cm; font-size:11px;}
.Chrome .container.receiver{left: 12.9cm; top: 0.9cm; width: 5.5cm; height: 0.2cm;}
.Chrome .container.company{left: 13.3cm; top: 1.1cm; width: 6cm; height: 0.2cm;}
.Chrome .container.addr2{left: 16.9cm; top: 1.7cm; width: 2cm; height: 0.8cm; font-size:5px;}
.Chrome .container.city{left: 12.9cm; top: 1.7cm; width: 3cm; height: 0.12cm;}
.Chrome .container.prov{left: 13.2cm; top: 1.9cm; width: 2.5cm; height: 0.2cm;}
.Chrome .container.addr1{left: 13cm; top: 1.3cm; width: 6.4cm; height: 0.6cm;}
.Chrome .container.items{left: 12.3cm; top: 2.7cm; width: 4cm; height: 1cm;}
.Chrome .container.nilai{left: 15.8cm; top: 4.5cm; width: 6cm; height: 0.2cm;}
.Chrome .items span{display:inline-block; vertical-align: top;}
.Chrome .items .name{font-size: 5px; width: 2.2cm; }
.Chrome .items .qty{width: 1.2cm; } 

@page {
	margin: 0.3cm;
}
</style>

<?php
$browser = get_browser(null, true);
$grup=$this->client_m->getClients();
$opsi=array();
foreach($grup as $id=>$row)
{
$opsi[$row['id']] = $row['client_code'];
}
 
foreach($list->result() as $i => $v): 
if($i%11 == 0 && $i > 0){ 
echo "<div style='page-break-before: always;'></div><div style='height:2px;'></div>";
}
$client=$opsi[$v->client_id];
?>
<body class="<?php echo $browser['browser'];?>">
<div class="area">
	<div class="container delivery-ins"><?php echo $v->ordernr." - ".$client?></div>
	<div class="container print-date"><?php echo date("d m Y", time())?></div>
	<div class="container package-type">X</div>
	<div class="container shipping-type" style="display:none;"><?php echo ($v->shipping_type == "1") ? "A" : ($v->shipping_type == "2" ? "<br /> <br /> <br /> B" : "<br />C")?></div>
	<div class="container receiver"><?php echo $v->receiver?></div>
	<div class="container company"><?php echo $v->company?></div>
	<div class="container addr2"><?php echo $v->zipcode."<br />".$v->country."<br />".$v->phone?></div>
	<div class="container city"><?php echo ($v->city) ? $v->city : ''?></div>
	<div class="container prov"><?php echo $v->province?></div>
	<?php if(intval($v->amount)):?>
	<div class="container nilai">RP <?php echo number_format($v->amount, 0);?></div>
	<?php endif;?>
	<?php
	$addr = explode("\n", $v->address);
	if(sizeof($addr) > 3) {
		$v->address = implode(" ", $addr);
	} else {
		$v->address = implode("<br />", $addr);
	}
	?>
	<div class="container addr1"><?php echo $v->address;?></div>
	<div class="container items">
		<?php
		$itemLists = explode("|", $v->itemlist);
		foreach($itemLists as $i): 
			if(empty($i)){continue;}
			else{$i = unserialize($i);}
		?>
		<div><span class="name"><?php echo $i['name']?></span><span class="qty"><?php echo $i['qty']?>&nbsp;&nbsp;<?php echo $i['weight']?></span></div>
		<?php endforeach; ?>
	</div>
  <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
</div>
</body>
<?php endforeach; ?>
<script>
window.print();
</script>