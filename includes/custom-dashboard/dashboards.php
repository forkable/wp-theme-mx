<?php
/**
 * @version 1.0.0
 */
theme_dashboards::init();
class theme_dashboards extends theme_custom_dashboard{

	public static function init(){
		
		
		add_action('account_dashboard_left',__CLASS__ . '::my_statistics');
		add_action('account_dashboard_left',__CLASS__ . '::my_point');
		
		add_action('account_dashboard_right',__CLASS__ . '::recent_comments_4_my_posts');
		add_action('account_dashboard_right',__CLASS__ . '::recent_posts');
		
		
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
			$histories = theme_custom_point::get_history_list(array(
				'posts_per_page' => 5,
			));
			if(!$histories){
				?>
				<div class="panel-body">
					<?php echo status_tip('info',___('No data yet.'));?>
				</div>
				<?php
			}else{
				echo $histories;
			}
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
			$current_user_id = get_current_user_id();
			$comments = get_comments([
				'post_author' => $current_user_id,
				'author__not_in' => [$current_user_id],
				'number' => 5,
				'status' => '1',
			]);
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
					href="<?php echo esc_url($comment_author_url);?>"
					<?php echo (int)$comment->user_id === 0 ? 'target="_blank"' : null;?>
				>
					<?php echo get_avatar($comment,50);?>
				</a>
				<?php 
			}else{
				echo get_avatar($comment,50);
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
		$current_user_id = get_current_user_id();
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-pie-chart"></i>
				<?php echo ___('My statistics');?>
			</div>
			<div class="panel-body">
				<a class="media" href="<?php echo theme_custom_user_settings::get_tabs('history')['url'];?>" title="<?php echo ___('Views my histories');?>">
					<div class="media-left">
						<img class="media-object" src="<?php echo theme_custom_point::get_point_img_url();?>" alt="">
					</div>
					<div class="media-body">
						<h4 class="media-heading"><strong class="total-point"><?php echo theme_custom_point::get_point();?> </strong></h4>
					</div>
				</a>
				<div class="row">
					<!-- posts count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(___('My posts: %s'),'<a href="' . theme_cache::get_author_posts_url($current_user_id) . '">' . theme_custom_author_profile::get_count('works',$current_user_id) . '</a>');
						?>
					</div>
					<!-- comments count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(
							___('My comments: %s'),
							'<a href="' . theme_custom_author_profile::get_tabs('comments',$current_user_id)['url'] . '">' . theme_custom_author_profile::get_count('comments',$current_user_id) . '</a>'
						);
						?>
					</div>
					<!-- followers count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(
							___('My followers: %s'),
							'<a href="' . theme_custom_author_profile::get_tabs('followers_count',$current_user_id)['url'] . '">' . theme_custom_author_profile::get_count('followers_count',$current_user_id) . '</a>'
						);
						?>
					</div>
					<!-- following count -->
					<div class="col-xs-6">
						<?php
						echo sprintf(
							___('My following: %s'),
							'<a href="' . theme_custom_author_profile::get_tabs('following_count',$current_user_id)['url'] . '">' .
							theme_custom_author_profile::get_count('following_count',$current_user_id) . '</a>'
						);
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
			global $post;
			$query = new WP_Query(array(
				'posts_per_page' => $posts_per_page,
				'author' => get_current_user_id(),
			));
			if($query->have_posts()){
				?>
				<ul class="list-group">
				<?php
				foreach($query->posts as $post){
					?>
					<li class="list-group-item">
						<a href="<?php the_permalink();?>"><?php the_title();?></a>
						<small><?php echo esc_html(friendly_date((get_the_time('U'))));?></small>
					</li>
					<?php
				}
				wp_reset_postdata();
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