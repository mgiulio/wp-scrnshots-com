<?php
/*
Plugin Name: gm_scrnshots
Plugin URI: http://mgiulio.altervista.org
Description: Blah blah
Version: ??.??
License: GPL
Author: Giulio Mainardi
Author URI: http://mgiulio.altervista.org
*/

/*
$username = 'giuliom'; //stripslashes(get_option('scrnshots_scrnshots_id'));
$numItems = 10; //get_option('scrnshots_display_numitems');
*/

$gm_scrnshots_plugin_dir = '';
$gm_scrnshots_plugin_url = '';

/*
 *
 */
//define(ABSPATH, '/membri/giulio');
require_once(ABSPATH . "/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
//require_once("../../../../wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");
//require_once('JSON.php');
function shotsMarkup($username, $numItems) {
	if (true/* Cache is old */) { // USe WP Cron ?
		$out = "<ul>\n"; // class, id?
		
		// Get the feed and parse it
		$feed = /*@*/file_get_contents("http://mgiulio.altervista.org/wp-content/plugins/gm_scrnshots/screenshots.json");//'http://www.scrnshots.com/users/' . $username . '/screenshots.json');
		if (!$feed) echo "Could not load feed";
		$jsonDecoder = new Moxiecode_JSON();
		$json = $jsonDecoder->decode($feed, true);
	
		$numShotsInFeed = count($json);
	
		if ($numShotsInFeed > 0) {
			if ($numShotsInFeed < $numItems)
				$numItems = $numShotsInFeed;

			for ($i = 0; $i < $numItems; $i++) {
				$s = $json[$i];
				
				// Extract data from feed
				$shotPage = $s['url'];
				$title = ($s['description'])? str_replace( "\"","'", $s['description'] ): 'Screenshot from ScrnShots.com';
				$fullSizeUrl = $s['images']['fullsize'];
				
				// Compute the thumbnail filename.
				// Use the numeric Id.
				$parts = array();
				$parts = explode('/', $fullSizeUrl);
				$tnFilename = $parts[5];
				/*
				$matches = array();
				preg_match("/\/\d+\//", $fullSizeUrl, $matches);
				$tnFilename = $matches[0];
				*/
				
				// Determine shot image format
				$tnExt = 'jpg';
				
				$tnFilenamePlusExt = $tnFilename . '.' . $tnExt;
				// Asemble the thumbnail url
	
				global $gm_scrnshots_plugin_dir, $gm_scrnshots_plugin_url;
				$tnPath = "$gm_scrnshots_plugin_dir/tn/$tnFilenamePlusExt";
				$tnUrl = "$gm_scrnshots_plugin_url/tn/$tnFilenamePlusExt";
			
				$out .= "\n<li><a href=\"$shotPage\" title=\"$title\" rel=\"nofollow\"><img src=\"$tnUrl\" alt=\"$title\" /></a></li>";
	
				// Generate the local thumbnail if we don't have it
				if (!file_exists("$tnPath")) {
					$fullSizeUrl = 'http://mgiulio.altervista.org/wp-content/plugins/gm_scrnshots/fullsize-test.jpg';
					$fullIm = imagecreatefromjpeg($fullSizeUrl); // FIXME
					if (!$fullIm)
						echo 'Failed';

					// Compute thumbnail size
					$w = imagesx($fullIm);
					$h = imagesy($fullIm);
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
					imagecopyresampled($tnIm, $fullIm, 0, 0, 0, 0, $tnW, $tnH, $w, $h); 

					// Save it locally
					//echo "Saving: $tnPath\n";
					imagejpeg($tnIm, $tnPath); // This doesn't work
					//fclose(fopen("/wp-content/plugins/scrnshots-com/tn/bar.test", 'w'));
					//fclose(fopen("$tnPath", 'w'));
					//fclose(fopen("./tn/foo.test", 'w'));
					//fclose(fopen("$tnFilenamePlusExt", 'w'));
					//imagejpeg($tnIm, "./tn/$tnFilenamePlusExt"); // This works
					
					imagedestroy($tnIm); // CHECKTHIS
					imagedestroy($fullIm); // CHECKTHIS
				} // Thumbnail generation
			} // Feed items cycle
		} // HTML string creation block
		
		// Close the markup
		$out .= "</ul>\n";
		
		// Store it in persistent storage.
	}
		file_put_contents("$gm_scrnshots_plugin_dir/markup.html", $out);
	
	include("$gm_scrnshots_plugin_dir/markup.html");
}

/*
 * Register the widget
 */
function widget_gm_scrnshots_init() {
	if (!function_exists('register_sidebar_widget')) 
		return;
		
	// Set paths and urls
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

	global $gm_scrnshots_plugin_dir, $gm_scrnshots_plugin_url;
	$gm_scrnshots_plugin_dir = ABSPATH . 'wp-content/plugins/gm_scrnshots';
	$gm_scrnshots_plugin_url = $wp_content_url . '/plugins/gm_scrnshots';
	//$gm_scrnshots_plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR .'/' . dirname(plugin_basename(__FILE__))

	function widget_gm_scrnshots($args) {
		extract($args);
		
		//$options = get_option('widget_gm_scrnshots');
		
		echo $before_widget; 
		shotsMarkup("giuliom", 3);
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

function scrnshots_js() {
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('cycle', $gm_scrnshots_plugin_url . '/jquery.cycle.all.js', array('jquery'));
		wp_enqueue_script('scrnshots_script', $gm_scrnshots_plugin_url .'/script.js', array('jquery'));
	}
}

/*
 * Hooks registration
 */
register_activation_hook(__FILE__, 'on_activation');
register_deactivation_hook(__FILE__, 'on_deactivation');
//add_action('admin_menu', 'scrnshots_admin_menu');
add_action('plugins_loaded', 'widget_gm_scrnshots_init');
//add_action('wp_print_scripts', 'scrnshots_js');

function on_activation()() {
	wp_schedule_event(0, 'daily', 'wp_votd_update_contents');
			$this->set_options('RESET');
			$this->update_contents();
		}
		
		function _on_deactivation() {
			delete_option('wp_votd_options');
			delete_option('wp_votd_cache');
			remove_action('wp_votd_update_contents', 'wp_votd_update_contents');
			wp_clear_scheduled_hook('wp_votd_update_contents');
		}
?>