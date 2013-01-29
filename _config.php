<?php

// make News-functions available to controller and templates:
Object::add_extension('ContentController', 'NewsExtension');

//Searchables in News
Object::add_extension('SiteConfig', 'NewsSiteConfigDecorator');

SSAkismet::setAPIKey('4e0ff1dc3702');