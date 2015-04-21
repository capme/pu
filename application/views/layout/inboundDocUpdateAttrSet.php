<?php
$client = $_GET['client'];
$doc = $_GET['doc'];
$rows = $this->inbounddocument_m->getInboundInvItem($client, $doc);
$clientArr = $this->clientoptions_m->get($client, "attribute_set");
?>

<?php echo $this->va_input->getFieldInput($this->va_input->fields[0]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[1]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[2]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[3]);?>
<div class="panel panel-default" style="width:100%">
	<?php 
	$listOption = json_decode($clientArr['option_value'],true);
	$listOption = array_map('strtolower', $listOption);
	?>
	<table class="table" border=0>
	<thead>
	<tr>
		<th width="25%">SKU</th>
		<th width="20%">Product Name</th>
		<th width="15%">Gender</th>
		<th width="15%">Category</th>
		<th width="25%" style="text-align:center;">Attribute Set</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$num = 4;
	foreach($rows as $itemRows){
		$arr = explode(",", $itemRows['sku_description']);
		$productName = $arr[4];
		$category = $arr[2];
		$gender = $arr[1];
		if(strtoupper($gender) == "F"){
			$gender = "women";
		}elseif(strtoupper($gender) == "M"){
			$gender = "men";
		}elseif(strtoupper($gender) == "U"){
			$gender = "unisex";
		}
		if(empty($itemRows['attribute_set'])){
			$bgColor = " bgcolor=\"#FBEFFB\"";
		}else{
			$bgColor = "";
		}
	?>
	<tr<?php echo $bgColor;?>>
		<td><?php echo $itemRows['sku_simple'];?></td>
		<td><?php echo $productName;?></td>
		<td><?php echo $gender;?></td>
		<td><?php echo $category;?></td>
		<td>
            <?php echo $this->va_input->getFieldInput($this->va_input->fields[$num]); $num++;?>
            <?php echo $this->va_input->getFieldInput($this->va_input->fields[$num]); $num++;?>
        </td>
	</tr>
	<?php } ?>
	</tbody>
	</table>
</div>
