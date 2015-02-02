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
	<?php echo theme_features::get_theme_css('modules/fa-fonts','normal');?>
	<?php echo theme_features::get_theme_css('modules/pure','normal',true);?>
	<?php echo theme_features::get_theme_css('frontend/style','normal',true);?>
	<link rel="shortcut icon" href="<?php //echo theme_features::get_theme_images_url('frontend/favicon.ico',true);?>" type="image/x-icon" />
	<?php wp_head();?>
</head>
<body <?php body_class(); ?>>
<div class="top-bar-container">
	<div class="top-bar pure-g">
		<div class="pure-u-1-1">
		<div class="top-bar-menu-container">
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
		<div class="top-bar-tools">
			<?php
			/**
			 * if user logged
			 */
			if(is_user_logged_in()){
				?>
				<a href="javascript:void(0);" class="meta user-avatar">
					<?php echo get_avatar(get_current_user_id());?>
					<span class="tx"><?php echo wp_get_current_user()->display_name;?></span>
				</a>
				<!-- ctb -->
				<?php if(class_exists('theme_custom_ctb')){ ?>
					<a href="<?php echo esc_url(theme_custom_ctb::get_url());?>" class="meta tool-contribution">
						<i class="fa fa-pencil-square-o"></i>
						<?php echo ___('Contribution');?>
					</a>
				<?php } ?>
				<!-- favor -->
				<?php if(class_exists('theme_custom_favor')){ ?>
					<a href="<?php echo esc_url(theme_custom_favor::get_url());?>" class="meta tool-favor">
						<i class="fa fa-heart"></i>
						<?php echo ___('My favor');?>
					</a>
				<?php } ?>
			<?php
			/**
			 * is visitor
			 */
			}else{
				?>
				<div class="sign-in-container meta">
					<a class="sign-in sign-in-meta" href="<?php echo esc_url(wp_login_url(get_current_url()));?>">
						<i class="fa fa-user"></i>
						<?php echo ___('Login');?>
					</a>
					<?php
					/**
					 * open sign
					 */
					if(method_exists('theme_open_sign','get_login_url')){
						if(theme_open_sign::get_login_url('qq')){
							?>
							<a href="<?php echo esc_url(theme_open_sign::get_login_url('qq'));?>" class="open-sign sign-in-meta qq" title="<?php echo ___('Login from QQ');?>">
								<i class="fa fa-qq"></i>
							</a>
						<?php
						}
						if(theme_open_sign::get_login_url('sina')){
							?>
							<a href="<?php echo esc_url(theme_open_sign::get_login_url('sina'));?>" class="open-sign sign-in-meta sina" title="<?php echo ___('Login from Weibo');?>">
								<i class="fa fa-weibo"></i>
							</a>
						<?php 
						}
					}
				?>
				</div>
				
				<a class="sign-up meta button-small button-success pure-button" href="<?php echo esc_url(wp_login_url(get_current_url()));?>">
					<i class="fa fa-user-plus"></i>
					<?php echo ___('Register');?>
				</a>
			<?php
			}
			?>
		</div>
	</div><!-- /.top-bar -->
	</div>
</div><!-- /.top-bar-container -->

<?php
/** 
 * banner
 */
if(get_header_image()){ ?>
	<div class="banner pure-g">
		<?php if(display_header_text()){ ?>
			<h1 hidden><?php echo esc_html(get_bloginfo('name'));?></h1>
			<h2 hidden><?php echo esc_html(get_bloginfo('description'));?></h2>
		<?php } ?>
	</div>
<?php } ?>


<div class="menu-header-container pure-g">
	<div class="pure-u-1-1">
		<?php
		/** 
		 * menu menu-header
		 */
		wp_nav_menu(array(
			'theme_location' => 'menu-header',
			'menu_class' => 'menu',
			'container' => 'nav',
			'container_class' => 'menu-header pure-hidden-xs pure-hidden-sm pure-menu pure-menu-open pure-menu-horizontal',
			'menu_id' => 'menu-header',
			'walker' => new pure_nav_menu_walker,
			'fallback_cb' => false,
		));
		?>
		<div class="menu-mobile-toggle pure-hidden-md pure-hidden-lg pure-hidden-xl">
			<a href="javascript:void(0);" class="toggle pure-button" data-target="#menu-header-mobile">
				<i class="fa fa-bars"></i>
				<?php echo ___('Navigation menu');?>
			</a>
		</div>

		<!-- search -->
		<form action="" id="header-search" class="pure-form">
			<input type="search" name="s" id="header-search-s" placeholder="<?php echo ___('Keywords');?>" value="<?php echo esc_attr(get_search_query())?>" required />
			<button type="submit" class="pure-button pure-button-primary"><?php echo ___('Search');?></button>
		</form>
		<?php
		/** 
		 * menu menu-header
		 */
		wp_nav_menu(array(
			'theme_location' => 'menu-header-mobile',
			'menu_class' => 'menu',
			'container' => 'nav',
			'container_class' => 'menu-header-mobile hide pure-menu pure-menu-open',
			'container_id' => 'menu-header-mobile',
			'menu_id' => 'menu-header-mobile',
			'walker' => new pure_nav_menu_walker,
			'fallback_cb' => false,
		));
		?>
	</div>
</div>
