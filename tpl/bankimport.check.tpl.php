<table class="border" width="100%">
	<tr>
		<td width="200"><?= $langs->trans("BankAccount") ?></td>
		<td><?= $import->account->getNomUrl(1) ?></td>
		<td width="200"><?= $langs->trans("DateStart") ?></td>
		<td><?= dol_print_date($import->dateStart, 'day') ?></td>
	</tr>
	<tr>
		<td><?= $langs->trans("BankImportFile") ?></td>
		<td><?= basename($import->file) ?></td>
		<td width="200"><?= $langs->trans("DateEnd") ?></td>
		<td><?= dol_print_date($import->dateEnd, 'day') ?></td>
	</tr>
</table>

<form method="post" enctype="multipart/form-data" name="bankimport">
	<input type="hidden" name="accountid" value="<?= $import->account->id ?>" />
	<input type="hidden" name="filename" value="<?= $import->file ?>" />
	<input type="hidden" name="datestart" value="<?= $import->dateStart ?>" />
	<input type="hidden" name="dateend" value="<?= $import->dateEnd ?>" />
	
	<center>
		<input type="submit" class="button" name="import" value="<?= dol_escape_htmltag($langs->trans("BankImport")) ?>">
	</center>
</form>