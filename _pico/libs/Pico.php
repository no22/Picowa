<?php
/**
 * Picowa Object Class
 *
 * @package		Picowa
 * @since		2010-04-13
 */	

class Pico
{
	static protected $cfg = null;
	protected $factory = null;
	protected $components = array();
	public $uses = array();

	static public function cfg($oCfg = null)
	{
		if (is_null($oCfg)) return self::$cfg;
		self::$cfg = $oCfg; 
	}

	public function __construct()
	{
		$this->initPico();
	}

	public function initPico()
	{
		$this->factory = PwComponentFactory::getInstance();
		$this->initializeComponents();
	}
	
	public function __call($sName, $aArg)
	{
		if (startsWith($sName, '_')) {
			return Curry::make(array($this, substr($sName, 1)), $aArg);
		}
	}

	public function __get($sName)
	{
		if (startsWith($sName, '_')) {
			return $this->factory->build('PwPropertyPromise', $this, substr($sName, 1));
		}
		else if (isset($this->components[$sName])) {
			return $this->{$sName} = call_user_func($this->components[$sName]);
		}
		return null;
	}

	public function componentClasses($sClass = null)
	{
		$sClass = is_null($sClass) ? get_class($this) : $sClass ;
		$aVars = get_class_vars($sClass);
		$aComponents = isset($aVars['uses']) ? $aVars['uses'] : array() ;
		$sParent = get_parent_class($sClass);
		if (!$sParent) return $aComponents;
		$aParentComponents = $this->componentClasses($sParent);
		return array_merge($aParentComponents, $aComponents);
	}

	public function initializeComponents()
	{
		$aComponents = $this->componentClasses();
		foreach ($aComponents as $sKey => $sClass) {
			if (is_array($sClass)) {
				$args = $sClass;
				$sClass = array_shift($args);
				$sName = is_string($sKey) ? $sKey : $sClass ;
				$this->components[$sName] = quote($this->factory)->buildArray($sClass,$args);
			}
			else {
				$sName = is_string($sKey) ? $sKey : $sClass ;
				$this->components[$sName] = quote($this->factory)->build($sClass);
			}
		}
	}

	public function attach($mName, $mClass = null)
	{
		if (is_array($mName)) {
			$sClass = array_shift($mName);
			if(property_exists($this, $sClass)) unset($this->{$sClass});
			$this->components[$sClass] = quote($this->factory)->buildArray($sClass,$mName);
			return $this;
		}
		if (is_array($mClass)) {
			$sClass = array_shift($mClass);
		}
		else {
			$sClass = is_null($mClass) ? $mName : $mClass ;
		}
		if(property_exists($this, $mName)) unset($this->{$mName});
		if (is_array($mClass)) {
			$this->components[$mName] = quote($this->factory)->buildArray($sClass,$mClass);
		}
		else {
			$this->components[$mName] = quote($this->factory)->build($sClass);
		}
		return $this;
	}
}
