<?php
//>php -l %FILE%
/**
 * Picowa AutoLoad 
 *
 * @package		Picowa
 * @since		2010-04-09
 */

class PicowaAutoload
{
	public static function autoload($sClass)
	{
		$sPath = strtr($sClass, array('_'=>'/')).'.php';
		if (file_exists(APP_LIB_PATH . $sPath)) {
			include(APP_LIB_PATH . $sPath);
		} 
		else if (file_exists(LIB_PATH . $sPath)) {
			include(LIB_PATH . $sPath);
		} 
	}
}
function_exists('__autoload') and spl_autoload_register('__autoload');
spl_autoload_register('PicowaAutoload::autoload');
