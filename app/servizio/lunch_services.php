<?php

$app->get('/closelunchservice', function () use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
    
	$app->response()->header("Content-Type", "application/json");
    
    writeLog("closelunchservice","Start close service",$app);
    /*
        Facoltativo: crea backup comande
        Chiude tutte le comande eventualmente ancora aperte
        Libera tutti i tavoli eventualmente ancora occupati
        Registra la fine del servizio
    */
    $db->transaction = 'BEGIN';
    $esito = true;
    $messageResult = "";
    try {
        // Chiude tutte le comande aperte
        $closing_date = getCurrentTimeStamp($db);
        // headers
        writeLog("closelunchservice","Start close headings",$app);
        $order_headings = $db->SSV_ORDER_HEADINGS;
        $order_headings->where('ORDINATION_CLOSED',0);
        $order_headings->update(['ORDINATION_CLOSED' => 1,'LAST_UPDATE_DATE' => $closing_date]);
        writeLog("closelunchservice","End close headings",$app);
        // details
        writeLog("closelunchservice","Start close details",$app);
        $order_details = $db->SSV_ORDER_DETAILS;
        $order_details->where('ORDINATION_CLOSED',0);
        $order_details->update(['ORDINATION_CLOSED' => 1,'LAST_UPDATE_DATE' => $closing_date]);
        writeLog("closelunchservice","End close details",$app);
        // notes
        writeLog("closelunchservice","Start close notes",$app);
        $order_details_notes = $db->SSV_ORDER_DETAILS_NOTES;
        $order_details_notes->where('ORDINATION_CLOSED',0);
        $order_details_notes->update(['ORDINATION_CLOSED' => 1,'LAST_UPDATE_DATE' => $closing_date]);
        writeLog("closelunchservice","End close notes",$app);
        // table situation
        writeLog("closelunchservice","Start clear tables situation",$app);
        $lunch_Tables_situation = $db->SST_LUNCH_TABLES_SITUATION;
        $lunch_Tables_situation->update(['LUNCH_STATE_ID' => 1,
                                         'OPENING_TIME' => NULL,
                                         'LAST_TIME' => $closing_date,
                                         'REAL_GUESTS' => 0,
                                         'COVERS_NUM' => 0,
                                         'OPERATOR_CODE' => '-NOTHING-']);
        writeLog("closelunchservice","End clear tables situation",$app);
                                         
        // close service
        writeLog("closelunchservice","Start save record of closing",$app);
        $shop_id = NULL;
        $pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
        foreach ($pos_system_settings as $setting) {
            $shop_id = $setting['SHOP_ID'];
        }

        $newId = getNewId($db, 'SST_LUNCH_SERVICES');
        $lunch_service = $db->SST_LUNCH_SERVICES;
        $lunch_service->insert(['ID' => $newId,
                                'CLOSE_DATE' => $closing_date,
                                'SHOP_ID' => $shop_id]);
        writeLog("closelunchservice","End save record of closing",$app);
    } catch(Exception $e) {
        if ($e->getCode() != 2) {
            $esito = false;
            $messageResult = $e->getCode().' - '.$e->getMessage();
            writeLog("closelunchservice","ERRORE ".$messageResult,$app);
            writeLog("closelunchservice","ROLLBACK",$app);
            $db->transaction = 'ROLLBACK';
        } else {
            $esito = true;
        }
    }
    $db->transaction = 'COMMIT';
    writeLog("closelunchservice","End close service",$app);

	echo json_encode(array("status" => $esito,"message" => $messageResult));

});


$app->get('/checklunchservice/:mobile_pos_id', function ($mobile_pos_id) use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
    
	$app->response()->header("Content-Type", "application/json");

    writeLog("closelunchservice","Start check lunch service for mobile_pos_id ".$mobile_pos_id,$app);

    $shop_id = NULL;
    $pos_id = NULL;
    $first_time = "false";
	$messageResult = "";
	$esito = true;
    $pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
    foreach ($pos_system_settings as $setting) {
        $shop_id = $setting['SHOP_ID'];
        $pos_id = $setting['POS_ID'];
    }
    
    // get last closing service
    $sst_lunch_services = $db->SST_LUNCH_SERVICES;
    $sst_lunch_services->where('SHOP_ID',$shop_id);
    $close_date = $sst_lunch_services->max('CLOSE_DATE');
    $sst_lunch_services = $db->SST_LUNCH_SERVICES;
    $sst_lunch_services->where('CLOSE_DATE',$close_date);
    foreach($sst_lunch_services as $service) {
        $sst_lunch_services_mobile = $db->SST_LUNCH_SERVICES_MOBILE;
        $sst_lunch_services_mobile->where('LUNCH_SERVICE_ID',$service['ID']);
        $sst_lunch_services_mobile->and('MOBILE_POS_ID',$mobile_pos_id);
        
        if (count($sst_lunch_services_mobile) == 0) {
            // prima volta
			try {
				$newId = getNewId($db, 'SST_LUNCH_SERVICES_MOBILE');
				$sst_lunch_services_mobile->insert(['ID'=>$newId,
													'LUNCH_SERVICE_ID'=>$service['ID'],
													'POS_ID'=>$pos_id,
													'MOBILE_POS_ID'=>$mobile_pos_id]);
			} catch(Exception $e) {
				if ($e->getCode() != 2) {
					$esito = false;
					$messageResult = $e->getCode().' - '.$e->getMessage();
                    writeLog("closelunchservice","ERROR ".$messageResult,$app);
				} else {
					$esito = true;
				}
			}
            //$first_time = "true";
            $first_time = $service['CLOSE_DATE'];
        } else {
            // volta successiva
            $first_time = "false";
        }
        
        break;        
    }

    writeLog("closelunchservice","End check lunch service for mobile_pos_id with result ".$first_time,$app);
    	
    echo json_encode(array("status" => $esito,"message" => $first_time));
});


