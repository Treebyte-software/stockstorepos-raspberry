<?php

//require_once('../classes/profile.php');

function packet_download($app, $db) {

    // contiene gli ID dei pacchetti scaricati
    $packetsId = array();
    // contiene l'xml di ogni singolo pacchetto
    $packets = array();
    // contiene gli ID dei pacchetti correttamente installati
    $packetsImported = array();
    $numPacketsDownloaded = 0;

    $sysSettings = $db->SST_POS_SYSTEM_SETTINGS->fetch();
    writeLog("poslivesync","POS_ID = ".$sysSettings['POS_ID'],$app);    

    $posSettings = $db->PLT_POS_LOCATIONS->where('POS_ID',$sysSettings['POS_ID'])->fetch();
    writeLog("poslivesync","HOST_LOCATION_ID = ".$posSettings['HOST_LOCATION_ID'],$app);    

    $hostSettings = $db->PLT_HOST_LOCATIONS->where('ID',$posSettings['HOST_LOCATION_ID'])->fetch();
    writeLog("poslivesync","HOST_NAME = ".$hostSettings['HOST_NAME'],$app);    
    
    //$url = "http://10.18.101.6:1212/poslive";
    //$url = "http://10.18.101.158:1212/poslive";
    $url = "http://".$hostSettings['HOST_NAME'].":".$hostSettings['PL_PORT']."/poslive";

    writeLog("poslivesync","Start for connet to ".$url,$app);    
    writeLog("poslivesync","  location : ".$posSettings['LOCATION_NAME'],$app);    
    
    // inizio loop
    do {
    
        $soapXml = '';
        $soapXml = $soapXml.'<?xml version="1.0" encoding="iso-8859-1"?>';
        $soapXml = $soapXml.'<methodCall>';
        $soapXml = $soapXml.'<methodName>GetPacketsBySize</methodName>';
        $soapXml = $soapXml.'<params>';
        $soapXml = $soapXml.'<param>';
        $soapXml = $soapXml.'<value>';
        $soapXml = $soapXml.'<struct>';
        $soapXml = $soapXml.'<member>';
        $soapXml = $soapXml.'<name>location_name</name>';
        $soapXml = $soapXml.'<value>';
        //$soapXml = $soapXml.'<string>101C01</string>'; // <- location name // da SSV_POS_SYSTEM_SETTINGS
        $soapXml = $soapXml.'<string>'.$posSettings['LOCATION_NAME'].'</string>';
        $soapXml = $soapXml.'</value>';
        $soapXml = $soapXml.'</member>';
        $soapXml = $soapXml.'<member>';
        $soapXml = $soapXml.'<name>operator_name</name>';
        $soapXml = $soapXml.'<value>';
        $soapXml = $soapXml.'<string>Admin</string>'; // <- operator // fisso
        $soapXml = $soapXml.'</value>';
        $soapXml = $soapXml.'</member>';
        $soapXml = $soapXml.'<member>';
        $soapXml = $soapXml.'<name>operator_status</name>';
        $soapXml = $soapXml.'<value>';
        $soapXml = $soapXml.'<string>LOGON</string>'; // fisso
        $soapXml = $soapXml.'</value>';
        $soapXml = $soapXml.'</member>';
        $soapXml = $soapXml.'<member>';
        $soapXml = $soapXml.'<name>max_packets_size</name>';
        $soapXml = $soapXml.'<value>';
        $soapXml = $soapXml.'<int>5120</int>'; // <- size // fisso
        $soapXml = $soapXml.'</value>';
        $soapXml = $soapXml.'</member>';
        $soapXml = $soapXml.'<member>';
        $soapXml = $soapXml.'<name>delete_packets_ids</name>';
        $soapXml = $soapXml.'<value>';

        if (count($packetsImported) >0) {
            $soapXml = $soapXml.'<string>';
            foreach ($packetsImported as $a => $b) {
                $soapXml = $soapXml.$b.',';
            }
            // rimuovo l'ultimo separatore
            $soapXml = substr($soapXml,0,strlen($soapXml)-1);
            $soapXml = $soapXml.'</string>';
        } else {
            $soapXml = $soapXml.'<string/>'; // <- list of id // da array $packetsImported
        }
        
        $soapXml = $soapXml.'</value>';
        $soapXml = $soapXml.'</member>';
        $soapXml = $soapXml.'</struct>';
        $soapXml = $soapXml.'</value>';
        $soapXml = $soapXml.'</param>';
        $soapXml = $soapXml.'</params>';
        $soapXml = $soapXml.'</methodCall>';

        //var_dump($soapXml);

        // reinizializzo l'array dei pacchetti da cancellare
        unset($packetsImported);
        $packetsImported = array();

        // struttura per xmlrpc
        $options = array(
            'http' => array(
                'header'  => "Content-type: text/xml\r\n",
                'method'  => 'POST',
                'content' => $soapXml
            )
        );

        // comunicazione
        $context  = stream_context_create($options);
	try {
	        $result = file_get_contents($url, false, $context);
	} catch(Exception $e) {
        	$result = FALSE; 
		writeLog("poslivesync","ERRORE in fase di connessione!!!! ESCO !!!!",$app);    
	}
				
        if ($result === FALSE) { 
            /* Handle error */ 
            $result = "ERRORE !!!";
            writeLog("poslivesync","ERRORE in fase di connessione!!!! ESCO !!!!",$app);    
        } else {

            //var_dump($result);		
            // decodifica il messaggio di ritorno
            $pacchetti = xmlrpc_decode($result);
            //var_dump($pacchetti);		
            if (array_key_exists('faultCode',$pacchetti)) {
                // la risposta contiene un messaggio di errore
                //   il messaggio riporta un faultCode e un faultString
		writeLog("poslivesync","ERRORE !!!! ".$pacchetti['faultString']." ESCO !!!!",$app);    
            } else {
                if (!array_key_exists('current_packets_count',$pacchetti)) {
		    writeLog("poslivesync","ERRORE !!!! Non ho il numero dei pacchetti scaricati. Situazione NON PREVISTA. ESCO !!!!",$app);    
                } else {
		    writeLog("poslivesync","Scaricati ".$pacchetti['current_packets_count'],$app);   
                    $numPacketsDownloaded = $pacchetti['current_packets_count'];
		if ($pacchetti['current_packets_count'] != 0) { 
		    //var_dump($result);

                    // ciclo sui pacchetti
                    foreach( $pacchetti['packets_dataset']['RTC.DATASET.ROWS'] as $pacchetto_singolo) {
                    
                    
                        // pacchetto_singolo contiene il record da salvare sul DB
                        //   ed il campo 6 l'xml da salvare su file system
                    
                        // quindi chiudo ill foreach
                    
                        // inizio foreach leggendo dalla tabella
                    
                        $packetsId[] = $pacchetto_singolo[0];
                        // $packets[] = gzinflate($pacchetto_singolo[6]->scalar);
                        
                        $xmlPacket = gzinflate($pacchetto_singolo[6]->scalar);

                        writeLog("poslivesync","Found ".count($packetsId),$app);
                    
                        // elaboro il singolo pacchetto, che logicamente può contenere varie tabelle
                        //   in caso di un qualsiasi errore il pacchetto NON deve venire cancellato.
                        $statusSinglePacket = true;
                            
                        $xmlDoc = new SimpleXMLElement($xmlPacket);

                        $xmlDoc->asXml('iaprofiles/data_poslive/'.$pacchetto_singolo[0].'_'.basename( str_replace("\\","/",$pacchetto_singolo[2])));
                        
			unset($queue_fields);
			$queue_fields = array();

                        $queue_fields ['ID'] = $pacchetto_singolo[0];
			$queue_fields ['LOCATION_NAME'] = $posSettings['LOCATION_NAME'];
                        // TEST $queue_fields ['LOCATION_NAME'] = '101C01';
                        $queue_fields ['SOURCE_ID'] = $pacchetto_singolo[0];
                        //$queue_fields ['PROFILE_FILE'] = $pacchetto_singolo[2];
			//$queue_fields ['PROFILE_FILE'] = $pacchetto_singolo[0].'_'.basename( str_replace("\\","/",$pacchetto_singolo[2]));
			$queue_fields ['PROFILE_FILE'] = str_replace("\\","/",$pacchetto_singolo[2]);
                        $queue_fields ['EXECUTION_TYPE'] = $pacchetto_singolo[3];
                        //$queue_fields ['PACKET'] = '';
                        //$queue_fields ['MD5_PACKET'] = $pacchetto_singolo[0].'_'.basename($pacchetto_singolo[2]);
                        $queue_fields ['MD5_PACKET'] = $pacchetto_singolo[0];
                        $queue_fields ['PACKET_SIZE'] = $pacchetto_singolo[4];
                        $queue_fields ['RECORDS_COUNT'] = $pacchetto_singolo[5];
                        $queue_fields ['INSERTION_DATE'] = getCurrentTimeStamp($db);
                        //$queue_fields ['INSERTION_DATE'] = '2017.01.31 15:53:01';
                        //$queue_fields ['LAST_IMPORT_TRY'] = '';
                        $queue_fields ['NUM_RETRY'] = 0;
                        $queue_fields ['ENABLED'] = 1;

                        $queue = $db->PLT_IA_PROFILES_QUEUE;
			$queue->where('ID',$queue_fields ['ID']);
			if (count($queue) == 0) {
				try {
					$ritorno_insert = $queue->insert($queue_fields);
				} catch(Exception $e) {
					if ($e->getCode() != 2) {
						//$tuttoOk = false;
						//$status = $status." head ".$volte." ".$e->getCode().' - '.$e->getMessage();
					} else {
						//$status = $status." head ".$volte." Insert ok !";
					}
				}
			} else {
				try {
					$ritorno_insert = $queue->update($queue_fields);
				} catch(Exception $e) {
					if ($e->getCode() != 2) {
						//$tuttoOk = false;
						//$status = $status." head ".$volte." ".$e->getCode().' - '.$e->getMessage();
					} else {
						//$status = $status." head ".$volte." Insert ok !";
					}
				}
			}
                        if ($statusSinglePacket) {
				writeLog("poslivesync","Salvo ID ".$pacchetto_singolo[0],$app);    
				$packetsImported[] = $pacchetto_singolo[0];
                        }
                    }
                }
		}
            }
        } // test numero pacchetti
		
    } while($numPacketsDownloaded > 0);

    writeLog("poslivesync","End of syncro",$app);    

}

?>

