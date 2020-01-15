<?php

//require_once('../classes/profile.php');

function packet_import($app, $db) {

	$packetsId = array(); // serve solo ora in quanto sdoppiato il codice
	$packetsImported = array(); // serve solo ora in quanto sdoppiato il codice

	$queue = $db->PLT_IA_PROFILES_QUEUE;
	$queue->where('ENABLED',1);
	$queue->and('NUM_RETRY < ?',3);

	foreach($queue as $packet_to_import) {
        
		// pacchetto_singolo contiene il record da salvare sul DB
	        //   ed il campo 6 l'xml da salvare su file system
                    
	        // quindi chiudo il foreach
                    
	        // inizio foreach leggendo dalla tabella
	                    
        	$packetsId[] = $packet_to_import['ID'];
            
		writeLog("poslivesync","Found ".count($packetsId)." packet",$app);
                    
        	// elaboro il singolo pacchetto, che logicamente può contenere varie tabelle
	        //   in caso di un qualsiasi errore il pacchetto NON deve venire cancellato.
        	$statusSinglePacket = true;
                       
		// recupero il profilo
		$def_profile = 'iaprofiles'.str_replace('.dat.','.def.',$packet_to_import['PROFILE_FILE']);
		$xmlDef = simplexml_load_file($def_profile);
		$defMaster = $xmlDef->xpath("/table");
		foreach($defMaster as $singleDefMaster) {
			writeLog('poslivesync','Unique fields -> '.$singleDefMaster->attributes()['unique_fields'],$app);
		}
     
		writeLog('poslivesync', 'Load file iaprofiles/data_poslive/'.$packet_to_import['SOURCE_ID'].'_'.basename($packet_to_import['PROFILE_FILE']),$app);
                        
	        //$xmlDoc = simplexml_load_file('iaprofiles/data_poslive/'.$packet_to_import['PROFILE_FILE']); // <-- valutare quale campo utilizzare
        	$xmlDoc = simplexml_load_file('iaprofiles/data_poslive/'.$packet_to_import['SOURCE_ID'].'_'.basename($packet_to_import['PROFILE_FILE'])); // <-- valutare quale campo utilizzare
            
	        $tableMaster = $xmlDoc->xpath("/table");
        	foreach($tableMaster as $singleTableMaster) {
			$result = "";
			writeLog("poslivesync","TABELLA = ".$singleTableMaster->attributes()['name'],$app);
			$recordMaster = $singleTableMaster->xpath("record");
			if (count($recordMaster) > 0) {
				$name_dbTableMaster = $singleTableMaster->attributes()['name'];
				///////////////////////////////////////////$dbTableMaster = $db->$name_dbTableMaster;
				// cicla su tutti i record della tabella principale
				$tuttoOk = true;
				foreach ($recordMaster as $singleRecordMaster) {
					// manca il lookup del record con la pk che però bisogna recuperare dal profilo
					$arraySingleRecordMaster = array();
					foreach($singleRecordMaster->attributes() as $a => $b) {
//if ($a != 'LAST_UPDATE_DATE') {
						//writeLog("poslivesync","             a = ".$a." b = ".$b,$app);
			                        $arraySingleRecordMaster[$a] = getFieldConvertByType($name_dbTableMaster, $a, $b, $db);
//						if ($b == "") {
//							$arraySingleRecordMaster[$a] = null;
//						} else {
//			                            if (isFieldDateTime($name_dbTableMaster,$a,$db)) {
//                        			//        //$arraySingleRecordMaster[$a] = substr($b,6,2).'-'.substr($b,4,2).'-'.substr($b,0,4).' '.substr($b,8,2).':'.substr($b,10,2).':'.substr($b,12,2);
//                        			        $arraySingleRecordMaster[$a] = substr($b,6,2).'.'.substr($b,4,2).'.'.substr($b,0,4).' '.substr($b,8,2).':'.substr($b,10,2).':'.substr($b,12,2);
//                        			//        writeLog('poslivesync'," => ".$a." = ".substr($b,6,2).'.'.substr($b,4,2).'.'.substr($b,0,4).' '.substr($b,8,2).':'.substr($b,10,2).':'.substr($b,12,2),$app);
//			                            } else {
//                        			        $arraySingleRecordMaster[$a] = $b;
//				                //        $arraySingleRecordMaster[$a] = getFieldConvertByType($name_dbTableMaster, $a, $b, $db);
//			                            }
//						}
//}
					}
					writeLog("poslivesync","  ID vale ".$arraySingleRecordMaster['ID'],$app);

				$dbTableMaster = $db->$name_dbTableMaster;
					$dbTableMaster->where('ID',$arraySingleRecordMaster['ID']);
					try {
						if (count($dbTableMaster) == 0) {
							writeLog("poslivesync","  Sono in insert",$app);
							$result = $dbTableMaster->insert($arraySingleRecordMaster);
						} else {
//print_r($arraySingleRecordMaster);
							writeLog("poslivesync","  Sono in update",$app);
//							writeLog("poslivesync","             -- ".implode(';',array_keys($arraySingleRecordMaster)),$app);
//							writeLog("poslivesync","             -- ".implode(';',$arraySingleRecordMaster),$app);
//							writelog("poslivesync","UPDATE ".$name_dbTableMaster." SET ",$app);
//							foreach($arraySingleRecordMaster as $uno => $due) {
//								writelog("poslivesync"," ".$uno." = ".$due.",",$app);							
//							}
//							writelog("poslivesync"," WHERE ID = ".$arraySingleRecordMaster['ID'].";",$app);
							//$result = $dbTableMaster->update($arraySingleRecordMaster);
							$result = $db->$name_dbTableMaster->where('ID',$arraySingleRecordMaster['ID'])->update($arraySingleRecordMaster);
//echo "<br>".$result."<br>";
						}
					} catch(Exception $e) {
						if ($e->getCode() != 2) {
							writeLog("poslivesync","Errore in catch. Result vale : ".$result." Error vale = ".$e->getCode().' - '.$e->getMessage(),$app);
							$tuttoOk = false;
							$statusSinglePacket = false;
						} else {
							$tuttoOk = true;
						}
					}
//var_dump($result); 
					if ($result) {
						//$esito=$esito."TRUE INSERT HEAD";
					} else {
						writeLog("poslivesync","Errore. Result vale : ".$result,$app);
						$tuttoOk = false;
						$statusSinglePacket = false;
						//$esito=$esito."FALSE INSERT HEAD";
					}
                                        
					// per ogni record elaboro eventuali child
					$childTables = $singleRecordMaster->xpath("child_records/table");
					foreach($childTables as $singleTableChild){
						writeLog("poslivesync","--TABELLA FIGLIA = ".$singleTableChild->attributes()['name'],$app);

						$defMaster = $xmlDef->xpath("/table/child_tables/table");
						$name_dbTableChild = $singleTableChild->attributes()['name'];
						writeLog('poslivesync',"    ...".$name_dbTableChild,$app);
						foreach($defMaster as $singleDefMaster) {
							if (strtoupper($singleDefMaster->attributes()['name']) == strtoupper($name_dbTableChild)) {
								if(strtolower($singleDefMaster->attributes()['deletion_type']) == 'parent') {
									writeLog('poslivesync','CANCELLO TUTTI I DETTAGLI',$app);
									$fieldsChild = $singleDefMaster->xpath("fields/field");
									foreach($fieldsChild as $singleFieldChild) {
										if ( $singleFieldChild->attributes()['parent_field'] != "") {
											$aaa = $singleFieldChild->attributes()['parent_field'];
											writeLog('poslivesync','DELETE FROM '.$name_dbTableChild.' WHERE '.$singleFieldChild->attributes()['name'].' = '.$arraySingleRecordMaster["$aaa"],$app);
											$table_clear = $db->$name_dbTableChild;
											$table_clear->where($singleFieldChild->attributes()['name'],$arraySingleRecordMaster["$aaa"]);
											$table_clear->delete();
											writeLog('poslivesync','DELETED',$app);
										}
									}
								}
							}
						}

						$recordChild = $singleTableChild->xpath("record");
						if (count($recordChild) > 0) {
							$name_dbTableChild = $singleTableChild->attributes()['name'];

							foreach($recordChild as $singleRecordChild){
								$dbTableChild = $db->$name_dbTableChild;
								$arraySingleRecordChild = array();
								foreach($singleRecordChild->attributes() as $ac => $bc) {
					                                $arraySingleRecordChild[$ac] = getFieldConvertByType($name_dbTableChild, $ac, $bc, $db);
//									if ($bc == "") {
//										$arraySingleRecordChild[$ac] = null;
//									} else {
//                                    					    if (isFieldDateTime($name_dbTableChild,$ac,$db)) {
//					                                        //$arraySingleRecordChild[$ac] = substr($bc,6,2).'-'.substr($bc,4,2).'-'.substr($bc,0,4).' '.substr($bc,8,2).':'.substr($bc,10,2).':'.substr($bc,12,2);
//					                                        $arraySingleRecordChild[$ac] = substr($bc,6,2).'.'.substr($bc,4,2).'.'.substr($bc,0,4).' '.substr($bc,8,2).':'.substr($bc,10,2).':'.substr($bc,12,2);
//					                                    } else {
//					                                        $arraySingleRecordChild[$ac] = $bc;
//					                                    }
//									}
								}
								writeLog("poslivesync","    ID vale ".$arraySingleRecordChild['ID'],$app);
								$dbTableChild->where('ID',$arraySingleRecordChild['ID']);
								try {
									if (count($dbTableChild) == 0) {
										writeLog("poslivesync","  Sono in insert",$app);
										$result = $dbTableChild->insert($arraySingleRecordChild);
									} else {
										writeLog("poslivesync","  Sono in update",$app);
										$result = $dbTableChild->update($arraySingleRecordChild);
										writeLog("poslivesync","  Result vale ".$result,$app);
									}
								} catch(Exception $e) {
									if ($e->getCode() != 2) {
										writeLog("poslivesync","Errore in catch child. Result vale : ".$result." Error vale = ".$e->getCode().' - '.$e->getMessage(),$app);
										$tuttoOk = false;
										$statusSinglePacket = false;
									} else {
										$tuttoOk = true;
									}
								}
								if ($result) {
									//$esito=$esito."TRUE INSERT HEAD";
								} else {
									writeLog("poslivesync","Errore. Result Child vale : ".$result,$app);
									$tuttoOk = false;
									$statusSinglePacket = false;
									//$esito=$esito."FALSE INSERT HEAD";
								}
							}
						}
					}
				}
			}
			if ($statusSinglePacket) {
				writeLog("poslivesync","Importazione corretta, elimino il pacchetto con ID ".$packet_to_import['ID'],$app);
				$queue->delete();
				$packetsImported[] = $packet_to_import['ID'];
			} else {
				//$queue['NUM_RETRY'] = $queue['NUM_RETRY'] + 1;
				writeLog("poslivesync","Porta i tentativi a ".$packet_to_import['NUM_RETRY']+1,$app);
				$queue->update(['NUM_RETRY'=>$packet_to_import['NUM_RETRY']+1]);
			}
		}
     
	}

	var_dump($packetsImported);  

	writeLog("poslivesync","End",$app);

}

?>

