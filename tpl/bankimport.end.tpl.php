/* Copyright (C) 2025 ATM Consulting
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <https://www.gnu.org/licenses/>.
*/
<table class="border" width="100%">
	<tr>
		<td width="200"><?php echo $langs->trans("BankAccount") ?></td>
		<td><?php echo $import->account->getNomUrl(1) ?></td>
		<td width="200"><?php echo $langs->trans("DateStart") ?></td>
		<td><?php echo dol_print_date($import->dateStart, 'day') ?></td>
		<td><?php echo $langs->trans("AccountStatement") ?></td>
		<td><?php echo $import->numReleve ?></td>
	</tr>
	<tr>
		<td width="200"><?php echo $langs->trans("BankImportFile") ?></td>
		<td><?php echo basename($import->file) ?></td>
		<td><?php echo $langs->trans("DateEnd") ?></td>
		<td><?php echo dol_print_date($import->dateEnd, 'day') ?></td>
		<td><?php echo $langs->trans("FileHasHeader") ?></td>
		<td><?php echo $import->hasHeader == 1 ? $langs->trans('Yes') : $langs->trans('No') ?></td>
	</tr>
</table>
<br />
<div class="center">
<a href="<?php echo dol_buildpath('/bankimport/releve.php', 2); ?>?account=<?php echo $import->account->id ?>&amp;num=<?php echo $import->numReleve ?>">
	<?php echo $langs->trans('StatementCreatedAndDataImported', $import->numReleve, $import->nbReconciled, $import->nbCreated) ?>
</a>
</div>
