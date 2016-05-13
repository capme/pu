<div class="table-responsive">
    <table class="table table-striped">
        <tr style="font-weight: bold;">
            <td>No</td>
            <td>Brand Code</td>
            <td>Attribute Set</td>
            <td>Brand Size</td>
            <td>Brand Size Sytem</td>
            <td>Paraplou Size</td>
            <td>Position</td>
        </tr>
        <?php
        $no=1;
        foreach($value as $sizechart){
            ?>
            <tr>
                <td><?php echo $no++?></td>
                <td><?php echo $sizechart['brand_code'] ?></td>
                <td><?php echo $sizechart['attribute_set'] ?></td>
                <td><?php echo $sizechart['brand_size'] ?></td>
                <td><?php echo $sizechart['brand_size_system'] ?></td>
                <td><?php echo $sizechart['paraplou_size'] ?></td>
                <td><?php echo $sizechart['position'] ?></td>
                </tr>
        <?php }?>
    </table>
</div>
