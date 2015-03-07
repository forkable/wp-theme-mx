<?php
/**
 * @version 1.0.0
 */

class theme_dashboards extends theme_custom_dashboard{

	public static function init(){
		
		
		add_action('account_dashboard_left',get_class() . '::my_statistics');
		add_action('account_dashboard_left',get_class() . '::my_point');
		
		add_action('account_dashboard_right',get_class() . '::recent_comments_4_my_posts');
		add_action('account_dashboard_right',get_class() . '::recent_posts');
		
		
	}
	public static function my_point(){
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-line-chart"></i> 
				<?php echo ___('My recent reward point actives');?>
			</div>
			<?php 
			/**
			 * show lastest histories
			 */
			echo theme_custom_point::get_history_list(array(
				'posts_per_page' => 5,
			));
			?>
		</div>
		<?php
	}
	/**
	 * recent_comments_4_my_posts
	 */
	public static function recent_comments_4_my_posts(){
		?>
		<!-- Recent comments for my posts -->
		<div class="dashboard-recent-comments-4-my-posts panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-comments"></i>
				<?php echo ___('Recent comments for my posts');?>
			</div>
			<?php
			/**
			 * get comments
			 */
			$comments = get_comments(array(
				'post_author' => get_current_user_id(),
				'author__not_in' => array(get_current_user_id()),
				'number' => 5,
				'status' => '1',
			));
			if(empty($comments)){
				?>
				<div class="panel-body">
					<?php echo status_tip('info',___('No comment for your post yet'));?>
				</div>
				<?php
			}else{
				?>
				<ul class="list-group">
					<?php
					global $comment;
					foreach($comments as $comment){
						?>
<li class="list-group-item">
	<div class="media">
		<div class="media-left media-top">
			<?php
			$comment_author_url = get_comment_author_url();
			if($comment_author_url){
				?>
				<a 
					href="<?php esc_url($comment_author_url);?>"
					target="<?php (int)$comment->user_id === 0 ? 'target' : null;?>"
				>
					<?php echo get_avatar(get_comment_author_email(),50);?>
				</a>
				<?php 
			}else{
				echo get_avatar(get_comment_author_email(),50);
			} 
			?>
		</div>
		<div class="media-body">
			<h4 class="media-heading">
				<?php 
				echo sprintf(
					___('%s commented your post "%s".'),
					'<span class="author">' . get_comment_author_link() . '</span>',
					'<a href="' . get_permalink($comment->comment_post_ID) . '">' . esc_html(get_the_title($comment->comment_post_ID)) . '</a>'
				);?>
			</h4>
			<p class="excerpt-tx">
				<?php comment_excerpt();?>
			</p>
		</div><!-- /.media-body -->
	</div><!-- /.media -->
</li>
						<?php
					}
				?>
				</ul>
				<?php
			}/** end have comment */
		?>
		</div>
		<?php
	}

	/**
	 * My statistics
	 */
	public static function my_statistics(){
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-pie-chart"></i>
				<?php echo ___('My statistics');?>
			</div>
			<div class="panel-body">
				<a class="media" href="<?php echo theme_custom_user_settings::get_tabs('history')['url'];?>" title="<?php echo ___('Views my histories');?>">
					<div class="media-left">
						<img class="media-object" src="<?php echo esc_url(theme_options::get_options(theme_custom_point::$iden)['point-img-url']);?>" alt="">
					</div>
					<div class="media-body">
						<h4 class="media-heading"><strong class="total-point"><?php echo theme_custom_point::get_point();?> </strong></h4>
					</div>
				</a>
				<div class="row">
					<!-- posts count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(___('My posts: %d'),theme_custom_author_profile::get_count('works',get_current_user_id()));
						?>
					</div>
					<!-- comments count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(___('My comments: %d'),theme_custom_author_profile::get_count('comments',get_current_user_id()));
						?>
					</div>
					<!-- followers count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(___('My followers: %d'),theme_custom_author_profile::get_count('followers_count',get_current_user_id()));
						?>
					</div>
					<!-- following count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(___('My following: %d'),theme_custom_author_profile::get_count('following_count',get_current_user_id()));
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public static function recent_posts(){
		$posts_per_page = 5;
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-clock-o"></i>
				<?php echo ___('My recent posts');?>
			</div>
			<?php
			global $post,$wp_query;
			$wp_query = new WP_Query(array(
				'posts_per_page' => $posts_per_page,
				'author' => get_current_user_id(),
			));
			if(have_posts()){
				?>
				<ul class="list-group">
				<?php
				while(have_posts()){
					the_post();
					?>
					<li class="list-group-item">
						<a href="<?php the_permalink();?>"><?php the_title();?></a>
						<small><?php echo esc_html(friendly_date((get_the_time('U'))));?></small>
					</li>
					<?php
				}
				?>
				</ul>
				<?php
			}else{
				?>
				<div class="panel-body"><?php echo status_tip('info',___('No posts yet'));?></div>
				<?php
			}
			?>
		</div>
		<?php
	}
}