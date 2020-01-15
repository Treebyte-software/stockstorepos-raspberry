<?php

$__tablename = $__entry.$__multi;

	try {
		$__fields = "";
		$__unk = "";
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename) {
				//$__fields = $tabelle[$__tablename];
				
				foreach ($tabelle[$__tablename] as $synonym => $name) {
					$__fields[$synonym] = $name['name'];
					if ($name['unk'] == 'YES') {
						$__unk[$synonym] = $name['name'];
					}
				}
				
			}
		}
		// $__fields = $tabelle[$__tablename];
	} catch(Exception $e) {
		writeLog("initialized","errore",$app);
		$__fields = "";
		$__unk = "";
	}

//require_once "logs.php";
require_once "reporter.php";
require_once "order.php";
require_once "test.php";
require_once "new_order.php";
require_once "poslive/poslivesync.php";
require_once "servizio/lunch_services.php";

$app->get('/now', function () use ($app, $db, $entry, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");

    $adesso = getCurrentTimeStamp($db);

    echo json_encode(array("for_last_value_date"=>$adesso));
});

$app->get('/listentity', function () use ($app, $db, $entry, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
    
    $singleTables[] = array();
    foreach ($entry as $key => $value) {
		$singleTables[count($singleTables)-1][$key] = $value['table'];
    }
    echo json_encode($singleTables);
});

$app->get('/listentitysync', function () use ($app, $db, $entry, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
    
    $singleTables[] = array();
    foreach ($entry as $key => $value) {
		if ($value['for_sync']=='YES') {
			//$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($value);
			$singleTables[count($singleTables)-1][$key] = $value['table'];
		}
    }
    echo json_encode($singleTables);
});

$app->get('/lasterrors(/:operator_id)', function ($operator_id = 0) use ($app, $db, $entry, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
    
    $esito = true;
    $msgError = "";
    try {
       $errori = $db->SST_WS_ERRORS;
       $errori->where('SHOW',0);
       if ($operator_id <> 0) {
           $errori->and('OPERATOR_ID',$operator_id);
       }
       if (count($errori) > 0) {
           foreach($errori as $errore){
               $msgError = $msgError.$errore['DESCRIPTION'].chr(10);
               $errori->update(['SHOW' => 1]);
           }
       }
    } catch(Exception $e) {
       $esito = false;
    }
    echo json_encode(array("status" => $esito, "message" => $msgError));
});

$app->get('/'.$__entry_url.$__multi_url.'/maxvalue/:max_value/lastvalue/:conditions+', function ($max_value, $conditions) use ($app, $db, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
	$ind = 1;
	$__where = array();
	$fieldCond = "";
	
	$curEntity = $db->$__tablename;
	foreach ($conditions as $condition) {
		if ($ind == 1) {
			$__where[] = array();
			$fieldCond = $condition;
			$ind = 0;
		} else {
			$__where[count($__where)-1][$fieldCond] = quoted_printable_encode($condition);
			$curEntity = $curEntity->where($__fields[$fieldCond]." > ?", $condition);
			$curEntity = $curEntity->and($__fields[$fieldCond]." < ?", $max_value);
			$curEntity = $curEntity->order($__fields[$fieldCond]);
			$ind = 1;
		}
	}


	$singleTables = array();
	foreach ($curEntity as $singleTable) {
		$singleTables[] = array();
		foreach ($__fields as $key => $value) {
				$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($singleTable[$value]);
	        }
	}
    echo json_encode($singleTables);
});


$app->get('/'.$__entry_url.$__multi_url.'/lastvalue/:conditions+', function ($conditions) use ($app, $db, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
	$ind = 1;
	$__where = array();
	$fieldCond = "";
	
	$curEntity = $db->$__tablename;
	foreach ($conditions as $condition) {
		if ($ind == 1) {
			$__where[] = array();
			$fieldCond = $condition;
			$ind = 0;
		} else {
			$__where[count($__where)-1][$fieldCond] = quoted_printable_encode($condition);
			$curEntity = $curEntity->where($__fields[$fieldCond]." > ?", $condition);
			$curEntity = $curEntity->order($__fields[$fieldCond]);
			$ind = 1;
		}
	}

	$singleTables = array();
	foreach ($curEntity as $singleTable) {
		$singleTables[] = array();
		foreach ($__fields as $key => $value) {
				$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($singleTable[$value]);
        }
    }
    echo json_encode($singleTables);
});


