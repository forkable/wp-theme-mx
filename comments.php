<?php
if( post_password_required() || !comments_open() ) 
	return;
	
$have_comments = have_comments();

?>

<div id="comments" class="comment-wrapper panel panel-default <?= $have_comments ? null : 'none';?>">
	<div class="panel-heading">
		<h3 class="have-comments-title panel-title">
			<i class="fa fa-comments"></i> 
			<span id="comment-number-<?= $post->ID;?>">-</span> 
			<?= ___('Comments');?>

			<a href="#respond" id="goto-comment" >
				<?= ___('Respond');?> 
				<i class="fa fa-pencil-square-o"></i> 
				
			</a>
		</h3>
	</div>

	<div class="panel-body">			
		<ul id="comment-list-<?= $post->ID;?>" class="comment-list">
			<li class="comment media comment-loading">
				<div class="page-tip"><?= status_tip('loading',___('Loading, please wait...'));?></div>
			</li>
		</ul>
	</div><!-- /.panel-body -->
	
	<?php 
	if(theme_features::get_comment_pages_count($wp_query->comments) > 1){ 
		?>
		<div class="panel-footer">
			<div id="comment-pagination-container"></div>
		</div>
	<?php } ?>
</div><!-- /.comment-wrapper -->

<?php theme_functions::theme_respond();?>
