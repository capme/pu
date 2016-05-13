
<!-- start body_v.php -->
<?php
	$this->load->view('partial/head_v.php');
?>

<!-- page specific styles -->
<link rel="stylesheet" href="/js/jquery_datatables/css/jquery.dataTables.css" />
<link rel="stylesheet" href="/js/jquery_datatables/css/jquery.dataTables_themeroller.css" />
<style>
#example_filter input {
	min-width: 500px;
}
</style>
<!-- page specific scripts (on footer) -->

</head>
<body class="page-header-fixed page-sidebar-closed">


<?php
	$this->load->view('partial/header_v.php');
?>

<!-- BEGIN CONTAINER -->
<div class="page-container">
	<?php
		$this->load->view('partial/sidebar_v');
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
					<h3 class="page-title"><?php echo $pageTitle?></h3>
					<ul class="page-breadcrumb breadcrumb">
						<?php if(isset($showClientSwitch) && $showClientSwitch == true) $this->load->view('clientswitch_v') ?>
						<li>
							<i class="fa fa-home"></i>
							<a href="<?php echo site_url() ?>">
								Home
							</a>
							<i class="fa fa-angle-right"></i>
						</li>
						<?php $l = 0; foreach($breadcrumb as $_title => $_link): $l++;?>
						<li>
						<?php if($_link):?>
							<a href=<?php echo site_url($_link)?>><?php echo $_title?></a>
						<?php else:?>
							<?php echo $_title?>
						<?php endif;?>
						<?php if($l != sizeof($breadcrumb)):?><i class="fa fa-angle-right"></i><?php endif;?>
						</li>
						<?php endforeach;?>
					</ul>
					<!-- END PAGE TITLE & BREADCRUMB-->
				</div>
			</div>
			<!-- END PAGE HEADER-->
			<!-- BEGIN PAGE CONTENT-->
			<div class="row">
				<div class="col-md-12">
					<!-- START PAGE CONTENT -->
					<?php $this->load->view($content); ?>
					<!-- ENDT PAGE CONTENT -->
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

<script type="text/javascript" src="/js/jquery_datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="/js/jquery_datatables/jquery.dataTables.fnSetFilteringDelay.js"></script>
<script type="text/javascript" src="/js/jquery_datatables/jquery.dataTables.fnFilterClear.js"></script>

<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->


<!-- END PAGE LEVEL SCRIPTS -->
<!-- END JAVASCRIPTS -->

<?php
	$this->load->view('partial/footer_v.php');
?>

<!-- eof body_v.php -->