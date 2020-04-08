=== Flo Social ===
Contributors: wpmigo,flothemesplugins
Donate link:
Tags: instagram feed, instagram widget, instagram shortcode, instagram photos, instagram images
Requires at least: 5.0
Tested up to: 5.2.2
Stable tag: 2.3.2
Author: Flothemes
Author URI: https://flothemes.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays the Instagram feed in an easy, clean and beautiful way.

== Description ==

Flo Social is an awesome plugin allowing you to easily add your Instagram feed to your WordPress site and customize it using built in features.

You can display your Instagram images using various methods:

<ul>
        <li><b>Widget</b> - display images using a widget and customize the feed. You can add images from multiple accounts.</li>
        <li><b>Shortcode</b> - add your feed to any page or post using a shortcode.</li>
        <li><b>Gutenberg Block</b> - add your feed to any page or post using a the Gutenberg block.</li>
        <li><b>Embed your feed in the footer</b> - add your feed in the footer of your site, customize it and choose different layouts. This option is only available if you are using a Flotheme. Have a look at <a href="https://flothemes.com/themes/" target="_blank">theme</a> demos to see examples of Instagram feeds.</li>
</ul>

Showcase your Instagram images in a clean and beautiful way and increase your social media engagement!

For more details and step by step instructions read the <a href="https://docs.flothemes.com/flo-instagram-plugin/" target="_blank">documentation here</a>.

NOTE: Flo Social is not an affiliate product of Instagram.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `flo-instagram` directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Visit the 'FLO Social' menu item in your admin sidebar to enter your Instagram user ID.
4. Use the widget or the shortcode. Check documentation for more details: http://docs.flothemes.com/flo-instagram-plugin/

For more details visit: http://docs.flothemes.com/flo-instagram-plugin/

== Frequently asked questions ==

= Instagram Images Not Showing or Broken =

There will be occasions when the API has been changed by Instagram, and the Access token that was previously created no longer works.

In this case the feed may disappear. To get your images working again it is necessary to regenerate the access token. Simply go to Flo Social > Then click “Get Access Toke” and create a new USER ID and ACCESS TOKEN, then “save changes”. You feed should now be showing.

= Broken Image Links =

Another issue may be that the images are showing a broken image icon. If that's the case there are 3 things that may resolve this issue.

<ol>
  <li>Update your plugin to the most recent version, go to plugins and check if there is an update available.</li>
  <li>Clear your browser cache, and any caching on your site (via plugins etc).</li>
  <li>For widgets and feeds, Change the image size for your images, this will regenerate the thumbnails for the images.</li>
</ol>

== Screenshots ==

== Changelog ==

V 2.3.2
Fixed shortcode rendering for search and 404 pages
Fixed admin styles conflict

V 2.3.1
Security Updates

V 2.3.0
Updated plugin name: "Flo Social for Instagram" -> "Flo Social"

V 2.2.9
Updated plugin description
Fixed ajaxurl undefined error

V 2.2.8
Updated plugin name: "Flo Social - Display your Instagram feed" -> "Flo Social for Instagram"
Shortcode - fixed "mobile images per row" option;

V 2.2.7
Footer account selector fixes
Options page - Added notifications
Options page - Added Reset button (deletes all saved accounts)
Implemented error handling for some specific use-cases

V 2.2.6 
Fixed widget padding option (can now be set to zero);
Plugin Options Page: 
Add Account - will now accept profile url and profile id with @ at the start;
Minor UI updates;
Updated logging messages;

V 2.2.5
Bug fixes and stability improvements
Plugin options page - Added some animations to specific actions so that the UI would be more intuitive 

V 2.2.4 - Minor fix for images rendering when cached information has expired

V 2.2.3 - Footer feed styling adjustment

V 2.2.2 - Minor fix for default account

V 2.2.1 - Bug fixes, stability improvements and minor ui redesign

V 2.1 - Improved plugin functionality and fixed unexpected errors

V 2.0.3 - Included Security Improvements and fixed data saving function

V 2.0.2 - Fixed errors if there are problems with accounts

V 2.0.1 - Compatibility with old WordPress versions

V 2.0.0
Added new Instagram functionality with Legacy Support
Added new Interface with multiple accounts
Added Gutenberg Block
Changed the Widget structure
Changed and optimized the Shortcode rendering
Changed the Shortcode generator interface and functionality

V 1.4.8 - Renamed the plugin to Flo Social

V 1.4.7 - Updated token generation URL

V 1.4.6 - If no caching time is set by the user, then we cache the feed for 60 min to avoid being blocked by Instagram.
If no user ID and token is set, then we break the loop requests.

V 1.4.5 - Fixed background conflict with some themes;
Appended the plugin version to the enqued js and css files to avoid the browser caching issue.
Solved conflict with Flothemes Instagram images link in the footer. Now the link to the intagram images works on mobile devices.

V 1.4.4 - For the accounts that return small number of images per request, we requests the images from the 'Next Pages'
                                        until we get 30 images;

V 1.4.3 - Deprecated the crop sizes to solve the broken images links after Instagram changed the link structure.

V 1.4.2 - If we have the next page, we will try to merge the images from the next page with the those from the first page;

V 1.4.1 - Fixed shortcode visibility issue on mobile.

V 1.4 - Changed the images URL structure when cropped images are requested.

V 1.3.6 -  Escape the image caption text.

V 1.3.5 - Adde possibility to delete the transient via a get parameter
        - Now we show the wrong token error message only to editors and administrators

V 1.3.4 - If the access token expires, we show a message about that and ask to regenerate it.

V 1.3.3 - Added image size option for the shortcode
                - Added option to link shortcode images to the original image on Instagram

V 1.3.2 - Changed the acces token generator URL

V 1.3.1 - Fixed warning message

V 1.3 - Added the following actions and filters for developers;
               Actions:
                       flo_insta_before_widget_ul
                       flo_insta_before_image
                       flo_insta_after_image
                       flo_insta_after_widget_ul
               Filters:
                       flo_insta_widget_profile_link
                       flo_insta_before_shortcode_image_block
                       flo_insta_after_shortcode_image_block

V 1.2.2  - Fixed warning appearing in the widget

V 1.2.1 - Fixed the shortcode generator pop up problem

V 1.2  - Added possibility to display images from several account by a using the user ID and the access token for the desired account.

V 1.1 - Changed the client ID to the new Flothemes APP client ID Because of the API permissions changes we can not display any more images by hashtag or other users's images and those options were deprecated. Added auto updater for the plugin.

V 1.0 – Fixed a conflict with the font icons

V 0.9 – Fixed Crop function when "full link image link" is not checked

V 0.7 – WP 4.3 compatibility fix

V 0.6 – Fixed conflict with SEO Yoast plugin

V 0.5 – Added option for images padding

V 0.4 – Added option for horizontal image list for the Flo Social widget

V 0.2 – Made the plugin compatible with WP 3.9 and newer

V 0.1 – Plugin was released

== Upgrade notice ==