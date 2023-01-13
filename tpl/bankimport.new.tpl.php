<form method="post" enctype="multipart/form-data" name="bankimport">
	<input type="hidden" name="token" value=" <?php echo function_exists('newToken') ? newToken() : $_SESSION['newtoken'];?> ">
	<table class="border" width="100%">
		<tr>
			<td width="200"><label class="fieldrequired" for="selectaccountid"><?php echo $langs->trans("BankAccount") ?></label></td>
			<td><?php echo $form->select_comptes( ($import->account) ? $import->account->id : -1,'accountid',0,'courant <> 2',1) ?></td>
			<td width="200"><label for="ds"><?php echo $langs->trans("DateStart") ?></label></td>
			<td><?php echo $form->select_date($import->dateStart, 'ds') ?></td>
			<td><label class="fieldrequired" for="numreleve"><?php echo $langs->trans("AccountStatement") ?></label></td>
			<td><input type="text" id="numreleve" name="numreleve" value="<?php echo $import->numReleve ?>" /></td>
		</tr>
		<tr>
			<td width="200"><label class="fieldrequired" for="bankimportfile"><?php echo $langs->trans("BankImportFile") ?></label></td>
			<td><input type="file" id="bankimportfile" name="bankimportfile" /></td>
			<td width="200"><label for="de"><?php echo $langs->trans("DateEnd") ?></label></td>
			<td><?php echo $form->select_date($import->dateEnd, 'de') ?></td>
			<td><label for="hasheader"><?php echo $langs->trans("FileHasHeader") ?></label></td>
			<td><input type="checkbox" id="hasheader" name="hasheader" value="1" <?php echo $conf->global->BANKIMPORT_HEADER ? ' checked="checked"' : '' ?> /></td>
		</tr>

		<tr>
			<td width="200"><?php echo $form->textwithpicto(
				'<label for="bankimportmapping" >' . $langs->trans("BankImportMapping") . '</label>',
				$langs->trans("BankImportMappingHelp")
			); ?>
			</td>
			<td><input type="text" id="bankimportmapping" name="bankimportmapping" value="<?php echo $conf->global->BANKIMPORT_MAPPING; ?>" /></td>
			<td><?php echo $form->textwithpicto(
				'<label for="bankimportdateformat" >' . $langs->trans("BankImportDateFormat") . '</label>',
				$langs->trans("BankImportDateFormatHelp")
			); ?>
			</td>
			<td><input type="text" id="bankimportdateformat" name="bankimportdateformat" value="<?php echo $conf->global->BANKIMPORT_DATE_FORMAT; ?>" size="12" /></td>
			<td><?php echo $form->textwithpicto(
				'<label for="bankimportseparator">' . $langs->trans("BankImportSeparator") . '</label>',
				$langs->trans("BankImportSeparatorHelp")
			); ?>
			</td>
			<td><input type="text" id="bankimportseparator" name="bankimportseparator" value="<?php echo $conf->global->BANKIMPORT_SEPARATOR; ?>" size="3" /></td>

		</tr>
	</table>
	<br />

	<div class="center">
		<input type="submit" class="button" name="compare" value="<?php echo dol_escape_htmltag($langs->trans("BankCompareTransactions")) ?>">
	</div>
</form>

<script type="text/javascript">
	$(function() {
		$('form[name=bankimport]').submit(function(event) {
			var TError = new Array;

			if ($('#selectaccountid').val() == -1) 	TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldBankAccountRequired'); ?>");
			if (!$('#numreleve').val().trim()) 		TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldNumReleveRequired'); ?>");
			if ($('#bankimportfile').val() == '') 	TError.push("<?php echo $langs->transnoentitiesnoconv('bankImportFieldBankImportFileRequired'); ?>");

			if (TError.length > 0)
			{
				for (var i = 0; i < TError.length; i++)
				{
					$.jnotify(TError[i], 'error', true);
				}

				return false;
			}

			return true;
		});
	});
</script>
