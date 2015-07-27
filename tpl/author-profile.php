<?php
global $author;
?>
<div class="panel-body">
	<fieldset class="author-profile">
		<legend>
			<i class="fa fa-newspaper-o"></i> 
			<?= ___('Basic profile');?>
		</legend>
		<table class="table">
			<tbody>
				<tr>
					<th><abbr title="<?= ___('Unique identifier');?>"><?= ___('UID');?></abbr></th>
					<td>
						<a href="<?= theme_cache::get_author_posts_url($author);?>" title="<?= theme_cache::get_author_posts_url($author);?>">
							<?= theme_cache::get_the_author_meta('user_nicename',$author);?>
						</a>
					</td>
				</tr>
				<tr>
					<th><?= ___('Nickname');?></th>
					<td><strong><?= theme_cache::get_the_author_meta('display_name',$author);?></strong></td>
				</tr>
				<tr>
					<th><?= ___('Registered');?></th>
					<td><?= theme_cache::get_the_author_meta('user_registered',$author);?></td>
				</tr>
				<tr>
					<th><?= ___('Website / Blog');?></th>
					<td>
					<?php 
					$website_url = esc_url(theme_cache::get_the_author_meta('user_url',$author));
					if($website_url){
						?>
						<a href="<?= $website_url;?>" target="_blank" rel="nofollow"><?= $website_url;?></a>
					<?php }else{ ?>
						-
					<?php } ?>
					</td>
				</tr>
				<tr>
					<th><?= ___('Description');?></th>
					<td><?= theme_cache::get_the_author_meta('description',$author) ? theme_cache::get_the_author_meta('description',$author) : '-';?></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
	<!-- Statistics -->
	<fieldset class="author-profile">
		<legend>
			<i class="fa fa-pie-chart"></i> 
			<?= ___('Statistics');?></legend>
		<table class="table">
			<tbody>
				<tr>
					<th><?= ___('Works');?></th>
					<td><a href="<?= theme_custom_author_profile::get_tabs('works',$author)['url'];?>">
						<?php
						$author_posts_count = theme_custom_author_profile::get_count('works',$author);
						echo (int)$author_posts_count !== 0 ? $author_posts_count : '-';
						?>
					</a></td>
				</tr>
				<tr>
					<th><?= ___('Comments');?></th>
					<td>
					<?php 
						$count_comments = theme_features::get_user_comments_count($author);
						echo (int)$count_comments != 0 ? $count_comments : '-';
						?>
					</td>
				</tr>
				<?php if(class_exists('theme_custom_point')){ ?>
					<tr>
						<th><?= theme_custom_point::get_point_name();?></th>
						<td>
							<?php 
							if(theme_custom_point::get_point($author) > 0){ 
								if(class_exists('theme_custom_point_bomb')){
									?>
									<a href="<?= theme_custom_point_bomb::get_tabs('bomb',$author)['url'];?>">
										<i class="fa fa-bomb"></i> 
										<?= theme_custom_point::get_point($author);?>
									</a>
								<?php 
								}else{
									echo theme_custom_point::get_point($author);
								}
							}else{ ?>
								0
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</fieldset>
</div>