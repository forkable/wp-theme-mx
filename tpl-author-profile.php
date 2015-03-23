<?php
global $author;
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
						<a href="<?php echo esc_url(theme_cache::get_author_posts_url($author));?>" title="<?php echo esc_url(theme_cache::get_author_posts_url($author));?>">
							<?php echo get_the_author_meta('user_nicename',$author);?>
						</a>
					</td>
				</tr>
				<tr>
					<th><?php echo ___('Nickname');?></th>
					<td><strong><?php echo esc_html(get_the_author_meta('display_name',$author));?></strong></td>
				</tr>
				<tr>
					<th><?php echo ___('Registered');?></th>
					<td><?php echo get_the_author_meta('user_registered',$author);?></td>
				</tr>
				<tr>
					<th><?php echo ___('Website / Blog');?></th>
					<td>
					<?php 
					$website_url = get_the_author_meta('user_url',$author);
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
					<td><?php echo esc_html(get_the_author_meta('description',$author) ? get_the_author_meta('description',$author) : '-');?></td>
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
					<td><a href="<?php echo esc_url(theme_custom_author_profile::get_tabs('works',$author)['url']);?>">
						<?php
						$author_posts_count = theme_custom_author_profile::get_count('works',$author);
						echo (int)$author_posts_count !== 0 ? $author_posts_count : '-';
						?>
					</a></td>
				</tr>
				<tr>
					<th><?php echo esc_html(___('Comments'));?></th>
					<td>
					<?php 
						$count_comments = theme_features::get_user_comments_count($author);
						echo (int)$count_comments != 0 ? $count_comments : '-';
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>