<?php

function writeLog($mark, $msg, $app) {

	if ($mark === "poslivesync") {

		$ora = new \DateTime();
		$dateTimeDML = $ora->format('Y-m-d H:i:s');

		error_log($dateTimeDML." [Client $_SERVER[REMOTE_ADDR]] [StockStorePOS][".$mark."] ".$msg."\n",3,getFolderLog().'poslivesync_php.log');
	} else {
		if ($app->config('LOGS') == "S") {
			error_log("[Client $_SERVER[REMOTE_ADDR]] [StockStorePOS][".$mark."] ".$msg,0);
		}
	}
}


function getFolderLog() {

        try {
                $day = date('d');
                $month = date('m');
                $year = date('Y');

                $folder = 'iaprofiles/temp/'.$year.'/'.$month.'/'.$day.'/';
	
                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                    if (!file_exists($folder)) {
                        $folder = 'iaprofiles/temp/';
                    }
                }
        } catch(Exception $e) {
                $folder = 'iaprofiles/temp/';
		error_log("[StockStorePOS][Log] ERRORE ".$e->getCode().' - '.$e->getMessage(),0);
        }

	return $folder;
}
