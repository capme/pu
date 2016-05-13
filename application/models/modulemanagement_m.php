<?php
class Modulemanagement_m extends MY_Model {
	var $filterSession = "DB_USER_FILTER";
	var $db = null;
	var $table ='module';
	var $filters = array("name" => "name", "group" => "group", "client" => "client","status"=>"status","hidden"=>"hidden","parent"=>"parent");
	var $sorts = array(1 => "id");
	var $aFilters = array();
	var $id = "id";
	var $structuredModule = array();
	var $parentMap = array();
	var $currentModule = null;
	//var $login_timeout = 350; // kelipatan 300 karena $config['sess_time_to_update']	= 300;
	
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('mysql', TRUE);
	}
	
	public function getModuleManagementList()
	{
		$iTotalRecords = $this->_doGetTotalRow();
		$iDisplayLength = intval($this->input->post('iDisplayLength'));
		$iDisplayLength = $iDisplayLength < 0 ? $iTotalRecords : $iDisplayLength;
		$iDisplayStart = intval($this->input->post('iDisplayStart'));
		$sEcho = intval($this->input->post('sEcho'));
	
		$records = array();
		$records["aaData"] = array();	
	
		$end = $iDisplayStart + $iDisplayLength;
		$end = $end > $iTotalRecords ? $iTotalRecords : $end;	
		$_row = $this->_doGetRows($iDisplayStart, $iDisplayLength);
		$statusList = array(
				0 => array("Not Active", "danger"),
				1 => array("Active", "success")
		);
		
		$visiblelist= array(
				0 => array("Visible", "success"),
				1 => array("Hidden", "danger")
		);
			
		$grup=$this->moduleList();
		//print_r($grup);
		$opsiarray=array();
		foreach($grup as $id=>$row)
		{
		$opsiarray[$row['id']]=$row['name'];
		}
		$opsiarray[0]="";
		
		$no=0;
		foreach($_row->result() as $_result) 
		{		
		$status = $statusList[$_result->status];
		$visible = $visiblelist[$_result->hidden];
		$records["aaData"][] = array(
					'<input type="checkbox" name="id[]" value="'.$_result->id.'">',
					$no=$no+1,
					$_result->name,
					$_result->slug,
					$_result->sort,
					$opsiarray[$_result->parent],					
					'<span class="label label-sm label-'.($status[1]).'">'.($status[0]).'</span>',
					'<span class="label label-sm label-'.($visible[1]).'">'.($visible[0]).'</span>',
					'<a href="'.site_url("modulemanagement/viewmodulemanagement/".$_result->id).'" class="btn btn-xs default"><i class="fa fa-search"></i> View</a> 
					<a href="'.site_url("modulemanagement/deletemodulemanagement/".$_result->id).'" onClick="return deletechecked()" class="btn btn-xs default"><i class="fa fa-trash-o"></i> Delete</a>',);
		}	
		$records["sEcho"] = $sEcho;
		$records["iTotalRecords"] = $iTotalRecords;
		$records["iTotalDisplayRecords"] = $iTotalRecords;

		return $records;
	}
	
	public function moduleList()
	{
	$grouplist=$this->db->get($this->table);
	return $grouplist->result_array();
	}
	
	public function newModule( $post )
	{
	$msg = array();	
		if(!empty($post['module'])) 
		{
			$data['name'] = $post['module'];
		} 
		if(!empty($post['parent'])) {
			$data['parent'] = $post['parent'];
		}
		if(!empty($post['icon'])) {
			$data['icon'] = $post['icon'];
		} else {
			$data['icon'] = "";
		}
		if(!empty($post['url'])) {
			$data['slug'] = $post['url'];
		}
		if(!empty($post['sort'])) {
			$data['sort'] = $post['sort'];
		}
		
		if(!empty($post['hidden'])) {
			$data['hidden'] = $post['hidden'];
		} else {
			$data['hidden'] = 0;
		}
		
		if(!empty($post['status'])) {
			$data['status'] = $post['status'];
		} else {
			$data['status'] = 0;
		}
				
		if(empty($msg)) 
		{
			
			$this->db->insert($this->table, $data);			
			return $this->db->insert_id();			
		} 
		else 
		{
			return $msg;
		}
	}
	
	public function getModuleManagement($id)
	{
	$this->db->where(array($this->id=>$id));
	$this->db->from($this->table);	
	return $this->db->get();
	}
	
	public function deleteModulemManagement($id)
	{
	$this->db->where(array($this->id => $id));
	$this->db->delete($this->table);
	}	
	
	public function massUpdate($ids = array(), $status)
	{
		$this->db->where_in($this->id, $ids);
		if( $status == 2 ) 
		{
			$this->db->delete($this->table);
		} 
		else 
		{
			$this->db->update($this->table, array("status" => $status));
		}		
	}
	
	public function updateModule($post)
	{
	$msg = array();
	
		if(!empty($post['module'])) 
		{
			$data['name'] = $post['module'];
		} 
		if(!empty($post['parent'])) {
			$data['parent'] = $post['parent'];
		} 
		if(!empty($post['url'])) {
			$data['slug'] = $post['url'];
		}
		if(!empty($post['sort'])) {
			$data['sort'] = $post['sort'];
		}
		if(!empty($post['hidden'])) {
			$data['hidden'] = $post['hidden'];
		} else {
			$data['hidden'] = 0;
		}
		if(!empty($post['icon'])) {
			$data['icon'] = $post['icon'];
		} else {
			$data['icon'] = '';
		}
		if(!empty($post['status'])) {
			$data['status'] = $post['status'];
		} else {
			$data['status'] = 0;
		}
		
		if(empty($msg)) 
		{
			$this->db->where($this->id, $post['id']);
			$this->db->update($this->table, $data);
			//return $post['id'];
		} 
		else {
			return $msg;
		}
	}
	
	/**
	 * Collect all active module in parent-child structured
	 * @todo	store data to cache
	 * @return	array $datas
	 */
	public function getStructuredModule($returnParentMap = false) {
		if(empty($this->structuredModule) || empty($this->parentMap)) {
		
			$res = $this->db->select("id, name, slug, parent, sort, icon, hidden")
				->where("status", 1)
				->order_by("parent", "ASC")->order_by("sort", "ASC");
		
			$modules = $res->get($this->table)->result_array();
			$datas = array();
			$parentMap = array();
			$temp = array();
		
			if(empty($modules)) {
				return array();
			}
		
			foreach($modules as $d) {
				if($d['parent'] == 0) {
					$d['child'] = array();
					$datas[$d['id']] = $d;
					$parentMap[$d['id']] = $d['parent'];
				} else {
					$_pMap = isset($parentMap[$d['parent']]) ? $parentMap[$d['parent']] : FALSE;
					$this->_checkCurrentModule($d);
					if($_pMap === FALSE) { // parent not found yet
						if(!isset($temp[$d['parent']])) { // stored to temporary variable
							$temp[$d['parent']] = array();
						}
							
						$temp[$d['parent']][$d['id']] = $d;
						$parentMap[$d['id']] = $d['parent'];
					} else {
						// parent found. Find top parent
						$hierarchy = array($d['parent']);
						while($_pMap != 0) {
							array_unshift($hierarchy, $_pMap);
							$_pMap = isset($parentMap[$_pMap]) ? $parentMap[$_pMap] : FALSE;
							if($_pMap === FALSE) {
								break;
							}
		
						}
							
						$parentMap[$d['id']] = $d['parent'];
							
						foreach($hierarchy as $i => $cId) {
							if($i == 0) {
								$parentData =& $datas[$cId];
							} else {
								$parentData =& $parentData['child'][$cId];
							}
						}
						$parentData['child'][$d['id']] = $d;
						if(isset($temp[$d['id']])) {
							$parentData =& $parentData['child'][$d['id']];
							$parentData['child'] = $temp[$d['id']];
							unset($temp[$d['id']]);
						}
					}
				}
			}
			
			$this->parentMap = $parentMap;
			$this->structuredModule = $datas;
		}
		
		if($returnParentMap){
			return $this->parentMap;
		} else {
			return $this->structuredModule;
		}
	}
	
	public function getCurrentModule() {
		return $this->currentModule;
	}
	
	private function _checkCurrentModule($module) {
		if( !is_null($this->currentModule) ){return;}
		
		if(strstr($module['slug'], "/") !== FALSE) {
			list($class, $method) = explode("/", $module['slug']);
		} else {
			$class = $module['slug'];
			$method = "";
		}
		
		if($class == $this->router->class) {$currentMethod = $this->router->method;
			if($this->router->method == "index" && (empty($method) || $method == $this->router->method)) {
				$this->currentModule = $module;
			} else if($method == $this->router->method) {
				$this->currentModule = $module;
			}
		}
		
	}
	
}