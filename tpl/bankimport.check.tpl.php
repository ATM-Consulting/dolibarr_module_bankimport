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

<form method="post" enctype="multipart/form-data" name="bankimport">
	<input type="hidden" name="accountid" value="<?= $import->account->id ?>" />
	<input type="hidden" name="filename" value="<?= $import->file ?>" />
	<input type="hidden" name="datestart" value="<?= $import->dateStart ?>" />
	<input type="hidden" name="dateend" value="<?= $import->dateEnd ?>" />
	<input type="hidden" name="numreleve" value="<?= $import->numReleve ?>" />
	
	<table class="border" width="100%">
		<tr class="liste_titre">
			<td colspan="4"><?= $langs->trans("FileTransactions") ?></td>
			<td colspan="6"><?= $langs->trans("DolibarrTransactions") ?></td>
		</tr>
		<tr class="liste_titre">
			<td><?= $langs->trans("Value") ?></td>
			<td><?= $langs->trans("Description") ?></td>
			<td><?= $langs->trans("Debit") ?></td>
			<td><?= $langs->trans("Credit") ?></td>
			<td><?= $langs->trans("Transaction") ?></td>
			<td><?= $langs->trans("Date") ?></td>
			<td><?= $langs->trans("Description") ?></td>
			<td><?= $langs->trans("Amount") ?></td>
			<td><?= $langs->trans("Result") ?></td>
			<td><?= $langs->trans("DoAction") ?></td>
		</tr>
		<? foreach($TTransactions as $i => $line) { ?>
		<tr <?= $bc[$var] ?>>
			<td rowspan="<?= count($line['bankline']) ?>"><?= $line['date'] ?></td>
			<td rowspan="<?= count($line['bankline']) ?>"><?= $line['desc'] ?></td>
			<td rowspan="<?= count($line['bankline']) ?>"><?= $line['debit'] ?></td>
			<td rowspan="<?= count($line['bankline']) ?>"><?= $line['credit'] ?></td>
			<? if(!empty($line['bankline'])) { ?>
				<? foreach($line['bankline'] as $i => $bankline) { ?>
				<? if($i > 0) echo '<tr>' ?>
				<td><?= $bankline['url'] ?></td>
				<td><?= $bankline['date'] ?></td>
				<td><?= $bankline['label'] ?></td>
				<td><?= $bankline['amount'] ?></td>
				<td><?= $bankline['result'] ?></td>
				<? if($bankline['autoaction']) { ?><td align="center"><input type="checkbox" name="TLine[<?= $bankline['id'] ?>]" value="<?= $i ?>" /></td><? } ?>
				<? if($i < count($line['bankline'])) echo '</tr>' ?>
				<? } ?>
			<? } else { ?>
			<td colspan="4">&nbsp;</td>
			<td><?= $langs->trans('BankTransactionWillBeCreatedAndReconciled') ?></td>
			<td align="center"><input type="checkbox" name="TLine[new][]" value="<?= $i ?>" /></td>
			<? } ?>
			<? $var = !$var ?>
		</tr>
		<? } ?>
	</table>
	<br />
	
	<center>
		<input type="submit" class="button" name="import" value="<?= dol_escape_htmltag($langs->trans("BankImport")) ?>">
	</center>
</form>