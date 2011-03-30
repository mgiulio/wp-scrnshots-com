<?php
/*
Plugin Name: ScrnShots.com Carousel
Plugin URI: http://mgiulio.altervista.org
Description: Blah blah
Version: ??.??
Author: Giulio Mainardi
Author URI: http://mgiulio.altervista.org
License: GPL2
*/

/*  Copyright 2011  Giulio Mainardi  (email : giulio.mainardi@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function gm_log($msg) {
	error_log(/*  time() .  */": $msg" );
}

/*
 * Setting paths and urls.
 */
if (!defined('ABSPATH')) {
	die('Please do not load this file directly.');
}

global // Use an array?
	$gm_scrnshots_content_dir, 
	$gm_scrnshots_content_url, 
	$gm_scrnshots_plugin_dir,
	$gm_scrnshots_plugin_url
;

function setPathsAndUrls_WP_DB() {
	global 
		$gm_scrnshots_content_dir, 
		$gm_scrnshots_content_url, 
		$gm_scrnshots_plugin_dir,
		$gm_scrnshots_plugin_url
	;
	
	$gm_scrnshots_content_dir = (defined('WP_CONTENT_DIR')) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
	$gm_scrnshots_content_url = (defined('WP_CONTENT_URL')) ? WP_CONTENT_URL : get_option('siteurl') . '/wp-content';
	$gm_scrnshots_plugin_dir =  (defined('WP_PLUGIN_DIR') ) ? WP_PLUGIN_DIR : $gm_scrnshots_content_dir . '/plugins';
	//$gm_scrnshots_plugin_url = TODO;
}

function setPathsAndUrls_Codex() {
	global 
		$gm_scrnshots_content_dir, 
		$gm_scrnshots_content_url, 
		$gm_scrnshots_plugin_dir,
		$gm_scrnshots_plugin_url
	;
	
	if (!function_exists( 'is_ssl' ) ) {
		function is_ssl() {
			if ( isset($_SERVER['HTTPS']) ) {
				if ( 'on' == strtolower($_SERVER['HTTPS']) )
					return true;
				if ( '1' == $_SERVER['HTTPS'] )
					return true;
			} 
			elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
				return true;
			}
			return false;
		}
	}

	if ( version_compare( get_bloginfo( 'version' ) , '3.0' , '<' ) && is_ssl() ) {
		$wp_content_url = str_replace( 'http://' , 'https://' , get_option( 'siteurl' ) );
	} 
	else {
		$wp_content_url = get_option( 'siteurl' );
	}
	$wp_content_url .= '/wp-content';

	$gm_scrnshots_plugin_dir = ABSPATH . 'wp-content/plugins/gm_scrnshots';
	$gm_scrnshots_plugin_url = $wp_content_url . '/plugins/gm_scrnshots';
	//$gm_scrnshots_plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR .'/' . dirname(plugin_basename(__FILE__))
}

setPathsAndUrls_Codex();
//setPathsAndUrls_WP_DB();

/*
 * Register the widget
 */
