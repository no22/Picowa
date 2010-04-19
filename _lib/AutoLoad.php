<?php
/**
 * AutoLoad.php
 *
 * @package		Picowa
 * @since		2008-11-07
 */	

define('MWLIBPATH', strtr(dirname(__FILE__) .'/',array('\\' => '/')));
define('MWSCRIPTDIR', (dirname($_SERVER['SCRIPT_NAME']) !== '/' ? dirname($_SERVER['SCRIPT_NAME']).'/' : '/'));
define('MWBASEPATH', dirname(MWLIBPATH) .'/');
file_exists(MWBASEPATH .'_sys/') and define('MWSYSPATH', MWBASEPATH .'_sys/');
file_exists(MWBASEPATH .'_html/') and define('MWHTMLPATH', MWBASEPATH .'_html/');
file_exists(MWBASEPATH .'_cache/') and define('MWCACHEPATH', MWBASEPATH .'_cache/');
file_exists(MWBASEPATH .'_hcache/') and define('MWHCACHEPATH', MWBASEPATH .'_hcache/');
file_exists(MWBASEPATH .'_data/') and define('MWDATAPATH', MWBASEPATH .'_data/');

function autoloadLibrary($s_className)
{
	$s_classPath = strtr($s_className,array('_' => '/')).'.php';
	$path_ary = array(MWLIBPATH);
	defined('MWSYSPATH') and array_push($path_ary,MWSYSPATH);
	foreach($path_ary as $s_path) {
		if(file_exists($s_path.$s_classPath)) {
			include($s_path.$s_classPath);
			return;
		}
	}
}

function_exists('__autoload') and spl_autoload_register('__autoload');
spl_autoload_register('autoloadLibrary');
file_exists(MWLIBPATH.'Sloth/AutoLoad.php') and require_once MWLIBPATH.'Sloth/AutoLoad.php';
require_once MWLIBPATH.'stdlib.php';
require_once MWLIBPATH.'bind.php';
