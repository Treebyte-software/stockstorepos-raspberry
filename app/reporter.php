<?php

require_once "classes/report.php";
require_once "classes/clsEntity.php";

require_once "report_order.php";

function stampa ($pos_id, $pos_device, $report_code, $conditions, $queue_identifier, $app, $db, $pdo) {
	
    // $app->response()->header("Content-Type", "application/json");
    $app->response()->header("Content-Type", "text/html");

	$device_id = "";
	$indirizzo_ipd = "";
	$device_id = $pos_device['DEVICE_ID'];
	$indirizzo_ip = $pos_device['DEVICE_ADDRESS'];

	$status = "1";
	
	writeLog("reporter/stampa", "Device_id=".$pos_device['DEVICE_ID']." Device_address=".$pos_device['DEVICE_ADDRESS']." Production_center_id=".$pos_device['PRODUCTION_CENTER_ID']." Report_code=".$report_code." Queue_identifier=".$queue_identifier, $app);
	
	try {
		try {
			// $debug = array();
						
			// $debug[] = "In stampa ";
            writeLog("reporter/stampa", "debug In stampa", $app);
            

			$printer = new Device($db, $device_id);
			// [ESCPos | Intelligent]
			$printerType = $printer->getType();
//writeLog("reporter/stampa", $printer->getCommands(),$app);
			$allCommands = new \SimpleXMLElement($printer->getCommands());

			$commands = $allCommands->xpath('/command/library[@name="no_fiscal"]');
			$fonts = $allCommands->xpath('/command/common/fonts');
			// da qui cancellare
			//$posDevice = new PosDevices($db, $pos_id, $device_id);
			//$valore = $posDevice->getHostPort();
			// a qui cancellare
			
			$valore = $indirizzo_ip;

			if (strpos($valore,":") === false) {
				$port = '9100';
				$host = $valore;
			} else {
				list($host, $port) = explode(":",$valore);
			}
			if ($port === "") {
				$port = '9100';
			}
			
			//			$debug[] = "Host = ".$host." Port = ".$port;
			
			$stampaObj = new ThermalReport();
			
			//			$debug[] = "Inizio getDat per il report ".$report_code;

			writeLog("reporter/stampa","Inizio getDat per il report ".$report_code,$app);

			$dat = $stampaObj->getDat($app, $db, $report_code, $conditions, $pdo);

			writeLog("reporter/stampa","Fine getDat per il report ".$report_code,$app);

			$ora = new \DateTime();
                        $date_for_save = $ora->format('Y-m-d_H-i-s');
			$dat->asXml(getFolderLog().$date_for_save.'_'.$report_code."_dat_".$device_id."_".$printerType."-".uniqid().".xml");
			
			//			$debug[] = "dat = ".$dat;
			//			$debug[] = "fine getDat e inizio Trasformazione del layout ".$stampaObj->getLayout_Id();

			writeLog("reporter/stampa","Inizio trasformazione del layout ".$stampaObj->getLayout_Id(),$app);

			$datXml = $stampaObj->transform($app, $db, $stampaObj->getLayout_Id(), $dat);
//			if ($app->config('LOGS') == "S") {
				file_put_contents(getFolderLog().$date_for_save.'_dat_'.$report_code.'_'.$device_id.'_'.$printerType.'-'.uniqid().'.xml',$datXml);
//			}
			//			$debug[] = "fine Trasformazione";

			writeLog("reporter/stampa","Fine trasformazione del layout ",$app);

			//			$debug[] = "Tipo stampante = ".$printerType;

			writeLog("reporter/stampa","Tipo stampante = ".$printerType,$app);
			
			$delimiterValue = "#";
			if ($printerType == 'Intelligent') {
				$delimiterValue = "#";
			}
			if ($printerType == 'ESCPos') {
				$delimiterValue = "#";
			}
			
			if ($datXml != "") {
				//				$debug[] = "datXml valorizzato";
				//				$debug[] = "delimitatore = ".$delimiterValue;

				writeLog("reporter/stampa","Before applyTemplate ",$app);
				
				$raw = $stampaObj->applyTemplate($datXml, $commands, $fonts, $delimiterValue);
//				if ($app->config('LOGS') == "S") {
					file_put_contents(getFolderLog().$date_for_save.'_raw_'.$report_code.'_'.$device_id.'_'.$printerType.'-'.uniqid().'.txt',$raw);
//				}

				//				$debug[] = "raw = ";

				if ($printerType == 'Intelligent') {
					$devid=$queue_identifier;
					//$timeout="6000";
					$timeout="60000";

					$ritorno = $stampaObj->printIntelligent($raw, $host, $devid, $timeout, $app->config('INTELLIGENT_TYPE'));
					try {
						$inizio = strpos($ritorno,'<');
						$fine = strrpos($ritorno,'>');
						$ritorno = substr($ritorno,$inizio, $fine - $inizio + 1);
						writeLog("reporter/stampa","Esito : ".$ritorno,$app);

						$xmlDoc = new \SimpleXMLElement($ritorno);
						//var_dump($xmlDoc->xpath("/s:Envelope/s:Body"));
						$ns = $xmlDoc->getDocnamespaces();
//var_dump($ns);
//echo $ns[1];
						$esitoIntelligent = "";
						$esitoCodeIntelligent = "";
						//foreach($xmlDoc->xpath("/s:Envelope/s:Body") as $pippo) {
					foreach($ns as $p=>$v) {
						foreach($xmlDoc->xpath("/".$p.":Envelope/".$p.":Body") as $pippo) {
							//var_dump($pippo->children());	
							writelog("reporter/stampa","esito = ".$pippo->children()->attributes()['success'],$app);
							$esitoIntelligent = $pippo->children()->attributes()['success'];
							$esitoCodeIntelligent = $pippo->children()->attributes()['code'];
						}
					}
						if ($esitoIntelligent == "true") {
							$status = "1";
						} else {
							$status = "-1";
							writelog("reporter/stampa","ERRORE = ".$esitoCodeIntelligent,$app);
						}
					} catch(Exception $e) {
						$status = "-1";
						writeLog("reporter/stampa","ERROR : ".$e->getCode().' - '.$e->getMessage(),$app);	
					}

				}
				if ($printerType == 'ESCPos') {
					writeLog("reporter/stampa","ESCPos Host=".$host." port=".$port,$app);
					$ritorno = $stampaObj->printEscPos($raw, $host, $port, $app);
					if (!$ritorno) {
						writeLog("reporter/stampa","ESCPos ritornato false",$app);
						$status = "-1";
					}
				}
			}
			writeLog("reporter/stampa","Fine stampa",$app);	
		} catch(Exception $e) {
			$status = "-1";
			writeLog("reporter/stampa","ERROR : ".$e->getCode().' - '.$e->getMessage(),$app);	
		}
	} finally {
		$stampaObj = null;
        writeLog("reporter/stampa","Finally",$app);
		//		return $debug;
		return $status;
	}
}
 
