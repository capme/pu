<div class="panel panel-default" style="width:50%">
    <table class="table" >
        <tr>
            <td>Brand Code</td>
            <td>Description</td>
        </tr>
        <?php for ($i=0; $i < count ($value); $i++){?>
            <tr>
                <td><?php echo $value[$i]['brand_code']; ?></td>
                <td><?php echo $value[$i]['description']; ?></td>
            </tr>
        <?php }?>
    </table>
</div>