$app->get('/'.$__entry_url.$__multi_url.'/describe', function () use ($app, $db, $__tablename, $__fields, $tabelle) {
    $app->response()->header("Content-Type", "application/json");
    
    $singleTables[] = array();
//    foreach ($__fields as $key => $value) {
	foreach ($tabelle[$__tablename] as $key => $value) {
		//$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($value);
		$singleTables[count($singleTables)-1][$key] = $value;
    }
	writeLog("get/describe","describe di ".$__tablename,$app);
    echo json_encode($singleTables);
});


$app->get('/'.$__entry_url.$__multi_url, function () use ($app, $db, $__tablename, $__fields) {
    $singleTables = array();

    writelog("get","Get all ".$__tablename,$app);
    foreach ($db->$__tablename as $singleTable) {
		$singleTables[] = array();
		foreach ($__fields as $key => $value) {
				//$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($singleTable[$value]);
				$singleTables[count($singleTables)-1][$key] = utf8_encode($singleTable[$value]);
        }
    }
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($singleTables);
});


$app->get('/'.$__entry_url.'/:id', function ($id) use ($app, $db, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
    
    writelog("get/id","Get ".$__tablename." by id : ".$id,$app);
    $curEntity = $db->$__tablename->where($__fields['id'], $id);
    if ($data = $curEntity->fetch()) {
        $singleTables[] = array();
		foreach ($__fields as $key => $value) {
				//$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($data[$value]);
				//$singleTables[count($singleTables)-1][$key."_UTF"] = quoted_printable_encode(htmlspecialchars($data[$value], ENT_NOQUOTES, 'WINDOWS-1252',true));
				//$singleTables[count($singleTables)-1][$key] = quoted_printable_encode(htmlentities($data[$value], ENT_NOQUOTES, 'WINDOWS-1252'));
				$singleTables[count($singleTables)-1][$key] = utf8_encode($data[$value]);
        }
        echo json_encode($singleTables);
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "ID $id does not exist"));
    }
});

$app->get('/'.$__entry_url.$__multi_url.'/:conditions+', function ($conditions) use ($app, $db, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
	$ind = 1;
	$__where = array();
	$fieldCond = "";
	
	$curEntity = $db->$__tablename;
	$strConditions = "";

	foreach ($conditions as $condition) {
		$strConditions = $strConditions.$condition." ";
		if ($ind == 1) {
			$__where[] = array();
			$fieldCond = $condition;
			$ind = 0;
		} else {
			$__where[count($__where)-1][$fieldCond] = quoted_printable_encode($condition);
			$curEntity = $curEntity->where($__fields[$fieldCond], $condition);
			$ind = 1;
		}
	}

        writelog("get/conditions","Get ".$__tablename." by multi conditions: ".$strConditions,$app);
	$singleTables = array();
	foreach ($curEntity as $singleTable) {
		$singleTables[] = array();
		foreach ($__fields as $key => $value) {
				//$singleTables[count($singleTables)-1][$key] = quoted_printable_encode($singleTable[$value]);
				$singleTables[count($singleTables)-1][$key] = utf8_encode($singleTable[$value]);
        }
    }
    echo json_encode($singleTables);

});


