<?php
// Terribly sorry for this brutish PHP
// We needed a place to put PHP files that'd fetch info for client-side use
// require('/home/rokabe/acrossti.me/private.php');
require('../../private.php');

try {
	$host = $private['SQL_HOST'];
	$dbname = $private['SQL_DB'];
	$user = $private['SQL_USER'];
	$pass = $private['SQL_PASS'];
	
	# MySQL with PDO_MYSQL
	$DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
	$DBH->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}
catch(PDOException $e) {
    echo $e->getMessage();
}


$STH = $DBH->prepare('SELECT aid, title, description FROM at_timeline');
$STH->setFetchMode(PDO::FETCH_ASSOC);
$STH->execute();
$Result = $STH->fetchAll();

$json = json_encode( $Result );
echo $json;

# close the connection
$DBH = null;
unset($private);
?>