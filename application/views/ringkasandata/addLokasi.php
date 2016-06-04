<?php echo $this->va_input->getFieldInput($this->va_input->fields[0]);?>
<div class="panel panel-default" style="width:100%">
    <br />
    <table align="center" style="border: 1px solid rgba(0, 0, 0, 0.16);">
        <tr>
            <td>
                Distribusi Ke
            </td>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[1]);?>
            </td>
        </tr>
        <tr>
            <td>
                Nomor Lembar Kartu Leger Jalan
            </td>
            <td colspan="3">
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[2]);?>
            </td>
        </tr>
        <tr>
            <td>
                Kode Provinsi
            </td>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[3]);?>
            </td>
            <td>
                Nama Provinsi
            </td>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[4]);?>
            </td>
        </tr>
    </table>
    <br />
    <table align="center" style="border: 1px solid rgba(0, 0, 0, 0.16);">
        <tr>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[5]);?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[6]);?>
            </td>
        </tr>
    </table>
    <br />
</div>
