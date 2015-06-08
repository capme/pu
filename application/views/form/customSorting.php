<div class="table-responsive">
    <table class="table table-striped" >
        <tr>
            <td>Name</td>
            <td>Path</td>
            <td>URL Path</td>
            <td>Updated At</td>
            <td>Action</td>
        </tr>
        <?php
        foreach($value as $data):
            ?>
        <tbody>
            <tr class="warning">
                <td><?php echo $data['name'] ?></td>
                <td><?php echo $data['path'] ?></td>
                <td><?php echo $data['url_path'] ?></td>
                <td><?php echo $data['updated_at'] ?></td>
                <td><?php echo '<a href="'.site_url("sortingtool/viewcategory?id=".$data['category_id']).'&client='.$data['client_id'].'" class="btn btn-xs default"><i class="fa fa-search"></i> View</a>'?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
