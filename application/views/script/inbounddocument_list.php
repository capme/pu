<script> 
function viewClick()
	{
	document.getElementById('view').disabled = true
 }
 
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
                    setTimeout(function(){
                        $('#datatable_ajax a.import3pl').click(function(e) {
                            if(confirm('Are you sure to import All NEW item into 3PL?')) {
                                return true;
                            } else {
                                return false;
                            }
                        })

                        $('#datatable_ajax a.importmage').click(function(e) {
                            if(confirm('Are you sure to import All NEW item into MAGE?')) {
                                return true;
                            } else {
                                return false;
                            }
                        })
                    }, 200);
                },
                onError: function(grid) {
                    // execute some code on network or other general error  
                },
                dataTable: {  // here you can define a typical datatable settings from http://datatables.net/usage/options 
                	"aoColumns": [
                          { "bSortable": true },
						  { "bSortable": true },
						  { "bSortable": false },
						  { "bSortable": false },
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
                    "aaSorting": [[ 1, "desc" ]] // set first column as a default sort by asc
                }
            });

            // handle group actionsubmit button click
            grid.getTableWrapper().on('click', '.table-group-action-submit', function(e){
                e.preventDefault();
                var action = $(".table-group-action-input", grid.getTableWrapper());
                if (action.val() != "" && grid.getSelectedRowsCount() > -1) 
				{
                    if ( action.val()==0||1)
					{	
						var records = grid.getSelectedRows();
						var ids = [];
						for (var i in records) {
							ids.push(records[i]["value"]);    
						}								
						var command = action.val();	
						grid.clearAjaxParams();
						if(action.val() == 0){
							document.location = "listinbounddoc/revise?ids="+ids.join(",") +"&command="+command;
						}else if(action.val() == 1){
							document.location = "listinbounddoc/uploadInboundForm?ids="+ids.join(",") +"&command="+command;
						}
					}					
                    grid.addAjaxParam("sAction", "group_action");
					grid.addAjaxParam("sGroupActionName", action.val());
					
                   grid.getDataTable().fnDraw();
                   grid.clearAjaxParams();
                } else if (action.val() == "") {
                    App.alert({type: 'danger', icon: 'warning', message: 'Please select an action', container: grid.getTableWrapper(), place: 'prepend'});
                } else if (grid.getSelectedRowsCount() === 0) {
                    App.alert({type: 'danger', icon: 'warning', message: 'No record selected', container: grid.getTableWrapper(), place: 'prepend'});
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