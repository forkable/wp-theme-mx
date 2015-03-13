<?php
/*
Feature Name:	主题帮助与说明
Feature URI:	http://www.inn-studio.com
Version:		2.0.1
Description:	主题必须组件，显示主题相关信息与说明
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_help::init';
	return $fns;
});
class theme_help{
	public static $iden = 'theme_help';
	public static function init(){
		add_action('help_settings',get_class() . '::admin');
		add_action('after_backend_tab_init',get_class() . '::js'); 
	}
	
	public static function admin(){
		
		$options = theme_options::get_options();
		$theme_data = wp_get_theme();
		$theme_meta_origin = theme_functions::theme_meta_translate();
		$is_oem = isset($theme_meta_origin['oem']) ? true : false;
		$theme_meta = isset($theme_meta_origin['oem']) ? $theme_meta_origin['oem'] : $theme_meta_origin;
		?>
<fieldset>
	<legend><?php echo ___('Theme Information');?></legend>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php echo ___('Theme name');?></th>
				<td><?php echo $theme_meta['name'];?></td>
			</tr>
			<tr>
				<th scope="row"><?php echo ___('Theme version');?></th>
				<td><?php echo $theme_data->display('Version');?></td>
			</tr>
			<tr>
				<th scope="row"><?php echo ___('Theme edition');?></th>
				<td><?php echo $theme_meta_origin['edition'];?></td>
			</tr>
			<tr>
				<th scope="row"><?php echo ___('Theme description');?></th>
				<td><p><?php echo $theme_meta['des'];?></p></td>
			</tr>
			<tr>
				<th scope="row"><?php echo ___('Theme URI');?></th>
				<td><a href="<?php echo esc_url($theme_meta['theme_url'])?>" target="_blank"><?php echo esc_url($theme_meta['theme_url'])?></a></td>
			</tr>
			<tr>
				<th scope="row"><?php echo ___('Theme author');?></th>
				<td><?php echo esc_html($theme_meta['author'])?></td>
			</tr>
			<tr>
				<th scope="row"><?php echo ___('Author site');?></th>
				<td><a href="<?php echo esc_url($theme_meta['author_url'])?>" target="_blank"><?php echo esc_url($theme_meta['author_url'])?></a></td>
			</tr>
			<tr>
				<th scope="row"><?php echo esc_html(___('Feedback and technical support'));?></th>
				<td>
				
					<?php if(isset($theme_meta['email'])){ ?>
						<p><?php echo esc_html(___('E-Mail'));?> <a href="mailto:<?php echo $theme_meta['email'];?>"><?php echo esc_html($theme_meta['email']);?></a></p>
					<?php } ?>
					
					<?php if(isset($theme_meta['qq'])){ ?>
						<p><?php echo esc_html(___('QQ'));?>
							<?php if(isset($theme_meta['qq']['link'])){ ?>
								<a target="_blank" href="<?php echo esc_url($theme_meta['qq']['link']);?>"><?php echo $theme_meta['qq']['number'];?></a>
							<?php }else{ ?>
								<?php echo $theme_meta['qq']['number'];?>
							<?php } ?>
						</p>
					<?php } ?>
					
					<?php if(isset($theme_meta['qq_group'])){ ?>
						<p><?php echo esc_html(___('QQ group'));?>
							<?php if(isset($theme_meta['qq_group']['link'])){ ?>
								<a target="_blank" href="<?php echo esc_url($theme_meta['qq_group']['link']);?>"><?php echo $theme_meta['qq_group']['number'];?></a>
							<?php }else{ ?>
								<?php echo $theme_meta['qq_group']['number'];?>
							<?php } ?>
						</p>
					<?php } ?>
				</td>
			</tr>
			<?php if(!$is_oem){ ?>
				<tr>
					<th scope="row"><?php echo ___('Donate');?></th>
					<td>
						<p>
							<a id="paypal_donate" href="javascript:void(0);" title="<?php echo ___('Donation by Paypal');?>">
								<img src="http://ww2.sinaimg.cn/large/686ee05djw1ella1kv74cj202o011wea.jpg" alt="<?php echo ___('Donation by Paypal');?>" width="96" height="37"/>
							</a>
							<a id="alipay_donate" target="_blank" href="http://ww3.sinaimg.cn/large/686ee05djw1eihtkzlg6mj216y16ydll.jpg" title="<?php echo ___('Donation by Alipay');?>">
								<img width="96" height="37" src="http://ww1.sinaimg.cn/large/686ee05djw1ellabpq9euj202o011dfm.jpg" alt="<?php echo ___('Donation by Alipay');?>"/>
							</a>
						</p>
					</td>
				</tr>
			<?php }else{ ?>
			<tr>
				<th scope="row"><?php echo ___('Theme core');?></th>
				<td><a href="<?php echo esc_url($theme_meta['core']['url'])?>" target="_blank"><?php echo esc_html($theme_meta['core']['name'])?></a></td>
			</tr>
			<?php } ?>


		</tbody>
	</table>
</fieldset>
	<?php
	}
	public static function js(){
		
		?>
		seajs.use('<?php echo theme_features::get_theme_includes_js(__DIR__);?>',function(m){
			/** alipay */
			m.alipay.config.lang.M00001 = '<?php echo esc_js(sprintf(___('Donate to INN STUDIO (%s)'),theme_features::get_theme_info('name')));?>';
			m.alipay.config.lang.M00002 = '<?php echo esc_js(___('Message for INN STUDIO:'));?>';
			
			/** paypal */
			m.paypal.config.lang.M00001 = '<?php echo esc_js(sprintf(___('Donate to INN STUDIO (%s)'),theme_features::get_theme_info('name')));?>';
			

			m.init();
		});
		<?php
	}
}
?>