<?php

//$app->post('/saveorder(:/mobile_pos_id)', function ($mobile_pos_id = 0) use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
$app->post('/saveorder', function () use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
	$app->response()->header("Content-Type", "application/json");
            writeLog("saveorder","1 Start save order",$app);

	$tuttoOk = True;
	
	$esito = "";
    $messageResult = "";

	$__tablename_head = "SST_SALE_HEADINGS";
	try {
		$__campi_head = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_head) {
				//$__fields = $tabelle[$__tablename];
				
				foreach ($tabelle[$__tablename_head] as $synonym => $name) {
					$__campi_head[$synonym] = $name['name'];
				}
				
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS HEAD - ";
	}

	$__tablename_dett = "SST_SALE_DETAILS";
	try {
		$__campi_dett = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_dett) {
				foreach ($tabelle[$__tablename_dett] as $synonym => $name) {
					$__campi_dett[$synonym] = $name['name'];
				}
				
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS DETT - ";
	}
	

	$__tablename_notes = "SST_SALE_DETAILS_NOTES";
	try {
		$__campi_notes = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_notes) {
				foreach ($tabelle[$__tablename_notes] as $synonym => $name) {
					$__campi_notes[$synonym] = $name['name'];
				}
				
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS NOTES - ";
	}
	
	$valuePassed = $app->request()->post();
	$xml = "";
	$status = "";
	$curEntity = array();
	$pos_id = "";
//$db->transaction = 'BEGIN';
	$pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
	foreach ($pos_system_settings as $setting) {
		$pos_id = $setting['POS_ID'];
	}

    // scritto anche in maiuscolo in quanto non va bene così
    //    con questo gestisco solo l'ultima comanda inviata
    //    è vero che normalmente viene gestita una comanda alla volta
    //    ma se la procedura prevede la possibilità di gestirne di più
    //    la si deve implementare fino alla fine TODO
    $LAST_LUNCH_TABLE_ID = 0; 

        // valorizzare variabile con heading_id !!!!!
        $current_heading_id = 0;

	try {
	   $mobile_pos_id = $valuePassed['MOBILE_POS_ID'];
	} catch(Exception $e) {
	   $mobile_pos_id = "0";
	}
	try {
		$xml = $valuePassed['DATI'];
		if ($xml != "") {

            writeLog("saveorder","Start save order",$app);
            if ($mobile_pos_id <> "0") {
                writeLog("saveorder","  richiamato dal mobile_pos_id ".$mobile_pos_id,$app);
            }
//$db->transaction = 'BEGIN';

			$xmlDoc = new DOMDocument();		
			$xmlDoc->preserveWitheSpace = false;
			$xmlDoc->LoadXML($xml);
			$xpathDoc = new DOMXpath($xmlDoc);
			
			$ora = new \DateTime();
			$dateTimeDML = $ora->format('Y-m-d H:i:s');
			
			$date_for_save = $ora->format('Y-m-d_H-i-s');
            
            writeLog("saveorder/heading","Salvo comanda in ".getFolderLog().$date_for_save.'_order_headings-'.uniqid().'.xml',$app);
            
			$xmlDoc->save(getFolderLog().$date_for_save.'_order_headings-'.uniqid().'.xml');
			
			$volte=0;
			$recordTestata = $xpathDoc->query("/sst_sale_headings/items/item");
			foreach ($recordTestata as $record) {
				$volte++;
				$rigaTestataDB = array();
				foreach ($record->childNodes as $fields) {
					if (($fields->nodeName != 'sst_sale_details') and ($fields->nodeName != 'sst_sale_details_notes') ){
						if (!($fields instanceof \DOMText) ) {
							try {
								if ($__campi_head[$fields->nodeName] != 'ID') {
									if ($__campi_head[$fields->nodeName] == 'POS_ID') {
										// $esito=$esito."=>".$pos_id."<=";
										$rigaTestataDB[$__campi_head[$fields->nodeName]]=$pos_id;
									} else {
										if ($fields->nodeValue <> '') {
											$rigaTestataDB[$__campi_head[$fields->nodeName]]=$fields->nodeValue;
										}
									}
								}
							} catch(Exception $e) {
								
							}
						}
					}
				}

				try {
                    
                    $LAST_LUNCH_TABLE_ID = $rigaTestataDB['LUNCH_TABLE_ID'];
                    
					$tabellaTestata = $db->$__tablename_head;
					$tabellaTestata->where('TRANSACTION_NUM', $rigaTestataDB['TRANSACTION_NUM']);
					$tabellaTestata->and('TRANSACTION_DATE', $rigaTestataDB['TRANSACTION_DATE']);
					$tabellaTestata->and('LUNCH_TABLE_ID', $rigaTestataDB['LUNCH_TABLE_ID']);
					$rigaTestataDB['SUSPENDED'] = 1;
					//$rigaTestataDB['LAST_UPDATE_DATE']=$dateTimeDML;

					if (count($tabellaTestata) == 0 ) {
                        // la comanda non esiste
                        //    verifico che non ce ne sia già una di aperta
                        $conta_aperte = $db->SSV_ORDER_HEADINGS;
                        $conta_aperte->where('LUNCH_TABLE_ID', $rigaTestataDB['LUNCH_TABLE_ID']);
                        $conta_aperte->and('ORDINATION_CLOSED', 0);
                        if (count($conta_aperte) > 0) {
                            // sono già presenti comande aperte !!!!
                            $tuttoOk = false;
                            $esito="Presenti ".count($conta_aperte)." comande aperte sul tavolo ".$rigaTestataDB['LUNCH_TABLE_ID'];
                            writeLog("saveorder/heading","Presenti ".count($conta_aperte)." comande aperte sul tavolo ".$rigaTestataDB['LUNCH_TABLE_ID'],$app);
                            $messageResult = "Tavolo con ".count($conta_aperte)." comande già presente";
                        } else {
                            writeLog("saveorder/heading","INSERT order with Transaction_num=".$rigaTestataDB['TRANSACTION_NUM']." Transaction_date=".$rigaTestataDB['TRANSACTION_DATE']." Lunch_table_id=".$rigaTestataDB['LUNCH_TABLE_ID'],$app);
                            
                            // $esito = $esito."fare_insert testata";
                            $newId = getNewId($db, $__tablename_head);
                                                    $current_heading_id = $newId;
                            $rigaTestataDB['ID'] = $newId;
                                                    $dateTimeDML = $ora->format('Y-m-d H:i:s');
                            //$rigaTestataDB['LAST_UPDATE_DATE']=$dateTimeDML;
                            $rigaTestataDB['LAST_UPDATE_DATE']=getCurrentTimeStamp($db);
// IMPOSTA A NULL QUANDO GESTISCO LA TRANSAZIONE
//						$rigaTestataDB['LAST_UPDATE_DATE']='';
                                                    writeLog("saveorder/heading","     ID=".$newId,$app);						
                            $result = $tabellaTestata->insert($rigaTestataDB);
                            if ($result) {
                                $esito=$esito."TRUE INSERT HEAD";
                            } else {
                                $tuttoOk = false;
                                $esito=$esito."FALSE INSERT HEAD";
                            }
                            writeLog("saveorder/heading","INSERT Esito=".$esito,$app);
                        }
					} else {
                        // la comanda esiste
                        //    quindi non deve essere già chiusa
			$isClosed = false;
			foreach($tabellaTestata as $testIfClosed) {
                        	if ($testIfClosed['ORDINATION_CLOSED'] == 1) {
					$isClosed = true;
				}
			}
                        //if ($tabellaTestata['ORDINATION_CLOSED'] == 1) {
                        if ($isClosed) {
                            // sono già presenti comande aperte !!!!
                            $tuttoOk = false;
                            $esito="ERRORE Non si puo riaprire una comanda chiusa";
                            writeLog("saveorder/heading","ERRORE Non si puo riaprire una comanda chiusa",$app);
                            $messageResult = "Non si puo riaprire una comanda chiusa";                            
                        } else {                        
                        
                            writeLog("saveorder/heading","UPDATE order with Transaction_num=".$rigaTestataDB['TRANSACTION_NUM']." Transaction_date=".$rigaTestataDB['TRANSACTION_DATE']." Lunch_table_id=".$rigaTestataDB['LUNCH_TABLE_ID']." -> id=".$tabellaTestata['ID'],$app);
                            // $esito = $esito."fare_update testata";
                            $dateTimeDML = $ora->format('Y-m-d H:i:s');

                            //$rigaTestataDB['LAST_UPDATE_DATE']=$dateTimeDML;
                            $rigaTestataDB['LAST_UPDATE_DATE']=getCurrentTimeStamp($db);
// IMPOSTA A NULL QUANDO GESTISCO LA TRANSAZIONE
//						$rigaTestataDB['LAST_UPDATE_DATE']='';
                            $result = $tabellaTestata->update($rigaTestataDB);
                            if ($result) {
                                $esito=$esito."TRUE UPDATE HEAD";
                            } else {
                                $tuttoOk = false;
                                $esito=$esito."FALSE UPDATE HEAD";
                            }
                            writeLog("saveorder/heading","UPDATE Esito=".$esito,$app);
                        }
					}
					$status = $status."Situazione sconosciuta in testata";
				} catch(Exception $e) {
					if ($e->getCode() != 2) {
						$tuttoOk = false;
						$status = $status." head ".$volte." ".$e->getCode().' - '.$e->getMessage();
					} else {
						$status = $status." head ".$volte." Insert ok !";
					}
				}

                if ($tuttoOk) {
                    // Details
                    
                    $righe=0;
                    $xpathDettaglioDoc = new DOMXpath($record->ownerDocument);
                    $recordDettaglio = $xpathDettaglioDoc->query('/sst_sale_headings/items/item['.$volte.']/sst_sale_details/items/item');
                    foreach ($recordDettaglio as $rigaDettaglio) {
                        $righe++;
                        $esito=$esito."riga -> ".$righe." - ";

                        $rigaDettaglioDB = array();
                        
                        foreach ($rigaDettaglio->childNodes as $campiDettaglio) {
                            if ($campiDettaglio->nodeName != 'sst_sale_details') {
                                if (!($campiDettaglio instanceof \DOMText) ) {
                                    try {
                                        //$esito=$esito.$campiDettaglio->nodeName." = ".$campiDettaglio->nodeValue." * ";
                                        if ($__campi_dett[$campiDettaglio->nodeName] != 'ID') {
                                            if ($campiDettaglio->nodeValue <> '') {
                                                $rigaDettaglioDB[$__campi_dett[$campiDettaglio->nodeName]]=$campiDettaglio->nodeValue;
                                            }
                                        }
                                    } catch(Exception $e) {
                                        
                                    }
                                }
                            }
                        }
                        //$rigaDettaglioDB['LAST_UPDATE_DATE']=$rigaTestataDB['LAST_UPDATE_DATE'];
                        $dateTimeDML = $ora->format('Y-m-d H:i:s');
                        //$rigaDettaglioDB['LAST_UPDATE_DATE']=$dateTimeDML;
                        $rigaDettaglioDB['LAST_UPDATE_DATE']=getCurrentTimeStamp($db);
// IMPOSTA A NULL QUANDO GESTISCO LA TRANSAZIONE
//					$rigaDettaglioDB['LAST_UPDATE_DATE']='';
                        try {
                            $tabellaDettaglio = $db->$__tablename_dett;
                            $tabellaDettaglio->where('TRANSACTION_NUM', $rigaDettaglioDB['TRANSACTION_NUM']);
                            $tabellaDettaglio->and('TRANSACTION_DATE', $rigaDettaglioDB['TRANSACTION_DATE']);
                            $tabellaDettaglio->and('LUNCH_TABLE_ID', $rigaDettaglioDB['LUNCH_TABLE_ID']);
                            $tabellaDettaglio->and('ROW_ORDER', $rigaDettaglioDB['ROW_ORDER']);
                            if (count($tabellaDettaglio) == 0 ) {
                                // $esito = $esito."fare_insert dettaglio";
                                $newId = getNewId($db, $__tablename_dett);
                                                            writeLog("saveorder/details","Insert Transaction_num ".$rigaDettaglioDB['TRANSACTION_DATE']." Transaction_date ".$rigaDettaglioDB['TRANSACTION_DATE']." Lunch_table_id ".$rigaDettaglioDB['LUNCH_TABLE_ID']." Row_order ".$rigaDettaglioDB['ROW_ORDER']." with id ".$newId,$app);								
                                $rigaDettaglioDB['ID'] = $newId;
                                $tabellaTestata = $db->$__tablename_head;
                                $tabellaTestata->where('TRANSACTION_NUM', $rigaTestataDB['TRANSACTION_NUM']);
                                $tabellaTestata->and('TRANSACTION_DATE', $rigaTestataDB['TRANSACTION_DATE']);
                                $tabellaTestata->and('LUNCH_TABLE_ID', $rigaTestataDB['LUNCH_TABLE_ID']);
                                if (count($tabellaTestata) == 1 ) {
                                                                    writeLog("saveorder/details","Trovata testata con id".$tabellaTestata['ID'],$app);
                                    $rigaDettaglioDB['HEADING_ID'] = $tabellaTestata['ID'];
                                }
                                try {
                                    if ($rigaDettaglioDB['DETAIL_ORDER'] == '') {
                                        $rigaDettaglioDB['DETAIL_ORDER'] = $tabellaDettaglio['ROW_ORDER'];
                                    }	
                                } catch(Exception $e) {
                                    $rigaDettaglioDB['DETAIL_ORDER'] = $tabellaDettaglio['ROW_ORDER'];
                                }
                                
                                $result = $tabellaDettaglio->insert($rigaDettaglioDB);
                                if ($result) {
                                    $esito=$esito."TRUE INSERT DETT";
                                                                    writeLog("saveorder/details","INSERT Esito=".$esito,$app);
                                } else {
                                    $tuttoOk = false;
                                    $esito=$esito."FALSE INSERT DETT";
                                                                    writeLog("saveorder/details","INSERT Esito=".$esito,$app);
                                }
                            } else {
                                                            writeLog("saveorder/details","Update Transaction_num ".$rigaDettaglioDB['TRANSACTION_DATE']." Transaction_date ".$rigaDettaglioDB['TRANSACTION_DATE']." Lunch_table_id ".$rigaDettaglioDB['LUNCH_TABLE_ID']." Row_order ".$rigaDettaglioDB['ROW_ORDER'],$app);
                                // $esito = $esito."fare_update dettaglio";
                                $result = $tabellaDettaglio->update($rigaDettaglioDB);
                                if ($result) {
                                    $esito=$esito."TRUE UPDATE DETT";
                                } else {
                                    $tuttoOk = false;
                                    $esito=$esito."FALSE UPDATE DETT";
                                }
                                                            writeLog("saveorder/details","UPDATE Esito=".$esito,$app);
                            }
                            $status = $status."Situazione sconosciuta in dettaglio";
                        } catch(Exception $e) {
                            // $newId = $newId;
                            // $status = $status."AAA";
                            if ($e->getCode() != 2) {
                                $tuttoOk = false;
                                $status = $status." dett ".$e->getCode().' - '.$e->getMessage();
                            } else {
                                $status = $status." dett "."Insert ok !";
                            }
                        }
                    }
				}
                
                if ($tuttoOk) {
                    // Notes
                    $righe=0;
                    $xpathNotesDoc = new DOMXpath($record->ownerDocument);
                    $recordNote = $xpathNotesDoc->query('/sst_sale_headings/items/item['.$volte.']/sst_sale_details_notes/items/item');
                    foreach ($recordNote as $rigaNote) {
                        $righe++;
                        $esito=$esito."riga -> ".$righe." - ";

                        $rigaNoteDB = array();
                                            writeLog("saveorder/notes","Nota numero ".$righe,$app);
                        
                        foreach ($rigaNote->childNodes as $campiNote) {
                            if ($campiNote->nodeName != 'sst_sale_details_notes') {
                                if (!($campiNote instanceof \DOMText) ) {
                                    try {
                                        //$esito=$esito.$campiNote->nodeName." = ".$campiNote->nodeValue." * ";
                                        if ($__campi_notes[$campiNote->nodeName] != 'ID') {
                                            if ($campiNote->nodeValue <> '') {
                                                $rigaNoteDB[$__campi_notes[$campiNote->nodeName]]=$campiNote->nodeValue;
                                            }
                                        }
                                    } catch(Exception $e) {
                                        writeLog("saveorder/notes","Errore valorizzando rigaNoteDB ".$e->getCode().' - '.$e->getMessage(),$app);
                                    }
                                }
                            }
                        }
                        //	$rigaNoteDB['LAST_UPDATE_DATE']=$rigaTestataDB['LAST_UPDATE_DATE'];
                        $dateTimeDML = $ora->format('Y-m-d H:i:s');
                        //$rigaNoteDB['LAST_UPDATE_DATE']=$dateTimeDML;
                        $rigaNoteDB['LAST_UPDATE_DATE']=getCurrentTimeStamp($db);
// IMPOSTA A NULL QUANDO GESTISCO LA TRANSAZIONE
//					$rigaNoteDB['LAST_UPDATE_DATE']='';
                        try {
                            $tabellaNote = $db->$__tablename_notes;
                            $tabellaNote->where('TRANSACTION_NUM', $rigaNoteDB['TRANSACTION_NUM']);
                            $tabellaNote->and('TRANSACTION_DATE', $rigaNoteDB['TRANSACTION_DATE']);
                            $tabellaNote->and('LUNCH_TABLE_ID', $rigaNoteDB['LUNCH_TABLE_ID']);
                            $tabellaNote->and('DETAIL_ORDER', $rigaNoteDB['DETAIL_ORDER']);
                            if (count($tabellaNote) == 0 ) {
                                // $esito = $esito."fare_insert note";
                                writeLog("saveorder/notes","INSERT notes with Transaction_num=".$rigaNoteDB['TRANSACTION_NUM']." Transaction_date=".$rigaNoteDB['TRANSACTION_DATE']." Lunch_table_id=".$rigaNoteDB['LUNCH_TABLE_ID']." Detail_order=".$rigaNoteDB['DETAIL_ORDER'],$app);
                                $newId = getNewId($db, $__tablename_notes);
                                writeLog("saveorder/notes","   New ID = ".$newId,$app);
                                $rigaNoteDB['ID'] = $newId;
                                $tabellaTestata = $db->$__tablename_head;
                                $tabellaTestata->where('TRANSACTION_NUM', $rigaTestataDB['TRANSACTION_NUM']);
                                $tabellaTestata->and('TRANSACTION_DATE', $rigaTestataDB['TRANSACTION_DATE']);
                                $tabellaTestata->and('LUNCH_TABLE_ID', $rigaTestataDB['LUNCH_TABLE_ID']);
                                writeLog("saveorder/notes","CHECK heading with Transaction_num=".$rigaTestataDB['TRANSACTION_NUM']." Transaction_date=".$rigaTestataDB['TRANSACTION_DATE']." Lunch_table_id=".$rigaTestataDB['LUNCH_TABLE_ID'],$app);
                                if (count($tabellaTestata) == 1 ) {
                                    foreach($tabellaTestata as $recordTabellaTestata) {
                                        writeLog("saveorder/notes","Trovata testata con id".$recordTabellaTestata['ID'],$app);
                                        $rigaNoteDB['HEADING_ID'] = $recordTabellaTestata['ID'];
                                    }
                                }
                                $result = $tabellaNote->insert($rigaNoteDB);
                                if ($result) {
                                    $esito=$esito."TRUE INSERT NOTES";
                                } else {
                                    $tuttoOk = false;
                                    $esito=$esito."FALSE INSERT NOTES";
                                    //print_r($rigaNoteDB);
                                    writeLog("saveorder/notes","INSERT Error. Dati=".implode("-",$rigaNoteDB),$app);
                                }
                                writeLog("saveorder/notes","INSERT Esito=".$esito,$app);
                            } else {
                                // $esito = $esito."fare_update note";
                                writeLog("saveorder/notes","UPDATE notes with Transaction_num=".$rigaNoteDB['TRANSACTION_NUM']." Transaction_date=".$rigaNoteDB['TRANSACTION_DATE']." Lunch_table_id=".$rigaNoteDB['LUNCH_TABLE_ID']." Detail_order=".$rigaNoteDB['DETAIL_ORDER']." -> ID=".$tabellaNote['ID'],$app);
                                $result = $tabellaNote->update($rigaNoteDB);
                                if ($result) {
                                    $esito=$esito."TRUE UPDATE NOTES";
                                } else {
                                    $tuttoOk = false;
                                    $esito=$esito."FALSE UPDATE NOTES";
                                }
                                writeLog("saveorder/notes","UPDATE Esito=".$esito,$app);
                            }
                            $status = $status."Situazione sconosciuta in note";
                        } catch(Exception $e) {
                            // $newId = $newId;
                            // $status = $status."AAA";
                            if ($e->getCode() != 2) {
                                $tuttoOk = false;
                                $status = $status." notes ".$e->getCode().' - '.$e->getMessage();
                            } else {
                                $status = $status." notes "."Insert ok !";
                            }
                        }
                    }
                }
			}
		}

	} catch(Exception $e) {
		if ($e->getCode() != 2) {
			$tuttoOk = false;
			$status = $status.$e->getCode().' - '.$e->getMessage();
			writeLog("saveorder","In exception ".$e->getCode().'-'.$e->getMessage(),$app);
		} else {
            $status = $status."Insert ok !";
			writeLog("saveorder","In exception [Insert ok]",$app);
	    }
	}
	
	
	$riga = "";
	$errDett = "";
	if (!($tuttoOk)) {
		$errDett = "Esito = ".$esito." <=> Status = ".$status;
		$esito = false;
		$status = "Error in save";
//$db->transaction = 'ROLLBACK';
	} else {
		$errDett = "";
		$esito = true;
		$status = "Insert Ok";
//$db->transaction = 'COMMIT';
    
            if ($mobile_pos_id <> "0") {
                // esegue la stampa
                writeLog("saveorder","Richiamo la stampa",$app);
                $ch = curl_init();
 
                $url_print_order = 'http://'.$app->request()->getHost().$app->request()->getRootUri().'/printorder/'.$LAST_LUNCH_TABLE_ID;
                writeLog("saveorder","URL ".$url_print_order,$app);

                //curl_setopt($ch, CURLOPT_URL, 'http://www.ma-no.org/test/test.php');
                curl_setopt($ch, CURLOPT_URL, $url_print_order);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
 
                curl_exec($ch);
                curl_close($ch);

                writeLog("saveorder","Stampa richiamata",$app);
            }
        }
	echo json_encode(array("status" => $esito,"message" => $messageResult, "dettaglio" => $errDett));

});


