<?php
/**
 * SimpleDocTest 簡易doctestクラス
 *
 * @package		Picowa
 * @since		2009-08-04
 */	
/**
 * eq
 * @param mixed $mValue1
 * @param mixed $mValue2
 * @return 
 */
function eq($mValue1, $mValue2)
{
    $bResult = is_object($mValue2) ? $mValue1 == $mValue2 : $mValue1 === $mValue2 ;
    echo $bResult ? "PASS.\n" : "FAIL.\n" ;
}

/**
 * ne
 * @param mixed $mValue1
 * @param mixed $mValue2
 * @return 
 */
function ne($mValue1, $mValue2)
{
    $bResult = is_object($mValue2) ? $mValue1 != $mValue2 : $mValue1 !== $mValue2 ;
    echo $bResult ? "PASS.\n" : "FAIL.\n" ;
}

class SimpleDocTest
{
    public $sourcePath;

    /**
     * __construct
     * @param string $sFilePath = NULL
     * @return 
     */
    public function __construct($sFilePath = NULL)
    {
        if (!is_null($sFilePath)) {
            $this->sourcePath = $sFilePath;
        }
    }
    
    /**
     * displayTestResult
     * @param string $sTest
     * @param int $iNum
     * @return 
     */
    private function displayTestResult($sTest, $iNum, $sResult = null)
    {
        $sOutput = null;
        echo "----------------------------------------\n";
        echo "-- File: {$this->sourcePath}\n";
        echo "-- Line: {$iNum}\n";
        foreach (explode("\n", $sTest) as $line) {
            echo ">> {$line}\n";
        }
        if (empty($sResult)) {
            if (eval($sTest) === false) {
                echo "Parse error.\n";
            }
        }
        else {
            ob_start();
            if (eval($sTest) === false) {
                echo "Parse error.\n";
            }
            else {
                $sOutput = ob_get_contents();
            }
            ob_end_flush();
            $sResult = rtrim(preg_replace('/\x0D\x0A|\x0D|\x0A/s',"\n", $sResult));
            $sOutput = rtrim(preg_replace('/\x0D\x0A|\x0D|\x0A/s',"\n", $sOutput));
            if (rtrim($sOutput) === rtrim($sResult)) {
                echo "PASS.\n";
            }
            else {
                echo "FAIL.\n";
                echo "expected:\n";
                echo "{$sResult}\n";
            }
        }
    }

    /**
     * __invoke
     * @param string $sFilePath = NULL
     * @return 
     */
    public function __invoke($sFilePath = NULL)
    {
        $this->invoke($sFilePath);
    }

    /**
     * invoke
     * @param string $sFilePath = NULL
     * @return 
     */
    public function invoke($sFilePath = NULL)
    {
        $sFilePath = is_null($sFilePath) ? $this->sourcePath : $sFilePath ;
        $num = 1;
        $aCode = array();
        $aResult = array();
        $isMultiLine = false;
        $oFile = new SplFileObject($sFilePath);
        foreach ($oFile as $line) {
            if (preg_match('/^\s*\*\s*>>>>\s*$/',$line)) {
                $isMultiLine = true;
                $aCode = array();
            }
            else if (preg_match('/^\s*\*\s*>>>\s*(.*)$/',$line,$matched)) {
                $this->displayTestResult($matched[1],$num);
            }
            else if ($isMultiLine) {
                if (preg_match('/^\s*\*\s*<<<<\s*$/',$line)) {
                    $this->displayTestResult(implode("\n", $aCode),$num, implode("\n", $aResult));
                    $isMultiLine = false;
                    $aCode = array();
                    $aResult = array();
                }
                else if (preg_match('/^\s*\*\|(.*)$/',$line,$matched)) {
                    $sResult = $matched[1];
                    $aResult[] = $sResult;
                }
                else if (preg_match('/^\s*\*(.*)$/',$line,$matched)) {
                    $sCode = $matched[1];
                    $aCode[] = $sCode;
                }
                else {
                    $isMultiLine = false;
                    $aCode = array();
                    $aResult = array();
                }
            }
            $num++;
        }
    }
    
    /**
     * doctest
     * @param string $sFile
     * @return 
     */
    public static function doctest($sFile)
    {
        $oTest = new SimpleDocTest($sFile);
        $oTest->invoke();
    }

}

/*
Usage:
	クラスや関数定義冒頭のコメント中に以下のようにPHPコードと出力結果を書いておく
	
	* >>>>
	* eq(foo(1,2),3);
	*|6
	* <<<<

	ソースファイルの冒頭に以下のように記述しておく

// AutoLoadの場所によって記述を変えること！
!count(debug_backtrace()) and require dirname(__FILE__)."/AutoLoad.php";

	ソースファイルの末尾に以下のように記述しておく

!count(debug_backtrace()) and doctest();

	コマンドラインでソースファイルを実行するとテストが実行される。
*/
