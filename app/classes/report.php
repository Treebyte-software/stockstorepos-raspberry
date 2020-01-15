<?php


class Report {
	

	static $layout_id = "";
	
	public function getLayoutId(){
		return Report::$layout_id;
	}
	
	function bindReportVariables($statement, $parametri){
			// sostituisce le variabili con i relativi valori

		$sql = $statement;
		foreach ($parametri as $parametro => $value) {
			$sql = str_replace($parametro, $value, $sql);
		}
		return $sql;
	}

	function clearReportVariables($statement) {
		// elimina i riferimenti ad eventuali variabili presenti
		//   esempio : select * from tabella where code = :codice ad enabled = 1
		//                diventa
		//             select * from tabella where enabled = 1
			
		// momentaneamente facciamo finta che vengano passati tutti i parametri ?????
		return $statement;
	}		
	
	function readDat($datasource_detail_id, $statement_id, $conditions, $previousOrderNum, $outputXml, $app, $db, $pdo, $parent_id = 0) {
	
		try {
			$esitoRead="";
			$dtsChilds = $db -> sst_datasource_details;
			$dtsChilds->where("PARENT_DETAIL_ID = ?", $datasource_detail_id);
			$dtsChilds->where("ORDER_NUM > ?", $previousOrderNum);
			$dtsChilds->order("ORDER_NUM");
                        writeLog("readDat","Parent_detail_id=".$datasource_detail_id." Order_num=".$previousOrderNum,$app);				
			$sqlStatements = $db->sst_sql_statements;
			$sqlStatements->where("ID = ?", $statement_id); 
                        writeLog("readDat","Sql_Statement_id=".$statement_id,$app);				
			foreach ($sqlStatements as $statement ) {
				$sql = $statement['SQL_STATEMENT'];
                                // meno log writeLog("readDat","Sql=".$sql,$app);				
				$newSql = $this->bindReportVariables($sql, $conditions);
				$newSql = $this->bindReportVariables($newSql, array(":parent_id"=>$parent_id));
                                // meno log writeLog("readDat","newSql=".$newSql,$app);				
				// ESEGUE LA QUERY
				$ind = 0;
				foreach ($pdo->query($newSql) as $dato) {
					if ($ind == 0) {
						$rigaTable = $outputXml;
						if ($previousOrderNum > 0) {
							$rigaTable = $outputXml->addChild('table');
						}
						$ind = 1;
						$rigaTable->addAttribute('name',$statement['DESCRIPTION']);
					}
					
					$riga = $rigaTable->addChild('record');
					$previousID = '-NOTHING-';
					$pari = 0;
					foreach ($dato as $campo => $valore) {
						//echo "Field ".$campo." Value ".$valore;
						if ($pari == 0) {
							$riga->addChild($campo, utf8_encode($valore));
							
							if ($campo == 'ID') {
								//$previousID = $campo;
								$previousID = $valore;
							}
							$pari = 1;
						} else {
							$pari = 0;
						}
					}
					if (count($dtsChilds) > 0) {
						foreach ($dtsChilds as $dtsChild) {
							// richiama se stessa passando il datasource_details_id
							$resultQuery = array();
							$resultQuery['ID'] = $previousID; // deve valere l'ID del record appena letto
                                                        writeLog("readDat","previousID=".$previousID,$app);
                                                        writeLog("readDat","resultQuery=".implode(", ",$resultQuery),$app);
							$child = $riga->addChild('child_records');
							$esitoRead = $esitoRead.$this->readDat($dtsChild['ID'], $dtsChild['STATEMENT_ID'], $conditions, $dtsChild['ORDER_NUM'], $child, $app, $db, $pdo, $resultQuery['ID']);
							
						}
					}
				}
			}
		} catch(Exception $e) {
			writeLog("getDat","Error (ricorsivo) ".$e->getMessage(),$app);
			$esitoRead=$esitoRead.$e->getMessage();
		}
		return $esitoRead; // return $outputXml;
	}
	
