# Newsmodule help
=======================

## Installation

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
* Copy the templates to your themes-directory for editing and styling, so any update wont trash your templates.
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


## Templates

* The ```archive``` method uses the base NewsHolderPage template
* Templates are examples, based on Twitter Bootstrap 3. They may not suit your own styling by default!

## HELP! Things broke!

Q: After switching to version 4, my templates are all broken!
A: Yes, I'm sorry about that, but I updated my templates to be styled as Bootstrap3 based websites. To fix this, you can download an older version and use those templates (don't worry, they are compatible).
I would advice though, to move your templates to your theme-folder. That way, your custom styling can be done and you can also upgrade, as the module-specific templates are overridden bij the themes templates.

Q: I found a bug, what should I do?
A: Preferably, open a ticket on Github. Try to explain as detailed as possible what you did to encounter this bug.

Q: I think your module is stupid.
A: That's not a question now, is it?