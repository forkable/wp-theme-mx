<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title">
			<i class="fa fa-<?= theme_custom_pm::get_tabs(get_query_var('tab'))['icon'];?>"></i>
			<?= ___('Private messages');?>
		</h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-2">
				<nav id="pm-tab" class="pm-tab">
					<a id="pm-tab-new" href="javascript:;" data-uid="new" class="active">
						<i class="fa fa-plus fa-fw"></i>&nbsp;<?= ___('New P.M.');?>
					</a>
					<?php theme_custom_pm::the_tabs();?>
				</nav>
			</div>
			<div class="col-sm-10">
				<div class="pm-dialog-container">
					<!-- pm-new -->
					<form action="javascript:;" id="pm-dialog-new" class="pm-dialog">
						<p class="well"><?= ___('Add a receiver UID to send private message.');?></p>
						<div class="form-group">
							<input type="number" name="pm[new-receiver-id]" id="pm-dialog-content-new" class="form-control text-center" placeholder="<?= ___('Receiver UID, e.g. 100914');?>" title="<?= ___('Please type receiver UID');?>" required min="1">
						</div>
						<div class="form-group">
							<button type="submit" class="btn btn-success btn-block"><?= ___('Next');?> <i class="fa fa-chevron-right"></i></button>
						</div>
					</form>
					<?php theme_custom_pm::the_dialogs();?>
				</div>
			</div><!-- col -->
		</div><!-- .row -->
	</div>
</div>