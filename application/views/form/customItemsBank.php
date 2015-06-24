<?php
$strenc = urlencode($value);
$arr = unserialize(urldecode($strenc));
?>
<div class="table-responsive" style="width:50%">
    <table class="table table-striped" >
        <tr style="font-weight: bold;">
            <td>SKU</td>
            <td>QTY</td>
            <td>Weight</td>
        </tr>
        <?php if (!empty($arr)){foreach($arr as $item):?>
            <tr>
                <td><?php echo $item['name']; ?></td>
                <td><?php echo number_format($item['qty']); ?></td>
                <td><?php echo $item['weight'];?></td>
            </tr>
        <?php endforeach;}?>
    </table>
</div>
