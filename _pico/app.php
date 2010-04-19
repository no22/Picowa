<?php
/**
 * Pico Web Application Framework Sample
 * @package		Picowa
 * @since		2010-04-09
 */
$app = new Picowa;

$app->get('/','hello');
$app->get('/ja/','hello');
	function hello() {
		return 'Hello world!';
	}

$app->after('get','/ja/','translate');
	function translate($text) {
		return strtr($text, array('Hello'=>'こんにちは', 'world'=>'世界'));
	}

$app->run();
