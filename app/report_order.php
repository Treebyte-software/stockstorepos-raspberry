<?php


function printAlertMoveOrder($order_id, $app, $db, $pdo) {
    
    $conditions = [];
    $report_code = '810';
    $pos_id = '';
    $pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
    foreach ($pos_system_settings as $setting) {
        $pos_id = $setting['POS_ID'];
    }	
	
    writeLog("printAlertMoveOrder","document_id=".$order_id,$app);

    if ($order_id == '') {
        writeLog("printAlertMoveOrder","ERROR: document_id is nul !!!",$app);
    } else {
        $prod = $db->SSV_SALE_DETAILS_COURSE_PROD;
        $prod->select('PRODUCTION_CENTER_ID');
        $prod->select('QUEUE_IDENTIFIER');	
        $prod->where('HEADING_ID',$order_id);
        $prod->and('COURSE_PRINTED',1);
        $prod->and('PRODUCTION_CENTER_ID IS NOT NULL');
        $prod->group('PRODUCTION_CENTER_ID, QUEUE_IDENTIFIER');
            $conditions[]='document_id';
        $conditions[]=$order_id;
        foreach ($prod as $prod_row) {
            writeLog("printAlertMoveOrder","   in ciclo di prod_row ".$prod_row['PRODUCTION_CENTER_ID'],$app);
            $prod_center_print = $db->SST_PRODUCTION_CENTERS;
            $prod_center_print->where('ID',$prod_row['PRODUCTION_CENTER_ID']);
            foreach ($prod_center_print as $prod_center) {
                writeLog("printAlertMoveOrder","      in ciclo di prod_center ".$prod_center['QUEUE_IDENTIFIER'],$app);
                $pos_devices = $db->SST_POS_DEVICES;
                $pos_devices->where('POS_ID',$pos_id);
                $pos_devices->and('PRODUCTION_CENTER_ID',$prod_center['ID']);
                foreach ($pos_devices as $dett_printer) {
                    writeLog("printAlertMoveOrder","         in ciclo di pos_devices ".$dett_printer['ID'],$app);
                    $status = stampa($pos_id, $dett_printer, $report_code, $conditions, $prod_center['QUEUE_IDENTIFIER'], $app, $db, $pdo);
                }
            }
        }
    }
}
