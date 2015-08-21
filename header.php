<!DOCTYPE html><html <?php language_attributes(); ?>><head>
<meta charset="<?= theme_cache::get_bloginfo( 'charset' ); ?>">
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><meta http-equiv="Cache-Control" content="no-transform"><![endif]-->
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="author" content="INN STUDIO">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?= theme_cache::get_bloginfo('pingback_url'); ?>">
<?php wp_head();?></head>
<body <?php body_class(); ?>>

<?php if(!wp_is_mobile()){ ?>
	<div class="top-bar navbar navbar-inverse hidden-xs">	
		<div class="container">
			<?php
			/** 
			 * menu top-bar
			 */
           theme_cache::wp_nav_menu([
                'theme_location'    => 'menu-top-bar',
                'container'         => 'nav',
                'container_class'   => 'top-bar-nav',
                'menu_class'        => 'menu',
                'menu_id' 			=> 'menu-top-bar',
                'fallback_cb'       => 'custom_navwalker::fallback',
                'walker'            => new custom_navwalker()
            ]);
			?>
			<div class="top-bar-tools">
				<?php include __DIR__ . '/tpl/header-topbar-tools.php';?>
			</div>
		</div><!-- /.container -->
	</div><!-- /.top-bar -->	
<?php } ?>





<div class="main-nav">
	<div class="container">
		<a href="javascript:;" class="navicon toggle visible-xs-block fa fa-navicon fa-2x fa-fw" data-target=".menu-mobile" data-icon-active="fa-times" data-icon-original="fa-navicon"></a>

		<?php
		/** 
		 * banner
		 */
		$header_img = get_header_image();
		if((bool)$header_img){ ?>
		<a class="logo" href="<?= theme_cache::home_url();?>" title="<?= theme_cache::get_bloginfo('name');?> - <?= theme_cache::get_bloginfo('description');?>">
			<img src="<?= $header_img; ?>" alt="<?= theme_cache::get_bloginfo('name');?>" width="100" height="40">
			<?php if(display_header_text()){ ?>
				<h1 hidden><?= theme_cache::get_bloginfo('name');?></h1>
				<span hidden><?= theme_cache::get_bloginfo('description');?></span>
			<?php } ?>
		</a>
		<?php } ?>

		
		<?php
		/** 
		 * menu-header
		 */
		if(!wp_is_mobile()){
			theme_cache::wp_nav_menu([
				'theme_location'    => 'menu-header',
				'container'         => 'nav',
				'container_class'   => 'menu-header',
				'menu_class'        => 'menu',
				'menu_id' 			=> 'menu-header',
				'fallback_cb'       => 'custom_navwalker::fallback',
				'walker'            => new custom_navwalker
			]);
		}
		?>
		
		<div class="tools">
			<?php if(wp_is_mobile()){ ?>
				<!-- account btn -->
				<?php if(theme_cache::is_user_logged_in()){ ?>
					<a class="tool mx-account-btn fa fa-user fa-fw fa-2x" href="javascript:;" data-target=".header-nav-account-menu" data-icon-active="fa-times" data-icon-original="fa-user"></a>
				<?php }else{ ?>
					<a class="tool mx-account-btn toggle" href="<?= esc_url(wp_login_url(get_current_url()));?>">
						<?= ___('Login');?>
					</a>
				<?php } ?>
			<?php } ?>
			<!-- search btn -->
			<a class="tool search fa fa-search fa-fw fa-2x" href="javascript:;" data-target="#fm-search" data-focus-target="#fm-search-s" data-icon-active="fa-times" data-icon-original="fa-search"></a>

		</div><!-- /.tools -->
	</div><!-- /.container -->
	<div class="container">
		<?php
		
	   	if(wp_is_mobile()){
			/** 
			 * menu menu-mobile
			 */
			theme_cache::wp_nav_menu([
				'theme_location'    => 'menu-mobile',
				'container'         => 'nav',
				'container_class'   => 'menu-mobile',
				'menu_class'        => 'menu',
				'menu_id' 			=> 'menu-mobile',
				'fallback_cb'       => 'custom_navwalker::fallback',
				'walker'            => new custom_navwalker
			]);
	   	}
		?>

		
		<?php
		/**
		 * account menu
		 */
		if(theme_cache::is_user_logged_in() && wp_is_mobile()){
			$active_tab = get_query_var('tab');
			if(!$active_tab)
				$active_tab = 'dashboard';
			?>
			<ul class="header-nav-account-menu">
				<?php
				$account_navs = apply_filters('account_navs',[]);
				if(!empty($account_navs)){
					foreach($account_navs as $k => $v){
						$active_class = $k === $active_tab ? ' active ' : null;
						?>
						<li class="<?= theme_custom_account::is_page() ? $active_class : null;?>"><?= $v;?></li>
						<?php
					}
				}
				?>
			</ul>
		<?php } ?>
	 </div><!-- /.container -->
	 
	<!-- search form -->
	<div class="container">
		<form id="fm-search" action="<?= theme_cache::home_url('/'); ?>" data-focus-target="#fm-search-s">
			<input id="fm-search-s" name="s" class="form-control" placeholder="<?= ___('Please input search keyword');?>" value="<?= esc_attr(get_search_query())?>" type="search" required>
        </form>		
	</div>
</div><!-- /.main-nav -->