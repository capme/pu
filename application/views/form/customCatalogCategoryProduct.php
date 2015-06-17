<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
    <script src="/assets/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf8" src="/assets/plugins/data-tables/jquery.dataTables.js"></script>
    <script>
        $(function(){
            $("#viewcategoryproduct").dataTable();
        })
    </script>
</head>
<body>
<div class="table-responsive">
    <table id="viewcategoryproduct" class="table table-striped" >
        <thead>
        <tr style="font-weight: bold;">
            <td>No</td>
            <td>SKU</td>
            <td>Product Id</td>
            <td>Position</td>
            <td>Manual Weight</td>
            <td>Result Index</td>
            <td>Updated At</td>
        </tr>
        </thead>
        </body>
        <?php
        $statList= array(
            0 =>array("Not Active", "warning"),
            1 =>array("Active", "success")
        );
        $no=0;
        foreach($value as $data):
        $manualweight=$statList[$data['manual_weight']];
        ?>

        <tr class="warning">
            <td><?php echo $no=$no+1; ?></td>
            <td><?php echo $data['sku'] ?></td>
            <td><?php echo $data['product_id'] ?></td>
            <td><?php echo $data['position'] ?></td>
            <td><?php echo '<span class="label label-sm label-'.($manualweight[1]).'">'.($manualweight[0]).'</span>' ?></td>
            <td><?php echo $data['result_index'] ?></td>
            <td><?php echo $data['updated_at'] ?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
</body>
</html>
