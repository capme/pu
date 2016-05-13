<?php
//print_r($value);
?>
<div class="table-responsive">
    <table class="table table-striped">
	<tr>
    <td style=" font:bold 12px/30px Arial;">Code</td>
    <td style=" font:bold 12px/30px Arial;">Brand</td>
    <td style=" font:bold 12px/30px Arial;">Inbound Type</td>
    <td style=" font:bold 12px/30px Arial;">Remove</td>
  </tr> 
<?php
$idInboundType=0;
$inboundtype=array();
for($i = 0 ; $i < count($value); $i++){
    if($value[$i]['option_name'] == "brand_code"){
        //data brand code
        $brand = json_decode($value[$i]['option_value'], true);
        $id = $value[$i]['id'];

    }elseif($value[$i]['option_name'] == "inbound_type"){
        //data inbound type
        $inboundtype = json_decode($value[$i]['option_value'], true);
        $idInboundType = $value[$i]['id'];
    }
}


?>
    <input type="hidden" name="brandcode[id]" value="<?php echo $id?>">
    <input type="hidden" name="brandcode[idinboundtype]" value="<?php echo $idInboundType?>">
<?php
$brands=array_values($brand);
$key=array_keys($brand);
$inboundtypes=array_values($inboundtype);
for($a=0; $a < count($brand);$a++){
?>	<tr>
        <td><input class="form-control" type="text" value="<?php echo $key[$a];?>" name="brandcode[key][<?php echo $a?>]" style="width:80%" required></td>
        <td><input class="form-control" type="text" value="<?php echo $brands[$a]?>" name="brandcode[brands][<?php echo $a ?>]" style="width:80%" required></td>
        <td>
            <?php
            if(isset($inboundtypes[$a])) {
                ?>
                <select name="brandcode[inboundtype][<?php echo $a ?>]" class="form-control">
                    <option value="normal"<?php if ($inboundtypes[$a] == "normal") echo " selected";?>>Normal</option>
                    <option value="scrapping"<?php if ($inboundtypes[$a] == "scrapping") echo " selected";?>>Scrapping</option>
                    <option value="crossdocking"<?php if ($inboundtypes[$a] == "crossdocking") echo " selected";?>>Cross Docking</option>
                </select>
            <?php
            }else{
                ?>
                <select name="brandcode[inboundtype][<?php echo $a ?>]" class="form-control">
                    <option value="normal">Normal</option>
                    <option value="scrapping">Scrapping</option>
                    <option value="crossdocking">Cross Docking</option>
                </select>
            <?php
            }
            ?>
        </td>
        <td><input type="checkbox"  name="brandcode[cek][<?php echo $a?>]" value="<?php echo $brands[$a]?>"></td>
	</tr>	
<?php
}
?>
	<tr>
		<td id="key0"> </td>
		<td id ="brands0"> </td>
        <td id ="inboundtype0"> </td>
    </tr>
</table>
</div>
<p><a href="javascript:action();" class="btn btn-xs default"><i class="glyphicon glyphicon-plus" ></i> Add Brand Code</a>
<a href="javascript:removekey();" class="btn btn-xs default"><i class="glyphicon glyphicon-trash" ></i> Remove</a></p>
