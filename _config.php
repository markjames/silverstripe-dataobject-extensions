<?php

// It would be nice to add this to ViewableData rather than DataObject,
// but methods added to ViewableData using add_extension are not available
// on DataObjects
Object::add_extension('DataObject','ExtsDataObjectDecorator');

Object::add_extension('SiteTree','ExtsSiteTreeDecorator');