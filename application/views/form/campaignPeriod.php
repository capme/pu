<input  name="campaign[start_date]" id="period1"  required/> To
<input  name="campaign[end_date]" id="period2" required/>
<div id="period" class="btn default">
    <i class="fa fa-calendar"></i>
    <span></span>
    <b class="fa fa-angle-down"></b>
</div>
<link rel="stylesheet" type="text/css" href="/assets/plugins/bootstrap-datepicker/css/datepicker.css"/>
<script type="text/javascript" src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap-daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
<script type="text/javascript">
    var handleDateRangePickers = function () {
        $('#period').daterangepicker({
                opens: (App.isRTL() ? 'right' : 'left'),
                startDate: moment().subtract('days', 29),
                endDate: moment(),
                showDropdowns: true,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                buttonClasses: ['btn'],
                applyClass: 'green',
                cancelClass: 'default',
                format: 'MM/DD/YYYY',
                separator: ' to ',
                locale: {
                    applyLabel: 'Apply',
                    fromLabel: 'From',
                    toLabel: 'To',
                    customRangeLabel: 'Custom Range',
                    daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    firstDay: 1
                }
            },

            function (start, end) {
                $("#period1").val(start.format("YYYY-MM-DD 00:00:00"));
                $("#period2").val(end.format("YYYY-MM-DD 00:00:00"));
            }
        );
    }();
</script>
