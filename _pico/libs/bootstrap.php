<?php
//>php -l %FILE%
/**
 * Picowa Bootstrap 
 *
 * @package		Picowa
 * @since		2010-04-09
 */

/**
 * Application Constants
 */
	// define directory separator short alias
	!defined('DS') and define('DS', DIRECTORY_SEPARATOR);

	// define root application directory
	!defined('ROOT') and define('ROOT', dirname(dirname(dirname(__FILE__))));

	// define picowa application directory name
	!defined('APP_DIR') and define('APP_DIR', '_pico');

	// define absolute path to picowa application directory
	!defined('PICOWA_INCLUDE_PATH') and define('PICOWA_INCLUDE_PATH', ROOT . DS . APP_DIR);

	// define web root directory name
	!defined('WEBROOT_DIR') and define('WEBROOT_DIR', basename(dirname(__FILE__)));

	// define www root directory
	!defined('WWW_ROOT') and define('WWW_ROOT', dirname(__FILE__) . DS);

	// define app path
	!defined('APP_PATH') and define('APP_PATH', PICOWA_INCLUDE_PATH . DS);

	// define lib path
	!defined('LIB_PATH') and define('LIB_PATH', APP_PATH . 'libs' . DS);

	// define model path
	!defined('APP_LIB_PATH') and define('APP_LIB_PATH', APP_PATH . 'app' . DS);

	// define view path
	!defined('VIEW_PATH') and define('VIEW_PATH', APP_PATH . 'views' . DS);

	// define config path
	!defined('CONF_PATH') and define('CONF_PATH', APP_PATH . 'confs' . DS);

	// define temporary path
	!defined('TEMP_PATH') and define('TEMP_PATH', APP_PATH . 'temp' . DS);

	// define root url
	if (!defined('ROOT_URL')) {
		$s = isset($_SERVER['HTTPS']) ? 's' : '' ;
		$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '' ;
		define('ROOT_URL', 'http'.$s.'://'.$httpHost);
	}

	// define mount point
	if (!defined('MOUNT_POINT')) {
		$url = isset($_GET['url']) ? $_GET['url'] : '' ;
		list($reqUrl) = explode('?',$_SERVER['REQUEST_URI']);
		define('MOUNT_POINT', substr($reqUrl, 0, -strlen($url)-1));
	}

/**
 * Include Picowa Autoload
 */
	if (!include(LIB_PATH . 'AutoLoad.php')) {
		trigger_error("Picowa library could not be found ".LIB_PATH . 'AutoLoad.php'.".", E_USER_ERROR);
	}

/**
 * Environment Constants
 */
 	$oCFG = PwComponentFactory::set('PwComponentBuilder')->build('PwConfig',
		array(
			'test_server' => '127.0.0.1',
			'admin_address' => '',
			'session_name' => 'picowa_session',
			'cookie_lifetime' => 0,
			'production' => array(
				'use_debug_mail' => 1,
				'use_debug_log' => 1,
				'use_error_handler' => 1,
				'builder' => 'PwComponentBuilder',
			),
			'debug' => array(
				'use_debug_mail' => 0,
				'use_debug_log' => 0,
				'use_error_handler' => 0,
				'builder' => 'PwComponentBuilder',
			),
		), 'picowa_config'
	);

	!defined('TEST_SERVER') and define('TEST_SERVER', $oCFG->test_server);
	!defined('ADMIN_ADDRESS') and define('ADMIN_ADDRESS', $oCFG->admin_address);
	!defined('SESSION_NAME') and define('SESSION_NAME', $oCFG->session_name);
	!defined('COOKIE_LIFETIME') and define('COOKIE_LIFETIME', $oCFG->cookie_lifetime);

	// define debug mode
	if (!defined('DEBUG_MODE')) {
		if ($_SERVER['SERVER_ADDR'] === TEST_SERVER) {
			define('DEBUG_MODE', 1);
		}
		else {
			define('DEBUG_MODE', 0);
		}
	}
	
	if (DEBUG_MODE) {
		!defined('USE_DEBUG_MAIL') and define('USE_DEBUG_MAIL', $oCFG->debug('use_debug_mail'));
		!defined('USE_DEBUG_LOG') and define('USE_DEBUG_LOG', $oCFG->debug('use_debug_log'));
		!defined('USE_ERROR_HANDLER') and define('USE_ERROR_HANDLER', $oCFG->debug('use_error_handler'));
		PwComponentFactory::set($oCFG->debug('builder'));
	}
	else {
		!defined('USE_DEBUG_MAIL') and define('USE_DEBUG_MAIL', $oCFG->production('use_debug_mail'));
		!defined('USE_DEBUG_LOG') and define('USE_DEBUG_LOG', $oCFG->production('use_debug_log'));
		!defined('USE_ERROR_HANDLER') and define('USE_ERROR_HANDLER', $oCFG->production('use_error_handler'));
		PwComponentFactory::set($oCFG->production('builder'));
	}

