<?php
/**
 * @version 1.0.1
 */
add_filter('theme_includes',function($fns){
	$fns[] = 'theme_custom_pm::init';
	return $fns;
});
class theme_custom_pm{
	public static $iden = 'theme_custom_pm';
	public static $page_slug = 'account';
	public static $metas = [];
	public static $comet_timeout = 30;	
	public static $cache_expire = 2505600;
	//public static $cache_group_id = [
	//	'latest-pm-id' => 'latest-pm-id'
	//];
	public static $table;
	public static $db_version = '1.0.0';
	public static function init(){
		global $wpdb;

		self::$table = $wpdb->prefix . 'pm';
		
		if(!self::get_db_version()){
			self::create_db_table();
			theme_options::set_options(__CLASS__,[
				'db-version' => self::$db_version,
			]);
		}
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);
		
		add_filter('wp_ajax_' . __CLASS__, __CLASS__ . '::process');
		
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		add_filter('frontend_seajs_alias', __CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use', __CLASS__ . '::frontend_seajs_use');

		add_filter('js_cache_request', __CLASS__ . '::js_cache_request');
		add_filter('cache_request' , __CLASS__ . '::cache_request');
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

		add_action('base_settings', 		__CLASS__ . '::display_backend');
		add_action('theme_options_save', 		__CLASS__ . '::options_save');
		
		add_action('wp_footer'		,__CLASS__ . '::wp_footer');
	}
	public static function wp_title($title, $sep){
		if(!self::is_page()) 
			return $title;
			
		$tab_active = get_query_var('tab');
		$tabs = self::get_tabs();
		if(!empty($tab_active) && isset($tabs[$tab_active])){
			$title = $tabs[$tab_active]['text'];
		}
		
		return $title . $sep . theme_cache::get_bloginfo('name');
	}
	public static function wp_footer(){
		if(!self::is_page()) 
			return false;
		/** remove unread count */
		$current_user_id = theme_cache::get_current_user_id();
		if(self::get_unread_count($current_user_id) > 0)
			self::clear_unreads($current_user_id);
	}
	public static function get_db_version(){
		return self::get_options('db-version');
	}
	public static function filter_nav_pm($navs){
		$badge = '';
		$unread_count = self::get_unread_count(theme_cache::get_current_user_id());
		if(!self::is_page() && $unread_count != 0){
			$badge = '<span class="badge">' . $unread_count . '</span>';
		}
		$navs['pm'] = '<a href="' . self::get_tabs('pm')['url'] . '">
			<i class="fa fa-' . self::get_tabs('pm')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('pm')['text'] . $badge . '
		</a>';
		return $navs;
	}
	public static function is_page(){
		static $cache = null;
		if($cache === null)
			$cache = theme_cache::is_page(self::$page_slug) && self::get_tabs(get_query_var('tab'));
			
		return $cache;
	}
	public static function get_url(){
		static $cache = null;
		if($cache === null){
			$cache = theme_cache::get_permalink(theme_cache::get_page_by_path(self::$page_slug)->ID);
		}
		return $cache;
	}
	public static function get_user_pm_url($user_id){
		return self::get_tabs('pm')['url'] . '#' . self::get_niceid($user_id);
	}
	public static function get_tabs($key = null){
		$baseurl = self::get_url();
		$tabs = array(
			'pm' => array(
				'text' => ___('P.M.') . ' beta',
				'icon' => 'envelope',
				'url' => esc_url(add_query_arg('tab','pm',$baseurl)),
				'filter_priority' => 28,
			),
		);
		if($key)
			return isset($tabs[$key]) ? $tabs[$key] : false;
		return $tabs;
	}
	private static function has_table(){
		global $wpdb;
		return $wpdb->get_var("SHOW TABLES LIKE '" . self::$table . "'") == self::$table;
	}
	private static function create_db_table(){
		global $wpdb,$charset_collate;
		
		$sql = "
			CREATE TABLE  " . self::$table . " (
				pm_id  			BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
				pm_author  		BIGINT(20) unsigned default 0,
				pm_receiver  	BIGINT(20) unsigned default 0,
				pm_content  	TEXT NOT NULL,
				pm_date  		DATETIME default '0000-00-00 00:00:00',
				pm_date_gmt  	DATETIME default '0000-00-00 00:00:00',
				pm_agent  		VARCHAR(255) NOT NULL default '',
			PRIMARY KEY ( pm_id ),
				KEY pm_author (pm_author),
				KEY pm_receiver (pm_receiver),
				KEY pm_date_gmt (pm_date_gmt)
			) $charset_collate;
		";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
	}

