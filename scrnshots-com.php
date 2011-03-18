<?php
/*
Plugin Name: scrnshots-com
Plugin URI: 
Description: 
Version: 
License: GPL
Author: Giulio Mainardi
Author URI: http://mgiulio.altervista.org
*/

require_once(ABSPATH . "/wp-includes/js/tinymce/plugins/spellchecker/classes/utils/JSON.php");

function shotsMarkup() {
	if (true/* Cache is old */) { // USe WP Cron ?
		$out = '<ul></ul>'; // class, id?
		
		// Retrieve the feed options
		$numItems = 10; //get_option('scrnshotsRSS_display_numitems');
		$username = 'giuliom'; //stripslashes(get_option('scrnshotsRSS_scrnshots_id'));

		// Get the feed and parse it
		$feed = @file_get_contents('http://www.scrnshots.com/users/' . $username . '/screenshots.json');
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
				$matches = array();
				preg_match("/\/\d+\//", $fullSizeUrl, $matches);
				$tnFilename = $matches[0];
				
				// Determine shot image format
				$tnExt = 'jpg';
				
				$localTnUrl = $tnDir . $tnFilename . '.' . $tnExt;
				
				$out .= "<li><a href=\"$shotPage\" title=\"$title\" rel=\"nofollow\"><img src=\"$localTnUrl\" alt=\"$title\" /></a></li>";
	
				// Generate the local thumbnail if we don't have it
				if (!file_exists('$localTnUrl')) {
					$fullIm = imagecreatefromjpeg($fullSizeUrl);
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

					imagejpeg($tnIm, $localTnUrl);
					imagedestroy($tnIm);
					imagedestroy($fullIm);
				} // Thumbnail generation
			} // Feed items cycle
		} // HTML string creation block
		// Store in persistent storage. fwrite($out, cachedHTML);
	}
	
	$out = // Retrieve from persisten storage. include($cachedHTMLPath);
	return $out;
}

function widget_scrnshotsRSS_init() {
	if (!function_exists('register_sidebar_widget')) return;

	function widget_scrnshotsRSS($args) {
		
		extract($args);

		$options = get_option('widget_scrnshotsRSS');
		$title = $options['title'];
		$before_images = $options['before_images'];
		$after_images = $options['after_images'];
		
		$before_images = '<div class="slideshow">';
		$after_images = '</div> <!-- .slideshow -->';
		echo $before_widget . $before_title . $title . $after_title . $before_images;
		echo shotsMarkup();
		echo $after_images . $after_widget;
	}

	function widget_scrnshotsRSS_control() {
		$options = get_option('widget_scrnshotsRSS');

		if ( $_POST['scrnshotsRSS-submit'] ) {
			$options['title'] = strip_tags(stripslashes($_POST['scrnshotsRSS-title']));
			$options['before_images'] = $_POST['scrnshotsRSS-beforeimages'];
			$options['after_images'] = $_POST['scrnshotsRSS-afterimages'];
			update_option('widget_scrnshotsRSS', $options);
		}

		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$before_images = htmlspecialchars($options['before_images'], ENT_QUOTES);
		$after_images = htmlspecialchars($options['after_images'], ENT_QUOTES);
		
		echo '<p style="text-align:right;"><label for="scrnshotsRSS-title">Title: <input style="width: 180px;" id="gsearch-title" name="scrnshotsRSS-title" type="text" value="'.$title.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="scrnshotsRSS-beforeimages">Before all images: <input style="width: 180px;" id="scrnshotsRSS-beforeimages" name="scrnshotsRSS-beforeimages" type="text" value="'.$before_images.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="scrnshotsRSS-afterimages">After all images: <input style="width: 180px;" id="scrnshotsRSS-afterimages" name="scrnshotsRSS-afterimages" type="text" value="'.$after_images.'" /></label></p>';
		echo '<input type="hidden" id="scrnshotsRSS-submit" name="scrnshotsRSS-submit" value="1" />';
	}		

	register_sidebar_widget('scrnshotsRSS', 'widget_scrnshotsRSS');
	register_widget_control('scrnshotsRSS', 'widget_scrnshotsRSS_control', 300, 100);
}


