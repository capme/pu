<div class="table-responsive">
    <table class="table table-striped">
        <tr style="font-weight: bold;">
            <td >No</td>
            <td>Name</td>
            <td>Path</td>
            <td>URL Path</td>
            <td>Updated At</td>
            <td>Action</td>
        </tr>
        <?php
        $no=0;
        foreach($value as $data):
            ?>
        <tbody>
            <tr class="warning">
                <td><?php echo $no=$no+1; ?></td>
                <td><?php echo $data['name'] ?></td>
                <td><?php echo $data['path'] ?></td>
                <td><?php echo $data['url_path'] ?></td>
                <td><?php echo $data['updated_at'] ?></td>
                <td><?php echo '<a href="'.site_url("sortingtool/viewcategory?category_id=".$data['category_id']).'&client='.$data['client_id'].'" class="btn btn-xs default"><i class="fa fa-search"></i> View</a> | <a href="'.site_url("sortingtool/manage?category_id=".$data['category_id']).'&client='.$data['client_id'].'" class="btn btn-xs default"><i class="fa fa-cog fa-fw"></i> Manage</a>'?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
