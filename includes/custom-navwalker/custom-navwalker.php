<?php

/**
 * Class Name: custom_navwalker
 * GitHub URI: https://github.com/twittem/wp-bootstrap-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 3 navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 2.0.4
 * Author: Edward McIntyre -
 * 
 * @twittem License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
custom_navwalker::custom_nav_menu_hook();
class custom_navwalker extends Walker_Nav_Menu{
	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat("\t", $depth);
		$output .= "\n$indent<ul class=\"sub-menu\">\n";
		//$output .= '<ul class="sub-menu">';
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>\n";
		//$output .= '</ul>';
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 * @param int    $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? [] : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = [];
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;
		/**
		 * icon
		 */
		if ( !empty($item->awesome) )
			$item_output .= '<a' . $attributes . '><i class="fa fa-fw fa-' . $item->awesome . '"></i>';
		else
			$item_output .= '<a' . $attributes . '>';
			
		/**
		 * hide title option
		 */
		$link_html = $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
		
		if(isset($item->hide_title) && $item->hide_title == 1){
			$hide_title = true;
			$link_html = '<i class="hide">' . $link_html . '</i>';
		}else{
			$hide_title = false;
		}
		
		/**
		 * add splace if has icon
		 */
		if( !$hide_title )
			$item_output .= '&nbsp;';

		$item_output .= $link_html . '</a>';

		$item_output .= $args->after;

		
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Page data object. Not used.
	 * @param int    $depth  Depth of page. Not Used.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 */
	public function end_el( &$output, $item, $depth = 0, $args = [] ) {
		$output .= "</li>\n";
	}
	/**
	 * Menu Fallback
	 * =============
	 * If this function is assigned to the wp_nav_menu's fallback_cb variable
	 * and a manu has not been assigned to the theme location in the WordPress
	 * menu manager the function with display nothing to a non-logged in user,
	 * and will add a link to the WordPress menu manager if logged in as an admin.
	 * 
	 * @param array $args passed from the wp_nav_menu function.
	 */
	public static function fallback($args){
		if (!theme_cache::current_user_can('manage_options'))
			return '';
			
		extract($args);
		$fb_output = null;
		if ($container){
			$fb_output = '<' . $container;
			if ($container_id)
				$fb_output .= ' id="' . $container_id . '"';
			if ($container_class)
				$fb_output .= ' class="' . $container_class . '"';
			$fb_output .= '>';
			}
		$fb_output .= '<ul';
		if ($menu_id)
			$fb_output .= ' id="' . $menu_id . '"';
		if ($menu_class)
			$fb_output .= ' class="' . $menu_class . '"';
		$fb_output .= '>';
		$fb_output .= '<li><a href="' . admin_url('nav-menus.php') . '">Add a menu</a></li>';
		$fb_output .= '</ul>';
		if ($container)
			$fb_output .= '</' . $container . '>';
			
		echo $fb_output;
	}
	public static function setup_nav_menu_item($menu_item){
		$menu_item->awesome = get_post_meta($menu_item->ID,'_menu_item_awesome',true);
		$menu_item->hide_title = get_post_meta($menu_item->ID,'_menu_item_hide_title',true);
		return $menu_item;
	}
	public static function icon_update_nav_menu_item($menu_id, $menu_item_db_id){
		/**
		 * icon
		 */
		if(!isset($_REQUEST['menu-item-awesome'][$menu_item_db_id]) || !is_string($_REQUEST['menu-item-awesome'][$menu_item_db_id]))
			return false;
			
		update_post_meta($menu_item_db_id,'_menu_item_awesome',$_REQUEST['menu-item-awesome'][$menu_item_db_id]);
	}
	public static function hide_title_update_nav_menu_item($menu_id, $menu_item_db_id){
		/**
		 * icon
		 */
		if( isset($_REQUEST['menu-item-hide-title'][$menu_item_db_id]) ){
			update_post_meta($menu_item_db_id, '_menu_item_hide_title', 1);
		}else{
			delete_post_meta($menu_item_db_id, '_menu_item_hide_title');
		}
	}
	public static function edit_nav_menu_walker(){
		return 'Walker_Nav_Menu_Edit_Custom';
	}
	public static function custom_nav_menu_hook(){
		add_filter('wp_edit_nav_menu_walker',__CLASS__ . '::edit_nav_menu_walker');
		add_filter('wp_setup_nav_menu_item' ,__CLASS__ . '::setup_nav_menu_item');
		add_action('wp_update_nav_menu_item',__CLASS__ . '::icon_update_nav_menu_item',10,2);
		add_action('wp_update_nav_menu_item',__CLASS__ . '::hide_title_update_nav_menu_item',10,2);
	}
}

/**
 * Create HTML list of nav menu input items.
 *
 * @package WordPress
 * @since 3.0.0
 * @uses Walker_Nav_Menu
 */
