<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2-metronic.css"/>
<!-- END PAGE LEVEL SCRIPTS -->


<form class="form-horizontal form-row-seperated" action="<?php echo site_url($this->router->class . "/" . "save")?>" method="POST" id="<?php echo "form_".$this->va_input->getGroup(); ?>">
	<div class="portlet">
		<div class="portlet-title">
			<div class="caption">
				<i class="fa fa-shopping-cart"></i>Test Product
			</div>
			<div class="actions btn-set">
				<button class="btn default" type="reset"><i class="fa fa-reply"></i> Reset</button>
				<button class="btn green" type="submit"><i class="fa fa-check"></i> Save</button>
				<button class="btn green" type="submit"><i class="fa fa-check-circle"></i> Save & Continue Edit</button>
			</div>

		</div>
		<div class="portlet-body">
			<div class="tabbable">
				<ul class="nav nav-tabs">
					<?php echo $this->va_input->renderGroupTab()?>
				</ul>
				<div class="tab-content no-space">
					<?php echo $this->va_input->renderField();?>
				</div>
			</div>
		</div>
	</div>
</form>


<?php 
/*
<div class="tabbable tabbable-custom boxless tabbable-reversed">
	<ul class="nav nav-tabs">
		<?php echo $this->va_input->renderGroupTab()?>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="tab_0">
			<div class="portlet box blue ">
				<div class="portlet-title">
					<div class="caption">
						<i class="fa fa-reorder"></i><?php echo $formTitle?>
					</div>
					<div class="tools">
						<a href="javascript:;" class="collapse">
						</a>
					</div>
				</div>
				<div class="portlet-body form">
					<!-- BEGIN FORM-->
					<form action="<?php echo site_url($this->router->class . "/" . "save")?>" method="POST" class="form-horizontal form-bordered form-row-stripped" id="<?php echo "form_".$this->va_input->getGroup(); ?>">
						<div class="form-body">
							<div class="alert alert-danger display-hide">
								<button class="close" data-close="alert"></button>
								You have some form errors. Please check below.
							</div>
							<div class="alert alert-success display-hide">
								<button class="close" data-close="alert"></button>
								Your form validation is successful!
							</div>
							
							<?php echo $this->va_input->renderField();?>
						</div>
						<div class="form-actions fluid">
							<div class="row">
								<div class="col-md-12">
									<div class="col-md-offset-3 col-md-9">
										<button type="submit" class="btn green"><i class="fa fa-check"></i> Submit</button>
										<button type="button" class="btn default">Cancel</button>
									</div>
								</div>
							</div>
						</div>
					</form>
					<!-- END FORM-->
				</div>
			</div>
		</div>
		<div class="tab-pane" id="tab_1">
			<div class="portlet box blue">
				<div class="portlet-title">
					<div class="caption">
						<i class="fa fa-reorder"></i>Form Sample
					</div>
					<div class="tools">
						<a href="javascript:;" class="collapse">
						</a>
						<a href="#portlet-config" data-toggle="modal" class="config">
						</a>
						<a href="javascript:;" class="reload">
						</a>
						<a href="javascript:;" class="remove">
						</a>
					</div>
				</div>
				<div class="portlet-body form">
					<!-- BEGIN FORM-->
					<form action="#" class="horizontal-form">
						<div class="form-body">
							<h3 class="form-section">Person Info</h3>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">First Name</label>
										<input type="text" id="firstName" class="form-control" placeholder="Chee Kin">
										<span class="help-block">
											 This is inline help
										</span>
									</div>
								</div>
								<!--/span-->
								<div class="col-md-6">
									<div class="form-group has-error">
										<label class="control-label">Last Name</label>
										<input type="text" id="lastName" class="form-control" placeholder="Lim">
										<span class="help-block">
											 This field has error.
										</span>
									</div>
								</div>
								<!--/span-->
							</div>
							<!--/row-->
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Gender</label>
										<select class="form-control">
											<option value="">Male</option>
											<option value="">Female</option>
										</select>
										<span class="help-block">
											 Select your gender
										</span>
									</div>
								</div>
								<!--/span-->
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Date of Birth</label>
										<input type="text" class="form-control" placeholder="dd/mm/yyyy">
									</div>
								</div>
								<!--/span-->
							</div>
							<!--/row-->
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Category</label>
										<select class="select2_category form-control" data-placeholder="Choose a Category" tabindex="1">
											<option value="Category 1">Category 1</option>
											<option value="Category 2">Category 2</option>
											<option value="Category 3">Category 5</option>
											<option value="Category 4">Category 4</option>
										</select>
									</div>
								</div>
								<!--/span-->
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Membership</label>
										<div class="radio-list">
											<label class="radio-inline">
											<input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked> Option 1 </label>
											<label class="radio-inline">
											<input type="radio" name="optionsRadios" id="optionsRadios2" value="option2"> Option 2 </label>
										</div>
									</div>
								</div>
								<!--/span-->
							</div>
							<!--/row-->
							<h3 class="form-section">Address</h3>
							<div class="row">
								<div class="col-md-12 ">
									<div class="form-group">
										<label>Street</label>
										<input type="text" class="form-control">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>City</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<!--/span-->
								<div class="col-md-6">
									<div class="form-group">
										<label>State</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<!--/span-->
							</div>
							<!--/row-->
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Post Code</label>
										<input type="text" class="form-control">
									</div>
								</div>
								<!--/span-->
								<div class="col-md-6">
									<div class="form-group">
										<label>Country</label>
										<select class="form-control">
										</select>
									</div>
								</div>
								<!--/span-->
							</div>
						</div>
						<div class="form-actions right">
							<button type="button" class="btn default">Cancel</button>
							<button type="submit" class="btn blue"><i class="fa fa-check"></i> Save</button>
						</div>
					</form>
					<!-- END FORM-->
				</div>
			</div>
		</div>
	</div>
</div>
*/
?>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="/assets/plugins/select2/select2.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-validation/dist/additional-methods.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/assets/scripts/custom/form-samples.js"></script>
<script>
$.validator.addMethod("pwcheck", function(value) {
   return /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$]{5,}$/.test(value)
});

$.validator.addMethod("ipaddr", function(value) {
	return /^(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))\.(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))\.(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))\.(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))$/.test(value);
});

$(document).ready(function(){
	FormSamples.init();
	FormValidation.init();
})
</script>
<?php echo $script?>
<!-- END PAGE LEVEL SCRIPTS -->
