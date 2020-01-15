<?php


class Device extends NotORM_Result {
	
	private $tableName;
	
	static $commands = "";
	static $type = "";
	
	function __construct(NotORM $notORM, $_id) {
		$table = 'SST_DEVICES';
		parent::__construct($table, $notORM);
		
		$this->table = $table;
		$this->notORM = $notORM;
		//$this->single = $single;
		$this->primary = $notORM->structure->getPrimary($table);
		
		
		$this->tableName = $table;
		$this->where('ID = ?',$_id);
		foreach ($this as $record) {
/* OLD
 			Device::$commands = $record['COMMANDS'];
			if ($record['WEB_SERVICE_URL'] == "") {
				Device::$type = "ESCPos";
			} else {
				Device::$type = "Intelligent";
			}
*/

// versione con il BLOB 			Device::$commands = $record['COMMANDS'];
//echo 'DEVICE_ID'.$record['ID'];
			$comandi = $notORM->SST_DEVICES_COMMANDS;
			$comandi->where('DEVICE_ID = ?',$record['ID']);
			$comandi->order('DETAIL_ORDER');
			Device::$commands = "";
			foreach($comandi as $rowCommand){
				Device::$commands = Device::$commands.$rowCommand['COMMAND_ROW'];
			}
			
			if ($record['PROTOCOL_TYPE'] == "6") {
				Device::$type = "ESCPos";
			} else {
				if ($record['PROTOCOL_TYPE'] == "5") {
					Device::$type = "Intelligent";
				} else {
					Device::$type = "ESCPos";
				}
			}
		}

	}

	function getCommands() {
		return Device::$commands;
	}
	function getType() {
		return Device::$type;
	}
	
	function getCommandsByCode($code, $name="") {
		$this->where('CODE = ?',$code);
		$this->fetch();
		foreach ($this as $record) {
			return $record['COMMANDS'];
		}
	}

	function getTypeByCode($code) {
		$this->where('CODE = ?',$code);
		$this->fetch();
		foreach ($this as $record) {
			if ($record['WEB_SERVICE_URL'] != "") {
				return "Intelligent";
			} else {
				return "ESCPos";
			}
		}
	}
	
	function getDeviceId() {
		foreach ($this as $record) {
			return $record['ID'];
		}
	}

}


class PosDevices extends NotORM_Result {
	
	private $tableName;
	
	function __construct(NotORM $notORM, $_pos_id, $_device_id) {
		$table = 'SST_POS_DEVICES';
		parent::__construct($table, $notORM);
		
		$this->table = $table;
		$this->notORM = $notORM;
		$this->primary = $notORM->structure->getPrimary($table);
		$this->tableName = $table;
		
		$this->where('POS_ID = ?',$_pos_id);
		$this->where('DEVICE_ID = ?',$_device_id);
		
		$this->fetch();
	}

	function getHostPort() {
		foreach ($this as $record) {
			return $record['DEVICE_ADDRESS'];
		}
	}
}

class PosTemplates extends NotORM_Result {
	
	private $tableName;
	
	function __construct(NotORM $notORM, $_id) {
		$table = 'SST_DEVICES';
		parent::__construct($table, $notORM);
		
		$this->table = $table;
		$this->notORM = $notORM;
		$this->primary = $notORM->structure->getPrimary($table);
		
		$this->where('ID = ?',$_id);
		
		$this->tableName = $table;
		$this->fetch();
	}

	function getCommands() {
		foreach ($this as $record) {
			return $record['COMMANDS'];
		}
	}
		
}

/*
 usando le 3 classi, devo riuscire a recuperare dove stampare (tipo stampante e "percorsi") e come elaborare il file (layout e commands)
*/

$app->get('/leggiblob', function () use ($app, $db, $pdo) {

    $app->response()->header("Content-Type", "text/html");

	$curEntity1 = new Device($db,11);

	//echo "Command : ".$curEntity1->getCommands();
});


