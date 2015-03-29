<?php
/*
Feature Name:	theme_clean_up
Feature URI:	http://www.inn-studio.com/theme_clean_up
Version:		3.0.0
Description:	optimizate your database
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_clean_up::init';
	return $fns;
});
class theme_clean_up{
	private static $iden = 'theme_clean_up';
	public static function init(){
		add_action('advanced_settings',		__CLASS__ . '::display_backend');
		add_action('wp_ajax_theme_clean_up',__CLASS__ . '::process');
		add_action('after_backend_tab_init',__CLASS__ . '::backend_seajs_use');
		add_action('backend_seajs_alias',__CLASS__ . '::backend_seajs_alias');
	}
	public static function display_backend(){
				
		?>
		<fieldset>
			<legend><?php echo ___('Database Optimization');?></legend>
			<p class="description"><?php echo ___('If your site works for a long time, maybe will have some redundant data in the database, they will reduce the operating speed of the your site, recommend to clean them regularly.');?></p>
			<p class="description"><strong><?php echo esc_html(___('Attention: this action will be auto clean up all theme cache.'));?></strong></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php echo ___('Clean redundant post data');?></th>
						<td>
							<p>
								<a 
									href="javascript:;"
									class="button <?php echo self::$iden;?>-btn" 
									data-action="redundant-posts" 
									data-tip-target="<?php echo self::$iden;?>-redundant-posts"
								><?php echo ___('Delete revision &amp; draft &amp; auto-draft &amp; trash posts');?></a>
							</p>
							<div id="<?php echo self::$iden;?>-redundant-posts"></div>
							<p>
								<a 
									href="javascript:;"
									class="button <?php echo self::$iden;?>-btn" 
									data-action="orphan-postmeta"
									data-tip-target="<?php echo self::$iden;?>-tip-orphan-postmeta"
								><?php echo ___('Delete orphan post meta');?></a>
							</p>
							<div id="<?php echo self::$iden;?>-orphan-postmeta"></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Clean redundant comment data');?></th>
						<td>
							<p><a 
								href="javascript:;"
								class="button <?php echo self::$iden;?>-btn" 
								data-action="redundant-comments"
								data-tip-target="<?php echo self::$iden;?>-tip-redundant-comments""
							><?php echo ___('Delete moderated &amp; spam &amp; trash comments');?></a></p>
							<div id="<?php echo self::$iden;?>-tip-redundant-comments"></div>
							<p><a 
								href="javascript:;"
								class="button <?php echo self::$iden;?>-btn" 
								data-action="orphan-commentmeta"
								data-tip-target="<?php echo self::$iden;?>-tip-orphan-commentmeta""
							><?php echo ___('Delete orphan comment meta');?></a></p>
							<div id="<?php echo self::$iden;?>-tip-orphan-commentmeta"></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Clean redundant other data');?></th>
						<td>
							<p><a 
								class="button <?php echo self::$iden;?>-btn" 
								data-action="orphan-relationships"
								data-tip-target="<?php echo self::$iden;?>-tip-orphan-relationships"
							><?php echo ___('Delete orphan relationship');?></a></p>
							<div id="<?php echo self::$iden;?>-tip-orphan-relationships"></div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Optimizate the WP Database');?></th>
						<td>
							<p><a 
								class="button <?php echo self::$iden;?>-btn" 
								data-action="optimizate"
								data-tip-target="<?php echo self::$iden;?>-tip-database-optimization"
							><?php echo ___('Optimizate Now');?></a></p>
							<div id="<?php echo self::$iden;?>-tip-database-optimization"></div>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function process(){
		$output = [];
		
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		
		timer_start();
		global $wpdb;
		switch($type){
			/** 
			 * revision
			 */
			case 'redundant_posts':
				$sql = $wpdb->prepare(
					"
					DELETE posts,term,postmeta 
					FROM `$wpdb->posts`posts 
					LEFT JOIN `$wpdb->term_relationships` term
					ON (posts.ID = term.object_id)
					LEFT JOIN `$wpdb->postmeta` postmeta 
					ON (posts.ID = postmeta.post_id)
					WHERE posts.post_type = '%s'
					OR posts.post_status = '%s'
					OR posts.post_status = '%s'
					OR posts.post_status = '%s'
					",
					'revision',
					'draft',
					'auto-draft',
					'trash'
				);

				break;
			/** 
			 * edit_lock
			 */
			case 'orphan_postmeta':
				$sql = $wpdb->prepare(
					"
					DELETE FROM `$wpdb->postmeta`
					WHERE `meta_key` = '%s'
					OR `post_id`
					NOT IN (SELECT `ID` FROM `$wpdb->posts`)
					",
					'_edit_lock'
				);
				break;
			
			/** 
			 * moderated
			 */
			case 'redundant_comments':
				$sql = $wpdb->prepare(
					"
					DELETE FROM `$wpdb->comments`
					WHERE `comment_approved` = '%s'
					OR `comment_approved` = '%s'
					OR `comment_approved` = '%s'
					",
					'0','spam','trash'
				);
				break;
			/** 
			 * commentmeta
			 */
			case 'orphan_commentmeta':
				$sql = 
				"
				DELETE FROM `$wpdb->commentmeta`
				WHERE `comment_ID` 
				NOT IN (SELECT `comment_ID` FROM `$wpdb->comments`)
				";
				
				break;
			/** 
			 * relationships
			 */
			case 'orphan_relationships':
				$sql = $wpdb->prepare(
					"
					DELETE FROM `$wpdb->term_relationships`
					WHERE `term_taxonomy_id` = %d 
					AND `object_id` 
					NOT IN (SELECT `id` FROM `$wpdb->posts`)
					",
					1
				);
				break;
			/** 
			 * optimizate
			 */
			case 'optimizate':
				$sql = 'SHOW TABLE STATUS FROM `'.DB_NAME.'`';
				$results = $wpdb->get_results($sql);
				foreach($results as $v){
					$sql = 'OPTIMIZE TABLE '.$v->Name;
					$wpdb->get_results($sql);
				}
				break;
				
			default:
				$output['status'] = 'error';
				$output['msg'] = ___('No param');
				die(theme_features::json_format($output));
		}
				
		if($type !== 'optimizate') $wpdb->query($sql);
		
		/** flush cache */
		wp_cache_flush();
			
		$output['status'] = 'success';
		$output['msg'] = sprintf(___('Database updated in %s s.'),timer_stop());
		
		die(theme_features::json_format($output));
	}
	public static function backend_seajs_alias($alias){
		$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		return $alias;
	}
	public static function backend_seajs_use(){
		?>
		seajs.use('<?php echo self::$iden;?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action'=>self::$iden));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});
		<?php
	}

}
?>