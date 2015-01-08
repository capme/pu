<div class="panel panel-default" style="width:50%">
  <table class="table" >
	<tr>
    <td>ITEM</td>
    <td>QTY</td>    
  </tr>
<?php 
$val=json_decode($value, true);
for ($row = 0; $row < count($val); $row++) {
	?>
	<tr>
	<td><?php echo $val[$row]['item']; ?></td>
	<td><?php echo $val[$row]['qty']; ?></td>    
	</tr>
	<?php 
	}
	?>
</table>
</div>

