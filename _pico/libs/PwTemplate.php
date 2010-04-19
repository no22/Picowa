<?php
/**
 * PwTemplate
 * Picowa標準HTMLテンプレートクラス
 * @package		Picowa
 * @since		2010-04-09
 */
class PwTemplate 
{
	protected $fileName;
	protected $root;
	
	public function __construct($root, $fileName) 
	{
		$this->fileName = $fileName;
		$this->root = $root;
	}
	
	public function render($locals) 
	{
		extract($locals);
		ob_start();
		include($this->root . 'views/' . $this->fileName . '.php');
		return ob_get_clean();
	}
}

