<?php
/*
Feature Name:	theme_clean_up
Feature URI:	http://www.inn-studio.com/theme_clean_up
Version:		1.0.5
Description:	optimizate your database
Author:			INN STUDIO
Author URI:		http://www.inn-studio.com
*/
add_action('advanced_settings','theme_clean_up::admin');
add_action('wp_ajax_theme_clean_up','theme_clean_up::process');
add_action('after_backend_tab_init','theme_clean_up::js');
class theme_clean_up{
	private static $iden = 'theme_clean_up';

	public static function admin(){
				
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
								<p><span class="button" id="clean_redundant_posts" data-action="redundant_posts"><?php echo ___('Delete revision &amp; draft &amp; auto-draft &amp; trash posts');?></span></p>
								<p><span class="button" id="clean_orphan_postmeta" data-action="orphan_postmeta"><?php echo ___('Delete orphan post meta');?></span></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Clean redundant comment data');?></th>
						<td>
								<p><span class="button" id="clean_redundant_comments" data-action="redundant_comments"><?php echo ___('Delete moderated &amp; spam &amp; trash comments');?></span></p>
								<p><span class="button" id="clean_orphan_commentmeta" data-action="orphan_commentmeta"><?php echo ___('Delete orphan comment meta');?></span></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Clean redundant other data');?></th>
						<td>
								<p><span class="button" id="clean_orphan_relationships" data-action="orphan_relationships"><?php echo ___('Delete orphan relationship');?></span></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php echo ___('Optimizate the WP Database');?></th>
						<td>
								<p><span class="button" id="database_optimization" data-action="optimizate"><?php echo ___('Optimizate Now');?></span></p>
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
	<?php
	}
	
	public static function process(){
		$output = null;
		
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		if($type){
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
					$output['des']['content'] = ___('No param');
					die(theme_features::json_format($output));
			}
				
			if($type !== 'optimizate') $wpdb->query($sql);
			theme_cache::cleanup();
			$time = timer_stop();
			$output['status'] = 'success';
			$output['des']['content'] = sprintf(___('Database updated in %s s.'),$time);
		}
		die(theme_features::json_format($output));
	}

	
	public static function js(){
		
		?>
		seajs.use('<?php echo theme_features::get_theme_includes_js(__FILE__);?>',function(m){
			m.config.process_url = '<?php echo theme_features::get_process_url(array('action'=>get_class()));?>';
			m.config.lang.M00001 = '<?php echo ___('Loading, please wait...');?>';
			m.config.lang.E00001 = '<?php echo ___('Server error or network is disconnected.');?>';
			m.init();
		});
		<?php
	}
}
?>