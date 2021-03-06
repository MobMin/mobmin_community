#MobMin Resources Community (Pligg)
===================================

This is the repository for the #MobMin Twitter hashtag community portal.  #MobMin is a collection of resources focusing in the use of mobile technology for fulfilling the Great Commission.  Find out more at the [Mobile Ministry Forum](http://mobileministryforum.org) main website.  This community portal is built on the [Pligg](http://pligg.com) content management system.

Production Notes
================

Composer
--------

You will also need to install [Composer](https://getcomposer.org) on your production server.  Once installed, run the following command in the project root directory:
`composer install`

PHP
---

The cron job could take a while if it is processing a lot of links.  Please increase the 'max_execution_time' in your PHP.ini.

Setting Files
-------------

When setting this up for production,  your document root should be the **Webroot** directory.  Code above that directory should be inaccessible!  From the project root directory:

  1. Copy the file `/Config/DatabaseSettings.sample.php` to `/Config/DatabaseSettings.php`
  2. Open the `/Config/DatabaseSettings.php`, add your MySql settings, and save the file.  (You can leave the Postgre's Settings alone since they are not used in Production.)
  3. Copy the file `/Config/EmbedRocksSettings.sample.php` to `/Config/EmbedRocksSettings.php`
  4. Open the `/Config/EmbedRocksSettings.php`, add your [Embed Rocks](http://embed.rocks) API Key, and save the file.
  5. Copy the file `/Config/TwitterSettings.sample.php` to `/Config/TwitterSettings.php`
  6. Open the `/Config/TwitterSettings.php`, add your [Twitter](https://twitter.com) API credentials, and save the file.
  7. Copy the file `/Webroot/settings.php.default` to `/Webroot/settings.php`
  8. Open the `/Webroot/settings.php`, update your settings, and save the file.
  9. Copy the file `/Webroot/libs/dbconnect.php.default` to `Webroot/libs/dbconnect.php`
  10. Open the `/Webroot/libs/dbconnect.php`, update your settings, and save the file.

Submodule
---------

You will now want to pull all the git submodules.  Run the following command in the project root directory:
`git submodule update --init --recursive`

Upgrading
---------

When upgrading,  make sure the links table retains the fields 'social_media_id', 'link_embedly_html', 'link_embedly_author_link', 'link_embedly_author', 'link_embedly_thumb_url', 'link_embedly_type' & 'social_media_account'.  This is to store the Twitter data.

Development Notes
=================

Coding Styles & Techniques
--------------------------

This repository is following the branching technique described in [this blog post](http://nvie.com/posts/a-successful-git-branching-model/), and the semantic version set out on the [Semantic Versioning Website](http://semver.org/).

Cron Job Tests
--------------

In order to run the tests for the Cron Job,  you will need to install all Composer libraries as described in the Production Notes above.  Once installed you will need:


  1. Copy the file `/Tests/Support/DatabaseSettings.sample.php` to `/Tests/Support/DatabaseSettings.php`
  2. Open the `/Tests/Support/DatabaseSettings.php`, add your MySql settings, and save the file.
  3. You will need to import the starting SQL for the [Pligg](http://pligg.com) content management system into your testing database.  You can get a copy by installing a new copy of [Pligg](http://pligg.com) on your development machine.
  4. Run phpunit with the following command:
  `Vendor/bin/phpunit ./Tests`

Issues
------

Questions or problems? Please post them on the [issue tracker](https://github.com/MobMin/mobmin_community/issues). You can contribute changes by forking the project and submitting a pull request.
