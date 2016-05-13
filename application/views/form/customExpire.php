<head>
    <link rel="stylesheet" type="text/css" href="/assets/plugins/bootstrap-datetimepicker/css/datetimepicker.css"/>
    <script type="text/javascript" src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script src="/assets/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            $('#date').datetimepicker({
                formatTime:'H:i',
                formatDate:'d.m.Y',
                timepickerScrollbar:true
            });
        });
    </script>
</head>

<input type="text" name="expiredorder[date]" id="date" value="<?php echo $value['expired_date']?>" class="form-control"/>


