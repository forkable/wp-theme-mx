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
	<?php echo theme_features::get_theme_css('modules/bootstrap','normal');?>
	<?php echo theme_features::get_theme_css('modules/bootstrap-theme','normal');?>
	<?php echo theme_features::get_theme_css('frontend/style','normal',true);?>
	<link rel="shortcut icon" href="<?php //echo theme_features::get_theme_images_url('frontend/favicon.ico',true);?>" type="image/x-icon" />
	<?php wp_head();?>
</head>
<body <?php body_class(); ?>>
<div class="top-bar-container nav-bar">
	<div class="top-bar container">
		<div class="top-bar-menu-container nav navbar-nav navbar-left">
			<?php
			/** 
			 * menu top-bar
			 */
            wp_nav_menu( array(
                'theme_location'    => 'menu-top-bar',
                'container'         => 'nav',
                'container_class'   => 'collapse navbar-collapse',
                'menu_class'        => 'nav navbar-nav',
                'menu_id' 			=> 'menu-top-bar',
                'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                'walker'            => new wp_bootstrap_navwalker())
            );
			?>
		</div>
		<div class="top-bar-tools navbar-right">
			<div class="btn-group btn-group-xs">
				<?php
				/**
				 * if user logged
				 */
				if(is_user_logged_in()){
					?>
					<!-- ctb -->
					<?php if(method_exists('theme_custom_ctb','get_url')){ ?>
						<a href="<?php echo esc_url(theme_custom_ctb::get_url());?>" class="btn btn-primary meta tool-contribution">
							<i class="fa fa-pencil-square-o"></i>
							<?php echo ___('Contribution');?>
						</a>
					<?php } ?>
					<!-- favor -->
					<?php if(method_exists('theme_custom_favor','get_url')){ ?>
						<a href="<?php echo esc_url(theme_custom_favor::get_url());?>" class="meta tool-favor btn btn-xs">
							<i class="fa fa-heart"></i>
							<?php echo ___('My favor');?>
						</a>
					<?php } ?>
					
					<!-- pm -->
					<?php if(class_exists('theme_pm')){ ?>
						<a href="<?php echo esc_url(theme_pm::get_url());?>" class="meta tool-favor btn btn-xs">
							<i class="fa fa-envelope"></i>
							<?php echo ___('My favor');?>
							<?php if(theme_pm::get_unread_count() != 0){ ?>
								<span class="badge"><?php echo theme_pm::get_unread_count();?></span>
							<?php } ?>
						</a>
					<?php } ?>
					
					<a href="###" class="btn btn-default meta user-avatar">
						<?php echo get_avatar(get_current_user_id());?>
						<span class="tx"><?php echo wp_get_current_user()->display_name;?></span>
					</a>
				</div>
			<?php
			/**
			 * is visitor
			 */
			}else{
				?>
				<div class="btn-group btn-group-sm">
					<a class="sign-in sign-in-meta btn btn-primary" href="<?php echo esc_url(wp_login_url(get_current_url()));?>">
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
							<a href="<?php echo esc_url(theme_open_sign::get_login_url('qq'));?>" class="open-sign sign-in-meta qq btn btn-primary" title="<?php echo ___('Login from QQ');?>">
								<i class="fa fa-qq"></i>
							</a>
						<?php
						}
						if(theme_open_sign::get_login_url('sina')){
							?>
							<a href="<?php echo esc_url(theme_open_sign::get_login_url('sina'));?>" class="open-sign sign-in-meta sina btn btn-primary" title="<?php echo ___('Login from Weibo');?>">
								<i class="fa fa-weibo"></i>
							</a>
						<?php 
						}
					}
				?>
				</div>
				<div class="sign-up-container meta btn-group">
					<a class="sign-up meta btn btn-success" href="<?php echo esc_url(wp_login_url(get_current_url()));?>">
						<i class="fa fa-user-plus"></i>
						<?php echo ___('Register');?>
					</a>
				</div>
			<?php
			}
			?>
		</div>
	</div><!-- /.top-bar -->
</div><!-- /.container -->

<?php
/** 
 * banner
 */
if(get_header_image()){ ?>
	<div class="banner">
		<?php if(display_header_text()){ ?>
		<div class="container">
			<h1 hidden><?php echo esc_html(get_bloginfo('name'));?></h1>
			<h2 hidden><?php echo esc_html(get_bloginfo('description'));?></h2>
		</div>
		<?php } ?>
	</div>
<?php } ?>


<div class="menu-header-container">
	<div class=" navbar navbar-default">
		<div class="container">
			<div class="navbar-header">
				<a href="javascript:void(0);" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse-menu">
					<i class="fa fa-bars"></i>
					<?php echo ___('Navigation menu');?>
				</a>
				
				
			</div>
			<div class="navbar-collapse navbar-left collapse navbar-collapse-menu">
			<?php
			/** 
			 * menu menu-header
			 */
			wp_nav_menu(array(
	            'theme_location'    => 'menu-header',
	            'container'         => 'nav',
	            'container_class'   => 'menu-header collapse navbar-collapse',
	            'menu_class'        => 'menu nav navbar-nav',
	            'menu_id' 			=> 'menu-header',
	            'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
	            'walker'            => new wp_bootstrap_navwalker
	       	));
			?>
			</div>

			
			<form class="navbar-form navbar-right" role="search" action="/search" method="get">
	            <div class="input-group">
	                <input name="s" class="form-control input-sm" placeholder="<?php echo ___('Keywords');?>" value="<?php echo esc_attr(get_search_query())?>" type="search">
	                <span class="input-group-btn">
	                    <button class="btn btn-default btn-sm" type="submit"><i class="fa fa-search"></i></button>
	                </span>
	            </div>
	        </form>		
		</div>
	</div>
</div>
