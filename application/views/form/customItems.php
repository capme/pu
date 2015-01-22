	<?php 
    $strenc = urlencode($value);    
	$arr = unserialize(urldecode($strenc));
	?>
<div class="panel panel-default" style="width:50%">
  <table class="table" >
	<tr>
    <td>SKU</td>
    <td>QTY</td>    
  </tr>
  <?php foreach($arr as $item):?>
  <tr>
    <td><?php echo $item['name']; ?></td>
	<td><?php echo $item['qty']; ?></td>    
  </tr>
  <?php endforeach;?>
  </table>
</div>
