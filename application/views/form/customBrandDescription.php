<div class="panel panel-default" style="width:50%">
    <table class="table" >
        <tr>
            <td>Brand Code</td>
            <td>Description ID</td>
            <td>Description EN</td>
        </tr>
        <?php for ($i=0; $i < count ($value); $i++){?>
            <tr>
                <td><?php echo $value[$i]['brand_code']; ?></td>
                <td><?php echo $value[$i]['description_id']; ?></td>
                <td><?php echo $value[$i]['description_en']; ?></td>
            </tr>
        <?php }?>
    </table>
</div>
