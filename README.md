# silverstripe-newsmodule
=======================

## Introduction

This is the Silverstripe 3 Newsmodule. It's a very basic newsmodule, separate from the SiteTree.
Mainly to keep the SiteTree uncluttered. 

It works. Pretty much. Has some missing features. Please read my comments on the functions and such. It should make everything clear.
If it doesn't... Then... wait a bit until I update this, ok?

Ow, and the default template is based on HTML5 features and my own website. Functional frontend demo can also be found on my website. `http://casa-laguna.net/all-the-news`

## Maintainer Contacts

* Simon "Sphere" Erkelens `simon[at]casa-laguna[dot]net`

## Features

* Handle news and impressions besides the news.
* Support for OG-data on some servers (some servers refuse)
* History of URL-segments
* Support for custom comments
* Akismet support
* Globally available NewsArchive function
* Configurable from the SiteConfig
* GridField overview, less clutter in the SiteTree

## Lacks

* History of changes in the items
* 

## Installation

 1.  Make a fork of this module.
 2.  In your site-root, do `git clone https://{your username}@github.com/{your username}/silverstripe-newsmodule.git`. 
 3.  Run in your browser - `www.example.com/dev/build` to rebuild the database. 
 4.  Create a NewsHolderPage type in your Pages Admin (todo, autocreate this page)

## Best Practices

* Write grammatically correct.
* If you have Akismet, add your Akismet API-key to _config.php as exampled. It saves you from comment-spam

## Configuration

* In the SiteConfig, set your wished configuration in the News-tab.

## Plans

* Integrate Facebook/Twitter OAuth to automagically post when a new item is created.
* Add a slideshow feature. Currently, not available, but you can use your own by integrating a slideshow manually.
* 

## Known Issues

* I think the renamer is broken at the moment.

## Requests

* Improvements.
* Translations.

## Other

* This module is given "as is" and I am not responsible for any damage it might do to your brain, dog, cat, house, computer or website.
* Code Comments should not be taken too seriously, since I'm bad at writing serious code-comments.
* Please use the Issue-tracker, otherwise I get lost too.

## Actual license

This module is published under BSD 2-clause license, although these are not in the actual classes, the license does apply:

http://www.opensource.org/licenses/BSD-2-Clause

Copyright (c) 2013, Simon "Sphere" Erkelens

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

