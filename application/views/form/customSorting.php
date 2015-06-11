<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
    <script src="/assets/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf8" src="/assets/plugins/data-tables/jquery.dataTables.js"></script>
    <script>
        $(function(){
            $("#viewcategory").dataTable();
        })
    </script>
</head>
<body>
<div class="table-responsive">
    <table id="viewcategory" class="table table-striped">
        <thead>
        <tr style="font-weight: bold;">
            <td>No</td>
            <td> Category Id</td>
            <td>Name</td>
            <td>Path</td>
            <td>URL Path</td>
            <td>Updated At</td>
            <td>Action</td>
        </tr>
        </thead>
        <tbody>
        <?php
        $no=0;
        foreach($value as $data):
            ?>
            <tr class="warning">
                <td><?php echo $no=$no+1; ?></td>
                <td><?php echo $data['category_id']?></td>
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
