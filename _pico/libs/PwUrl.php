<?php
/**
 * PwUrl
 * 
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
	public $requestUrl;
	public $requestMethod;
	
	public function __construct($mountPoint = '') 
	{
		$this->mountPoint = $mountPoint;
		$this->requestMethod = $_SERVER['REQUEST_METHOD'];
		$this->requestUrl = str_replace($mountPoint, '', $_SERVER['REQUEST_URI']);
	}

	public function match($httpMethod, $url, $conditions=array(), $mountPoint = null) 
	{
		$requestUri = is_null($mountPoint) ? $this->requestUrl : str_replace($mountPoint, '', $_SERVER['REQUEST_URI']) ;
		$requestMethod = $this->requestMethod;
		$this->method = strtoupper($httpMethod);
		$this->url = $url;
		$this->conditions = $conditions;
		$this->match = false;
		$httpMethods = explode('|', strtoupper($httpMethod));
		if ($httpMethod === '*' || in_array($requestMethod, $httpMethods)) {
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

	protected function regexValue($matches) 
	{
		$key = strtr($matches[0], array(':' => ''));
		if (array_key_exists($key, $this->conditions)) {
			return '(' . $this->conditions[$key] . ')';
		} 
		else {
			return '([a-zA-Z0-9_]+)';
		}
	}

	public function extractQuery($sUrl = null)
	{
		$sUrl = is_null($sUrl) ? $this->requestUrl : $sUrl ;
		if (preg_match('/\?(.*)(?:#.*)?$/', $sUrl, $matches)) {
			$aQuery = explode('&', $matches[1]);
			$aResult = array();
			foreach ($aQuery as $elem) {
				list($key, $value) = explode('=', $elem);
				$aResult[$key] = $value;
			}
			return $aResult;
		}
		return array();
	}

	public function makeQuery($aQuery, $sPath = null)
	{
		$sPath = is_null($sPath) ? PICOWA_REQUEST_URL : $sPath ;
		$aResult = array();
		foreach ($aQuery as $key => $value) {
			$aResult[] = $key.'='.$value;
		}
		return $this->path($sPath).'?'.implode('&', $aResult);
	}

}

