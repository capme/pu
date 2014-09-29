
<!-- start body_v.php -->
<?php
	$this->load->view('head_v.php');
?>

<!-- page specific styles -->

<!-- page specific scripts (on footer) -->

</head>
<body class="page-header-fixed">


<?php
	$this->load->view('header_v.php');
?>

<!-- BEGIN CONTAINER -->
<div class="page-container">
	<?php
		$this->load->view('sidebar_v');
	?>
	<!-- BEGIN CONTENT -->
<div class="page-content-wrapper">
		<div class="page-content" style="min-height:956px !important">
			<!-- BEGIN SAMPLE PORTLET CONFIGURATION MODAL FORM-->
			<div class="modal fade" id="portlet-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
							<h4 class="modal-title">Modal title</h4>
						</div>
						<div class="modal-body">
							 Widget settings form goes here
						</div>
						<div class="modal-footer">
							<button type="button" class="btn blue">Save changes</button>
							<button type="button" class="btn default" data-dismiss="modal">Close</button>
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /.modal -->
			<!-- END SAMPLE PORTLET CONFIGURATION MODAL FORM-->
			<!-- BEGIN STYLE CUSTOMIZER -->
<?php // $this->load->view('themeswitcher_v') ?>
			<!-- END STYLE CUSTOMIZER -->
			<!-- BEGIN PAGE HEADER-->
			<div class="row">
				<div class="col-md-12">
					<!-- BEGIN PAGE TITLE & BREADCRUMB-->
					<h3 class="page-title">
					Dashboard <small>dashboard &amp; statistics</small>
					</h3>
					<ul class="page-breadcrumb breadcrumb">
<?php $this->load->view('clientswitch_v') ?>
						<li>
							<i class="fa fa-home"></i>
							<a href="<?php echo site_url() ?>">
								Home
							</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<li>
								Dashboard
						</li>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-md-12">
					 Page content goes here
				</div>
			</div>
			<!-- END PAGE CONTENT-->
		</div>
	</div>
	<!-- END CONTENT -->
</div>
<!-- END CONTAINER -->


<!-- BEGIN JAVASCRIPTS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->


<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->


<!-- END PAGE LEVEL SCRIPTS -->
<!-- END JAVASCRIPTS -->

<?php
	$this->load->view('footer_v.php');
?>

<!-- eof body_v.php -->