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
							$icon = '<i class="fa fa-yelp fa-fw"></i>';
						}
						?>
						<input type="radio" class="lottery-item" name="lottery[id]" id="lottery-item-<?= $k;?>" data-target="lottery-box-<?= $k;?>" value="<?= $k;?>"  required >
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
				<button type="submit" class="btn btn-success btn-block btn-lg" data-loading-tx="<?= ___('Loading...');?>" >
					<i class="fa fa-check"></i> 
					<?= ___('Good luck!');?>
				</button>
			</div>
		</form>
	</div>
</div>