<?php

$resourceURI = $app->request()->getResourceUri();
//$resourceURI = strtolower($resourceURI);

$__multi = $entry['__multi']['table'];

//DEBUG echo '__multi vale : '.$__multi.'<br>';
//DEBUG echo 'resourceURI vale : '.$resourceURI.'<br>';

$entries = explode('/',$resourceURI);
//DEBUG echo 'entries vale : '.$entries.'<br>';

$firstEntry = $entries[1];
//DEBUG echo 'firstEntry vale : '.$firstEntry.' e second entry vale : '.$entries[1].'<br>';
$__multi_url = '';

if (substr($firstEntry,-1,1) === $__multi_url) {
	$firstEntry = substr($firstEntry,0,strlen($firstEntry)-1);
}
$__entry_url = $firstEntry;
//DEBUG echo 'firstEntry vale : '.$firstEntry.'<br>';

$__entry = $firstEntry;
//DEBUG echo 'firstEntry vale : '.$firstEntry.'<br>';
foreach ($entry as $key => $value) {
//DEBUG echo 'key vale : '.$key.' e value vale '.$value.'<br>';
	if ($key === $firstEntry) {
//DEBUG 		echo "entrato<br>";
		$__entry = $value['table'];
	}
}			
//DEBUG echo '__multi vale : '.$__multi.'<br>';
//DEBUG echo '__entry vale : '.$__entry.'<br>';

$app->post('/provami', function() use ($app, $db) {
	echo "prova<br>";
	$tabella = 'SST_LUNCH_TABLES_SITUATION';
	$aa = $db->$tabella;
	echo "inizio impostazioni where<br>";
	$aa->where('ID',1612);
        $aa->and('OPERATOR_CODE = ? OR OPERATOR_CODE = ?','-NOTHING-','12');

	echo "ciclo sul risultato<br>";
	$ind=0;
	foreach($aa as $aariga){
	$ind=$ind+1;
		echo "riga ".$ind."<br>";
//		print_r($aariga);
	}
	echo "fine<br>";

$valuePassed = $app->request()->post();
echo "passato il valore<br>";
echo $valuePassed['CODE'];
echo ".<br>";

});

require_once('orm.php');
