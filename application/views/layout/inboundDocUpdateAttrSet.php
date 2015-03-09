<?php
$client = $_GET['client'];
$doc = $_GET['doc'];
$rows = $this->inbounddocument_m->getInboundInvItem($client, $doc);
?>

<?php echo $this->va_input->getFieldInput($this->va_input->fields[0]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[1]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[2]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[3]);?>
<div class="panel panel-default" style="width:100%">
	<table class="table" border=0>
	<thead>
	<tr>
		<th width="25%">SKU</th>
		<th width="25%">Product Name</th>
		<th width="15%">Category</th>
		<th width="35%">Attribute Set</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$num = 4;
	foreach($rows as $itemRows){
		$arr = explode(",", $itemRows['sku_description']);
		$productName = $arr[4];
		$category = $arr[2];
	?>
	<tr>
		<td><?php echo $itemRows['sku_simple'];?></td>
		<td><?php echo $productName;?></td>
		<td><?php echo $category;?></td>
		<td><?php echo $this->va_input->getFieldInput($this->va_input->fields[$num]);?></td>
	</tr>
	<?php
		$num++;
	}
	?>
	</tbody>
	</table>
</div>