	public static function options_save(array $opts = []){
		if(isset($_POST[__CLASS__])){
			$opts[__CLASS__] = $_POST[__CLASS__];
		}
		return $opts;
	}
	public static function display_backend(){
		?>
		<fieldset>
			<legend><?= ___('Private message settings');?></legend>
			<p class="description"><?= ___('User can send private message to other user.');?></p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?= ___('Control');?></th>
						<td>
							<?php if(isset($_GET[__CLASS__])){ ?>
								<div id="<?= __CLASS__;?>-tip" calss="page-tip"><?= status_tip('success',___('Database table has been created.'));?></div>
							<?php } ?>
							<a id="<?= __CLASS__;?>-create-table" href="<?= theme_features::get_process_url([
								'action' => __CLASS__,
								'type' => 'create-db',
							]);?>"><?= ___('Create database table');?></a>
							<input type="hidden" name="<?= __CLASS__;?>[db-version]" value="<?= self::get_db_version() ?self::get_db_version() : self::$db_version;?>">
						</td>
					</tr>
				</tbody>
			</table>
		</fieldset>
		<?php
	}
	public static function get_options($key = null){
		static $caches = null;
		if($caches === null)
			$caches = theme_options::get_options(__CLASS__);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches[$key];
	}
	public static function check_uid( $uid ){
		if(!$uid || !is_numeric($uid)){
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'invaild_uid',
				'msg' => ___('Sorry, the UID is invaild, please try again.'),
			]));
		}
		/**
		 * check target user
		 */
		if(class_exists('number_user_nicename'))
			$uid -= number_user_nicename::$prefix_number;
		
		$user = get_user_by('id',$uid);
		if(!$user){
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'user_not_exist',
				'msg' => ___('Sorry, the user does not exist.'),
			]));
		}

		/**
		 * check user is myself
		 */
		if(theme_cache::get_current_user_id() == $uid){
			die(theme_features::json_format([
				'status' => 'error',
				'code' => 'user_is_myself',
				'msg' => ___('Sorry, you can not send P.M. to yourself.'),
			]));
		}
		return $user;
	}
	public static function process(){

		theme_features::check_referer();

		$type = isset($_REQUEST['type']) && is_string($_REQUEST['type']) ? $_REQUEST['type'] : false;

		$current_user_id = theme_cache::get_current_user_id();
		
		switch($type){
			/**
			 * backend create db table
			 */
			case 'create-db':
				if(!theme_cache::current_user_can('manage_options'))
					die(___('Sorry, your permission is not enough to create database table.'));
					//die(theme_features::json_format([
					//	'status' => 'error',
					//	'code' => 'invaild_permission',
					//	'msg' => ___('Sorry, your permission is not enough to create database table.'),
					//]));
				if(self::has_table())
					die(___('Sorry, the database table already exists.'));
					//die(theme_features::json_format([
					//	'status' => 'error',
					//	'code' => 'exists_table',
					//	'msg' => ___('Sorry, the database table already exists.'),
					//]));
				
				self::create_db_table();
				
				theme_options::set_options(__CLASS__,[
					'db-version' => self::$db_version
				]);

				header('location: ' . theme_options::get_url() . '&' . __CLASS__);
				die;
				//die(theme_features::json_format([
				//	'status' => 'success',
				//	'msg' => ___('Database table has been created.'),
				//]));
			/**
			 * get-userdata
			 */
			case 'get-userdata':
				/** nonce */
				theme_features::check_nonce();
				/**
				 * uid
				 */
				$uid = isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']) ? $_REQUEST['uid'] : false;
				/**
				 * get userdata
				 */
				$user = self::check_uid($uid);

				/** add user to lists */
				self::add_list($current_user_id, $user->ID);
				
				die(theme_features::json_format([
					'status' => 'success',
					'name' => esc_html($user->display_name),
					'avatar' => get_avatar_url($user->ID),
					'msg' => ___('User data loaded, you can send P.M. now.'),
				]));
			/**
			 * remove user lists
			 */
			case 'remove-dialog':
				$receiver_uid = isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']) ? (int)$_REQUEST['uid'] : false;
				$receiver = self::check_uid($receiver_uid);
				$status = self::remove_list($current_user_id, $receiver->ID);
				if($status)
					die(theme_features::json_format([
						'status' => 'success',
						'code' => 'removed',
					]));
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'remove_fail',
				]));

				
			/**
			 * send
			 */
			case 'send':
				/** nonce */
				theme_features::check_nonce();
				
				$receiver_uid = isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']) ? $_REQUEST['uid'] : false;
			
				$receiver = self::check_uid($receiver_uid);

				/** check content */
				$content = isset($_REQUEST['content']) && is_string($_REQUEST['content']) ? trim($_REQUEST['content']) : false;
				if($content != '')
					$content = fliter_script(strip_tags($content,'<a><b><strong><em><i><del>'));
				if(trim($content) == '')
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'empty_content',
						'msg' => ___('Sorry, message content is null, please try again.'),
					]));

				/** pass */
				$pm_id = self::insert_pm([
					'pm_author' => $current_user_id,
					'pm_receiver' => $receiver->ID,
					'pm_content' => $content,
				]);
				if(!$pm_id){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'can_not_create_pm',
						'msg' => ___('Sorry, system can not create the private message, please try again later.'),
					]));
				}
				/** get pm */
				$pm = self::get_pm($pm_id);

				/** add list for author */
				self::add_list($current_user_id,$pm->pm_receiver);
				/** add list for receiver */
				self::add_list($pm->pm_receiver,$current_user_id);
				
				die(theme_features::json_format([
					'status' => 'success',
					'pm' => [
						'pm_receiver' => self::get_niceid($pm->pm_receiver),
						'pm_author' => self::get_niceid($pm->pm_author),
						'pm_date' => current_time('Y/m/d H:i:s'),
						'pm_content' => $pm->pm_content,
					],
					'msg' => ___('Message sent.'),
				]));
			/**
			 * latest pm id
			 */
			case 'comet':
				/** nonce */
				theme_features::check_nonce();
				
				$receiver_id = $current_user_id;

				$client_timestamp = isset($_REQUEST['timestamp']) && is_numeric($_REQUEST['timestamp']) ? $_REQUEST['timestamp'] : false;
				
				/** if not client timestamp, return error */
				if(!$client_timestamp){
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'invaild_timestamp',
						'msg' => ___('Sorry, your session is timeout, please refresh page.'),
					]));
				}

				/** set timeout */
				set_time_limit(60);
				/** check new pm for receiver */
				for($i = 0;$i < self::$comet_timeout; ++$i){
					/** have new pm */
					$timestamp = self::get_timestamp($receiver_id);
					if($timestamp <= $client_timestamp){
						sleep(1);
						continue;
					}
						
					/** have new pm, output latest pm */
					$latest_pm = self::get_pm(self::get_latest_pm_id($receiver_id));

					/** clear unreads for me */
					self::clear_unreads($current_user_id);
					
					die(theme_features::json_format([
						'status' => 'success',
						'pm' => [
							'pm_receiver' => self::get_niceid($latest_pm->pm_receiver),
							//'pm_receiver_name' => theme_cache::get_the_author_meta('display_name',$latest_pm->pm_receiver),
							//'pm_receiver_avatar' => theme_cache::get_the_author_meta('display_name',$latest_pm->pm_receiver),
							'pm_author' => self::get_niceid($latest_pm->pm_author),
							'pm_author_name' => theme_cache::get_the_author_meta('display_name',$latest_pm->pm_author),
							'pm_author_avatar' => get_avatar_url($latest_pm->pm_author),
							'pm_date' => current_time('Y/m/d H:i:s'),
							'pm_content' => $latest_pm->pm_content,
						],
						'timestamp' => $timestamp,
					]));
				}

				/** timeout msg */				
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'timeout',
					'msg' => ___('Timeout'),
				]));
				
			default:
				die(theme_features::json_format([
					'status' => 'error',
					'code' => 'invaild_type',
					'msg' => ___('Sorry, type param is invaild.'),
				]));
		}
	}
	public static function get_niceid($user_id){
		if(class_exists('number_user_nicename'))
			$user_id += number_user_nicename::$prefix_number;
		return $user_id;
	}
	public static function set_latest_pm_id($user_id,$pm_id){
		wp_cache_set("latest-pm-id:$user_id",$pm_id,__CLASS__,self::$cache_expire);
	}
	public static function get_latest_pm_id($user_id){
		$pm_id = wp_cache_get("latest-pm-id:$user_id",__CLASS__);
		if($pm_id)
			return $pm_id;
		$pms = self::get_pms([
			'posts_per_page' => 1,
			'author' => $user_id,
		]);
		if(empty($pms))
			$pm_id = false;
		$pm = $pms[0];
		self::setup_pmdata($pm);
		$pm_id = $pm->pm_id;
		
		wp_cache_set("latest-pm-id:$user_id",$pm_id,__CLASS__,self::$cache_expire);
		
		return $pm_id;
	}
	public static function get_user_meta($user_id,$key = null,$force = false){
		$metas = get_user_meta($user_id,__CLASS__,true);
		if($key)
			return isset($metas[$key]) ? $metas[$key] : false;
		return $metas;
	}
	public static function update_user_meta($user_id,$key,$data){
		$metas = self::get_user_meta($user_id);
		$metas[$key] = $data;
		update_user_meta($user_id,__CLASS__,$metas);
	}
	public static function get_unreads($user_id,$force = false){
		return self::get_user_meta($user_id,'unreads',$force);
	}
	public static function remove_list($user_id,$receiver_id){
		$lists = self::get_lists($user_id,true);
		if(empty($lists))
			return false;
		$key = array_search($receiver_id,$lists);
		if($key === false)
			return false;
		unset($lists[$key]);
		self::update_user_meta($user_id,'lists',$lists);
		return $lists;
	}
	public static function add_list($user_id,$receiver_id){
		$lists = self::get_lists($user_id,true);
		if(!$lists){
			$lists = [$receiver_id];
		}else{
			if(in_array($receiver_id,$lists))
				return false;
			$lists[] = $receiver_id;
		}
		self::update_user_meta($user_id,'lists',$lists);
		return $lists;
	}
	public static function get_lists($user_id,$force = false){
		return self::get_user_meta($user_id,'lists',$force);
	}
	public static function is_unread($user_id,$unread_user_id){
		$unreads = self::get_unreads($user_id);
		return is_array($unreads) && in_array($unread_user_id,$unreads);
	}
	public static function get_unread_count($user_id,$force = false){
		return count(self::get_unreads($user_id,$force));
	}
	public static function add_unread($user_id,$unread_user_id){
		$unreads = self::get_unreads($user_id,true);
		if(!$unreads){
			$unreads = [$unread_user_id];
		}else{
			if(in_array($unread_user_id,$unreads))
				return false;
			$unreads[] = $unread_user_id;
		}
		self::update_user_meta($unread_user_id,'unreads',$unreads);
	}
	public static function clear_unreads($user_id){
		self::update_user_meta($user_id,'unreads',[]);
	}
	public static function clear_lists($user_id){
		self::update_user_meta($user_id,'lists',[]);
	}

	public static function insert_pm(array $args){
		$args = array_merge([
			'pm_author' => 0,
			'pm_receiver' => 0,
			'pm_content' => null,
			'pm_date' => current_time('mysql'),
			'pm_date_gmt' => current_time('mysql',1),
			'pm_agent' => $_SERVER['HTTP_USER_AGENT'],
		],$args);

		if(!$args['pm_author'])
			return false;
		if(!$args['pm_receiver'])
			return false;
		if(!$args['pm_content'])
			return false;
			
		global $wpdb;
		$wpdb->insert(
			self::$table,
			[
				'pm_author' 	=> $args['pm_author'],
				'pm_receiver' 	=> $args['pm_receiver'],
				'pm_content' 	=> $args['pm_content'],
				'pm_date' 		=> $args['pm_date'],
				'pm_date_gmt' 	=> $args['pm_date_gmt'],
				'pm_agent' 		=> $args['pm_agent'],
			],
			[
				'%d',	/** pm_author */
				'%d',	/** pm_receiver */
				'%s',	/** pm_content */
				'%s',	/** pm_date */
				'%s',	/** pm_date_gmt */
				'%s'	/** pm_agent */
			]
		);
		$pm_id = $wpdb->insert_id;
		if($pm_id){
			self::setup_pmdata(self::get_pm($pm_id));
			
			self::update_timestamp($args['pm_receiver']);
			self::set_latest_pm_id($args['pm_receiver'],$pm_id);
			
			self::update_timestamp($args['pm_author']);
			self::set_latest_pm_id($args['pm_author'],$pm_id);

			/** add unread */
			self::add_unread($args['pm_author'],$args['pm_receiver']);
		}
		return $pm_id;
	}
	/**
	 * setup private message data to cache
	 *
	 * @param object $pm
	 * @version 1.0.0
	 */
	public static function setup_pmdata($pm){
		if(!wp_cache_get("pm:$pm->pm_id",__CLASS__))
			wp_cache_set("pm:$pm->pm_id",$pm,__CLASS__,self::$cache_expire);
	}
	/**
	 * get a private message
	 *
	 * @param int $id PM id 
	 * @return null/object
	 * @version 1.0.0
	 */
	public static function get_pm($id){
		$cache = wp_cache_get("pm:$id",__CLASS__);
		if($cache)
			return $cache;
		global $wpdb;
		$cache = $wpdb->get_row($wpdb->prepare(
			"
			SELECT * FROM " . self::$table . "
			WHERE pm_id = %d
			",
			$id
		));
		if(!$cache)
			return null;
		wp_cache_set("pm:$id",$cache,__CLASS__,self::$cache_expire);
		return $cache;
	}
	public static function get_pms(array $args){
		$args = array_merge([
			'id' => null,
			'author' => null,
			'dialog_in' => [],
			'receiver' => null,
			'posts_per_page' => 0,
			'paged' => 1,
		],$args);
		/**
		 * check id value
		 */
		if($args['id'] !== null)
			return self::get_pm($args['id']);

		global $wpdb;

		/** where */
		$where = '';
		
		/** paged */
		if($args['paged'] < 1)
			$args['paged'] = 1;
			
		/** limit */
		if($args['posts_per_page'] == 0){
			$limit = null;
		}else{
			$limit = 'limit ' . ($args['paged'] - 1) . ',' . $args['posts_per_page'];
		}

		/**
		 * check author or
		 */
		if(!empty($args['dialog_in'])){
			
			$opposites = implode(',',array_map('absint',$args['dialog_in']['opposite']));

			$me = abs($args['dialog_in']['me']);
			$where .= " 
				AND (
					(pm_receiver IN ($opposites) AND pm_author = $me) OR 
					(pm_author IN ($opposites) AND pm_receiver = $me) 
				)";
		/**
		 * check if appoint author and receiver
		 */
		}else if($args['author'] !== null && $args['receiver'] !== null){
			$where .= sprintf(
				' AND pm_author = %d AND pm_receiver = %d ',
				$args['author'],
				$args['receiver']
			);
		/**
		 * check if only appoint author
		 */
		}else if($args['author'] !== null && $args['receiver'] === null){
			$where .= sprintf(
				' AND pm_author = %d ',
				$args['author']
			);
		/**
		 * check if only appoint receiver
		 */
		}else if($args['receiver'] !== null && $args['author'] === null){
			$where .= sprintf(
				' AND pm_receiver = %d ',
				$args['receiver']
			);
		}
		//var_dump($where);die;
		$results = $wpdb->get_results(
			"
			SELECT * FROM " . self::$table . "
			WHERE 
				1 = 1 
				$where 
			$limit
			"
		);
		return $results;
	}
	public static function update_timestamp($user_id){
		$current_time = current_time('timestamp');
		wp_cache_set("timestamp:$user_id",$current_time,__CLASS__,self::$cache_expire);
		return $current_time;
	}
	public static function get_timestamp($user_id){
		$timestamp = wp_cache_get("timestamp:$user_id",__CLASS__,true);
		if(!$timestamp)
			$timestamp = self::update_timestamp($user_id);
		return $timestamp;
	}
	public static function get_histories($user_id){
		$timestamp = self::get_timestamp($user_id);
		$cache_id = "histories:$user_id:$timestamp";
		$histories = wp_cache_get($cache_id,__CLASS__,true);
		if(!empty($histories)){
			return $histories;
		}
		$users = self::get_lists($user_id);
		if(!$users)
			return false;

		$pms = self::get_pms([
			'dialog_in' => [
				'opposite' => $users,
				'me' => $user_id,
			]
		]);
		if(!$pms)
			return false;
			
		$histories = [];
		foreach($pms as $pm){
			if($user_id == $pm->pm_author){
				if(!isset($histories[$pm->pm_receiver]))
					$histories[$pm->pm_receiver] = [];
				$histories[$pm->pm_receiver][$pm->pm_id] = $pm;
			}else{
				if(!isset($histories[$pm->pm_author]))
					$histories[$pm->pm_author] = [];
				$histories[$pm->pm_author][$pm->pm_id] = $pm;
			}
		}
		wp_cache_set($cache_id,$histories,__CLASS__,self::$cache_expire);
		return $histories;
	}
	public static function the_tabs(){
		$current_user_id = theme_cache::get_current_user_id();
		$pm_lists = self::get_lists($current_user_id);
		
		if(!$pm_lists)
			return false;
			
		foreach($pm_lists as $user_id){ 
			?>
			<a id="pm-tab-<?= theme_custom_pm::get_niceid($user_id);?>" href="javascript:;" data-uid="<?= theme_custom_pm::get_niceid($user_id);?>" class="<?= self::is_unread($current_user_id,$user_id) ? 'new-msg' : null;?>">
				<img src="<?= get_avatar_url($user_id);?>" alt="<?= ___('Avatar');?>" class="avatar" width="24" height="24"> 
				<span class="author"><?= theme_cache::get_the_author_meta('display_name',$user_id);?></span>
				<b class="close">&times;</b>
			</a>
		<?php 
		}
	}
	public static function the_dialogs(){
		$current_user_id = theme_cache::get_current_user_id();
		$pm_lists = self::get_lists($current_user_id);
		$history_users = theme_custom_pm::get_histories($current_user_id);
		$dialog_histories = [];
		
		if(empty($pm_lists))
			return false;
			
		foreach($pm_lists as $user_id){
			if($history_users && isset($history_users[$user_id])){
				foreach($history_users[$user_id] as $history){
					if($history->pm_author == $user_id || $history->pm_receiver == $user_id){
						if(!isset($dialog_histories[$user_id]))
							$dialog_histories[$user_id] = [
								$history->pm_id => $history
							];
						$dialog_histories[$user_id][$history->pm_id] = $history;
					}
				}
			}
			/** sort */
			foreach($pm_lists as $v){
				if(isset($dialog_histories[$v]))
					ksort($dialog_histories[$v]);
			}
		}
		foreach($pm_lists as $user_id){
		?>
<form action="javascript:;" id="pm-dialog-<?= self::get_niceid($user_id);?>" class="pm-dialog">
	<div class="form-group pm-dialog-list">
		<?php 
		if(isset($history_users[$user_id])){
			foreach($dialog_histories[$user_id] as $history){
				$name = $current_user_id == $history->pm_author ? ___('Me') : theme_cache::get_the_author_meta('display_name',$user_id);
				?>
				<section class="pm-dialog-<?= $current_user_id == $history->pm_author ? 'me' : 'sender' ;?>">
					<div class="pm-dialog-bg">
						<h4>
							<span class="name"><?= $name;?></span> 
							<span class="date"><?= date('Y/m/d H:i:s',strtotime($history->pm_date));?></span>
						</h4>
						<div class="media-content">
							<?= $history->pm_content;?>
						</div>
					</div>
				</section>
				<?php
			}/** end dialog loop */
		}/** end if histories */
		?>
	</div>
	<div class="form-group">
		<input type="text" id="pm-dialog-content-<?= self::get_niceid($user_id);?>" name="content" class="pm-dialog-conteng form-control" placeholder="<?= ___('Enter to send P.M.');?>" required title="<?= ___('P.M. content');?>">
	</div>
	<div class="form-group">
		<button class="btn btn-success btn-block" type="submit"><i class="fa fa-check"></i>&nbsp;<?= ___('Send P.M.');?></button>
	</div>
</form>
		<?php
		}
	}
	public static function cache_request(array $alias = []){
		if(isset($_GET[__CLASS__]) && $_GET[__CLASS__] == 1){
			$alias[__CLASS__] = [
				'timestamp' => self::get_timestamp(theme_cache::get_current_user_id())
			];
		}
		return $alias;
	}
	public static function js_cache_request(array $alias = []){
		if(self::is_page()){
			$alias[__CLASS__] = 1;
		}
		return $alias;
	}
	public static function frontend_seajs_alias(array $alias = []){
		if(self::is_page()){
			$alias[__CLASS__] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		?>
		seajs.use('<?= __CLASS__;?>',function(m){
			m.config.lang.M01 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.M02 = '<?= ___('Enter to send P.M.');?>';
			m.config.lang.M03 = '<?= ___('P.M. content');?>';
			m.config.lang.M04 = '<?= ___('Send P.M.');?>';
			m.config.lang.M05 = '<?= ___('Hello, I am %name%, welcome to chat with me what do you want.');?>';
			m.config.lang.M06 = '<?= ___('P.M. is sending, please wait...');?>';
			m.config.lang.M07 = '<?= ___('Me');?>';
			m.config.lang.E01 = '<?= ___('Sorry, server is busy now, can not respond your request, please try again later.');?>';
	
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => __CLASS__,
			]);?>';
			m.config.my_uid = <?= self::get_niceid(theme_cache::get_current_user_id());?>;
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			__CLASS__,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}