class Walker_Nav_Menu_Edit_Custom extends Walker_Nav_Menu {
	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker_Nav_Menu::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker_Nav_Menu::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth;
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) )
				$original_title = false;
		} elseif ( 'post_type' == $item->type ) {
			$original_object = theme_cache::get_post( $item->object_id );
			$original_title = theme_cache::get_the_title( $original_object->ID );
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( __( '%s (Invalid)' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __('%s (Pending)'), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

		$submenu_text = '';
		if ( 0 == $depth )
			$submenu_text = 'style="display: none;"';

		?>
		<li id="menu-item-<?= $item_id; ?>" class="<?= implode(' ', $classes ); ?>">
			<dl class="menu-item-bar">
				<dt class="menu-item-handle">
					<span class="item-title"><span class="menu-item-title"><?= esc_html( $title ); ?></span> <span class="is-submenu" <?= $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
					<span class="item-controls">
						<span class="item-type"><?= esc_html( $item->type_label ); ?></span>
						<span class="item-order hide-if-js">
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
							|
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
						</span>
						<a class="item-edit" id="edit-<?= $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
							echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>"><?php _e( 'Edit Menu Item' ); ?></a>
					</span>
				</dt>
			</dl>

			<div class="menu-item-settings" id="menu-item-settings-<?= $item_id; ?>">
				<?php if( 'custom' == $item->type ) : ?>
					<p class="field-url description description-wide">
						<label for="edit-menu-item-url-<?= $item_id; ?>">
							<?php _e( 'URL' ); ?><br />
							<input type="text" id="edit-menu-item-url-<?= $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?= $item_id; ?>]" value="<?= esc_attr( $item->url ); ?>" />
						</label>
					</p>
				<?php endif; ?>
				<p class="description description-thin">
					<label for="edit-menu-item-title-<?= $item_id; ?>">
						<?php _e( 'Navigation Label' ); ?><br />
						<input type="text" id="edit-menu-item-title-<?= $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?= $item_id; ?>]" value="<?= esc_attr( $item->title ); ?>" />
					</label>
				</p>
				<p class="description description-thin">
					<label for="edit-menu-item-attr-title-<?= $item_id; ?>">
						<?php _e( 'Title Attribute' ); ?><br />
						<input type="text" id="edit-menu-item-attr-title-<?= $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?= $item_id; ?>]" value="<?= esc_attr( $item->post_excerpt ); ?>" />
					</label>
				</p>
				<p class="field-link-target description">
					<label for="edit-menu-item-target-<?= $item_id; ?>">
						<input type="checkbox" id="edit-menu-item-target-<?= $item_id; ?>" value="_blank" name="menu-item-target[<?= $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
						<?php _e( 'Open link in a new window/tab' ); ?>
					</label>
				</p>
				<!-- awesome icon -->
				<p class="description description-thin">
					<label for="edit-menu-item-awesome-<?= $item_id; ?>">
						<?php __e( 'Awesome icon' ); ?><br />
						<input type="text" id="edit-menu-item-awesome-<?= $item_id; ?>" class="widefat edit-menu-item-awesome" name="menu-item-awesome[<?= $item_id; ?>]" value="<?= esc_attr( $item->awesome ); ?>" />
					</label>
				</p><!-- /awesome icon -->
				
				<p class="field-css-classes description description-thin">
					<label for="edit-menu-item-classes-<?= $item_id; ?>">
						<?php _e( 'CSS Classes (optional)' ); ?><br />
						<input type="text" id="edit-menu-item-classes-<?= $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?= $item_id; ?>]" value="<?= esc_attr( implode(' ', $item->classes ) ); ?>" />
					</label>
				</p>
				<!-- only show icon -->
				<p class="description description-thin field-hide-title">
					<label for="edit-menu-item-hide-title-<?= $item_id; ?>">
						<input type="checkbox" id="edit-menu-item-hide-title-<?= $item_id; ?>" class="widefat edit-menu-item-hide-title" name="menu-item-hide-title[<?= $item_id; ?>]" value="1" <?= isset( $item->hide_title ) && $item->hide_title == 1 ? 'checked' : null; ?> /> <?php __e( 'Hide navigation label' ); ?>
					</label>
				</p><!-- /only show icon -->
				
				<p class="field-xfn description description-thin">
					<label for="edit-menu-item-xfn-<?= $item_id; ?>">
						<?php _e( 'Link Relationship (XFN)' ); ?><br />
						<input type="text" id="edit-menu-item-xfn-<?= $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?= $item_id; ?>]" value="<?= esc_attr( $item->xfn ); ?>" />
					</label>
				</p>
				<p class="field-description description description-wide">
					<label for="edit-menu-item-description-<?= $item_id; ?>">
						<?php _e( 'Description' ); ?><br />
						<textarea id="edit-menu-item-description-<?= $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?= $item_id; ?>]"><?= esc_html( $item->description ); // textarea_escaped ?></textarea>
						<span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
					</label>
				</p>

				<p class="field-move hide-if-no-js description description-wide">
					<label>
						<span><?php _e( 'Move' ); ?></span>
						<a href="#" class="menus-move menus-move-up" data-dir="up"><?php _e( 'Up one' ); ?></a>
						<a href="#" class="menus-move menus-move-down" data-dir="down"><?php _e( 'Down one' ); ?></a>
						<a href="#" class="menus-move menus-move-left" data-dir="left"></a>
						<a href="#" class="menus-move menus-move-right" data-dir="right"></a>
						<a href="#" class="menus-move menus-move-top" data-dir="top"><?php _e( 'To the top' ); ?></a>
					</label>
				</p>

				<div class="menu-item-actions description-wide submitbox">
					<?php if( 'custom' != $item->type && $original_title !== false ) : ?>
						<p class="link-to-original">
							<?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?= $item_id; ?>" href="<?php
					echo wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'delete-menu-item',
								'menu-item' => $item_id,
							),
							admin_url( 'nav-menus.php' )
						),
						'delete-menu_item_' . $item_id
					); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?= $item_id; ?>" href="<?= esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
						?>#menu-item-settings-<?= $item_id; ?>"><?php _e('Cancel'); ?></a>
				</div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?= $item_id; ?>]" value="<?= $item_id; ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?= $item_id; ?>]" value="<?= esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?= $item_id; ?>]" value="<?= esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?= $item_id; ?>]" value="<?= esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?= $item_id; ?>]" value="<?= esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?= $item_id; ?>]" value="<?= esc_attr( $item->type ); ?>" />
			</div><!-- .menu-item-settings-->
			<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}

} // Walker_Nav_Menu_Edit