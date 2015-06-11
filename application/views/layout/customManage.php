<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
    <script src="/assets/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf8" src="/assets/plugins/data-tables/jquery.dataTables.js"></script>
    <script>
        $(function(){
            $("#manage").dataTable();
        })
    </script>
</head>
<body>
<div class="table-responsive">
    <table id="manage"class="table table-striped">
        <thead>
        <tr>
            <th>No</th>
            <th>SKU</th>
            <th>Manual Weight</th>
        </tr>
        </thead>
        <?php
        echo $this->va_input->getFieldInput($this->va_input->fields[0]);
        echo $this->va_input->getFieldInput($this->va_input ->fields[1]);
        echo $this->va_input->getFieldInput($this->va_input->fields[2]);
        ?>
    <tbody>
    <?php
    $no=0;
    $client=$this->input->get("client");
    $id=$this->input->get("category_id");
    $data = $this->sortingtool_m->getCategory($id, $client);
    $value = $data->result_array();
    $x=3;
    foreach ($value as $result):?>
        <tr>
        <td><?php echo $no =$no+1?></td>
        <td><?php echo $result['sku']?></td>
        <td width="30%"><?php echo $this->va_input->getFieldInput($this->va_input->fields[$x]); $x++;?></td>
        </tr>
    <?php endforeach;?>
</tbody>
</table>
</div>
</body>
</html>
