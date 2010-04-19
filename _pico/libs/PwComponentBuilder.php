<?php
/**
 * PwComponentBuilder
 * Picowa用ComponentBuilderクラス
 * @package		Picowa
 * @since		2010-04-15
 */
class PwComponentBuilder
{
	protected $factory = null;
	
	public function __construct($oFactory)
	{
		$this->factory = $oFactory;
	}
}
