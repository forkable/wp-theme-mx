<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_edit::get_tabs('edit')['icon'];?>"></i> 
			<?= theme_custom_edit::get_tabs('edit')['text'];?>
		</h3>
	</div>
	<?php
	global $post;
	$query = theme_custom_edit::get_query();
	//var_dump($query);
	if($query->have_posts()){
		?>
		<table class="table edit-table">
			<thead>
				<tr>
					<th class="edit-head-thumbnail"><?= ___('Thumbnail');?></th>
					<th class="edit-head-title"><?= ___('Title');?></th>
					<th class="edit-head-categories hidden-xs"><?= ___('Categories');?></th>
					<!-- <th class="edit-head-tags"><?= ___('Tags');?></th> -->
					<th class="edit-head-date"><?= ___('Date');?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach($query->posts as $post){
				setup_postdata($post);
				$post_title = esc_html(get_the_title());
				?>
				<tr>
					<td class="edit-post-thumbnail">
						<?php
						$thumbnail_real_src = esc_url(theme_functions::get_thumbnail_src($post->ID));

						$thumbnail_placeholder = esc_url(theme_features::get_theme_images_url(theme_functions::$thumbnail_placeholder));
						
						?>
						<img class="post-list-img" src="<?= $thumbnail_placeholder;?>" data-src="<?= $thumbnail_real_src;?>" alt="<?= $post_title;?>" width="<?= theme_functions::$thumbnail_size[1];?>" height="<?= theme_functions::$thumbnail_size[2];?>"/>
					</td>
					<td class="edit-post-title">
						<p><strong><a href="<?php the_permalink();?>" title="<?= ___('Click to edit');?>"><?= $post_title;?></a></strong></p>
						<div class="edit-post-action btn-group btn-group-xs">
							<a href="<?= esc_url(get_edit_post_link($post->ID));?>" class="btn btn-primary edit-post-action-edit">
								<i class="fa fa-pencil-square-o"></i> 
								<?= ___('Edit');?>
							</a>
							<a class="btn btn-default edit-post-action-view" href="<?php the_permalink();?>" target="_blank">
								<i class="fa fa-link"></i> 
								<?= ___('View');?>
							</a>
						</div>
					</td>
					<td class="edit-post-categories hidden-xs">
						<?= get_the_category_list(' / ');?>
						<!--<?php foreach(get_the_category() as $cat){ ?>
							<a href="<?= esc_url(get_category_link($cat->term_id));?>" title="<?= ___('Views more posts in the category');?>">
								<?= esc_html($cat->name);?>
							</a>
						<?php } ?>-->
					</td>
					<!-- <td class="edit-post-tags">
						<?= get_the_tag_list('',' / ','');?>
					</td> -->
					<td class="edit-post-date">
						<abbr title="<?= get_the_time('Y/m/d H:i:s');?>"><?= friendly_date(get_the_time('U'));?></abbr>
						<div class="edit-post-status">
							<?php
							switch($post->post_status){
								case 'publish':
									echo ___('Published');
									break;
								case 'pending':
									echo ___('Pending');
									break;
								
							}
							?>
						</div>
					</td>
				</tr>
				<?php
			}
			wp_reset_postdata();
			?>
			</tbody>
		</table>
		<?php
	}else{
		?>
		<div class="panel-body">
			<div class="page-tip"><?= status_tip('info',___('No data yet.'));?></div>
		</div>
		<?php
	}
	?>
	
	
	
		
	</table>
</div>
<?php
