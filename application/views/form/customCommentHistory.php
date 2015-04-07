<?php
$statList= array(
    0 =>array("New Request", "warning"),
    1 =>array("Approve", "success"),
    2 =>array("Cancel","danger")
);
foreach($value as $result){
    $status=$statList[$result['status']];
echo '<div class="tab-pane" id="tab_1_3">
    <div  style="height: 100px;" data-always-visible="1" data-rail-visible1="1">
        <div class="row">
            <div class="col-md-6 user-info">
                <img alt="" src="'.site_url().'assets/img/avatar.png" class="img-responsive"/>
                <div class="details">
                    <div>
                        <a href="#">
                        '.$result['username'].'
                        </a>
                        |
						<span class="label label-sm label-'.($status[1]).' label-mini">'.($status[0]).'
						</span>
                    </div>
                    <br>
                    <div>
                        '.$result['created_at'].'
                    </div>
                    <br>
                    <div>
                        '.$result['note'].'
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>';
}?>