$app->post('/moveorder/from/:from_lunch_table_id/to/:to_lunch_table_id', function ($from_lunch_table_id, $to_lunch_table_id) use ($app, $db, $pdo, $__tablename, $__fields, $tabelle) {
	
	$app->response()->header("Content-Type", "application/json");

	$tuttoOk = True;
	$esito = "";
        writeLog("moveorder","Start move order from table with id ".$from_lunch_table_id." to table with id ".$to_lunch_table_id,$app);	

	if ($from_lunch_table_id == $to_lunch_table_id) {
                writeLog("moveorder","Cannot move order in same table (".$from_lunch_table_id." to ".$to_lunch_table_id,$app);	
		exit;
	}
	
	$__tablename_head = "SST_SALE_HEADINGS";
	try {
		$__campi_head = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_head) {
				foreach ($tabelle[$__tablename_head] as $synonym => $name) {
					$__campi_head[$synonym] = $name['name'];
				}
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS HEAD - ";
	}

	$__tablename_dett = "SST_SALE_DETAILS";
	try {
		$__campi_dett = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_dett) {
				foreach ($tabelle[$__tablename_dett] as $synonym => $name) {
					$__campi_dett[$synonym] = $name['name'];
				}
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS DETT - ";
	}	

	$__tablename_notes = "SST_SALE_DETAILS_NOTES";
	try {
		$__campi_notes = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_notes) {
				foreach ($tabelle[$__tablename_notes] as $synonym => $name) {
					$__campi_notes[$synonym] = $name['name'];
				}
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS NOTES - ";
	}
	

	$__tablename_lunch = "SST_LUNCH_TABLES_SITUATION";
	try {
		$__campi_lunch = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_lunch) {
				foreach ($tabelle[$__tablename_lunch] as $synonym => $name) {
					$__campi_lunch[$synonym] = $name['name'];
				}
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS LUNCH - ";
	}
	
	$valuePassed = $app->request()->post();
	$xml = "";
	$status = "";
	$curEntity = array();
	$pos_id = "";
	$heading_id = "";
	$pos_system_settings = $db->SST_POS_SYSTEM_SETTINGS;
	foreach ($pos_system_settings as $setting) {
		$pos_id = $setting['POS_ID'];
	}
	
	$debug = array();
	try {

		$table_source = $db->SST_LUNCH_TABLES_SITUATION;
		$table_source->where('ID',$from_lunch_table_id);
		$state_source = "0";

		$table_target = $db->SST_LUNCH_TABLES_SITUATION;
		$table_target->where('ID',$to_lunch_table_id);
		$state_target = "1";

		$lunch_table_source = $db->SST_LUNCH_TABLES;
		$lunch_table_source->where('ID',$from_lunch_table_id);
		$lunch_state_source_desc = "";
		foreach ($lunch_table_source as $lunch_table_source_row) {
                    $lunch_state_source_desc = $lunch_table_source_row['DESCRIPTION'];                    
		}
                writeLog("moveorder","1 Source table with description ".$lunch_state_source_desc,$app);			
		$lunch_table_target = $db->SST_LUNCH_TABLES;
		$lunch_table_target->where('ID',$to_lunch_table_id)->fetch();
		$lunch_state_target_desc = "";
		foreach ($lunch_table_target as $lunch_table_target_row) {
                    $lunch_state_target_desc = $lunch_table_target_row['DESCRIPTION'];                    
		}
                writeLog("moveorder","1 Target table with description ".$lunch_state_target_desc,$app);			

		// verifica che lo stato di partenza non sia libero (1)
		$debug[] = $table_source['LUNCH_STATE_ID'];
		foreach ($table_source as $riga_source) {
			$debug[] = $riga_source['LUNCH_STATE_ID'];
			$state_source = $riga_source['LUNCH_STATE_ID'];
		}
		$debug[] = "Stato tavolo sorgente = ".$state_source;
		
		// verifica lo stato di arrivo
		$debug[] = $table_target['LUNCH_STATE_ID'];
		foreach ($table_target as $riga_target) {
			$debug[] = $riga_target['LUNCH_STATE_ID'];
			$state_target = $riga_target['LUNCH_STATE_ID'];
		}
		$debug[] = "Stato tavolo arrivo = ".$state_target;
				
		$ora = new \DateTime();
		$dateTimeDML = $ora->format('Y-m-d H:i:s');
				
		if ($state_source <> "1") {
			
			if ($state_target == "1") {
				// il tavolo di arrivo è libero
                                writeLog("moveorder","Tavolo di arrivo libero",$app);			


				// crea la nuova comanda sul tavolo libero
				
				// recupera le info della testata di partenza
				$orderHeadSource = $db->SST_SALE_HEADINGS;
				$orderHeadSource->where('LUNCH_TABLE_ID',$from_lunch_table_id);
				$orderHeadSource->and('ORDINATION_CLOSED',0);
				$head_id_source = "";
				foreach ($orderHeadSource as $headSource) {
                                        writeLog("moveorder","in lettura orderHeadSource",$app);			
					$head_id_source = $headSource['ID'];
					$headTarget = $headSource;
					// come era prima
					$targetTransactionNum = $headSource['TRANSACTION_NUM'];
					// $targetTransactionDate = $headSource['TRANSACTION_DATE'];
					// $targetLunchTableId = $to_lunch_table_id;
					
				}
                                writeLog("moveorder","dopo lettura orderHeadSource",$app);			
				//$targetTransactionNum = getNewTransactionNum($db);
				$targetTransactionDate = getCurrentTimeStamp($db);
				$targetLunchTableId = $to_lunch_table_id;
                                writeLog("moveorder","nuova TransactionDate=".$targetTransactionDate,$app);			

                                //$headTarget['TRANSACTION_NUM'] = $targetTransactionNum;
                                $headTarget['TRANSACTION_DATE'] = $targetTransactionDate;
                                $headTarget['LUNCH_TABLE_ID'] = $to_lunch_table_id;
				$heading_id = getNewId($db, 'SST_SALE_HEADINGS');
				$headTarget['ID'] = $heading_id;
				//$headTarget['LAST_UPDATE_DATE'] = $ora->format('Y-m-d H:i:s'); //$dateTimeDML;
				$headTarget['LAST_UPDATE_DATE'] = getCurrentTimeStamp($db); //$dateTimeDML;
					
                                $headTarget['TEXT_FIELD_4'] = $lunch_state_source_desc.' -> '.$lunch_state_target_desc;
					
                                writeLog("moveorder","prima di insert",$app);			
				$orderHeadTarget = $db->SST_SALE_HEADINGS;
				$orderHeadTarget->insert($headTarget);
                                writeLog("moveorder","dopo di insert",$app);			
				

				// recupera l'ultimo DETAIL_ORDER dalle righe di arrivo
				$lastOrderNum = 0;
				
				//$targetTransactionNum = $headTarget->max('TRANSACTION_NUM');
				//$targetTransactionDate = $headTarget->max('TRANSACTION_DATE');
				//$targetLunchTableId = $headTarget->max('LUNCH_TABLE_ID');
				
                                writeLog("moveorder","Riporta le righe",$app);			
				// riporta le righe
				$orderDettTarget = $db->SST_SALE_DETAILS;
				$orderDettSource = $db->SST_SALE_DETAILS;
				$orderDettSource->where('HEADING_ID',$head_id_source);
				foreach ($orderDettSource as $dettSource) {
					//    (nuovo detail_order = attuale detail_order più il max detail_order del nuovo tavolo)
					$dettSource['DETAIL_ORDER'] = $dettSource['DETAIL_ORDER'] + $lastOrderNum;
					if ($dettSource['PARENT_DETAIL_ORDER'] != '0') {
						$dettSource['PARENT_DETAIL_ORDER'] = $dettSource['PARENT_DETAIL_ORDER'] + $lastOrderNum;							
					}
					$dettSource['TRANSACTION_NUM'] = $targetTransactionNum;
					$dettSource['TRANSACTION_DATE'] = $targetTransactionDate;
					$dettSource['LUNCH_TABLE_ID'] = $targetLunchTableId;
					//$dettSource['LAST_UPDATE_DATE'] = $ora->format('Y-m-d H:i:s'); //$dateTimeDML;
					$dettSource['LAST_UPDATE_DATE'] = getCurrentTimeStamp($db); //$dateTimeDML;
					
					$dettSource['ID'] = getNewId($db, 'SST_SALE_DETAILS');
					$orderDettTarget->insert($dettSource);
				}

                                writeLog("moveorder","Riporta le note",$app);			
				// riporta le note
				$orderNoteTarget = $db->SST_SALE_DETAILS_NOTES;
				$orderNoteSource = $db->SST_SALE_DETAILS_NOTES;
				$orderNoteSource->where('HEADING_ID',$head_id_source);
				foreach ($orderNoteSource as $noteSource) {
					$noteSource['DETAIL_ORDER'] = $noteSource['DETAIL_ORDER'] + $lastOrderNum;
					if ($noteSource['PARENT_DETAIL_ORDER'] != '0') {
						$noteSource['PARENT_DETAIL_ORDER'] = $noteSource['PARENT_DETAIL_ORDER'] + $lastOrderNum;							
					}
					$noteSource['TRANSACTION_NUM'] = $targetTransactionNum;
					$noteSource['TRANSACTION_DATE'] = $targetTransactionDate;
					$noteSource['LUNCH_TABLE_ID'] = $targetLunchTableId;
					//$noteSource['LAST_UPDATE_DATE'] = $ora->format('Y-m-d H:i:s'); //$dateTimeDML;
					$noteSource['LAST_UPDATE_DATE'] = getCurrentTimeStamp($db); //$dateTimeDML;
					
					$noteSource['ID'] = getNewId($db, 'SST_SALE_DETAILS_NOTES');
					$orderNoteTarget->insert($noteSource);
				}
				
				$orderHeadSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
                                
				$orderDettSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);

                                $orderNoteSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);

				// $orderHeadSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
                                writeLog("moveorder","Aggiorno lo stato dei tavoli",$app);			
				
				// somma il valore dei coperti dalla partenza all'arrivo
				// pongo lo stato del tavolo di arrivo uguale a quello del tavolo di partenza
				// azzera (=1) lo stato del tavolo di partenza
				$table_target->update(["LUNCH_STATE_ID"=>$riga_source['LUNCH_STATE_ID'],
										"OPENING_TIME"=>$riga_source['OPENING_TIME'],
										"REAL_GUESTS"=>$riga_source['REAL_GUESTS'], //+$riga_target['REAL_GUESTS'],
										"COVERS_NUM"=>$riga_source['COVERS_NUM'], //+$riga_source['COVERS_NUM'],
										"OPERATOR_CODE"=>'-NOTHING-',
										"LAST_TIME"=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
										//"LAST_TIME"=>$ora->format('Y-m-d H:i:s')]); //$dateTimeDML]);

                                writeLog("moveorder","Stato tavolo source aggiornato",$app);			
				$table_source->update(["LUNCH_STATE_ID"=>1,
										"OPENING_TIME"=>null,
										"REAL_GUESTS"=>0,
										"COVERS_NUM"=>0,
										"OPERATOR_CODE"=>'-NOTHING-',
										"LAST_TIME"=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
										//"LAST_TIME"=>$ora->format('Y-m-d H:i:s')]); //$dateTimeDML]);
				
                                writeLog("moveorder","Stato tavolo target aggiornato",$app);			

			} else {
				// il tavolo di arrivo è occupato
                                writeLog("moveorder","Tavolo di arrivo occupato",$app);			
				
				// aggiunge le righe alla comanda già presente sul tavolo di arrivo
				
				// recupera le info della testata di arrivo
				$orderHeadTarget = $db->SST_SALE_HEADINGS;
				$orderHeadTarget->where('LUNCH_TABLE_ID',$to_lunch_table_id);
				$orderHeadTarget->and('ORDINATION_CLOSED',0);
				foreach ($orderHeadTarget as $headTarget) {
					$heading_id = $headTarget['ID'];
                                        writeLog("moveorder","  -- arrivo nella comanda con id ".$heading_id."[".$headTarget['ID']."]",$app);								
				        //$headTarget['LAST_UPDATE_DATE'] = $ora->format('Y-m-d H:i:s'); //$dateTimeDML;
				        $headTarget['LAST_UPDATE_DATE'] = getCurrentTimeStamp($db); //$dateTimeDML;
					//$orderHeadTarget->update(['LAST_UPDATE_DATE'=>$ora->format('Y-m-d H:i:s')]);
					$orderHeadTarget->update(['LAST_UPDATE_DATE'=>getCurrentTimeStamp($db),'TEXT_FIELD_4'=>$lunch_state_source_desc." -> ".$lunch_state_target_desc]);

					// recupera le info della testata di partenza
					$orderHeadSource = $db->SST_SALE_HEADINGS;
					$orderHeadSource->where('LUNCH_TABLE_ID',$from_lunch_table_id);
					$orderHeadSource->and('ORDINATION_CLOSED',0);
					$head_id_source = "";
					foreach ($orderHeadSource as $headSource) {
						$head_id_source = $headSource['ID'];
					}
					
					// recupera l'ultimo DETAIL_ORDER dalle righe di arrivo
					$orderDettTarget = $db->SST_SALE_DETAILS;
					$orderDettTarget->where('HEADING_ID',$heading_id); // indifferente usare $headTarget['ID']
					$lastOrderNum = $orderDettTarget->max('DETAIL_ORDER');
					
					// recupera l'ultimo DETAIL_ORDER dalle note di arrivo, che se è maggiore dell'altro, vince
					$orderNoteTarget = $db->SST_SALE_DETAILS_NOTES;
					$orderNoteTarget->where('HEADING_ID',$heading_id);
					$lastOrderNumNote = $orderDettTarget->max('DETAIL_ORDER');
					if ($lastOrderNumNote > $lastOrderNum){
						$lastOrderNum = $lastOrderNumNote;
					}
									
					$targetTransactionNum = $orderDettTarget->max('TRANSACTION_NUM');
					$targetTransactionDate = $orderDettTarget->max('TRANSACTION_DATE');
					$targetLunchTableId = $orderDettTarget->max('LUNCH_TABLE_ID');

                                        writeLog("moveorder","Riporta le righe",$app);
					$orderDettSource = $db->SST_SALE_DETAILS;
					$orderDettSource->where('HEADING_ID',$head_id_source);
					foreach ($orderDettSource as $dettSource) {
						//    (nuovo detail_order = attuale detail_order più il max detail_order del nuovo tavolo)
                                                writeLog("moveorder","detail_order partenza = ".$dettSource['DETAIL_ORDER'],$app);
                                                writeLog("moveorder","lastOrderNum = ".$lastOrderNum,$app);
						$dettSource['DETAIL_ORDER'] = $dettSource['DETAIL_ORDER'] + $lastOrderNum;
						$dettSource['ROW_ORDER'] = $dettSource['DETAIL_ORDER'];
                                                writeLog("moveorder","detail_order arrivo = ".$dettSource['DETAIL_ORDER'],$app);
                                                writeLog("moveorder","PARENT_DETAIL_ORDER partenza = ".$dettSource['PARENT_DETAIL_ORDER'],$app);
						if ($dettSource['PARENT_DETAIL_ORDER'] != '0') {
							$dettSource['PARENT_DETAIL_ORDER'] = $dettSource['PARENT_DETAIL_ORDER'] + $lastOrderNum;							
						}
                                                writeLog("moveorder","PARENT_DETAIL_ORDER arrivo = ".$dettSource['PARENT_DETAIL_ORDER'],$app);
                                                $dettSource['TRANSACTION_NUM'] = $targetTransactionNum;
						$dettSource['TRANSACTION_DATE'] = $targetTransactionDate;
						$dettSource['LUNCH_TABLE_ID'] = $targetLunchTableId;
						//$dettSource['LAST_UPDATE_DATE'] = $ora->format('Y-m-d H:i:s'); //$dateTimeDML;
						$dettSource['LAST_UPDATE_DATE'] = getCurrentTimeStamp($db); //$dateTimeDML;
						
						$dettSource['ID'] = getNewId($db, 'SST_SALE_DETAILS');
						$orderDettTarget->insert($dettSource);
					}

                                        writeLog("moveorder","Riporta le note",$app);
					$orderNoteSource = $db->SST_SALE_DETAILS_NOTES;
					$orderNoteSource->where('HEADING_ID',$head_id_source);
					foreach ($orderNoteSource as $noteSource) {
                                                writeLog("moveorder","singola nota con DETAIL_ORDER ".$noteSource['DETAIL_ORDER'],$app);
						$noteSource['DETAIL_ORDER'] = $noteSource['DETAIL_ORDER'] + $lastOrderNum;
					//	$noteSource['ROW_ORDER'] = $noteSource['DETAIL_ORDER'];
                                                writeLog("moveorder","    che diventa ".$noteSource['DETAIL_ORDER'],$app);
						if ($noteSource['PARENT_DETAIL_ORDER'] != '0') {
							$noteSource['PARENT_DETAIL_ORDER'] = $noteSource['PARENT_DETAIL_ORDER'] + $lastOrderNum;							
						}
						$noteSource['TRANSACTION_NUM'] = $targetTransactionNum;
						$noteSource['TRANSACTION_DATE'] = $targetTransactionDate;
						$noteSource['LUNCH_TABLE_ID'] = $targetLunchTableId;
						//$noteSource['LAST_UPDATE_DATE'] = $ora->format('Y-m-d H:i:s'); //$dateTimeDML;
						$noteSource['LAST_UPDATE_DATE'] = getCurrentTimeStamp($db); //$dateTimeDML;
						
						$noteSource['ID'] = getNewId($db, 'SST_SALE_DETAILS_NOTES');
                                                writeLog("moveorder","inizia insert DETAIL_ORDER ".$noteSource['DETAIL_ORDER']." con NEW ID = ".$noteSource['ID'] ,$app);
						$orderNoteTarget->insert($noteSource);
                                                writeLog("moveorder","fine insert",$app);
					}
					
                                        writeLog("moveorder","Chiudo la comanda di partenza",$app);	

                                        $orderHeadSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
                                        
					$orderDettSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);

                                        $orderNoteSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);

                                        //$orderHeadSource->update(['ORDINATION_CLOSED'=>1,'LAST_UPDATE_DATE'=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
					
					// somma il valore dei coperti dalla partenza all'arrivo
					// pongo lo stato del tavolo di arrivo uguale a quello del tavolo di partenza
					// azzera (=1) lo stato del tavolo di partenza
                                        writeLog("moveorder","Aggiorno gli stati dei tavoli",$app);			
					$table_target->update(["REAL_GUESTS"=>$riga_source['REAL_GUESTS']+$riga_target['REAL_GUESTS'],
											"COVERS_NUM"=>$riga_source['COVERS_NUM']+$riga_target['COVERS_NUM'],
											"OPERATOR_CODE"=>'-NOTHING-',
											"LAST_TIME"=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
											//"LAST_TIME"=>$ora->format('Y-m-d H:i:s')]); //$dateTimeDML]);
                                        writeLog("moveorder","Stato tavolo source aggiornato",$app);			

					$table_source->update(["LUNCH_STATE_ID"=>1,
											"OPENING_TIME"=>null,
											"REAL_GUESTS"=>0,
											"COVERS_NUM"=>0,
											"OPERATOR_CODE"=>'-NOTHING-',
											"LAST_TIME"=>getCurrentTimeStamp($db)]); //$dateTimeDML]);
											//"LAST_TIME"=>$ora->format('Y-m-d H:i:s')]); //$dateTimeDML]);
                                        writeLog("moveorder","Stato tavolo target aggiornato",$app);			
					
				}
				
				// somma i coperti
				// azzera (=1) lo stato del tavolo di partenza
			}
		} else {
			// nessun tavolo da spostare (source state = 1)
                        writeLog("moveorder","Nothing to do",$app);			
		}
		
        } catch(Exception $e) {
		if ($e->getCode() != 2) {
			$tuttoOk = false;
			$status = $status.$e->getCode().' - '.$e->getMessage();
		} else {
                        $status = $status."Operation ok !";
                }
	}
	
	
	$riga = "";
	$errDett = "";
	if (!($tuttoOk)) {
		$errDett = "Esito = ".$esito." <=> Status = ".$status;
		$esito = -1;
		$status = "Error in save";
                        writeLog("moveorder","Esito KO = ".$esito." status = ".$status,$app);			
	} else {
	
                // stampa avviso cambio tavolo
                // il tavolo :from_lunch_table_id è stato spostato sul tavolo :to_lunch_table_id
                printAlertMoveOrder($heading_id, $app, $db, $pdo);

		$errDett = "";
		$esito = 1;
		$status = "Insert Ok";
                        writeLog("moveorder","Esito OK = ".$esito." status = ".$status,$app);			
	}
	echo json_encode(array("esito" => $esito,"status" => $status, "dettaglio" => $errDett, "debug" => $debug));
//	error_log('[StockStorePOS][moveorder] '.implode(array("esito" => $esito,"status" => $status, "dettaglio" => $errDett, "debug" => $debug)), 0);
});
