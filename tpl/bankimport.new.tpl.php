<form method="post" enctype="multipart/form-data" name="bankimport">
	<table class="border" width="100%">
		<tr>
			<td width="200"><?php echo $langs->trans("BankAccount") ?></td>
			<td><?php echo $form->select_comptes( ($import->account) ? $import->account->id : -1,'accountid',0,'courant <> 2',1) ?></td>
			<td width="200"><?php echo $langs->trans("DateStart") ?></td>
			<td><?php echo $form->select_date($import->dateStart, 'ds') ?></td>
			<td><?php echo $langs->trans("AccountStatement") ?></td>
			<td><input type="text" name="numreleve" value="<?php echo $import->numReleve ?>" /></td>
		</tr>
		<tr>
			<td width="200"><?php echo $langs->trans("BankImportFile") ?></td>
			<td><input type="file" name="bankimportfile" /></td>
			<td width="200"><?php echo $langs->trans("DateEnd") ?></td>
			<td><?php echo $form->select_date($import->dateEnd, 'de') ?></td>
			<td><?php echo $langs->trans("FileHasHeader") ?></td>
			<td><input type="checkbox" name="hasheader" value="1" <?php echo $import->hasHeader ? ' checked="checked"' : '' ?> /></td>
		</tr>

        <tr>
            <td width="200"><?php echo $form->textwithpicto($langs->trans("BankImportMapping"), $langs->trans("BankImportMappingHelp")) ?></td>
            <td><input type="text" name="bankimportmapping" value="<?php echo $conf->global->BANKIMPORT_MAPPING; ?>" /></td>
            <td><?php echo $form->textwithpicto($langs->trans("BankImportDateFormat"), $langs->trans("BankImportDateFormatHelp")); ?></td>
            <td><input type="text" name="bankimportdateformat" value="<?php echo $conf->global->BANKIMPORT_DATE_FORMAT; ?>" size="12" /></td>
            <td><?php echo $form->textwithpicto($langs->trans("BankImportSeparator"), $langs->trans("BankImportSeparatorHelp")); ?></td>
            <td><input type="text" name="bankimportseparator" value="<?php echo $conf->global->BANKIMPORT_SEPARATOR; ?>" size="3" /></td>
            
        </tr>
	</table>
	<br />
	
	<center>
		<input type="submit" class="button" name="compare" value="<?php echo dol_escape_htmltag($langs->trans("BankCompareTransactions")) ?>">
	</center>
</form>