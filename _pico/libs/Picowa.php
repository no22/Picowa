<?php
/**
 * Picowa 軽量Webアプリケーションクラス
 *
 * @package		Picowa
 * @since		2010-04-06
 */	

class Picowa extends Pico
{
	protected $filters = array(
		'before' => array(),
		'after' => array(),
		'around' => array(),
	);
	protected $mappings = array();
	protected $options;
	protected $session;
	protected $request;
	protected $args;
	protected $error;
	protected $success;

	public function __construct($options = array()) 
	{
		parent::__construct();
		if (!isset($options['mountPoint'])) {
			$options['mountPoint'] = MOUNT_POINT;
		}
		$confs = isset($options['config']) ? $options['config'] : array() ;
		$this->confs = $this->factory->build('PwConfig', $confs);
		$this->options = $this->factory->build('PwArrayWrapper', $options);
		$this->initializeSession();
		session_name(SESSION_NAME);
		session_start();
		$this->session = $this->factory->build('PwSessionWrapper');
		$this->request = $this->factory->build('PwRequestWrapper');
		USE_ERROR_HANDLER and set_error_handler(array($this, 'handleError'), 2);
	}

	protected function initializeSession()
	{
		ini_set('session.serialize_handler', 'php');
		ini_set('session.name', SESSION_NAME);
		ini_set('session.cookie_lifetime', COOKIE_LIFETIME);
		ini_set('session.auto_start', 0);
		ini_set('session.save_path', TEMP_PATH . 'sessions');
	}
	
	public function root() { return APP_PATH; }

	protected function path($path) { return $this->root() . $path; }

	public function url($path) { return ROOT_URL . MOUNT_POINT . $path; }
	
	public function options() { return $this->options; }

	public function session() { return $this->session; }

	public function request() { return $this->request; }

	public function args() { return $this->args; }

 	public function handleError($errno, $message, $file, $line) 
	{
		$aError = compact($errno, $message, $file, $line);
		USE_DEBUG_MAIL and dbgmail(print_r($aError, true), ADMIN_ADDRESS);
		USE_DEBUG_LOG and dbglog(print_r($aError, true));
		header("HTTP/1.0 500 Server Error");
		DEBUG_MODE and print_r($aError);
		echo $this->render('500');
		die();
	}

	public function show404() 
	{
		header("HTTP/1.0 404 Not Found");
		echo $this->render('404');
		die();
	}

	public function get($url, $callback, $conditions=array()) 
	{
		$this->event('get', $url, $callback, $conditions);
	}

	public function post($url, $callback, $conditions=array()) 
	{
		$this->event('post', $url, $callback, $conditions);
	}

	protected function event($httpMethod, $url, $callback, $conditions=array()) 
	{
		if (is_string($callback) && function_exists($callback)) {
			array_push($this->mappings, array($httpMethod, $url, $callback, $conditions));
		}
		else if (is_array($callback) && method_exists($callback[0], $callback[1])) {
			array_push($this->mappings, array($httpMethod, $url, $callback, $conditions));
		}
		else if (is_object($callback) && $callback instanceof Closure) {
			array_push($this->mappings, array($httpMethod, $url, $callback, $conditions));
		}
	}

	public function run() 
	{
		echo $this->processRequest();
	}

	protected function processRequest() 
	{
		foreach ($this->mappings as $i => $mapping) {
			$mountPoint = is_string($this->options->mountPoint) ? $this->options->mountPoint : '';
			$url = $this->factory->build('PwUrl', $mapping[0], $mapping[1], $mapping[3], $mountPoint);
			if ($url->match) {
				return $this->execute($mapping[2], $url->params, $mapping[0], $mapping[1]);
			}
		}
		return $this->show404();
	}

	public function setSessionValue($args)
	{
		if ($this->session->error) {
			$this->error = $this->session->error;
			$this->session->error = null;
		}
		if ($this->session->success) {
			$this->success = $this->session->success;
			$this->session->success = null;
		}
	}

