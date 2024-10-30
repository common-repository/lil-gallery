<?php

/*
Plugin Name: Li&#39;l Gallery
Plugin URI: http://andrey.eto-ya.com/wordpress/my-plugins/lil-gallery
Description: Big main picture of a gallery and thumbnails of others, and the main image changes when one clicks thumbnails. Replaces the standard wordpress gallery shortcode output.
Author: Andrey K.
Version: 0.6.1
Author URI: http://andrey.eto-ya.com/
*/

/*  Copyright 2011 Andrey K. (email: v5@bk.ru, URL: http://andrey.eto-ya.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function lil_gallery_init() {
	global $lg_options;
	$lil_default_options= array('shortcode'=> 'gallery',
		'order'=> 'ASC', 'orderby'=> 'menu_order ID',
		'size'=> 'medium', 'link'=>'file', 'exclude'=>'', 'width'=>'', 'height'=>'', 'thumbnail_height'=> '60', 'featured'=> 'include'
	);
	$lg_options= array_merge($lil_default_options, (array)get_option('lil_gallery') );

	if ( 'gallery' == $lg_options['shortcode'] ) {
		remove_shortcode('gallery');
		add_shortcode('gallery', 'lil_gallery_shortcode');
	}
	elseif ( 'lil_gallery' == $lg_options['shortcode'] ) {
		add_shortcode('lil_gallery', 'lil_gallery_shortcode');
	}
}

add_action('init', 'lil_gallery_init');
add_action('wp_head', 'lil_gallery_css', 9);

function lil_gallery_shortcode($attr) {
	global $post;
	global $lg_options;

	$options= array_merge($lg_options, array('id'=> $post->ID));

	static $instance = 0;
	$instance++;

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts($options, $attr));

	$height= min( (int)abs($height), 3000);
	$width= min( (int)abs($width), 3000);
	$thumbnail_height= min( (int)abs($thumbnail_height), 500);
	$style_thumb= ($thumbnail_height!==$lg_options['thumbnail_height'])?'style="height:'.$thumbnail_height.'px;"':'';

	$id = (int)$id;

	if ( 'exclude' == $featured && current_theme_supports('post-thumbnails') && (int)$thumb_id = get_post_thumbnail_id($id) )
	{
		$exclude= $exclude? $thumb_id.','.$exclude:$thumb_id;
	}

	$attachments = get_children( array('post_parent'=>$id, 'post_status'=>'inherit', 'post_type'=> 'attachment', 'post_mime_type'=>'image', 'order'=> $order, 'orderby'=>$orderby, 'exclude'=> $exclude) );

/* get_children returns an array of objects where the ID of attachments is key - Great! */

	if ( empty($attachments) )
		return '';

		$output = "\r\n";

		if ( is_feed() ) {

		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link($att_id, $size='thumbnail', true) . "\r\n";
		return $output;
	}

	$attcount= count($attachments);

	if ( !$attcount )
		return;

	$i = 0;
	$selector = "gallery-{$instance}";

	$output = '<div id="'.$selector.'" class="lil_wrapper gallery_id-'.$id.'" '.
		(($width && $width!==$lg_options['width'])?'style="width:'.$width.'px;"':'').'>';
	foreach ( $attachments as $att_id => $attachment ) {
		
		$title= apply_filters('the_title', $attachment->post_title);

		$s= wp_get_attachment_image_src($att_id, $size);

		$output .= "\r\n". '<div class="lil-first-image" '.
			(($height && $height!==$lg_options['height'])?'style="height:'.$height.'px;"':'').'>';

		if ( 'none'!=$link ) {	
			$output .= '<a id="lil_a'.$instance.'" title="'.$title.'" href="'.($attachment->guid).'">';
		}
		$output .= '<img alt="'.$title.'" id="lil_img'.$instance.'" src="'.$s[0].'" />';

		if ( 'none'!=$link ) {	
			$output .= '</a>';
		}

		$output .= '</div>';

		break;
	}

/* if only one image we do not want to show thumbnail */
	if ( $attcount<2 ) {
		$output .= '</div><!--/gallery-->';
 		return $output;
	}

	$output .= "\r\n<div class=\"lil_thumbnails\">";
	foreach ( $attachments as $id => $attachment ) {
		$linkto= wp_get_attachment_image_src($id, $size);
		$linkfrom = wp_get_attachment_image_src($id, 'thumbnail');

		$output .= " <a title=\"$attachment->post_title\" href=\"$attachment->guid\" onclick=\"{lil_change_img($instance, '$linkto[0]', '$attachment->guid'); return false;}\"><img src=\"$linkfrom[0]\" $style_thumb /></a>";
		$i++;
	}
	
	$output .= "<div style=\"clear: both;\"></div>\n";
	$output .= "\n</div><!-- /lil_thumbnails --></div>\r\n";

	return $output;
}

