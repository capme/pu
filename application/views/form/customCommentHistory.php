<?php
$statList= array(
    0 =>array("New Request", "warning"),
    1 =>array("Approve", "success"),
    2 =>array("Processing","info"),
    3 =>array("Received","primary"),
    4 =>array("Canceled","danger")
);

foreach($value as $result){
    $date= date("d F Y", strtotime($result['created_at']));
    $time= date("h:i:s", strtotime($result['created_at']));
    if(!isset($result['status'])) {continue;}

    $status=$statList[$result['status']];
echo '<div class="tab-pane" id="tab_1_3" >
        <div class="row">
            <div class="col-md-6 user-info" style="width:100%;">
                <img alt="" src="'.site_url().'assets/img/avatar.png" class="img-responsive"/>
                <div class="details" style="width:80%; padding-left:inherit;">
                    <div style="margin-bottom: 10px;">
                        <b>'.$date.' </b>'.$time.'| <span class="label label-sm label-'.($status[1]).' label-mini">'.($status[0]).' </span>
                    </div>
                    <div style="margin-bottom: 10px;">
                         '.$result['note'].'
                    </div>
                    <div >
                    Updated by :  <a href="#">'.$result['username'].'</a>
                    </div>
                    <div><hr></div>
                </div>
            </div>
        </div>
</div>';}
?>