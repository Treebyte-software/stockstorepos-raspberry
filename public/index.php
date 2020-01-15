<?php
require_once "../vendor/autoload.php";
require_once "../common/db.php";
require_once "../app/logs.php";

$pdo = new PDO($dsn, $username, $password);

$structure = new NotORM_Structure_Convention(
    $primary = 'id',
    $foreign = 'id',
    $table = '%s',
    $prefix = ''
);
/*
$day = date('d');
$month = date('m');
$year = date('Y');
$folder = 'iaprofiles/temp/'.$year.'/'.$month.'/'.$day.'/';
	
if (!file_exists($folder)) {
	mkdir($folder, 0777, true);
        if (!file_exists($folder)) {
        	$folder = 'iaprofiles/temp/';
        }
}
ini_set("error_log", $folder."/error_log.log");
*/
ini_set("error_log", getFolderLog()."/stockstorepos_php.log");
error_reporting(E_ALL);

//$db = new NotORM($pdo, $structure);
$db = new NotORM($pdo);

$app = new Slim(array(
    "MODE" => "development",
    "TEMPLATES.PATH" => "./templates",
	"LOGS" => "S",
	"INTELLIGENT_TYPE" => "S"
));

$app->get("/", function() {
    //echo "<h1>Vai al sito <a href='http://stockstorepos.com'>stockstorepos.com</a></h1>";
    //echo "<h1>esegui test <a href='example_2_xml.php'>xml</a></h1>";
echo "<ul>";
echo " <li>";
echo "  <a href='showlogs.php'>Visualizza cartella dei logs</a>";
echo " </li>";
echo " <li>";
echo "  <a href='testPrinter'>Ping sulle stampanti delle comande</a>";
echo " </li>";
echo "</ul>";
	exit;
});

require_once "../app/routes.php";

//require_once "../app/orm.php";

$app->run();
