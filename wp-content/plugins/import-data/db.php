<?php 
$db_typo3 = @mysql_connect('localhost', 'stefan_main', 'm~Nul266', true) or die("Database error");
@mysql_select_db('stefan_typo3', $db_typo3);
@mysql_query("set names 'utf8'", $db_typo3);

$db_wp = mysql_connect('localhost', 'stefan_main', 'm~Nul266', true) or die("Database error");
mysql_select_db('stefan_main', $db_wp);
mysql_query("set names 'utf8'", $db_wp);