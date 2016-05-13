<div class="panel panel-default" style="width:50%">
    <table class="table table-bordered" >
        <tr>
            <th>Attribute</th>
            <th>Value</th>
        </tr>
        <?php $ccInfo = json_decode($value, true); foreach($ccInfo as $attr => $value): ?>
        <tr>
            <td><b><?php $attr = str_replace("_", " ", $attr); echo ucwords($attr);?></b></td>
            <td><?php echo $value;?></td>
        </tr>
        <?php endforeach;?>
    </table>
</div>