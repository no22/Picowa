<?php
/**
 * Picowa Lightweight Web Application Class
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
	public $error;
	public $success;
	public $uses = array(
		'session' => 'PwSessionWrapper',
		'request' => 'PwRequestWrapper',
	); 

	public function __construct($options = array()) 
	{
		parent::__construct();
		if (!isset($options['mountPoint'])) {
			$options['mountPoint'] = PICOWA_MOUNT_POINT;
		}
		$confs = isset($options['config']) ? $options['config'] : array() ;
		$this->attach('confs', array('PwConfig', $confs));
		$this->attach('options', array('PwArrayWrapper', $options));
		$this->attach('url', array('PwUrl', $options['mountPoint']));
		$this->initializeSession();
		session_start();
		Pico::cfg()->use_error_handler and set_error_handler(array($this, 'handleError'), 2);
	}

	protected function initializeSession()
	{
		ini_set('session.serialize_handler', 'php');
		ini_set('session.name', Pico::cfg()->session_name);
		ini_set('session.cookie_lifetime', Pico::cfg()->cookie_lifetime);
		ini_set('session.auto_start', 0);
		ini_set('session.save_path', PICOWA_TEMP_PATH . 'sessions');
	}
	
	public function root() { return PICOWA_APP_PATH; }

	public function path($path) { return $this->root() . $path; }
	
	public function options() { return $this->options; }

	public function session() { return $this->session; }

	public function request() { return $this->request; }

	public function url() { return $this->url; }

	public function args() { return $this->args; }

 	public function handleError($errno, $message, $file, $line) 
	{
		$aError = compact($errno, $message, $file, $line);
		Pico::cfg()->use_debug_mail and dbgmail(print_r($aError, true), Pico::cfg()->admin_email);
		Pico::cfg()->use_debug_log and dbglog(print_r($aError, true));
		header("HTTP/1.0 500 Server Error");
		PICOWA_DEBUG_MODE and print_r($aError);
		echo $this->render('500');
		die();
	}

	public function show404() 
	{
		header("HTTP/1.0 404 Not Found");
		echo $this->render('404');
		die();
	}

	public function http($method, $url, $callback, $conditions=array()) 
	{
		$this->event($method, $url, $callback, $conditions, $this->mappings);
	}

	public function get($url, $callback, $conditions=array()) 
	{
		$this->event('get', $url, $callback, $conditions, $this->mappings);
	}

	public function post($url, $callback, $conditions=array()) 
	{
		$this->event('post', $url, $callback, $conditions, $this->mappings);
	}

	public function put($url, $callback, $conditions=array()) 
	{
		$this->event('put', $url, $callback, $conditions, $this->mappings);
	}

	public function delete($url, $callback, $conditions=array()) 
	{
		$this->event('delete', $url, $callback, $conditions, $this->mappings);
	}

	protected function event($httpMethod, $url, $callback, $conditions=array(), &$mappings) 
	{
		if (is_string($callback) && function_exists($callback)) {
			array_push($mappings, array($httpMethod, $url, $callback, $conditions));
		}
		else if (is_array($callback) && method_exists($callback[0], $callback[1])) {
			array_push($mappings, array($httpMethod, $url, $callback, $conditions));
		}
		else if (is_object($callback) && $callback instanceof Closure) {
			array_push($mappings, array($httpMethod, $url, $callback, $conditions));
		}
	}

	public function run() 
	{
		echo $this->processRequest();
	}

	protected function processRequest() 
	{
		$url = $this->url;
		foreach ($this->mappings as $mapping) {
			if ($url->match($mapping[0], $mapping[1], $mapping[3])) {
				return $this->execute($mapping[2], $url, $mapping[0]);
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

	protected function setFilterCallback($kind, $callback, $url)
	{
		if (!isset($this->filters[$kind])) return $callback;
		$filters = $kind !== 'after' ? array_reverse($this->filters[$kind]) : $this->filters[$kind] ;
		foreach ($filters as $filter) {
			if ($url->match($filter[0], $filter[1], $filter[3])) {
				$callback = $kind($callback, $filter[2]);
			}
		}
		return $callback;
	}

	protected function execute($callback, $url, $method) 
	{
		$method = strtoupper($method);
		$params = $url->params;
		$callback = before($callback, quote($this)->setSessionValue);
		$urlMatch = $this->factory->build('PwUrl',$url->mountPoint);
		foreach (array('around','before','after') as $kind) {
			$callback = $this->setFilterCallback($kind, $callback, $urlMatch);
		}
		$this->attach('args', array('PwArrayWrapper', $params));
		return call_user_func_array($callback, $params);
	}

	public function before($httpMethod, $urls, $callback, $conditions=array()) 
	{
		$this->event($httpMethod, $urls, $callback, $conditions, $this->filters['before']);
	}
	public function after($httpMethod, $urls, $callback, $conditions=array())
	{
		$this->event($httpMethod, $urls, $callback, $conditions, $this->filters['after']);
	}

	public function around($httpMethod, $urls, $callback, $conditions=array())
	{
		$this->event($httpMethod, $urls, $callback, $conditions, $this->filters['around']);
	}
	
	public function redirect($path, $isFull = false) 
	{
		$uri = $isFull ? $path : $this->url()->path($path);
		$this->session->error = $this->error;
		$this->session->success = $this->success;
		header("Location: {$uri}");
		return false;
	}
	
	public function render($fileName, $variableArray=array()) 
	{
		$variableArray['controller'] = $this;
		$variableArray['viewName'] = $fileName;
		if(isset($this->error)) {
			$variableArray['error'] = $this->error;
		}
		if(isset($this->success)) {
			$variableArray['success'] = $this->success;
		}
		if (is_string($this->options->layout)) {
			$contentTemplate = $this->factory->build('PwTemplate', $this->root(), $fileName);
			$variableArray['content'] = $contentTemplate->render($variableArray);
			$layoutTemplate = $this->factory->build('PwTemplate', $this->root(), $this->options->layout);
			return $layoutTemplate->render($variableArray);
		} 
		else {
			$template = $this->factory->build('PwTemplate', $this->root(), $fileName);
			return $template->render($variableArray);
		}
	}

	public function sendFile($filename, $contentType, $path, $type = 'attachment')
	{
		header("Content-type: $contentType");
		header("Content-Disposition: $type; filename=$filename");
		return readfile($path);
	}

	public function sendDownload($filename, $path) 
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

