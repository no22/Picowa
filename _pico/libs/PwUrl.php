<?php
/**
 * PwUrl
 * Picowa標準URLクラス
 * @package		Picowa
 * @since		2010-04-09
 */
class PwUrl 
{
	public $url;
	public $method;
	public $conditions;
	public $params = array();
	public $match = false;
	public $mountPoint = '';
	
	public function __construct($mountPoint = '') 
	{
		$this->mountPoint = $mountPoint;
	}

	public function match($httpMethod, $url, $conditions=array(), $mountPoint = null) 
	{
		$mountPoint = is_null($mountPoint) ? $this->mountPoint : $mountPoint ;
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$requestUri = str_replace($mountPoint, '', $_SERVER['REQUEST_URI']);
		$this->url = $url;
		$this->method = $httpMethod;
		$this->conditions = $conditions;
		$this->match = false;
		if (strtoupper($httpMethod) === $requestMethod) {
			$paramNames = array();
			$paramValues = array();
			preg_match_all('@:([a-zA-Z]+)@', $url, $paramNames, PREG_PATTERN_ORDER);
			$paramNames = $paramNames[1];
			$regexedUrl = preg_replace_callback('@:[a-zA-Z_]+@', array($this, 'regexValue'), $url);
			if (preg_match('@^' . $regexedUrl . '(?:\?.*)?$@', $requestUri, $paramValues)) {
				array_shift($paramValues);
				foreach ($paramNames as $i => $paramName) {
					$this->params[$paramName] = $paramValues[$i];
				}
				$this->match = true;
			}
		}
		return $this->match;
	}

	public function path($path)
	{
		return PICOWA_ROOT_URL . $this->mountPoint . $path;
	}

	private function regexValue($matches) 
	{
		$key = strtr($matches[0], array(':' => ''));
		if (array_key_exists($key, $this->conditions)) {
			return '(' . $this->conditions[$key] . ')';
		} 
		else {
			return '([a-zA-Z0-9_]+)';
		}
	}
}

