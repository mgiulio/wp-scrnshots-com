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

/*
 * Setting paths and urls.
 */
global
	$gm_scrnshots_plugin_dir,
	$gm_scrnshots_plugin_url
;
$gm_scrnshots_plugin_dir = plugin_dir_path( __FILE__ );
$gm_scrnshots_plugin_url = plugin_dir_url( __FILE__ ); 

function gm_log( $msg ) {
	trigger_error( "gm_scrnshots: $msg" );
	//error_log($msg);
}

class gm_ScrnShots_Widget extends WP_Widget {

	function gm_ScrnShots_Widget() {
		$this->WP_Widget( 
			'gm_scrnshots_widget_id', // Widget base HTML Id attribute.
			'ScrnShot.com Widget',  // Name for the widget displayed on the configuration page
			array(
				'classname' => 'gm_scrnshots_widget_class',
				'description' => 'Blah Blah'
			) 
		);
	}
	
	function form( $instance ) {
		$defaults = array(
			'username' => '',
			'num_items' => 5
		);
		
		$instance = wp_parse_args( (array)$instance, $defaults );

		$username = $instance['username'];
		$num_items = $instance['num_items'];
		?>
		<p>Username: <input type="text" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo esc_attr( $username ); ?>" class="widefat" ></p>
		<p>Howmany feed items: <input type="text" name="<?php echo $this->get_field_name( 'num_items' ); ?>" value="<?php echo esc_attr( $num_items ); ?>" class="widefat" ></p>
		<?php
	}
	
	function update( $new_instance, $old_instance ) {
		global $gm_scrnshots_plugin_dir;
		
		$instance = $old_instance;
		
		$username = $instance['username'] = strip_tags( $new_instance['username'] );
		$num_items = $instance['num_items'] = strip_tags( $new_instance['num_items'] );
		
		$widget_instance_cache_folder = "instance-$this->number";
		$widget_instance_cache_path = "{$gm_scrnshots_plugin_dir}cache/{$widget_instance_cache_folder}";
		
		if (file_exists( $widget_instance_cache_path ) ) {
			// Clear all the files in the cache
			$objects = scandir($widget_instance_cache_path);
			foreach ($objects as $object)
			if ($object != "." && $object != "..")
				unlink($widget_instance_cache_path."/".$object);
			reset($objects); 
		}
		else {
			// Create the cahe directory for this widget instance
			mkdir( $widget_instance_cache_path );
		}
	
		if ( $username && $num_items ) {
			gm_log( $widget_instance_cache_folder );
			gm_scrnshots_update_feed( $username, $num_items, $widget_instance_cache_folder );
			// Reschedule the event
			wp_clear_scheduled_hook( 'gm_scrnshots_update_feed_event', array( $username, $num_items, $widget_instance_cache_folder )/*$old_instance*/ );
			wp_schedule_event( time() + 180/*3600 * 24*/, 'gm_scrnshots_recurrence', 'gm_scrnshots_update_feed_event', array( $user_name, $num_items, $widget_instance_cache_folder )/*$instance*/ );
		}
		
		return $instance;
	}
	
	function widget( $args, $instance ) {
		global $gm_scrnshots_plugin_dir;
		
		extract( $args );
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo "<ul></ul>";
		echo $after_widget;
	}
}

/*
 * Hooks registration
 */
add_action( 'widgets_init', 'gm_scrnshots_on_widgets_init' );
//add_action( 'wp_register_sidebar_widget', 'gm_scrnshots_on_wp_register_sidebar_widget', 99, 1 );
//add_action( 'wp_unregister_sidebar_widget', 'gm_scrnshots_on_wp_unregister_sidebar_widget', 99, 1 );
add_action( 'wp_print_scripts', 'gm_scrnshots_on_wp_print_scripts' );
add_action( 'wp_print_styles', 'gm_scrnshots_on_wp_print_styles' );
add_action( 'gm_scrnshots_update_feed_event', 'gm_scrnshots_on_update_feed_event', 10, 3);
add_action( 'wp_ajax_gm_scrnshots_ajax_get_feed', 'gm_scrnshots_ajax_get_feed' );
add_action( 'wp_ajax_nopriv_gm_scrnshots_ajax_get_feed', 'gm_scrnshots_ajax_get_feed' );
add_filter( 'cron_schedules', 'gm_scrnshots_schedules' );
register_activation_hook( __FILE__, 'gm_scnshots_on_activation' );
register_deactivation_hook( __FILE__, 'gm_scrnshots_on_deactivation' );

/*
 * Callbacks
 */
function gm_scrnshots_on_update_feed_event( $a, $b, $c ) {
	gm_log( "gm_scrnshots_update_feed_event fired" );
	gm_scrnshots_update_feed( $a, $b, $c );
}

function gm_scrnshots_on_wp_register_sidebar_widget( $widget ) {
	gm_log( "gm_scrnshots_on_wp_register_sidebar_widget: $widget[name], $widget[id]" );
}

