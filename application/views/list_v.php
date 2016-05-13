<link rel="stylesheet" href="/assets/plugins/data-tables/DT_bootstrap.css"/>
<link rel="stylesheet" type="text/css" href="/assets/plugins/bootstrap-datepicker/css/datepicker.css"/>

<div class="portlet">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-shopping-cart"></i><?php echo $this->va_list->getListName()?>
		</div>
		<div class="actions">
			<?php if($this->va_list->isAddPluginActive()):?>
			<a href="<?php echo $this->va_list->getAddUrl();?>" class="btn default yellow-stripe">
				<i class="fa fa-plus"></i>
				<span class="hidden-480">
					 <?php echo $this->va_list->getAddLabel()?>
				</span>
			</a>
			<?php endif;?>
			<?php /*?>
			<div class="btn-group">
				<a class="btn default yellow-stripe" href="#" data-toggle="dropdown">
					<i class="fa fa-share"></i>
					<span class="hidden-480">
						 Tools
					</span>
					<i class="fa fa-angle-down"></i>
				</a>
				<ul class="dropdown-menu pull-right">
					<li>
						<a href="#">
							 Export to Excel
						</a>
					</li>
					<li>
						<a href="#">
							 Export to CSV
						</a>
					</li>
					<li>
						<a href="#">
							 Export to XML
						</a>
					</li>
					<li class="divider">
					</li>
					<li>
						<a href="#">
							 Print Invoices
						</a>
					</li>
				</ul>
			</div>
			*/ ?>
		</div>
	</div>
	<div class="portlet-body">
		<div class="table-container">
			<?php if($this->va_list->isMassActive()):?>
			<div class="table-actions-wrapper">
				<span>
				</span>
				<select class="table-group-action-input form-control input-inline input-small input-sm">
					<option value="">Select...</option>
					<?php foreach($this->va_list->getMassAction() as $value => $label):?>
					<option value="<?php echo $value?>"><?php echo $label?></option>
					<?php endforeach;?>
				</select>
				<button class="btn btn-sm yellow table-group-action-submit"><i class="fa fa-check"></i> Submit</button>
			</div>
			<?php endif;?>
			<table class="table table-striped table-bordered table-hover" id="datatable_ajax">
			<thead>
			<tr role="row" class="heading">
				<?php echo $this->va_list->renderHeading()?>
			</tr>
			<tr role="row" class="filter">
				<?php echo $this->va_list->renderFilter();?>
				<?php /*
				<td>
				</td>
				<td>
					<input type="text" class="form-control form-filter input-sm" name="order_id">
				</td>
				<td>
					<div class="input-group date date-picker margin-bottom-5" data-date-format="dd/mm/yyyy">
						<input type="text" class="form-control form-filter input-sm" readonly name="order_date_from" placeholder="From">
						<span class="input-group-btn">
							<button class="btn btn-sm default" type="button"><i class="fa fa-calendar"></i></button>
						</span>
					</div>
					<div class="input-group date date-picker" data-date-format="dd/mm/yyyy">
						<input type="text" class="form-control form-filter input-sm" readonly name="order_date_to" placeholder="To">
						<span class="input-group-btn">
							<button class="btn btn-sm default" type="button"><i class="fa fa-calendar"></i></button>
						</span>
					</div>
				</td>
				<td>
					<input type="text" class="form-control form-filter input-sm" name="order_customer_name">
				</td>
				<td>
					<input type="text" class="form-control form-filter input-sm" name="order_ship_to">
				</td>
				<td>
					<div class="margin-bottom-5">
						<input type="text" class="form-control form-filter input-sm" name="order_price_from" placeholder="From"/>
					</div>
					<input type="text" class="form-control form-filter input-sm" name="order_price_to" placeholder="To"/>
				</td>
				<td>
					<div class="margin-bottom-5">
						<input type="text" class="form-control form-filter input-sm margin-bottom-5 clearfix" name="order_quantity_from" placeholder="From"/>
					</div>
					<input type="text" class="form-control form-filter input-sm" name="order_quantity_to" placeholder="To"/>
				</td>
				<td>
					<select name="order_status" class="form-control form-filter input-sm">
						<option value="">Select...</option>
						<option value="pending">Pending</option>
						<option value="closed">Closed</option>
						<option value="hold">On Hold</option>
						<option value="fraud">Fraud</option>
					</select>
				</td>
				<td>
					<div class="margin-bottom-5">
						<button class="btn btn-sm yellow filter-submit margin-bottom"><i class="fa fa-search"></i> Search</button>
					</div>
					<button class="btn btn-sm red filter-cancel"><i class="fa fa-times"></i> Reset</button>
				</td>
				*/ ?>
			</tr>
			</thead>
			<tbody>
			</tbody>
			</table>
		</div>
	</div>
</div>


<script type="text/javascript" src="/assets/plugins/data-tables/jquery.dataTables.js"></script>
<script type="text/javascript" src="/assets/plugins/data-tables/DT_bootstrap.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>

<script src="/assets/scripts/core/datatable.js"></script>
<?php echo $script?>
<script>
$(document).ready(function(){
	$(".dataTable .filter").find("input[type='text']").keyup(function(e){
		if(e.keyCode == 13) {
			$(".dataTable .filter .filter-submit").trigger("click")
		}
	})
})
</script>