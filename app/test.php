<?php

$app->get('/order_by_table/:table_id', function ($table_id) use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
	$app->response()->header("Content-Type", "application/json");

	$lunch_table = $db->SSV_LUNCH_TABLES_SITUATION_HALL->where('LUNCH_TABLE_ID',$table_id)->fetch();
	if ($lunch_table != false) {
		$headings = $db->SSV_ORDER_HEADINGS->where('LUNCH_TABLE_ID',$table_id)->and('ORDINATION_CLOSED',0)->fetch();
		if ($headings != false) {
			// spostata sotto $lunch_table['HEADING'] = $headings;
			//$details = $db->SSV_ORDER_DETAILS->where('HEADING_ID',$headings['ID'])->fetch();
			$details = $db->SSV_ORDER_DETAILS->where('HEADING_ID',$headings['ID']);
			if ($details != false) {
				$headings['DETAILS'] = $details;
			} else {
				$headings['DETAILS'] = false;
			}
			$notes = $db->SSV_ORDER_DETAILS_NOTES->where('HEADING_ID',$headings['ID']);
			if ($notes != false) {
				$headings['NOTES'] = $notes;
			} else {
				$headings['NOTES'] = false;
			}
			$lunch_table['HEADING'] = $headings;
		} else {
			$lunch_table['HEADING'] = false;
		}
	}
	
	echo json_encode(array("LUNCH_TABLE_SITUATION"=>$lunch_table));

});


$app->get('/testPrinter', function () use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
	//$app->response()->header("Content-Type", "application/json");
	writeLog("testPrinter","Start ",$app);
	
	$systemSettings = $db->SST_POS_SYSTEM_SETTINGS;
	foreach($systemSettings as $syssett) {
		$pos_id = $syssett['POS_ID'];
	}
	writeLog("testPrinter","pos_id = ".$pos_id,$app);
	
	$printer = $db->SST_POS_DEVICES;
	$printer->where('PRODUCTION_CENTER_ID IS NOT NULL AND POS_ID = ?',$pos_id);
	$printer->order('DEVICE_ADDRESS');
	
	foreach($printer as $singlePrinter) {
			$ip = $singlePrinter['DEVICE_ADDRESS'];

	$prodCenter = $db->SST_PRODUCTION_CENTERS;
	$prodCenter->where('ID',$singlePrinter['PRODUCTION_CENTER_ID']);
	foreach($prodCenter as $prodC) {
		$prod =$prodC['DESCRIPTION'];
	}
				//$prodCenter->fetch();
				
			try {
				$starttime = microtime(true);
				$file      = @fsockopen ($ip, 80, $errno, $errstr, 10);
				$stoptime  = microtime(true);
				$status    = 0;
			} catch(Exception $e) {
				$stoptime  = microtime(true);
			}

			if (!$file) {
				$status = "down";  // Site is down
			} else {
				fclose($file);
				$status = ($stoptime - $starttime) * 1000;
				$status = floor($status);
			}
			
			echo "<br>".$ip." [".$prod."] : ".$status;
			writeLog("testPrinter",$ip." [".$prod."] : ".$status,$app);
	}
	
	writeLog("testPrinter","End ",$app);

/*

// Function to check response time
function pingDomain($domain){
	
	try {
		try {
		$starttime = microtime(true);
		$file      = @fsockopen ($domain, 80, $errno, $errstr, 10);
		$stoptime  = microtime(true);
		$status    = 0;
	} catch(Exception $e) {
		$stoptime  = microtime(true);
//		$status = -1;
//		$file = null;
//		$esito = $esito."EXCEPT_FIELDS LUNCH TABLE - ";
	}
	} finally {
//				$file = null;
        //fclose($file);
	}
	if (!$file) $status = "down";  // Site is down
    else {
//        fclose($file);
        $status = ($stoptime - $starttime) * 1000;
        $status = floor($status);
    }
    return $status;
}

echo "<br>192.168.1.21 : ";
echo pingDomain('192.168.1.21');

echo "<br>192.168.1.22 : ";
echo pingDomain('192.168.1.22');

echo "<br>192.168.1.23 : ";
echo pingDomain('192.168.1.23');

echo "<br>192.168.1.24 : ";
echo pingDomain('192.168.1.24');

echo "<br>192.168.1.25 : ";
echo pingDomain('192.168.1.25');

*/

});