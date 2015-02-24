<?php get_header();?>
<?php
global $author;
$author_id = $author;
$tab_active = get_query_var('tab');
if(empty($tab_active)) $tab_active = 'profile';
$tabs = theme_custom_author_profile::get_tabs();
?>
<div class="container">
	<h3 class="crumb-title">
		<?php echo esc_html(get_the_author_meta('display_name',$author_id));?> - <?php echo $tabs[$tab_active]['text'];?>
	</h3>
	<div class="row">
		<div id="main" class="col-md-9 col-sm-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="btn-group btn-group-justified" role="group">
						<?php 
						foreach($tabs as $k => $v){
							$class_active = $tab_active === $k ? ' btn-primary ' : null;
							?>
							<a href="<?php echo esc_url($v['url']);?>" class="btn btn-default <?php echo $class_active;?>" role="button">
								<i class="fa fa-<?php echo esc_attr($v['icon']);?>"></i> 
								<span class="tx <?php echo $class_active ? null : 'hidden-xs';?>">
									<?php echo esc_html($v['text']);?>
								</span>
							</a>
						<?php } ?>					
					</div>
				</div>
				<?php
				switch($tab_active){
					/**
					 * works
					 */
					case 'works':
						?>
						<div class="panel-body row mx-card-body post-mixed-list-container">
							<?php
							if(have_posts()){
								while(have_posts()){
									the_post();
									theme_functions::archive_content(array(
										'classes' => array('col-lg-6 col-md-6 col-sm-12 col-xs-12')
									));
								}
								?>
								<div class="area-pagination">
									<?php echo theme_functions::get_post_pagination('posts-pagination posts-pagination-bottom');?>
								</div>
							<?php }else{ ?>

							<?php } ?>
						</div>
						<?php
						break;
					case 'comments':
						$comments = get_comments(array(
							'user_id' => $author_id,
							'number' => 50
						));
					?>
						<div class="panel-body">
							<?php if(empty($comments)){
								echo status_tip('info',___('No comment yet.'));
							}else{
								?>
								<ul class="list-group">
									<?php foreach($comments as $comment){ ?>
										<li class="list-group-item">
											<h4><a href="<?php echo get_permalink($comment->comment_post_ID);?>"><?php echo esc_html(get_the_title($comment->comment_post_ID));?></a></h4>
											<div class="comment-tx"><?php comment_text($comment->comment_ID);?></div>
										</li>
									<?php } ?>
								</ul>
							<?php } ?>
						</div>
						<?php
						break;
					case 'followers':

						break;
					case 'following':
						
						break;
					case 'profile':
					default:
						?>
						<div class="panel-body">
							<fieldset class="author-profile">
								<legend>
									<i class="fa fa-newspaper-o"></i> 
									<?php echo ___('Basic profile');?></span>
								</legend>
								<table class="table">
									<tbody>
										<tr>
											<th><abbr title="<?php echo ___('Unique identifier');?>"><?php echo ___('UID');?></abbr></th>
											<td>
												<a href="<?php echo esc_url(get_author_posts_url($author_id));?>" title="<?php echo esc_url(get_author_posts_url($author_id));?>">
													<?php echo $author_id;?>
												</a>
											</td>
										</tr>
										<tr>
											<th><?php echo ___('Nickname');?></th>
											<td><strong><?php echo esc_html(get_the_author_meta('display_name',$author_id));?></strong></td>
										</tr>
										<tr>
											<th><?php echo ___('Registered');?></th>
											<td><?php echo esc_html(get_the_author_meta('user_registered',$author_id));?></td>
										</tr>
										<tr>
											<th><?php echo ___('Website / Blog');?></th>
											<td>
											<?php 
											$website_url = get_the_author_meta('user_url',$author_id);
											if($website_url){
												?>
												<a href="<?php echo esc_url($website_url);?>" target="_blank" rel="nofollow"><?php echo esc_html($website_url);?></a>
											<?php }else{ ?>
												-
											<?php } ?>
											</td>
										</tr>
										<tr>
											<th><?php echo ___('Description');?></th>
											<td><?php echo esc_html(get_the_author_meta('description',$author_id) ? get_the_author_meta('description',$author_id) : '-');?></td>
										</tr>
									</tbody>
								</table>
							</fieldset>
							<!-- Statistics -->
							<fieldset class="author-profile">
								<legend>
									<i class="fa fa-pie-chart"></i> 
									<?php echo ___('Statistics');?></legend>
								<table class="table">
									<tbody>
										<tr>
											<th><?php echo ___('Works');?></th>
											<td><a href="<?php echo esc_url(theme_custom_author_profile::get_tabs('works',$author_id)['url']);?>">
												<?php
												$author_posts_count = theme_custom_author_profile::get_count('works',$author_id);
												echo (int)$author_posts_count !== 0 ? $author_posts_count : '-';
												?>
											</a></td>
										</tr>
										<tr>
											<th><?php echo esc_html(___('Comments'));?></th>
											<td>
											<?php 
												$count_comments = theme_features::get_user_comments_count($author_id);
												echo (int)$count_comments != 0 ? $count_comments : '-';
												?>
											</td>
										</tr>
									</tbody>
								</table>
							</fieldset>
						</div>
						<?php
				}
				?>
				
			</div>
		</div><!-- /#main -->
		<?php get_sidebar() ;?>
	</div>
	
	<?php get_sidebar();?>
</div>
<?php get_footer();?>