	protected function setFilterCallback($kind, $key, $callback)
	{
		if (!isset($this->filters[$kind][$key])) return $callback;
		$filters = $kind !== 'after' ? array_reverse($this->filters[$kind][$key]) : $this->filters[$kind][$key] ;
		foreach ($filters as $filter) {
			$callback = $kind($callback, $filter);
		}
		return $callback;
	}

	protected function execute($callback, $params, $method, $url) 
	{
		$filterKey = $this->filterKey($method, $url);
		$method = strtoupper($method);
		$callback = before($callback, quote($this)->setSessionValue);
		$keys = array($filterKey,$this->filterKey('*',$url),$this->filterKey($method,'*'),$this->filterKey('*','*'));
		foreach (array('around','before','after') as $kind) {
			$filterKeys = $keys;
			$filterKeys = $kind === 'after' ? array_reverse($filterKeys) : $filterKeys ;
			foreach ($filterKeys as $key) {
				$callback = $this->setFilterCallback($kind, $key, $callback);
			}
		}
		$parameters = callbackParams($callback);
		$args = array();
		foreach ($parameters as $param) {
			$args[$param->name] = $params[$param->name];
		}
		$this->args = $this->factory->build('PwArrayWrapper', $args);
		return call_user_func_array($callback, $args);
	}

	protected function filterKey($httpMethod, $url)
	{
		return strtoupper($httpMethod) . '@' . $url;
	}

	protected function setFilter($httpMethod, $urls, $callback, &$filters)
	{
		$urls = !is_array($urls) ? explode('|', $urls) : $urls ;
		foreach ($urls as $url) {
			$filterKey = $this->filterKey($httpMethod, $url);
			if (!isset($filters[$filterKey])) {
				$filters[$filterKey] = array();
			}
			array_push($filters[$filterKey], $callback);
		}
	}

	public function before($httpMethod, $urls, $callback) 
	{
		$this->setFilter($httpMethod, $urls, $callback, $this->filters['before']);
	}
	public function after($httpMethod, $urls, $callback) 
	{
		$this->setFilter($httpMethod, $urls, $callback, $this->filters['after']);
	}

	public function around($httpMethod, $urls, $callback) 
	{
		$this->setFilter($httpMethod, $urls, $callback, $this->filters['around']);
	}
	
	protected function redirect($path) 
	{
		$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
		$host = (preg_match('%^http://|https://%', $path) > 0) ? '' : "$protocol://" . $_SERVER['HTTP_HOST'];
		$uri = is_string($this->options->mountPoint) ? $this->options->mountPoint : '';
		$this->session->error = $this->error;
		$this->session->success = $this->success;
		header("Location: $host$uri$path");
		return false;
	}
	
	public function render($fileName, $variableArray=array()) 
	{
		$variableArray['confs'] = $this->confs;
		$variableArray['viewName'] = $fileName;
		$variableArray['options'] = $this->options;
		$variableArray['request'] = $this->request;
		$variableArray['session'] = $this->session;
		if(isset($this->error)) {
			$variableArray['error'] = $this->error;
		}
		if(isset($this->success)) {
			$variableArray['success'] = $this->success;
		}
		if (is_string($this->options->layout)) {
			$contentTemplate = $this->factory->build('PwTemplate', $this->root(), $fileName);	// create content template
			$variableArray['content'] = $contentTemplate->render($variableArray);				// render and store contet
			$layoutTemplate = $this->factory->build('PwTemplate', $this->root(), $this->options->layout);	// create layout template
			return $layoutTemplate->render($variableArray);										// render layout template and return
		} 
		else {
			$template = $this->factory->build('PwTemplate', $this->root(), $fileName);			// create template
			return $template->render($variableArray);											// render template and return
		}
	}

	protected function sendFile($filename, $contentType, $path) 
	{
		header("Content-type: $contentType");
		header("Content-Disposition: attachment; filename=$filename");
		return readfile($path);
	}

	protected function sendDownload($filename, $path) 
	{
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=$filename".";");
		header("Content-Transfer-Encoding: binary");
		return readfile($path);
	}
}

