<?php

$app->get('/orderbytable/:table_id', function ($table_id) use ($app, $db, $__tablename, $__fields, $tabelle, $pdo) {
	$app->response()->header("Content-Type", "application/json");

	$__tablename_lunch = "SSV_LUNCH_TABLES_SITUATION_HALL";
	try {
		$__campi_lunch = array();
		foreach ($tabelle as $synonym => $name) {
			if ($synonym == $__tablename_lunch) {
				//$__fields = $tabelle[$__tablename];
				
				foreach ($tabelle[$__tablename_lunch] as $synonym => $name) {
					$__campi_lunch[$synonym] = $name['name'];
				}
				
			}
		}
	} catch(Exception $e) {
		$esito = $esito."EXCEPT_FIELDS LUNCH TABLE - ";
	}

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

	$esito = true;
	try {
	
	       	$lunch_table = array();
	       	$headings = array();
	       	$details = array();
	       	$notes = array();
		$curEntity = $db->$__tablename_lunch->where('LUNCH_TABLE_ID', $table_id);
		//if ($dataLunch = $curEntity->fetch()) {
		if ($curEntity) {
			foreach ($curEntity as $dataLunch) {
				foreach ($__campi_lunch as $key => $value) {
					$lunch_table[$key] = $dataLunch[$value];
			        }
				$curEntity_head = $db->$__tablename_head->where('LUNCH_TABLE_ID', $table_id)->and('ORDINATION_CLOSED',0);
				if (count($curEntity_head) != 0) {
					//if ($dataHead = $curEntity_head->fetch()) {
					foreach ($curEntity_head as $dataHead) {
						foreach ($__campi_head as $key => $value) {
							$headings[$key] = $dataHead[$value];
					        }
						$curEntity_dett = $db->$__tablename_dett->where('HEADING_ID',$headings['id']);
						if (count($curEntity_dett) != 0) {
							//if ($dataDett = $curEntity_dett->fetch()) {
							foreach ($curEntity_dett as $dataDett) {
								foreach ($__campi_dett as $key => $value) {
									$details[$key] = $dataDett[$value];
						        	}
								$headings['DETAILS'][]=$details;
							}
						}
						$curEntity_notes = $db->$__tablename_notes->where('HEADING_ID',$headings['id']);
						if (count($curEntity_notes) != 0) {
							//if ($dataNotes = $curEntity_notes->fetch()) {
							foreach ($curEntity_notes as $dataNotes) {
								foreach ($__campi_notes as $key => $value) {
									$notes[$key] = $dataNotes[$value];
						        	}
								$headings['NOTES'][]=$notes;
							}
						}
						$lunch_table['HEADING'][]=$headings;
					}
				} else {
					$lunch_table['HEADING']=false;
				}
			}
		}

	} catch(Exception $e) {
		$esito = false;
		$status = $e->getCode().' - '.$e->getMessage();
		writeLog("post","Error on ".$__entry_url." [".$key."] : ".$status,$app);
	}

	if ($esito) {
		echo json_encode(array("LUNCH_TABLE_SITUATION"=>$lunch_table));
	} else {
		echo json_encode(array("Status"=>false,"Message"=>$status));
	}

});


