<?php
/**
 * bind ver.0.1.0
 * PHPでカリー化もどきを実現するライブラリ
 * 元ネタページ：	http://d.hatena.ne.jp/anatoo/20090402/1238603946
 * 必要なファイル：	Curry.php, Quotation.php
 * @package		Bind
 * @since		2009-08-04
 */	

!count(debug_backtrace()) and require_once dirname(__FILE__)."/AutoLoad.php";

/**
 * quote
 * メソッドを取り出すためのラップオブジェクトを作成する
 * @param object $obj
 * @return object Quotation
 */
function quote($obj)
{
	return new Quotation($obj);
}

/**
 * callee
 * 自分自身をコールバックオブジェクトとして取得する
 * @param  void
 * @return object Curry
 */
function callee()
{
	list(, $frame) = debug_backtrace() + array(1 => false);
	if (!$frame) throw new BadFunctionCallException('You must call in function');
	
	$callback = isset($frame['object']) ? array($frame['object'], $frame['function']) :
		(isset($frame['class']) ? array($frame['class'], $frame['function']) : 
		$frame['function']);
	$args = func_get_args();
	
	return $args ? Curry::make($callback, $args) : $callback;
}

/**
 * method
 * クラス内部で自身のメソッドを取り出す
 * @param string $name
 * @return object Curry
 */
function method($name)
{
	list(, $frame) = debug_backtrace() + array(1 => false);
	if (!isset($frame['class'])) throw new BadFunctionCallException('You must call in class method');
	
	$callback = array(isset($frame['object']) ? $frame['object'] : $frame['class'], $name);
	$args = func_get_args();
	array_shift($args);
	
	return $args ? Curry::make($callback, $args) : $callback;
}

/**
 * bind
 * 関数に引数を与えコールバックオブジェクトを生成する
 * カリー化もどき
 * @param function $callback
 * @return object Curry
 */
function bind($callback)
{
	$args = func_get_args();
	array_shift($args);
	return Curry::make($callback, $args);
}

/**
 * call
 * call_user_func_arrayのエイリアス
 * @param function $callback
 * @return 
 */
function call($callback)
{
	$args = func_get_args();
	array_shift($args);
	return call_user_func_array($callback, $args);
}

/**
 * apply
 * call_user_func_arrayのエイリアス
 * @param function $callback
 * @param array $args
 * @return 
 */
function apply($callback, $args)
{
	return call_user_func_array($callback, $args);
}

/**
 * once
 * 引数のない関数を一度だけ評価する
 * @param func $fnCallback
 * @return mixed
 */
function once($fnCallback)
{
	return new Once($fnCallback);
}


/**
 * callbackParams
 * コールバックの引数リフレクションの配列を取得する
 * @param mixed $mCallback
 * @return mixed
 */
function callbackParams($mCallback)
{
	if (is_string($mCallback)) {
		if (!matchesIn($mCallback, '::')) {
			// function
			return ref(new ReflectionFunction($mCallback))->getParameters();
		}
		else {
			// static method
			return ref(new ReflectionMethod($mCallback))->getParameters();
		}
	}
	else if (is_array($mCallback)) {
		$mFunc = $mCallback[0];
		if (is_string($mFunc)) {
			// function or static method
			return callbackParams($mFunc);
		}
		else if (is_object($mFunc)) {
			if ($mFunc instanceof Curry) {
				// curry object
				return $mFunc->getParams();
			}
			else {
				// instance method
				return ref(new ReflectionMethod(get_class($mFunc),$mCallback[1]))->getParameters();
			}
		}
	}
	return false;
}

function before($mCallback, $mBefore)
{
	return Curry::makeWithFilter($mCallback, $mBefore);
}

function after($mCallback, $mAfter)
{
	return Curry::makeWithFilter($mCallback, null, $mAfter);
}

function around($mCallback, $mAround)
{
	return Curry::makeWithFilter($mCallback, null, null, $mAround);
}

//
// テスト
//
!count(debug_backtrace()) and doctest(__FILE__);
