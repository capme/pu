<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 * @author Technology <technology@velaasia.com>
 * @property Auth_m $auth_m
 * @property Client_m $client_m
 *
 */
class MY_Model extends CI_Model{
	
	var $filterSession = null;
	var $db = null;
	var $table = null;
	var $filters = array();
	var $sorts = array();
	var $aFilters = array();
	var $pkField = null;
	var $relation = array();
	var $select = array();
	
	function __construct() {
		parent::__construct();
	}
	
	public function clearCurrentFilter() {
		$this->_clearCurrentFilter();
	}
	
	/**
	 * Clear all active filter
	 */
	protected function _clearCurrentFilter() {
		$this->aFilters = array();
		$this->session->unset_userdata( $this->filterSession );
	}
	
	/**
	 * Get total row
	 */
	protected function _doGetTotalRow() {
		$this->_prepareFilter();
	
		if( !empty($this->relation) ) {
			foreach($this->relation as $relation) {
				$this->db->join($relation['table'], $relation['link'], $relation['type']);
			}
		}
		
		foreach($this->aFilters as $tField => $val) {
			$this->db->where($tField, $val);
		}

		if(!empty($client)){
			$res->where("client", $client);
		}
	
		return $this->db->count_all_results( $this->table );
	}
	
	/**
	 * Prepare filter for current request
	 */
	protected function _prepareFilter() {
		$sAction = $this->input->post("sAction");
			
		if( $this->input->post("sAction") == "filter" ) {
			foreach($this->filters as $tField => $iField) {
				$val = $this->input->post($iField);
				if(is_numeric($val) && $val >= -1) {
					$this->aFilters[$tField] = $val;
				} else if(!empty($val)) {
					$this->aFilters[$tField] = $val;
				}
			}
				
			$this->_saveCurrentFilter();
		} else {
			$this->_getSavedFilter();
		}
	
		if( $this->input->post("sAction") == "filter_cancel" ) {
			$this->_clearCurrentFilter();
		}
	}
	
	/**
	 * store current filter to session for next listing
	 */
	protected function _saveCurrentFilter() {
		if( empty($this->aFilters) ) {
			return;
		}
	
		$this->session->set_userdata( array($this->filterSession => json_encode($this->aFilters)) );
	}
	
	/**
	 * get saved filter from previous request
	 */
	protected function _getSavedFilter() {
		$filter = $this->session->userdata( $this->filterSession );
		if(!$filter) return;
	
		$filter = json_decode($filter, true);
		if( is_array($filter) && !empty($filter) ) {
			$this->aFilters = $filter;
		}
	}
	
	/**
	 * Get row from database, do filtering, sorting, limit
	 *
	 * @param integer $offset
	 * @param integer $limit
	 * @return unknown
	 */
	protected function _doGetRows($offset, $limit) {
		$res = $this->db->limit($limit, $offset);
	
		$this->_prepareFilter();
	
		if(!empty($this->select)) {
			$res->select( implode(",", $this->select) );
		}
		
		if( !empty($this->relation) ) {
			foreach($this->relation as $relation) {
				$res->join($relation['table'], $relation['link'], $relation['type']);
			}
		}
		
		foreach($this->aFilters as $tField => $val) {
			$res->where($tField, $val);
		}
	
		$iSortingCols = $this->input->post("iSortingCols");
		if( !empty($iSortingCols) ) {
			for($i=0; $i<$iSortingCols; $i++) {
				$colId = $this->input->post("iSortCol_{$i}");
				$col = $this->sorts[$colId];
				$dir = $this->input->post("sSortDir_{$i}");
				$res = $res->order_by($col, $dir);
	
			}
		} else {
			$res = $res->order_by($this->pkField, "asc");
		}
		
		$client = $this->session->userdata("client"); 

		if(!empty($client)){
			$res->where("client", $client);
		}

		$res = $res->get( $this->table );
		return $res;
	}
	
}
