<div class="table-responsive" style="width:50%">
  <table class="table table-striped" >
	<tr style="font-weight: bold;">
    <td>ITEM (SKU)</td>
    <td>QTY</td>    
  </tr>
<?php 
$val=json_decode($value, true);
for ($row = 0; $row < count($val); $row++) {
	?>
	<tr>
	<td><?php echo $val[$row]['item']; ?></td>
	<td><?php echo number_format($val[$row]['qty']); ?></td>
	</tr>
	<?php 
	}
	?>
</table>
</div>

