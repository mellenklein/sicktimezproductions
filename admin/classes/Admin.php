<?php
class Admin {

	public $db;
	public $id = 0;
	public $data;
	public $whoami;
	public $salt = 'ag3ntv!ll@ge';

	public function __construct($user=FALSE, $pass=FALSE, $keep_login_days=0)
	{
		$this->db = Database::getInstance();
		$this->whoami = $_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'];

		$this->check_admin_tables();

		$result = $this->login($user, $pass);

		if($result == FALSE){
			$_SESSION['admin'] = array('id' => 0, 'whoami' => '', 'lastaccess' => '');
		}
    else{
			$_SESSION['admin'] = array(
				'id' => $this->id,
				'whoami' => $this->whoami,
				'lastaccess' => time()
			);
			$this->get_user_nav();
		}
		return true;
	}

  private function login($user=FALSE, $pass=FALSE){

    // Check for valid session
    if( isset($_SESSION['admin']['id']) && $_SESSION['admin']['id'] != 0 ){
      if( time() <= ($_SESSION['admin']['lastaccess'] + (60 * 60)) ){
        if( $_SESSION['admin']['whoami'] == $this->whoami ){
          $this->id = $_SESSION['admin']['id'];
        }
      }
    }


    if($this->id != 0){
			// login from id in session/cookie
			$sql = "SELECT * FROM admins WHERE id=".$this->id." LIMIT 1";
			$rows = $this->db->fetch($sql);
			if(!empty($rows)){
				$this->data = $rows[0];
			}
    }
		else{
			// login from submitted user/pass
      $sql = "SELECT * FROM admins WHERE username=? LIMIT 1";
			$rows = $this->db->fetch($sql, array($user));
			if(!empty($rows)){
        $hash = $this->salt_pw($pass, '');
        if($hash == $rows[0]['wordpass']){
          $this->data = $rows[0];
        }
      }
    }

    if(!empty($this->data)){
      $this->id = $this->data['id'];
      return TRUE;
    }
    else{
      return FALSE;
    }

	}


	public function logout()
	{
		$_SESSION['admin'] = array('id' => 0, 'whoami' => '', 'lastaccess' => '');
		$this->data = array();
	}

	public function generate_salt()
	{
		$range_start = 33;
		$range_end   = 122;
		$salt = '';
		$strlen = 8;

		for($i = 0; $i < $strlen; $i++) {
		  $ascii_no = rand($range_start ,$range_end);
		  $salt .= chr($ascii_no);
		}

		return $salt;
	}

	public function salt_pw($password, $salt)
	{
		$salty = substr($password, 0 , 1).$salt.substr($password, 1);
		return hash('sha1', $salty);
	}

	private function generate_cookie( $id, $expiration )
	{
		$key = hash_hmac('md5', $id . $expiration, $this->salt);
		$hash = hash_hmac('md5', $id . $expiration, $key);

		$cookie = $id . '|' . $expiration . '|' . $hash;

		return $cookie;
	}


	private function verify_cookie()
	{
		if (empty($_COOKIE['auth'])){
		  return 0;
		}

		list($id, $expiration, $hmac) = explode('|', $_COOKIE['auth']);

		if ($expiration < time()){
		  return 0;
		}

		$key = hash_hmac('md5', $id . $expiration, $this->salt);
		$hash = hash_hmac('md5', $id . $expiration, $key);

		if ($hmac != $hash){
		  return 0;
		}

		return $id;
	}
	public function get_controller_access($ctrl)
	{
		$access = array('read'=>0, 'edit'=>0);
		if ($this->data['is_super_admin'] == '1' || $this->data['is_fox_admin'] != 0 ) {
			$access = array('read'=>1, 'edit'=>1);
		}
		elseif ($this->id) {
			$sql = "SELECT * FROM admins_access WHERE admin_id=? AND controller=?";
			$params = array($this->id, $ctrl);
			$results = $this->db->fetch($sql, $params);
			if (!empty($results[0])) {
				$access = array('read'=>1, 'edit'=>$results[0]['allow_edit']);
			}
		}
		return $access;
	}
	/*
	*
	* executeFirewall()
	* Takes in ARRAY()
	* 'ctrl'=>The controller file slug the user is trying to access.  This should match one of the files in admin/ controllers and should be in the db table "admin_access"
	*
	*
	*
	*/
	public function createFirewall($params){

		//define params
		$ctrl = $params['ctrl'];

		//create the firewall
		$sql = "SELECT * FROM cms_tools WHERE controller = ?";
		$controllers = $this->db->fetch($sql, array($ctrl));
		if(empty($controllers)){
			echo 'No firewall found for contoller '.$ctrl;
			die();
		}
		$controller = $controllers[0];
		$firewall = json_decode($controller['firewall'], true);


		//next let's figure out the user's access level for this controller
		$access = $this->get_controller_access( $ctrl );

		//now, lets create the firewall for all the routes
		foreach($firewall as $k=>$v){
			if($access[$v]){
				$this->firewall[$k] = true;
			} else{
				$this->firewall[$k] = false;
			}
		}

	}
	public function get_user_nav() {
		$params = array();

		if ($this->data['is_super_admin'] == '1') {
			$sql = "SELECT t.*, c.title AS cat_title FROM cms_tools t, cms_categories c WHERE t.cms_category_id = c.id ORDER BY c.title, t.sort, t.title";
		} else {
			$sql = "
				SELECT t.*, c.title AS cat_title FROM cms_tools t, admins_access a, cms_categories c
				WHERE t.controller = a.controller AND a.admin_id = ? AND t.cms_category_id = c.id ORDER BY c.title, t.sort, t.title";
			$params[] = $this->id;
		}
		$this->nav = $this->db->fetch($sql, $params);
	}

