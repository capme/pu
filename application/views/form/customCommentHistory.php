<?php
$statList= array(
    1 => array(
        0 =>array("New Request", "warning"),
        1 =>array("Approve", "success"),
        2 =>array("Cancel","danger"),
        3 =>array("Approve", "success"),
        4 =>array("Cancel","danger"),
    ),
    2 =>array(
        0 =>array("New Request", "warning"),
        1 =>array("Approve", "success"),
        2 =>array("Cancel","danger")
    ),
    3 =>array(
        0 => array("Pending Payment", "warnig"),
        1 => array("Processing","success"),
        2 => array("Complete","primary"),
        3 => array("Fraud","danger")
   )
);

foreach($value as $result){
    $date= date("d F Y", strtotime($result['created_at']));
    $time= date("h:i:s", strtotime($result['created_at']));
    if(!isset($result['status']) || !isset($result['type'])) {continue;}

    $status=$statList[$result['type']][$result['status']];
echo '<div class="tab-pane" id="tab_1_3" >
        <div class="row">
            <div class="col-md-6 user-info" style="width:1500px;">
                <img alt="" src="'.site_url().'assets/img/avatar.png" class="img-responsive"/>
                <div class="details" style="width:1000px; padding-left:inherit;">
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