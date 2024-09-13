<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Login class
 *
 * Copyright (c) CIBoard <www.ciboard.co.kr>
 *
 * @author CIBoard (develop@ciboard.co.kr)
 */

/**
 * 로그인 페이지와 관련된 controller 입니다.
 */
class Install extends CI_Controller
{

	/**
	 * 모델을 로딩합니다
	 */
	protected $models = array();


	function __construct()
	{
		parent::__construct();

		$this->load->helper(array('array', 'form'));

		ini_set('display_errors', 0);
		if (version_compare(PHP_VERSION, '5.3', '>=')) {
			error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
		} else {
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
		}
	}


	/**
	 * 인스톨 페이지입니다
	 */
	public function index()
	{
		if (config_item('install_ip') !== 'all' AND ( config_item('install_ip') === '' OR config_item('install_ip') !== $this->input->ip_address())) {
			$header = array();
			$header['install_step'] = 0;
			$this->load->view('install/header', $header);
			$this->load->view('install/step0');
			$this->load->view('install/footer');
			return;
		}

		if ($this->_check_installed()) {
			alert('이미 데이터베이스에 config 테이블이 존재하여 설치를 진행하지 않습니다');
		}
		redirect('install/step1');
	}


	public function step1()
	{
		if (config_item('install_ip') !== 'all' AND ( config_item('install_ip') === '' OR config_item('install_ip') !== $this->input->ip_address())) {
			$header = array();
			$header['install_step'] = 0;
			$this->load->view('install/header', $header);
			$this->load->view('install/step0');
			$this->load->view('install/footer');
			return;
		}

		if ($this->_check_installed()) {
			alert('이미 데이터베이스에 config 테이블이 존재하여 설치를 진행하지 않습니다');
		}

		$header = array();
		$header['install_step'] = 1;
		$this->load->view('install/header', $header);
		$this->load->view('install/step1');
		$this->load->view('install/footer');
	}


	public function step2()
	{
		if (config_item('install_ip') !== 'all' AND ( config_item('install_ip') === '' OR config_item('install_ip') !== $this->input->ip_address())) {
			$header = array();
			$header['install_step'] = 0;
			$this->load->view('install/header', $header);
			$this->load->view('install/step0');
			$this->load->view('install/footer');
			return;
		}

		if ($this->_check_installed()) {
			alert('이미 데이터베이스에 config 테이블이 존재하여 설치를 진행하지 않습니다');
		}
		if ( ! $this->input->post('agree')) {
			alert('약관에 동의하신 후에 설치가 가능합니다', site_url('install'));
			return;
		}

		$view = array();
		$header = array();
		$message = '';

		$install_avaiable = true;
		$view['title1'] = 'PHP VERSION';
		$phpversion = phpversion();
		if (version_compare($phpversion, '5.3.0') >= 0) {
			$view['content1'] = '<span class="bold color_blue">' . $phpversion . '</span>';
		} else {
			$view['content1'] = '<span class="bold color_red">' . $phpversion . '</span>';
			$install_avaiable = false;
			$message .= 'PHP Version 을 5.3.0 이상으로 업그레이드 한 후에 설치가 가능합니다<br />';
		}
		$view['desc1'] = 'PHP version 5.3.0 or newer is recommended.';

		$view['title2'] = 'GD Support';
		if (extension_loaded('gd') && function_exists('gd_info')) {
			$view['content2'] = '<span class="bold color_blue">지원</span>';
		} else {
			$view['content2'] = '<span class="bold color_red">미지원</span>';
			$install_avaiable = false;
			$message .= 'GD Library 를 설치한 후에 Install 이 가능합니다<br />';
		}

		$view['title3'] = 'XML Support';
		$xml_support = extension_loaded('xml');
		if ($xml_support) {
			$view['content3'] = '<span class="bold color_blue">지원</span>';
		} else {
			$view['content3'] = '<span class="bold color_red">미지원</span>';
			$install_avaiable = false;
			$message .= 'XML Library 를 설치한 후에 Install 이 가능합니다<br />';
		}

		$view['title4'] = 'iconv Support';
		$iconv = function_exists('iconv');
		if ($iconv) {
			$view['content4'] = '<span class="bold color_blue">지원</span>';
		} else {
			$view['content4'] = '<span class="bold color_red">미지원</span>';
			$install_avaiable = false;
			$message .= 'Iconv Library 를 설치한 후에 Install 이 가능합니다<br />';
		}

		$view['title5'] = 'CURL Support';
		$curl = function_exists('curl_version');
		if ($curl) {
			$view['content5'] = '<span class="bold color_blue">지원</span>';
		} else {
			$view['content5'] = '<span class="bold color_red">미지원</span>';
			$install_avaiable = false;
			$message .= 'CURL Extension 을 설치한 후에 Install 이 가능합니다<br />';
		}

		$view['title6'] = 'uploads directory';
		$uploads_dir = config_item('uploads_dir');
		if (is_dir($uploads_dir) === false) {
			$install_avaiable = false;
			$view['content6'] = '<span class="bold color_red">루트 디렉토리에 ' . config_item('uploads_dir') . ' 라는 디렉토리가 존재하지 않습니다</span>';
			$message .= '루트 디렉토리에서 ' . config_item('uploads_dir') . ' 라는 디렉토리를 생성하시고 그 퍼미션을 707 로 변경해주세요<br />';
		} elseif ( ! (is_readable($uploads_dir) && is_writeable($uploads_dir))) {
			$install_avaiable = false;
			$view['content6'] = '<span class="bold color_red">' . config_item('uploads_dir') . ' 디렉토리의 퍼미션이 올바르지 않습니다</span>';
			$message .= config_item('uploads_dir') . ' 라는 디렉토리의 퍼미션을 707 로 변경해주세요<br />';
		} else {
			$view['content6'] = '<span class="bold color_blue"> 정상</span>';
		}

		$view['title7'] = 'cache directory';
		$cache_dir = APPPATH . 'cache';
		if (is_dir($cache_dir) === false) {
			$install_avaiable = false;
			$view['content7'] = '<span class="bold color_red">application 디렉토리에 cache 라는 디렉토리가 존재하지 않습니다</span>';
			$message .= 'application/cache 를 생성해주시고 그 퍼미션을 707 로 변경해주세요<br />';
		} elseif ( ! (is_readable($cache_dir) && is_writeable($cache_dir))) {
			$install_avaiable = false;
			$view['content7'] = '<span class="bold color_red">application/cache 디렉토리의 퍼미션이 올바르지 않습니다</span>';
			$message .= 'application/cache 디렉토리의 퍼미션을 707 로 변경해주세요<br />';
		} else {
			$view['content7'] = '<span class="bold color_blue"> 정상</span>';
		}

		$view['title8'] = 'logs directory';
		$log_dir = APPPATH . 'logs';
		if (is_dir($log_dir) === false) {
			$install_avaiable = false;
			$view['content8'] = '<span class="bold color_red">application 디렉토리에 logs 라는 디렉토리가 존재하지 않습니다</span>';
			$message .= 'application/logs 를 생성해주시고 그 퍼미션을 707 로 변경해주세요<br />';
		} elseif ( ! (is_readable($log_dir) && is_writeable($log_dir))) {
			$install_avaiable = false;
			$view['content8'] = '<span class="bold color_red">application/logs 디렉토리의 퍼미션이 올바르지 않습니다</span>';
			$message .= 'application/logs 디렉토리의 퍼미션을 707 로 변경해주세요<br />';
		} else {
			$view['content8'] = '<span class="bold color_blue"> 정상</span>';
		}

		$view['title9'] = 'migration_enabled';
		$this->config->load('migration');
		if (config_item('migration_enabled') === false) {
			$install_avaiable = false;
			$view['content9'] = '<span class="bold color_red">config[\'migration_enabled\'] 가 활성화되어있지 않습니다</span>';
			$message .= 'application/config/migration.php 안에 config[\'migration_enabled\'] 의 값을 true 로 변경해주세요<br />';
		} else {
			$view['content9'] = '<span class="bold color_blue"> 정상</span>';
		}

		$view['install_avaiable'] = $install_avaiable;
		$view['message'] = $message;

		$header['install_step'] = 2;
		$this->load->view('install/header', $header);
		$this->load->view('install/step2', $view);
		$this->load->view('install/footer');
	}


	public function step3()
	{
		if (config_item('install_ip') !== 'all' AND ( config_item('install_ip') === '' OR config_item('install_ip') !== $this->input->ip_address())) {
			$header = array();
			$header['install_step'] = 0;
			$this->load->view('install/header', $header);
			$this->load->view('install/step0');
			$this->load->view('install/footer');
			return;
		}

		if ($this->_check_installed()) {
			alert('이미 데이터베이스에 config 테이블이 존재하여 설치를 진행하지 않습니다');
		}
		if ( ! $this->input->post('agree')) {
			alert('약관에 동의하신 후에 설치가 가능합니다', site_url('install'));
			return;
		}

		$view = array();
		$header = array();
		$message = '';

		$install_avaiable = true;

		$view['title1'] = 'encryption_key';
		if (config_item('encryption_key')) {
			$view['content1'] = '<span class="bold color_blue">설정완료</span>';
		} else {
			$install_avaiable = false;
			$view['content1'] = '<span class="bold color_red">비어있음</span>';
			$message .= 'application/config/config.php 의 &dollar;config[\'encryption_key\'] 에 내용을 입력해주세요, 현재 그 값이 비어있습니다. 한번 입력하신 값은 변경하지 말아주세요. 패스워드 암호화에 사용됩니다<br />';
		}

		$view['title2'] = 'base_url';
		if (config_item('base_url')) {
			$view['content2'] = '<span class="bold color_blue">설정완료</span>';
		} else {
			$install_avaiable = false;
			$view['content2'] = '<span class="bold color_red">비어있음</span>';
			$message .= 'application/config/config.php 의 &dollar;config[\'base_url\'] 에 현재 사이트 주소를 입력해주세요, 현재 그 값이 비어있습니다<br />';
		}

		$view['install_avaiable'] = $install_avaiable;
		$view['message'] = $message;

		$header['install_step'] = 3;
		$this->load->view('install/header', $header);
		$this->load->view('install/step3', $view);
		$this->load->view('install/footer');
	}


	public function step4()
	{
		if (config_item('install_ip') !== 'all' AND ( config_item('install_ip') === '' OR config_item('install_ip') !== $this->input->ip_address())) {
			$header = array();
			$header['install_step'] = 0;
			$this->load->view('install/header', $header);
			$this->load->view('install/step0');
			$this->load->view('install/footer');
			return;
		}

		if ($this->_check_installed()) {
			alert('이미 데이터베이스에 config 테이블이 존재하여 설치를 진행하지 않습니다');
		}
		if ( ! $this->input->post('agree')) {
			alert('약관에 동의하신 후에 설치가 가능합니다', site_url('install'));
			return;
		}

		$view = array();
		$header = array();
		$message = '';

		include(APPPATH . 'config/database.php');

		$install_avaiable = true;
		$dbinfo = $db['default'];

		$view['title2'] = 'username';
		if ( ! empty($dbinfo['username'])) {
			$view['content2'] = '<span class="bold color_blue">설정완료</span>';
		} else {
			$install_avaiable = false;
			$view['content2'] = '<span class="bold color_red">비어있음</span>';
			$message .= 'application/config/database.php 의 &dollar;db[\'default\'][\'username\'] 에 데이터베이스 정보를 입력해주세요<br />';
		}

		$view['title3'] = 'password';
		if ( ! empty($dbinfo['password'])) {
			$view['content3'] = '<span class="bold color_blue">설정완료</span>';
		} else {
			$view['content3'] = '<span class="bold color_red">비어있음</span>';
		}

		$view['title4'] = 'database';
		if ( ! empty($dbinfo['database'])) {
			$view['content4'] = '<span class="bold color_blue">설정완료</span>';
		} else {
			$install_avaiable = false;
			$view['content4'] = '<span class="bold color_red">비어있음</span>';
			$message .= 'application/config/database.php 의 &dollar;db[\'default\'][\'database\'] 에 데이터베이스 정보를 입력해주세요<br />';
		}
		$view['title5'] = 'db connect';
		$view['content5'] = '';
		if ($install_avaiable) {
			$database = $this->load->database($dbinfo, true);
			$connected = $database->initialize();
			if ($connected) {
				$view['content5'] = '<span class="bold color_blue">데이터베이스 접속 성공</span>';
			} else {
				$install_avaiable = false;
				$view['content5'] = '<span class="bold color_red">데이터베이스 접속 실패</span>';
				$message .= 'application/config/database.php 의 데이터베이스 정보가 올바르게 입력되었는지 확인해주세요<br />';
			}
		}

		$view['install_avaiable'] = $install_avaiable;
		$view['message'] = $message;

		$header['install_step'] = 4;
		$this->load->view('install/header', $header);
		$this->load->view('install/step4', $view);
		$this->load->view('install/footer');
	}


	public function step5()
	{
		if (config_item('install_ip') !== 'all' AND ( config_item('install_ip') === '' OR config_item('install_ip') !== $this->input->ip_address())) {
			$header = array();
			$header['install_step'] = 0;
			$this->load->view('install/header', $header);
			$this->load->view('install/step0');
			$this->load->view('install/footer');
			return;
		}

		if ($this->_check_installed()) {
			alert('이미 데이터베이스에 config 테이블이 존재하여 설치를 진행하지 않습니다');
		}
		if ( ! $this->input->post('agree')) {
			alert('약관에 동의하신 후에 설치가 가능합니다', site_url('install'));
			return;
		}

		$view = array();
		$header = array();

		$config = array();
		$config['mem_userid'] = array(
			'field' => 'mem_userid',
			'label' => 'User ID',
			'rules' => 'trim|required|alphanumunder|min_length[3]|max_length[20]',
		);
		$config['mem_password'] = array(
			'field' => 'mem_password',
			'label' => '패스워드',
			'rules' => 'trim|required|min_length[4]',
		);
		$config['mem_password_re'] = array(
			'field' => 'mem_password_re',
			'label' => '패스워드 확인',
			'rules' => 'trim|required|min_length[4]|matches[mem_password]',
		);
		$config['mem_nickname'] = array(
			'field' => 'mem_nickname',
			'label' => '닉네임',
			'rules' => 'trim|required|min_length[2]|max_length[20]|callback__mem_nickname_check',
		);
		$config['mem_email'] = array(
			'field' => 'mem_email',
			'label' => '이메일',
			'rules' => 'trim|required|valid_email|max_length[50]',
		);
		$config['skin'] = array(
			'field' => 'skin',
			'label' => '스킨',
			'rules' => 'trim|required',
		);
		$form_validation = '';
		if ($this->input->post('mem_userid')) {
			$this->load->library('form_validation');
			$this->form_validation->set_rules($config);
			$form_validation = $this->form_validation->run();
		}
		$this->load->database();
		if ($form_validation) {
			if ($this->_create_tables() === false) {
				alert('설치에 실패하였습니다', site_url('install'));
				return;
			}
			if ($this->_create_pro_tables() === false) {
				alert('설치에 실패하였습니다', site_url('install'));
				return;
			}
			if ($this->_migration_tables() === false) {
				alert('설치에 실패하였습니다', site_url('install'));
				return;
			}
			$this->_insert_init_data();
			$this->_insert_pro_init_data();

			redirect();
		} else {
			$header['install_step'] = 5;
			$this->load->view('install/header', $header);
			$this->load->view('install/step5', $view);
			$this->load->view('install/footer');
		}
	}


	public function _check_installed()
	{
		include(APPPATH . 'config/database.php');
		$dbinfo = $db['default'];

		if (empty($dbinfo['username'])) {
			return false;
		}
		if (empty($dbinfo['password'])) {
			return false;
		}
		if (empty($dbinfo['database'])) {
			return false;
		}
		$database = $this->load->database($dbinfo, true);
		if (empty($database->conn_id)) {
			return false;
		}
		$connected = $database->initialize();
		if ($connected) {
			$this->load->database();
			if ($this->db->table_exists('config')) {
				return true;
			}
		}
		return false;

	}


