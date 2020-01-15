<?php

require_once('poslivesync_download.php');
require_once('poslivesync_import.php');

$app->get('/poslivesync', function() use ($app, $db) {

	packet_download($app, $db);
	packet_import($app, $db);

});

?>