function lil_gallery_css() {
	global $lg_options;
echo '
<style type="text/css" title="">
.lil_wrapper { '.($lg_options['width']?('width:'.$lg_options['width'].'px;'):'').' display:block; clear: both; overflow:hidden;}'
.($lg_options['height']?(' .lil-first-image {height:'.$lg_options['height'].'px; overflow: hidden;}'):'').
' .lil_wrapper img {padding:1px !important; border:solid 1px #cfcfcf; margin:1px;}
.lil_thumbnails {margin-bottom: 12px; }
.lil_thumbnails a img {float:left; height:'.$lg_options['thumbnail_height'].'px;}

</style>

<script type="text/javascript">
function lil_change_img(gins, linkto, guid) {
	document.getElementById("lil_img"+gins).src= linkto;
	if ( document.getElementById("lil_a"+gins) ) 
		document.getElementById("lil_a"+gins).href= guid;
}
</script>
';
}

/* -- Administer -- */
add_action('admin_menu', 'lil_gallery_menu');

function lil_gallery_menu() {
	add_submenu_page('upload.php', 'Li&#39;l gallery options', 'Li&#39;l Gallery', 'manage_options', 'lil_gallery_settings', 'lil_gallery_settings_page');
	add_action( 'admin_init', 'register_lil_gallery_settings' );
}

function register_lil_gallery_settings() {
	register_setting('lg-settings-group', 'lil_gallery', 'lil_gallery_sanitize');
}

function lil_gallery_sanitize($ret) {
	global $lg_options;

	$ret['height']= (int)$ret['height'];
	if ( $ret['height']<=0 ) 
		$ret['height']= '';

	$ret['width']= (int)$ret['width'];
	if ( $ret['width']<=0 ) 
		$ret['width']= '';

	$ret['thumbnail_height']= (int)$ret['thumbnail_height'];
	if ( $ret['thumbnail_height']<=0 ) 
		$ret['thumbnail_height']= '60';

	if  ( !in_array($ret['size'], array('medium', 'large', 'file') ) )
		$ret['size']= 'medium';

	if  ( !in_array($ret['link'], array('none', 'file') ) )
		$ret['link']= 'file';

	if  ( !in_array($ret['shortcode'], array('gallery', 'lil_gallery') ) )
		$ret['shortcode']= 'gallery';

	if  ( !in_array($ret['featured'], array('exclude', 'include') ) )
		$ret['featured']= 'include';

	return $ret;
}

function lil_gallery_settings_page() {
	global $lg_options;
?>
<div class="wrap">
<div id="icon-upload" class="icon32"><br /></div>
<h2>Li&#39;l Gallery plugin options</h2>

<form method="post" action="options.php" id="lil_gallery_settings_form">
    <?php settings_fields( 'lg-settings-group' ); ?>
    <table class="form-table">

    <tr valign="top">
        <th>Shortcode</th>
        <td>
<?php
		foreach ( array('gallery', 'lil_gallery') as $val )
			echo '<input name="lil_gallery[shortcode]" type="radio" value="'.$val.'" '.($val==$lg_options['shortcode'] ?' checked="checked"':'').' />'.$val.' &nbsp; ';	
		 ?></select>
		 <br /> <span class="description">If you select <em style="font-weight:bold">lil_gallery</em> then the plugin recognizes <code>[lil_gallery]</code> shortcode and you may use <code>[gallery]</code> shotrcode with another plugin or for usual wordpress gallery.</span>
	 </td>     
	</tr>

    <tr valign="top">
        <th>Gallery width</th>
        <td><input size="4" type="text" name="lil_gallery[width]" value="<?php echo $lg_options['width']; ?>" /> px <br />

<span class="description">If empty then it takes 100% width of it&#39;s container</span>
	 </td>     
	</tr>
    <tr valign="top">
        <th>Maximum height of a first image</th>
        <td ><input size="4" type="text" name="lil_gallery[height]" value="<?php echo $lg_options['height']; ?>" /> px <br />
<span class="description"> If empty then in case your gallery includes both portrait and landscape images the page height may change when you click thumbnails.</span>
	</tr>

    <tr valign="top">
        <th>Thumbnails height</th>
        <td><input size="4" type="text" name="lil_gallery[thumbnail_height]" value="<?php echo $lg_options['thumbnail_height']; ?>" /> px<br />
<span class="description"> 
To make thumbnails row looks pretty (it is not real size of your thumbnails)</span>
	 </td>     
	</tr>

    <tr valign="top">
        <th>Size of a first image</th>
        <td>
		<select name="lil_gallery[size]"><?php
		foreach ( array('medium', 'large', 'file') as $val )
			echo '<option value="'.$val.'" '.($val==$lg_options['size'] ?' selected="selected"':'').'>'.$val.'</option>';	
		 ?></select>
		</td>
   
	</tr>

    <tr valign="top">
        <th >Main image links to</th>
        <td>
		<select name="lil_gallery[link]"><?php
		foreach ( array('none', 'file') as $val )
			echo '<option value="'.$val.'" '.($val==$lg_options['link'] ?' selected="selected"':'').'>'.$val.'</option>';
		 ?></select>
		</td>
	</tr>

    <tr valign="top">
        <th >Exclude/include featured image?</th>
        <td>
<?php
		foreach ( array('include', 'exclude') as $val )
			echo '<input type="radio" name="lil_gallery[featured]" value="'.$val.'" '.($val==$lg_options['featured'] ?' checked':'').'/>'.$val.' &nbsp; ';
		 ?>
		</td>
	</tr>
    </table>
    
    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
</form>
</div>
<?php
	
}