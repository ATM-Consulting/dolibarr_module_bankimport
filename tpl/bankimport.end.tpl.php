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
		<td><?php echo $langs->trans("HasHeader") ?></td>
		<td><?php echo $import->hasHeader == 1 ? $langs->trans('Yes') : $langs->trans('No') ?></td>
	</tr>
</table>
<br />
<center>
<?php echo $langs->trans('StatementCreatedAndDataImported', $import->numReleve, $import->nbReconciled, $import->nbCreated) ?>
</center>