function widget_gm_scrnshots_init() {
	if (!function_exists('register_sidebar_widget')) 
		return;
		
	function widget_gm_scrnshots($args) {
		global $gm_scrnshots_plugin_dir;
		
		extract($args);
		
		//$options = get_option('widget_gm_scrnshots');
		
		echo $before_widget; 
		echo $before_title . "ScrnShots.com" . $after_title;
		include("$gm_scrnshots_plugin_dir/cache/markup.html");
		echo $after_widget;
	}

	function widget_gm_scrnshots_control() {
		$options = get_option('widget_gm_scrnshots');

		if ( $_POST['gm_scrnshots-submit'] ) {
			/* $options['title'] = strip_tags(stripslashes($_POST['scrnshots-title']));
			$options['before_images'] = $_POST['scrnshots-beforeimages'];
			$options['after_images'] = $_POST['scrnshots-afterimages'];
			update_option('widget_scrnshots', $options); */
		}

		/* $title = htmlspecialchars($options['title'], ENT_QUOTES);
		$before_images = htmlspecialchars($options['before_images'], ENT_QUOTES);
		$after_images = htmlspecialchars($options['after_images'], ENT_QUOTES); */
		
		/* echo '<p style="text-align:right;"><label for="gm_scrnshots-title">Title: <input style="width: 180px;" id="gsearch-title" name="gm_scrnshots-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="gm_scrnshots-beforeimages">Before all images: <input style="width: 180px;" id="gm_scrnshots-beforeimages" name="gm_scrnshots-beforeimages" type="text" value="'.$before_images.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="gm_scrnshots-afterimages">After all images: <input style="width: 180px;" id="gm_scrnshots-afterimages" name="gm_scrnshots-afterimages" type="text" value="'.$after_images.'" /></label></p>';
		echo '<input type="hidden" id="gm_scrnshots-submit" name="gm_scrnshots-submit" value="1" />'; */
	}		

	register_sidebar_widget('gm_scrnshots', 'widget_gm_scrnshots');
	register_widget_control('gm_scrnshots', 'widget_gm_scrnshots_control', 300, 100);
}


