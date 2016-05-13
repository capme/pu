<input  name="exportorder[period1]" id="period1"  required/>
<input  name="exportorder[period2]" id="period2" required/>
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
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                        'Last 7 Days': [moment().subtract('days', 6), moment()],
                        'Last 30 Days': [moment().subtract('days', 29), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                    },
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
                    $("#period2").val(end.format("YYYY-MM-DD 23:59:59"));
                }
            );
        }();
    </script>