function scrnshotsRSS_subpanel() {
     if (isset($_POST['save_scrnshotsRSS_settings'])) {
       $option_scrnshots_id = $_POST['scrnshots_id'];
       $option_display_numitems = $_POST['display_numitems'];
       $option_display_imagesize = $_POST['display_imagesize'];
       $option_before = $_POST['before_image'];
       $option_after = $_POST['after_image'];
       $option_useimagecache = $_POST['use_image_cache'];
       $option_imagecacheuri = $_POST['image_cache_uri'];
       $option_imagecachedest = $_POST['image_cache_dest'];
       update_option('scrnshotsRSS_scrnshots_id', $option_scrnshots_id);
       update_option('scrnshotsRSS_display_numitems', $option_display_numitems);
       update_option('scrnshotsRSS_display_imagesize', $option_display_imagesize);
       update_option('scrnshotsRSS_before', $option_before);
       update_option('scrnshotsRSS_after', $option_after);
       update_option('scrnshotsRSS_use_image_cache', $option_useimagecache);
       update_option('scrnshotsRSS_image_cache_uri', $option_imagecacheuri);
       update_option('scrnshotsRSS_image_cache_dest', $option_imagecachedest);
       ?> <div class="updated"><p>scrnshotsRSS settings saved</p></div> <?php
     }

	?>

	<div class="wrap">
		<h2>scrnshotsRSS Settings</h2>
		
		<form method="post">
		<table class="form-table">
		 <tr valign="top">
		  <th scope="row">ScrnShots.com Username</th>
	      <td><input name="scrnshots_id" type="text" id="scrnshots_id" value="<?php echo get_option('scrnshotsRSS_scrnshots_id'); ?>" size="20" /><em> http://www.scrnshots.com/users/<strong>username</strong></em></td>
         </tr>
         <tr valign="top">
          <th scope="row">Display</th>
          <td>
        	<select name="display_numitems" id="display_numitems">
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '1') { echo 'selected'; } ?> value="1">1</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '2') { echo 'selected'; } ?> value="2">2</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '3') { echo 'selected'; } ?> value="3">3</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '4') { echo 'selected'; } ?> value="4">4</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '5') { echo 'selected'; } ?> value="5">5</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '6') { echo 'selected'; } ?> value="6">6</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '7') { echo 'selected'; } ?> value="7">7</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '8') { echo 'selected'; } ?> value="8">8</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '9') { echo 'selected'; } ?> value="9">9</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '10') { echo 'selected'; } ?> value="10">10</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '11') { echo 'selected'; } ?> value="11">11</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '12') { echo 'selected'; } ?> value="12">12</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '13') { echo 'selected'; } ?> value="13">13</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '14') { echo 'selected'; } ?> value="14">14</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '15') { echo 'selected'; } ?> value="15">15</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '16') { echo 'selected'; } ?> value="16">16</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '17') { echo 'selected'; } ?> value="17">17</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '18') { echo 'selected'; } ?> value="18">18</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '19') { echo 'selected'; } ?> value="19">19</option>
		      <option <?php if(get_option('scrnshotsRSS_display_numitems') == '20') { echo 'selected'; } ?> value="20">20</option>
		      </select>
			images, in 
            <select name="display_imagesize" id="display_imagesize">
		      <option <?php if(get_option('scrnshotsRSS_display_imagesize') == 'small') { echo 'selected'; } ?> value="small">small</option>
		      <option <?php if(get_option('scrnshotsRSS_display_imagesize') == 'medium') { echo 'selected'; } ?> value="medium">medium</option>
		      <option <?php if(get_option('scrnshotsRSS_display_imagesize') == 'large') { echo 'selected'; } ?> value="large">large</option>
		      <option <?php if(get_option('scrnshotsRSS_display_imagesize') == 'fullsize') { echo 'selected'; } ?> value="fullsize">full</option>
		    </select>
			size.
            </p>
           </td> 
         </tr>
         <tr valign="top">
          <th scope="row">HTML Wrapper</th>
          <td><label for="before_image">Before Image:</label> <input name="before_image" type="text" id="before_image" value="<?php echo htmlspecialchars(stripslashes(get_option('scrnshotsRSS_before'))); ?>" size="10" />
        	  <label for="after_image">After Image:</label> <input name="after_image" type="text" id="after_image" value="<?php echo htmlspecialchars(stripslashes(get_option('scrnshotsRSS_after'))); ?>" size="10" />
          </td>
         </tr>
         </table>      

        <h3>Cache Settings</h3>
		<p>This allows you to store the images on your server and reduce the load on ScrnShots.com. Make sure the plugin works without the cache enabled first.</p>
		<table class="form-table">
         <tr valign="top">
          <th scope="row">URL</th>
          <td><input name="image_cache_uri" type="text" id="image_cache_uri" value="<?php echo get_option('scrnshotsRSS_image_cache_uri'); ?>" size="50" />
          <em>http://yoursite.com/wp-content/scrnshotsrss/</em></td>
         </tr>
         <tr valign="top">
          <th scope="row">Full Path</th>
          <td><input name="image_cache_dest" type="text" id="image_cache_dest" value="<?php echo get_option('scrnshotsRSS_image_cache_dest'); ?>" size="50" /> 
          <em>/home/path/to/wp-content/scrnshotsrss/</em></td>
         </tr>
		 <tr valign="top">
		  <th scope="row" colspan="2" class="th-full">
		  <input name="use_image_cache" type="checkbox" id="use_image_cache" value="true" <?php if(get_option('scrnshotsRSS_use_image_cache') == 'true') { echo 'checked="checked"'; } ?> />  
		  <label for="use_image_cache">Enable the image cache</label></th>
		 </tr>
        </table>
        <div class="submit">
           <input type="submit" name="save_scrnshotsRSS_settings" value="<?php _e('Save Settings', 'save_scrnshotsRSS_settings') ?>" />
        </div>
        </form>
    </div>

<?php } // end scrnshotsRSS_subpanel()

function scrnshotsRSS_admin_menu() {
   if (function_exists('add_options_page')) {
        add_options_page('scrnshotsRSS Settings', 'scrnshotsRSS', 8, basename(__FILE__), 'scrnshotsRSS_subpanel');
        }
}

function scrnshotsRSS_js() {
	$plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR .'/' . dirname(plugin_basename(__FILE__));
 
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('cycle', $plugin_url . '/jquery.cycle.all.js', array('jquery'));
		wp_enqueue_script('scrnshotsRSS_script', $plugin_url .'/script.js', array('jquery'));
	}
}

add_action('admin_menu', 'scrnshotsRSS_admin_menu');
add_action('plugins_loaded', 'widget_scrnshotsRSS_init');
add_action('wp_print_scripts', 'scrnshotsRSS_js');
?>