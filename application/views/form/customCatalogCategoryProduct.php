<div class="table-responsive">
    <table class="table table-striped" >
        <tr style="font-weight: bold;">
            <td>No</td>
            <td>SKU</td>
            <td>Product Id</td>
            <td>Position</td>
            <td>Manual Weight</td>
            <td>Result Index</td>
            <td>Updated At</td>
        </tr>
        <?php
        $statList= array(
            0 =>array("Not Active", "warning"),
            1 =>array("Active", "success")
        );
        $no=0;
        foreach($value as $data):
        $manualweight=$statList[$data['manual_weight']];
        ?>
        <tbody>
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
