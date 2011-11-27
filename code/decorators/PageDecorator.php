<?php

class PageDecorator extends SiteTreeDecorator {

	public function ChildrenOfType( $type="SiteTree" ) {
		
	}

	public function AllChildrenOfType( $type="SiteTree" ) {
		
	}

	public function Siblings() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID 
		        . " AND \"{$baseClass}\".\"ID\" != " . (int)$this->owner->ID
		        . " AND \"{$baseClass}\".\"ShowInMenus\" = 1";

		$results = array();
		foreach(DataObject::get("SiteTree", $filter) as $child) { 
 			if($child->canView()) { 
 				$results []= $child;
 			} 
 		}
		return new DataObjectSet($results);

	}

	public function SiblingsAndCurrent() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID
		        . " AND \"{$baseClass}\".\"ShowInMenus\" = 1";

		$results = array();
		foreach(DataObject::get("SiteTree", $filter) as $child) { 
 			if($child->canView()) { 
 				$results []= $child;
 			} 
 		}
		return new DataObjectSet($results);

	}

	public function AllSiblings() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID 
		        . " AND \"{$baseClass}\".\"ID\" != " . (int)$this->owner->ID;

		$results = array();
		foreach(DataObject::get("SiteTree", $filter) as $child) { 
 			if($child->canView()) { 
 				$results []= $child;
 			} 
 		}
		return new DataObjectSet($results);

	}

	public function AllSiblingsAndCurrent() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID;

		$results = array();
		foreach(DataObject::get("SiteTree", $filter) as $child) { 
 			if($child->canView()) { 
 				$results []= $child;
 			} 
 		}
		return new DataObjectSet($results);

	}

	public function TopLevelParent() {

		$page = $this->owner;
		while($page->ParentID > 0) {
			$page = $page->Parent;
		}
		return $page;

	}

	/**
	 *
	 *
	 * @todo NextSibling does not work if the default sort for this pagetype contains multiple columns
	 */
	public function NextSibling( $showHiddenPages=false, $sortField=null, $sortOrder=null ) {

		// Force as boolean
		$showHiddenPages = !!$showHiddenPages;

		// If we havn't specified the sortField, then
		// we should check what the default sort order is for this
		// class type, and then try and pull out the field and the order
		if( null === $sortField )  {
			$class = $this->owner->class;
			$sqlSort = $this->owner->stat('default_sort');
			
			if( strpos($sqlSort, 'DESC') ) {
				null === $sortOrder and $sortOrder = 'DESC';
			}
			// Get field part
			if( !$sortField = strstr( $sqlSort, ' ', true ) ) {
				$sortField = $sqlSort;
			}

			// Trim off any quotes to normalize
			$sortField = trim($sortField,'"\\');
		}

		// Sort order should be ASC if it hasn't been specified
		// and we don't have a default
		null === $sortOrder and $sortOrder = 'ASC';

		$sortValue = $this->owner->$sortField;

		// If the current page exists outside the heirarchy, don't attempt
		// to find the next page (because it will error)
		if( null === $sortValue ) {
			return new DataObjectSet();
		}

		// If the order is by ID, specify the full table so we don't error
		if( $sortField == 'ID' ) {
			$sortField = 'SiteTree"."'.$sortField;
		}

		$where = "\"ParentID\" = " . $this->owner->ParentID
		       . " AND \"$sortField\"" . ('ASC' == $sortOrder ? " > " : " < ") . $sortValue;
		
		if( !$showHiddenPages ) {
			$baseClass = ClassInfo::baseDataClass($this->owner->class);
			$where .= " AND \"{$baseClass}\".\"ShowInMenus\" = 1";
		}

		$pages = DataObject::get("SiteTree", $where, "\"$sortField\" ".('ASC' == $sortOrder ? "ASC" : "DESC"), "", 1);
		return $pages;

	}

	/**
	 *
	 *
	 * @todo PreviousSibling does not work if the default sort for this pagetype contains multiple columns
	 */
	public function PreviousSibling( $showHiddenPages=false, $sortField=null, $sortOrder=null ) {

		// Force as boolean
		$showHiddenPages = !!$showHiddenPages;

		// If we havn't specified the sortField, then
		// we should check what the default sort order is for this
		// class type, and then try and pull out the field and the order
		if( null === $sortField )  {
			$class = $this->owner->class;
			$sqlSort = $this->owner->stat('default_sort');
			
			if( strpos($sqlSort, 'DESC') ) {
				null === $sortOrder and $sortOrder = 'DESC';
			}
			// Get field part
			if( !$sortField = strstr( $sqlSort, ' ', true ) ) {
				$sortField = $sqlSort;
			}

			// Trim off any quotes to normalize
			$sortField = trim($sortField,'"\\');
		}

		// Sort order should be ASC if it hasn't been specified
		// and we don't have a default
		null === $sortOrder and $sortOrder = 'ASC';

		$sortValue = $this->owner->$sortField;

		// If the current page exists outside the heirarchy, don't attempt
		// to find the next page (because it will error)
		if( null === $sortValue ) {
			return new DataObjectSet();
		}

		// If the order is by ID, specify the full table so we don't error
		if( $sortField == 'ID' ) {
			$sortField = 'SiteTree"."'.$sortField;
		}

		$where = "\"ParentID\" = " . $this->owner->ParentID
		       . " AND \"$sortField\"" . ('ASC' == $sortOrder ? " < " : " > ") . $sortValue;
		
		if( !$showHiddenPages ) {
			$baseClass = ClassInfo::baseDataClass($this->owner->class);
			$where .= " AND \"{$baseClass}\".\"ShowInMenus\" = 1";
		}

		$pages = DataObject::get("SiteTree", $where, "\"$sortField\" ".('ASC' == $sortOrder ? "DESC" : "ASC"), "", 1);
		return $pages;

	}

}