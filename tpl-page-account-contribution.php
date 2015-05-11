<?php

/**
 * tags
 */
$tags_args = array(
	'orderby' => 'count',
	'order' => 'desc',
	'hide_empty' => 0,
	'number' => theme_custom_contribution::get_options('tags-number') ? theme_custom_contribution::get_options('tags-number') : 16,
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
//var_dump($cap = get_user_meta(get_current_user_id(),'wp_capabilities', true));
?>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_contribution::get_tabs('contribution')['icon'];?>"></i> 
			<?= theme_custom_contribution::get_tabs('contribution')['text'];?>
		</h3>
	</div>
	<div class="panel-body">
		<form action="javascript:;" id="fm-ctb" class="form-horizontal">
			<div class="form-group">
				<label for="ctb-title" class="col-sm-2 control-label">
					<?= ___('Post title');?>
				</label>
				<div class="col-sm-10">
					<input 
						type="text" 
						name="ctb[post-title]"
						class="form-control" 
						id="ctb-title" 
						placeholder="<?= ___('Post title (require)');?>" 
						title="<?= ___('Post title must to write');?>" 
						required 
						autofocus
					>
				</div>
			</div>
			<!-- post content -->
			<div class="form-group">
				<label for="ctb-content" class="col-sm-2 control-label">
					<?= ___('Post content');?>
				</label>
				<div class="col-sm-10">
					<?php 
					wp_editor( '', 'ctb-content', [
						'textarea_name' => 'ctb[post-content]',
						'drag_drop_upload' => true,
						'teeny' => true,
						'media_buttons' => false,
					]);
					?>
				</div>
			</div>
			<!-- upload image -->
			<div class="form-group">
				<div class="col-sm-2 control-label">
					<i class="fa fa-image"></i>
					<?= ___('Upload image');?>
				</div>
				<div class="col-sm-10">
					<div id="ctb-file-area">
						<div class="" id="ctb-file-btn">
							<i class="fa fa-image"></i>
							<?= ___('Select or Drag images');?>
							<input type="file" id="ctb-file" multiple >
						</div>
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
						<?= ___('Storage link');?>
					</div>
					<div class="col-sm-10">
						<?php foreach(theme_custom_storage::get_types() as $k => $v){ ?>
<div class="row">
<div class="col-sm-8">
	<div class="input-group">
	<label class="input-group-addon" id="<?= theme_custom_storage::$iden;?>-<?= $k;?>-url-addon" for="<?= theme_custom_storage::$iden;?>-<?= $k;?>-url"><i class="fa fa-link fa-fw"></i></label>
	<input 
		type="url" 
		name="<?= theme_custom_storage::$iden;?>[<?= $k;?>][url]" 
		id="<?= theme_custom_storage::$iden;?>-<?= $k;?>-url" 
		class="form-control" 
		placeholder="<?= sprintf(___('%s url'),$v['text']);?>"
		aria-describedby="<?= theme_custom_storage::$iden;?>-<?= $k;?>-url-addon"
		title="<?= sprintf(___('%s url'),$v['text']);?>"
	>
	</div>
</div>
<div class="col-sm-4">
	<div class="input-group">
	<label class="input-group-addon" id="<?= theme_custom_storage::$iden;?>-<?= $k;?>-pwd-addon" for="<?= theme_custom_storage::$iden;?>-<?= $k;?>-pwd"><i class="fa fa-key fa-fw"></i></label>
	<input 
		type="text" 
		name="<?= theme_custom_storage::$iden;?>[<?= $k;?>][pwd]" 
		id="<?= theme_custom_storage::$iden;?>-<?= $k;?>-pwd" 
		class="form-control" 
		placeholder="<?= sprintf(___('%s password'),$v['text']);?>"
		aria-describedby="<?= theme_custom_storage::$iden;?>-<?= $k;?>-pwd-addon"
		title="<?= sprintf(___('%s password'),$v['text']);?>"
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
					<?= ___('Category');?>
				</div>
				<div class="col-sm-10">
					<?php 
					wp_dropdown_categories([
						'id' => 'ctb-cat',
						'name' => 'ctb[cat]',
						'class' => 'form-control',
						'show_option_none' => ___('Select a category'),
						'hierarchical' => true,
						'include' => (array)theme_custom_contribution::get_options('cats'),
					]); 
					?>
				</div>
			</div>
			<!-- tags -->
			<div class="form-group">
				<div class="col-sm-2 control-label">
					<i class="fa fa-tags"></i>
					<?= ___('Pop. tags');?>
				</div>
				<div class="col-sm-10">
					<div class="checkbox-select">
						<?php
						foreach($tags as $tag){
							?>
							<label class="ctb-tag" for="ctb-tags-<?= $tag->term_id;?>">
								<input 
									type="checkbox" 
									id="ctb-tags-<?= $tag->term_id;?>" 
									name="ctb[tags][]" 
									value="<?= esc_attr($tag->name);?>"
									hidden
								>
								<span class="label label-default">
									<?= esc_html($tag->name);?>
								</span>
							</label>
							
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-2 control-label">
					<i class="fa fa-tag"></i>
					<?= ___('Custom tags');?>
				</div>
				<div class="col-sm-10">
					<div id="custom-tag">
						<div class="row">
							<?php for($i = 0;$i<=3;++$i){ ?>
								<div class="col-xs-6 col-sm-3">
									<div class="input-group input-group-sm">
										<label class="input-group-addon" for="ctb-tag-<?= $i;?>"><i class="fa fa-tag fa-fw"></i></label>
										<input id="ctb-tag-<?= $i;?>" class="custom-tag-list form-control" type="text" name="ctb[tags][]" placeholder="<?= sprintf(___('tag %d'),$i+1);?>" size="10">
									</div>
								</div>
							<?php } ?>
						</div>
						<!-- <div id="custom-tag-added-container"></div>
						<div id="custom-tag-add-container">
							<div class="input-group">
								<input type="text" id="custom-tag-new" class="form-control" placeholder="<?= ___('Custom tag');?>" size="10">
								<span class="input-group-btn">
									<a id="custom-tag-add-btn" href="javascript:;" class="btn btn-info"><i class="fa fa-plus" data-tpl=""></i> <?= ___('Add');?></a>
								</span>
							</div>
						</div> -->
					</div>
				</div>
			</div>

			<!-- source -->
			<?php if(class_exists('theme_custom_post_source')){ ?>
				<div class="form-group">
					<div class="col-sm-2 control-label">
						<i class="fa fa-truck"></i>
						<?= ___('Source');?>
					</div>
					<div class="col-sm-10">
						<label class="radio-inline" for="<?= theme_custom_post_source::$iden;?>-source-original">
							<input type="radio" name="<?= theme_custom_post_source::$iden;?>[source]" id="<?= theme_custom_post_source::$iden;?>-source-original" value="original" class="<?= theme_custom_post_source::$iden;?>-source-radio" checked >
							<?= ___('Original');?>
						</label>
						<label class="radio-inline" for="<?= theme_custom_post_source::$iden;?>-source-reprint">
							<input type="radio" name="<?= theme_custom_post_source::$iden;?>[source]" id="<?= theme_custom_post_source::$iden;?>-source-reprint" value="reprint" class="<?= theme_custom_post_source::$iden;?>-source-radio" >
							<?= ___('Reprint');?>
						</label>
						<div class="row" id="reprint-group">
							<div class="col-sm-7">
								<div class="input-group">
									<label class="input-group-addon" for="<?= theme_custom_post_source::$iden;?>-reprint-url">
										<i class="fa fa-link"></i>
									</label>
									<input type="url" class="form-control" name="<?= theme_custom_post_source::$iden;?>[reprint][url]" id="<?= theme_custom_post_source::$iden;?>-reprint-url" placeholder="<?= ___('The source of work URL, includes http://');?>" title="<?= ___('The source of work URL, includes http://');?>">
								</div>
							</div>
							<div class="col-sm-5">
								<div class="input-group">
									<label class="input-group-addon" for="<?= theme_custom_post_source::$iden;?>-reprint-author">
										<i class="fa fa-user"></i>
									</label>
									<input type="text" class="form-control" name="<?= theme_custom_post_source::$iden;?>[reprint][author]" id="<?= theme_custom_post_source::$iden;?>-reprint-author" placeholder="<?= ___('Author');?>" title="<?= ___('Author');?>">
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php } /** end theme_custom_post_source */ ?>
			<!-- submit -->
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="page-tip submit-tip"></div>
					
					<button type="submit" class="btn btn-lg btn-success btn-block submit" data-loading-text="<?= ___('Loading, please wait...');?>">
						<i class="fa fa-check"></i>
						<?= ___('Submit');?>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>