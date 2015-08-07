<?php
/**
 * @version 1.0.0
 */
//add_filter('theme_includes',function($fns){
//	$fns[] = 'theme_custom_pm::init';
//	return $fns;
//});
class theme_custom_pm{
	public static $iden = 'theme_custom_pm';
	public static $page_slug = 'account';
	public static $metas = [];
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
			theme_options::set_options(self::$iden,[
				'db-version' => self::$db_version,
			]);
		}
		
		add_filter('wp_title',				__CLASS__ . '::wp_title',10,2);
		
		add_filter('wp_ajax_' . self::$iden, __CLASS__ . '::process');
		
		add_action('wp_enqueue_scripts', 	__CLASS__ . '::frontend_css');
		add_filter('frontend_seajs_alias', __CLASS__ . '::frontend_seajs_alias');
		add_action('frontend_seajs_use', __CLASS__ . '::frontend_seajs_use');
		
		foreach(self::get_tabs() as $k => $v){
			$nav_fn = 'filter_nav_' . $k; 
			add_filter('account_navs',__CLASS__ . "::$nav_fn",$v['filter_priority']);
		}

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
	public static function get_db_version(){
		return self::get_options('db-version');
	}
	public static function filter_nav_pm($navs){
		$navs['pm'] = '<a href="' . esc_url(self::get_tabs('pm')['url']) . '">
			<i class="fa fa-' . self::get_tabs('pm')['icon'] . ' fa-fw"></i> 
			' . self::get_tabs('pm')['text'] . '
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
	public static function options_default(array $opts = []){
		
	}
	public static function options_save(array $opts = []){
		
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
							<?php if(isset($_GET[self::$iden])){ ?>
								<div id="<?= self::$iden;?>-tip" calss="page-tip"><?= status_tip('success',___('Database tabble has been created.'));?></div>
							<?php } ?>
							<a id="<?= self::$iden;?>-create-table" href="javascript:;"><?= ___('Create database table');?></a>
							<input type="hidden" name="<?= self::$iden;?>[db-version]" value="<?= self::get_db_version() ?self::get_db_version() : self::$db_version;?>">
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
			$caches = theme_options::get_options(self::$iden);
		if($key)
			return isset($caches[$key]) ? $caches[$key] : false;
		return $caches[$key];
	}
	public static function check_uid( &$uid ){
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
				header('location: ' . theme_options::get_url() . '&' . self::$iden);
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

				die(theme_features::json_format([
					'status' => 'success',
					'name' => esc_html($user->display_name),
					'avatar' => get_avatar_url($user->ID),
					'msg' => ___('User data loaded, you can send P.M. now.'),
				]));
			/**
			 * send
			 */
			case 'send':
				/** nonce */
				theme_features::check_nonce();
				
				$receiver_id = isset($_REQUEST['receiver-id']) && is_numeric($_REQUEST['receiver-id']) ? $_REQUEST['receiver-id'] : false;
			
				$receiver = self::check_uid($receiver_id);

				/** check content */
				$content = isset($_REQUEST['content']) && is_string($_REQUEST['content']) ? trim($_REQUEST['content']) : false;
				if($content != '')
					$content = fliter_script(strip_tags($content,'<a><b><strong><em><i><del>'));
				if($content == '')
					die(theme_features::json_format([
						'status' => 'error',
						'code' => 'empty_content',
						'msg' => ___('Sorry, message content is null, please try again.'),
					]));

				/** pass */
				$pm_id = self::insert_pm([
					'pm_author' => theme_cache::get_current_user_id(),
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
				
				$receiver_id = theme_cache::get_current_user_id();

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
				for($i = 0;$i < 45; ++$i){
					/** have new pm */
					if(self::get_histories_timestamp($receiver_id) <= $client_timestamp){
						sleep(1);
						continue;
					}
						
					/** have new pm, output latest pm */
					$latest_pm = self::get_latest_pm($receiver_id);
					die(theme_features::json_format([
						'status' => 'success',
						'pm' => [
							'pm_receiver' => self::get_niceid($latest_pm->pm_receiver),
							'pm_author' => self::get_niceid($latest_pm->pm_author),
							'pm_date' => current_time('Y/m/d H:i:s'),
							'pm_content' => $latest_pm->pm_content,
						],
						'msg' => ___('Sorry, type param is invaild.'),
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
	public static function get_latest_pm($receiver_id){
		$latest_pm_id = wp_cache_get("latest-pm-id:$receiver_id",self::$iden);
		if($latest_pm_id)
			return $latest_pm_id;
		$latest_pm = self::get_pms([
			'posts_per_page' => 1,
			'receiver' => $receiver_id,
		]);
		if(!$latest_pm)
			$latest_pm_id = false;
		setup_pmdata($latest_pm);
		$latest_pm_id = $latest_pm->pm_id;
		
		wp_cache_set("latest-pm-id:$receiver_id",$latest_pm_id,self::$iden,self::$cache_expire);
		
		return $latest_pm_id;
	}
	public static function get_user_meta($user_id,$key = null,$force = false){
		if($force || !isset(self::$metas[$user_id]))
			self::$metas[$user_id] = get_user_meta($user_id,self::$iden,true);
		if($key)
			return isset(self::$metas[$user_id][$key]) ? self::$metas[$user_id][$key] : false;
		return self::$metas[$user_id];
	}
	public static function update_user_meta($user_id,$key = null,$data){
		if(!isset(self::$metas[$user_id]))
			self::$metas[$user_id] = [];
		self::$metas[$user_id][$key] = $data;
		update_user_meta($user_id,self::$iden,self::$metas[$user_id]);
	}
	public static function get_unreads($user_id){
		return self::get_user_meta($user_id,'unreads');
	}
	public static function get_lists($user_id){
		return self::get_user_meta($user_id,'lists');
	}
	public static function is_unread($user_id,$pm_id){
		$unreads = self::get_unreads($user_id);
		return is_array($unread) && in_array($pm_id,$unread);
	}
	public static function set_unread_pm($user_id,$pm_id){
		$unreads = self::get_unreads($user_id);
		if(!$unreads)
			$unreads = [$pm_id];
		
		if(!in_array($pm_id,$unreads)){
			$unreads[] = $pm_id;
			self::update_user_meta($user_id,'unreads',$unreads);
		}
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
		if($args['pm_content'])
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
			self::update_histories_timestamp($args['pm_receiver']);
			self::update_histories_timestamp($args['pm_author']);
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
		if(!wp_cache_get($id,'pm'))
			wp_cache_set($id,$pm,'pm');
	}
	/**
	 * get a private message
	 *
	 * @param int $id PM id 
	 * @return null/object
	 * @version 1.0.0
	 */
	public static function get_pm($id){
		$cache = wp_cache_get("pm:$id",self::$iden);
		if($cache)
			return $cache;
		global $wpdb;
		$cache = $wpdb->get_row($wpdb->prepare(
			"
			SELECT * FROM $wpdb->pm
			WHERE pm_id = %d
			",
			$id
		));
		if(!$cache)
			return null;
		wp_cache_set("pm:$id",$cache,self::$iden,self::$cache_expire);
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
			$limit = ($args['paged'] - 1) . ',' . $args['posts_per_page'];
		}

		/**
		 * check author or
		 */
		if(!empty($args['dialog_in'])){
			
			$opposites = implode(',',array_map('absint',$args['dialog_in']['opposite']));
			
			$where .= sprintf(
				" 
				AND 
					(
					(pm_receiver IN (%1$s) AND pm_author = %2$d) OR 
					(pm_author IN (%1$s) AND pm_receiver = %2$d) 
					)
				",
				$opposites,
				$args['dialog_in']['me']
			);
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
		$results = $wpdb->get_results($wpdb->prepare(
			"
			SELECT * FROM $wpdb->pm
			WHERE 
				1 = 1 
				$where 
			$limit
			"
		));
		return $results;
	}
	public static function update_histories_timestamp($uer_id){
		wp_cache_set("histories:timestamp:$user_id",$_SERVER['REQUEST_TIME'],self::$iden,self::$cache_expire);
		return $_SERVER['REQUEST_TIME'];
	}
	public static function get_histories_timestamp($user_id){
		return wp_cache_get("histories:timestamp:$user_id",self::$iden);
	}

	public static function get_histories($user_id){
		$cache_timestamp = self::get_histories_timestamp($user_id);
		$histories = wp_cache_get("histories:$user_id",self::$iden);
		if(!empty($histories)){
			return $histories;
		}
		$users = self::get_list_userss($user_id);
		if(!$users)
			return false;

		$current_user_id = theme_cache::get_current_user_id();
		$pms = self::get_pms([
			'dialog_in' => [
				'opposite' => $users,
				'me' => $current_user_id,
			]
		]);
		if(!$pms)
			return false;
			
		$histories = [];
		foreach($pms as $pm){
			//$pm->pm_author = self::get_niceid($pm->pm_author);
			//$pm->pm_receiver = self::get_niceid($pm->pm_receiver);
			if($current_user_id == $pm->pm_author){
				if(!isset($histories[$pm->pm_receiver]))
					$histories[$pm->pm_receiver] = [];
				$histories[$pm->pm_receiver][$pm->pm_id] = $pm;
			}else{
				if(!isset($histories[$pm->pm_author]))
					$histories[$pm->pm_author] = [];
				$histories[$pm->pm_author][$pm->pm_id] = $pm;
			}
		}
		wp_cache_set("histories:$user_id",$histories,self::$iden,self::$cache_expire);
		return $histories;
	}
	public static function get_list_users($user_id){
		return self::get_user_meta($uer_id,'lists');
	}
	public static function cache_request(array $alias = []){
		if(isset($_GET[self::$iden]) && $_GET[self::$iden] == 1){
			$alias[self::$iden] = [
				'lists' => self::get_histories()
			];
		}
		return $alias;
	}
	public static function js_cache_request(array $alias = []){
		if(self::is_page()){
			$alias[self::$iden] = 1;
		}
		return $alias;
	}
	public static function frontend_seajs_alias($alias){
		if(self::is_page()){
			$alias[self::$iden] = theme_features::get_theme_includes_js(__DIR__);
		}
		return $alias;
	}
	public static function frontend_seajs_use(){
		if(!self::is_page()) 
			return false;
		?>
		seajs.use('<?= self::$iden;?>',function(m){
			m.config.lang.M01 = '<?= ___('Loading, please wait...');?>';
			m.config.lang.E01 = '<?= ___('Sorry, server is busy now, can not respond your request, please try again later.');?>';
			m.config.process_url = '<?= theme_features::get_process_url([
				'action' => self::$iden,
			]);?>';
			
			m.init();
		});
		<?php
	}
	public static function frontend_css(){
		if(!self::is_page()) 
			return false;
			
		wp_enqueue_style(
			self::$iden,
			theme_features::get_theme_includes_css(__DIR__),
			'frontend',
			theme_file_timestamp::get_timestamp()
		);
	}
}