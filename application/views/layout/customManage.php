<div class="table-responsive">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>No</th>
            <th>SKU</th>
            <th>Posistion</th>
            <th>Manual Weight</th>
        </tr>
        </thead>
    <tbody>
    <?php
    $no=0;
    $client=$this->input->get("client");
    $id=$this->input->get("category_id");
    $data = $this->sortingtool_m->getCategory($id, $client);
    $value = $data->result_array();
    echo $this->va_input->getFieldInput($this->va_input->fields[0]);
    echo $this->va_input->getFieldInput($this->va_input->fields[1]);
    echo $this->va_input->getFieldInput($this->va_input->fields[2]);
    $x=3;
    foreach ($value as $result):?>
        <tr>
        <td><?php echo $no =$no+1?></td>
        <td><?php echo $result['sku']?></td>
        <td width="30%"><?php echo $this->va_input->getFieldInput($this->va_input->fields[$x]); $x++;?></td>
        <td width="30%"><?php echo $this->va_input->getFieldInput($this->va_input->fields[$x]); $x++;?></td>
        </tr>
    <?php endforeach;?>
</tbody>
</table>
</div>
