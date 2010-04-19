<?php
/**
 * PwUrl
 * Picowa標準URLクラス
 * @package		Picowa
 * @since		2010-04-09
 */
class PwUrl 
{
	private $url;
	private $method;
	private $conditions;
	public $params = array();
	public $match = false;

	public function __construct($httpMethod, $url, $conditions=array(), $mountPoint) 
	{
		$requestMethod = $_SERVER['REQUEST_METHOD'];
		$requestUri = str_replace($mountPoint, '', $_SERVER['REQUEST_URI']);
		$this->url = $url;
		$this->method = $httpMethod;
		$this->conditions = $conditions;
		if (strtoupper($httpMethod) == $requestMethod) {
			$paramNames = array();
			$paramValues = array();
			preg_match_all('@:([a-zA-Z]+)@', $url, $paramNames, PREG_PATTERN_ORDER);					// get param names
			$paramNames = $paramNames[1];															   // we want the set of matches
			$regexedUrl = preg_replace_callback('@:[a-zA-Z_]+@', array($this, 'regexValue'), $url);	 // replace param with regex capture
			if (preg_match('@^' . $regexedUrl . '(?:\?.*)?$@', $requestUri, $paramValues)) {				  // determine match and get param values
				array_shift($paramValues);															  // remove the complete text match
				for ($i=0; $i < count($paramNames); $i++) {
					$this->params[$paramNames[$i]] = $paramValues[$i];
				}
				$this->match = true;
			}
		}
	}

	private function regexValue($matches) 
	{
		$key = str_replace(':', '', $matches[0]);
		if (array_key_exists($key, $this->conditions)) {
			return '(' . $this->conditions[$key] . ')';
		} 
		else {
			return '([a-zA-Z0-9_]+)';
		}
	}

}

