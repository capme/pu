<style>
@font-face {
  font-family: 'FXMatrix105MonoEliteExpDbl';
  src: url('<?php echo base_static()?>fonts/FXMatrix105MonoEliteExpDblRegular.woff') format('woff');
}
body{
	margin: 0px;
	padding: 0px;
}
.area > div {
    border-bottom: 1px solid blue;
	border-right: 1px solid blue;
    width: 1cm;
    height: 0.5cm;
    display: inline-block;
}
.container{position: absolute; border:1px solid gray !important;}
*, .container {border: none !important;}
.area{border-bottom: 1px solid white !important;}
.container.delivery-ins{left: 6cm; top: 3.2cm; width: 7cm; height: 0.5cm;}
.container.print-date{left:8.6cm; top: 4.2cm; height: 0.4cm; width:2cm;}
.container.package-type{left: 1.2cm; top: 3.9cm; width: .5cm; height: 0.3cm; font-size:11px;}
.container.shipping-type{left: 1.2cm; top: 1.7cm; width: .5cm; height: 0.5cm; font-size:11px;}
.container.receiver{left: 13.3cm; top: 0.9cm; width: 5.5cm; height: 0.2cm;}
.container.company{left: 13.3cm; top: 1.1cm; width: 6cm; height: 0.2cm;}
.container.addr2{left: 17.3cm; top: 1.7cm; width: 2cm; height: 0.8cm; font-size:5px;}
.container.city{left: 13.3cm; top: 1.7cm; width: 3cm; height: 0.12cm;}
.container.prov{left: 13.8cm; top: 1.9cm; width: 2.5cm; height: 0.2cm;}
.container.addr1{left: 13.3cm; top: 1.3cm; width: 6.4cm; height: 0.6cm;}
.container.items{left: 12.3cm; top: 2.7cm; width: 4cm; height: 1cm;}
.items span{display:inline-block; vertical-align: top;}
.items .name{font-size: 5px; width: 2.8cm; }
.items .qty{width: 0.8cm; }
@page {
	margin: 0.3cm;
}
</style>

<?php
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
<div style="font-family: FXMatrix105MonoEliteExpDbl; font-size: 6px; 0cm; border: 1px solid white; width: 19cm; height: 5cm;  position: relative; letter-spacing:.5; margin-bottom:0.7cm; margin-top:14px; margin-left: -15px;" class="area">
	<div class="container delivery-ins"><?php echo $v->ordernr." - ".$client?></div>
	<div class="container print-date"><?php echo date("d m Y", time())?></div>
	<div class="container package-type">X</div>
	<div class="container shipping-type"><?php echo ($v->shipping_type == "YES") ? "X" : "<br />X"?></div>
	<div class="container receiver"><?php echo $v->receiver?></div>
	<div class="container company"><?php echo $v->company?></div>
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
		<div><span class="name"><?php echo $i['name']?></span><span class="qty"><?php echo $i['qty']?>&nbsp;&nbsp;<?php echo $i['weight']?></span></div>
		<?php endforeach; ?>
	</div>
  <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
</div>
<?php endforeach; ?>
<script>
window.print();
</script>