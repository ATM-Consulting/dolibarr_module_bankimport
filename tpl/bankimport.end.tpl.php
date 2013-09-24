<table class="border" width="100%">
	<tr>
		<td width="200"><?= $langs->trans("BankAccount") ?></td>
		<td><?= $import->account->getNomUrl(1) ?></td>
		<td width="200"><?= $langs->trans("DateStart") ?></td>
		<td><?= dol_print_date($import->dateStart, 'day') ?></td>
		<td width="200"><?= $langs->trans("AccountStatement") ?></td>
		<td><?= $import->numReleve ?></td>
	</tr>
	<tr>
		<td><?= $langs->trans("BankImportFile") ?></td>
		<td><?= basename($import->file) ?></td>
		<td width="200"><?= $langs->trans("DateEnd") ?></td>
		<td><?= dol_print_date($import->dateEnd, 'day') ?></td>
	</tr>
</table>
<br />
<center>
<?= $langs->trans('StatementCreatedAndDataImported', $import->numReleve, $import->nbReconciled, $import->nbCreated) ?>
</center>