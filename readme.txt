=== Li'l Gallery ===

Contributors: andreyk
Tags: image, images, gallery, galleries, shortcode, picture
Requires at least: 2.9.2
Tested up to: 3.4.1
Stable tag: 0.6

Big main picture of a gallery and thumbnails of others, and the main image changes when one clicks thumbnails.

== Description ==

Big main picture of a gallery and thumbnails of others, and the main image changes when one clicks thumbnails. Replaces the standard wordpress [gallery] shortcode output. No flash. Available options: width, heigth, thumbnail height, size, link to image file or not.

== Installation ==

* Upload `lil-gallery.zip` from Plugins/Add New/Upload and activate the plugin.
* Set desirable default options of the plugin at the Li'l Gallery options page.

== Frequently Asked Questions ==

= How can I change some gallery look? =

Here is a gallery shortcode sample with the full set of parameters:
[gallery id="10" width="500" height="400" link="file" size="medium" thumbnail_height="80" order="ASC" orderby="ID" exclude="11,12" featured="exclude"]

Parameters description:

* id: integer, the ID of a post the images attached to. Default: current post ID.
* width: width in pixels (px) of the DIV block that contains the gallery, overflow hidden. Empty by default, then the gallery takes 100% of available width.
* height: height in pixels of the DIV block that contains first image of a gallery. Empty by default. If your gallery includes both portrait and landscape images or different width/height rate the page height may change when you click thumbnails, and you can avoid this inconvenience by setting this parameter for the specific gallery instance.
* link: 'file' or 'none', default 'file' - is the main image clickable or not.
* size: 'medium', 'large' or 'file' - the size of a main image, default 'medium'.
* thumbnail_height: to make thumbnails row looks pretty (it is not real size of your thumbnails), default 60 (pixels).
* order, orderby, exclude - the same as in [standard wordpress gallery](http://codex.wordpress.org/Gallery_Shortcode).
* featured: 'include' (default) or 'exclude' - is the featured image of a post displayed in a gallery or not.

== Screenshots ==

1. A sample how li'l gallery looks.

== Changelog ==

= 0.6 =
* New parameter/option: featured image exclusion.

= 0.5 =
* Fixed bug in links from main image.

= 0.4 =
* Correction of handling link and thumbnail_height parameters, small changes in css.

= 0.3 =
* Now the plugin can use [gallery] or [lil_gallery] shortcode, of your choice.

= 0.2 =
* First public version.