//$app->get('/'.$__entry_url.$__multi_url.'/report/:repcode(/:conditions+)', function ($repcode, $conditions = []) use ($app, $db) {
$app->get('/'.$__entry_url.$__multi_url.'/report/:repcode(/:conditions+)', function ($repcode, $conditions = []) use ($app, $db, $pdo) {
//$app->get('/'.$__entry_url.$__multi_url.'/report/:repcode/:conditions+', function ($repcode, $conditions) use ($app, $db, $__tablename, $__fields) {



    $app->response()->header("Content-Type", "application/json");
    
	$ind = 1;
	$__where = array();
	$fieldCond = "";

	$parametro = "";
    
        $test[] = array();
        $test[0]['Codice Report'] = $repcode;  // codice del report
        
        $parameters = array();
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
        
        $test[count($test)]['PARAMETRI'] = $parameters;
        
        // dato il codice del report, recupero la query da eseguire
        $reports = $db -> sst_reports;
        $reports -> where("code = ?",$repcode);
        //$testataReport[] = array();
        $test[count($test)]['COUNT REPORTS']=count($reports);
        foreach ($reports as $singoloReport) {
            $ind = 0;
            foreach ($singoloReport as $campo => $valore) {
                //$testataReport[$ind][$campo] = quoted_printable_encode(htmlspecialchars($valore));
                $test[count($test)][$campo] = quoted_printable_encode(htmlspecialchars($valore));
                $ind = $ind + 1;
            }
            //echo "<br/>Fine lettura report ID = ".$repcode."<br/>";

            //$dts = $db->sst_datasources->where("id = ?",$datiReport['DATASOURCE_ID']);
            //echo "-------------------> ".$singoloReport['DATASOURCE_ID']." <--------------------";
        
            $datiReport[] = array();
            $dts = $db->sst_datasources;
            $dts->where("ID = ?",$singoloReport['DATASOURCE_ID']);
            $queryDaEseguire = array();
$test[count($test)]['COUNT DTS']=count($dts);
            foreach ($dts as $singleDts) {
                //echo "-------------------> ".$singleDts['ID']." <--------------------";
                $dtsDetails = $db->sst_datasource_details;
                $dtsDetails->where("DATASOURCE_ID = ?",$singleDts['ID']);
                $dtsDetails->order("ORDER_NUM");
$test[count($test)]['COUNT DTSDETAILS']=count($dtsDetails);
                foreach ($dtsDetails as $singleDetails) {
                    $sqlStatements = $db->sst_sql_statements;
                    $sqlStatements->where("ID = ?",$singleDetails['STATEMENT_ID']);
                    foreach ($sqlStatements as $statement ) {
                        $queryDaEseguire[]['SQL']= quoted_printable_encode(htmlspecialchars($statement['SQL_STATEMENT']));
                        $queryDaEseguire[count($queryDaEseguire)-1]['Ordine']= quoted_printable_encode(htmlspecialchars($singleDetails['ORDER_NUM']));
                        $queryDaEseguire[count($queryDaEseguire)-1]['ID']= quoted_printable_encode(htmlspecialchars($singleDetails['ID']));
                    }
                    // Have you some childs ?
                    $dtsChilds = $db->sst_datasource_details;
                    $dtsChilds->where("PARENT_DETAIL_ID = ?", $singleDetails['ID']);
                    $dtsChilds->order("ORDER_NUM");
                    $ind = 0;
                    foreach ($dtsChilds as $dtsChild) {
                        if ($ind == 0) {
                            $queryDaEseguire[count($queryDaEseguire)-1]['CHILDS']=$dtsChild['ID'];
                        } else {
                            $queryDaEseguire[count($queryDaEseguire)-1]['CHILDS']=$queryDaEseguire[count($queryDaEseguire)-1]['CHILDS'].", ".$dtsChild['ID'];
                        }
                        $ind = $ind + 1;
                    }
                    if ($ind == 0 ){
                        $queryDaEseguire[count($queryDaEseguire)-1]['CHILDS']="";                    
                        // no child, read all records
                        $sqlStatements = $db->sst_sql_statements;
                        $sqlStatements->where("ID = ?",$singleDetails['STATEMENT_ID']);
                        foreach ($sqlStatements as $statement ) {
                            $queryDaEseguire[]['SQL']= quoted_printable_encode(htmlspecialchars($statement['SQL_STATEMENT']));
                            $queryDaEseguire[count($queryDaEseguire)-1]['Ordine']= quoted_printable_encode(htmlspecialchars($singleDetails['ORDER_NUM']));
                            $queryDaEseguire[count($queryDaEseguire)-1]['ID']= quoted_printable_encode(htmlspecialchars($singleDetails['ID']));

                            /* 
                            
                            // esempio di esecuzione di una query generica (RAW)
                            
                            $indice = 0;
                            //$zio_pino= "";
                            $zio_pino[] = array();
                            foreach ($pdo->query("select * from sst_reports") as $stampa) {
                                // $queryDaEseguire[]['report_code'] = $stampa['CODE'].' '.$stampa['DESCRIPTION'];
                                $zio_pino[$indice]= $stampa['CODE'].' '.$stampa['DESCRIPTION'];
                                $indice = $indice + 1;
                                //$zio_pino= $zio_pino.'-'.$stampa['CODE'].' '.$stampa['DESCRIPTION'];
                            }
                            $queryDaEseguire[]['elenco reports'] = $zio_pino;
                            */
                        }
                            
                    }
                }
            }
            $datiReport[$ind]['dettaglio'] = $queryDaEseguire;
        }

        $test[count($test)]['righe'] = $datiReport;
        
        echo json_encode($test);
        //echo json_encode($zio_pino);
        //print_r($test);
        
});

$app->get('/'.$__entry_url.$__multi_url.'/db_to_xml', function () use ($app, $db, $__tablename, $__fields) {
    $singleTables = array();

    //echo "<br>INIZIO<br>";
    $xml = new SimpleXMLElement('<table/>');

    foreach ($db->$__tablename as $singleTable) {
        //echo "<br>  INIZIO FOREACH<br>";
        $riga = $xml->addChild('record');
	$singleTables[] = array();
	foreach ($__fields as $key => $value) {
            //echo "<br>     INIZIO FIELDS<br>";
            $riga->addChild($key,quoted_printable_encode(htmlspecialchars($singleTable[$value])));
			$singleTables[count($singleTables)-1][quoted_printable_encode(htmlspecialchars($singleTable[$value]))] = $key;
			//echo quoted_printable_encode(htmlspecialchars($singleTable[$value])) " = " $key;
            //echo "<br>     FINE FIELDS<br>";
        }
        //echo "<br>  FINE FOREACH<br>";
    }
    //echo "<br>FINE<br>";
    //$app->response()->header("Content-Type", "application/json");
    // echo json_encode($singleTables);
    
    //echo $singleTables;   
    
    //TEST    $xml = new SimpleXMLElement('<root/>');
    //TEST    array_walk_recursive($singleTables, array ($xml, 'addChild'));
    // print $xml->asXML();
  //  echo $xml->asXML();
});
