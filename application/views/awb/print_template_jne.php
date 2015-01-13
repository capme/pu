<style>
@font-face {
  font-family: 'FXMatrix105MonoEliteExpDbl';
  src: url('<?php echo base_static()?>/FXMatrix105MonoEliteExpDblRegular.woff') format('woff');
}
body.Firefox{
	margin: 0px;
	padding: 0px;	
}

body.Chrome{
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
	font-size: 5px; 0cm; 
	border: 1px solid white;
	width: 19cm; 
	height: 5cm; 
	position: relative; 
	letter-spacing:.5; 
	margin-bottom:0.7cm; 
	margin-top:14px; 
	margin-left: -15px;
}
.Firefox .area > div{
    border-bottom: 1px solid blue;
	border-right: 1px solid blue;
    width: 1cm;
    height: 0.5cm;
}

.Chrome .area > div {
    border-bottom: 1px solid blue;
	border-right: 1px solid blue;
    width: 1cm;
    height: 0.5cm;
    display: inline-block;
}

.Firefox .container{position: absolute; border:1px solid gray !important;}
*, .Firefox .container {border: none !important;}
.Firefox .container.delivery-ins{left: 1.6cm; top: 5.3cm; width: 12cm; height: 0.5cm;font-size: 11px;}
.Firefox .container.print-date{left:6.5cm; top: 7cm; height: 0.4cm; width:3cm; font-size: 9px;}
.Firefox .container.package-type{left: 27.5cm; top: 6.3cm; width: .5cm; height: 0.3cm; font-size:12px;}
.Firefox .container.shipping-type{left: 27.5cm; top: 2.9cm; width: .5cm; height: 0.5cm; font-size:12px;}
.Firefox .container.receiver{left: 16cm; top: 1.5cm; width: 9.5cm; height: 0.2cm;font-size: 10px;}
.Firefox .container.company {left: 10.4cm; top: 1.1cm; width: 6cm; height: 0.2cm;font-size: 9px;  display:none;}
.Firefox .container.addr2{left: 22.4cm; top: 2.7cm; width: 2cm; height: 0.8cm; font-size:9px;}
.Firefox .container.city{left: 14.4cm; top: 2.7cm; width: 5cm; height: 0.12cm; font-size: 9px;}
.Firefox .container.prov{left: 16.3cm; top: 3cm; width: 4.5cm; height: 0.2cm; font-size: 9px;}
.Firefox .container.addr1{left: 14.9cm; top: 1.9cm; width: 11cm; height: 0.6cm; font-size: 8px;}
.Firefox .container.items{left: 14.1cm; top: 4.2cm; width: 6cm; height: 1cm; font-size: 9px;}
.Firefox .items span{display:inline-block; vertical-align: top; font-size: 9px;}
.Firefox .items .name{font-size: 9px; width: 3.5cm; }
.Firefox .items .qty{width: 1cm; font-size: 9px;}

.Chrome .container{position: absolute; border:1px solid gray !important;}
*, .Chrome .container {border: none !important;}
.Chrome .container.delivery-ins{left: 1.6cm; top: 3.2cm; width: 7cm; height: 0.5cm;}
.Chrome .container.print-date{left:4.2cm; top: 4.2cm; height: 0.4cm; width:2cm;}
.Chrome .container.package-type{left: 16cm; top: 3.8cm; width: .5cm; height: 0.3cm; font-size:11px;}
.Chrome .container.shipping-type{left: 16cm; top: 1.9cm; width: .5cm; height: 0.5cm; font-size:11px;}
.Chrome .container.receiver{left: 9.4cm; top: 0.9cm; width: 5.5cm; height: 0.2cm;}
.Chrome .container.company {left: 10.4cm; top: 1.1cm; width: 6cm; height: 0.2cm;  display:none;}
.Chrome .container.addr2{left: 13.1cm; top: 1.8cm; width: 2cm; height: 0.8cm; font-size:5px;}
.Chrome .container.city{left: 8.7cm; top: 1.7cm; width: 3cm; height: 0.12cm;}
.Chrome .container.prov{left: 9.8cm; top: 1.9cm; width: 2.5cm; height: 0.2cm;}
.Chrome .container.addr1{left: 9.1cm; top: 1.3cm; width: 6.4cm; height: 0.6cm;}
.Chrome .container.items{left: 8.2cm; top: 2.7cm; width: 4cm; height: 1cm;}
.Chrome .items span{ display:inline-block; vertical-align: top;}
.Chrome .items .name{ font-size: 5px; width: 2.4cm; }
.Chrome .items .qty{width: 1cm;}

@page {
	margin: 0.3cm;
}
</style>
<?php 
$browser = get_browser(null, true);
$grup=$this->client_m->getClients();
$opsi=array();
foreach($grup as $id=>$row){
	$opsi[$row['id']] = $row['client_code'];
	}

foreach($list->result() as $i => $v): 
	if($i%11 == 0 && $i > 0){ 
	echo "<div style='page-break-before: always;'></div><div style='height:2px;'></div>";
	}
	$client=$opsi[$v->client_id];
?>
<body class="<?php echo $browser['browser'];?>">
<div  class="area">
	<div class="container delivery-ins"><?php echo $v->ordernr." - ".$client?></div>
	<div class="container print-date"><?php echo date("d m Y", time())?></div>
	<div class="container package-type">X</div>
	<div class="container shipping-type"><?php echo ($v->shipping_type == "YES") ? "X" : "<br />X"?></div>
	<div class="container receiver"><?php echo $v->receiver?></div>
	<div class="container company" style="display:none" ><?php echo $v->company?></div>		
	<div class="container addr2"><?php echo $v->zipcode."<br />".$v->country."<br />".$v->phone?></div>
	<div class="container city"><?php echo ($v->city) ? $v->city : ''?></div>
	<div class="container prov"><?php echo $v->province?></div>
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
		<div><span class="name"><?php echo $i['name']?></span><span class="qty"><?php echo $i['qty']?>&nbsp;&nbsp<?php echo $i['weight']?></span></div>
		<?php endforeach; ?>
	</div>
  <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
</div>
</body>
<?php endforeach; ?>
<script>
window.print();
</script>