function gm_scrnshots_subpanel() {
    if (isset($_POST['save_gm_scrnshots_settings'])) {
		$option_gm_scrnshots_id = $_POST['gm_scrnshots_id'];
		$option_display_numitems = $_POST['display_numitems'];
		$option_display_imagesize = $_POST['display_imagesize'];
		$option_before = $_POST['before_image'];
		$option_after = $_POST['after_image'];
		$option_useimagecache = $_POST['use_image_cache'];
		$option_imagecacheuri = $_POST['image_cache_uri'];
		$option_imagecachedest = $_POST['image_cache_dest'];
		update_option('gm_scrnshots_gm_scrnshots_id', $option_gm_scrnshots_id);
		update_option('gm_scrnshots_display_numitems', $option_display_numitems);
		update_option('gm_scrnshots_display_imagesize', $option_display_imagesize);
		update_option('gm_scrnshots_before', $option_before);
		update_option('gm_scrnshots_after', $option_after);
		update_option('gm_scrnshots_use_image_cache', $option_useimagecache);
		update_option('gm_scrnshots_image_cache_uri', $option_imagecacheuri);
		update_option('gm_scrnshots_image_cache_dest', $option_imagecachedest);
		?> <div class="updated"><p>scrnshots settings saved</p></div> <?php
     }

	?>

	<div class="wrap">
		<h2>scrnshots Settings</h2>
		
		<form method="post">
		<table class="form-table">
		 <tr valign="top">
		  <th scope="row">ScrnShots.com Username</th>
	      <td><input name="scrnshots_id" type="text" id="scrnshots_id" value="<?php echo get_option('scrnshots_scrnshots_id'); ?>" size="20" /><em> http://www.scrnshots.com/users/<strong>username</strong></em></td>
         </tr>
         <tr valign="top">
          <th scope="row">Display</th>
          <td>
        	<select name="display_numitems" id="display_numitems">
		      <option <?php if(get_option('scrnshots_display_numitems') == '1') { echo 'selected'; } ?> value="1">1</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '2') { echo 'selected'; } ?> value="2">2</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '3') { echo 'selected'; } ?> value="3">3</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '4') { echo 'selected'; } ?> value="4">4</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '5') { echo 'selected'; } ?> value="5">5</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '6') { echo 'selected'; } ?> value="6">6</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '7') { echo 'selected'; } ?> value="7">7</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '8') { echo 'selected'; } ?> value="8">8</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '9') { echo 'selected'; } ?> value="9">9</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '10') { echo 'selected'; } ?> value="10">10</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '11') { echo 'selected'; } ?> value="11">11</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '12') { echo 'selected'; } ?> value="12">12</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '13') { echo 'selected'; } ?> value="13">13</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '14') { echo 'selected'; } ?> value="14">14</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '15') { echo 'selected'; } ?> value="15">15</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '16') { echo 'selected'; } ?> value="16">16</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '17') { echo 'selected'; } ?> value="17">17</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '18') { echo 'selected'; } ?> value="18">18</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '19') { echo 'selected'; } ?> value="19">19</option>
		      <option <?php if(get_option('scrnshots_display_numitems') == '20') { echo 'selected'; } ?> value="20">20</option>
		      </select>
			images, in 
            <select name="display_imagesize" id="display_imagesize">
		      <option <?php if(get_option('scrnshots_display_imagesize') == 'small') { echo 'selected'; } ?> value="small">small</option>
		      <option <?php if(get_option('scrnshots_display_imagesize') == 'medium') { echo 'selected'; } ?> value="medium">medium</option>
		      <option <?php if(get_option('scrnshots_display_imagesize') == 'large') { echo 'selected'; } ?> value="large">large</option>
		      <option <?php if(get_option('scrnshots_display_imagesize') == 'fullsize') { echo 'selected'; } ?> value="fullsize">full</option>
		    </select>
			size.
            </p>
           </td> 
         </tr>
         <tr valign="top">
          <th scope="row">HTML Wrapper</th>
          <td><label for="before_image">Before Image:</label> <input name="before_image" type="text" id="before_image" value="<?php echo htmlspecialchars(stripslashes(get_option('scrnshots_before'))); ?>" size="10" />
        	  <label for="after_image">After Image:</label> <input name="after_image" type="text" id="after_image" value="<?php echo htmlspecialchars(stripslashes(get_option('scrnshots_after'))); ?>" size="10" />
          </td>
         </tr>
         </table>      

        <h3>Cache Settings</h3>
		<p>This allows you to store the images on your server and reduce the load on ScrnShots.com. Make sure the plugin works without the cache enabled first.</p>
		<table class="form-table">
         <tr valign="top">
          <th scope="row">URL</th>
          <td><input name="image_cache_uri" type="text" id="image_cache_uri" value="<?php echo get_option('scrnshots_image_cache_uri'); ?>" size="50" />
          <em>http://yoursite.com/wp-content/scrnshots/</em></td>
         </tr>
         <tr valign="top">
          <th scope="row">Full Path</th>
          <td><input name="image_cache_dest" type="text" id="image_cache_dest" value="<?php echo get_option('scrnshots_image_cache_dest'); ?>" size="50" /> 
          <em>/home/path/to/wp-content/scrnshots/</em></td>
         </tr>
		 <tr valign="top">
		  <th scope="row" colspan="2" class="th-full">
		  <input name="use_image_cache" type="checkbox" id="use_image_cache" value="true" <?php if(get_option('scrnshots_use_image_cache') == 'true') { echo 'checked="checked"'; } ?> />  
		  <label for="use_image_cache">Enable the image cache</label></th>
		 </tr>
        </table>
        <div class="submit">
           <input type="submit" name="save_scrnshots_settings" value="<?php _e('Save Settings', 'save_scrnshots_settings') ?>" />
        </div>
        </form>
    </div>

<?php } // end scrnshots_subpanel()

function scrnshots_admin_menu() {
   if (function_exists('add_options_page'))
	add_options_page('scrnshots Settings', 'scrnshots', 8, basename(__FILE__), 'scrnshots_subpanel');
}

function on_wp_print_scripts() {
	global $gm_scrnshots_plugin_url;
	
	if (!is_admin()) {
		wp_enqueue_script( "jquery" );
		wp_enqueue_script( "cycle", "$gm_scrnshots_plugin_url/jquery.cycle.all.js", array('jquery') );
		wp_enqueue_script( "gm_scrnshots_script", "$gm_scrnshots_plugin_url/script.js", array('jquery') );
	}
}

