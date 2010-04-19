<?php
/**
 * Once
 * 一度だけコールバックを実行し、それ以降の呼び出しは最初の実行結果を返すクラス
 * @package		
 * @since		2009-8-31
 */
!count(debug_backtrace()) and require_once dirname(__FILE__)."/AutoLoad.php";

class Once
{
	protected $callback = null;
	protected $value = null;
	protected $invoked = false;
	
	/**
	 * __construct
	 * コンストラクタ
	 * @param func $fnCallback
	 * @return 
	 */
	public function __construct($fnCallback)
	{
		$this->callback = $fnCallback;
		$this->value = null;
		$this->invoked = false;
	}
	
	/**
	 * __invoke
	 * PHP5.3用
	 * @param  
	 * @return 
	 */
	public function __invoke()
	{
		return $this->invoke();
	}

	/**
	 * invoke
	 * PHP5.2用
	 * @param  
	 * @return 
	 */
	public function invoke()
	{
		if (!$this->invoked) {
			$this->value = call_user_func($this->callback);
			$this->invoked = true;
		}
		return $this->value;
	}
}

//
// DocTest
//
!count(debug_backtrace()) and doctest(__FILE__);
