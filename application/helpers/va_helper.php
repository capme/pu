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

?>