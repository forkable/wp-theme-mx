<?php
if(post_password_required()) 
	return;

global $wp_query;



?>

<div id="comments" class="comment-wrapper panel panel-default <?php echo have_comments() ? null : 'none';?>">
	<div class="panel-heading">
		<h3 class="have-comments-title panel-title">
			<span id="comment-number-<?php echo $post->ID;?>" class="badge">-</span> 
			<?php echo ___('Comments');?>
		</h3>
	</div>

	<div class="panel-body">
		<?php
		/** 
		 * if comment open
		 */
		if(comments_open()){
			?>			
			<ul id="comment-list-<?php echo $post->ID;?>" class="comment-list">
				<?php
				/** 
				 * have comment
				 */
				//if(have_comments()){
				//	wp_list_comments(array(
				//		'type' => 'comment',
				//		'callback'=>'theme_functions::theme_comment',
				//	));
				//}
				?>
			</ul>
			<?php
		}
		?>
	</div><!-- /.panel-body -->
	
	<?php 
	if(have_comments() && comments_open() && theme_features::get_comment_pages_count($wp_query->comments) > 1){ 
		?>
		<div class="panel-footer">
			<div id="comment-pagination-container"></div>
		</div>
	<?php } ?>
</div><!-- /.comment-wrapper -->

<?php theme_functions::theme_respond();?>