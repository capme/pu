<script>
    var TableAjax = function () {

        var initPickers = function () {
            //init date pickers
            $('.date-picker').datepicker({
                rtl: App.isRTL(),
                autoclose: true
            });
        }

        var handleRecords = function() {

            var grid = new Datatable();
            grid.init({
                src: $("#datatable_ajax"),
                onSuccess: function(grid) {
                    // execute some code after table records loaded
                },
                onError: function(grid) {
                    // execute some code on network or other general error
                },
                dataTable: {  // here you can define a typical datatable settings from http://datatables.net/usage/options
                    "aoColumns": [
                        { "bSortable": false },
                        null, //{ "bSortable": false },
                        { "bSortable": false },
                        { "bSortable": false },
                        { "bSortable": false }
                    ],
                    /*
                     By default the ajax datatable's layout is horizontally scrollable and this can cause an issue of dropdown menu is used in the table rows which.
                     Use below "sDom" value for the datatable layout if you want to have a dropdown menu for each row in the datatable. But this disables the horizontal scroll.
                     */
                    //"sDom" : "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>r>>",

                    "aLengthMenu": [
                        [20, 50, 100, 150, -1],
                        [20, 50, 100, 150, "All"] // change per page values here
                    ],
                    "iDisplayLength": 20, // default record count per page
                    "bServerSide": true, // server side processing
                    "sAjaxSource": "<?php echo $ajaxSource?>", // ajax source /metronic_v2.0/v2.0/admin/template/demo/table_ajax.php
                    "aaSorting": [[ 1, "DESC" ]] // set first column as a default sort by asc
                }
            });

            // handle group actionsubmit button click
            grid.getTableWrapper().on('click', '.table-group-action-submit', function(e){
               e.preventDefault();
                var action = $(".table-group-action-input", grid.getTableWrapper());
                if (action.val() != "" && grid.getSelectedRowsCount() > -1)                {
                    if(action.val() == 1){
                        document.location = "colormap/add";
                    }else if(action.val() == 2){
                        document.location = "colormap/export";
                    }
                }
            });
        }

        return {

            //main function to initiate the module
            init: function () {

                initPickers();
                handleRecords();
            }

        };

    }();
    jQuery(document).ready(function() {
        TableAjax.init();
    });
</script>