	public function _create_tables()
	{
		$this->load->database();
		$this->load->dbforge();


		// autologin table
		$this->dbforge->add_field(array(
			'aul_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'aul_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'aul_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'aul_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
		),
		));
		$this->dbforge->add_key('aul_id', true);
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('autologin', true) === false) {
			return false;
		}


		// banner table
		$this->dbforge->add_field(array(
			'ban_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'ban_start_date' => array(
				'type' => 'DATE',
				'null' => true,
			),
			'ban_end_date' => array(
				'type' => 'DATE',
				'null' => true,
			),
			'bng_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'ban_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ban_url' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ban_target' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ban_device' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ban_width' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'ban_height' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'ban_hit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ban_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ban_image' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ban_activated' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'ban_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ban_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('ban_id', true);
		$this->dbforge->add_key(array('bng_name'));
		$this->dbforge->add_key(array('ban_start_date'));
		$this->dbforge->add_key(array('ban_end_date'));
		if ($this->dbforge->create_table('banner', true) === false) {
			return false;
		}


		// banner_click_log table
		$this->dbforge->add_field(array(
			'bcl_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'ban_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => null,
			),
			'bcl_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'bcl_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'bcl_referer' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'bcl_url' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'bcl_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('bcl_id', true);
		$this->dbforge->add_key(array('ban_id'));
		if ($this->dbforge->create_table('banner_click_log', true) === false) {
			return false;
		}


		// banner group table
		$this->dbforge->add_field(array(
			'bng_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'bng_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
		));
		$this->dbforge->add_key('bng_id', true);
		$this->dbforge->add_key(array('bng_name'));
		if ($this->dbforge->create_table('banner_group', true) === false) {
			return false;
		}


		// blame table
		$this->dbforge->add_field(array(
			'bla_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'target_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'bla_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'bla_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('bla_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('target_mem_id');
		$this->dbforge->add_key('target_id');
		$this->dbforge->add_key('brd_id');
		if ($this->dbforge->create_table('blame', true) === false) {
			return false;
		}


		// board table
		$this->dbforge->add_field(array(
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'bgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'brd_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'brd_mobile_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'brd_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'brd_search' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('brd_id', true);
		$this->dbforge->add_key('bgr_id');
		if ($this->dbforge->create_table('board', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'board ADD UNIQUE KEY `brd_key` (`brd_key`)');


		// board_admin table
		$this->dbforge->add_field(array(
			'bam_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('bam_id', true);
		$this->dbforge->add_key('brd_id');
		if ($this->dbforge->create_table('board_admin', true) === false) {
			return false;
		}


		// board_category table
		$this->dbforge->add_field(array(
			'bca_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'bca_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'bca_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'bca_parent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'bca_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('bca_id', true);
		$this->dbforge->add_key('brd_id');
		if ($this->dbforge->create_table('board_category', true) === false) {
			return false;
		}


		// board_group table
		$this->dbforge->add_field(array(
			'bgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'bgr_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'bgr_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'bgr_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('bgr_id', true);
		$this->dbforge->add_key('bgr_order');
		if ($this->dbforge->create_table('board_group', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'board_group ADD UNIQUE KEY `bgr_key` (`bgr_key`)');


		// board_group_admin table
		$this->dbforge->add_field(array(
			'bga_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'bgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('bga_id', true);
		$this->dbforge->add_key('bgr_id');
		if ($this->dbforge->create_table('board_group_admin', true) === false) {
			return false;
		}


		// board_group_meta table
		$this->dbforge->add_field(array(
			'bgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'bgm_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'bgm_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('board_group_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'board_group_meta ADD UNIQUE KEY `bgr_id_bgm_key` (`bgr_id`, `bgm_key`)');


		// board_meta table
		$this->dbforge->add_field(array(
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'bmt_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'bmt_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('board_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'board_meta ADD UNIQUE KEY `brd_id_bmt_key` (`brd_id`, `bmt_key`)');


		// comment table
		$this->dbforge->add_field(array(
			'cmt_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cmt_num' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cmt_reply' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'default' => '',
			),
			'cmt_html' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cmt_secret' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cmt_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cmt_password' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cmt_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cmt_username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cmt_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cmt_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cmt_homepage' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cmt_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cmt_updated_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cmt_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cmt_like' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cmt_dislike' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cmt_blame' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'cmt_device' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'cmt_del' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('cmt_id', true);
		$this->dbforge->add_key(array('post_id', 'cmt_num', 'cmt_reply'));
		if ($this->dbforge->create_table('comment', true) === false) {
			return false;
		}


		// comment_meta table
		$this->dbforge->add_field(array(
			'cmt_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cme_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cme_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('comment_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'comment_meta ADD UNIQUE KEY `cmt_id_cme_key` (`cmt_id`, `cme_key`)');


		// config table
		$this->dbforge->add_field(array(
			'cfg_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cfg_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('config', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'config ADD UNIQUE KEY `cfg_key` (`cfg_key`)');


		// currentvisitor table
		$this->dbforge->add_field(array(
			'cur_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cur_mem_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cur_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cur_page' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cur_url' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cur_referer' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cur_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cur_ip', true);
		if ($this->dbforge->create_table('currentvisitor', true) === false) {
			return false;
		}


		// document table
		$this->dbforge->add_field(array(
			'doc_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'doc_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'doc_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'doc_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'doc_mobile_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'doc_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'doc_layout' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'doc_mobile_layout' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'doc_sidebar' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'doc_mobile_sidebar' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'doc_skin' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'doc_mobile_skin' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'doc_hit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'doc_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'doc_updated_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'doc_updated_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('doc_id', true);
		if ($this->dbforge->create_table('document', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'document ADD UNIQUE KEY `doc_key` (`doc_key`)');


		// editor_image table
		$this->dbforge->add_field(array(
			'eim_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'eim_originname' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'eim_filename' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'eim_filesize' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'eim_width' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'eim_height' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'eim_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'eim_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'eim_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('eim_id', true);
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('editor_image', true) === false) {
			return false;
		}


		// faq table
		$this->dbforge->add_field(array(
			'faq_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'fgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'faq_title' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'faq_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'faq_mobile_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'faq_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'faq_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'faq_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'faq_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('faq_id', true);
		$this->dbforge->add_key('fgr_id');
		if ($this->dbforge->create_table('faq', true) === false) {
			return false;
		}


		// faq_group table
		$this->dbforge->add_field(array(
			'fgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'fgr_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'fgr_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'fgr_layout' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'fgr_mobile_layout' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'fgr_sidebar' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'fgr_mobile_sidebar' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'fgr_skin' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'fgr_mobile_skin' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'fgr_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'fgr_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('fgr_id', true);
		if ($this->dbforge->create_table('faq_group', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'faq_group ADD UNIQUE KEY `fgr_key` (`fgr_key`)');


		// follow table
		$this->dbforge->add_field(array(
			'fol_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'fol_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('fol_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('target_mem_id');
		if ($this->dbforge->create_table('follow', true) === false) {
			return false;
		}


		// like table
		$this->dbforge->add_field(array(
			'lik_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'target_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'lik_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
			),
			'lik_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'lik_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('lik_id', true);
		$this->dbforge->add_key('target_id');
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('target_mem_id');
		if ($this->dbforge->create_table('like', true) === false) {
			return false;
		}


		// member table
		$this->dbforge->add_field(array(
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_password' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_level' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_point' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'mem_homepage' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_birthday' => array(
				'type' => 'CHAR',
				'constraint' => '10',
				'default' => '',
			),
			'mem_sex' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_zipcode' => array(
				'type' => 'VARCHAR',
				'constraint' => '7',
				'default' => '',
			),
			'mem_address1' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_address2' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_address3' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_address4' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_receive_email' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_use_note' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_receive_sms' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_open_profile' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_denied' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_email_cert' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_register_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mem_register_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_lastlogin_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mem_lastlogin_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_is_admin' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_profile_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_adminmemo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_following' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_followed' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_icon' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_photo' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('mem_id', true);
		$this->dbforge->add_key('mem_email');
		$this->dbforge->add_key('mem_lastlogin_datetime');
		$this->dbforge->add_key('mem_register_datetime');
		if ($this->dbforge->create_table('member', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'member ADD UNIQUE KEY `mem_userid` (`mem_userid`)');


		// member_auth_email table
		$this->dbforge->add_field(array(
			'mae_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mae_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mae_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mae_generate_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mae_use_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mae_expired' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('mae_id', true);
		$this->dbforge->add_key(array('mae_key', 'mem_id'));
		if ($this->dbforge->create_table('member_auth_email', true) === false) {
			return false;
		}


		// member_certify table
		$this->dbforge->add_field(array(
			'mce_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mce_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mce_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mce_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mce_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mce_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('mce_type');
		if ($this->dbforge->create_table('member_certify', true) === false) {
			return false;
		}


		// member_dormant table
		$this->dbforge->add_field(array(
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_password' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_level' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_point' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'mem_homepage' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_birthday' => array(
				'type' => 'CHAR',
				'constraint' => '10',
				'default' => '',
			),
			'mem_sex' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_zipcode' => array(
				'type' => 'VARCHAR',
				'constraint' => '7',
				'default' => '',
			),
			'mem_address1' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_address2' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_address3' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_address4' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_receive_email' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_use_note' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_receive_sms' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_open_profile' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_denied' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_email_cert' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_register_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mem_register_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_lastlogin_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mem_lastlogin_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_is_admin' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_profile_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_adminmemo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mem_following' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_followed' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_icon' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_photo' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('mem_email');
		$this->dbforge->add_key('mem_lastlogin_datetime');
		$this->dbforge->add_key('mem_register_datetime');
		if ($this->dbforge->create_table('member_dormant', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'member_dormant ADD UNIQUE KEY `mem_userid` (`mem_userid`)');


		// member_dormant_notify table
		$this->dbforge->add_field(array(
			'mdn_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_register_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mem_lastlogin_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mdn_dormant_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mdn_dormant_notify_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mdn_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('mem_email');
		$this->dbforge->add_key('mem_register_datetime');
		$this->dbforge->add_key('mem_lastlogin_datetime');
		$this->dbforge->add_key('mdn_dormant_datetime');
		$this->dbforge->add_key('mdn_dormant_notify_datetime');
		if ($this->dbforge->create_table('member_dormant_notify', true) === false) {
			return false;
		}


		// member_extra_vars table
		$this->dbforge->add_field(array(
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mev_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mev_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('member_extra_vars', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'member_extra_vars ADD UNIQUE KEY `mem_id_mev_key` (`mem_id`, `mev_key`)');


		// member_group table
		$this->dbforge->add_field(array(
			'mgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mgr_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mgr_is_default' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mgr_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mgr_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'mgr_description' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mgr_id', true);
		$this->dbforge->add_key('mgr_order');
		if ($this->dbforge->create_table('member_group', true) === false) {
			return false;
		}


		// member_group_member table
		$this->dbforge->add_field(array(
			'mgm_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mgr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mgm_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mgm_id', true);
		$this->dbforge->add_key('mgr_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('member_group_member', true) === false) {
			return false;
		}


		// member_level_history table
		$this->dbforge->add_field(array(
			'mlh_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mlh_from' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mlh_to' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mlh_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mlh_reason' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mlh_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('mlh_id', true);
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('member_level_history', true) === false) {
			return false;
		}


		// member_login_log table
		$this->dbforge->add_field(array(
			'mll_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mll_success' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mll_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mll_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mll_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mll_reason' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mll_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mll_url' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'mll_referer' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mll_id', true);
		$this->dbforge->add_key('mll_success');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('member_login_log', true) === false) {
			return false;
		}


		// member_meta table
		$this->dbforge->add_field(array(
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mmt_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mmt_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('member_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'member_meta ADD UNIQUE KEY `mem_id_mmt_key` (`mem_id`, `mmt_key`)');


		// member_nickname table
		$this->dbforge->add_field(array(
			'mni_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mni_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mni_start_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mni_end_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mni_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('mni_nickname');
		if ($this->dbforge->create_table('member_nickname', true) === false) {
			return false;
		}


		// member_register table
		$this->dbforge->add_field(array(
			'mrg_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mrg_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mrg_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'mrg_recommend_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mrg_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mrg_referer' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('mrg_id', true);
		if ($this->dbforge->create_table('member_register', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'member_register ADD UNIQUE KEY `mem_id` (`mem_id`)');


		// member_userid table
		$this->dbforge->add_field(array(
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
			),
			'mem_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_status' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('member_userid', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'member_userid ADD UNIQUE KEY `mem_userid` (`mem_userid`)');


		// menu table
		$this->dbforge->add_field(array(
			'men_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'men_parent' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'men_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'men_link' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'men_target' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'men_desktop' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'men_mobile' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'men_custom' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'men_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('men_id', true);
		if ($this->dbforge->create_table('menu', true) === false) {
			return false;
		}


		// note table
		$this->dbforge->add_field(array(
			'nte_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'send_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'recv_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'nte_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'related_note_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'nte_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'nte_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'nte_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'nte_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'nte_read_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'nte_originname' => array(
					'type' => 'VARCHAR',
					'constraint' => '255',
					'default' => '',
			),
			'nte_filename' => array(
					'type' => 'VARCHAR',
					'constraint' => '255',
					'default' => '',
			),
		));
		$this->dbforge->add_key('nte_id', true);
		$this->dbforge->add_key('send_mem_id');
		$this->dbforge->add_key('recv_mem_id');
		if ($this->dbforge->create_table('note', true) === false) {
			return false;
		}


		// notification table
		$this->dbforge->add_field(array(
			'not_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'not_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'not_content_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'not_message' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'not_url' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'not_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'not_read_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('not_id', true);
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('notification', true) === false) {
			return false;
		}


		// point table
		$this->dbforge->add_field(array(
			'poi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'poi_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'poi_content' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'poi_point' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'poi_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'default' => '',
			),
			'poi_related_id' => array(
				'type' => 'VARCHAR',
				'constraint' => '20',
				'default' => '',
			),
			'poi_action' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('poi_id', true);
		$this->dbforge->add_key(array('mem_id', 'poi_type', 'poi_related_id', 'poi_action'));
		if ($this->dbforge->create_table('point', true) === false) {
			return false;
		}


		// popup table
		$this->dbforge->add_field(array(
			'pop_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'pop_start_date' => array(
				'type' => 'DATE',
				'null' => true,
			),
			'pop_end_date' => array(
				'type' => 'DATE',
				'null' => true,
			),
			'pop_is_center' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_left' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_top' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_width' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_height' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_device' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'pop_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'pop_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'pop_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_disable_hours' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_activated' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_page' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'pop_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'pop_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('pop_id', true);
		$this->dbforge->add_key('pop_start_date');
		$this->dbforge->add_key('pop_end_date');
		if ($this->dbforge->create_table('popup', true) === false) {
			return false;
		}


		// post table
		$this->dbforge->add_field(array(
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_num' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'post_reply' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'post_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'post_category' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'post_userid' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'post_username' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'post_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'post_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'post_homepage' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'post_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'post_password' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'post_updated_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'post_update_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_comment_count' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'post_comment_updated_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'post_link_count' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'post_secret' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_html' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_hide_comment' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_notice' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_receive_email' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_hit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_like' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_dislike' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'post_blame' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'post_device' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'post_file' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_image' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'post_del' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('post_id', true);
		$this->dbforge->add_key(array('post_num', 'post_reply'));
		$this->dbforge->add_key('brd_id');
		$this->dbforge->add_key('post_datetime');
		$this->dbforge->add_key('post_updated_datetime');
		$this->dbforge->add_key('post_comment_updated_datetime');
		if ($this->dbforge->create_table('post', true) === false) {
			return false;
		}


		// post_extra_vars tables
		$this->dbforge->add_field(array(
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pev_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'pev_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('post_extra_vars', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'post_extra_vars ADD UNIQUE KEY `post_id_pev_key` (`post_id`, `pev_key`)');


		// post_file table
		$this->dbforge->add_field(array(
			'pfi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pfi_originname' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'pfi_filename' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'pfi_download' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pfi_filesize' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pfi_width' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'pfi_height' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'pfi_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'pfi_is_image' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'pfi_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'pfi_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('pfi_id', true);
		$this->dbforge->add_key('post_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('post_file', true) === false) {
			return false;
		}


		// post_file_download_log table
		$this->dbforge->add_field(array(
			'pfd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'pfi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pfd_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'pfd_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'pfd_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('pfd_id', true);
		$this->dbforge->add_key('pfi_id');
		$this->dbforge->add_key('post_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('post_file_download_log', true) === false) {
			return false;
		}


		// post_history table
		$this->dbforge->add_field(array(
			'phi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'phi_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'phi_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'phi_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'phi_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'phi_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('phi_id', true);
		$this->dbforge->add_key('post_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('post_history', true) === false) {
			return false;
		}


		// post_link table
		$this->dbforge->add_field(array(
			'pln_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pln_url' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'pln_hit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('pln_id', true);
		$this->dbforge->add_key('post_id');
		if ($this->dbforge->create_table('post_link', true) === false) {
			return false;
		}


		// post_link_click_log table
		$this->dbforge->add_field(array(
			'plc_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'pln_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'plc_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'plc_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'plc_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('plc_id', true);
		$this->dbforge->add_key('pln_id');
		$this->dbforge->add_key('post_id');
		if ($this->dbforge->create_table('post_link_click_log', true) === false) {
			return false;
		}


		// post_meta table
		$this->dbforge->add_field(array(
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pmt_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'pmt_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('post_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'post_meta ADD UNIQUE KEY `post_id_pmt_key` (`post_id`, `pmt_key`)');


		// post_naver_syndi_log table
		$this->dbforge->add_field(array(
			'pns_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pns_status' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'default' => '',
			),
			'pns_return_code' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'default' => '',
			),
			'pns_return_message' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'default' => '',
			),
			'pns_receipt_number' => array(
				'type' => 'varchar',
				'constraint' => '255',
				'default' => '',
			),
			'pns_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('pns_id', true);
		$this->dbforge->add_key('post_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('post_naver_syndi_log', true) === false) {
			return false;
		}


		//post_tag table
		$this->dbforge->add_field(array(
			'pta_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'pta_tag' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
		));
		$this->dbforge->add_key('pta_id', true);
		$this->dbforge->add_key('post_id');
		$this->dbforge->add_key('pta_tag');
		if ($this->dbforge->create_table('post_tag', true) === false) {
			return false;
		}
		
		
		// scrap table
		$this->dbforge->add_field(array(
			'scr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'target_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'scr_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'scr_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('scr_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('post_id');
		if ($this->dbforge->create_table('scrap', true) === false) {
			return false;
		}


		// search_keyword table
		$this->dbforge->add_field(array(
			'sek_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'sek_keyword' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'sek_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'sek_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('sek_id', true);
		$this->dbforge->add_key(array('sek_keyword', 'sek_datetime', 'sek_ip'));
		if ($this->dbforge->create_table('search_keyword', true) === false) {
			return false;
		}


		// session table
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'VARCHAR',
				'constraint' => '120',
				'default' => '',
			),
			'ip_address' => array(
				'type' => 'VARCHAR',
				'constraint' => '45',
				'default' => '',
			),
			'timestamp' => array(
				'type' => 'INT',
				'constraint' => 10,
				'default' => '0',
			),
			'data' => array(
				'type' => 'BLOB',
			),
		));
		$this->dbforge->add_key('id', true);
		$this->dbforge->add_key('timestamp');
		if ($this->dbforge->create_table('session', true) === false) {
			return false;
		}


		// social table
		$this->dbforge->add_field(array(
			'soc_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'soc_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'soc_account_id' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'soc_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'soc_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('soc_id', true);
		$this->dbforge->add_key('soc_account_id');
		if ($this->dbforge->create_table('social', true) === false) {
			return false;
		}


		// social_meta table
		$this->dbforge->add_field(array(
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'smt_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'smt_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
		));
		$this->dbforge->add_key(array('smt_value'));
		if ($this->dbforge->create_table('social_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'social_meta ADD UNIQUE KEY `mem_id_smt_key` (`mem_id`, `smt_key`)');


		// stat_count table
		$this->dbforge->add_field(array(
			'sco_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'sco_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'sco_date' => array(
				'type' => 'DATE',
			),
			'sco_time' => array(
				'type' => 'TIME',
			),
			'sco_referer' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'sco_current' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'sco_agent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('sco_id', true);
		$this->dbforge->add_key('sco_date');
		if ($this->dbforge->create_table('stat_count', true) === false) {
			return false;
		}


		// stat_count_board table
		$this->dbforge->add_field(array(
			'scb_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'scb_date' => array(
				'type' => 'DATE',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'scb_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('scb_id', true);
		$this->dbforge->add_key(array('scb_date', 'brd_id'));
		if ($this->dbforge->create_table('stat_count_board', true) === false) {
			return false;
		}


		// stat_count_date table
		$this->dbforge->add_field(array(
			'scd_date' => array(
				'type' => 'DATE',
			),
			'scd_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('scd_date', true);
		if ($this->dbforge->create_table('stat_count_date', true) === false) {
			return false;
		}


		// tempsave table
		$this->dbforge->add_field(array(
			'tmp_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'tmp_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'tmp_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'tmp_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'tmp_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('tmp_id', true);
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('tempsave', true) === false) {
			return false;
		}


		// unique_id table
		$this->dbforge->add_field(array(
			'unq_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
			),
			'unq_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('unq_id', true);
		if ($this->dbforge->create_table('unique_id', true) === false) {
			return false;
		}

		return true;
	}

	
	public function _create_pro_tables()
	{
		$this->load->database();
		$this->load->dbforge();


		// attendance table
		$this->dbforge->add_field(array(
			'att_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'att_point' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'att_memo' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'null' => true,
			),
			'att_continuity' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'att_ranking' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'att_date' => array(
				'type' => 'DATE',
				'null' => true,
			),
			'att_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('att_id', true);
		$this->dbforge->add_key(array('att_datetime', 'mem_id'));
		if ($this->dbforge->create_table('attendance', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'attendance ADD UNIQUE KEY att_date_mem_id (`att_date`, `mem_id`)');


		// cmall_cart table
		$this->dbforge->add_field(array(
			'cct_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cde_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cct_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cct_cart' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cct_order' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cct_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cct_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cct_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('cit_id');
		if ($this->dbforge->create_table('cmall_cart', true) === false) {
			return false;
		}


		// cmall_category table
		$this->dbforge->add_field(array(
			'cca_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cca_value' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cca_parent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cca_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('cca_id', true);
		if ($this->dbforge->create_table('cmall_category', true) === false) {
			return false;
		}


		// cmall_category_rel table
		$this->dbforge->add_field(array(
			'ccr_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cca_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
		));
		$this->dbforge->add_key('ccr_id', true);
		$this->dbforge->add_key('cit_id');
		$this->dbforge->add_key('cca_id');
		if ($this->dbforge->create_table('cmall_category_rel', true) === false) {
			return false;
		}


		// cmall_demo_click_log table
		$this->dbforge->add_field(array(
			'cdc_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cdc_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cdc_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cdc_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cdc_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cdc_id', true);
		$this->dbforge->add_key('cit_id');
		if ($this->dbforge->create_table('cmall_demo_click_log', true) === false) {
			return false;
		}


		// cmall_download_log table
		$this->dbforge->add_field(array(
			'cdo_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cde_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cdo_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cdo_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cdo_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cdo_id', true);
		$this->dbforge->add_key('cde_id');
		$this->dbforge->add_key('cit_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('cmall_download_log', true) === false) {
			return false;
		}


		// cmall_item table
		$this->dbforge->add_field(array(
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cit_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_order' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cit_type1' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_type2' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_type3' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_type4' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_status' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_summary' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cit_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'cit_mobile_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'cit_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_price' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_file_1' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_2' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_3' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_4' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_5' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_6' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_7' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_8' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_9' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cit_file_10' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_hit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cit_updated_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cit_sell_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_wish_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_download_days' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_review_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cit_review_average' => array(
				'type' => 'DECIMAL',
				'constraint' => '2,1',
				'default' => '0',
			),
			'cit_qna_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
		));
		$this->dbforge->add_key('cit_id', true);
		$this->dbforge->add_key('cit_order');
		$this->dbforge->add_key('cit_price');
		$this->dbforge->add_key('cit_sell_count');
		if ($this->dbforge->create_table('cmall_item', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'cmall_item ADD UNIQUE KEY `cit_key` (`cit_key`)');


		// cmall_item_detail table
		$this->dbforge->add_field(array(
			'cde_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cde_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cde_price' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cde_originname' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cde_filename' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cde_download' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cde_filesize' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'cde_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '10',
				'default' => '',
			),
			'cde_is_image' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'cde_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cde_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cde_status' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
		));
		$this->dbforge->add_key('cde_id', true);
		$this->dbforge->add_key('cit_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('cmall_item_detail', true) === false) {
			return false;
		}


		// cmall_item_history table
		$this->dbforge->add_field(array(
			'chi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'chi_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'chi_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'chi_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
				'unsigned' => true,
			),
			'chi_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'chi_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('chi_id', true);
		$this->dbforge->add_key('cit_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('cmall_item_history', true) === false) {
			return false;
		}


		// cmall_item_meta table
		$this->dbforge->add_field(array(
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cim_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cim_value' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		if ($this->dbforge->create_table('cmall_item_meta', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'cmall_item_meta ADD UNIQUE KEY `cit_id_cim_key` (`cit_id`, `cim_key`)');


		// cmall_order table
		$this->dbforge->add_field(array(
			'cor_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
				'unsigned' => true,
			),
			'mem_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_realname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_memo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cor_total_money' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cor_deposit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cor_cash_request' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cor_cash' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cor_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cor_pay_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'cor_pg' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_tno' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_app_no' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_bank_info' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_admin_memo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'cor_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cor_approve_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cor_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cor_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_status' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cor_vbank_expire' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'is_test' => array(
				'type' => 'CHAR',
				'constraint' => '1',
				'default' => '',
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cor_refund_price' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cor_order_history' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('cor_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('cor_pay_type');
		$this->dbforge->add_key('cor_datetime');
		$this->dbforge->add_key('cor_approve_datetime');
		$this->dbforge->add_key('cor_status');
		if ($this->dbforge->create_table('cmall_order', true) === false) {
			return false;
		}


		// cmall_order_detail table
		$this->dbforge->add_field(array(
			'cod_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cor_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cde_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cod_download_days' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cod_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cod_status' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cod_id', true);
		$this->dbforge->add_key('cor_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('cmall_order_detail', true) === false) {
			return false;
		}


		// cmall_qna table
		$this->dbforge->add_field(array(
			'cqa_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cqa_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'cqa_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_reply_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'cqa_reply_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_secret' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_receive_email' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_receive_sms' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cqa_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cqa_reply_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cqa_reply_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cqa_reply_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cqa_id', true);
		$this->dbforge->add_key('cit_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('cmall_qna', true) === false) {
			return false;
		}


		// cmall_review table
		$this->dbforge->add_field(array(
			'cre_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cre_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'cre_content' => array(
				'type' => 'MEDIUMTEXT',
				'null' => true,
			),
			'cre_content_html_type' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cre_score' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'default' => '0',
			),
			'cre_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cre_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'cre_status' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('cre_id', true);
		$this->dbforge->add_key('cit_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('cmall_review', true) === false) {
			return false;
		}


		// cmall_wishlist table
		$this->dbforge->add_field(array(
			'cwi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cit_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'cwi_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'cwi_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('cwi_id', true);
		if ($this->dbforge->create_table('cmall_wishlist', true) === false) {
			return false;
		}
		$this->db->query('ALTER TABLE ' . $this->db->dbprefix . 'cmall_wishlist ADD UNIQUE KEY `mem_id_cit_id` (`mem_id`, `cit_id`)');


		// deposit table
		$this->dbforge->add_field(array(
			'dep_id' => array(
				'type' => 'BIGINT',
				'constraint' => 20,
				'unsigned' => true,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_nickname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_realname' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_email' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'mem_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_from_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'dep_to_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'dep_deposit_request' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_deposit' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_deposit_sum' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_cash_request' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_cash' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_point' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_content' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_pay_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '100',
				'default' => '',
			),
			'dep_pg' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_tno' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_app_no' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_bank_info' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_admin_memo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'dep_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'dep_deposit_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'dep_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'dep_useragent' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_status' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'dep_vbank_expire' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'is_test' => array(
				'type' => 'CHAR',
				'constraint' => '1',
				'default' => '',
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'dep_refund_price' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'dep_order_history' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('dep_id', true);
		$this->dbforge->add_key('mem_id');
		$this->dbforge->add_key('dep_pay_type');
		$this->dbforge->add_key('dep_datetime');
		$this->dbforge->add_key('dep_deposit_datetime');
		$this->dbforge->add_key('dep_status');
		if ($this->dbforge->create_table('deposit', true) === false) {
			return false;
		}


		// member_selfcert_history table
		$this->dbforge->add_field(array(
			'msh_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				 'auto_increment' => TRUE,
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => TRUE,
				'default' => '0',
			),
			'msh_company' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'msh_certtype' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'msh_cert_key' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'msh_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'msh_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('msh_id', TRUE);
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('member_selfcert_history', true) === false) {
			return false;
		}


		// payment_inicis_log table
		$this->dbforge->add_field(array(
			'pil_id' => array(
				'type' => 'BIGINT',
				'constraint' => 11,
				'unsigned' => true,
			),
			'pil_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_TID' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_MID' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_AUTH_DT' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_STATUS' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_TYPE' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_OID' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_FN_NM' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_AMT' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'P_AUTH_NO' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'P_RMESG1' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
		));
		$this->dbforge->add_key('pil_id');
		if ($this->dbforge->create_table('payment_inicis_log', true) === false) {
			return false;
		}


		// payment_order_data table
		$this->dbforge->add_field(array(
			'pod_id' => array(
				'type' => 'BIGINT',
				'constraint' => 11,
				'unsigned' => true,
			),
			'pod_pg' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'pod_type' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'pod_data' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'pod_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'pod_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'cart_id' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '0',
			),
		));
		$this->dbforge->add_key('pod_id');
		if ($this->dbforge->create_table('payment_order_data', true) === false) {
			return false;
		}


		// post_poll table
		$this->dbforge->add_field(array(
			'ppo_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'post_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'brd_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ppo_start_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ppo_end_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ppo_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ppo_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ppo_choose_count' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'ppo_after_comment' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'ppo_point' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => '0',
			),
			'ppo_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ppo_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('ppo_id', true);
		$this->dbforge->add_key('post_id');
		if ($this->dbforge->create_table('post_poll', true) === false) {
			return false;
		}


		// post_poll_item table
		$this->dbforge->add_field(array(
			'ppi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'ppo_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ppi_item' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ppi_count' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
		));
		$this->dbforge->add_key('ppi_id', true);
		$this->dbforge->add_key('ppo_id');
		if ($this->dbforge->create_table('post_poll_item', true) === false) {
			return false;
		}


		// post_poll_item_poll table
		$this->dbforge->add_field(array(
			'ppp_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'ppo_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ppi_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ppp_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ppp_ip' => array(
				'type' => 'VARCHAR',
				'constraint' => '50',
				'default' => '',
			),
		));
		$this->dbforge->add_key('ppp_id', true);
		$this->dbforge->add_key('ppo_id');
		$this->dbforge->add_key('ppi_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('post_poll_item_poll', true) === false) {
			return false;
		}


		// sms_favorite table
		$this->dbforge->add_field(array(
			'sfa_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'sfa_title' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'sfa_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'sfa_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('sfa_id', true);
		if ($this->dbforge->create_table('sms_favorite', true) === false) {
			return false;
		}


		// sms_member table
		$this->dbforge->add_field(array(
			'sme_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'smg_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'sme_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'sme_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'sme_receive' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'sme_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'sme_memo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('sme_id', true);
		$this->dbforge->add_key('smg_id');
		$this->dbforge->add_key('mem_id');
		if ($this->dbforge->create_table('sms_member', true) === false) {
			return false;
		}


		// sms_member_group table
		$this->dbforge->add_field(array(
			'smg_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'smg_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'smg_order' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'smg_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
		));
		$this->dbforge->add_key('smg_id', true);
		if ($this->dbforge->create_table('sms_member_group', true) === false) {
			return false;
		}


		// sms_send_content table
		$this->dbforge->add_field(array(
			'ssc_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'ssc_content' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'send_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ssc_send_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ssc_booking' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ssc_total' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'ssc_success' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'ssc_fail' => array(
				'type' => 'MEDIUMINT',
				'constraint' => 6,
				'unsigned' => true,
				'default' => '0',
			),
			'ssc_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ssc_memo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('ssc_id', true);
		if ($this->dbforge->create_table('sms_send_content', true) === false) {
			return false;
		}


		// sms_send_history table
		$this->dbforge->add_field(array(
			'ssh_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			),
			'ssc_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'send_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'recv_mem_id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'default' => '0',
			),
			'ssh_name' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ssh_phone' => array(
				'type' => 'VARCHAR',
				'constraint' => '255',
				'default' => '',
			),
			'ssh_success' => array(
				'type' => 'TINYINT',
				'constraint' => 4,
				'unsigned' => true,
				'default' => '0',
			),
			'ssh_datetime' => array(
				'type' => 'DATETIME',
				'null' => true,
			),
			'ssh_memo' => array(
				'type' => 'TEXT',
				'null' => true,
			),
			'ssh_log' => array(
				'type' => 'TEXT',
				'null' => true,
			),
		));
		$this->dbforge->add_key('ssh_id', true);
		$this->dbforge->add_key('ssc_id');
		if ($this->dbforge->create_table('sms_send_history', true) === false) {
			return false;
		}


		return true;
	}


	public function _migration_tables()
	{
		$this->load->library('migration');
		$this->config->load('migration');
		$row = $this->db->get('migrations')->row();
		$latest_version = config_item('migration_version');
		if ($latest_version && ! $this->migration->version($latest_version)) {
			return false;
		}

		return true;

	}


	public function _insert_init_data()
	{
		$this->load->library(array('user_agent', 'session'));
		$this->load->model(array(
			'Member_model', 'Member_group_model', 'Member_userid_model', 'Member_meta_model',
			'Member_nickname_model', 'Member_register_model', 'Document_model', 'Faq_model',
			'Faq_group_model', 'Board_model', 'Config_model', 'Board_meta_model',
			'Board_group_model', 'Board_group_meta_model', 'Menu_model',
		));

		if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}

		$this->load->driver('cache', config_item('cache_method'));


		$skin = $this->input->post('skin');
		$skin_mobile = $this->input->post('skin') === 'basic' ? 'mobile' : 'bootstrap';


		$configdata = array(
			'site_title' => 'OOOO 지역주택조합',
			'site_logo' => 'OOOO 지역주택조합',
			'admin_logo' => 'ADMINISTRATION',
			'webmaster_name' => '관리자',
			'webmaster_email' => $this->input->post('mem_email'), // 'noreply@domain.com',
			'spam_word' => '18아,18놈,18새끼,18년,18뇬,18노,18것,18넘,개년,개놈,개뇬,개새,개색끼,개세끼,개세이,개쉐이,개쉑,개쉽,개시키,개자식,개좆,게색기,게색끼,광뇬,뇬,눈깔,뉘미럴,니귀미,니기미,니미,도촬,되질래,뒈져라,뒈진다,디져라,디진다,디질래,병쉰,병신,뻐큐,뻑큐,뽁큐,삐리넷,새꺄,쉬발,쉬밸,쉬팔,쉽알,스패킹,스팽,시벌,시부랄,시부럴,시부리,시불,시브랄,시팍,시팔,시펄,실밸,십8,십쌔,십창,싶알,쌉년,썅놈,쌔끼,쌩쑈,썅,써벌,썩을년,쎄꺄,쎄엑,쓰바,쓰발,쓰벌,쓰팔,씨8,씨댕,씨바,씨발,씨뱅,씨봉알,씨부랄,씨부럴,씨부렁,씨부리,씨불,씨브랄,씨빠,씨빨,씨뽀랄,씨팍,씨팔,씨펄,씹,아가리,아갈이,엄창,접년,잡놈,재랄,저주글,조까,조빠,조쟁이,조지냐,조진다,조질래,존나,존니,좀물,좁년,좃,좆,좇,쥐랄,쥐롤,쥬디,지랄,지럴,지롤,지미랄,쫍빱,凸,퍽큐,뻑큐,빠큐,ㅅㅂㄹㅁ',
			'white_iframe' => 'www.youtube.com
www.youtube-nocookie.com
maps.google.co.kr
maps.google.com
flvs.daum.net
player.vimeo.com
sbsplayer.sbs.co.kr
serviceapi.rmcnmv.naver.com
serviceapi.nmv.naver.com
www.mgoon.com
videofarm.daum.net
player.sbs.co.kr
sbsplayer.sbs.co.kr
www.tagstory.com
play.tagstory.com
flvr.pandora.tv',
			'new_post_second' => '30',
			'open_currentvisitor' => '1',
			'currentvisitor_minute' => '10',
			'use_copy_log' => '1',
			'max_level' => '100',
			'ip_display_style' => '1001',
			'list_count' => '20',
			'site_blacklist_title' => '사이트가 공사중에 있습니다',
			'site_blacklist_content' => '<p>안녕하세요</p><p>블편을 드려 죄송합니다. 지금 이 사이트는 접근이 금지되어있습니다</p><p>감사합니다</p>',
			'use_point' => '',
			'point_register' => '50',
			'point_login' => '5',
			'point_recommended' => '5',
			'point_recommender' => '5',
			'point_note' => '10',
			'block_download_zeropoint' => '1',
			'block_read_zeropoint' => '1',
			'use_sideview' => '1',
			'use_mobile_sideview' => '1',
			'use_sideview_email' => '1',
			'use_mobile_sideview_email' => '1',
			'post_editor_type' => 'smarteditor',
			'use_document_dhtml' => '1',
			'document_editor_type' => 'smarteditor',
			'document_thumb_width' => '1280',
			'document_mobile_thumb_width' => '400',
			'document_content_target_blank' => '1',
			'use_document_auto_url' => '1',
			'use_faq_dhtml' => '1',
			'faq_editor_type' => 'smarteditor',
			'faq_thumb_width' => '1280',
			'faq_mobile_thumb_width' => '400',
			'faq_content_target_blank' => '1',
			'use_faq_auto_url' => '1',
			'use_popup_dhtml' => '1',
			'popup_editor_type' => 'smarteditor',
			'popup_thumb_width' => '1280',
			'popup_mobile_thumb_width' => '400',
			'popup_content_target_blank' => '1',
			'use_popup_auto_url' => '1',
			'use_formmail_dhtml' => '1',
			'formmail_editor_type' => 'smarteditor',
			'use_note' => '1',
			'note_list_page' => '10',
			'note_mobile_list_page' => '10',
			'use_note_dhtml' => '1',
			'use_note_mobile_dhtml' => '1',
			'note_editor_type' => 'smarteditor',
			'use_notification' => '1',
			'notification_reply' => '1',
			'notification_comment' => '1',
			'notification_comment_comment' => '1',
			'notification_note' => '1',

			'layout_default' => $skin,
			'sidebar_default' => '',
			'skin_default' => $skin,
			'mobile_layout_default' => $skin_mobile,
			'mobile_skin_default' => $skin_mobile,
			'skin_popup' => 'basic',
			'mobile_skin_popup' => 'basic',
			'skin_emailform' => 'basic',

			'use_login_account' => 'both',
			'password_length' => '4',
			'use_member_photo' => '',
			'member_photo_width' => '80',
			'member_photo_height' => '80',
			'use_member_icon' => '',
			'member_icon_width' => '20',
			'member_icon_height' => '20',
			'denied_nickname_list' => 'admin,administrator,관리자,운영자,어드민,주인장,webmaster,웹마스터,sysop,시삽,시샵,manager,매니저,메니저,root,루트,su,guest,방문객',
			'denied_userid_list' => 'admin,administrator,webmaster,sysop,manager,root,su,guest,super',
			'member_register_policy1' => '제1조 목 적

본 이용약관 (이하 \'약관\'이라 합니다)은 OO지역주택조합 (이하 \'회사\' 이라 합니다)에서 제공하는 회사의 인터넷 홈페이지를 이용함에 있어 이용자의 권리, 의무 및 책임 사항을 규정함을 목적으로 합니다.
제2조 이용자의 정의

① “OO지역주택조합 홈페이지”란 회사가 이용자에게 서비스를 제공하기 위하여 컴퓨터 등 정보통신설비를 이용하여 구성한 가상의 공간을 의미하며, 회사가 운용하는 웹사이트를 말합니다.
② “이용자”라 함은 OO지역주택조합 홈페이지에 접속하여 본 약관에 따라 회사가 제공하는 서비스를 받는 회원 및 비회원을 말합니다.
③ “회원”이라 함은 OO지역주택조합 홈페이지에 접속하여 본 약관에 따라 회사에 개인정보를 제공하고 이용약관 및 개인정보취급방침에 동의한 후 회원등록을 한 자로서, 회사가 제공하는 서비스를 계속적으로 이용할 수 있는 자를 말합니다.
④ “아이디(ID)”라 함은 “회원”의 식별과 회원의 서비스 이용을 위하여 회원이 정하고 “회사”가 승인한 문자와 숫자의 조합을 말합니다.
⑤ “비밀번호”라 함은 회원이 부여받은 아이디(ID)와 일치된 회원임을 확인하고, 회원의 개인정보를 보호하기 위하여 회원 자신이 정한 문자 또는 숫자의 조합을 의미합니다.
제3조 회원가입

① 이용자가 되고자 하는 자는 회사가 정한 가입 양식에 따라 회원정보를 기입하고 "등록하기" 단추를 누르는 방법으로 회원 가입을 신청합니다.
② 회사는 제1항과 같이 회원으로 가입할 것을 신청한 자가 다음 각 호에 해당하지 않는 한 신청한 자를 회원으로 등록합니다.
1. 가입신청자가 본 약관의 각 조항을 위반하여 회원자격을 상실한 적이 있는 경우. 다만 회원자격 상실 후 3년이 경과한 자로서 회사의 회원재가입 승낙을 얻은 경우에는 예외로 합니다.
2. 등록 내용에 허위, 기재누락, 오기가 있는 경우
3. 기타 회원으로 등록하는 것이 회사의 기술상 현저히 지장이 있다고 판단되는 경우
③ 회원가입계약의 성립시기는 회사의 승낙이 가입신청자에게 도달한 시점으로 합니다.
④ 회원은 제1항의 회원정보 기재 내용에 변경이 발생한 경우, 즉시 변경사항을 정정하여 기재하여야 합니다.
제4조(약관의 효력과 개정)

① 회사는 본 약관의 내용을 회원이 쉽게 알 수 있도록 OO지역주택조합 홈페이지 초기 서비스화면에 게시합니다.
② 회사는 약관의 규제에 관한 법률, 전자거래기본법, 전자서명법, 정보통신망 이용촉진 및 정보보호 등에 관한 법률 등 관련법을 위배하지 않는 범위에서 본 약관을 개정할 수 있습니다.
③ 회사는 본 약관을 개정할 경우에는 적용일자 및 개정사유를 명시하여 현행 약관과 함께 홈페이지의 초기 화면에 그 적용일자 7일 이전부터 적용일자 전일까지 공지합니다. 다만, 회원에게 불리하게 약관내용을 변경하는 경우에는 최소한 30일 이상의 사전 유예기간을 두고 공지합니다. 이 경우 회사는 개정 전 내용과 개정 후 내용을 명확하게 비교하여 회원이 알기 쉽도록 표시합니다.
④ 회원은 개정된 약관에 대해 거부할 권리가 있습니다. 회원은 개정된 약관에 동의하지 않을 경우 서비스 이용을 중단하고 회원등록을 해지할 수 있습니다. 단, 개정된 약관의 효력 발생일 이후에도 서비스를 계속 이용할 경우에는 약관의 변경사항에 동의한 것으로 간주합니다.
⑤ 변경된 약관에 대한 정보를 알지 못해 발생하는 회원 피해는 회사가 책임지지 않습니다.
제5조 회원정보 사용에 대한 동의

① 회원의 개인정보에 대해서는 회사의 개인정보 취급방침이 적용됩니다.
② 회사의 회원 정보는 다음과 같이 수집, 사용, 관리 보호됩니다.
1. 개인정보의 수집 : 회사는 귀하의 회사 서비스 가입시 회원이 제공하는 정보 커뮤니티 활동을 위하여 회원이 제공하는 정보, 각종 이벤트 참가를 위하여 귀하가 제공하는 정보, 광고나 경품의 취득을 위하여 회원이 제공하는 정보 등을 통하여 회원에 관한 정보를 수집합니다.
2. 개인 정보의 사용 : 회사는 회사 서비스 제공과 관련해서 수집된 회원 신상정보를 회원의 승낙없이 제3자에게 누설, 배포하지 않습니다. 단, 전기통신기본법 등 법률의 규정에 의해 국가기관의 요구가 있는 경우, 범죄에 대한 수사상의 목적이 있거나 정보통신윤리 위원회의 요청이 있는 경우 또는 기타 관계법령에서 정한 절차에 따른 요청이 있는 경우, 귀하가 회사에 제공한 개인정보를 스스로 공개한 경우에는 그러하지 않습니다.
3. 개인정보의 관리 : 귀하는 개인정보의 보호 및 관리를 위하여 홈페이지 회원정보에서 수시로 귀하의 개인정보를 수정/삭제할 수 있습니다.
4. 개인정보의 보호 : 귀하의 개인정보는 오직 귀하만이 열람/수정/삭제 할 수 있으며, 이는 전적으로 귀하의 비밀번호를 알려주어서는 아니되며, 작업 종료 시에는 반드시 로그아웃 해 주시고, 웹 브라우저의 창을 닫아 주시기 바랍니다. (이는 타인과 컴퓨터를 공유하는 인터넷 카페나 도서관 같은 공공장소에서 컴퓨터를 사용하는 경우에 귀하의 정보의 보호를 위하여 필요한 사항입니다.)
5. 기타 : 게시판이나 E-mail, 등 온라인상에서 귀하가 자발적으로 제공하는 개인정보는 다른 사람들이 수집하여 사용할 수 있음을 인지하시기 바랍니다. 공개적인 공간에 게재되는 개인정보로 인해 원하지 않는 상황이 발생할 수 도 있습니다. 개인정보에 대한 비밀을 유지할 책임은 귀하에게 있으며, 회사는 개인정보 유출로 인해 발생한 결과에 대하여 어떤 책임도 부담하지 아니합니다.
③ 회원이 회사에 본 약관에 따라 이용신청을 하는 것은 회사가 본 약관에 따라 신청서에 기재된 회원정보를 수집, 이용 및 제공하는 것에 동의하는 것으로 간주됩니다.
제6조 서비스의 중단

① 회사는 컴퓨터 등 정보통신설비의 보수점검?교체 및 고장, 통신의 두절 등의 사유가 발생한 경우에는 서비스의 제공을 일시적으로 중단할 수 있고, 새로운 서비스로의 교체 기타 회사가 적절하다고 판단하는 사유에 기하여 현재 제공되는 서비스를 완전히 중단할 수 있습니다.
② 제1항에 의한 서비스 중단의 경우 회사는 제8조 제2항에서 정한 방법으로 이용자에게 통지합니다. 다만, 회사가 통제할 수 없는 사유로 인한 서비스의 중단(시스템 관리자의 고의, 과실이 없는 디스크 장애, 시스템 다운 등)으로 인하여 사전 통지가 불가능한 경우에는 그러하지 아니합니다.
제7조 이용자 탈퇴 및 자격 상실 등

① 이용자는 회사에 언제든지 자신의 회원 등록을 말소해 줄 것(이용자 탈퇴)을 요청할 수 있으며 회사은 위 요청을 받은 즉시 해당 이용자의 회원 등록 말소를 위한 절차를 밟습니다.
② 이용자가 다음 각 호의 사유에 해당하는 경우, 회사는 이용자의 회원자격을 적절한 방법으로 제한 및 정지, 상실 시킬 수 있습니다.
1. 가입 신청 시에 허위 내용을 등록한 경우
2. 다른 사람의 회사 이용을 방해하거나 그 정보를 도용하는 등 전자거래질서를 위협하는 경우
3. 회사을 이용하여 법령과 본 약관이 금지하거나 공서양속에 반하는 행위를 하는 경우
③ 회사는 이용자의 회원자격을 상실 시키기로 결정한 경우에는 회원등록을 말소합니다. 이 경우 이용자인 회원에게 회원등록 말소 전에 이를 통지하고, 소명할 기회를 부여합니다.
제8조 이용자에 대한 통지

① 회사가 제4조에서 정한 약관의 개정이외의 사항에 대하여 특정 이용자에 대한 통지를 하는 경우 회사가 부여한 메일주소로 할 수 있으며, 가입시 기입한 메일 주소로 할 수 있습니다.
② 회사는 제4조에서 정한 약관의 개정이외의 사항에 대하여 불특정다수 이용자에 대한 통지를 하는 경우 1주일이상 회사 게시판에 게시함으로써 개별 통지에 갈음할 수 있습니다.
제9조 이용자의 개인정보보호

① 회사는 관련법령이 정하는 바에 따라서 이용자 등록정보를 포함한 이용자의 개인정보를 보호하기 위하여 노력합니다. 이용자의 개인정보보호에 관해서는 관련법령 및 회사가 정하는 개인정보취급방침에 정한 바에 의합니다.
제10조 회사의 의무

① 회사는 법령과 본 약관이 금지하거나 공서양속에 반하는 행위를 하지 않으며 본 약관이 정하는 바에 따라 지속적이고, 안정적으로 서비스를 제공하기 위해서 노력합니다.
② 회사는 이용자가 안전하게 인터넷 서비스를 이용할 수 있도록 이용자의 개인정보(신용정보 포함)보호를 위한 보안 시스템을 구축합니다.
③ 회사는 이용자가 원하지 않는 영리목적의 광고성 전자우편을 발송하지 않습니다.
④ 회사는 이용자가 서비스를 이용함에 있어 회사의 고의 또는 중대한 과실로 인하여 입은 손해를 배상할 책임을 부담합니다.
제11조 이용자의 ID 및 비밀번호에 대한 의무

① 회사는 관계법령, 개인정보 취급방침에 의해서 그 책임을 지는 경우를 제외하고, 자신의 ID와 비밀번호에 관한 관리책임은 각 이용자에게 있습니다.
② 이용자는 자신의 ID 및 비밀번호를 제3자에게 이용하게 해서는 안됩니다.
③ 이용자는 자신의 ID 및 비밀번호를 도난 당하거나 제3자가 사용하고 있음을 인지한 경우에는 바로 회사에 통보하고 회사의 안내가 있는 경우에는 그에 따라야 합니다.
제12조 이용자의 의무

① 이용자는 다음 각 호의 행위를 하여서는 안됩니다.
1. 회원가입신청 또는 변경시 허위내용을 등록하는 행위
2. 회사에 게시된 정보를 변경하는 행위
3. 회사 기타 제3자의 인격권 또는 지적재산권을 침해하거나 업무를 방해하는 행위
4. 다른 회원의 ID를 도용하는 행위
5. 정크메일(junk mail), 스팸메일(spam mail), 행운의 편지(chain letters), 피라미드 조직에 가입할 것을 권유하는 메일, 외설 또는 폭력적인 메시지 ?화상?음성 등이 담긴 메일을 보내거나 기타 공서양속에 반하는 정보를 공개 또는 게시하는 행위.
6. 관련 법령에 의하여 그 전송 또는 게시가 금지되는 정보(컴퓨터 프로그램 등)의 전송 또는 게시하는 행위
7. 회사의 직원이나 회사 서비스의 관리자를 가장하거나 사칭하여 또는 타인의 명의를 도용하여 글을 게시하거나 메일을 발송하는 행위
8. 컴퓨터 소프트웨어, 하드웨어, 전기통신 장비의 정상적인 가동을 방해, 파괴할 목적으로 고안된 소프트웨어 바이러스, 기타 다른 컴퓨터 코드, 파일, 프로그램을 포함하고 있는 자료를 게시하거나 전자우편으로 발송하는 행위
9. 스토킹(stalking) 등 다른 이용자를 괴롭히는 행위
10. 다른 이용자에 대한 개인정보를 그 동의 없이 수집, 저장, 공개하는 행위
11. 불특정 다수의 자를 대상으로 하여 광고 또는 선전을 게시하거나 스팸메일을 전송하는 등의 방법으로 회사의 서비스를 이용하여 영리목적의 활동을 하는 행위
12. 회사가 제공하는 서비스에 정한 약관 기타 서비스 이용에 관한 규정을 위반하는 행위
② 제1항에 해당하는 행위를 한 이용자가 있을 경우 회사는 본 약관 제7조 제2, 3항에서 정한 바에 따라 이용자의 회원자격을 적절한 방법으로 제한 및 정지, 상실시킬 수 있습니다.
③ 이용자는 그 귀책사유로 인하여 회사나 다른 이용자가 입은 손해를 배상할 책임이 있습니다.
제 13조 회원 정보의 변경

① 회원은 회원정보의 변경(주소, E-mail Address 등)이 있을 경우 즉시 회사가 운영하는 웹상에 통보 및 수정을 하여야 합니다.
② 회원의 변경사항에 대한 미통보 및 미수정으로 인한 책임은 전적으로 회원에게 있으며 이는 서비스의 정지 및 해지의 사유가 될 수 있습니다.
제 14조 공개게시물의 삭제

이용자의 공개게시물의 내용이 다음 각 호에 해당하는 경우 회사는 이용자에게 사전 통지 없이 해당 공개게시물을 삭제할 수 있고, 해당 이용자의 회원 자격을 제한, 정지 또는 상실시킬 수 있습니다.
1. 다른 이용자 또는 제3자를 비방하거나 명예를 손상시키는 내용
2. 공서양속에 위반되는 내용의 정보, 문장, 도형 등을 유포하는 내용
3. 범죄행위와 관련이 있다고 판단되는 내용
4. 다른 이용자 또는 제3자의 저작권 등 기타 권리를 침해하는 내용
5. 기타 관계 법령에 위배된다고 판단되는 내용
제15조 저작권의 귀속 및 이용제한

① 회사이 작성한 저작물에 대한 저작권 기타 지적재산권은 회사에 귀속합니다.
② 이용자는 회사를 이용함으로써 얻은 정보를 회사의 사전승낙 없이 복제, 전송, 출판, 배포, 방송 기타 방법에 의하여 영리목적으로 이용하거나 제3자에게 이용하게 하여서는 안됩니다.
제16조 손해배상

회사는 무료로 제공되는 서비스와 관련하여 회원에게 어떠한 손해가 발생되더라도 동 손해가 회사의 중대한 과실에 의한 경우를 제외하고 이에 대하여 책임을 부담하지 아니합니다.
제17조 면책조항

① 회사는 천재지변 기타 이에 준하는 불가항력으로 인하여 서비스를 제공할 수 없을 경우에는 이용자 및 회원들의 귀책사유로 인한 서비스 이용의 장애에 대하여 책임을 면합니다.
② 회사는 이용자 및 회원들의 귀책사유로 인한 서비스 이용의 장애에 대한여 책임을 면합니다.
③ 회사는 이용자 및 회원이 서비스를 이용하여 얻은 정보 등으로 인해 입은 손해등에 대하여는 책임을 면합니다.
④ 회사는 회사가 제공하는 서비스망을 통해 제공하는 정보의 신뢰도나 정확성에 대하여는 책임을 면합니다.
⑤ 회사는 이용자 및 회원이 게시 또는 전송한 자료의 내용에 관하여는 책임을 면합니다.
제18조 약관외 준칙

① 본 약관에 명시되지 않은 사항에 대하여는 관련법령 또는 상관례에 따릅니다.
제19조 사용요금 확인 및 결제

① 회사가 제공하는 재화나 용역을 이용하는 회원은 회사가 제공하는 재화나 용역에 대한 월 사용금액을 OO지역주택조합 홈페이지를 통하여 확인할 수 있으며, 확인한 금액에 대하여 신용카드, 실시간 계좌이체 중 회원이 선택하고 회사가 승낙한 방법으로 결제할 수 있습니다.
제20조 재판관할

회사와 이용자간에 발생한 서비스 이용에 관한 분쟁으로 인한 소는 서울중앙지방법원 또는 민사소송법상의 관할을 가지는 대한민국의 법원에 제기합니다.
부 칙

본 약관은 '.date('Y. m. d.').' 부터 적용하고, 적용되던 종전의 약관은 본 약관으로 대체합니다.',
			'member_register_policy2' => 'OO지역주택조합은 (이하 \'회사\'는) 고객님의 개인정보를 중요시하며, 정보통신망 이용촉진 및 정보보호에 관한 법률을 준수하고 있습니다. 회사는 개인정보취급방침을 통하여 고객님께서 제공하시는 개인정보가 어떠한 용도와 방식으로 이용되고 있으며, 개인정보보호를 위해 어떠한 조치가 취해지고 있는지 알려드립니다. 회사는 개인정보취급방침을 개정하는 경우 웹사이트 공지사항(또는 개별공지)을 통하여 공지할 것입니다.

제1조 수집하는 개인정보 항목
① 회사는 회원가입, 상담, 서비스 신청 등등을 위해 아래와 같은 개인정보를 수집하고 있습니다.
수집항목 : 이름 , 로그인ID , 비밀번호 , 휴대전화번호 , 이메일 , 메일 SNS수신동의
개인정보 수집방법 : 홈페이지(홈페이지 회원가입)

제2조 개인정보의 수집 및 이용목적
① 회사는 수집한 개인정보를 다음의 목적을 위해 활용합니다.
서비스 제공에 관한 계약 이행 및 서비스 제공에 따른 요금정산 콘텐츠 제공
회원제 서비스 이용에 따른 본인확인 , 개인 식별 , 불량회원의 부정 이용 방지와 비인가 사용 방지 , 가입 의사 확인 , 연령확인 , 만14세 미만 아동 개인정보 수집 시 법정 대리인 동의여부 확인 , 불만처리 등 민원처리 , 고지사항 전달
마케팅 및 광고에 활용 신규 서비스(제품) 개발 및 특화 , 이벤트 등 광고성 정보 전달 , 인구통계학적 특성에 따른 서비스 제공 및 광고 게재 , 접속 빈도 파악 또는 회원의 서비스 이용에 대한 통계

제3조 개인정보의 보유 및 이용기간
① 회사는 개인정보 수집 및 이용목적이 달성된 후에는 예외 없이 해당 정보를 지체 없이 파기합니다.

제4조 개인정보의 파기절차 및 방법
① 회사는 원칙적으로 개인정보 수집 및 이용목적이 달성된 후에는 해당 정보를 지체없이 파기합니다. 파기절차 및 방법은 다음과 같습니다.
파기절차 회원님이 회원가입 등을 위해 입력하신 정보는 목적이 달성된 후 별도의 DB로 옮겨져(종이의 경우 별도의 서류함) 내부 방침 및 기타 관련 법령에 의한 정보보호 사유에 따라(보유 및 이용기간 참조) 일정 기간 저장된 후 파기되어집니다.별도 DB로 옮겨진 개인정보는 법률에 의한 경우가 아니고서는 보유되어지는 이외의 다른 목적으로 이용되지 않습니다.
파기방법 전자적 파일형태로 저장된 개인정보는 기록을 재생할 수 없는 기술적 방법을 사용하여 삭제합니다.

제5조 개인정보 제공
① 회사는 이용자의 개인정보를 원칙적으로 외부에 제공하지 않습니다. 다만, 아래의 경우에는 예외로 합니다.
이용자들이 사전에 동의한 경우
법령의 규정에 의거하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 수사기관의 요구가 있는 경우

제6조 수집한 개인정보의 위탁
회사는 고객서비스 관리 및 민원사항에 대한 처리 등 원활한 업무 수행을 위하여 아래와 같이 개인정보 취급 업무를 위탁하여 운영하고 있습니다. 또한 위탁계약 시 개인정보보호의 안전을 기하기 위하여 개인정보보호 관련 법규의 준수, 개인정보에 관한 제3자 공급 금지 및 사고시의 책임부담 등을 명확히 규정하고 있습니다. 동 업체가 변경될 경우, 변경된 업체 명을 공지사항 내지 개인정보취급방침 화면을 통해 고지 하겠습니다.

제7조 이용자 및 법정대리인의 권리와 그 행사방법
① 이용자 및 법정 대리인은 언제든지 등록되어 있는 자신 혹은 당해 만 14세 미만 아동의 개인정보를 조회하거나 수정할 수 있으며 가입해지를 요청할 수 있습니다.
② 이용자 혹은 만 14세 미만 아동의 개인정보 조회 또는 수정을 위해서는 개인정보변경(또는 회원정보수정 등)을, 가입해지(동의철회)를 위해서는 회원탈퇴를 클릭 하여 본인 확인 절차를 거치신 후 직접 열람, 정정 또는 탈퇴가 가능합니다. 혹은 개인정보관리책임자에게 서면, 전화 또는 이메일로 연락하시면 지체없이 조치하겠습니다.
③ 회사는 귀하가 개인정보의 오류에 대한 정정을 요청하신 경우에는 정정을 완료하기 전까지 당해 개인정보를 이용 또는 제공하지 않습니다. 또한 잘못된 개인정보를 제3자 에게 이미 제공한 경우에는 정정 처리결과를 제3자에게 지체없이 통지하여 정정이 이루어지도록 하겠습니다.
④ 회사는 이용자 혹은 법정 대리인의 요청에 의해 해지 또는 삭제된 개인정보는 회사가 수집하는 개인정보의 보유 및 이용기간에 명시된 바에 따라 처리하고 그 외의 용도로 열람 또는 이용할 수 없도록 처리하고 있습니다.

제8조 개인정보 자동수집 장치의 설치, 운영 및 그 거부에 관한 사항
① 회사는 쿠키 등 인터넷 서비스 이용 시 자동 생성되는 개인정보를 수집하는 장치를 운영하지 않습니다.

제9조 개인정보에 관한 민원서비스
① 회사는 고객의 개인정보를 보호하고 개인정보와 관련한 불만을 처리하기 위하여 아래와 같이 관련 부서 및 개인정보관리책임자를 지정하고 있습니다.
개인정보관리책임자 성명 :
전화번호 :
이메일 :

② 귀하께서는 회사의 서비스를 이용하시며 발생하는 모든 개인정보보호 관련 민원을 개인정보관리책임자 혹은 담당부서로 신고하실 수 있습니다. 회사는 이용자들의 신고사항에 대해 신속하게 충분한 답변을 드릴 것입니다. 기타 개인정보침해에 대한 신고나 상담이 필요하신 경우에는 아래 기관에 문의하시기 바랍니다.
개인분쟁조정위원회 (privacy.kisa.or.kr/(국번없이)118)
정보보호마크인증위원회 (www.eprivacy.or.kr/02-580-0533~4)
대검찰청 인터넷범죄수사센터 (www.spp.go.kr/02-3480-3573)
경찰청 사이버테러대응센터 (www.netan.go.kr/1566-0112)
부  칙 시행일 등
본 방침은 '.date('Y. m. d.').'일부터 시행합니다.
',
			'register_level' => '1',
			'change_nickname_date' => '60',
			'change_open_profile_date' => '60',
			'change_use_note_date' => '60',
			'change_password_date' => '180',
			'max_login_try_count' => '5',
			'max_login_try_limit_second' => '30',
			
			'send_email_register_admin' => '1',
			'send_email_register_user' => '1',
			'send_note_register_admin' => '1',
			'send_note_register_user' => '1',
			'send_sms_register_admin' => '1',
			'send_email_changepw_user' => '1',
			'send_note_changepw_user' => '1',
			'send_email_memberleave_admin' => '1',
			'send_email_memberleave_user' => '1',
			'send_note_memberleave_admin' => '1',
			
			'total_rss_feed_count' => '100',
			'site_meta_title_default' => '{홈페이지제목}',
			'site_meta_title_main' => '{홈페이지제목}',
			'site_meta_title_board_list' => '{게시판명} - {홈페이지제목}',
			'site_meta_title_board_post' => '{글제목} > {게시판명} - {홈페이지제목}',
			'site_meta_title_board_write' => '{게시판명} 글쓰기 - {홈페이지제목}',
			'site_meta_title_board_modify' => '{글제목} 글수정 - {홈페이지제목}',
			'site_meta_title_group' => '{그룹명} - {홈페이지제목}',
			'site_meta_title_document' => '{문서제목} - {홈페이지제목}',
			'site_meta_title_faq' => '{FAQ제목} - {홈페이지제목}',
			'site_meta_title_register' => '회원가입 - {홈페이지제목}',
			'site_meta_title_register_form' => '회원가입 - {홈페이지제목}',
			'site_meta_title_register_result' => '회원가입결과 - {홈페이지제목}',
			'site_meta_title_findaccount' => '회원정보찾기 - {홈페이지제목}',
			'site_meta_title_login' => '로그인 - {홈페이지제목}',
			'site_meta_title_mypage' => '{회원닉네임}님의 마이페이지 - {홈페이지제목}',
			'site_meta_title_mypage_post' => '{회원닉네임}님의 작성글 - {홈페이지제목}',
			'site_meta_title_mypage_comment' => '{회원닉네임}님의 작성댓글 - {홈페이지제목}',
			'site_meta_title_mypage_point' => '{회원닉네임}님의 포인트 - {홈페이지제목}',
			'site_meta_title_mypage_followinglist' => '{회원닉네임}님의 팔로우 - {홈페이지제목}',
			'site_meta_title_mypage_followedlist' => '{회원닉네임}님의 팔로우 - {홈페이지제목}',
			'site_meta_title_mypage_like_post' => '{회원닉네임}님의 추천글 - {홈페이지제목}',
			'site_meta_title_mypage_like_comment' => '{회원닉네임}님의 추천댓글 - {홈페이지제목}',
			'site_meta_title_mypage_scrap' => '{회원닉네임}님의 스크랩 - {홈페이지제목}',
			'site_meta_title_mypage_loginlog' => '{회원닉네임}님의 로그인기록 - {홈페이지제목}',
			'site_meta_title_membermodify' => '회원정보수정 - {홈페이지제목}',
			'site_meta_title_membermodify_memberleave' => '회원탈퇴 - {홈페이지제목}',
			'site_meta_title_currentvisitor' => '현재접속자 - {홈페이지제목}',
			'site_meta_title_search' => '{검색어} - {홈페이지제목}',
			'site_meta_title_note_list' => '{회원닉네임}님의 쪽지함 - {홈페이지제목}',
			'site_meta_title_note_view' => '{회원닉네임}님의 쪽지함 - {홈페이지제목}',
			'site_meta_title_note_write' => '{회원닉네임}님의 쪽지함 - {홈페이지제목}',
			'site_meta_title_profile' => '{회원닉네임}님의 프로필 - {홈페이지제목}',
			'site_meta_title_formmail' => '메일발송 - {홈페이지제목}',
			'site_meta_title_notification' => '{회원닉네임}님의 알림 - {홈페이지제목}',

			'send_email_register_admin_title' => '[회원가입알림] {회원닉네임}님이 회원가입하셨습니다',
			'send_email_register_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 관리자님,</span><br /></td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>{회원닉네임} 님이 회원가입 하셨습니다.</p><p>회원아이디 : {회원아이디}</p><p>닉네임 : {회원닉네임}</p><p>이메일 : {회원이메일}</p><p>가입한 곳 IP : {회원아이피}</p><p>감사합니다.</p></td></tr></table>',
			'send_email_register_user_title' => '[{홈페이지명}] {회원닉네임}님의 회원가입을 축하드립니다',
			'send_email_register_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원가입을 축하드립니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요 {회원닉네임} 회원님,</p><p>회원가입을 축하드립니다.</p><p>{홈페이지명} 회원으로 가입해주셔서 감사합니다.</p><p>더욱 편리한 서비스를 제공하기 위해 항상 최선을 다하겠습니다.</p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_email_register_user_verifytitle' => '[{홈페이지명}] {회원닉네임}님의 회원가입을 축하드립니다',
			'send_email_register_user_verifycontent' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원가입을 축하드립니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요 {회원닉네임} 회원님,</p><p>회원가입을 축하드립니다.</p><p>{홈페이지명} 회원으로 가입해주셔서 감사합니다.</p><p>더욱 편리한 서비스를 제공하기 위해 항상 최선을 다하겠습니다.</p><p>&nbsp;</p><p>아래 링크를 클릭하시면 회원가입이 완료됩니다.</p><p><a href="{메일인증주소}" target="_blank" style="font-weight:bold;">메일인증 받기</a></p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_note_register_admin_title' => '[회원가입알림] {회원닉네임}님이 회원가입하셨습니다',
			'send_note_register_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 관리자님,</span><br /></td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>{회원닉네임} 님이 회원가입 하셨습니다.</p><p>회원아이디 : {회원아이디}</p><p>닉네임 : {회원닉네임}</p><p>이메일 : {회원이메일}</p><p>가입한 곳 IP : {회원아이피}</p><p>감사합니다.</p></td></tr></table>',
			'send_note_register_user_title' => '회원가입을 축하드립니다',
			'send_note_register_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원가입을 축하드립니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요 {회원닉네임} 회원님,</p><p>회원가입을 축하드립니다.</p><p>{홈페이지명} 회원으로 가입해주셔서 감사합니다.</p><p>더욱 편리한 서비스를 제공하기 위해 항상 최선을 다하겠습니다.</p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_email_changepw_admin_title' => '{회원닉네임}님이 패스워드를 변경하셨습니다',
			'send_email_changepw_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 관리자님,</span><br /></td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>{회원닉네임} 님이 패스워드를 변경하셨습니다.</p><p>회원아이디 : {회원아이디}</p><p>닉네임 : {회원닉네임}</p><p>이메일 : {회원이메일}</p><p>변경한 곳 IP : {회원아이피}</p><p>감사합니다.</p></td></tr></table>',
			'send_email_changepw_user_title' => '[{홈페이지명}] 패스워드가 변경되었습니다',
			'send_email_changepw_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원님의 패스워드가 변경되었습니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요 {회원닉네임} 회원님,</p><p>회원님의 패스워드가 변경되었습니다.</p><p>변경한 곳 IP : {회원아이피}</p><p>더욱 편리한 서비스를 제공하기 위해 항상 최선을 다하겠습니다.</p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_note_changepw_admin_title' => '{회원닉네임}님이 패스워드를 변경하셨습니다',
			'send_note_changepw_admin_content ' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 관리자님,</span><br /></td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>{회원닉네임} 님이 패스워드를 변경하셨습니다.</p><p>회원아이디 : {회원아이디}</p><p>닉네임 : {회원닉네임}</p><p>이메일 : {회원이메일}</p><p>변경한 곳 IP : {회원아이피}</p><p>감사합니다.</p></td></tr></table>',
			'send_note_changepw_user_title' => '패스워드가 변경되었습니다',
			'send_note_changepw_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원님의 패스워드가 변경되었습니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요 {회원닉네임} 회원님,</p><p>회원님의 패스워드가 변경되었습니다.</p><p>변경한 곳 IP : {회원아이피}</p><p>더욱 편리한 서비스를 제공하기 위해 항상 최선을 다하겠습니다.</p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_email_memberleave_admin_title' => '{회원닉네임}님이 회원탈퇴하셨습니다',
			'send_email_memberleave_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 관리자님,</span><br /></td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>{회원닉네임} 님이 회원탈퇴하셨습니다.</p><p>회원아이디 : {회원아이디}</p><p>닉네임 : {회원닉네임}</p><p>이메일 : {회원이메일}</p><p>탈퇴한 곳 IP : {회원아이피}</p><p>감사합니다.</p></td></tr></table>',
			'send_email_memberleave_user_title' => '[{홈페이지명}] 회원탈퇴가 완료되었습니다',
			'send_email_memberleave_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원님의 탈퇴가 처리되었습니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요 {회원닉네임} 회원님,</p><p>그 동안 {홈페이지명} 이용을 해주셔서 감사드립니다</p><p>요청하신대로 회원님의 탈퇴가 정상적으로 처리되었습니다.</p><p>더욱 편리한 서비스를 제공하기 위해 항상 최선을 다하겠습니다.</p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_note_memberleave_admin_title' => '{회원닉네임}님이 회원탈퇴하셨습니다',
			'send_note_memberleave_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 관리자님,</span><br /></td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>{회원닉네임} 님이 회원탈퇴하셨습니다.</p><p>회원아이디 : {회원아이디}</p><p>닉네임 : {회원닉네임}</p><p>이메일 : {회원이메일}</p><p>탈퇴한 곳 IP : {회원아이피}</p><p>감사합니다.</p></td></tr></table>',
			'send_email_changeemail_user_title' => '[{홈페이지명}] 회원님의 이메일정보가 변경되었습니다',
			'send_email_changeemail_user_content' => '<table width="100%" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원님의 이메일 주소가 변경되어 알려드립니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>회원님의 이메일 주소가 변경되었으므로 다시 인증을 받아주시기 바랍니다.</p><p>&nbsp;</p><p>아래 링크를 클릭하시면 주소변경 인증이 완료됩니다.</p><p><a href="{메일인증주소}" target="_blank" style="font-weight:bold;">메일인증 받기</a></p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_email_findaccount_user_title' => '{회원닉네임}님의 아이디와 패스워드를 보내드립니다',
			'send_email_findaccount_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원님의 아이디와 패스워드를 보내드립니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>&nbsp;</p><p>회원님의 아이디는 <strong>{회원아이디}</strong> 입니다.</p><p>아래 링크를 클릭하시면 회원님의 패스워드 변경이 가능합니다.</p><p><a href="{패스워드변경주소}" target="_blank" style="font-weight:bold;">패스워드 변경하기</a></p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_email_resendverify_user_title' => '{회원닉네임}님의 인증메일이 재발송되었습니다',
			'send_email_resendverify_user_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br />회원님의 인증메일을 다시 보내드립니다..</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>안녕하세요,</p><p>&nbsp;</p><p>아래 링크를 클릭하시면 이메일 인증이 완료됩니다.</p><p><a href="{메일인증주소}" target="_blank" style="font-weight:bold;">메일인증 받기</a></p><p>&nbsp;</p><p>감사합니다.</p></td></tr></table>',
			'send_email_post_admin_title' => '[{게시판명}] {게시글제목}',
			'send_email_post_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_post_writer_title' => '[{게시판명}] {게시글제목}',
			'send_email_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_post_admin_title' => '[{게시판명}] {게시글제목}',
			'send_note_post_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_post_writer_title' => '[{게시판명}] {게시글제목}',
			'send_note_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_comment_admin_title' => '[{게시판명}] {게시글제목} - 댓글이 등록되었습니다',
			'send_email_comment_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_comment_post_writer_title' => '[{게시판명}] {게시글제목} - 댓글이 등록되었습니다',
			'send_email_comment_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_comment_comment_writer_title' => '[{게시판명}] {게시글제목} - 댓글이 등록되었습니다',
			'send_email_comment_comment_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_comment_admin_title' => '[{게시판명}] {게시글제목} - 댓글이 등록되었습니다',
			'send_note_comment_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_comment_post_writer_title' => '[{게시판명}] {게시글제목} - 댓글이 등록되었습니다',
			'send_note_comment_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_comment_comment_writer_title' => '[{게시판명}] {게시글제목} - 댓글이 등록되었습니다',
			'send_note_comment_comment_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />작성자 : {게시글작성자닉네임}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_blame_admin_title' => '[{게시판명}] {게시글제목} - 신고가접수되었습니다',
			'send_email_blame_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />게시글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_blame_post_writer_title' => '[{게시판명}] {게시글제목} - 신고가접수되었습니다',
			'send_email_blame_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />게시글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_blame_admin_title' => '[{게시판명}] {게시글제목} - 신고가접수되었습니다',
			'send_note_blame_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />게시글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_blame_post_writer_title' => '[{게시판명}] {게시글제목} - 신고가접수되었습니다',
			'send_note_blame_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />게시글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{게시글내용}</div><p><a href="{게시글주소}" target="_blank" style="font-weight:bold;">사이트에서 게시글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_comment_blame_admin_title' => '[{게시판명}] {게시글제목} - 댓글에신고가접수되었습니다',
			'send_email_comment_blame_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />댓글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_comment_blame_post_writer_title' => '[{게시판명}] {게시글제목} - 댓글에신고가접수되었습니다',
			'send_email_comment_blame_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />댓글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_email_comment_blame_comment_writer_title' => '[{게시판명}] {게시글제목} - 댓글에신고가접수되었습니다',
			'send_email_comment_blame_comment_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />댓글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_comment_blame_admin_title' => '[{게시판명}] {게시글제목} - 댓글에신고가접수되었습니다',
			'send_note_comment_blame_admin_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />댓글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_comment_blame_post_writer_title' => '[{게시판명}] {게시글제목} - 댓글에신고가접수되었습니다',
			'send_note_comment_blame_post_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />댓글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'send_note_comment_blame_comment_writer_title' => '[{게시판명}] {게시글제목} - 댓글에신고가접수되었습니다',
			'send_note_comment_blame_comment_writer_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td width="200" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">{게시글제목}</span><br />댓글에 신고가 접수되었습니다</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td colspan="2" style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div>{댓글내용}</div><p><a href="{댓글주소}" target="_blank" style="font-weight:bold;">사이트에서 댓글 확인하기</a></p><p>&nbsp;</p></td></tr></table>',
			'member_dormant_days' => '365',
			'member_dormant_method' => 'archive',
			'member_dormant_auto_clean' => '1',
			'member_dormant_auto_email' => '1',
			'member_dormant_auto_email_days' => '30',
			'send_email_dormant_notify_user_title' => '[{홈페이지명}] 휴면 계정 전환 예정 안내',
			'send_email_dormant_notify_user_content' => '<table width="100%" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tbody><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><span style="font-size:14px;font-weight:bold;color:rgb(0,0,0)">안녕하세요 {회원닉네임}님,</span><br>항상 믿고 이용해주시는 회원님께 깊은 감사를 드립니다.</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><p>{정리기준} 이상 서비스를 이용하지 않은 계정 ‘정보통신망 이용 촉진 및 정보보호 등에 관한 법률 및 시행령 제16조에 따라 휴면 계정으로 전환되며, 해당 계정 정보는 별도 분리 보관될 예정입니다. </p><p>(법령 시행일 : 2015년 8월 18일)</P><p>&nbsp;</p><p><strong>1. 적용 대상 :</strong> {정리기준}간 로그인 기록이 없는 고객의 개인정보</p><p><strong>2. 적용 시점 :</strong> {정리예정날짜}</p><p><strong>3. 처리 방법 :</strong> {정리방법}</p><p>&nbsp;</p><p>{홈페이지명}에서는 앞으로도 회원님의 개인정보를 소중하게 관리하여 보다 더 안전하게 서비스를 이용하실 수 있도록 최선의 노력을 다하겠습니다. 많은 관심과 참여 부탁 드립니다. 감사합니다.</p></td></tr><tr><td style="padding:10px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;text-align:center;">{홈페이지명}</td></tr></tbody></table>',
			'cb_version' => CB_VERSION,
			'site_meta_title_tag' => '{태그명} - {홈페이지제목}',
			'send_sms_register_admin_content' => '[회원가입알림] {회원닉네임}님이 회원가입하셨습니다',
			'send_sms_register_user_content' => '[{홈페이지명}] 회원가입을 축하드립니다. 감사합니다',
			'send_sms_changepw_admin_content' => '[패스워드변경알림] {회원닉네임}님이 패스워드를변경하셨습니다',
			'send_sms_changepw_user_content' => '[{홈페이지명}] 회원님의 패스워드가 변경되었습니다. 감사합니다',
			'send_sms_memberleave_admin_content' => '[회원탈퇴알림] {회원닉네임}님이 회원탈퇴하셨습니다',
			'send_sms_memberleave_user_content' => '[{홈페이지명}] 회원탈퇴완료 - 그동안이용해주셔서감사합니다',
			'send_sms_post_admin_content' => '[게시글작성알림] {게시판명} - {게시글제목}',
			'send_sms_post_writer_content' => '[게시글작성알림] {게시판명} - {게시글제목}',
			'send_sms_comment_admin_content' => '[댓글작성알림] {게시판명} - {게시글제목}',
			'send_sms_comment_post_writer_content' => '[댓글작성알림] {게시판명} - {게시글제목}',
			'send_sms_comment_comment_writer_content' => '[댓글작성알림] {게시판명} - {게시글제목}',
			'send_sms_blame_admin_content' => '[게시글신고알림] {게시판명} - {게시글제목}',
			'send_sms_blame_post_writer_content' => '[게시글신고알림] {게시판명} - {게시글제목}',
			'send_sms_comment_blame_admin_content' => '[댓글신고알림] {게시판명} - {게시글제목}',
			'send_sms_comment_blame_post_writer_content' => '[댓글신고알림] {게시판명} - {게시글제목}',
			'send_sms_comment_blame_comment_writer_content' => '[댓글신고알림] {게시판명} - {게시글제목}',
		);
		$registerform = array(
			'mem_userid' => array(
				'field_name' => 'mem_userid',
				'func' => 'basic',
				'display_name' => '아이디',
				'field_type' => 'text',
				'use' => '1',
				'open' => '1',
				'required' => '1',
			),
			'mem_email' => array(
				'field_name' => 'mem_email',
				'func' => 'basic',
				'display_name' => '이메일주소',
				'field_type' => 'email',
				'use' => '1',
				'open' => '',
				'required' => '1',
			),
			'mem_password' => array(
				'field_name' => 'mem_password',
				'func' => 'basic',
				'display_name' => '비밀번호',
				'field_type' => 'password',
				'use' => '1',
				'open' => '',
				'required' => '1',
			),
			'mem_nickname' => array(
				'field_name' => 'mem_nickname',
				'func' => 'basic',
				'display_name' => '닉네임',
				'field_type' => 'text',
				'use' => '1',
				'open' => '1',
				'required' => '1',
			),
			'mem_username' => array(
				'field_name' => 'mem_username',
				'func' => 'basic',
				'display_name' => '이름',
				'field_type' => 'text',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_homepage' => array(
				'field_name' => 'mem_homepage',
				'func' => 'basic',
				'display_name' => '홈페이지',
				'field_type' => 'url',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_phone' => array(
				'field_name' => 'mem_phone',
				'func' => 'basic',
				'display_name' => '전화번호',
				'field_type' => 'phone',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_birthday' => array(
				'field_name' => 'mem_birthday',
				'func' => 'basic',
				'display_name' => '생년월일',
				'field_type' => 'date',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_sex' => array(
				'field_name' => 'mem_sex',
				'func' => 'basic',
				'display_name' => '성별',
				'field_type' => 'radio',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_address' => array(
				'field_name' => 'mem_address',
				'func' => 'basic',
				'display_name' => '주소',
				'field_type' => 'address',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_profile_content' => array(
				'field_name' => 'mem_profile_content',
				'func' => 'basic',
				'display_name' => '자기소개',
				'field_type' => 'textarea',
				'use' => '',
				'open' => '',
				'required' => '',
			),
			'mem_recommend' => array(
				'field_name' => 'mem_recommend',
				'func' => 'basic',
				'display_name' => '추천인',
				'field_type' => 'text',
				'use' => '',
				'open' => '',
				'required' => '',
			),
		);
		$configdata['registerform'] = json_encode($registerform);

		$scheduler = array(
			'Sample_scheduler' => array(
				'library_name' => 'Sample_scheduler',
				'interval_field_name' => 'hourly',
			),
		);

		$interval = array(
			'hourly' => array(
				'field_name' => 'hourly',
				'interval' => '3600',
				'display_name' => '매시간마다',
			),
			'twicedaily' => array(
				'field_name' => 'twicedaily',
				'interval' => '43200',
				'display_name' => '하루에2번',
			),
			'daily' => array(
				'field_name' => 'daily',
				'interval' => '86400',
				'display_name' => '하루에1번',
			),
		);
		$configdata['scheduler'] = json_encode($scheduler, true);
		$configdata['scheduler_interval'] = json_encode($interval, true);

		$this->cache->delete('config-model-get');
		$this->cache->clean();
		$this->Config_model->save($configdata);


		$hash = password_hash($this->input->post('mem_password'), PASSWORD_BCRYPT);
		$insertdata = array(
			'mem_userid' => $this->input->post('mem_userid'),
			'mem_email' => $this->input->post('mem_email'),
			'mem_password' => $hash,
			'mem_username' => $this->input->post('mem_nickname'),
			'mem_nickname' => $this->input->post('mem_nickname'),
			'mem_level' => 100,
			'mem_receive_email' => 1,
			'mem_use_note' => 1,
			'mem_receive_sms' => 1,
			'mem_open_profile' => 1,
			'mem_email_cert' => 1,
			'mem_register_datetime' => cdate('Y-m-d H:i:s'),
			'mem_register_ip' => $this->input->ip_address(),
			'mem_lastlogin_datetime' => cdate('Y-m-d H:i:s'),
			'mem_lastlogin_ip' => $this->input->ip_address(),
			'mem_is_admin' => 1,
		);
		$mem_id = $this->Member_model->insert($insertdata);

		$useriddata = array(
			'mem_id' => $mem_id,
			'mem_userid' => $this->input->post('mem_userid'),
		);
		$this->Member_userid_model->insert($useriddata);

		$membermeta = array(
			'meta_change_pw_datetime' => cdate('Y-m-d H:i:s'),
			'meta_email_cert_datetime' => cdate('Y-m-d H:i:s'),
			'meta_open_profile_datetime' => cdate('Y-m-d H:i:s'),
			'meta_use_note_datetime' => cdate('Y-m-d H:i:s'),
			'meta_nickname_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Member_meta_model->save($mem_id, $membermeta);

		$insertdata = array(
			'mem_id' => $mem_id,
			'mni_nickname' => $this->input->post('mem_nickname'),
			'mni_start_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Member_nickname_model->insert($insertdata);

		$insertdata = array(
			'mem_id' => $mem_id,
			'mrg_ip' => $this->input->ip_address(),
			'mrg_datetime' => cdate('Y-m-d H:i:s'),
			'mrg_useragent' => $this->agent->agent_string(),
		);
		$this->Member_register_model->insert($insertdata);

		$insertdata = array(
			'doc_key' => 'aboutus',
			'doc_title' => '조합소개',
			'doc_content' => '조합소개 내용을 입력해주세요',
			'doc_content_html_type' => 1,
			'mem_id' => $mem_id,
			'doc_datetime' => cdate('Y-m-d H:i:s'),
			'doc_updated_mem_id' => $mem_id,
			'doc_updated_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Document_model->insert($insertdata);

		$insertdata = array(
			'doc_key' => 'provision',
			'doc_title' => '이용약관',
			'doc_content' => '이용약관 내용을 입력해주세요',
			'doc_content_html_type' => 1,
			'mem_id' => $mem_id,
			'doc_datetime' => cdate('Y-m-d H:i:s'),
			'doc_updated_mem_id' => $mem_id,
			'doc_updated_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Document_model->insert($insertdata);

		$insertdata = array(
			'doc_key' => 'privacy',
			'doc_title' => '개인정보 취급방침',
			'doc_content' => '개인정보 취급방침 내용을 입력해주세요',
			'doc_content_html_type' => 1,
			'mem_id' => $mem_id,
			'doc_datetime' => cdate('Y-m-d H:i:s'),
			'doc_updated_mem_id' => $mem_id,
			'doc_updated_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Document_model->insert($insertdata);
		
		$insertdata = array(
			'doc_key' => 'info',
			'doc_title' => '사업개요',
			'doc_content' => '사업개요 및 조감도 등 내용을 입력해주세요',
			'doc_content_html_type' => 1,
			'mem_id' => $mem_id,
			'doc_datetime' => cdate('Y-m-d H:i:s'),
			'doc_updated_mem_id' => $mem_id,
			'doc_updated_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Document_model->insert($insertdata);
		
		$insertdata = array(
			'doc_key' => 'brand',
			'doc_title' => '브랜드 소개',
			'doc_content' => '브랜드 및 특장점 내용을 입력해주세요',
			'doc_content_html_type' => 1,
			'mem_id' => $mem_id,
			'doc_datetime' => cdate('Y-m-d H:i:s'),
			'doc_updated_mem_id' => $mem_id,
			'doc_updated_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Document_model->insert($insertdata);
		
		$insertdata = array(
			'doc_key' => 'map',
			'doc_title' => '오시는길',
			'doc_content' => '찾아오시는 길 내용을 입력해주세요',
			'doc_content_html_type' => 1,
			'mem_id' => $mem_id,
			'doc_datetime' => cdate('Y-m-d H:i:s'),
			'doc_updated_mem_id' => $mem_id,
			'doc_updated_datetime' => cdate('Y-m-d H:i:s'),
		);
		$this->Document_model->insert($insertdata);



		$insertdata = array(
			'mgr_title' => '승인대기',
			'mgr_is_default' => 1,
			'mgr_datetime' => cdate('Y-m-d H:i:s'),
			'mgr_order' => 1,
		);
		$mgr_id = $this->Member_group_model->insert($insertdata);
		$insertdata = array(
			'mgr_title' => '조합원',
			'mgr_is_default' => 0,
			'mgr_datetime' => cdate('Y-m-d H:i:s'),
			'mgr_order' => 2,
		);
		$this->Member_group_model->insert($insertdata);
		$insertdata = array(
			'mgr_title' => '스태프',
			'mgr_is_default' => 0,
			'mgr_datetime' => cdate('Y-m-d H:i:s'),
			'mgr_order' => 3,
		);
		$this->Member_group_model->insert($insertdata);

		if ($this->input->post('autocreate')) {
			
			$insertdata = array(
				'fgr_title' => '자주하는 질문',
				'fgr_key' => 'faq-01',
				'fgr_datetime' => cdate('Y-m-d H:i:s'),
				'fgr_ip' => $this->input->ip_address(),
				'mem_id' => $mem_id,
			);
			$fgr_id = $this->Faq_group_model->insert($insertdata);
			
			$insertdata = array(
				'fgr_id' => $fgr_id,
				'faq_title' => '우리 조합의 조합원 자격 요건(기준)은 어떻게 되나요?',
				'faq_content' => '<p><span style="font-size: 10pt;"><u>지역주택조합의 조합원 자격은 주택법 시행령 제21조에 규정이 되어 있습니다.</u></span></p><table border="0" cellpadding="0" cellspacing="0" class="__se_tbl" style="border-width: 1px 1px 0px 0px; border-top-style: solid; border-right-style: solid; border-top-color: rgb(204, 204, 204); border-right-color: rgb(204, 204, 204); border-image: initial; border-left-style: initial; border-left-color: initial; border-bottom-style: initial; border-bottom-color: initial;"><tbody><tr><td width="1069" style="border-width: 0px 0px 1px 1px; border-bottom-style: solid; border-left-style: solid; border-bottom-color: rgb(204, 204, 204); border-left-color: rgb(204, 204, 204); border-image: initial; border-top-style: initial; border-top-color: initial; border-right-style: initial; border-right-color: initial; background-color: rgb(255, 255, 255); padding: 10px;"><p>&nbsp;<span class="bl" style="font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px; color: rgb(21, 21, 148); margin: 0px; padding: 0px; font-weight: bold;">제21조(조합원의 자격)</span><span style="color: rgb(99, 99, 99); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px;">&nbsp;①&nbsp;</span><a title="팝업으로 이동" class="link sfon1" style="font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px; color: rgb(8, 109, 255); margin: 0px; padding: 0px; text-decoration-line: underline;">법</a><span style="color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px;">&nbsp;</span><a title="팝업으로 이동" class="link sfon2" style="font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px; color: rgb(0, 0, 205); margin: 0px; padding: 0px; text-decoration-line: underline;">제11조</a><span style="color: rgb(99, 99, 99); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px;">에 따른 주택조합의 조합원이 될 수 있는 사람은 다음 각 호의 구분에 따른 사람으로 한다. 다만, 조합원의 사망으로 그 지위를 상속받는 자는 다음 각 호의 요건에도 불구하고 조합원이 될 수 있다. &nbsp;</span><span class="sfon" style="font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -30px; color: rgb(2, 79, 206); margin: 0px; padding: 0px;">&lt;개정 2019. 10. 22.&gt;</span>&nbsp;</p><p class="pty1_de2h" style="padding-left: 48px; text-indent: -15px; color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px;"><span style="color: rgb(99, 99, 99);">1.&nbsp;</span><a class="oneView" style="color: rgb(0, 90, 132); margin: 0px; padding: 0px;">지역주택조합 조합원</a><span style="color: rgb(99, 99, 99);">: 다음 각 목의 요건을 모두 갖춘 사람</span></p><p class="pty1_de3" style="padding-left: 65px; text-indent: -17px; color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px;"><span style="color: rgb(99, 99, 99);">가. 조합설립인가 신청일(해당 주택건설대지가&nbsp;</span><a title="팝업으로 이동" class="link sfon1" style="color: rgb(8, 109, 255); margin: 0px; padding: 0px; text-decoration-line: underline;">법</a>&nbsp;<a title="팝업으로 이동" class="link sfon2" style="color: rgb(0, 0, 205); margin: 0px; padding: 0px; text-decoration-line: underline;">제63조</a><span style="color: rgb(99, 99, 99);">에 따른 투기과열지구 안에 있는 경우에는 조합설립인가 신청일 1년 전의 날을 말한다. 이하 같다)부터 해당 조합주택의 입주 가능일까지 주택을 소유(주택의 유형, 입주자 선정방법 등을 고려하여&nbsp;</span><a title="팝업으로 이동" class="link" style="color: rgb(0, 90, 132); margin: 0px; padding: 0px; text-decoration-line: underline;">국토교통부령</a><span style="color: rgb(99, 99, 99);">으로 정하는 지위에 있는 경우를 포함한다. 이하 이 호에서 같다)하는지에 대하여 다음의 어느 하나에 해당할 것</span><span class="sfon" style="color: rgb(2, 79, 206); margin: 0px; padding: 0px;"></span></p><p class="pty1_de3" style="padding-left: 65px; text-indent: -17px; color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px;"><span style="color: rgb(99, 99, 99);">　 1)&nbsp;</span><a title="팝업으로 이동" class="link" style="color: rgb(0, 90, 132); margin: 0px; padding: 0px; text-decoration-line: underline;">국토교통부령</a><span style="color: rgb(99, 99, 99);">으로 정하는 기준에 따라 세대주를 포함한 세대원[세대주와 동일한 세대별 주민등록표에 등재되어 있지 아니한 세대주의 배우자 및 그 배우자와 동일한 세대를 이루고 있는 사람을 포함한다. 이하 2)에서 같다] 전원이 주택을 소유하고 있지 아니한 세대의 세대주일 것</span><span class="sfon" style="color: rgb(2, 79, 206); margin: 0px; padding: 0px;"></span></p><p class="pty1_de3" style="padding-left: 65px; text-indent: -17px; color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px;"><span style="color: rgb(99, 99, 99);">　 2)&nbsp;</span><a title="팝업으로 이동" class="link" style="color: rgb(0, 90, 132); margin: 0px; padding: 0px; text-decoration-line: underline;">국토교통부령</a><span style="color: rgb(99, 99, 99);">으로 정하는 기준에 따라 세대주를 포함한 세대원 중 1명에 한정하여 주거전용면적 85제곱미터 이하의 주택 1채를 소유한 세대의 세대주일 것</span><span class="sfon" style="color: rgb(2, 79, 206); margin: 0px; padding: 0px;"></span></p><p class="pty1_de3" style="padding-left: 65px; text-indent: -17px; color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px;"><span style="color: rgb(99, 99, 99);">나. 조합설립인가 신청일 현재&nbsp;</span><a title="팝업으로 이동" class="link sfon1" style="color: rgb(8, 109, 255); margin: 0px; padding: 0px; text-decoration-line: underline;">법</a>&nbsp;<a title="팝업으로 이동" class="link sfon2" style="color: rgb(0, 0, 205); margin: 0px; padding: 0px; text-decoration-line: underline;">제2조</a><a title="팝업으로 이동" class="link sfon4" style="color: rgb(0, 0, 105); margin: 0px; padding: 0px; text-decoration-line: underline;">제11호</a><a title="팝업으로 이동" class="link sfon5" style="color: rgb(95, 146, 160); margin: 0px; padding: 0px; text-decoration-line: underline;">가목</a><span style="color: rgb(99, 99, 99);">의 구분에 따른 지역에 6개월 이상 계속하여 거주하여 온 사람일 것</span><span class="sfon" style="color: rgb(2, 79, 206); margin: 0px; padding: 0px;"></span></p><p class="pty1_de3" style="padding-left: 65px; text-indent: -17px; color: rgb(68, 68, 68); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px;"><span style="color: rgb(99, 99, 99);">다. 본인 또는 본인과 같은 세대별 주민등록표에 등재되어 있지 않은 배우자가 같은 또는 다른 지역주택조합의 조합원이거나 직장주택조합의 조합원이 아닐 것</span></p></td></tr></tbody></table><p><br></p><p><br></p><p><span style="font-size: 10pt;"><u>우리 조합은 <span style="color: rgb(0, 117, 200);"><b>2019. 07. 30. </b></span>조합설립인가를 신청하였고, 투기과열지구에 해당하지 않으므로 조합원 자격 요건은 다음과 같습니다.</u></span></p><table class="__se_tbl" _se2_tbl_template="4" border="0" cellpadding="0" cellspacing="0" style="border: 1px solid rgb(199, 199, 199);"><tbody><tr><td class="" style="width: 134px; height: 18px; padding: 3px 4px 2px; color: rgb(102, 102, 102); border-right: 1px solid rgb(231, 231, 231); background-color: rgb(243, 243, 243);"><p style="text-align: center; margin-left: 0px;">구분</p></td><td class="" style="width: 234px; height: 18px; padding: 3px 4px 2px; color: rgb(102, 102, 102); border-right: 1px solid rgb(231, 231, 231); background-color: rgb(243, 243, 243);"><p style="text-align: center; margin-left: 0px;">기간&nbsp;</p></td><td class="" style="width: 697px; height: 18px; padding: 3px 4px 2px; color: rgb(102, 102, 102); border-right: 1px solid rgb(231, 231, 231); background-color: rgb(243, 243, 243);"><p style="text-align: center; margin-left: 0px;" align="center">내용</p></td></tr><tr><td class="" style="width: 134px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="text-align: center; margin-left: 0px;">거주요건</p></td><td class="" style="width: 234px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="text-align: center; margin-left: 0px;">2019. 01. 30. - <span style="color: rgb(0, 117, 200);"><b>2019. 07. 30.</b></span></p></td><td class="" style="width: 697px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="margin-left: 40px;">해당 기간 동안 <span style="color: rgb(0, 117, 200);">서울특별시</span><span style="color: rgb(0, 117, 200); font-family: Gulim, doutm, tahoma, sans-serif; font-size: 13.2px; text-indent: -2px;">ㆍ</span><span style="color: rgb(0, 117, 200);">인천광역시 및 경기도</span> 거주</p></td></tr><tr><td class="" style="width: 134px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="text-align: center; margin-left: 0px;">주택소유 요건</p></td><td class="" colspan="1" rowspan="3" style="width: 234px; height: 41px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p align="left" style="text-align: center; margin-left: 0px;"><b><span style="color: rgb(0, 117, 200);">2019. 07. 30.</span></b> - 입주가능일 까지</p></td><td class="" style="width: 697px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="margin-left: 40px;">해당 기간 동안 본인 및 배우자, 세대원 전원이 무주택 또는 전용면적 85제곱미터 이하의 주택(분양권 포함) 1채를 소유</p></td></tr><tr><td class="" style="width: 134px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="text-align: center; margin-left: 0px;">세대주 요건</p></td><td class="" style="width: 697px; height: 18px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="margin-left: 40px;">해당 기간 동안 연속적으로 본인이 세대주일 것</p></td></tr><tr><td class="" rowspan="1" colspan="1" style="width: 5px; height: 5px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="text-align: center; margin-left: 0px;" align="center">중복가입금지</p></td><td class="" rowspan="1" colspan="1" style="width: 342px; height: 5px; padding: 3px 4px 2px; border-top: 1px solid rgb(231, 231, 231); border-right: 1px solid rgb(231, 231, 231); color: rgb(102, 102, 102); background-color: rgb(255, 255, 255);"><p style="margin-left: 40px;">해당 기간 동안 본인 및 배우자가 다른 지역주택조합 또는 직장주택조합의 조합원이 아닐 것</p></td></tr></tbody></table>',
				'faq_content_html_type' => 1,
				'faq_order' => 1,
				'faq_datetime' => cdate('Y-m-d H:i:s'),
				'faq_ip' => $this->input->ip_address(),
				'mem_id' => $mem_id,
			);
			$this->Faq_model->insert($insertdata);


			$metadata = array(
				'header_content' => '',
				'footer_content' => '',
				'mobile_header_content' => '',
				'mobile_footer_content' => '',
			);
			
			$insertdata = array(
				'bgr_key' => 'information',
				'bgr_name' => '조합 공개 자료',
				'bgr_order' => 1,
			);
			$bgr_id_1 = $bgr_id = $this->Board_group_model->insert($insertdata);
			$this->Board_group_meta_model->save($bgr_id, $metadata);
			
			$insertdata = array(
				'bgr_key' => 'community',
				'bgr_name' => '조합원 커뮤니티',
				'bgr_order' => 2,
			);
			$bgr_id_2 = $bgr_id = $this->Board_group_model->insert($insertdata);
			$this->Board_group_meta_model->save($bgr_id, $metadata);
			
			$metadata = array(
				'header_content' => '',
				'footer_content' => '',
				'mobile_header_content' => '',
				'mobile_footer_content' => '',
				'order_by_field' => 'post_num, post_reply',
				'list_count' => '20',
				'mobile_list_count' => '10',
				'page_count' => '5',
				'mobile_page_count' => '3',
				'show_list_from_view' => '1',
				'new_icon_hour' => '24',
				'hot_icon_hit' => '100',
				'hot_icon_day' => '30',
				'subject_length' => '60',
				'mobile_subject_length' => '40',
				'reply_order' => 'asc',
				'gallery_cols' => '4',
				'gallery_image_width' => '120',
				'gallery_image_height' => '90',
				'mobile_gallery_cols' => '2',
				'mobile_gallery_image_width' => '120',
				'mobile_gallery_image_height' => '90',
				'use_scrap' => '1',
				'use_post_like' => '1',
				'use_post_dislike' => '1',
				'use_print' => '1',
				'use_sns' => '1',
				'use_prev_next_post' => '1',
				'use_mobile_prev_next_post' => '1',
				'use_blame' => '1',
				'blame_blind_count' => '3',
				'syntax_highlighter' => '1',
				'comment_syntax_highlighter' => '1',
				'use_autoplay' => '1',
				'post_image_width' => '700',
				'post_mobile_image_width' => '400',
				'content_target_blank' => '1',
				'use_auto_url' => '1',
				'use_mobile_auto_url' => '1',
				'use_post_dhtml' => '1',
				'link_num' => '2',
				'use_upload_file' => '1',
				'upload_file_num' => '2',
				'mobile_upload_file_num' => '2',
				'upload_file_max_size' => '32',
				'comment_count' => '20',
				'mobile_comment_count' => '20',
				'comment_page_count' => '5',
				'mobile_comment_page_count' => '3',
				'use_comment_like' => '1',
				'use_comment_dislike' => '1',
				'use_comment_secret' => '1',
				'comment_order' => 'asc',
				'use_comment_blame' => '1',
				'comment_blame_blind_count' => '3',
				'protect_comment_num' => '5',
				'use_sideview' => '1',
				'use_tempsave' => '1',
				'list_date_style' => 'sns',
				'view_date_style' => 'user',
				'view_date_style_manual' => 'Y-m-d H:i:s',
				'comment_date_style' => 'sns',
				'mobile_list_date_style' => 'sns',
				'mobile_comment_date_style' => 'sns',
				'use_poll' => '',
				'use_mobile_poll' => '',
			);
			
			$info_auth = array(
				'access_blame' => 2, // 게시물신고가능
				'access_blame_group' => json_encode(["2", "3"]),
				'access_comment' => 2, // 댓글 작성
				'access_comment_group' => json_encode(["2", "3"]),
				'access_dhtml' => 2, // DHTML 에디터사용
				'access_dhtml_group' => json_encode(["2", "3"]),
				'access_download' => 2, // 파일다운로드
				'access_download_group' => json_encode(["2", "3"]),
				'access_list' => 2, // 목록
				'access_list_group' => json_encode(["2", "3"]),
				'access_reply' => 2, // 답변 작성
				'access_reply_group'=> json_encode(["3"]),
				'access_subject_style' => 2, // 제목스타일사용가능
				'access_subject_style_group'=> json_encode(["3"]),
				'access_tag_write'=> 2, // 태그입력가능
				'access_tag_write_group' => json_encode(["3"]),
				'access_upload'=> 2, // 파일업로드
				'access_upload_group'  => json_encode(["3"]),
				'access_view'=> 2, // 글열람
				'access_view_group' => json_encode(["2", "3"]),
				'access_write' => 2,  // 글 작성
				'access_write_group'=> json_encode(["3"]),
			);
			
			$metadata = array_merge($metadata, $info_auth);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-01',
				'brd_name' => '조합규약 및 내규',
				'brd_order' => 1,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-02',
				'brd_name' => '공동사업주체와 체결한 협약서',
				'brd_order' => 2,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-03',
				'brd_name' => '설계자 등 용역업체 선정 계약서',
				'brd_order' => 3,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-04',
				'brd_name' => '조합총회 및 이사회 등의 의사록',
				'brd_order' => 4,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-05',
				'brd_name' => '사업시행계획서',
				'brd_order' => 5,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-06',
				'brd_name' => '조합사업의 시행에 관한 공문서',
				'brd_order' => 6,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-07',
				'brd_name' => '회계감사보고서',
				'brd_order' => 7,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-08',
				'brd_name' => '분기별 사업실적보고서',
				'brd_order' => 8,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-09',
				'brd_name' => '업무대행자가 제출한 실적보고서',
				'brd_order' => 9,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-10',
				'brd_name' => '연간 자금운용 계획서',
				'brd_order' => 10,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-11',
				'brd_name' => '월별 자금 입출금 명세서',
				'brd_order' => 11,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-12',
				'brd_name' => '월별 공사진행 상황에 관한 서류',
				'brd_order' => 12,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-13',
				'brd_name' => '분양신청에 관한 서류 및 관련 자료',
				'brd_order' => 13,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-14',
				'brd_name' => '조합원별 분담금 납부내역',
				'brd_order' => 14,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_1,
				'brd_key' => 'info-15',
				'brd_name' => '조합원별 추가 분담금 산출내역',
				'brd_order' => 15,
				'brd_search' => 1,
			);
			
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$metadata['access_list'] = 1;
			$post_notice = array(
				'send_email_post_board_admin' => 1,
				'send_email_post_group_admin' => 1,
				'send_email_post_super_admin' => 1,
				'send_note_post_board_admin' => 1,
				'send_note_post_group_admin' => 1,
				'send_note_post_super_admin' => 1,
				'send_sms_post_board_admin' => 1,
				'send_sms_post_group_admin' => 1 ,
				'send_sms_post_super_admin' => 1,
			);
			$insertdata = array(
				'bgr_id' => $bgr_id_2,
				'brd_key' => 'notice',
				'brd_name' => '공지 사항',
				'brd_order' => 21,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata + $post_notice);
			
			$insertdata = array(
				'bgr_id' => $bgr_id_2,
				'brd_key' => 'storage',
				'brd_name' => '조합 자료실',
				'brd_order' => 22,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$use_secret = array('use_post_secret' => 1);
			$metadata['access_write_group'] = json_encode(["2", "3"]);
			$metadata['access_upload_group'] = json_encode(["2", "3"]);
			$insertdata = array(
				'bgr_id' => $bgr_id_2,
				'brd_key' => 'qna',
				'brd_name' => '질문 게시판',
				'brd_order' => 23,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata + $use_secret + $post_notice);
			
			$metadata['access_reply_group'] = json_encode(["2", "3"]);
			$insertdata = array(
				'bgr_id' => $bgr_id_2,
				'brd_key' => 'free',
				'brd_name' => '자유 게시판',
				'brd_order' => 24,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$metadata['access_write_group'] = json_encode(["3"]);
			$metadata['access_reply_group'] = json_encode(["3"]);
			$metadata['access_upload_group'] = json_encode(["3"]);
			$metadata['use_poll'] = '1';
			$metadata['use_mobile_poll'] = '1';
			$metadata['access_poll_write'] = 2;
			$metadata['access_poll_write_group'] = json_encode(["3"]);
			$insertdata = array(
				'bgr_id' => $bgr_id_2,
				'brd_key' => 'poll',
				'brd_name' => '설문 게시판',
				'brd_order' => 25,
				'brd_search' => 0,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata);
			
			$metadata['use_poll'] = '';
			$metadata['use_mobile_poll'] = '';
			$metadata['access_poll_write'] = 1;
			$metadata['access_poll_write_group'] = "";
			
			$metadata['access_view_group'] = json_encode(["1", "2", "3"]);
			$metadata['access_write_group'] = json_encode(["1", "2", "3"]);
			$metadata['access_reply_group'] = json_encode(["1", "2", "3"]);
			$metadata['access_comment_group'] = json_encode(["1", "2", "3"]);
			$metadata['access_upload_group'] = json_encode(["1", "2", "3"]);
			$metadata['access_download_group'] = json_encode(["1", "2", "3"]);
			$metadata['access_dhtml_group'] = json_encode(["1", "2", "3"]);
			$metadata['use_post_secret_selected'] = 1;
			$insertdata = array(
				'bgr_id' => $bgr_id_2,
				'brd_key' => 'ask-cert',
				'brd_name' => '조합원 인증 요청',
				'brd_order' => 26,
				'brd_search' => 1,
			);
			$brd_id = $this->Board_model->insert($insertdata);
			$this->Board_meta_model->save($brd_id, $metadata + $use_secret + $post_notice);
			
			
			$insertdata = array(
				'men_parent' => 0,
				'men_name' => '사업 안내',
				'men_link' => document_url('info'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 0,
			);
			$men_id = $this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '사업개요',
				'men_link' => document_url('info'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 0,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '브랜드 소개',
				'men_link' => document_url('brand'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 1,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '오시는길',
				'men_link' => document_url('map'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 2,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => 0,
				'men_name' => '조합 공개 자료',
				'men_link' => group_url('information'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 1,
			);
			$men_id = $this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합규약 및 내규',
				'men_link' => board_url('info-01'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 0,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '공동사업주체와 체결한 협약서',
				'men_link' => board_url('info-02'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 1,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '설계자 등 용역업체 선정 계약서',
				'men_link' => board_url('info-03'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 2,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합총회 및 이사회 등의 의사록',
				'men_link' => board_url('info-04'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 3,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '사업시행계획서',
				'men_link' => board_url('info-05'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 4,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합사업의 시행에 관한 공문서',
				'men_link' => board_url('info-06'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 5,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '회계감사보고서',
				'men_link' => board_url('info-07'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 6,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '분기별 사업실적보고서',
				'men_link' => board_url('info-08'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 7,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '업무대행자가 제출한 실적보고서',
				'men_link' => board_url('info-09'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 8,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '연간 자금운용 계획서',
				'men_link' => board_url('info-10'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 9,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '월별 자금 입출금 명세서',
				'men_link' => board_url('info-11'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 10,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '월별 공사진행 상황에 관한 서류',
				'men_link' => board_url('info-12'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 11,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '분양신청에 관한 서류 및 관련 자료',
				'men_link' => board_url('info-13'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 12,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합원별 분담금 납부내역',
				'men_link' => board_url('info-14'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 13,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합원별 추가 분담금 산출내역',
				'men_link' => board_url('info-15'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 14,
			);
			$this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => 0,
				'men_name' => '조합원 커뮤니티',
				'men_link' => group_url('community'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 2,
			);
			$men_id = $this->Menu_model->insert($insertdata);
			
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '공지 사항',
				'men_link' => board_url('notice'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 0,
			);
			$this->Menu_model->insert($insertdata);
//
			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합 자료실',
				'men_link' => board_url('storage'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 1,
			);
			$this->Menu_model->insert($insertdata);

			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '질문 게시판',
				'men_link' => board_url('qna'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 2,
			);
			$this->Menu_model->insert($insertdata);

			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '자유 게시판',
				'men_link' => board_url('free'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 3,
			);
			$this->Menu_model->insert($insertdata);

			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '투표(설문) 코너',
				'men_link' => poll_url('poll'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 4,
			);
			$this->Menu_model->insert($insertdata);

			$insertdata = array(
				'men_parent' => $men_id,
				'men_name' => '조합원 인증 요청',
				'men_link' => board_url('ask-cert'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 5,
			);
			$this->Menu_model->insert($insertdata);

			$insertdata = array(
				'men_parent' => 0,
				'men_name' => '자주하는질문',
				'men_link' => faq_url('faq-01'),
				'men_desktop' => 1,
				'men_mobile' => 1,
				'men_order' => 3,
			);
			$men_id = $this->Menu_model->insert($insertdata);

		}

		$this->session->set_userdata(
			'mem_id',
			$mem_id
		);
	}


	public function _insert_pro_init_data()
	{
		$this->load->library(array('user_agent', 'session'));
		$this->load->model(array(
			'Config_model', 'Member_group_model', 'Document_model'
		));

		if ( ! function_exists('password_hash')) {
			$this->load->helper('password');
		}

		$this->load->driver('cache', config_item('cache_method'));

		$skin = $this->input->post('skin');
		$skin_cmall = 'cmall_' . $this->input->post('skin');
		$skin_mobile = $this->input->post('skin') === 'basic' ? 'mobile' : 'bootstrap';

		$configdata = array(
			'use_pointranking' => '',
			'use_poll_list' => '1',
			'site_meta_title_tag' => '{태그명} - {홈페이지제목}',
			'site_meta_title_levelup' => '레벨업 - {홈페이지제목}',
			'site_meta_title_pointranking' => '전체 포인트 랭킹 - {홈페이지제목}',
			'site_meta_title_pointranking_month' => '월별 포인트 랭킹 - {홈페이지제목}',
			'site_meta_title_poll' => '설문조사모음 - {홈페이지제목}',
			'site_meta_title_attendance' => '출석체크 - {홈페이지제목}',
			'send_sms_register_admin_content' => '[회원가입알림] {회원닉네임}님이 회원가입하셨습니다',
			'send_sms_register_user_content' => '[{홈페이지명}] 회원가입을 축하드립니다. 감사합니다',
			'send_sms_changepw_admin_content' => '[패스워드변경알림] {회원닉네임}님이 패스워드를변경하셨습니다',
			'send_sms_changepw_user_content' => '[{홈페이지명}] 회원님의 패스워드가 변경되었습니다. 감사합니다',
			'send_sms_memberleave_admin_content' => '[회원탈퇴알림] {회원닉네임}님이 회원탈퇴하셨습니다',
			'send_sms_memberleave_user_content' => '[{홈페이지명}] 회원탈퇴완료 - 그동안이용해주셔서감사합니다',
			'send_sms_post_admin_content' => '[게시글작성알림] {게시판명} - {게시글제목}',
			'send_sms_post_writer_content' => '[게시글작성알림] {게시판명} - {게시글제목}',
			'send_sms_comment_admin_content' => '[댓글작성알림] {게시판명} - {게시글제목}',
			'send_sms_comment_post_writer_content' => '[댓글작성알림] {게시판명} - {게시글제목}',
			'send_sms_comment_comment_writer_content' => '[댓글작성알림] {게시판명} - {게시글제목}',
			'send_sms_blame_admin_content' => '[게시글신고알림] {게시판명} - {게시글제목}',
			'send_sms_blame_post_writer_content' => '[게시글신고알림] {게시판명} - {게시글제목}',
			'send_sms_comment_blame_admin_content' => '[댓글신고알림] {게시판명} - {게시글제목}',
			'send_sms_comment_blame_post_writer_content' => '[댓글신고알림] {게시판명} - {게시글제목}',
			'send_sms_comment_blame_comment_writer_content' => '[댓글신고알림] {게시판명} - {게시글제목}',
		);

		$this->cache->delete('config-model-get');
		$this->cache->clean();
		$this->Config_model->save($configdata);

		$depositdata = array(
			'site_meta_title_deposit' => '예치금 관리 - {홈페이지제목}',
			'site_meta_title_deposit_mylist' => '나의 예치금 내역 - {홈페이지제목}',
			'site_meta_title_deposit_result' => '예치금 충전 결과 - {홈페이지제목}',
			'deposit_email_admin_cash_to_deposit_title' => '[결제 알림] {회원닉네임}님이 결제하셨습니다',
			'deposit_email_admin_cash_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 결제하셨습니다</p><p>회원님께서 결제하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_email_user_cash_to_deposit_title' => '[{홈페이지명}] 결제가 완료되었습니다',
			'deposit_email_user_cash_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>구매해주셔서 감사합니다</p><p>{회원닉네임}님께서 구매하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_admin_cash_to_deposit_title' => '[결제 알림] {회원닉네임}님이 결제하셨습니다',
			'deposit_note_admin_cash_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 결제하셨습니다</p><p>회원님께서 결제하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_user_cash_to_deposit_title' => '결제가 완료되었습니다',
			'deposit_note_user_cash_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>구매해주셔서 감사합니다</p><p>{회원닉네임}님께서 구매하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_sms_admin_cash_to_deposit_content' => '[결제알림] {회원닉네임}님, 결제금액 : {결제금액} 원',
			'deposit_sms_user_cash_to_deposit_content' => '[{홈페이지명}] 결제완료 : {결제금액} 원 - 감사합니다',
			'deposit_email_admin_bank_to_deposit_title' => '[무통장입금요청] {회원닉네임}님이 무통장입금 요청하셨습니다',
			'deposit_email_admin_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 무통장입금 요청하셨습니다</p><p>회원님께서 구매하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>통장에 입금된 내역이 확인되면 관리자페이지에서 입금완료 승인을 해주시기 바랍니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_email_user_bank_to_deposit_title' => '[{홈페이지명}] 무통장입금요청을 하셨습니다',
			'deposit_email_user_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>{회원닉네임}님께서 구매요청하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>아래의 계좌번호로 입금부탁드립니다</p><p>은행안내 : {은행계좌안내}</p><p>입금이 확인되면 24시간 내에 처리가 완료됩니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_admin_bank_to_deposit_title' => '[무통장입금요청] {회원닉네임}님이 무통장입금 요청하셨습니다',
			'deposit_note_admin_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 무통장입금 요청하셨습니다</p><p>회원님께서 구매하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>통장에 입금된 내역이 확인되면 관리자페이지에서 입금완료 승인을 해주시기 바랍니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_user_bank_to_deposit_title' => '무통장입금요청을 하셨습니다',
			'deposit_note_user_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>{회원닉네임}님께서 구매요청하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>아래의 계좌번호로 입금부탁드립니다</p><p>은행안내 : {은행계좌안내}</p><p>입금이 확인되면 24시간 내에 처리가 완료됩니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_sms_admin_bank_to_deposit_content' => '[무통장입금요청] {회원닉네임}님, 결제요청금액 : {결제금액} 원',
			'deposit_sms_user_bank_to_deposit_content' => '[{홈페이지명}] 입금요청 : {결제금액} 원 - 감사합니다',
			'deposit_email_admin_approve_bank_to_deposit_title' => '[입금처리완료] {회원닉네임}님의 입금처리요청이 완료되었습니다',
			'deposit_email_admin_approve_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님의 입금처리요청이 완료되었습니다</p><p>회원님께서 구매하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_email_user_approve_bank_to_deposit_title' => '[{홈페이지명}] 구매해주셔서 감사합니다',
			'deposit_email_user_approve_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>{회원닉네임}님께서 구매요청하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>정상 구매가 완료되었습니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_admin_approve_bank_to_deposit_title' => '[입금처리완료] {회원닉네임}님의 입금처리요청이 완료되었습니다',
			'deposit_note_admin_approve_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님의 입금처리요청이 완료되었습니다</p><p>회원님께서 구매하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_user_approve_bank_to_deposit_title' => '구매해주셔서 감사합니다',
			'deposit_note_user_approve_bank_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>{회원닉네임}님께서 구매요청하신 내용입니다</p><p>결제금액 : {결제금액} 원</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>정상 구매가 완료되었습니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_sms_admin_approve_bank_to_deposit_content' => '[입금처리완료] {회원닉네임}님의 {결제금액} 원 입금처리요청 완료',
			'deposit_sms_user_approve_bank_to_deposit_content' => '[{홈페이지명}] {결제금액}원 입금처리완료되었습니다. 감사합니다',
			'deposit_email_admin_point_to_deposit_title' => '[구매 알림] {회원닉네임}님이 포인트로 {예치금명} 구매 하셨습니다',
			'deposit_email_admin_point_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 포인트로 {예치금명} 구매하셨습니다</p><p>회원님께서 구매하신 내용입니다</p><p>사용포인트 : {전환포인트} 점</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_email_user_point_to_deposit_title' => '[{홈페이지명}] 포인트 결제가 완료되었습니다',
			'deposit_email_user_point_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>구매해주셔서 감사합니다</p><p>회원님께서 구매하신 내용입니다</p><p>사용포인트 : {전환포인트} 점</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_admin_point_to_deposit_title' => '[구매 알림] {회원닉네임}님이 포인트로 {예치금명} 구매 하셨습니다',
			'deposit_note_admin_point_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 포인트로 {예치금명} 구매하셨습니다</p><p>회원님께서 구매하신 내용입니다</p><p>사용포인트 : {전환포인트} 점</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_user_point_to_deposit_title' => '포인트 결제가 완료되었습니다',
			'deposit_note_user_point_to_deposit_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>구매해주셔서 감사합니다</p><p>회원님께서 구매하신 내용입니다</p><p>사용포인트 : {전환포인트} 점</p><p>전환되는 {예치금명} : {전환예치금액}{예치금단위}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_sms_admin_point_to_deposit_content' => '[포인트->예치금 결제] {회원닉네임} 님 결제 완료',
			'deposit_sms_user_point_to_deposit_content' => '[{홈페이지명}] 결제완료 - 전환{예치금명}:{전환예치금액}{예치금단위} 감사합니다',
			'deposit_email_admin_deposit_to_point_title' => '[포인트 전환 알림] {회원닉네임}님이 포인트를 구매하셨습니다',
			'deposit_email_admin_deposit_to_point_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임} 님이 포인트를 구매하셨습니다</p><p>회원님께서 구매하신 내용입니다</p><p> 포인트 : {전환포인트}점</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_email_user_deposit_to_point_title' => '[{홈페이지명}] 포인트구매가 완료되었습니다',
			'deposit_email_user_deposit_to_point_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>포인트를 구매해주셔서 감사합니다</p><p>{회원닉네임}님께서 구매하신 내용입니다</p><p> 포인트 : {전환포인트}점</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_admin_deposit_to_point_title' => '[포인트 전환 알림] {회원닉네임}님이 포인트를 구매하셨습니다',
			'deposit_note_admin_deposit_to_point_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임} 님이 포인트를 구매하셨습니다</p><p>회원님께서 구매하신 내용입니다</p><p> 포인트 : {전환포인트}점</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_note_user_deposit_to_point_title' => '포인트구매가 완료되었습니다',
			'deposit_note_user_deposit_to_point_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>포인트를 구매해주셔서 감사합니다</p><p>{회원닉네임}님께서 구매하신 내용입니다</p><p> 포인트 : {전환포인트}점</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'deposit_sms_admin_deposit_to_point_content' => '[예치금->포인트 결제] {회원닉네임} 님 결제 완료',
			'deposit_sms_user_deposit_to_point_content' => '[{홈페이지명}] 결제완료 - 적립포인트 {전환포인트}점. 감사합니다',
		);
		$cmalldata = array(
			'site_meta_title_cmall' => '{컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_list' => '{컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_item' => '{상품명} > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_cart' => '장바구니 > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_order' => '상품주문 > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_orderresult' => '주문결과 > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_orderlist' => '주문내역 > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_wishlist' => '찜한 목록 > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_review_write' => '상품후기작성 > {컨텐츠몰명} - {홈페이지제목}',
			'site_meta_title_cmall_qna_write' => '상품문의작성 > {컨텐츠몰명} - {홈페이지제목}',
			'cmall_email_admin_cash_to_contents_title' => '[주문안내] {회원닉네임}님이 결제하셨습니다',
			'cmall_email_user_cash_to_contents_title' => '[{홈페이지명}] 상품을 구매해주셔서 감사합니다',
			'cmall_note_admin_cash_to_contents_title' => '[주문안내] {회원닉네임}님이 결제하셨습니다',
			'cmall_note_admin_cash_to_contents_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 상품을 구매하셨습니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'cmall_note_user_cash_to_contents_title' => '상품을 구매해주셔서 감사합니다',
			'cmall_note_user_cash_to_contents_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>구매해주셔서 감사합니다</p><p>구매하신 상품 이용이 가능합니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'cmall_sms_admin_cash_to_contents_content' => '[구매알림] {회원닉네임}님이 구매하셨습니다',
			'cmall_sms_user_cash_to_contents_content' => '[{홈페이지명}] 구매가완료되었습니다 감사합니다',
			'cmall_email_admin_bank_to_contents_title' => '[주문안내] {회원닉네임}님이 무통장입금 요청하셨습니다',
			'cmall_email_user_bank_to_contents_title' => '[{홈페이지명}] 구매신청이접수되었습니다.입금확인후상품이용가능합니다',
			'cmall_note_admin_bank_to_contents_title' => '[주문안내] {회원닉네임}님이 무통장입금 요청하셨습니다',
			'cmall_note_admin_bank_to_contents_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님이 무통장입금요청하셨습니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'cmall_note_user_bank_to_contents_title' => '구매신청이접수되었습니다.입금확인후상품이용가능합니다',
			'cmall_note_user_bank_to_contents_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>구매해주셔서 감사합니다</p><p>입금이 확인되는대로 승인처리해드리겠습니다</p><p>결제금액 : {결제금액}원</p><p>은행계좌안내 : {은행계좌안내}</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'cmall_sms_admin_bank_to_contents_content' => '[무통장입금요청] {회원닉네임}님이 무통장입금요청하였습니다',
			'cmall_sms_user_bank_to_contents_content' => '[{홈페이지명}] 구매신청이접수되었습니다.입금확인후상품이용가능합니다',
			'cmall_email_admin_approve_bank_to_contents_title' => '[입금처리완료] {회원닉네임}님의 입금처리요청이 완료되었습니다',
			'cmall_email_user_approve_bank_to_contents_title' => '[{홈페이지명}] 입금이 확인되어 주문처리가 완료되었습니다',
			'cmall_note_admin_approve_bank_to_contents_title' => '[입금처리완료] {회원닉네임}님의 입금처리요청이 완료되었습니다',
			'cmall_note_admin_approve_bank_to_contents_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요</p><p>{회원닉네임}님의 입금확인 처리가 완료되었습니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'cmall_note_user_approve_bank_to_contents_title' => '입금이 확인되어 주문처리가 완료되었습니다',
			'cmall_note_user_approve_bank_to_contents_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><p>안녕하세요 {회원닉네임}님</p><p>구매해주셔서 감사합니다</p><p>입금이 확인되어 이제 정상적으로 상품 이용이 가능합니다</p><p>감사합니다</p></div><p><a href="{홈페이지주소}" target="_blank" style="font-weight:bold;">홈페이지 가기</a></p><p>&nbsp;</p></td></tr></table>',
			'cmall_sms_admin_approve_bank_to_contents_content' => '[무통장입금확인] {회원닉네임}님의 무통장입금요청이확인되었습니다',
			'cmall_sms_user_approve_bank_to_contents_content' => '[{홈페이지명}] 입금이확인되었습니다. 구매하신상품다운로드가가능합니다',
			'cmall_email_admin_write_product_review_title' => '[상품후기] {상품명} 상품 후기가 작성되었습니다',
			'cmall_email_admin_write_product_review_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[후기제목]</strong></div><div>{후기제목}</div><div>&nbsp;</div><div><strong>[후기내용]</strong></div><div>{후기내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_email_user_write_product_review_title' => '[홈페이지명] {상품명} 상품 후기를 작성해주셔서 감사합니다',
			'cmall_email_user_write_product_review_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[후기제목]</strong></div><div>{후기제목}</div><div>&nbsp;</div><div><strong>[후기내용]</strong></div><div>{후기내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_note_admin_write_product_review_title' => '[상품후기] {상품명} 상품 후기가 작성되었습니다',
			'cmall_note_admin_write_product_review_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[후기제목]</strong></div><div>{후기제목}</div><div>&nbsp;</div><div><strong>[후기내용]</strong></div><div>{후기내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_note_user_write_product_review_title' => '{상품명} 상품 후기를 작성해주셔서 감사합니다',
			'cmall_note_user_write_product_review_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[후기제목]</strong></div><div>{후기제목}</div><div>&nbsp;</div><div><strong>[후기내용]</strong></div><div>{후기내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_sms_admin_write_product_review_content' => '[상품후기] {상품명} 상품후기가 작성되었습니다',
			'cmall_sms_user_write_product_review_content' => '[홈페이지명] {상품명} 상품후기를 작성해주셔서 감사합니다',
			'cmall_email_admin_write_product_qna_title' => '[상품문의] {상품명} 상품 문의가 작성되었습니다',
			'cmall_email_admin_write_product_qna_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[문의내용]</strong></div><div>{문의내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_email_user_write_product_qna_title' => '[홈페이지명] {상품명} 상품 문의가 접수되었습니다',
			'cmall_email_user_write_product_qna_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[문의내용]</strong></div><div>{문의내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_note_admin_write_product_qna_title' => '[상품문의] {상품명} 상품 문의가 작성되었습니다',
			'cmall_note_admin_write_product_qna_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[문의내용]</strong></div><div>{문의내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_note_user_write_product_qna_title' => '{상품명} 상품 문의가 접수되었습니다',
			'cmall_note_user_write_product_qna_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[문의내용]</strong></div><div>{문의내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_sms_admin_write_product_qna_content' => '[상품문의] {상품명} 상품문의가 접수되었습니다',
			'cmall_sms_user_write_product_qna_content' => '[홈페이지명] {상품명} 상품문의가 접수되었습니다 감사합니다',
			'cmall_email_admin_write_product_qna_reply_title' => '[상품문의] {상품명} 상품 문의에 대한 답변이 등록되었습니다',
			'cmall_email_admin_write_product_qna_reply_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[답변내용]</strong></div><div>{답변내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_email_user_write_product_qna_reply_title' => '[홈페이지명] {상품명} 상품 문의에 대한 답변입니다',
			'cmall_email_user_write_product_qna_reply_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[답변내용]</strong></div><div>{답변내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_note_admin_write_product_qna_reply_title' => '[상품문의] {상품명} 상품 문의에 대한 답변이 등록되었습니다',
			'cmall_note_admin_write_product_qna_reply_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[답변내용]</strong></div><div>{답변내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_note_user_write_product_qna_reply_title' => '{상품명} 상품 문의에 대한 답변입니다',
			'cmall_note_user_write_product_qna_reply_content' => '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-left: 1px solid rgb(226,226,225);border-right: 1px solid rgb(226,226,225);background-color: rgb(255,255,255);border-top:10px solid #348fe2; border-bottom:5px solid #348fe2;border-collapse: collapse;"><tr><td style="font-size:12px;padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;">{홈페이지명}</td></tr><tr style="border-top:1px solid #e2e2e2; border-bottom:1px solid #e2e2e2;"><td style="padding:20px 30px;font-family: Arial,sans-serif;color: rgb(0,0,0);font-size: 14px;line-height: 20px;"><div><strong>[문의제목]</strong></div><div>{문의제목}</div><div>&nbsp;</div><div><strong>[답변내용]</strong></div><div>{답변내용}</div><div>&nbsp;</div><div><a href="{상품주소}" target="_blank"><strong>[상품페이지 보기]</strong></a></div><p>&nbsp;</p></td></tr></table>',
			'cmall_sms_admin_write_product_qna_reply_content' => '[상품문의] {상품명} 상품문의답변이 등록되었습니다',
			'cmall_sms_user_write_product_qna_reply_content' => '[홈페이지명] {상품명} 상품문의에 대한 답변이 등록되었습니다 감사합니다',
		);
		$this->Config_model->save($depositdata);
		$this->Config_model->save($cmalldata);
	}


	/**
	 * 회원가입시 닉네임을 체크하는 함수입니다
	 */
	public function _mem_nickname_check($str)
	{
		$this->load->helper('chkstring');
		if (chkstring($str, _HANGUL_ + _ALPHABETIC_ + _NUMERIC_) === false) {
			$this->form_validation->set_message(
				'_mem_nickname_check',
				'닉네임은 공백없이 한글, 영문, 숫자만 입력 가능합니다'
			);
			return false;
		}

		return true;
	}
}
