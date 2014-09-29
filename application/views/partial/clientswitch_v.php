<?php 
$this->load->model("client_m");
$curClient = $this->session->userdata("client");
$clientDetail = $this->client_m->getClientDetail( $curClient );
if(!empty($clientDetail)){
	return;
}
?>

<?php 
if($this->auth_m->is_admin()) {
	$clients = $this->client_m->getClients();
?>
						<li class="btn-group" id="clientswitch">
							<button type="button" class="btn blue dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
							<span>
							<?php if(!empty($client)) { ?>
								Client: <?php echo $client['name'] ?>
							<?php } else { ?>
								SELECT CLIENT
							<?php } ?>
							</span>
							<i class="fa fa-angle-down"></i>
							</button>
							
							<ul id="client-switch" class="dropdown-menu pull-right" role="menu">
								<?php foreach($clients as $row): if(!$row['hasGA'] && !$row['hasEC']) continue;?>
								<li>
									<a data-client="<?php echo $row['name']?>" href="#">
										<?php echo $row['name']?>
									</a>
								</li>
								<?php endforeach; ?>
								<?php /*<li>
									<a data-client="LOLABOX" href="#">
										LOLABOX
									</a>
								</li>
								<li>
									<a data-client="SHOOTYOURDREAM" href="#">
										SHOOTYOURDREAM
									</a>
								</li>
								<li>
									<a data-client="LEECOOPER" href="#">
										LEECOOPER
									</a>
								</li>
								
								*/ ?>
								<li class="divider">
								</li>
								<li>
									<a data-client="" href="#">
										View All
									</a>
								</li>
							</ul>
						</li>


<?php } else if ($this->auth_m->is_client()) { // do nothing ?>

<?php } ?>