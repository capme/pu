<?php
function adminCollectModule($selected, $list = array(), $group, &$result = "") {
	foreach($list as $id => $val) {
		$result .= '<li><label><input type="checkbox" name="'.$group.'[modules][]" value="'.$id.'" '.(in_array($id, $selected) ? 'checked' : '').'>'.$val['name'].'</label>';
		if( isset($val['child']) && sizeof($val['child']) ) {
			$result .= '<ul class="list-unstyled">';
			adminCollectModule($selected, $val['child'], $group, $result);
			$result .= "</ul>";
		}
	}

	return $result;

}


function buildMenu($list = array(), $parents = array(), $currentModule, $allowedModule, &$result = "") {
	foreach($list as $id => $val) {
		if(!in_array($id, $allowedModule) || $val['hidden'] == 1) {
			continue;
		}
		$result .= '<li class="'.( (in_array($id, $parents) || $id == $currentModule['id']) ? 'active' : '').'"><a href="'.($val['slug']? '/'.$val['slug'] : 'javascript:;').'"><i class="fa '.$val['icon'].'"></i><span class="title">&nbsp;&nbsp;'.$val['name'].'</span>' . ( (in_array($id, $parents) || $id == $currentModule['id']) ? '<span class="selected"></span>' : '');
		$result .= (isset($val['child']) && sizeof($val['child']) ? '<span class="arrow "></span>' : '') . '</a>';
		if( isset($val['child']) && sizeof($val['child']) ) {
			$result .= '<ul class="sub-menu">';
			buildMenu($val['child'], $parents, $currentModule, $allowedModule, $result);
			$result .= "</ul>";
		}
		$result .= '</li>';
	}

	return $result;

}

/**
 * @param array $list : array of notification list
 * @param string $type : type of notification ['notification','inbox','tasks']
 * @return string
 */
function buildNotification($list = array(), $type = ''){
    $icon = ['notification' => 'fa-warning', 'inbox' => 'fa-envelope', 'task' => 'fa-tasks'];

    if(empty($type)) return '';

    $result = '<!-- BEGIN '.strtoupper($type).' DROPDOWN -->'.
        '<li class="dropdown" id="header_'.$type.'_bar">'.
        '<a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">'.
        '<i class="fa '.(isset($icon[$type]) ? $icon[$type] : 'fa-warning').'"></i>';
    if(count($list) > 0) $result .= '<span class="badge">'.count($list).'</span>';
    $result .= '</a><ul class="dropdown-menu extended '.$type.'">'.
        '<li><p>You have '.count($list).' '.$type.'</p></li>';
    if(count($list) > 0){
        $result .= '<li>'.
            '<ul class="dropdown-menu-list scroller" style="height: 250px;">';

        foreach( $list as $id=>$val ){
            $now = new DateTime('now');
            $created = new DateTime($val['created_at']);
            $diff = $now->diff($created);
            $statDiff = $diff->format('%i mins');
            if($diff->format('%h') > 0) $statDiff = $diff->format('%h hrs');
            if($diff->format('%d') > 0) $statDiff = $diff->format('%d days');

            $result .= '<li>'.
                '<a href="'.site_url('notification/read?id='.$val['id'].'&url='.$val['url']).'">'.
                '<span class="label label-sm label-icon label-warning"><i class="fa fa-bell-o"></i></span>'.
                $val['message'].'. <span class="time">'.$statDiff.'</span>'.
                '</a></li>';
        }

        $result .= '</ul></li>';
    }

    $result .= '</ul></li>'.
        '<!-- END '.strtoupper($type).' DROPDOWN -->';

    return $result;
}

?>