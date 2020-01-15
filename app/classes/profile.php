<?php

class Profile {

	private $_fileName = '';
	private $_rootPath = '/iaprofiles';
	private $_pk = '';

	function __construct($_profileName){
		$_fileName = $_rootPath.str_replace('\\', '/',$_profileName);
		if (file_exists($_fileName)) {
			// carica l'xml del file
			//  e recupera 
			$_pk = 'ID';			
		} else {
			$_pk = '-NOTHING-';			
		}
	}

	function getPk() {
		return $_pk;
	}

	function getUnk() {
	}
	
}

?>
