<?php
/**
 * theme custom header
 *
 * @version 1.0.1
 * @author KM@INN STUDIO
 */
theme_custom_header::init();
class theme_custom_header{
	public static function init(){
		add_action( 'after_setup_theme', get_class() . '::custom_header_setup', 11 );

	}
	public static function custom_header_setup() {
		$default_headers = array();
		$img_names = array('bridge.jpg','classical.jpg','polygon.jpg','natural.jpg','dusk.jpg','mountain.jpg');
		$args = array(
			'default-text-color'     => 'fff',
			'default-image'          => theme_features::get_theme_includes_image(__FILE__) . $img_names[0],

			'height'                 => 150,
			'width'                  => 1600,

			'wp-head-callback'       => get_class() . '::header_style',
			'admin-head-callback'    => get_class() . '::admin_header_style',
			'admin-preview-callback' => get_class() . '::admin_header_image',
			'random-default'         => true,
		);

		foreach($img_names as $img_name){
			$basename = explode('.',$img_name);
			$default_headers[$img_name] = array(
				'url' => theme_features::get_theme_includes_image(__FILE__) . $img_name,
				'thumbnail_url' => theme_features::get_theme_includes_image(__FILE__) . $basename[0] . '-thumbnail.' . $basename[1],
			);
		}
		add_theme_support( 'custom-header', $args );
		register_default_headers($default_headers);
	}
	public static function header_style() {
		$header_image = get_header_image();
		$text_color   = get_header_textcolor();

		if ( empty( $header_image ) && $text_color == get_theme_support( 'custom-header', 'default-text-color' ) )
			return;
		?>
		<style id="header-css">
		<?php if(!empty($header_image)){ ?>
		.banner{
			background-image: url(<?php header_image(); ?>);
		}
		<?php } ?>
		</style>
		<?php
	}
	public static function admin_header_style() {
		$header_image = get_header_image();
	?>
		<style id="admin-header-css">
		#banner{
			position:relative;
			height:100px;
		}
			#banner:hover .tx{
				text-shadow:0 1px 3px black;
			}
			#banner h2{
				color:white;
				padding:0;
				margin:0;
			}

		</style>
	<?php
	}
	public static function admin_header_image() {
		?>
		<div id="banner" style="background-image:url(<?php echo get_header_image();?>)">
			<?php if(display_header_text()){ ?>
				<h2><?php echo esc_html(get_bloginfo('description'));?></h2>
				</div>
			<?php } ?>
		</div>

	<?php }
}
