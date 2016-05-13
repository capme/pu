<?php
$ids = $_GET['ids'];
$arrIds = explode(",", $ids);
?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[1]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[2]);?>
<?php echo $this->va_input->getFieldInput($this->va_input->fields[3]);?>
<table width="100%">
<?php
foreach($arrIds as $row){

	$datas = $this->inbounddocument_m->getInboundDocumentRow($row);
	$dataClient = $this->client_m->getClientById($datas['client_id']);
	$dataClientRows = $dataClient->row_array();
	
	if($_GET['command'] == 1 and $datas['type'] == 1 and $datas['status'] <= 1) continue;
?>
<tr>
	<td width="25%">
		<center>
		<table cellpadding="2" cellspacing="2" width="100%">
		<tr>
			<td align="right">Client</td>
			<td width="10" align="center">:</td>
			<td><?php echo $dataClientRows['client_code'];?></td>
		</tr>	
		<tr>
			<td align="right">Doc Number</td>
			<td width="10" align="center">:</td>
			<td><?php echo $datas['doc_number'];?></td>
		</tr>	
		<tr>
			<td align="right">Note</td>
			<td width="10" align="center">:</td>
			<td><?php echo $datas['note'];?></td>
		</tr>	
		<tr>
			<td align="right">Created At</td>
			<td width="10" align="center">:</td>
			<td><?php echo $datas['created_at'];?></td>
		</tr>	
		<tr>
			<td align="right">Current File</td>
			<td width="10" align="center">:</td>
			<td><?php echo $datas['filename'];?></td>
		</tr>	
		</table>
		</center>
	</td>
	<td width="75%">
		<?php echo $this->va_input->getFieldInput($this->va_input->fields[0]);?>
	</td>
</tr>
<?php
}
?>
</table>