	function getDat($app, $db, $repcode, $conditions, $pdo) {
		//	protected
		$debug = array();
	
		try {
			$debug[] = "inizio getDat";
			$parameters = array();
			$ind = 1;
			$debug[] = $conditions;
			foreach ($conditions as $condition) {
				if ($ind == 1) {
						$parametro = ':'.$condition;
						//$parameters[] = $parametro;
						$ind = 0;
				} else {
						$parameters[$parametro] = $condition;  // coppia parametro e valore
						$ind = 1;
				}
			}
			
			$debug[] = "verifico l'esistenza del report ".$repcode;
			// verifico l'esistenza del report
			$reports = $db -> sst_reports;
			$reports -> where("code = ?",$repcode);
			if (count($reports) == 0) {
				echo json_encode(array("status" => false, "message" => "Report ".$repcode." not found"));
			} else {
				foreach ($reports as $singoloReport) {
					// $singoloReport è il record del report da stampare
                                        $debug[] = "   usa il layout ".$singoloReport['LAYOUT_ID'];
					Report::$layout_id = $singoloReport['LAYOUT_ID'];
					// recupero il primo statement da eseguire
					$dtsDetails = $db->sst_datasource_details;
					$dtsDetails->where("DATASOURCE_ID",$singoloReport['DATASOURCE_ID']);
					$dtsDetails->where("PARENT_DETAIL_ID IS NULL");
					// TODO nella funzione passare il precedente order_num e la prima volta passare 0
					$dtsDetails->order("ORDER_NUM");
					if (count($dtsDetails) == 0) {
                                                $debug[] = "   nessun dettaglio";
						echo json_encode(array("status" => false, "message" => "Report ".$repcode." not configured [dtsDetails not found]"));
					} else {
                                                $debug[] = "   ok dettaglio";
						$outputXml = new \SimpleXMLElement('<table/>');                                    
						foreach ($dtsDetails as $singleDetails) {
                                                        $debug[] = "   eseguo readDat";
							$esitoRead = $this->readDat($singleDetails['ID'], $singleDetails['STATEMENT_ID'], $parameters, 0, $outputXml, $app, $db, $pdo);
                                                        $debug[] = "   esito readDat ".$esitoRead;
						}
					}
				}
			}
		
			$debug[] = "fine getDat";
		} catch(Exception $e) {
			// $outputXml = $debug;
			$debug[] = "In getDat ".$e->getCode().' - '.$e->getMessage();

			writeLog("getDat","Error ".$e->getCode().' - '.$e->getMessage(),$app);
			writeLog("getDat","Error trace : ".implode(" * ",$debug),$app);

			return $debug;
		}
		
		if ($esitoRead == "") {
			return $outputXml;
		} else {
			return $debug;
		}
	
	}
	
	function transform($app, $db, $xsl_id, $dat) {

		$xml = "";
		
		$layout = $db->sst_layouts;
		$layout->where('ID',$xsl_id);
	        if (count($layout) == 0) {
        	    echo json_encode(array("status" => false, "message" => "Layout : ".$xsl_id));
	        } else {
        		foreach ($layout as $template) {
		                $xslDoc = new DOMDocument();
		                $xslDoc->load(str_replace("%SSDIR%\\", '', str_replace('%LAYOUTS%\\', '',$template['FILE_NAME'])));
		                $proc = new XSLTProcessor();
                		$proc->importStylesheet($xslDoc);
		                $xml = $xml.$proc->transformToXML($dat);
			}
		}
		return $xml ;
	}
		
}


class ThermalReport extends Report {
	

	private	function postXMLToURL ($server, $path, $xmlDocument) {

		$contentLength = strlen($xmlDocument);
		//$fp = fsockopen($server, 80, $errno, $errstr, 30);
		$fp = fsockopen($server, 80, $errno, $errstr, 10);
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $server\r\n");
		fputs($fp, "Content-Type: text/html\r\n");
		fputs($fp, "Content-Length: $contentLength\r\n");
		fputs($fp, "Connection: close\r\n");
		fputs($fp, "\r\n"); // all headers sent
		fputs($fp, $xmlDocument);
		$result = '';
		while (!feof($fp)) {
			$result .= fgets($fp, 128);
		}
		return $result;
	}
	
	private	function getBody ($httpResponse) {
		//var_dump($httpResponse);
		$lines = preg_split('/(\r\n|\r|\n)/', $httpResponse);
		$responseBody = '';
		$lineCount = count($lines);
		for ($i = 0; $i < $lineCount; $i++) {
			if ($lines[$i] == '') {
				break;
			}
		}
		for ($j = $i + 1; $j < $lineCount; $j++) {
			$responseBody .= $lines[$j]; // . "n";
		}
		return $responseBody;
	}
		
	private function printIntelligent_s($dati, $host, $devid="02", $timeout="6000") {
		//writeLog("report/intelligent_s","inizio stampa su ".$host,$app);
//		echo "intelligent_s";
		$xmlpacket = $dati;

		$soapXml = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body>';
		$soapXml = $soapXml.$xmlpacket;
		$soapXml = $soapXml.'</s:Body></s:Envelope>';		
		
		$result = $this->postXMLtoURL($host, "/cgi-bin/epos/service.cgi?devid=".$devid."&timeout=".$timeout,$soapXml);
		$responseBody = $this->getBody($result);
		return $responseBody;
		
	}

