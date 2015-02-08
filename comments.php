<?php if(post_password_required()) return; ?>
<div id="comments" class="comment-wrapper">
	<?php if(have_comments()){ ?>
		<h3 class="have-comments-title"><?php echo esc_html(sprintf(___('Comments list (%d)'),get_comments_number()));?></h3>
	<?php } ?>
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
		<?php
		/** 
		 * comment form
		 */
		$req      = get_option( 'require_name_email' );
		$req_html = $req ? '<span class="required">' . esc_html(___('(required)')) . '</span>' : null;
		$req_val = $req ? ___('(required)') : null;
		$req_attr = $req ? ' required ' : null;
		$commenter = wp_get_current_commenter();
		comment_form(array(
			'cancel_reply_link' => '<span class="btn btn-small">' . esc_html(___('Cancel reply')) . '</span>',
			'title_reply' 		=> '<span class="leave-reply">' . esc_html(___('Leave a Reply')) . '</span>',
			'title_reply_to' 	=> '<span class="leave-reply">' . esc_html(___('Leave a Reply to %s')) . '</span>',
			'fields' 			=> array(
				'author' 	=> '<div class="comment-form-group-author form-group">
					<label for="comment-form-author" class="sr-only">' . ___('Nickname') . ' ' . $req_val . '</label>
					<div class="input-group">
						<label for="comment-form-author" class="input-group-addon"><i class="fa fa-user"></i></label>
						<input 
							type="text" 
							id="comment-form-author" 
							name="author" 
							class=" form-control" 
							placeholder="' . ___('Nickname') . ' ' . $req_val . '"
							value="' . esc_attr($commenter['comment_author']) . '" 
							size="30" 
							' . $req_attr . ' 
						>
					</div>
				</div>',
				'email' 	=> '<div class="comment-form-group-email form-group">
					<label for="comment-form-email" class="sr-only">' . ___('Email') . ' ' . $req_val . '</label>
					<div class="input-group">
						<label for="comment-form-email" class="input-group-addon"><i class="fa fa-envelope"></i></label>
						<input 
							type="email" 
							name="email" 
							id="comment-form-email" 
							class=" form-control" 
							id="comment-form-author" 
							placeholder="' . ___('Email') . ' ' . $req_val . '"
							value="' . esc_attr($commenter['comment_author_email']) . '" 
							size="30" 
							' . $req_attr . ' 
						>
					</div>
				</div>',
				'url' 	=> '<div class="comment-form-group-url form-group">
					<label for="comment-form-url" class="sr-only">' . ___('Website') . ' ' . $req_val . '</label>
					<div class="input-group">
						<label for="comment-form-url" class="input-group-addon"><i class="fa fa-home"></i></label>
						<input 
							type="url" 
							id="comment-form-url" 
							name="url" 
							class=" form-control" 
							id="comment-form-author" 
							placeholder="' . ___('Website') . '"
							value="' . esc_url($commenter['comment_author_url']) . '" 
							size="30" 
						>
					</div>
				</div>',
			),
			'logged_in_as'		=> null,
			'comment_field' 	=> '<div class="comment-form-group-comment form-group">
					<label for="comment-form-comment" class="sr-only">' . ___('Comment') . '</label>
					<div class="input-group">
						<label for="comment-form-comment" class="input-group-addon"><i class="fa fa-comment"></i></label>
						<textarea 
						class="form-control" 
						id="comment-form-comment" 
						name="comment" 
						cols="45" 
						rows="5" 
						required 
						placeholder="' . ___('Comment content') . '"></textarea>
					</div>
				</div>
			',
			'comment_notes_before' => null,
			'comment_notes_after' => '<div class="comment-form-group-submit form-group">
				<button class="btn btn-primary" type="submit">
					<i class="fa fa-send"></i> 
					' . ___('Submit') . '
				</button>
			</div>',
			
		));
		?>		