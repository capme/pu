<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2.css"/>
<link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2-metronic.css"/>
<!-- END PAGE LEVEL SCRIPTS -->


<!-- START FORM BOX -->
<div class="portlet box blue ">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-reorder"></i><?php echo $formTitle?>
		</div>
		<div class="tools">
			<a href="javascript:;" class="collapse">
			</a>
			<!-- a href="#portlet-config" data-toggle="modal" class="config">
			</a>
			<a href="javascript:;" class="reload">
			</a>
			<a href="javascript:;" class="remove">
			</a-->
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
				<?php /*
				<div class="form-group">
					<label class="control-label col-md-3">First Name</label>
					<div class="col-md-9">
						<input type="text" placeholder="small" class="form-control"/>
						<span class="help-block">
							 This is inline help
						</span>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Last Name</label>
					<div class="col-md-9">
						<input type="text" placeholder="medium" class="form-control"/>
						<span class="help-block">
							 This is inline help
						</span>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Gender</label>
					<div class="col-md-9">
						<select class="form-control">
							<option value="">Male</option>
							<option value="">Female</option>
						</select>
						<span class="help-block">
							 Select your gender.
						</span>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Date of Birth</label>
					<div class="col-md-9">
						<input type="text" class="form-control" placeholder="dd/mm/yyyy">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Category</label>
					<div class="col-md-9">
						<select class="form-control select2_category">
							<option value="Category 1">Category 1</option>
							<option value="Category 2">Category 2</option>
							<option value="Category 3">Category 5</option>
							<option value="Category 4">Category 4</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Multi-Value Select</label>
					<div class="col-md-9">
						<select class="form-control select2_sample1" multiple>
							<optgroup label="NFC EAST">
							<option>Dallas Cowboys</option>
							<option>New York Giants</option>
							<option>Philadelphia Eagles</option>
							<option>Washington Redskins</option>
							</optgroup>
							<optgroup label="NFC NORTH">
							<option>Chicago Bears</option>
							<option>Detroit Lions</option>
							<option>Green Bay Packers</option>
							<option>Minnesota Vikings</option>
							</optgroup>
							<optgroup label="NFC SOUTH">
							<option>Atlanta Falcons</option>
							<option>Carolina Panthers</option>
							<option>New Orleans Saints</option>
							<option>Tampa Bay Buccaneers</option>
							</optgroup>
							<optgroup label="NFC WEST">
							<option>Arizona Cardinals</option>
							<option>St. Louis Rams</option>
							<option>San Francisco 49ers</option>
							<option>Seattle Seahawks</option>
							</optgroup>
							<optgroup label="AFC EAST">
							<option>Buffalo Bills</option>
							<option>Miami Dolphins</option>
							<option>New England Patriots</option>
							<option>New York Jets</option>
							</optgroup>
							<optgroup label="AFC NORTH">
							<option>Baltimore Ravens</option>
							<option>Cincinnati Bengals</option>
							<option>Cleveland Browns</option>
							<option>Pittsburgh Steelers</option>
							</optgroup>
							<optgroup label="AFC SOUTH">
							<option>Houston Texans</option>
							<option>Indianapolis Colts</option>
							<option>Jacksonville Jaguars</option>
							<option>Tennessee Titans</option>
							</optgroup>
							<optgroup label="AFC WEST">
							<option>Denver Broncos</option>
							<option>Kansas City Chiefs</option>
							<option>Oakland Raiders</option>
							<option>San Diego Chargers</option>
							</optgroup>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Loading Data</label>
					<div class="col-md-9">
						<input type="hidden" class="form-control select2_sample2">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Tags Support List</label>
					<div class="col-md-9">
						<input type="hidden" class="form-control select2_sample3" value="red, blue">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Membership</label>
					<div class="col-md-9">
						<div class="radio-list">
							<label>
							<input type="radio" name="optionsRadios2" value="option1"/>
							Free </label>
							<label>
							<input type="radio" name="optionsRadios2" value="option2" checked/>
							Professional </label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Street</label>
					<div class="col-md-9">
						<input type="text" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">City</label>
					<div class="col-md-9">
						<input type="text" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">State</label>
					<div class="col-md-9">
						<input type="text" class="form-control">
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Post Code</label>
					<div class="col-md-9">
						<input type="text" class="form-control">
					</div>
				</div>
				<div class="form-group last">
					<label class="control-label col-md-3">Country</label>
					<div class="col-md-9">
						<select class="form-control">
						</select>
					</div>
				</div>
				*/ ?>
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
<!-- END FORM BOX -->

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
