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
	<?php
	/** 
	 * comment pagination
	 */
	if(have_comments() && comments_open()){
		?>
		<div class="panel-footer">
			<?php
			echo theme_functions::get_comment_pagination([
				'classes' => 'comment-pagination comment-pagination-above',
			]);
			?>
	</div>
	<?php } ?>

	
	<div class="panel-body">
		<?php
		/** 
		 * if comment open
		 */
		if(comments_open()){
			?>			
			<ul id="comment-list-<?php $post->ID;?>" class="comment-list">
				<?php
				/** 
				 * have comment
				 */
				if(have_comments()){
					wp_list_comments(array(
						'type' => 'comment',
						'callback'=>'theme_functions::theme_comment',
					));
				}
				?>
			</ul>
			<?php
		}
		?>
	</div><!-- /.panel-body -->
	
	<?php if(have_comments() && comments_open()){ ?>
		<div class="panel-footer">
			<?php
			/** 
			 * comment pagination
			 */
			echo theme_functions::get_comment_pagination([
				'classes' => 'comment-pagination comment-pagination-below',
			]);
			?>
		</div>
	<?php } ?>
</div><!-- /.comment-wrapper -->

<?php theme_functions::theme_respond();?>