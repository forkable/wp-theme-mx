<!DOCTYPE html><html <?php language_attributes(); ?>><head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<title><?php echo class_exists('theme_seo_plus') ? theme_seo_plus::wp_title( '-', false, 'right' ) : wp_title( '-', false, 'right' );?></title>
	<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /><![endif]-->
	<meta name="renderer" content="webkit" />
	<meta name="viewport" content="width=device-width" />
	<meta name="author" content="INN STUDIO" />
	<meta http-equiv="Cache-Control" content="no-transform" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php echo theme_features::get_theme_css('frontend/fonts','normal');?>
	<?php echo theme_features::get_theme_css('modules/pure','normal',true);?>
	<?php echo theme_features::get_theme_css('frontend/style','normal',true);?>
	<link rel="shortcut icon" href="<?php //echo theme_features::get_theme_images_url('frontend/favicon.ico',true);?>" type="image/x-icon" />
	<?php wp_head();?>
</head>
<body <?php body_class(); ?>>
<div class="top-bar-container">
	<div class="top-bar pure-g">
		<div class="top-bar-menu-container pure-u-2-3">
			<?php
			/** 
			 * menu top-bar
			 */
			wp_nav_menu(array(
				'theme_location' => 'menu-top-bar',
				'menu_class' => 'menu',
				'container' => 'nav',
				'container_class' => 'pure-menu pure-menu-open pure-menu-horizontal',
				'menu_id' => 'menu-top-bar',
				'walker' => new pure_nav_menu_walker,
				'fallback_cb' => false,
			));
			?>
		</div>
		<div class="top-bar-tools pure-u-1-3">
			<?php
			/**
			 * if user logged
			 */
			if(is_user_logged_in()){
				?>
				<a href="javascript(0);" class="user-avatar">
					<?php echo get_avatar(get_current_user_id());?>
					<span class="tx"><?php echo wp_get_current_user()->display_name;?></span>
				</a>
			<?php
			/**
			 * is visitor
			 */
			}else{
				?>
				<a class="sign-in" href="<?php echo esc_url(wp_login_url(get_current_url()));?>">
					<?php echo ___('Login');?>
				</a>
				<a class="sign-up" href="<?php echo esc_url(wp_login_url(get_current_url()));?>"><?php echo ___('Register');?></a>
			<?php
			}
			?>
		</div>
	</div><!-- /.top-bar -->
</div><!-- /.top-bar-container -->