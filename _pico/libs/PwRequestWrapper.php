<?php
/**
 * Picowa Request wrapper
 *
 * @package		Picowa
 * @since		2010-04-13
 */	
class PwRequestWrapper 
{
	public function __get($key) 
	{
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
	}

	public function __set($key, $value) 
	{
		$_REQUEST[$key] = $value;
		return $value;
	}

	public function __call($sName, $aArgs)
	{
		if (count($aArgs) < 2) {
			list($sKey) = $aArgs;
			return isset($_REQUEST[$sName][$sKey]) ? $_REQUEST[$sName][$sKey] : null ;
		}
		else {
			list($sKey, $mValue) = $aArgs;
			$_REQUEST[$sName][$sKey] = $mValue;
			return $mValue;
		}
	}
}

