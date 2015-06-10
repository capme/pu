<table class="table" border=0>
        <thead>
        <tr>
            <th width="25%">SKU</th>
            <th width="20%">Posistion</th>
            <th width="15%">Manual Weight</th>
        </tr>
        </thead>
    <tbody>
    <?php
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
        <td><?php echo $result['sku']?></td>
        <td><?php echo $this->va_input->getFieldInput($this->va_input->fields[$x]); $x++;?></td>
        <td><?php echo $this->va_input->getFieldInput($this->va_input->fields[$x]); $x++;?></td>
        </tr>
    <?php endforeach;?>
</tbody>
</table>
