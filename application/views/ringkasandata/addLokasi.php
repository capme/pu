<?php echo $this->va_input->getFieldInput($this->va_input->fields[0]);?>
<div class="panel panel-default" style="width:100%">
    <br />
    <table align="center" style="border: 1px solid rgba(0, 0, 0, 0.16);">
        <tr>
            <td colspan="4" bgcolor="#6495ed">&nbsp;</td>
        </tr>
        <tr>
            <td style="padding-left: 10px;padding-right: 10px">
                Distribusi Ke
            </td>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[1]);?>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 10px;padding-right: 10px">
                Nomor Lembar Kartu Leger Jalan
            </td>
            <td colspan="3">
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[2]);?>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 10px;padding-right: 10px">
                Kode Provinsi
            </td>
            <td>
                <?php echo $this->va_input->getFieldInput($this->va_input->fields[3]);?>
            </td>
            <td style="padding-left: 10px;padding-right: 10px">
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
            <td bgcolor="#6495ed">&nbsp;</td>
        </tr>
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
<?php echo $this->va_input->getFieldInput($this->va_input->fields[7]);?>