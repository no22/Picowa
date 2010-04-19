<?php
//>php %FILE%
/**
 * stdlib ver.0.0.1
 * @package		Picowa
 */	

!count(debug_backtrace()) and require dirname(__FILE__)."/AutoLoad.php";

/**
 * startsWith, endsWith, matchesIn
 * http://blog.anoncom.net/2009/02/20/124.html
 */
/**
 * startsWith
 * 文字列$sHaystackが文字列$sNeedleで始まっていれば真を返す
 * >>> eq(startsWith('ABC1234', 'ABC'), true);
 * @param string $sHaystack
 * @param string $sNeedle
 * @return boolean
 */
function startsWith($sHaystack, $sNeedle) {
	return strpos($sHaystack, $sNeedle, 0) === 0;
}

/**
 * endsWith
 * 文字列$sHaystackが文字列$sNeedleで終わっていれば真を返す
 * >>> eq(endsWith('ABC1234', '234'), true);
 * @param string $sHaystack
 * @param string $sNeedle
 * @return boolean
 */
function endsWith($sHaystack, $sNeedle) {
	$iLength = (strlen($sHaystack) - strlen($sNeedle));
	if ($iLength < 0) { return false; }
	return strpos($sHaystack, $sNeedle, $iLength) !== false;
}

/**
 * matchesIn
 * >>> eq(matchesIn('ABC1234', 'C12'), true);
 * 文字列$sHaystackが文字列$sNeedleを含んでいれば真を返す
 * @param string $sHaystack
 * @param string $sNeedle
 * @return boolean
 */
function matchesIn($sHaystack, $sNeedle) {
	return strpos($sHaystack, $sNeedle) !== false;
}

/**
 * dbglog
 * デバッグログ出力
 * @param string $sText
 * @param string $sFilePath = null
 * @return 
 */
function dbglog($sText,$sFilePath = null)
{
	$sFilePath = is_null($sFilePath) ? MWDATAPATH.'mwdebug.log' : $sFilePath ;
	error_log(date('Y-m-d H:i:s: ').$sText."\n", 3, $sFilePath);
}

/**
 * dbgmail
 * デバッグメール送信
 * @param string $sText
 * @param string $sMail
 * @return 
 */
function dbgmail($sText, $sMail)
{
	error_log(date('Y-m-d H:i:s: ').$sText."\n", 1, $sMail);
}

/**
 * sendm
 * 簡易メール送信
 * @param string $sMail
 * @param string $sSubject
 * @param string $sBody
 * @param string $sFrom = null
 * @return 
 */
function sendm($sMail,$sSubject,$sBody,$sFrom = null)
{
	$sHeader = $sFrom ? "From: {$sFrom}" : null ;
	$sOption = $sFrom ? "-f{$sFrom}" : null ;
	return mb_send_mail($sMail,$sSubject,$sBody,$sHeader,$sOption);
}

/**
 * ref
 * http://d.hatena.ne.jp/anatoo/20090320/1237530764
 * @param mixed $obj
 * @return mixed
 */
if (!function_exists('ref')) {
	function ref($obj) { return $obj; }
}

/**
 * doctest
 * @param string $file
 * @return 
 */
if (!function_exists('doctest')) {
	function doctest($file) 
	{ 
		return SimpleDocTest::doctest($file); 
	}
}

//
// テスト
//
!count(debug_backtrace()) and doctest(__FILE__);
