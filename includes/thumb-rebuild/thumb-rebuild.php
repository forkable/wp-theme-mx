<?php /*

**************************************************************************

Plugin Name:  Regenerate Thumbnails
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/regenerate-thumbnails/
Description:  Allows you to regenerate all thumbnails after changing the thumbnail sizes.
Version:      2.2.4
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Copyright (C) 2008-2011 Viper007Bond

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/
if(!class_exists('RegenerateThumbnails')){

class RegenerateThumbnails {
	var $menu_id;
	// Plugin initialization
	function RegenerateThumbnails() {
		// Load up the localization file if we're using WordPress in a different language
		// Place it in this plugin's "localization" folder and name it "regenerate-thumbnails-[value in wp-config].mo"
		// load_plugin_textdomain( 'regenerate-thumbnails', false, '/regenerate-thumbnails/localization' );

		add_action( 'admin_menu',                              array( &$this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts',                   array( &$this, 'admin_enqueues' ) );
		add_action( 'wp_ajax_regeneratethumbnail',             array( &$this, 'ajax_process_image' ) );
		add_filter( 'media_row_actions',                       array( &$this, 'add_media_row_action' ), 10, 2 );
		//add_filter( 'bulk_actions-upload',                     array( &$this, 'add_bulk_actions' ), 99 ); // A last minute change to 3.1 makes this no longer work
		add_action( 'admin_head-upload.php',                   array( &$this, 'add_bulk_actions_via_javascript' ) );
		add_action( 'admin_action_bulk_regenerate_thumbnails', array( &$this, 'bulk_action_handler' ) ); // Top drowndown
		add_action( 'admin_action_-1',                         array( &$this, 'bulk_action_handler' ) ); // Bottom dropdown (assumes top dropdown = default value)

		// Allow people to change what capability is required to use this plugin
		$this->capability = apply_filters( 'regenerate_thumbs_cap', 'manage_options' );
	}


	// Register the management page
	function add_admin_menu() {
		$this->menu_id = add_management_page( ___( 'Regenerate Thumbnails'), ___( 'Regen. Thumbnails'), $this->capability, 'regenerate-thumbnails', array(&$this, 'regenerate_interface') );
	}


	// Enqueue the needed Javascript and CSS
	function admin_enqueues( $hook_suffix ) {
		if ( $hook_suffix != $this->menu_id )
			return;

		// WordPress 3.1 vs older version compatibility
		if ( wp_script_is( 'jquery-ui-widget', 'registered' ) )
			wp_enqueue_script( 'jquery-ui-progressbar', theme_features::get_theme_includes_js( __FILE__,'jquery-ui/jquery.ui.progressbar.min.js' ), array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6' );
		else
			wp_enqueue_script( 'jquery-ui-progressbar', theme_features::get_theme_includes_js(__DIR__,'jquery-ui/jquery.ui.progressbar.min.1.7.2.js'  ), array( 'jquery-ui-core' ), '1.7.2' );

		wp_enqueue_style( 'jquery-ui-regenthumbs', theme_features::get_theme_includes_css(__DIR__,'jquery-ui-1.7.2.custom.css' ), [], '1.7.2' );
	}


	// Add a "Regenerate Thumbnails" link to the media row actions
	function add_media_row_action( $actions, $post ) {
		if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) || ! theme_cache::current_user_can( $this->capability ) )
			return $actions;

		$url = wp_nonce_url( admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $post->ID ), 'regenerate-thumbnails' );
		$actions['regenerate_thumbnails'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( ___( "Regenerate the thumbnails for this single image") ) . '">' . ___( 'Regenerate Thumbnails') . '</a>';

		return $actions;
	}


	// Add "Regenerate Thumbnails" to the Bulk Actions media dropdown
	function add_bulk_actions( $actions ) {
		$delete = false;
		if ( ! empty( $actions['delete'] ) ) {
			$delete = $actions['delete'];
			unset( $actions['delete'] );
		}

		$actions['bulk_regenerate_thumbnails'] = ___( 'Regenerate Thumbnails');

		if ( $delete )
			$actions['delete'] = $delete;

		return $actions;
	}


	// Add new items to the Bulk Actions using Javascript
	// A last minute change to the "bulk_actions-xxxxx" filter in 3.1 made it not possible to add items using that
	function add_bulk_actions_via_javascript() {
		if ( ! theme_cache::current_user_can( $this->capability ) )
			return;
?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$('select[name^="action"] option:last-child').before('<option value="bulk_regenerate_thumbnails"><?= esc_attr( ___( 'Regenerate Thumbnails') ); ?></option>');
			});
		</script>
<?php
	}


	// Handles the bulk actions POST
	function bulk_action_handler() {
		if ( empty( $_REQUEST['action'] ) || ( 'bulk_regenerate_thumbnails' != $_REQUEST['action'] && 'bulk_regenerate_thumbnails' != $_REQUEST['action2'] ) )
			return;

		if ( empty( $_REQUEST['media'] ) || ! is_array( $_REQUEST['media'] ) )
			return;

		check_admin_referer( 'bulk-media' );

		$ids = implode( ',', array_map( 'intval', $_REQUEST['media'] ) );

		// Can't use wp_nonce_url() as it escapes HTML entities
		wp_redirect( add_query_arg( '_wpnonce', wp_create_nonce( 'regenerate-thumbnails' ), admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $ids ) ) );
		exit();
	}


	// The user interface plus thumbnail regenerator
	function regenerate_interface() {
		global $wpdb;

		?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap regenthumbs">
	<h2><?= ___('Regenerate Thumbnails'); ?></h2>

<?php

		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( ! theme_cache::current_user_can( $this->capability ) )
				wp_die( ___( 'Cheatin&#8217; uh?' ) );

			// Form nonce check
			check_admin_referer( 'regenerate-thumbnails' );

			// Create the list of image IDs
			if ( ! empty( $_REQUEST['ids'] ) ) {
				$images = array_map( 'intval', explode( ',', trim( $_REQUEST['ids'], ',' ) ) );
				$ids = implode( ',', $images );
			} else {
				// Directly querying the database is normally frowned upon, but all
				// of the API functions will return the full post objects which will
				// suck up lots of memory. This is best, just not as future proof.
				if ( ! $images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC" ) ) {
					echo '	<p>' . sprintf( ___( "Unable to find any images. Are you sure <a href='%s'>some exist</a>?"), admin_url( 'upload.php?post_mime_type=image' ) ) . "</p></div>";
					return;
				}

				// Generate the list of IDs
				$ids = [];
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );
			}

			echo '	<p>' . ___( "Please be patient while the thumbnails are regenerated. This can take a while if your server is slow (inexpensive hosting) or if you have many images. Do not navigate away from this page until this script is done or the thumbnails will not be resized. You will be notified via this page when the regenerating is completed.") . '</p>';

			$count = count( $images );

			$text_goback = ( ! empty( $_GET['goback'] ) ) ? sprintf( ___( 'To go back to the previous page, <a href="%s">click here</a>.'), 'javascript:history.go(-1)' ) : '';
			$text_failures = sprintf( ___( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s). To try regenerating the failed images again, <a href="%4$s">click here</a>. %5$s'), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'tools.php?page=regenerate-thumbnails&goback=1' ), 'regenerate-thumbnails' ) . '&ids=' ) . "' + rt_failedlist + '", $text_goback );
			$text_nofailures = sprintf( ___( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were 0 failures. %3$s'), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>


	<noscript><p><em><?= ___( 'You must enable Javascript in order to proceed!') ?></em></p></noscript>

	<div id="regenthumbs-bar" style="position:relative;height:25px;">
		<div id="regenthumbs-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="regenthumbs-stop" id="regenthumbs-stop" value="<?= ___( 'Abort Resizing Images') ?>" /></p>

	<h3 class="title"><?= ___( 'Debugging Information') ?></h3>

	<p>
		<?php printf( ___( 'Total Images: %s'), $count ); ?><br />
		<?php printf( ___( 'Images Resized: %s'), '<span id="regenthumbs-debug-successcount">0</span>' ); ?><br />
		<?php printf( ___( 'Resize Failures: %s'), '<span id="regenthumbs-debug-failurecount">0</span>' ); ?>
	</p>

	<ol id="regenthumbs-debuglist">
		<li style="display:none"></li>
	</ol>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?= $ids; ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$("#regenthumbs-bar").progressbar();
			$("#regenthumbs-bar-percent").html( "0%" );

			// Stop button
			$("#regenthumbs-stop").click(function() {
				rt_continue = false;
				$('#regenthumbs-stop').val("<?= $this->esc_quotes( ___( 'Stopping...') ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#regenthumbs-debuglist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function RegenThumbsUpdateStatus( id, success, response ) {
				$("#regenthumbs-bar").progressbar( "value", ( rt_count / rt_total ) * 100 );
				$("#regenthumbs-bar-percent").html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$("#regenthumbs-debug-successcount").html(rt_successes);
					$("#regenthumbs-debuglist").append("<li>" + response.success + "</li>");
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$("#regenthumbs-debug-failurecount").html(rt_errors);
					$("#regenthumbs-debuglist").append("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function RegenThumbsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$('#regenthumbs-stop').hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?= $text_failures; ?>';
				} else {
					rt_resulttext = '<?= $text_nofailures; ?>';
				}

				$("#message").html("<p><strong>" + rt_resulttext + "</strong></p>");
				$("#message").show();
			}

			// Regenerate a specified image via AJAX
			function RegenThumbs( id ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: "regeneratethumbnail", id: id },
					success: function( response ) {
						if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
							response = new Object;
							response.success = false;
							response.error = "<?php printf( esc_js( ___( 'The resize request was abnormally terminated (ID %s). This is likely due to the image exceeding available memory or some other type of fatal error.') ), '" + id + "' ); ?>";
						}

						if ( response.success ) {
							RegenThumbsUpdateStatus( id, true, response );
						}
						else {
							RegenThumbsUpdateStatus( id, false, response );
						}

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					},
					error: function( response ) {
						RegenThumbsUpdateStatus( id, false, response );

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					}
				});
			}

			RegenThumbs( rt_images.shift() );
		});
	// ]]>
	</script>
<?php
		}

		// No button click? Display the form.
		else {
?>
	<form method="post" action="">
<?php wp_nonce_field('regenerate-thumbnails') ?>

	<p><?php printf( ___( "Use this tool to regenerate thumbnails for all images that you have uploaded to your blog. This is useful if you've changed any of the thumbnail dimensions on the <a href='%s'>media settings page</a>. Old thumbnails will be kept to avoid any broken images due to hard-coded URLs."), admin_url( 'options-media.php' ) ); ?></p>

	<p><?php printf( ___( "You can regenerate specific images (rather than all images) from the <a href='%s'>Media</a> page. Hover over an image's row and click the link to resize just that one image or use the checkboxes and the &quot;Bulk Actions&quot; dropdown to resize multiple images (WordPress 3.1+ only)."), admin_url( 'upload.php' ) ); ?></p>

	<p><?= ___( "Thumbnail regeneration is not reversible, but you can just change your thumbnail dimensions back to the old values and click the button again if you don't like the results."); ?></p>

	<p><?= ___( 'To begin, just press the button below.'); ?></p>

	<p><input type="submit" class="button hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?= ___( 'Regenerate All Thumbnails') ?>" /></p>

	<noscript><p><em><?= ___( 'You must enable Javascript in order to proceed!') ?></em></p></noscript>

	</form>
<?php
		} // End if button
?>
</div>

<?php
	}


	// Process a single image ID (this is an AJAX handler)
	function ajax_process_image() {
		@error_reporting( 0 ); // Don't break the JSON result

		header( 'Content-type: application/json' );

		$id = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( ___( 'Failed resize: %s is an invalid image ID.'), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( ! theme_cache::current_user_can( $this->capability ) )
			$this->die_json_error_msg( $image->ID, ___( "Your user account doesn't have permission to resize images") );

		$fullsizepath = get_attached_file( $image->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			$this->die_json_error_msg( $image->ID, sprintf( ___( 'The originally uploaded image file cannot be found at %s'), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); // 5 minutes per image should be PLENTY

		$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

		if ( is_wp_error( $metadata ) )
			$this->die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			$this->die_json_error_msg( $image->ID, ___( 'Unknown failure reason.') );

		// If this fails, then it just means that nothing was changed (old value == new value)
		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( ___( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.'), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}


	// Helper to make a JSON error message
	function die_json_error_msg( $id, $message ) {
		die( json_encode( array( 'error' => sprintf( ___( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s'), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
	}


	// Helper function to escape quotes in strings for use in Javascript
	function esc_quotes( $string ) {
		return str_replace( '"', '\"', $string );
	}
}

// Start up this plugin
add_action( 'init', 'RegenerateThumbnails' );
function RegenerateThumbnails() {
	global $RegenerateThumbnails;
	$RegenerateThumbnails = new RegenerateThumbnails();
}

}/** end class_exists */
?>