$app->get('/printreport/:pos_id/:device_id/:report_code(/:conditions+)', function ($pos_id, $device_id, $report_code, $conditions = []) use ($app, $db, $pdo) {
	
	
	$pos_templates = $db->SST_POS_TEMPLATES;
	$pos_templates->where('POS_ID',$pos_id);
	$pos_templates->and('SALE_TYPE_ID','12');  // preconto
	$pos_templates->and('IS_DEFAULT','1');
	foreach ($pos_templates as $dett_templates) {
                writeLog("printreport","template:".$dett_templates['ID'],$app);	
		$prod_printer = $db->SST_POS_DEVICES;
		$prod_printer->select('POS_ID,DEVICE_ID,DEVICE_ADDRESS,MIN(PRODUCTION_CENTER_ID) AS PRODUCTION_CENTER_ID');
		$prod_printer->where('POS_ID',$pos_id);
		$prod_printer->and('DEVICE_ID',$dett_templates['DEVICE_ID']);
		$prod_printer->group('POS_ID,DEVICE_ID,DEVICE_ADDRESS');
                writeLog("printreport","count printer ".count($prod_printer),$app);	
		
		foreach ($prod_printer as $dett_printer) {
                        writeLog("printreport","device_id:".$dett_printer['DEVICE_ID'],$app);	
			stampa($pos_id, $dett_printer, $report_code, $conditions, '', $app, $db, $pdo);
		}
	}
	
});