function gm_scrnshots_on_wp_unregister_sidebar_widget( $widget ) {
	gm_log( "gm_scrnshots_on_wp_unregister_sidebar_widget: $widget[name], $widget[id]" );
}

function gm_scrnshots_schedules( $arr ) {
	$arr['gm_scrnshots_recurrence'] = array(
		'interval' => 180,
 		'display' => __('gm rec')
	);
	return $arr;
}

function gm_scrnshots_on_activation() {
}
		
function gm_scrnshots_on_deactivation() {
	remove_action( 'gm_scrnshots_update_feed_event', 'gm_scrnshots_update_feed' );
	wp_clear_scheduled_hook( 'gm_scrnshots_update_feed_event' ); // FIXME: what about $args?
}

function gm_scrnshots_on_wp_print_scripts() {
	global $gm_scrnshots_plugin_url;
	
	if (!is_admin()) {
		wp_enqueue_script( "jquery" );
		wp_enqueue_script( "gm_scrnshots_jcycle", "{$gm_scrnshots_plugin_url}jquery.cycle.all.js", array('jquery'), '', true  );
		wp_enqueue_script( "gm_scrnshots_script", "{$gm_scrnshots_plugin_url}js/script.js", array('gm_scrnshots_jcycle'), '', true );
	}
}

function gm_scrnshots_on_wp_print_styles() {
	global $gm_scrnshots_plugin_url;
	
	if (!is_admin()) {
		wp_enqueue_style( "gm_scrnshots_style", "{$gm_scrnshots_plugin_url}css/style.css" );
	}
}

function gm_scrnshots_on_widgets_init() {
	/* wp_register_sidebar_widget( 
		'gm_scrnshots_widget_id', // Id
		'My ScrnShot.com',
		'gm_scrnshots_widget_display',
		array(
			'classname' => 'gm_scrnshots_widget_class',
			'description' => 'Blah blah...'
		)
	); */
	
	 /* wp_register_widget_control(
		'gm_scrnshots_widget_id', // Id
		'My ScrnShot.com',
		'gm_scrnshots_widget_control'
	 ); */
	
	//gm_scrnshots_update_feed();
	
	register_widget( 'gm_ScrnShots_Widget' );
}

function gm_scrnshots_ajax_get_feed() {
	global $gm_scrnshots_plugin_dir;
	include("{$gm_scrnshots_plugin_dir}cache/feed-ajax.json");
	die();
}

/*
 * This function is invoked from WP Cron to update the shots displayed in the widget.
 * Fetch the feed, generate the JSON for the widget content and cache it.
 * Full size images of new items will web retrieved to generate and cache their thumbnails. 
 */
function gm_scrnshots_update_feed( $username, $num_items, $widget_instance_cache_folder ) {
	global
		$gm_scrnshots_plugin_dir,
		$gm_scrnshots_plugin_url
	;
	
	gm_log( "Feed update process started" );
	gm_log( "Arguments: $username, $num_items, $widget_instance_cache_folder");
	
	//$out = '[';
	$out = array();
	
	/*
	 * Fetch the feed
	 */
	$feed_url = 
		//"http://www.scrnshots.com/users/$username/screenshots.json"
		"http://mgiulio.altervista.org/wp-content/plugins/gm_scrnshots/screenshots.json"
	;
	gm_log( "Fetching feed $feed_url" );
	$feed_str = file_get_contents($feed_url);
	if ( ! $feed_str ) {
		gm_log( "Could not load $feed_url" );
		exit();
	}
	gm_log( "Feed loaded" );
	
	/*
	 * Parse it
	 */
	gm_log("Feed parsing started" );
	$json = json_decode( $feed_str, true );
	gm_log("Feed parsing finished" );

	/*
	 * Process it
	 */
	gm_log("Feed processing started" );
	$num_shots_in_feed = count($json);
	gm_log("$num_shots_in_feed items in feed");
	if ($num_shots_in_feed > 0) {
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
			$tnPath = "{$gm_scrnshots_plugin_dir}cache/$widget_instance_cache_folder/$tnFilenamePlusExt";
			$tnUrl  = "{$gm_scrnshots_plugin_url}cache/$widget_instance_cache_folder/$tnFilenamePlusExt";
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
				imagejpeg($tnIm, $tnPath);
				gm_log("Thumb saved");
				
				imagedestroy($tnIm);
				imagedestroy($full_im);
			} // Thumbnail generation
			
			$out[] = array($tnUrl, $title, $shotPage);
			//$out .= "[\"$tnUrl\",\"$title\",\"$shotPage\"],";
		} // Feed items cycle
	}
	
	$out_json = json_encode( $out );
	//$out[strlen($out)-1] = ']';
	
	// Cache the JSON file
	file_put_contents( "{$gm_scrnshots_plugin_dir}cache/$widget_instance_cache_folder/feed-ajax.json", $out_json );
	
	gm_log( "gm_scrnshots: feed $feed_url updated" );
}

?>