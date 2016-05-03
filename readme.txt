=== Digital Publishing Tools for WordPress ===
Contributors: StudioMercury
Tags: digital publishing, publishing, Adobe, AEM Mobile, Adobe Experience Manager Mobile, DPS, Digital Publishing Solution   
Requires at least: 3.5
Tested up to: 4.3.1
Stable tag: 2.1
License: GPLv2 or later

Digital Publishing Tools for WordPress allows anyone to create HTML articles for Adobe Experience Manager Mobile (AEM Mobile) directly from WordPress.

== Description ==

Digital Publishing Tools for WordPress is a plugin that allows anyone to create HTML articles for Adobe Experience Manager Mobile (AEM Mobile) directly from WordPress.

= Prerequisite Knowledge =

Experience with WordPress (Content Management System) and knowledge of Adobe Experience Manager Mobile (AEM Mobile) is required. This plugin also assumes you have access to a web server and have basic HTML/CSS knowledge.

= Requirements =

WordPress 3.5 +   
* PHP 5.4 or higher  
* MySQL 5.0 or higher   
* Apache or nginx recommended  
* FTP access to the server to install the plugin  

= Optional But Recommended =
<a href="http://www.adobe.com/marketing-cloud/enterprise-content-management/mobile-app-development.html">Adobe Experience Manager Mobile (AEM Mobile)</a> API access

== Installation ==

This section describes how to install the plugin and get it working.

= From your WordPress dashboard =
1. Visit 'Plugins > Add New'
2. Search for 'Digital Publishing'
3. Activate Digital Publishing Tools from your Plugins page.
4. Click on the `Digital Publishing` icon on the left navigation menu to get started

= From your WordPress dashboard =
1. Download Digital Publishing Tools for WordPress.
2. Upload the 'digital-publishing-tools-for-wordpress' directory to your '/wp-content/plugins/' directory
3. Activate Digital Publishing Tools from your Plugins page.
4. Click on the 'Digital Publishing' icon on the left navigation menu to get started

= Once Activated =

For more information about how to use the plugin, please see the <a href="http://studiomercury.github.io/digital-publishing-tools-for-wordpress/">plugin page</a> for a tutorial.

= Support =

Please use the GitHub page for <a href="https://github.com/StudioMercury/digital-publishing-tools-for-wordpress/issues">Digital Publishing Tools for WordPress</a>.

== Frequently Asked Questions ==

= Does the plugin support multiple projects? =

At this time the plugin only supports one project. Multi-project support is on our roadmap.

= Does this plugin work with Wordpress MU (multi-user)? =
We haven't tested or explicitly built the plugin to work with Wordpress MU. That's not to say it won't work, only that our initial release didn't target a multi-user environment. 

== Screenshots ==

1. Keep track of all of your articles. You can easily create new or import existing articles directly from WordPress.
2. Easily edit Article metadata.
3. Preview articles before you export them or upload them into Adobe Experience Manager Mobile (AEM Mobile).
4. The plugin works with Adobe Experience Manager Mobile (AEM Mobile). Entering API credentials for AEM Mobile will allow you to upload and manipulate articles in Adobe's platform.

== Changelog ==

= 2.1 =
** New Features ** 
* Import Presets: You can now customize how post data is imported as an AEM Mobile Article
* Added the ability to create new device preview sizes for use when editing articles. 
* Added Image Sizes for use in AEM Mobile's cards/layout view.
* Sync allows you to pull changes from the original article if you've made updates to the original post. 
* System Status shows you all required libraries and if they are installed on your server.

** Enhancements **
* Article List View now lazy loads to prevent memory errors
* UI Enhancements
* New tooltips in the Article List View to expand on what the cloud icons mean


** General **
* New plugin web site with better documentation and new support options
* Metadata validation to prevent errors when pushing content to AEM Mobile
* Changed references to DPS to AEM Mobile

= 2.0.10 =
* TEMPLATE CHANGES

1. If you are using the default template and you haven't moved the `publish-templates` folder into the theme folder, you don't have to do anything.
2. If you're using the default template and have moved the `publish-templates` folder into the theme folder, you need to copy the new `publish-templates` folder into your current theme.
3. If you've created your own, please review the information below:

-- All relative links will be turned into full URLs: for example: if you create a link `/image/1.jpg` the packager will turn this into: `http://yourdomain.com/image/1.jpg`. Relative links are no longer relative to the .article file. This is part of a bigger move to allow any theme / template to be packaged. That will come in a release soon.

-- There's a new way to add files to the .article using the `dpsfa_bundle_article` filter:

Automatic: Specify full url to file (array of images)
Specifying the full url will create the necessary folder structure in the article and download the external file
Folder structure for external resources: ARTICLE > sanitized hostname > path > file
Example: array('http://www.domain.com/wp-content/themes/theme/file.jpg') will put that file in the article as: domaincom/wp-content/themes/theme/file.jpg
    
Manual: Specify the full paths array( "file path relative in article" => "file path relative to server (or url)" )
You can have control over where the file is placed in the article and where to pull it from the server
Example: array( array('slideshow/image/file.jpg' => 'www/wp-content/themes/theme/file.jpg') ) will put that file in the article as: domaincom/wp-content/themes/theme/file.jpg


= 2.0.6 =
* Resolved article folio uploads with 0bytes (missing files).
* Resolved an issue if template files went missing or were changed
* publish-templates/article.php - now has a file path for files relative to the plugin folder and the theme folder. If you're upgrading to 2.0.6 please make sure to re-copy the publish-templates folder from the plugin folder to the active theme folder you are working in.


= 2.0.5 =
* Resolved error in checking for serialized meta values
* Resolved error in the article thumbnail when importing them from
existing posts
* Internal keywords now sync

= 2.0.4 =
* Fixed error in getting settings / refreshing settings
* Added error logging for php errors that show up
* Resolved error in returning and saving settings using AJAX

= 2.0.3 =
* Fixed a bug where a "/" was being prepended to assets during article bundle. 

= 2.0.2 =
* Fixed an error when saving in settings
* Updated the example article.php to expose a function to bundle additional files for an article. 

= 2.0.1 =
* Cleaned up sample templates

= 2.0.0 =
* NEW Access to the new DPS 2015 API's and services
* Article Creation

= 1.0.0 =
* Old Release with access to the original version of DPS, this is currently deprecated.