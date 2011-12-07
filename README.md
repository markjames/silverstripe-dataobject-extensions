# Silverstripe DataObject Extensions Module

## Introduction

A SilverStripe module which adds additional template functions and methods on data objects and pages

## Requirements

*  SilverStripe 2.4

## Installation

* Put the module into your SilverStripe installation

## Methods

Adds the following methods to your SiteTree pages:

* ChildrenOfType(NewsPage,NewsHolder,...)
* AllChildrenOfType(NewsPage,NewsHolder,...)
* Siblings
* SiblingsAndCurrent
* AllSiblings
* AllSiblingsAndCurrent
* TopLevelParent
* NextSibling
* PreviousSibling
* ListClass(4) - (An improvement on FirstLast which adds a start/end class on every nth and n-1th item)

Adds the following methods to your DataObjects pages:

* ListClass(3) - (An improvement on FirstLast which adds a start/end class on every nth and n-1th item)

## Usage

A few examples of how to use these controls in your templates:

	<% if SiblingsAndCurrent %>
	<nav>
	  <h2>Siblings</h2>
	  <ul>
	  <% control SiblingsAndCurrent %>
	    <li class="ListClass(4)"><a href="$Link">$MenuTitle</a></li>
	  <% end_control %>
	  </ul>
	</nav>
	<% end_if %>

	<% control NextSibling %>
	  <a class="next" href="$Link">$MenuTitle</a>
	<% end_control %>

	<div>
	  <h2>Red and Blue Pages (but not Yellow Pages!)</h2>
	  <ul>
	  <% control ChildrenOfType(RedPage,BluePage) %>
	    <li class="ListClass(3)"><a href="$Link">$MenuTitle</a></li>
	  <% end_control %>
	  </ul>
	</div>