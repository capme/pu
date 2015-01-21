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
		<form action="<?php echo site_url($this->router->class . "/" . "save")?>" method="POST" class="form-horizontal form-bordered form-row-stripped" id="<?php echo "form_".$this->va_input->getGroup(); ?>" enctype="multipart/form-data">
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
							<?php if(!$this->va_input->justView()):?>
							<button type="submit" class="btn green"><i class="fa fa-check"></i> Submit</button>
							<button type="button" class="btn default">Cancel</button>
							<?php else:?>
							<button type="button" class="btn green" onclick="history.back();"><i class="fa fa-check"></i> Back</button>
							<?php endif;?>
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