	private function printIntelligent_c($dati, $host, $devid="02", $timeout="6000") {
		//writeLog("report/intelligent_c","inizio stampa su ".$host,$app);
//		echo "intelligent_c";

		$datXml = htmlspecialchars_decode($dati);
		
		$soapXml = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body>';
		$soapXml = $soapXml.$datXml;
		$soapXml = $soapXml.'</s:Body></s:Envelope>';

                $url = "http://".$host."/cgi-bin/epos/service.cgi?devid=".$devid."&timeout=".$timeout;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		//curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $soapXml);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
											'Content-type: text/xml', 
											'Content-length: ' . strlen($soapXml)
											));
		$output = curl_exec($ch);
		curl_close($ch);
		
		return $output;
		
	}

	private function printIntelligent_f($dati, $host, $devid="02", $timeout="6000") {
		//writeLog("report/intelligent_f","inizio stampa su ".$host,$app);
		//echo "intelligent_f";

		$datXml = htmlspecialchars_decode($dati);
				
		$soapXml = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body>';
		$soapXml = $soapXml.$datXml;
		$soapXml = $soapXml.'</s:Body></s:Envelope>';
		
		
                $url = "http://".$host."/cgi-bin/epos/service.cgi?devid=".$devid."&timeout=".$timeout;
		$data = array('xml' => $soapXml);
				//'timeout'  => 3,
		$options = array(
			'http' => array(
				'header'  => "Content-type: text/xml\r\n",
				'method'  => 'POST',
				'content' => $soapXml
			)
		);

		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if ($result === FALSE) { 
			/* Handle error */ 
			$result = "ERRORE !!!";
		}

		return $result;
	}
	
	function printIntelligent($dati, $host, $devid="02", $timeout="6000", $version = "S") {
		//$dati = utf8_encode($dati);
		$version = "S"; // forzo alla versione S
		if (strtoupper($version) == "S") {
			return $this->printIntelligent_s($dati, $host, $devid, $timeout);
		}
		if (strtoupper($version) == "C") {
			return $this->printIntelligent_c($dati, $host, $devid, $timeout);
		}
		if (strtoupper($version) == "F") {
			return $this->printIntelligent_f($dati, $host, $devid, $timeout);
		}
	}
	
	function printEscPos($dati, $host, $port = "9100", $app) {

        $string = $dati;
        $string = "";
        $sonoHex = 0;
        $valore = "";
        $esito = false;
//echo "1\n";	
        // verifico il semaforo
        $SEMKEY = 1234567890;
//        try {
//echo "2\n";	
//            $semaforo = sem_get($SEMKEY, 1);
//echo "3\n";	
//            if ($semaforo === false) {
//echo "4\n";	
//                $esito = false;
//echo "problemi in sem_get\n";
//            } else {
//echo "5\n";	
                // se libero, me lo riservo e stampo
//                if (!sem_acquire($semaforo) ) {
//echo "6\n";	
//echo "Semaforo occupato\n";
//                    $esito = false;
//                } else {
//echo "7\n";	
//echo "Semaforo libero, me lo riservo (attendo 30 secondi)\n";
//sleep(30);
//sleep(15);
//echo "30 secondi passati\n";
                    
                    $ogniCarattere = str_split($dati);
                    $dati = '$1B;$52;$06;$1B;$74;$19;'.$dati.chr(13);
                    $ora = new \DateTime();
                    $date_for_save = $ora->format('Y-m-d_H-i-s');
                    file_put_contents(getFolderLog().$date_for_save.'_dati_esc_pos-'.uniqid().'.txt',$dati, FILE_APPEND);	
                    
//echo "8\n";	
                    foreach ($ogniCarattere as $single) {
                        if ($single == '$') {
                            $sonoHex = 1;
                            $valore = "";
                        }
                        if (($sonoHex == 1) && ($single != '$') && ($single != ';')) {
                            $valore = $valore.$single;
                        }
                        if (($sonoHex != 1) && ($single != '$') && ($single != ';')) {
                            //$string .= mb_convert_encoding($single, "WINDOWS-1252");
                            $string .= $single;
                        }
                        if (($single == ';') && ($sonoHex = 1)) {
                            //$string .= chr('0x'.$valore);
                            $string .= chr(base_convert($valore,16,10));
                            $valore = "";
                            $sonoHex = 0;
                        }
                    }

                    file_put_contents(getFolderLog().$date_for_save.'_flusso_esc_pos-'.uniqid().'.txt',$string, FILE_APPEND);	
                    try {
//echo "9\n";	
                        //send data via TCP/IP port : the printer has tcp interface
                        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                        //if ($socket === false) {
                        if (!$socket) {
                            writeLog("printEscPos","socket_create() failed: reason: ".socket_strerror(socket_last_error()),$app);
                            //echo "socket_create() failed: reason: ".socket_strerror(socket_last_error())."\n";
                            $esito = false;
                        } else {
                            //echo "OK\n";
//echo "10\n";	
//HNST                        }

//echo "A\n";	
						socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array('sec'=>10,'usec'=>0));
						socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,array('sec'=>10,'usec'=>0));
                        $result = socket_connect($socket, $host, $port);
                        //if ($result === false) {
                        if (!$result) {
                            //echo "socket_connect() failed.\nReason: ($result) " . socket_strerror    (socket_last_error($socket)) . "\n";
                            writeLog("printEscPos", "socket_connect() failed. Reason: ($result) ".socket_strerror(socket_last_error($socket)),$app);
                            //echo "socket_connect() failed.\nReason: ($result) ".socket_strerror(socket_last_error($socket))."\n";
                            $esito = false;
                        } else {
//echo "11\n";	
//HNST                        }	
//echo "B\n";	
                        try {

                            $lunghezza = strlen($string);
                            while (true) {
//echo "Before send\n";	
                                $sent = socket_write($socket, $string, $lunghezza);
//echo "After send\n";	
                                //if ($sent === false) {
                                if (!$sent) {
                                    break;
                                }
                                if ($sent < $lunghezza) {
                                    $string = substr($string,$sent);
                                    $length -= $sent;
                                } else {
                                    break;
                                }
                            }
                            $esito = true;	
                            //echo "12\n";	
                            
                        } catch(Exception $e) {
                            //echo "ERRORE !!!!!!<br/>";
                            //echo $e->getMessage();
                            $esito = false;
                            writeLog("printEscPos", "ERRORE : ".$e->getMessage(),$app);
                            //echo "ERRORE : ".$e->getMessage()."\n";
                        }
					}
					}
                    } catch(Exception $e) {
                        $esito = false;
                        writeLog("printEscPos", "ERRORE : ".$e->getMessage(),$app);
                        //echo "ERRORE : ".$e->getMessage()."\n";
                    } finally {
                        socket_close($socket);
                    }
