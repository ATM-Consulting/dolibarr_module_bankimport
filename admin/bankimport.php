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

/**
 * 	\file		admin/bankimport.php
 * 	\ingroup	bankimport
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include "../../main.inc.php"; // From htdocs directory
if (! $res) {
    $res = @include "../../../main.inc.php"; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/bankimport.lib.php';
//require_once "../class/myclass.class.php";

global $bc, $conf, $db, $langs, $user;

// Translations
$langs->load("bankimport@bankimport");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg)) {
	$code = $reg[1];
	if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0) {
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	} else {
		dol_print_error($db);
	}
}
	
if (preg_match('/del_(.*)/',$action,$reg)) {
	$code = $reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0) {
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	} else {
		dol_print_error($db);
	}
}

/*
 * View
 */
llxHeader('', $langs->trans("BankImportSetupPage"));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans("BankImportSetupPage"), $linkback, 'bankimport@bankimport');


$form = new Form($db);
$var = true;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans("Parameters") . '</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">' . $langs->trans("Value") . '</td>';

// Separator
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>';
print $form->textwithpicto(
	'<label for="BANKIMPORT_SEPARATOR">' . $langs->trans("BankImportSeparator") . '</label>',
	$langs->trans("BankImportSeparatorHelp")
);
print '</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="500">';
print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="set_BANKIMPORT_SEPARATOR">';
print '<input type="text" id="BANKIMPORT_SEPARATOR" name="BANKIMPORT_SEPARATOR" value="' . $conf->global->BANKIMPORT_SEPARATOR . '" size="10" />';
print '&nbsp;<input type="submit" class="button" value="' . $langs->trans("Modify") . '">';
print '</form>';
print '</td></tr>';

// Mapping
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>';
print $form->textwithpicto(
	'<label for="BANKIMPORT_SEPARATOR">' . $langs->trans("BankImportMapping") . '</label>',
	$langs->trans("BankImportMappingHelp")
);
print '</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="500">';
print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="set_BANKIMPORT_MAPPING">';
print '<input type="text" id="BANKIMPORT_MAPPING" name="BANKIMPORT_MAPPING" value="' . $conf->global->BANKIMPORT_MAPPING . '" size="50" />';
print '&nbsp;<input type="submit" class="button" value="' . $langs->trans("Modify") . '">';
print '</form>';
print '</td></tr>';

// Date format
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>';
print $form->textwithpicto(
	'<label for="BANKIMPORT_DATE_FORMAT">' . $langs->trans("BankImportDateFormat") . '</label>',
	$langs->trans("BankImportDateFormatHelp")
);
print '</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="500">';
print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="set_BANKIMPORT_DATE_FORMAT">';
print '<input type="text" id="BANKIMPORT_DATE_FORMAT" name="BANKIMPORT_DATE_FORMAT" value="' . $conf->global->BANKIMPORT_DATE_FORMAT . '" size="10" />';
print '&nbsp;<input type="submit" class="button" value="' . $langs->trans("Modify") . '">';
print '</form>';
print '</td></tr>';

// File header
$var = !$var;
print '<tr ' . $bc[$var] . '>';
print '<td>';
print '<label for="BANKIMPORT_HEADER">' . $langs->trans("FileHasHeader") . '</label>';
print '</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="right" width="500">';
print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="set_BANKIMPORT_HEADER">';
print '<input type="checkbox" id="BANKIMPORT_HEADER" name="BANKIMPORT_HEADER" value="1"' . ($conf->global->BANKIMPORT_HEADER ? ' checked = "checked"' : '') . '>';
print '&nbsp;<input type="submit" class="button" value="' . $langs->trans("Modify") . '">';
print '</form>';
print '</td></tr>';

print '</table>';

llxFooter();

$db->close();
