# On to the news. From ModelAdmin. Silverstripe Newsmodule.
=======================

# WARNING

This latest version has a different multiselectfield! If you have the old multiselectfield, manually delete it and then run composer update.

This new field is prettier and a bit more userfriendly.

Apologies for any inconvenience.

### REQUEST

Help translate via [`Transifex`](https://www.transifex.com/projects/p/newsmodule/)

## Introduction

This is the Silverstripe 3 Newsmodule. It's separate from the SiteTree.    
Mainly to keep the SiteTree uncluttered.

If you expect to have a few hundred articles (like I do), your SiteTree becomes very unreadable. That's why I chose to make an alternative, using ModelAdmin.

It works. Pretty much. Has some missing features. Please read my comments on the functions and such. It should make everything clear.
If you encounter any problems with the Master, please let me know! I can't bugfix without a ticket!    
If you use the development-branch, be aware that this is my untested area and therefor might contain bugs or just plain ruin your site with errors.

Ow, and the default template is based on HTML5 features and my own website. Functional frontend demo can also be found on my website. [`Casa Laguna`](http://casa-laguna.net/all-the-news)

Strange code and undocumented features are probably due to my cat sleeping on the keyboard.

I strongly advice to read my inline comments. I've worked hard to make them both functional as funny. Feedback appreciated.
And keep an eye on my issues, those are issues I want to fix or features I want to add. Feel free to add suggestions!

This project is always in development. I will always add features or fix bugs. Although it is always in development, I won't leave a bugged out version here. It always works.
All new features or fixes are backwards compatible unless stated in the commit-message.

## Maintainer Contacts

* Simon "Sphere" Erkelens `github[at]casa-laguna[dot]net`
* I'm also on twitter. [`SphereSilver`](https://twitter.com/SphereSilver)

E-mail me your twitter contact if you added something and like to be mentioned!

## Requirements

* [`Silverstripe 3.1.* framework`](https://github.com/silverstripe/Sapphire)
* [`Silverstripe 3.1.* CMS`](https://github.com/silverstripe/cms)
* [`GridFieldBulkEditingTools`](https://github.com/colymba/GridFieldBulkEditingTools)
* [`Gridfield Extensions`](https://github.com/ajshort/silverstripe-gridfieldextensions)

### Child grouping support

* Requires [`ENHANCEMENT issue #2501`](https://github.com/silverstripe/silverstripe-framework/pull/2105) for child grouping support

## Highly recommended

* [`Silverstripe Display Logic`](https://github.com/UncleCheese/silverstripe-display-logic)

## Optional

* [`Silverstripe Translatable`](https://github.com/silverstripe/silverstripe-translatable)
* [`GridfieldPaginatorWithShowAll`](https://github.com/normann/gridfieldpaginatorwithshowall)

Note on which version of translatable, CMS and Framework you use! They might not be compatible.

## Features

[See the changelog](docs/en/Changelog)

## Demo of frontend

* [`General news overview`](https://newsmodule.casa-laguna.net/news)
* [`Tagcloud`](https://newsmodule.casa-laguna.net/news/tags)
* [`Redirect to urlsegment`](https://newsmodule.casa-laguna.net/news/show/1)

## Demo of backend

* [`user: news@test.com password: newsmodule`](https://newsmodule.casa-laguna.net/admin)

## Lacks

* History of changes in the items
* 

(Indeed, that second bullet means it only lacks history, nothing else :P )

## [Help and installation](docs/en/Help.md)

If you don't have a github account, just download:
 1. Click on the big "ZIP" button at the top (Make sure you download a Master-branch!).
 2. Extract the zip to your site-root
 3. Run in your browser - `www.example.com/dev/build?flush=all` to rebuild the manifest and database.
A default page will be created if possible.

The advised option is to clone the repo into your site-root:
 1.  In your site-root, do `git clone https://github.com/Firesphere/silverstripe-newsmodule.git`. 
 2.  Run in your browser - `www.example.com/dev/build?flush=all` to rebuild the manifest and database.
A default page will be created if possible.
This method will make sure you can always pull the latest updates from the upstream, thus, keeping you up-to-date with the latest features!

Or use Composer:
 1. ```composer require firesphere/silverstripe-newsmodule:dev-master```
 2. Pray it works.
 3. Run in your browser - `www.example.com/dev/build?flush=all` to rebuild the manifest and database.

I would like it if you forked and cloned if you want to help out improving this module!
 1.  Make a fork of this module.
 2.  In your site-root, do `git clone https://{your username}@github.com/{your username}/silverstripe-newsmodule.git`. 
 3.  Run in your browser - `www.example.com/dev/build?flush=all` to rebuild the manifest and database.
A default page will be created if possible.
This method is only if you know how git works and know how to add upstreams etc. to contribute.

Note, forking is NOT REQUIRED, only handy if you want to help out.

## Best Practices

* Write grammatically correct. (If you don't, I'll prevent you from getting any updates. Be warned!)
* If you have Akismet, add your Akismet API-key to the SiteConfig in News settings (It's in the Comments-section). It saves you from comment-spam
* Make sure your cat doesn't walk over yoa23'oqexkji6ygfp89cbhv2

## Configuration

* In the SiteConfig, set your wished configuration in the News-tab.
* To enable smilies, change in templates/Layout, the to-parse files. Add ```.Parse(BBCodeParser)``` To the items you want parsed. To enable smilies, in your config, set BBCodeParser::enable_smilies() (If I'm not mistaken)
* In _config, you can disable, edit or change parsers. Note, the parsers are **GLOBAL**, so $Content from pages will be parsed too!
* In javascript/newsmodule.js, you can configure the tagcloud. Which element it should trigger on, etc.
* Check the templates, Replace placeholder texts!
* Set optional extra anti-spam methods in your SiteConfig
* The slideshow-feature requires you (or your developer) to include a slideshow-javascript. It's not included. Edit the templates in \templates\includes\NewsSlideshow*.ss as needed for the slideshow to work.
If you set optional extra anti-spam method, the commentform will contain a field with the ID "Extra", position it "on the other side of the street", e.g. a position absolute with in your css #Extra.field{ left: -9000; }

## Plans

* Integrate Facebook OAuth to automagically post when a new item is created (FB OAuth is really crappy :( ).
* Add a "Fetch me a beer" function. Should be useful I think.

## Notes

As of version 2.0, multiple developers have contributed. Many thanks to them! Also, translators, thank you a lot!


To include reCaptcha as anti-spam method, alter the NewsHolderPage on line 399. Change that one line to these three:
```
		$form = (CommentForm::create($this, 'CommentForm', $siteconfig, $params));
		$protector = SpamProtectorManager::update_form($form, 'Message');
		return $form;
```

### Also, if you can't answer the following question, you are not allowed to help :P

"The cow, what do you think of it?"

## Requests

* Beer.
* Improvements.
* Translations.
* Beer.
* Did I mention beer?

## Other

* **This module is given "as is" and I am not responsible for any damage it might do to your brain, dog, cat, house, computer or website.**
* Code Comments should not be taken too seriously, since I'm bad at writing serious code-comments.
* Please use the Issue-tracker, otherwise I get lost too.
* This ModelAdmin method is chosen to declutter the SiteTree.

## Actual license

This module is published under BSD 2-clause license, although these are not in the actual classes, the license does apply:

http://www.opensource.org/licenses/BSD-2-Clause

Copyright (c) 2012-NOW(), Simon "Sphere" Erkelens

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


(I shouldn't scream, should I? This is copy-paste from BSD-2 license...)

## Did you read this entire readme? You rock!

Pictured below is a cow, just for you.
```

               /( ,,,,, )\
              _\,;;;;;;;,/_
           .-"; ;;;;;;;;; ;"-.
           '.__/`_ / \ _`\__.'
              | (')| |(') |
              | .--' '--. |
              |/ o     o \|
              |           |
             / \ _..=.._ / \
            /:. '._____.'   \
           ;::'    / \      .;
           |     _|_ _|_   ::|
         .-|     '==o=='    '|-.
        /  |  . /       \    |  \
        |  | ::|         |   | .|
        |  (  ')         (.  )::|
        |: |   |;  U U  ;|:: | `|
        |' |   | \ U U / |'  |  |
        ##V|   |_/`"""`\_|   |V##
           ##V##         ##V##
```
