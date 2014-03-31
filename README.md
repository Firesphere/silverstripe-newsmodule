# On to the news. From ModelAdmin. Silverstripe Newsmodule.
=======================

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
* [`SortableGridField`](https://github.com/UndefinedOffset/SortableGridField)

### Child grouping support

* Requires [`ENHANCEMENT issue #2501`](https://github.com/silverstripe/silverstripe-framework/pull/2105) for child grouping support

## Highly recommended

* [`Silverstripe Display Logic`](https://github.com/UncleCheese/silverstripe-display-logic)

## Optional

* [`Silverstripe Translatable`](https://github.com/silverstripe/silverstripe-translatable)
* [`GridfieldPaginatorWithShowAll`](https://github.com/normann/gridfieldpaginatorwithshowall)

Note on which version of translatable, CMS and Framework you use! They might not be compatible.

## Version numbering explained

After releasing 1.4.2, I noticed I needed to redesign my version-numbering. So here's the deal:

 * The first number is a master release. It's the master, it's at this number I decide when to up the number.
 * The second number is a major feature release. This means a whole bunch of minor features are bundled into one big upgrade. Major releases are always safe to use and are only hotfixed, never feature-upgraded without a subnumber.
 * The third number is a minor feature release. This means a feature is added, but it's not a big deal. They might include minor features like help, but never a big upgrade.
 * The fourth number is a hotfix. It's just something that might bug. Or a small fix. Or a quick fix for a bug.

Major feature and Master releases are also defined in the milestones. So you know what issues will be resolved for that version.

I think this tagging method should be the best way to go. As you can see, this README update is 1.4.8.1. Meaning:
1. It's master version 1
4. This is my current major release but no breaking changes to version 1
8. I just fixed publishfrom, although it was a major bug, the update is minor.
1. Well, to make a full feature release of this tiny readme update?

Note, this means features and hotfixes in the 4th digit doesn't mean it's a hotfix, nor does it mean it's a release, or a feature. It can be both! (I understand this might cause some problems, but I don't want to go to 5 numbers)

## Work in progress

Yeah. It kinda is. I'm trying not to break old features with new features!

## Features

[See the changelog](Changelog)

## Demo of frontend

* [`General news overview`](http://casa-laguna.net/all-the-news)
* [`Tagcloud`](http://casa-laguna.net/all-the-news/tags)
* [`Specific tag`](http://casa-laguna.net/all-the-news/tag/sphere)
* [`Redirect to urlsegment`](http://casa-laguna.net/all-the-news/show/1)

## Demo of backend

* [`user: news@test.com password: newsmodule`](http://newsmodule.casa-laguna.net/admin)

## Lacks

* History of changes in the items
* 

(Indeed, that second bullet means it only lacks history, nothing else :P )

## [Help and installation](Help)

If you don't have a github account, just download:
 1. Click on the big "ZIP" button at the top (Make sure you download a Master-branch!).
 2. Extract the zip to your site-root
 3. Run in your browser - `www.example.com/dev/build` to rebuild the database. 
A default page will be created if possible.

The advised option is to clone the repo into your site-root:
 1.  In your site-root, do `git clone https://github.com/Firesphere/silverstripe-newsmodule.git`. 
 2.  Run in your browser - `www.example.com/dev/build` to rebuild the database. 
A default page will be created if possible.
This method will make sure you can always pull the latest updates from the upstream, thus, keeping you up-to-date with the latest features!

Preferred, I would like it if you forked and cloned, because if you do, you can help me by adding features and make pull-requests to improve this module!
 1.  Make a fork of this module.
 2.  In your site-root, do `git clone https://{your username}@github.com/{your username}/silverstripe-newsmodule.git`. 
 3.  Run in your browser - `www.example.com/dev/build` to rebuild the database. 
A default page will be created if possible.
This method is only if you know how git works and know how to add upstreams etc. to contribute.

Note, forking is NOT REQUIRED, only handy if you want to help out.

## Best Practices

* Write grammatically correct. (If you don't, I'll prevent you from getting any updates. Be warned!)
* If you have Akismet, add your Akismet API-key to the SiteConfig in News settings (It's in the Comments-section). It saves you from comment-spam
* Make sure your cat doesn't walk over yoa23'oqexkji6ygfp89cbhv2

## Configuration

* In the SiteConfig, set your wished configuration in the News-tab.
* To enable the OpenGraph features, include the og-module and uncomment the implementation on the News-class.
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

## Known Issues

* See the Issues-list on Github.
* Version 3.0 is outdated as hell.
* No, I'm serious, Master_3.0 is really far behind.

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