function on_wp_print_styles() {
	global $gm_scrnshots_plugin_url;
	
	if (!is_admin()) {
		wp_enqueue_style( "gm_scrnshots_style", "$gm_scrnshots_plugin_url/css/style.css" );
	}
}

/*
 * This function is invoked from WP Cron to update the shots displayed in the widget.
 * Fetch the feed, generate the markup for the widget content and cache it.
 * Full size images of new items will web retrieved to generate and cache their thumbnails. 
 * Talk about feed parser.
 */
//define(ABSPATH, '/membri/giulio');
require_once(ABSPATH . "/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
//require_once("../../../../wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
//require_once('JSON.php');
function gm_scrnshots_update_feed() {
	global
		$gm_scrnshots_plugin_dir,
		$gm_scrnshots_plugin_url
	;
	
	gm_log("Feed update process started" );
	
	$markup = "\n<ul>\n"; // class, id?
	
	/*
	 * Fetch the feed
	 */
	$feed_url = 
		//"http://mgiulio.altervista.org/wp-content/plugins/gm_scrnshots/screenshots.json"
		"http://www.scrnshots.com/users/giuliom/screenshots.json"
	;
	gm_log( "Fetching feed $feed_url" );
	$feed_str = @file_get_contents($feed_url);
	if ( ! $feed_str ) {
		gm_log( "Could not load $feed_url: $php_errormsg" );
		wp_mail($developer, "gm_scrnshots", "Could not load $feed_url: $php_errormsg" );
		exit();
	}
	gm_log("Feed loaded" );
	
	/*
	 * Parse it
	 */
	gm_log("Feed parsing started" );
	$jsonDecoder = new Moxiecode_JSON();
	$json = $jsonDecoder->decode($feed_str, true);
	gm_log("Feed parsing finished" );

	/*
	 * Process it
	 */
	gm_log("Feed processing started" );
	$num_shots_in_feed = count($json);
	gm_log("$num_shots_in_feed items in feed");
	if ($num_shots_in_feed > 0) {
		$num_items = 10;
		if ($num_shots_in_feed < $num_items)
			$num_items = $num_shots_in_feed;

		gm_log("Processing $num_items");
		for ($i = 0; $i < $num_items; $i++) {
			gm_log("Feed item #$i");
			$s = $json[$i];
			
			// Extract data from feed
			$shotPage = $s['url'];
			$title = ($s['description'])? str_replace( "\"","'", $s['description'] ): 'Screenshot from ScrnShots.com';
			$fullsize_url = $s['images']['fullsize'];
			gm_log( "Shot page url: $shotPage" );
			gm_log( " Fullsize url: $fullsize_url" );
			
			/* 
			 * Determine the thumbnail filename and extension.
			 * We extract them from the shot's url.
			 * For the filename use the numeric ID.
			 * To determine the image type we don't use getimagesize() for performance reasons.
			 */
			$parts = array();
			$parts = explode('/', $fullsize_url);
			$tnFilename = $parts[5];
			$tnExt = substr( $fullsize_url, -3 );
			$tnFilenamePlusExt = "$tnFilename.$tnExt";
			//
			$tnPath = "$gm_scrnshots_plugin_dir/cache/$tnFilenamePlusExt";
			$tnUrl = "$gm_scrnshots_plugin_url/cache/$tnFilenamePlusExt";
			gm_log("Thumbnail path: $tnPath" );
			gm_log( "Thumbnail url: $tnUrl" );
		
			// Generate the local thumbnail if we don't have it
			if (!file_exists("$tnPath")) {
				gm_log("Cached thumbnail does not exist");
				
				// Fetch the full size shots from ScrnShots.com
				gm_log("Fullsize shot fetching started: $fullsize_url" );
				$full_im;
				switch ( $tnExt ) {
					case 'jpg':
						$full_im = imagecreatefromjpeg( $fullsize_url );
					break;
					case 'png':
						$full_im = imagecreatefrompng( $fullsize_url );
					break;
					default:
						gm_log( "Unsupported image format: " . substr($fullsize_url, -1) );
				}				
				if ( ! $full_im ) {
					gm_log("Could not create thumbnail for $fullsize_url");
					continue;
				}
				gm_log( "Fullsize shot fetching finished: $fullsize_url" );

				// Compute thumbnail size
				$w = imagesx($full_im);
				$h = imagesy($full_im);
				$aspectRatio = (float)$w / (float)$h;
				$tnSize = 240; // Pixels
				if ($aspectRatio > 1.0) { // Landscape format
					$tnW = $tnSize;
					$tnH = $tnSize / $aspectRatio;
				}
				else { // Portrait format
					$tnH = $tnSize;
					$tnW = $tnSize * $aspectRatio;
				}

				// Thumbnail creation
				$tnIm = imagecreatetruecolor($tnW, $tnH);
				imagecopyresampled($tnIm, $full_im, 0, 0, 0, 0, $tnW, $tnH, $w, $h); 

				// Cache it
				imagejpeg($tnIm, $tnPath); // This doesn't work
				gm_log("Thumb saved");
				//fclose(fopen("/wp-content/plugins/scrnshots-com/tn/bar.test", 'w'));
				//fclose(fopen("$tnPath", 'w'));
				//fclose(fopen("./tn/foo.test", 'w'));
				//fclose(fopen("$tnFilenamePlusExt", 'w'));
				//imagejpeg($tnIm, "./tn/$tnFilenamePlusExt"); // This works
				
				imagedestroy($tnIm); // CHECKTHIS
				imagedestroy($full_im); // CHECKTHIS
			} // Thumbnail generation
			
			$markup .= "\n<li><a href=\"$shotPage\" title=\"$title\" rel=\"nofollow\"><img src=\"$tnUrl\" alt=\"$title\" /></a></li>";
		} // Feed items cycle
	} // HTML string creation block
	
	// Close the markup
	$markup .= "</ul>\n";
	
	// Cache it
	file_put_contents("$gm_scrnshots_plugin_dir/cache/markup.html", $markup);
	
	gm_log("gm_scrnshots: feed $feed_url updated");
}

/*
 * Hooks registration
 */
register_activation_hook(__FILE__, 'on_activation');
add_action('gm_scrnshots_update_feed_event', 'gm_scrnshots_update_feed');
register_deactivation_hook(__FILE__, 'on_deactivation');
//add_action('admin_menu', 'scrnshots_admin_menu');
add_action('plugins_loaded', 'on_plugins_loaded');
add_action( 'wp_print_scripts', 'on_wp_print_scripts' );
add_action( 'wp_print_styles', 'on_wp_print_styles' );

function on_activation() {
	gm_scrnshots_update_feed();
	wp_schedule_event( time() + 3600 * 24, 'daily', 'gm_scrnshots_update_feed_event' );
}
		
function on_deactivation() {
	remove_action('gm_scrnshots_update_feed_event', 'gm_scrnshots_update_feed');
	wp_clear_scheduled_hook('gm_scrnshots_update_feed_event');
}

function on_plugins_loaded() {
	gm_log( "on_plugins_loaded" );
	
	/* add_filter('cron_schedules', 'my_cron_definer');    
	function my_cron_definer($schedules) {
	  $schedules['threemin'] = array(
		  'interval'=> 60*3,
		  'display'=>  __('Once Every 3 Minutes')
	  );
	  return $schedules; 
	}*/
	
	//gm_scrnshots_update_feed();
	
	widget_gm_scrnshots_init();
}

/*
$username = 'giuliom'; //stripslashes(get_option('scrnshots_scrnshots_id'));
$num_items = 10; //get_option('scrnshots_display_numitems');
*/

?>