$app->get('/printorder/:lunch_table_id(/course_id/:course_id)(/reprint/:reprint)(/:conditions+)', function ($lunch_table_id, $course_id = 0, $reprint = 0, $conditions = []) use ($app, $db, $pdo) {

    $app->response()->header("Content-Type", "application/json");
	
	$pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
	foreach ($pos_system_settings as $setting) {
		$pos_id = $setting['POS_ID'];
	}	
	
	$esito = "";
	$status = "1";
	$printStatus = "1";
	$printAllStatus = "1";
	$prod_in_error = "";
	
    $operator_id = array();
    
	$prodCenterOK = array();
	
//	$debug = array();
	if ($course_id == 0) {
		$course_id = '999999';
	} 
        writeLog("printorder","Inizio stampa con course_id= ".$course_id,$app);
	// ho il tavolo, recupero lo scontrino
	$comandaHead = $db->SST_SALE_HEADINGS;
	$comandaHead->where('LUNCH_TABLE_ID',$lunch_table_id);
	$comandaHead->and('ORDINATION_CLOSED',0);
    foreach($comandaHead as $singleComandaHead) {
        $operator_id[] = $singleComandaHead['OPERATOR_ID'];
    }
	if (count($comandaHead) != 1) {
		$esito = "ERRORE : Il tavolo con id ".$lunch_table_id." non risulta aperto. (count=".count($comandaHead).")";
        writeLog("printorder",$esito,$app);
		$printAllStatus = "-1";
		if (count($comandaHead) == 0) {
			$prod_in_error = "Errore in stampa: Non ci sono comande sul tavolo";
		} else {
			$prod_in_error = "Errore in stampa: sul tavolo sono presenti ".count($comandaHead)." comande aperte";
		}
	} else {
        writeLog("printorder","1 comanda trovata",$app);
		$comanda = $comandaHead->fetch();
        writeLog("printorder","ID = ".$comanda['ID'],$app);
		
		try {
			$course_prod = $db->SSV_SALE_DETAILS_COURSE_PROD;
			$course_prod->select('COURSE_ID');
			$course_prod->select('PRODUCTION_CENTER_ID');
			$course_prod->select('QUEUE_IDENTIFIER');
			$course_prod->where('HEADING_ID',$comanda['ID']); // comanda
			if ($reprint == 0) {
				$course_prod->and('COURSE_PRINTED',0); // 0 = da stampare, 1 = stampate
			}
			$course_prod->and('COURSE_ID',$course_id); // portata
			$course_prod->and('PRODUCTION_CENTER_ID IS NOT NULL');
//			$course_prod->group('COURSE_ID, PRODUCTION_CENTER_ID');
			$course_prod->group('COURSE_ID, PRODUCTION_CENTER_ID, QUEUE_IDENTIFIER');
			
            writeLog("printorder","- - Inizio RECORD LETTO di ".count($course_prod),$app);
			foreach ($course_prod as $dettaglio) {
			
                $printStatus = "1";
				/*
				foreach ($dettaglio as $field => $value) {
					$debug[] = $field."=".$value; //." ovvero production center = ".$comandaDett->SST_ARTICLES['DESCRIPTION'];
				}	
				*/
                                writeLog("printorder","Stampa la portata con ID ".$dettaglio['COURSE_ID']." sul centro di produzione con ID ".$dettaglio['PRODUCTION_CENTER_ID'],$app);
				$prod_center_print = $db->SST_PRODUCTION_CENTERS_REPORTS;
				$prod_center_print->where('PRODUCTION_CENTER_ID',$dettaglio['PRODUCTION_CENTER_ID']);
				$prod_center_print->order('ORDER_NUM');
				foreach ($prod_center_print as $dett_print) {
                    writeLog("printorder","     usando il report con id ".$dett_print['REPORT_ID'],$app);
                            
                    $reports = $db->SST_REPORTS;
                    $reports->where('ID',$dett_print['REPORT_ID']);
                    foreach ($reports as $singleReport) {
                                    
                        $prod_printer = $db->SST_POS_DEVICES;
                        $prod_printer->where('POS_ID',$pos_id);
                        $prod_printer->and('PRODUCTION_CENTER_ID',$dettaglio['PRODUCTION_CENTER_ID']);
                        $prod_printer->and('ENABLED',1);
                        
                        foreach ($prod_printer as $dett_printer) {
                            writeLog("printorder","     sulla stampante con id  ".$dett_printer['DEVICE_ID'],$app);

                            $newConditions = array();
                            //if (in_array(array('801','802','803','804'), array($singleReport['CODE']) )) {
                            $article_exist = false;
                            if (!in_array($singleReport['CODE'],array('801','802','803','804') )) {
                                $newConditions = $conditions;
                                //$debug[] = stampa($pos_id, $dett_printer['DEVICE_ID'], $singleReport['CODE'], $newConditions, $app, $db, $pdo);
                                $status = stampa($pos_id, $dett_printer, $singleReport['CODE'], $newConditions, $dettaglio['QUEUE_IDENTIFIER'], $app, $db, $pdo);
                                $debug[] = $status;
                                if ($status == "-1") {
                                    writeLog("printorder","ERROR: errore nella procedura di stampa",$app);
                                    $printStatus = "-1";
                                }
                            } else {
                                $newConditions[0] = 'heading_id';
                                $newConditions[1] = $comanda['ID'];
                                $newConditions[2] = 'production_center_id';
                                $newConditions[3] = $dettaglio['PRODUCTION_CENTER_ID'];
                                $newConditions[4] = 'course_id';
                                $newConditions[5] = $course_id;

                                writeLog("printorder","Codice Report ".$singleReport['CODE'],$app);
                                if (in_array($singleReport['CODE'], array('801','803'))) {
                                    foreach ($conditions as $cond) {
                                        if ($cond == 'sale_article_id') {
                                                $article_exist = true;		
                                        }
                                        if (($cond != 'sale_article_id') and ($article_exist)) {
                                                $newConditions[6] = 'sale_article_id';
                                                $newConditions[7] = $cond;
                                                //$article_exist = false;
                                                break;
                                        }
                                    }

                                    writeLog("printorder","Condizioni ".implode(",",$newConditions),$app);
                                    $articoli = $db->SST_SALE_DETAILS;
                                    $articoli->where('HEADING_ID',$comanda['ID']);
                                    $articoli->and('PRODUCTION_CENTER_ID',$dettaglio['PRODUCTION_CENTER_ID']);
                                    $articoli->and('COALESCE(COURSE_ID,999999)',$course_id);
                                    if ($article_exist) {
                                        $articoli->and('SALE_ARTICLE_ID',$newConditions[7]);
                                    }
                                    foreach ($articoli as $articolo) {
                                        $newConditions[6] = 'sale_article_id';
                                        $newConditions[7] = $articolo['SALE_ARTICLE_ID'];

                                        //$debug[] = stampa($pos_id, $dett_printer['DEVICE_ID'], $singleReport['CODE'], $newConditions, $app, $db, $pdo);
                                        $status = stampa($pos_id, $dett_printer, $singleReport['CODE'], $newConditions, $dettaglio['QUEUE_IDENTIFIER'], $app, $db, $pdo);
                                        $debug[] = $status;
                                        if ($status == "-1") {
                                            writeLog("printorder","ERROR: Errore nella procedura di stampa",$app);
                                            $printStatus = "-1";
                                        }
                                    }
                                } else {
                                    writeLog("printorder","else with ".implode(",",$newConditions),$app);

                                    //$debug[] = stampa($pos_id, $dett_printer['DEVICE_ID'], $singleReport['CODE'], $newConditions, $app, $db, $pdo);
                                    $status = stampa($pos_id, $dett_printer, $singleReport['CODE'], $newConditions, $dettaglio['QUEUE_IDENTIFIER'], $app, $db, $pdo);
                                    $debug[] = $status;
                                    if ($status == "-1") {
                                        writeLog("printorder","ERROR: Errore nella procedura di stampa",$app);
                                        $printStatus = "-1";
                                    }
                                }
                            }
                        }
                    }
				}
				
                if ($printStatus == "1") {
                    writeLog("printorder","printStatus = 1: Mi salvo il centro di produzione ".$dettaglio['PRODUCTION_CENTER_ID']." per dopo per la portata ".$course_id,$app);
                    $prodCenterOK[] = $dettaglio['PRODUCTION_CENTER_ID'];
                    /*
                    writeLog("printorder","printStatus = 1: Segno come stampato il centro di produzione ".$dettaglio['PRODUCTION_CENTER_ID']." per la portata ".$course_id,$app);
                    $artPrinted = $db->SST_SALE_DETAILS;
                    $artPrinted->where('HEADING_ID',$comanda['ID']);
                    $artPrinted->and('PRODUCTION_CENTER_ID',$dettaglio['PRODUCTION_CENTER_ID']);
                    $artPrinted->and('COALESCE(COURSE_ID,999999)',$course_id);
                    $artPrinted->update(["COURSE_PRINTED"=>1,"LAST_UPDATE_DATE"=>getCurrentTimeStamp($db)]);
                    */
                } else {
                    writeLog("printorder","printStatus NON è 1: NON Segno come stampato il centro di produzione ".$dettaglio['PRODUCTION_CENTER_ID']." per la portata ".$course_id,$app);
                    
                    $prod_center_err = $db->SST_PRODUCTION_CENTERS;
                    $prod_center_err->where('ID',$dettaglio['PRODUCTION_CENTER_ID']);
                    $prod_center_err->fetch();
                    foreach ($prod_center_err as $prod_center_row) {
                        $prod_in_error = $prod_in_error.$prod_center_row['DESCRIPTION']." ";
                    }
                    
                    $printAllStatus = "-1";
                }
// qui				
			}
			
			if (count($prodCenterOK) > 0) {
				foreach($prodCenterOK as $singleProdCenterOK) {
					writeLog("printorder","printStatus = 1: Segno come stampato il centro di produzione ".$singleProdCenterOK." per la portata ".$course_id,$app);
					$artPrinted = $db->SST_SALE_DETAILS;
					$artPrinted->where('HEADING_ID',$comanda['ID']);
					$artPrinted->and('PRODUCTION_CENTER_ID',$singleProdCenterOK);
					$artPrinted->and('COALESCE(COURSE_ID,999999)',$course_id);
					$artPrinted->update(["COURSE_PRINTED"=>1,"LAST_UPDATE_DATE"=>getCurrentTimeStamp($db)]);				
				}
			}
			
			if ($printAllStatus == "1") {
                writeLog("printorder","printAllStatus = 1: Segno tutto come stampato",$app);
				$ora = new \DateTime();
				$artPrinted = $db->SST_SALE_DETAILS;
				$artPrinted->where('HEADING_ID',$comanda['ID']);
				//$artPrinted->where('PRODUCTION_CENTER_ID',$dettaglio['PRODUCTION_CENTER_ID']);
				$artPrinted->and('COALESCE(COURSE_ID,999999)',$course_id);
				//$artPrinted->update(["COURSE_PRINTED"=>1,"LAST_UPDATE_DATE"=>$ora->format('Y-m-d H:i:s')]);
				$artPrinted->update(["COURSE_PRINTED"=>1,"LAST_UPDATE_DATE"=>getCurrentTimeStamp($db)]);
                $prod_in_error = "";
			} else {
                writeLog("printorder","printAllStatus NON è 1: NON segno niente",$app);				
                //$prod_in_error = "Si sono riscontrati problemi nei seguenti centri di produzione: ".$prod_in_error;
		$lunch_tables = $db->SST_LUNCH_TABLES;
		$lunch_tables->where('ID',$lunch_table_id);
		$tavolo = "";
		foreach($lunch_tables as $single_lunch_table) {
			$tavolo = $single_lunch_table['DESCRIPTION'];
		}
                $prod_in_error = "Errore sul tavolo ".$tavolo." nei seguenti centri di produzione: ".$prod_in_error;
                writeLog("printorder","printAllStatus NON è 1: ".$prod_in_error,$app);				
			}
			$debug[] = "- - Fine RECORD LETTO";			
		} catch(Exception $e) {
            writeLog("printorder","ERROR : ".$e->getCode().' - '.$e->getMessage(),$app);
		}
	}
	
    if ($printAllStatus == "1") {
        //echo json_encode(array("status" => true, "message" => "","Debug" => $debug));
        echo json_encode(array("status" => true, "message" => ""));
	} else {
        //echo json_encode(array("status" => false, "message" => $prod_in_error,"Debug" => $debug));
        writeLog("printorder",$prod_in_error,$app);
        $reportError = $db->SST_WS_ERRORS;
        $reportErrorField = array();
        foreach($operator_id as $singleOperator) {
            $reportErrorField['ID'] = getNewId($db, 'SST_WS_ERRORS');
            $reportErrorField['OPERATOR_ID'] = $singleOperator;
            $reportErrorField['DESCRIPTION'] = $prod_in_error;
            $reportErrorField['TYPE'] = 1; // report
            $reportErrorField['SHOW'] = 0;
            $reportError->insert($reportErrorField);
        }
        echo json_encode(array("status" => false, "message" => $prod_in_error));
    }

	
});

 
$app->get('/printpreconto/lunch_table_id/:lunch_table_id', function ($lunch_table_id) use ($app, $db, $pdo) {
	
    $app->response()->header("Content-Type", "application/json");
	
	$conditions = [];
	$report_code = '805';
	$pos_id = '';
	$pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
	foreach ($pos_system_settings as $setting) {
		$pos_id = $setting['POS_ID'];
	}	

	$order = $db->SSV_ORDER_HEADINGS;
	$order->where('LUNCH_TABLE_ID',$lunch_table_id);
	$order->and('ORDINATION_CLOSED',0);
	
	writeLog("printpreconto","LUNCH_TABLE_ID=".$lunch_table_id,$app);
	writeLog("printpreconto","count=".count($order),$app);
    
	$document_id = -1;
	foreach($order as $rowOrder) {
		$document_id = $rowOrder['ID'];
	}
	$conditions[]='document_id';
	$conditions[]=$document_id;

	writeLog("printpreconto","document_id=".$document_id,$app);
	
	$pos_templates = $db->SST_POS_TEMPLATES;
	$pos_templates->where('POS_ID',$pos_id);
	$pos_templates->and('SALE_TYPE_ID','12');  // preconto
	$pos_templates->and('IS_DEFAULT','1');
	foreach ($pos_templates as $dett_templates) {

		writeLog("printpreconto","template:".$dett_templates['ID'],$app);	
		
		$prod_printer = $db->SST_POS_DEVICES;
		$prod_printer->select('POS_ID,DEVICE_ID,MIN(DEVICE_ADDRESS) AS DEVICE_ADDRESS,MIN(PRODUCTION_CENTER_ID) AS PRODUCTION_CENTER_ID');
		$prod_printer->where('POS_ID',$pos_id);
		$prod_printer->and('DEVICE_ID',$dett_templates['DEVICE_ID']);
		$prod_printer->group('POS_ID,DEVICE_ID');

		writeLog("printpreconto","count printer ".count($prod_printer),$app);	
		
		foreach ($prod_printer as $dett_printer) {
			
			$prod_center_prod_id = $db->SST_POS_DEVICES;
			$prod_center_prod_id->where('POS_ID',$dett_printer['POS_ID']);
			$prod_center_prod_id->and('DEVICE_ID',$dett_printer['DEVICE_ID']);
			$prod_center_prod_id->and('DEVICE_ADDRESS',$dett_printer['DEVICE_ADDRESS']);
			foreach($prod_center_prod_id as $single_prod_center_prod_id){
				
				writeLog("printpreconto","device_id:".$dett_printer['DEVICE_ID'],$app);	
				$prod_center_print = $db->SST_PRODUCTION_CENTERS;
				//$prod_center_print->where('ID',$dett_printer['PRODUCTION_CENTER_ID']);
				$prod_center_print->where('ID',$single_prod_center_prod_id['PRODUCTION_CENTER_ID']);
				writeLog("printpreconto","trovati prdouction center :".count($prod_center_print),$app);	
				
				foreach ($prod_center_print as $prod_center) {
					writeLog("printpreconto","queue:".$prod_center['QUEUE_IDENTIFIER'],$app);	
					//$status = stampa($pos_id, $dett_printer, $report_code, $conditions, $prod_center['QUEUE_IDENTIFIER'], $app, $db, $pdo);
					$status = stampa($pos_id, $single_prod_center_prod_id, $report_code, $conditions, $prod_center['QUEUE_IDENTIFIER'], $app, $db, $pdo);
				}
			}
		}
	}
	
});
