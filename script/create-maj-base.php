<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}


dol_include_once('/bankimport/class/bankimport.class.php');

global $db;

$o=new BankImportDet($db);
$o->init_db_by_vars();


// Deprecate
$PDOdb=new TPDOdb;
$o=new TBankImportHistory;
$o->init_db_by_vars($PDOdb);
