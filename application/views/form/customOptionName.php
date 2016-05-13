<div class="panel panel-default" style="width:100%">
<input class="form-control" type="text" value=""  name="<?php echo $group."[".$name."][0]";?>" id="name">
</div>

<div><span class="label label-default" style="font-size: large;">OR</span></div>
<br>
<div>
<select name="<?php echo $group."[".$name."][1]";?>" class="select2-container form-control select2_category" id="namea">
    <option value="">Select Name</option>
    <?php
        foreach($value as $row){
            $row['option_name'];
            echo "<option value=".$row['option_name'].">".$row['option_name']."</option>";
        }
    ?>
</select>
</div>