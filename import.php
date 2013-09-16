<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2013 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$res = 0;
if (! $res && file_exists("../main.inc.php")) {
        $res = @include("../main.inc.php");
}
if (! $res && file_exists("../../main.inc.php")) {
        $res = @include("../../main.inc.php");
}
if (! $res) {
    die("Main include failed");
}

dol_include_once('/bankimport/class/bankimport.class.php');

$mesg = "";
$form = new Form($db);
$tpl = 'tpl/bankimport.new.tpl.php';

$import = new BankImport($db);

$accountId = GETPOST('accountid');

if(GETPOST('compare')) {
	$datestart=dol_mktime(0, 0, 0, GETPOST('dsmonth'), GETPOST('dsday'), GETPOST('dsyear'));
	$dateend=dol_mktime(0, 0, 0, GETPOST('demonth'), GETPOST('deday'), GETPOST('deyear'));
	
	if($import->analyse(GETPOST('accountid','int'), 'bankimportfile', $datestart, $dateend)) {
		$import->load_transactions();
		$tpl = 'tpl/bankimport.check.tpl.php';
	}
} else if(GETPOST('import')) {
	if($import->analyse(GETPOST('accountid','int'), GETPOST('filename','alpha'), GETPOST('datestart','int'), GETPOST('dateend','int'))) {
		// Récupération des actions validées par l'utilisateurs (création écritures et rapprochement)
		$tpl = 'tpl/bankimport.end.tpl.php';
	}
} else {
	$tpl = 'tpl/bankimport.new.tpl.php';
}
// Load translation files required by the page
//$langs->load("accountingexport@accountingexport");

llxHeader('', $langs->trans('TitleBankImport'));

print_fiche_titre($langs->trans("TitleBankImport"));

include($tpl);

llxFooter();
$db->close();