$app->post('/'.$__entry_url, function () use($app, $db, $__tablename, $__fields, $__unk) {
    $app->response()->header("Content-Type", "application/json");

        writelog("post","Post ".$__tablename,$app);
       
        if ($__tablename == 'SSV_ORDER_HEADINGS') {
		$__tablename = 'SST_SALE_HEADINGS';
                writelog("post","---> ".$__tablename,$app);
	}
	if ($__tablename == 'SSV_ORDER_DETAILS') {
		$__tablename = 'SST_SALE_DETAILS';
                writelog("post","---> ".$__tablename,$app);
	}
	if ($__tablename == 'SSV_ORDER_DETAILS_NOTES') {
		$__tablename = 'SST_SALE_DETAILS_NOTES';
                writelog("post","---> ".$__tablename,$app);
	}        
        
	$status = "";
	$newId = -1;
	try {

		// $newId = -1;
		$valuePassed = $app->request()->post();
		$curEntity = array();
		foreach ($valuePassed as $key => $value) {
			$curEntity[$__fields[$key]] = $value;
		}
		$newId = getNewId($db, $__tablename);
		//$curEntity[$__fields['id']] = $newId;
		$curEntity['ID'] = $newId;
		$db->$__tablename->insert($curEntity);
		$key = "";
		foreach ($__unk as $syn => $field) {
			$key = $key.$field." ".$curEntity[$field]." ";
		}
		writeLog("post"," => Entity [".$key."] inserted successfully",$app);
                $status = "Insert ok";
		
	} catch(Exception $e) {
		// $newId = $newId;
		if ($e->getCode() != 2) {
			$status = $e->getCode().' - '.$e->getMessage();
			writeLog("post","Error on ".$__tablename." [".$key."] : ".$status,$app);
		} else {
        		$status = "Insert ok !";
	        }
	}
	echo json_encode(array("id" => $newId,"status" => $status));
});

$app->put('/'.$__entry_url.'/:id', function ($id) use ($app, $db, $__tablename, $__fields, $__unk ) {
        $app->response()->header("Content-Type", "application/json");
        writelog("put","Put ".$__tablename." with id ".$id,$app);
        if ($__tablename == 'SSV_ORDER_HEADINGS') {
		$__tablename = 'SST_SALE_HEADINGS';
                writelog("post","---> ".$__tablename,$app);
	}
	if ($__tablename == 'SSV_ORDER_DETAILS') {
		$__tablename = 'SST_SALE_DETAILS';
                writelog("post","---> ".$__tablename,$app);
	}
	if ($__tablename == 'SSV_ORDER_DETAILS_NOTES') {
		$__tablename = 'SST_SALE_DETAILS_NOTES';
                writelog("post","---> ".$__tablename,$app);
	}        
        
	$valuePassed = $app->request()->post();

        $curEntity = $db->$__tablename;
        $curEntity->where("id", $id);
/*
	if ($__tablename == 'SST_LUNCH_TABLES_SITUATION') {
		$op_code = "-NOTHING-";
		foreach ($valuePassed as $key => $value) {
			if (strtoupper($key) == 'OPERATOR_CODE') {
				$op_code = $value;
			}
		}
		if ($op_code != '-NOTHING-') {
			$curEntity->and('OPERATOR_CODE = ? OR OPERATOR_CODE = ?','-NOTHING-', $op_code);
		}
	}
//	$op_code = $valuePassed['OPERATOR_CODE'];
*/
	try {
                $curEntity->fetch();
		writeLog("put"," Info put ".$__tablename." with id ".$id,$app);
			
		//$valuePassed = $app->request()->post();
		$dataRead = array();
		foreach ($valuePassed as $key => $value) {
			$dataRead[$__fields[$key]] = $value;
		}
		$result = $curEntity->update($dataRead);
$key = "";
foreach ($__unk as $syn => $field) {
	$key = $key.$field." ".$dataRead[$field]." ";
}
		writeLog("put","Result update : ".$result,$app);
		writeLog("put"," => Entity with id $id [".$key."] updated successfully",$app);
		echo json_encode(array(
			"status" => (bool)$result,
			"message" => "Entity with id $id [".$key."] updated successfully"
			));

	} catch(Exception $e) {
		// $newId = $newId;
		$status = $e->getCode().' - '.$e->getMessage();
		writeLog("put","Error on ".$__tablename." with id ".$id." : ".$status,$app);
                echo json_encode(array("status" => false,"message" => "Error on ".$__tablename." with id ".$id." : ".$status));
	}			
			
});


