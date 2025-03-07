<?php
/**
 * Implementation of search in SQlite FTS index
 *
 * @category   DMS
 * @package    SeedDMS_Lucene
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2010, Uwe Steinmann
 * @version    Release: @package_version@
 */


/**
 * Class for searching in a SQlite FTS index.
 *
 * @category   DMS
 * @package    SeedDMS_Lucene
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2011, Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_SQliteFTS_Search {
	/**
	 * @var object $index SQlite FTS index
	 * @access protected
	 */
	protected $index;

	/**
	 * @var string $version
	 * @access protected
	 */
	protected $version;

	/**
	 * Create a new instance of the search
	 *
	 * @param object $index SQlite FTS index
	 * @return object instance of SeedDMS_SQliteFTS_Search
	 */
	function __construct($index) { /* {{{ */
		$this->index = $index;
		$this->version = '@package_version@';
		if($this->version[0] == '@')
			$this->version = '3.0.0';
	} /* }}} */

	/**
	 * Get document from index
	 *
	 * @param int $id id of seeddms document
	 * @return object instance of SeedDMS_SQliteFTS_QueryHit or false
	 */
	function getDocument($id) { /* {{{ */
		$hits = $this->index->findById('D'.$id);
		return $hits ? $hits[0] : false;
	} /* }}} */

	/**
	 * Get folder from index
	 *
	 * @param int $id id of seeddms folder
	 * @return object instance of SeedDMS_SQliteFTS_QueryHit or false
	 */
	function getFolder($id) { /* {{{ */
		$hits = $this->index->findById('F'.$id);
		return $hits ? $hits[0] : false;
	} /* }}} */

	protected function addQuery($querystr, $fn, $fv, $isdate=false, $isint=false, $ch=':') { /* {{{ */
		if(is_string($fv)) {
			if($querystr)
				$querystr .= ' AND ';
			if($ch=='=' && $fv == '__notset__') /* experimental */
				$querystr .= $fn.$ch.'""';
			elseif($ch=='=' && $fv == '__any__') /* experimental */
				$querystr .= $fn.' != '.'""';
			else
				$querystr .= $fn.$ch.'"'.str_replace('"', '""', $fv).'"';
		}	elseif(is_int($fv)) {
			if($querystr)
				$querystr .= ' AND ';
			$querystr .= $fn.$ch.$fv;
		} elseif(is_array($fv)) {
			if(isset($fv['from']) || isset($fv['to'])) {
				if(($fv['from'] !== '') || ($fv['to'] !== '')) {
					if($querystr)
						$querystr .= ' AND ';
					if(!empty($fv['from'])) {
						if($isdate) // Not use anymore because timestamps are used ?
							$querystr .= $fn.'>="'.date('Y-m-d', $fv['from']).'"';
						elseif($isint)
							$querystr .= $fn.'>='.$fv['from'];
						else
							$querystr .= '('.$fn.">='".$fv['from']."')";
					}
					if(!empty($fv['to'])) {
						if(!empty($fv['from']))
							$querystr .= ' AND ';
						if($isdate) // Not use anymore because timestamps are used ?
							$querystr .= $fn.'<="'.date('Y-m-d', $fv['to']).'"';
						elseif($isint)
							$querystr .= $fn.'<='.$fv['to'];
						else
							$querystr .= '('.$fn."<='".$fv['to']."')";
					}
					if($querystr)
						$querystr .= ' AND ('.$fn."!='')";
				}
			} else {
				if($querystr)
					$querystr .= ' AND ';
				$querystr .= '('.$fn.$ch.'"';
				array_walk($fv, function(&$item, $key){$item = str_replace('"', '""', $item);});
				$querystr .= implode('" OR '.$fn.$ch.'"', $fv);
				$querystr .= '")';
			}
		}
		return $querystr;
	} /* }}} */

	/**
	 * Search in index
	 *
	 * @param object $index SQlite FTS index
	 * @return object instance of SeedDMS_Lucene_Search
	 */
	function search($term, $fields=array(), $limit=array(), $order=array(), $options=array()) { /* {{{ */
		$querystr = '';
		$filterstr = '';
		$term = trim($term);
		if($term) {
			$querystr = substr($term, -1) != '*' ? $term.'*' : $term;
		}
		if(!empty($fields['owner'])) {
			$filterstr = $this->addQuery($filterstr, 'owner', $fields['owner'], false, false, '=');
		}
		if(!empty($fields['record_type'])) {
			$filterstr = $this->addQuery($filterstr, 'record_type', $fields['record_type'], false, false, '=');
		}
		if(!empty($fields['category'])) {
			$querystr = $this->addQuery($querystr, 'category', $fields['category']);
		}
		if(!empty($fields['mimetype'])) {
			$filterstr = $this->addQuery($filterstr, 'mimetype', $fields['mimetype'], false, false, '=');
		}
		if(!empty($fields['status'])) {
			$status = array_map(function($v){return (int)$v+10;}, $fields['status']);
			$filterstr = $this->addQuery($filterstr, 'status', $status, false, false, '=');
		}
		/* Searching by query is faster than filter. Hence, stay with it
		 * for now
		 */
		if(!empty($fields['user'])) {
			$querystr = $this->addQuery($querystr, 'user', $fields['user']);
		}
		if(!empty($fields['rootFolder']) && $fields['rootFolder']->getFolderList()) {
			$xpath = str_replace(':', 'x', $fields['rootFolder']->getFolderList().$fields['rootFolder']->getID().':').'*';
			$querystr = $this->addQuery($querystr, 'path', $xpath);
		}
		if(!empty($fields['startFolder']) && $fields['startFolder']->getFolderList()) {
			$xpath = str_replace(':', 'x', $fields['startFolder']->getFolderList().$fields['startFolder']->getID().':').'*';
			$querystr = $this->addQuery($querystr, 'path', $xpath);
		}

		if(!empty($fields['created_start']) || !empty($fields['created_end'])) {
			$filterstr = $this->addQuery($filterstr, 'created', ['from'=>$fields['created_start'], 'to'=>$fields['created_end']], false, true, '=');
		}
		if(!empty($fields['modified_start']) || !empty($fields['modified_end'])) {
			$filterstr = $this->addQuery($filterstr, 'modified', ['from'=>$fields['modified_start'], 'to'=>$fields['modified_end']], false, true, '=');
		}
		if(!empty($fields['filesize_start']) || !empty($fields['filesize_end'])) {
			$filterstr = $this->addQuery($filterstr, 'filesize', ['from'=>$fields['filesize_start'], 'to'=>$fields['filesize_end']], false, true, '=');
		}
		if(!empty($fields['attributes'])) {
			foreach($fields['attributes'] as $fname=>$fvalue) {
				$filterstr = $this->addQuery($filterstr, $fname, $fvalue, false, false, '=');
			}
		}
//		echo $querystr."<br />";
//		echo htmlspecialchars($filterstr);
		try {
			$result = $this->index->find($querystr, $filterstr, $limit, $order, $options);
			$recs = array();
			foreach($result["hits"] as $hit) {
				$recs[] = array('id'=>$hit->id, 'document_id'=>$hit->documentid);
			}
			return array('count'=>$result['count'], 'hits'=>$recs, 'facets'=>$result['facets']);
		} catch (Exception $e) {
			return false;
		}
	} /* }}} */
}
?>