	public function checkAccess($ctrl, $action, $redirect_url){

		$this->createFirewall(array('ctrl'=>$ctrl));
		if (!$this->firewall[$action]) {
			//user should not be here, but first let's see if they have access to view the list
			if ($this->firewall['list']) {
				$action = 'list';
				set_message('Sorry, that page does not exist', 'fail');
			} else{
				header('location:'.$redirect_url);
				set_message('Sorry, that page does not exist', 'fail');
				exit();
			}

		}
		//action has been modified. return it
		return $action;
	}

	public function check_admin_tables() {
		// Please be sure to update this with any changes to admin table(s) structure
		$sql = "SELECT 1 FROM admins LIMIT 1";
		if(!$this->db->query($sql, array('table_exists' => true))) {
			$create_sql = "CREATE TABLE IF NOT EXISTS `admins` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `username` varchar(64) NOT NULL,
								  `wordpass` varchar(64) NOT NULL,
								  `first_name` varchar(255) NOT NULL,
								  `last_name` varchar(255) NOT NULL DEFAULT '',
								  `is_super_admin` tinyint(1) NOT NULL,
								  `is_fox_admin` int(11) NOT NULL DEFAULT '0',
								  PRIMARY KEY (`id`)
								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
			$this->db->query($create_sql, array());
			$this->db->insert("INSERT INTO `admins` (`id`, `username`, `wordpass`, `first_name`, `last_name`, `is_super_admin`, `is_fox_admin`) VALUES (1, 'foxfuel', SHA1('f0xfu3l'), 'FoxFuel', 'Creative', 1, 1)", array());
		}
		$sql = "SELECT 1 FROM admins_access LIMIT 1";
		if(!$this->db->query($sql, array('table_exists' => true))) {
			$create_sql = "CREATE TABLE IF NOT EXISTS `admins_access` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `admin_id` int(11) NOT NULL,
								  `controller` varchar(100) NOT NULL,
								  `allow_edit` tinyint(4) NOT NULL DEFAULT '0',
								  PRIMARY KEY (`id`)
								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
			$this->db->query($create_sql, array());
		}
		$sql = "SELECT 1 FROM cms_tools LIMIT 1";
		if(!$this->db->query($sql, array('table_exists' => true))) {
			$create_sql = "CREATE TABLE IF NOT EXISTS `cms_tools` (
									`id` int(11) NOT NULL AUTO_INCREMENT,
									`controller` varchar(100) NOT NULL,
									`title` varchar(100) NOT NULL,
									`firewall` text NOT NULL,
									`sort` int(11) NOT NULL,
									`cms_category_id` int(11) NOT NULL DEFAULT '1',
									PRIMARY KEY (`id`)
								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
			$this->db->query($create_sql, array());
			$this->db->insert('INSERT INTO `cms_tools` (`id`, `controller`, `title`, `firewall`, `sort`, `cms_category_id`) VALUES
									(20, \'site-info\', \'Site Information\', \'    {\r\n    "update":"edit",\r\n    "delete":"edit",\r\n    "add":"edit",\r\n    "edit":"edit",\r\n    "list":"read",\r\n    "export":"read",\r\n    "view":"read"\r\n    }\', 1, 1);', array());
		}
		$sql = "SELECT 1 FROM cms_categories LIMIT 1";
		if(!$this->db->query($sql, array('table_exists' => true))) {
			$create_sql = "CREATE TABLE IF NOT EXISTS `cms_categories` (
									`id` int(11) NOT NULL AUTO_INCREMENT,
									`title` varchar(255) NOT NULL,
									PRIMARY KEY (`id`)
								) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;";
			$this->db->query($create_sql, array());
			$this->db->insert("INSERT INTO `cms_categories` (`id`, `title`) VALUES (1, 'General');", array());
		}
	}
}
?>
