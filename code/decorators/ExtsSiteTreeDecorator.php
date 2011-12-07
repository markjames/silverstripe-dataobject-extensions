<?php

/**
 * A Decorator which adds additional utility methods to SiteTree objects
 *
 * @package		dataobject-extensions
 * @author		Mark James <mail@mark.james.name>
 * @copyright	2011 - Mark James
 * @license		New BSD License
 * @link		http://github.com/markjames/silverstripe-fieldextensions
 * 
 * Copyright (c) 2011, Mark James
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 *     * Neither the name of Zend Technologies USA, Inc. nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class ExtsSiteTreeDecorator extends SiteTreeDecorator {

	/**
	 * Return classes for the body (or container!) based on the page type, site
	 * top-level section and page id.
	 *
	 * - The following class types will be output:
	 * - section-{toplevelsectionurlsegment}
	 * - page-id-{1}
	 * - primary-type-{pagetype}
	 * - type-{pagetype}
	 * - type-{parenttype}
	 *
	 * @return string Space-delimited classes for the current page
	 */
	public function BodyClasses() {

		$classes = array();
		$page = $this->owner;

		// Get each of the page types
		$cName = get_class($page);
		$classes []= 'primary-type'
		           . strtolower(preg_replace('~([A-Z])~','-$1',$cName));
		$classes []= 'type'
		           . strtolower(preg_replace('~([A-Z])~','-$1',$cName));

		while (($cName = get_parent_class($cName)) && $cName != 'SiteTree') {
			$classes []= 'type'
			           . strtolower(preg_replace('~([A-Z])~','-$1',$cName));
		}

		// Add a page ID
		$classes []= 'page-id-'.$page->ID;

		// Find the section by looping through the parents
		while( true ) {
			$newpage = $page->Parent;
			if( $newpage ) {
				$page = $newpage;
			} else {
				$classes []= 'section-'.$page->URLSegment;
				break;
			}
		}

		return implode(' ',$classes);

	}

	/**
	* Find all the child pages with the given type (that the current user has
	* permission to view)
	*
	* @param string $type,... unlimited OPTIONAL number of names of types of
	*                         children to find
	* @return DataObjectSet A DataObjectSet containing matching child pages
	*/
	public function ChildrenOfType() {
		
		// Determine types from function arguments
		$types = func_get_args();
		$subClasses = array();
		if( count($types) ) {
			foreach( $types as $type ) {
				$subClasses = array_merge(ClassInfo::subclassesFor( $type ));
			}
			// Remove duplicates (flipflip!)
			$subClasses = array_flip(array_flip($subClasses));
		} else {
			// Fallback to all SiteTree types
			$subClasses = ClassInfo::subclassesFor( 'SiteTree' );
		}

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ID 
		        . " AND \"{$baseClass}\".\"ClassName\" IN ('"
		        . implode("','",$subClasses)
		        . "')";

		$results = array();
		$set = DataObject::get("SiteTree", $filter);
		if( !$set ) {
			return new DataObjectSet();
		}
		foreach($set as $child) { 
 			if($child->canView()) { 
 				$results []= $child;
 			} 
 		}
		return new DataObjectSet($results);

	}

	/**
	* Find all the child pages with the given type (including pages the current
	* user does not have permission to view)
	*
	* @param string $type,... unlimited OPTIONAL number of names of types of
	*                         children to find
	* @return DataObjectSet A DataObjectSet containing matching child pages
	*/
	public function AllChildrenOfType() {

		// Determine types from function arguments
		$types = func_get_args();
		$subClasses = array();
		if( count($types) ) {
			foreach( $types as $type ) {
				$subClasses = array_merge(ClassInfo::subclassesFor( $type ));
			}
			// Remove duplicates (flipflip!)
			$subClasses = array_flip(array_flip($subClasses));
		} else {
			// Fallback to all SiteTree types
			$subClasses = ClassInfo::subclassesFor( 'SiteTree' );
		}

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ID 
		        . " AND \"{$baseClass}\".\"ClassName\" IN ('"
		        . implode("','",$subClasses)
		        . "')";

		return DataObject::get("SiteTree", $filter);

	}

	/**
	* Find all of the sibling pages (i.e. pages as the same level) of the
	* current page - _excluding_ the current page - that the user has permission
	* to view and are have ShowInMenus set to true.
	*
	* @return DataObjectSet A DataObjectSet containing sibling pages
	*/
	public function Siblings() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID 
		        . " AND \"{$baseClass}\".\"ID\" != " . (int)$this->owner->ID
		        . " AND \"{$baseClass}\".\"ShowInMenus\" = 1";

		$results = array();
		$set = DataObject::get("SiteTree", $filter);
		if( !$set ) {
			return new DataObjectSet();
		}
		foreach($set as $child) { 
				if($child->canView()) { 
					$results []= $child;
				} 
			}
		return new DataObjectSet($results);

	}

	/**
	* Find all of the sibling pages (i.e. pages as the same level) of the
	* current page - _including_ the current page - that the user has permission
	* to view and are have ShowInMenus set to true.
	*
	* @return DataObjectSet A DataObjectSet containing sibling pages
	*/
	public function SiblingsAndCurrent() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID
		        . " AND \"{$baseClass}\".\"ShowInMenus\" = 1";

		$results = array();
		$set = DataObject::get("SiteTree", $filter);
		if( !$set ) {
			return new DataObjectSet();
		}
		foreach($set as $child) { 
				if($child->canView()) { 
					$results []= $child;
				} 
			}
		return new DataObjectSet($results);

	}

	/**
	* Find all of the sibling pages (i.e. pages as the same level) of the
	* current page - _excluding_ the current page - that have ShowInMenus set
	* to true (including pages the user does not have permission to view)
	*
	* @return DataObjectSet A DataObjectSet containing sibling pages
	*/
	public function AllSiblings() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = " . (int)$this->owner->ParentID 
		        . " AND \"{$baseClass}\".\"ID\" != " . (int)$this->owner->ID;

		$set = DataObject::get("SiteTree", $filter);
		return $set ? $set : new DataObjectSet();

	}

	/**
	* Find all of the sibling pages (i.e. pages as the same level) of the
	* current page - _including_ the current page - that have ShowInMenus set
	* to true (including pages the user does not have permission to view)
	*
	* @return DataObjectSet A DataObjectSet containing sibling pages
	*/
	public function AllSiblingsAndCurrent() {

		$baseClass = ClassInfo::baseDataClass($this->owner->class);

		$filter = "\"{$baseClass}\".\"ParentID\" = "
		        . (int)$this->owner->ParentID;

		$set = DataObject::get("SiteTree", $filter);
		return $set ? $set : new DataObjectSet();

	}

	/**
	* Get the nearest ancestor with one of the given types (or subclasses of
	* that type) (beginning at the current page and progressing up through the
	* hierarchy until a page is found).
	*
	* @param string $type,... unlimited OPTIONAL number of names of types to
	*                         match ancestors against
	* @return SiteTree|null The closest ancestor with the given type, or null
	*                       if no page exists in the ancestry with one of the
	*                       specified types.
	*/
	public function Closest() {

		$searchTypes = func_get_args();
		if( !count($searchTypes) ) {
			$searchTypes = array('SiteTree');
		}

		$page = $this->owner;
		while($page->ParentID > 0) {
			$page = $page->Parent;
			if( !$page ) {
				return null;
			}
			$pageType = get_class($page);
			foreach( $searchTypes as $searchType ) {
				if( $page instanceof $searchType ) {
					return $page;
				}
			}
		}
		return null;

	}

	/**
	* Get the the ancestor closest to the top level ancestor with one of the
	* given types (or subclasses of that type)
	*
	* @param string $type,... unlimited OPTIONAL number of names of types to
	*                         match ancestors against
	* @return SiteTree|null The furthest ancestor with the given type, or null
	*                       if no page exists in the ancestry with one of the
	*                       specified types.
	*/
	public function Furthest() {

		$searchTypes = func_get_args();
		if( !count($searchTypes) ) {
			$searchTypes = array('SiteTree');
		}

		$matchedPage = null;
		$page = $this->owner;
		while($page->ParentID > 0) {
			$page = $page->Parent;
			if( !$page ) {
				return null;
			}
			$pageType = get_class($page);
			foreach( $searchTypes as $searchType ) {
				if( $page instanceof $searchType ) {
					$matchedPage = $page;
				}
			}
		}
		return $matchedPage;

	}

	/**
	* Get the top-level ancestor of the current page
	*
	* @see SiteTree::Level()
	* @return SiteTree The top-level ('section') ancestor page of the
	*                  current page
	*/
	public function TopLevelParent() {

		return $this->owner->Level(1);

	}

	/**
	 * Get the next sibling of the current page
	 *
	 * @param boolean $showHiddenPages TRUE to include pages which are set to
	 *                                 Show In Menus (defaults to false)
	 * @param string|null $sortField The name of the field used to sort pages.
	 *                               Defaults to the default sort field of the
	 *                               current page type.
	 * @param string|null $sortOrder The direction (ASC or DESC) used to sort
	 *                               pages. Defaults to the default sort order
	 *                               of the current page type.
	 * @return SiteTree The next sibling page (or null if this is the last)
	 * @todo NextSibling does not work if the default sort for this pagetype 
	 *       contains multiple columns
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
		       . " AND \"$sortField\"" . ('ASC' == $sortOrder ? " > " : " < ")
		       . $sortValue;
		
		if( !$showHiddenPages ) {
			$baseClass = ClassInfo::baseDataClass($this->owner->class);
			$where .= " AND \"{$baseClass}\".\"ShowInMenus\" = 1";
		}

		$pages = DataObject::get(
			"SiteTree",
			$where,
			"\"$sortField\" " . ('ASC' == $sortOrder ? "ASC" : "DESC"),
			"",
			1
		);
		return $pages;

	}

	/**
	 * Get the previous sibling of the current page
	 *
	 * @param boolean $showHiddenPages TRUE to include pages which are set to
	 *                                 Show In Menus (defaults to false)
	 * @param string|null $sortField The name of the field used to sort pages.
	 *                               Defaults to the default sort field of the
	 *                               current page type.
	 * @param string|null $sortOrder The direction (ASC or DESC) used to sort
	 *                               pages. Defaults to the default sort order
	 *                               of the current page type.
	 * @return SiteTree The previous sibling page (or null if this is the first)
	 * @todo PreviousSibling does not work if the default sort for this pagetype 
	 *       contains multiple columns
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
		       . " AND \"$sortField\"" . ('ASC' == $sortOrder ? " < " : " > ")
		       . $sortValue;
		
		if( !$showHiddenPages ) {
			$baseClass = ClassInfo::baseDataClass($this->owner->class);
			$where .= " AND \"{$baseClass}\".\"ShowInMenus\" = 1";
		}

		$pages = DataObject::get(
			"SiteTree",
			$where,
			"\"$sortField\" ".('ASC' == $sortOrder ? "DESC" : "ASC"),
			"",
			1
		);
		return $pages;

	}

}