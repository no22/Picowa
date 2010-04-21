<?php
/**
 * Picowa オブジェクトクラス
 *
 * @package		Picowa
 * @since		2010-04-13
 */	

class Pico
{
	protected $factory = null;
	protected $components = array();
	protected $uses = array();

	public function __construct()
	{
		$this->factory = new PwComponentFactory;
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
		if (isset($this->components[$sName])) {
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
		$aComponents = array_unique($this->componentClasses());
		foreach ($aComponents as $sClass) {
			$this->components[$sClass] = quote($this->factory)->build($sClass);
		}
	}

	public function attach($sName, $sClass = null)
	{
		$sClass = is_null($sClass) ? $sName : $sClass ;
		if(property_exists($this, $sName)) unset($this->{$sName});
		$this->components[$sName] = quote($this->factory)->build($sClass);
		return $this;
	}
}
