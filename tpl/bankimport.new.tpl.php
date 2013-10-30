<form method="post" enctype="multipart/form-data" name="bankimport">
	<table class="border" width="100%">
		<tr>
			<td width="200"><?= $langs->trans("BankAccount") ?></td>
			<td><?= $form->select_comptes($import->account->id,'accountid',0,'courant <> 2',1) ?></td>
			<td width="200"><?= $langs->trans("DateStart") ?></td>
			<td><?= $form->select_date($import->dateStart, 'ds') ?></td>
			<td><?= $langs->trans("AccountStatement") ?></td>
			<td><input type="text" name="numreleve" value="<?= $import->numReleve ?>" /></td>
		</tr>
		<tr>
			<td width="200"><?= $langs->trans("BankImportFile") ?></td>
			<td><input type="file" name="bankimportfile" /></td>
			<td width="200"><?= $langs->trans("DateEnd") ?></td>
			<td><?= $form->select_date($import->dateEnd, 'de') ?></td>
			<td><?= $langs->trans("FileHasHeader") ?></td>
			<td><input type="checkbox" name="hasheader" value="1" <?= $import->hasHeader ? ' checked="checked"' : '' ?> /></td>
		</tr>
	</table>
	<br />
	
	<center>
		<input type="submit" class="button" name="compare" value="<?= dol_escape_htmltag($langs->trans("BankCompareTransactions")) ?>">
	</center>
</form>