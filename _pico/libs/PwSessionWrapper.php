<?php
/**
 * Picowa Session wrapper
 *
 * @package		Picowa
 * @since		2010-04-13
 */	
 class PwSessionWrapper 
 {
	public function __get($key) 
	{
		return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
	}

	public function __set($key, $value) 
	{
		if (is_null($value)) {
			unset($_SESSION[$key]);
			return $value;
		}
		else {
			$_SESSION[$key] = $value;
			return $value;
		}
	}

	public function __call($sName, $aArgs)
	{
		if (count($aArgs) < 2) {
			list($sKey) = $aArgs;
			return isset($_SESSION[$sName][$sKey]) ? $_SESSION[$sName][$sKey] : null ;
		}
		else {
			list($sKey, $mValue) = $aArgs;
			$_SESSION[$sName][$sKey] = $mValue;
			return $mValue;
		}
	}
}

