<table class="border" width="100%">
	<tr>
		<td width="200"><?= $langs->trans("BankAccount") ?></td>
		<td><?= $import->account->getNomUrl(1) ?></td>
		<td width="200"><?= $langs->trans("DateStart") ?></td>
		<td><?= dol_print_date($import->dateStart, 'day') ?></td>
		<td><?= $langs->trans("AccountStatement") ?></td>
		<td><?= $import->numReleve ?></td>
	</tr>
	<tr>
		<td width="200"><?= $langs->trans("BankImportFile") ?></td>
		<td><?= basename($import->file) ?></td>
		<td width="200"><?= $langs->trans("DateEnd") ?></td>
		<td><?= dol_print_date($import->dateEnd, 'day') ?></td>
		<td><?= $langs->trans("HasHeader") ?></td>
		<td><?= $import->hasHeader == 1 ? $langs->trans('Yes') : $langs->trans('No') ?></td>
	</tr>
</table>
<br />

<form method="post" enctype="multipart/form-data" name="bankimport">
	<input type="hidden" name="accountid" value="<?= $import->account->id ?>" />
	<input type="hidden" name="filename" value="<?= $import->file ?>" />
	<input type="hidden" name="datestart" value="<?= $import->dateStart ?>" />
	<input type="hidden" name="dateend" value="<?= $import->dateEnd ?>" />
	<input type="hidden" name="numreleve" value="<?= $import->numReleve ?>" />
	<input type="hidden" name="hasheader" value="<?= $import->hasHeader ?>" />
	
	<table class="border" width="100%">
		<tr class="liste_titre">
			<td colspan="4" width="50%"><?= $langs->trans("FileTransactions") ?></td>
			<td colspan="6" width="50%"><?= $langs->trans("DolibarrTransactions") ?></td>
		</tr>
		<tr class="liste_titre">
			<td><?= $langs->trans("Line") ?></td>
			<td><?= $langs->trans("Date") ?></td>
			<td><?= $langs->trans("Description") ?></td>
			<td width="80"><?= $langs->trans("Amount") ?></td>
			<td><?= $langs->trans("Transaction") ?></td>
			<td><?= $langs->trans("Date") ?></td>
			<td><?= $langs->trans("Description") ?></td>
			<td width="80"><?= $langs->trans("Amount") ?></td>
			<td><?= $langs->trans("Action") ?></td>
			<td align="center"><?= $langs->trans("DoAction") ?></td>
		</tr>
		<? foreach($TTransactions as $i => $line) { ?>
		<tr <?= $bc[$var] ?>>
			<? if(!empty($line['bankline'])) { ?>
				
				<td rowspan="<?= count($line['bankline']) ?>"><?= $i + 1 ?></td>
				<td rowspan="<?= count($line['bankline']) ?>"><?= $line['date'] ?></td>
				<td rowspan="<?= count($line['bankline']) ?>"><?= $line['label'] ?></td>
				<td rowspan="<?= count($line['bankline']) ?>" align="right"><?= price($line['amount']) ?></td>
				
				<? foreach($line['bankline'] as $j => $bankline) { ?>
				<? if($j > 0) echo '<tr>' ?>
				<td><?= $bankline['url'] ?></td>
				<td><?= $bankline['date'] ?></td>
				<td><?= $bankline['label'] ?></td>
				<td align="right"><?= $bankline['amount'] ?></td>
				<td><?= $bankline['result'] ?></td>
				<td align="center">
				<? if($bankline['autoaction']) { ?><input type="checkbox" checked="checked" name="TLine[<?= $bankline['id'] ?>]" value="<?= $i ?>" /><? } ?>
				</td>
				<? if($j < count($line['bankline'])) echo '</tr>' ?>
				<? } ?>
			
			<? } else if(!empty($line['error'])) { ?>
				<td><?= $i + 1 ?></td>
				<td colspan="4"><?= $line['error'] ?></td>
				<td colspan="6">&nbsp;</td>
			
			<? } else { ?>
				<td><?= $i + 1 ?></td>
				<td><?= $line['date'] ?></td>
				<td><?= $line['label'] ?></td>
				<td><?= price($line['debit']) ?></td>
				<td><?= price($line['credit']) ?></td>
				<td colspan="4">&nbsp;</td>
				<td><?= $langs->trans('BankTransactionWillBeCreatedAndReconciled') ?></td>
				<td align="center"><input type="checkbox" checked="checked" name="TLine[new][]" value="<?= $i ?>" /></td>
			<? } ?>
			
			<? $var = !$var ?>
		</tr>
		<? } ?>
	</table>
	<br />
	
	<center>
		<input type="submit" class="button" name="import" value="<?= dol_escape_htmltag($langs->transnoentities("BankImport")) ?>">
	</center>
</form>