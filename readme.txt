=== Digital Publishing Tools for WordPress ===
Contributors: StudioMercury
Tags: digital publishing, publishing, Adobe, DPS, Digital Publishing Solution
Requires at least: 3.5
Tested up to: 4.3.1
Stable tag: 2.0.7
License: GPLv2 or later

Digital Publishing Tools for WordPress allows anyone to create HTML articles for Adobe's Digital Publishing Solution directly from WordPress.

== Description ==

Digital Publishing Tools for WordPress is a plugin that allows anyone to create HTML articles for Adobe's Digital Publishing Solution directly from WordPress.

= Prerequisite Knowledge =

Experience with WordPress (Content Management System) and knowledge of the Adobe Digital Publishing Solution is required. This plugin also assumes you have access to a web server and have basic HTML/CSS knowledge.

= Requirements =

WordPress 3.5 +   
* PHP 5.4 or higher  
* MySQL 5.0 or higher   
* Apache or nginx recommended  
* FTP access to the server to install the plugin  

= Optional But Recommended =
<a href="http://www.adobe.com/products/digital-publishing-solution.html">Adobe Digital Publishing Solution</a> API access

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
3. Preview articles before you export them or upload them into the Adobe Digital Publishing Solution platform.
4. The plugin works with Adobe's Digital Publishing Solution. Entering API credentials for DPS will allow you to upload and manipulate articles in Adobe's platform.

== Changelog ==

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