//                }
//            } // chiusura del sem_acquire
//        } catch(Exception $e) {
//            $esito = false;
//            echo "ERRORE : ".$e->getMessage()."\n";
//        } finally {
            // indipendentemente da tutto, rilascio il semaforo
//echo "Libero il semaforo\n";
//            if (! sem_release($semaforo) ) {
//                $esito = false;
//                echo "problemi nel liberare il semaforo\n";
//            }
//        }
        return $esito;	
	}

	function applyTemplate($datXml, $commands, $fonts, $delimiterValue = "'#") {

		$debug= array();
		try {
			$inputxml = \simplexml_load_string($datXml); // or die("Error: Cannot create object");
			$ind = 0;

			$dati_da_stampare = "";

			foreach ($inputxml as $element) {
				$ind = $ind + 1;
				foreach ($commands[0] as $key => $value) {
					if ($key == $element->getName()) {
						$singleCommand = $value;
					}
				}
				$fontStyle = "";
				$realSingleCommand = "";
				$realSingleCommand = $singleCommand;
				$attr = $element->attributes();
				if ($attr->count() == 0) {
					$realSingleCommand = $singleCommand;
				} else {
				
					foreach ($attr as $key => $value) {
						$position = strpos($singleCommand, $delimiterValue.$key.$delimiterValue);
						if (strpos($singleCommand, $delimiterValue.$key.$delimiterValue)<0 ) {
						}else{
							if ($key == 'font_style') {
								foreach ($fonts[0] as $k => $v) {
									if ($k == $value) {
										$fontStyle = $v;
									}
								}
								$realSingleCommand = str_replace($delimiterValue.$key.$delimiterValue, $fontStyle, $realSingleCommand);
							} else {
								$realSingleCommand = str_replace($delimiterValue.$key.$delimiterValue, $value, $realSingleCommand);
							}
						}
					}
				}
				$output[] = $realSingleCommand;
				$dati_da_stampare = $dati_da_stampare.$realSingleCommand;
			}

		} catch(Exception $e) {
			// $outputXml = $debug;
			$debug[] = "In getDat ".$e->getCode().' - '.$e->getMessage();
			return $debug;
		}

		$dati_da_stampare = str_replace(array("\n","\r"),"",$dati_da_stampare);
		return $dati_da_stampare;
	}

	public function getLayout_Id() {
		return $this->getLayoutId(); //layout_id;
	}
}
