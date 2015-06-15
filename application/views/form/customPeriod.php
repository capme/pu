<html>
<head>
    <link rel="stylesheet" type="text/css" href="/assets/plugins/bootstrap-datepicker/css/datepicker.css"/>
    <script type="text/javascript" src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#period1').datepicker({
                dateFormat:'yy-mm-dd',
                changeMonth: true,
                changeYear: true
                });
            $('#period2').datepicker({
                dateFormat:'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        });
    </script>
</head>
<body>
    <table>
    <thead>
    <tr>
        <td><input data-provide="datepicker" name="exportorder[period1]" id="period1"  class="datepicker" data-date-format="yyyy-mm-dd" required/><td>
        <td></td>
        <td><span class="label label-default"> To </span></td>
        <td></td>
        <td><input data-provide="datepicker" name="exportorder[period2]" id="period2"  class="datepicker" data-date-format="yyyy-mm-dd" required/></td>
    </tr>
    </thead>
</table>
</body>
</html>