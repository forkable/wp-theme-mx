<?php if(post_password_required()) return; ?>
<div id="comments" class="comment-wrapper panel panel-default <?php echo have_comments() ? null : 'none';?>">
	<div class="panel-heading">
		<h3 class="have-comments-title panel-title">
			<i class="fa fa-comments"></i> 
			<?php if(have_comments()){ ?>
				<?php echo esc_html(sprintf(___('Comments list (%d)'),get_comments_number()));?>
			<?php }else{ ?>
				<?php echo ___('Comments list');?>
			<?php } ?>
		</h3>
	</div>
	<div class="panel-body">
<?php
	/** 
	 * if comment open
	 */
	if(comments_open()){
		?>

		<?php
		/** 
		 * have comment
		 */
		if(have_comments()){
			?>
			<?php
			/** 
			 * comment pagination
			 */
			echo theme_functions::get_comment_pagination(array(
				'classes' => 'comment-pagination comment-pagination-above',
			));
			?>				
			<ul id="comment-list-<?php the_ID();?>" class="comment-list">
				<?php wp_list_comments(array(
					'callback'=>'theme_functions::theme_comment',
				));?>
			</ul>
			<?php
			/** 
			 * comment pagination
			 */
			echo theme_functions::get_comment_pagination(array(
				'classes' => 'comment-pagination comment-pagination-below',
			));
		/** 
		 * no comment
		 */
		}else{
			?>
			<ul id="comment-list-<?php the_ID();?>" class="comment-list "></ul>				
			<?php
		}
	/** 
	 * comment is close
	 */
	}else{
	?>
		<p class="no-comment hide"><?php __e( 'Comments are closed.'); ?></p>
	<?php
	}
	?>
</div><!-- .comment-wrapper -->

</div><!-- /.panel -->


<?php theme_functions::theme_respond();?>