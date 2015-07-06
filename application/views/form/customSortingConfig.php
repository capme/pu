<div class="panel panel-default" style="width:100%">
  <table class="table" >
	<tr>
    <td>Name</td>
    <td>Value</td>
  </tr>
<?php
for($i = 0 ; $i < count($value); $i++){
    $varName = str_replace('_',' ',$value[$i]['name']);
    $bar = ucwords(strtolower($varName));
    ?>
    <tr>
        <td style="width:30%"><?php echo $bar ?></td>
        <td>
            <?php if($value[$i]['name'] == 'push_to_magento'): ?>
                <select class="form-control select2_category" name="config[<?php echo $i; ?>][value]" id="<?php echo $id?>" style="width:40%">
                    <option <?php echo $value[$i]['value'] == 0 ? "selected" : ""?> value="0">Disabled</option>
                    <option <?php echo $value[$i]['value'] == 1 ? "selected" : ""?> value="1">Enabled</option>
                </select>
            <?php else: ?>
            <input class="form-control" type="number" min="0" value="<?php echo htmlspecialchars($value[$i]['value'])?>" name="config[<?php echo $i; ?>][value]" style="width:40%">
            <?php endif; ?>
            <input class="form-control" type="hidden" value="<?php echo htmlspecialchars($value[$i]['name'])?>" name="config[<?php echo $i; ?>][name]">
            <input class="form-control" type="hidden" value="<?php echo htmlspecialchars($value[$i]['id'])?>" name="config[<?php echo $i; ?>][id]">
        </td>
    </tr>
<?php }?>
</table>
</div>
