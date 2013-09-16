<form method="post" enctype="multipart/form-data" name="bankimport">
	<table class="border" width="100%">
		<tr>
			<td width="200"><?= $langs->trans("BankAccount") ?></td>
			<td><?= $form->select_comptes($accountId,'accountid',0,'courant <> 2',1) ?></td>
			<td width="200"><?= $langs->trans("DateStart") ?></td>
			<td><?= $form->select_date($import->dateStart, 'ds') ?></td>
		</tr>
		<tr>
			<td><?= $langs->trans("BankImportFile") ?></td>
			<td><input type="file" name="bankimportfile" /></td>
			<td width="200"><?= $langs->trans("DateEnd") ?></td>
			<td><?= $form->select_date($import->dateEnd, 'de') ?></td>
		</tr>
	</table>
	<center>
		<input type="submit" class="button" name="compare" value="<?= dol_escape_htmltag($langs->trans("BankImport")) ?>">
	</center>
</form>