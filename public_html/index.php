<?php
require_once dirname(dirname(__FILE__)).'/_lib/AutoLoad.php';
require_once dirname(dirname(__FILE__)).'/_pico/libs/bootstrap.php';

if (isset($_GET['url']) && $_GET['url'] === 'favicon.ico') return;

if (!include(PICOWA_APP_PATH . 'app.php')) {
	trigger_error("Picowa application could not be found.", E_USER_ERROR);
}
