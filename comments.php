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

			<a href="#respond" id="goto-comment" class="btn btn-success btn-xs">
				<?php echo ___('Respond');?> 
				<i class="fa fa-pencil-square-o"></i> 
				
			</a>
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
				<li class="comment media">
					<div class="page-tip"><?php echo status_tip('loading',___('Loading, please wait...'));?></div>
				</li>
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