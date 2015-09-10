<div class="panel panel-default">
	<div class="panel-body">
		<?= theme_point_lottery::get_des();?>
		<form action="javascript:;" method="post" id="fm-lottery">
			<div class="form-group my-info">
				<span class="meta">
					<img class="avatar" src="<?= theme_cache::get_avatar_url(theme_cache::get_current_user_id());?>" alt="<?= theme_cache::get_the_author_meta('display_name',theme_cache::get_current_user_id());?>" width="16" height="16">
					<span class="author"><?= theme_cache::get_the_author_meta('display_name',theme_cache::get_current_user_id());?></span>
				</span>
				<span class="meta">
					<img src="<?= theme_custom_point::get_point_img_url();?>" alt="point-icon" width="16" height="16"> 
					<span id="point-count"><?= theme_custom_point::get_point(theme_cache::get_current_user_id());?></span><span id="modify-count"></span>
				</span>
			</div>
			<div class="form-group">
				<?php
				$boxes = theme_point_lottery::get_box();
				if(empty($boxes)){
					echo status_tip('info',___('No any lottery yet.'));
				}else{
					
					foreach($boxes as $k => $box){
						if($box['type'] === 'point'){
							$icon = '<img src="' . theme_custom_point::get_point_img_url() . '" alt="icon" width="16" height="16">';
						}else{
							$icon = '<i class="fa fa-yelp fa-fw"></i>';
						}
						?>
						<input type="radio" class="lottery-item" name="id" id="lottery-item-<?= $k;?>" data-target="lottery-box-<?= $k;?>" value="<?= $k;?>"  required >
						<label id="lottery-box-<?= $k;?>" for="lottery-item-<?= $k;?>" class="lottery-box">
							<span class="icon"><?= $icon;?></span>
							<span class="name"><?= $box['name'];?> </span>
							<span class="remaining"><?= sprintf(___('Remaining: %d'),$box['remaining']);?></span>
							<span class="des"><?= $box['des'];?></span>
							
						</label>
						<?php
					}
				}
				?>
			</div>
			<div class="form-group">
				<button type="submit" class="submit btn btn-success btn-block btn-lg" data-loading-tx="<?= ___('Loading...');?>">
					<i class="fa fa-check"></i> 
					<?= ___('Good luck!');?>
				</button>
				<input type="hidden" name="type" value="start">
			</div>
		</form>
	</div>
</div>