<?php
/**
 * cats
 */
$cats_args = array(
	'orderby' => 'count',
	'order' => 'desc',
	'hide_empty' => 0,
	'number' => 5,
);
if(class_exists('theme_custom_contribution')){
	$cat_ids = theme_custom_contribution::get_options('cats');
	if(empty($cat_ids)){
		$cats = get_categories($cats_args);
	}else{
		$cats = get_categories(array(
			'include' => $cat_ids,
			'orderby' => 'count',
			'order' => 'desc',
			'hide_empty' => 0,
		));
	}
}else{
	$cats = get_categories($cats_args);
}
/**
 * tags
 */
$tags_args = array(
	'orderby' => 'count',
	'order' => 'desc',
	'hide_empty' => 0,
	'number' => 5,
);
if(class_exists('theme_custom_contribution')){
	$tags_ids = theme_custom_contribution::get_options('tags');
	if(empty($tag_ids)){
		$tags = get_tags($tags_args);
	}else{
		$tags = get_tags(array(
			'include' => implode($tags_ids),
			'orderby' => 'count',
			'order' => 'desc',
			'hide_empty' => 0,
		));
	}
}else{
	$tags = get_tags($tags_args);
}
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?php echo theme_custom_contribution::get_tabs('contribution')['icon'];?>"></i> 
			<?php echo theme_custom_contribution::get_tabs('contribution')['text'];?>
		</h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-ctb" class="form-horizontal">
			<div class="form-group">
				<label for="ctb-title" class="col-sm-2 control-label">
					<?php echo ___('Post title');?>
				</label>
				<div class="col-sm-10">
					<input 
						type="text" 
						name="ctb[post-title]"
						class="form-control" 
						id="ctb-title" 
						placeholder="<?php echo ___('Post title (require)');?>" 
						title="<?php echo ___('Post title must to write');?>" 
						required 
					>
				</div>
			</div>
			<!-- post content -->
			<div class="form-group">
				<label for="ctb-content" class="col-sm-2 control-label">
					<?php echo ___('Post content');?>
				</label>
				<div class="col-sm-10">
					<textarea 
					class="form-control" 
						id="ctb-content" 
						name="ctb[post-content]" 
						placeholder="<?php echo ___('Just write about introduction (require)');?>" 
						title="<?php echo ___('Post content must to write');?>" 
						rows="5" 
						required 
					></textarea>
				</div>
			</div>
			<!-- upload image -->
			<div class="form-group">
				<div class="col-sm-2 control-label">
					<i class="fa fa-image"></i>
					<?php echo ___('Preview images');?>
				</div>
				<div class="col-sm-10">
					<div id="ctb-file-area">
						<a href="javascript:;" class="btn btn-info" id="ctb-file-btn">
							<i class="fa fa-image"></i>
							<?php echo ___('Upload image');?>
							<input type="file" id="ctb-file" multiple >
						</a>
					</div>
					<!-- upload progress -->
					<div id="ctb-file-progress">
						<div id="ctb-file-progress-tx"></div>
						<div id="ctb-file-progress-bar"></div>
					</div>
					<!-- file completion -->
					<div id="ctb-file-completion"></div>
					<!-- files -->
					<div id="ctb-files" class="row"></div>
					
				</div>
			</div>
			<!-- storage -->
			<?php if(class_exists('theme_custom_storage')){ ?>
				<div class="form-group">
					<div class="col-sm-2 control-label">
						<i class="fa fa-cloud-download"></i>
						<?php echo ___('Storage link');?>
					</div>
					<div class="col-sm-10">
						<?php foreach(theme_custom_storage::get_types() as $k => $v){ ?>
<div class="row">
<div class="col-sm-8">
<div class="input-group">
<label class="input-group-addon" id="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-url-addon" for="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-url"><i class="fa fa-link"></i></label>
<input 
	type="url" 
	name="<?php echo theme_custom_storage::$iden;?>[storage][<?php echo $k;?>][url]" 
	id="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-url" 
	class="form-control" 
	placeholder="<?php echo sprintf(___('%s url'),$v['text']);?>"
	aria-describedby="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-url-addon"
	title="<?php echo sprintf(___('%s url'),$v['text']);?>"
>
</div>
</div>
<div class="col-sm-4">
<div class="input-group">
<label class="input-group-addon" id="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-pwd-addon" for="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-pwd"><i class="fa fa-key"></i></label>
<input 
	type="text" 
	name="<?php echo theme_custom_storage::$iden;?>[storage][<?php echo $k;?>][pwd]" 
	id="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-pwd" 
	class="form-control" 
	placeholder="<?php echo sprintf(___('%s password'),$v['text']);?>"
	aria-describedby="<?php echo theme_custom_storage::$iden;?>-<?php echo $k;?>-pwd-addon"
	title="<?php echo sprintf(___('%s password'),$v['text']);?>"
>
</div>
</div>
</div>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
			<!-- cats -->
			<div class="form-group">
				<div class="col-sm-2 control-label">
					<i class="fa fa-folder-open"></i>
					<?php echo ___('Categories');?>
				</div>
				<div class="col-sm-10">
					<div class="checkbox-select">
						<?php
						foreach($cats as $cat){
							?>
							<label class="ctb-cat label label-default" for="ctb-cats-<?php echo $cat->term_id;?>">
								<?php echo esc_html($cat->name);?>
							</label>
							
							<?php
						}
						?>
					</div>
					<?php foreach($cats as $cat){ ?>
						<input 
							type="checkbox" 
							id="ctb-cats-<?php echo $cat->term_id;?>" 
							name="ctb[cats][]" 
							value="<?php echo $cat->term_id;?>"
							hidden
						>
					<?php } ?>
				</div>
			</div>
			<!-- tags -->
			<div class="form-group">
				<div class="col-sm-2 control-label">
					<i class="fa fa-tags"></i>
					<?php echo ___('Tags');?>
				</div>
				<div class="col-sm-10">
					<div class="checkbox-select">
						<?php
						foreach($tags as $tag){
							?>
							<label class="ctb-tag label label-default" for="ctb-tags-<?php echo $tag->term_id;?>">
								<?php echo esc_html($tag->name);?>
							</label>
							
							<?php
						}
						?>
					</div>
					<?php foreach($tags as $tag){ ?>
						<input 
							type="checkbox" 
							id="ctb-tags-<?php echo $tag->term_id;?>" 
							name="ctb[tags][]" 
							value="<?php echo esc_attr($tag->name);?>"
							hidden
						>
					<?php } ?>
				</div>
			</div>
			<!-- source -->
			<?php if(class_exists('theme_custom_post_source')){ ?>
				<div class="form-group">
					<div class="col-sm-2 control-label">
						<i class="fa fa-truck"></i>
						<?php echo ___('Source');?>
					</div>
					<div class="col-sm-10">
						<label class="radio-inline" for="<?php echo theme_custom_post_source::$iden;?>-source-original">
							<input type="radio" name="<?php echo theme_custom_post_source::$iden;?>[source]" id="<?php echo theme_custom_post_source::$iden;?>-source-original" value="original" class="<?php echo theme_custom_post_source::$iden;?>-source-radio" checked >
							<?php echo ___('Original');?>
						</label>
						<label class="radio-inline" for="<?php echo theme_custom_post_source::$iden;?>-source-reprint">
							<input type="radio" name="<?php echo theme_custom_post_source::$iden;?>[source]" id="<?php echo theme_custom_post_source::$iden;?>-source-reprint" value="reprint" class="<?php echo theme_custom_post_source::$iden;?>-source-radio" >
							<?php echo ___('Reprint');?>
						</label>
						<div class="row" id="reprint-group">
							<div class="col-sm-7">
								<div class="input-group">
									<label class="input-group-addon" for="<?php echo theme_custom_post_source::$iden;?>-reprint-url">
										<i class="fa fa-link"></i>
									</label>
									<input type="url" class="form-control" name="<?php echo theme_custom_post_source::$iden;?>[reprint][url]" id="<?php echo theme_custom_post_source::$iden;?>-reprint-url" placeholder="<?php echo ___('The source of work URL, includes http://');?>" title="<?php echo ___('The source of work URL, includes http://');?>">
								</div>
							</div>
							<div class="col-sm-5">
								<div class="input-group">
									<label class="input-group-addon" for="<?php echo theme_custom_post_source::$iden;?>-reprint-author">
										<i class="fa fa-user"></i>
									</label>
									<input type="url" class="form-control" name="<?php echo theme_custom_post_source::$iden;?>[reprint][author]" id="<?php echo theme_custom_post_source::$iden;?>-reprint-author" placeholder="<?php echo ___('Author');?>" title="<?php echo ___('Author');?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php } /** end theme_custom_post_source */ ?>
			<!-- submit -->
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="page-tip"><div class="submit-tip"></div></div>
					
					<button type="submit" class="btn btn-lg btn-success btn-block submit" data-loading-text="<?php echo ___('Loading, please wait...');?>">
						<i class="fa fa-check"></i>
						<?php echo ___('Submit');?>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>