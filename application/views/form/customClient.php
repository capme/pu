<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
    <script src="/assets/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf8" src="/assets/plugins/data-tables/jquery.dataTables.js"></script>
    <script>
        $(function(){
            $("#clientoptions").dataTable();
        })
    </script>
</head>
<body>
<div class="table-responsive">
    <table id="clientoptions" class="table table-striped">
        <thead>
	<tr style="font-weight: bold;">
        <td>No</td>
        <td>Name</td>
        <td>Value</td>
        <td>Remove</td>
  </tr>
        </thead>
        <tbody>
<?php
$no=1;
for($i = 0 ; $i < count($value); $i++){
$ex =explode('_',$value[$i]['option_name']);
$im = implode(" ", $ex); 
$bar = ucwords(strtolower($im));
?>
	<tr>
        <td><?php echo $no++?></td>
        <td style="width:30%"><?php echo $bar ?></td>
        <td><input class="form-control" type="text" value="<?php echo htmlspecialchars($value[$i]['option_value'])?>" name="clientoptions[option_value][<?php echo $value[$i]['id']?>]" style="width:80%"></td>
        <td><input type="checkbox" name="clientoptions[cek][<?php echo $i?>]" value="<?php echo $value[$i]['id']?>" ></td>
	</tr>
<?php }?>
        </tbody>
</table>
</div>
