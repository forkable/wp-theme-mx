<div class="panel panel-default">
	<div class="panel-body">
		<?= theme_point_lottery::get_des();?>
		<form action="javascript:;" method="post" id="fm-lottery">
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
							$icon = '<i class="fa fa-yelp"></i>';
						}
						?>
						<label for="lottery-box-<?= $k;?>" class="label label-default" title="<?= esc_attr($box['des']);?>">
							<?= $icon;?> <?= $box['name'];?>
							<input type="radio" name="lottery[id]" id="lottery-box-<?= $k;?>" data-target="lottery-box-<?= $k;?>" value="<?= $k;?>" hidden>
						</label>
						<?php
					}
				}
				?>
			</div>
			<div class="form-group">
				<button type="submit" class="btn btn-success btn-block btn-lg" data-loading-tx="<?= ___('Loading...');?>" >
					<i class="fa fa-check"></i> 
					<?= ___('Start!');?>
				</button>
			</div>
		</form>
	</div>
</div>