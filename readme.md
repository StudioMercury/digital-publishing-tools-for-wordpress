# Digital Publishing Tools for WordPress
### Digital Publishing Tools for WordPress is a plugin that allows anyone to create HTML articles for Digital Publishing Solution directly from WordPress.

**Contributors**: StudioMercury  
**Website**: http://www.smny.us  
**Tags**: digital publishing, publishing, Adobe, DPS, Digital Publishing Solution  
**Requires at least**: Wordpress 3.5  
**Tested up to**: Wordpress 4.2  
**Stable tag**: Wordpress 4.2  
**License**: GPLv2 or later 
**License URI**: http://www.gnu.org/licenses/gpl-2.0.html  


**PREREQUISITE KNOWLEDGE**
* Experience with WordPress (Content Management System) and knowledge of the Adobe Digital Publishing Solution is required. 
* This plugin also assumes you have access to a web server and have basic HTML/CSS knowledge.


**REQUIREMENTS**
* WordPress 3.5 +
* PHP 5.4 or higher
* MySQL 5.0 or higher
* Apache or nginx recommended
* FTP access to the server to install the plugin


**OPTIONAL BUT RECOMMENDED**
* Adobe Digital Publishing Solution API access


## Installation

This section describes how to install the plugin and get it working.

1. Upload the folder `digital-publishing-tools-for-wordpress` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on the `Digital Publishing` icon on the left navigation menu to get started


## Frequently Asked Questions

**Does the plugin support multiple projects?**  
At this time the plugin only supports one project. Multi-project support is on our roadmap.


**Does this plugin work with Wordpress MU (multi-user)?**  
We haven't tested or explicitly built the plugin to work with Wordpress MU. That's not to say it won't work, only that our initial release didn't target a multi-user enviroment. 

## Screenshots
![](assets/screenshot-1.png)  
![](assets/screenshot-2.jpeg)  
![](assets/screenshot-3.jpeg)  
![](assets/screenshot-4.png)


## Changelog

### 2.03
* Fixed a bug where a "/" was being prepended to assets during article bundle. 

### 2.02
* Fixed an error when saving in settings
* Updated the example article.php to expose a function to bundle additional files for an article. 

### 2.01
* Cleaned up sample templates

### 2.0
* NEW Access to the new DPS 2015 API's and services
* Article Creation

### 1.0
* Old Release with access to the original version of DPS, this is currently deprecated.


## Upgrade Notice
* nothing to see here