$app->put('/'.$__entry_url.'/:conditions+', function ($conditions) use ($app, $db, $__tablename, $__fields) {
	
        $app->response()->header("Content-Type", "application/json");
        writelog("put","Put ".$__tablename." with multi conditions",$app);
        if ($__tablename == 'SSV_ORDER_HEADINGS') {
		$__tablename = 'SST_SALE_HEADINGS';
                writelog("post","---> ".$__tablename,$app);
	}
	if ($__tablename == 'SSV_ORDER_DETAILS') {
		$__tablename = 'SST_SALE_DETAILS';
                writelog("post","---> ".$__tablename,$app);
	}
	if ($__tablename == 'SSV_ORDER_DETAILS_NOTES') {
		$__tablename = 'SST_SALE_DETAILS_NOTES';
                writelog("post","---> ".$__tablename,$app);
	}        
	
	$ind = 1;
	$__where = array();
	$fieldCond = "";
	
	$curEntity = $db->$__tablename;

	foreach ($conditions as $condition) {
		if ($ind == 1) {
			$__where[] = array();
			$fieldCond = $condition;
			$ind = 0;
		} else {
			$__where[count($__where)-1][$fieldCond] = quoted_printable_encode($condition);
			$curEntity = $curEntity->where($__fields[$fieldCond], $condition);
			$ind = 1;
		}
	}
	
	try {
//                if ($curEntity->fetch()) {
                        $curEntity->fetch();
                        //$dataRead = $app->request()->put();
                        $valuePassed = $app->request()->post();
                        $dataRead = array();
                        foreach ($valuePassed as $key => $value) {
                                $dataRead[$__fields[$key]] = $value;
                        }
                        $result = $curEntity->update($dataRead);
			writeLog("put","Result update : ".$result,$app);
                        echo json_encode(array(
                                "status" => (bool)$result,
                                "message" => "Entity updated successfully"
                        ));
//                } else{
//                        echo json_encode(array("status" => false, "message" => "Entity does not exist"));
//                }
        
        } catch(Exception $e) {
                // $newId = $newId;
                $status = $e->getCode().' - '.$e->getMessage();
                writeLog("put","Error on ".$__tablename." with id ".$id." : ".$status,$app);
                echo json_encode(array("status" => false, "message" => "Error on ".$__tablename." with id ".$id." : ".$status));
        }			
        
        
});


$app->delete('/'.$__entry_url.'/:id', function ($id) use($app, $db, $__tablename, $__fields) {
    $app->response()->header("Content-Type", "application/json");
    $curEntity = $db->$__tablename->where("id", $id);
    if ($curEntity->fetch()) {
        $result = $curEntity->delete();
        echo json_encode(array(
            "status" => true,
            "message" => "Entity with id $id deleted successfully"
        ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "Entity with id $id does not exist"
        ));
    }
});


$app->delete('/'.$__entry_url.'/:conditions+', function ($conditions) use ($app, $db, $__tablename, $__fields) {
	
    $app->response()->header("Content-Type", "application/json");
	
	$ind = 1;
	$__where = array();
	$fieldCond = "";
	
	$curEntity = $db->$__tablename;

	foreach ($conditions as $condition) {
		if ($ind == 1) {
			$__where[] = array();
			$fieldCond = $condition;
			$ind = 0;
		} else {
			$__where[count($__where)-1][$fieldCond] = quoted_printable_encode($condition);
			$curEntity = $curEntity->where($__fields[$fieldCond], $condition);
			$ind = 1;
		}
	}
		
    if ($curEntity->fetch()) {
        $result = $curEntity->delete();
        echo json_encode(array(
            "status" => true,
            "message" => "Entity deleted successfully"
        ));
    }	
    else{
        echo json_encode(array(
            "status" => false,
			"message" => "Entity does not exist"
        ));
    }
});

