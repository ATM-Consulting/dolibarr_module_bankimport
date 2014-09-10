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
		<td width="200"><?php echo $langs->trans("DateEnd") ?></td>
		<td><?php echo dol_print_date($import->dateEnd, 'day') ?></td>
		<td><?php echo $langs->trans("FileHasHeader") ?></td>
		<td><?php echo $import->hasHeader == 1 ? $langs->trans('Yes') : $langs->trans('No') ?></td>
	</tr>
</table>
<br />

<form method="post" enctype="multipart/form-data" name="bankimport">
	<input type="hidden" name="accountid" value="<?php echo $import->account->id ?>" />
	<input type="hidden" name="filename" value="<?php echo $import->file ?>" />
	<input type="hidden" name="datestart" value="<?php echo $import->dateStart ?>" />
	<input type="hidden" name="dateend" value="<?php echo $import->dateEnd ?>" />
	<input type="hidden" name="numreleve" value="<?php echo $import->numReleve ?>" />
	<input type="hidden" name="hasheader" value="<?php echo $import->hasHeader ?>" />
	
	<table class="border" width="100%">
		<tr class="liste_titre">
			<td colspan="4" width="40%"><?php echo $langs->trans("FileTransactions") ?></td>
			<td colspan="7" width="60%"><?php echo $langs->trans("DolibarrTransactions") ?></td>
		</tr>
		<tr class="liste_titre">
			<td><?php echo $langs->trans("Line") ?></td>
			<td><?php echo $langs->trans("Date") ?></td>
			<td><?php echo $langs->trans("Description") ?></td>
			<td width="80"><?php echo $langs->trans("Amount") ?></td>
			<td><?php echo $langs->trans("Transaction") ?></td>
			<td><?php echo $langs->trans("Date") ?></td>
			<td><?php echo $langs->trans("Description") ?></td>
			<td><?php echo $langs->trans("RelatedItem") ?></td>
			<td width="80"><?php echo $langs->trans("Amount") ?></td>
			<td><?php echo $langs->trans("PlannedAction") ?></td>
			<td align="center"><?php echo $langs->trans("DoAction") ?></td>
		</tr>
		<?php foreach($TTransactions as $i => $line) { ?>
		<tr <?php echo $bc[$var] ?>>
			<?php if(!empty($line['bankline'])) { ?>
				
				<td rowspan="<?php echo count($line['bankline']) ?>"><?php echo $i + 1 ?></td>
				<td rowspan="<?php echo count($line['bankline']) ?>"><?php echo $line['date'] ?></td>
				<td rowspan="<?php echo count($line['bankline']) ?>"><?php echo $line['label'] ?></td>
				<td rowspan="<?php echo count($line['bankline']) ?>" align="right"><?php echo price($line['amount']) ?></td>
				
				<?php foreach($line['bankline'] as $j => $bankline) { ?>
				<?php if($j > 0) echo '<tr>' ?>
				<td><?php echo $bankline['url'] ?></td>
				<td><?php echo $bankline['date'] ?></td>
				<td><?php echo $bankline['label'] ?></td>
				<td><?php echo $bankline['relateditem'] ?></td>
				<td align="right"><?php echo $bankline['amount'] ?></td>
				<td><?php echo $bankline['result'] ?></td>
				<td align="center">
				<?php if($bankline['autoaction']) { ?><input type="checkbox" checked="checked" name="TLine[<?php echo $bankline['id'] ?>]" value="<?php echo $i ?>" /><?php } ?>
				</td>
				<?php if($j < count($line['bankline'])) echo '</tr>' ?>
				<?php } ?>
			
			<?php } else if(!empty($line['error'])) { ?>
				<td><?php echo $i + 1 ?></td>
				<td colspan="4"><?php echo $line['error'] ?></td>
				<td colspan="7">&nbsp;</td>
			
			<?php } else { ?>
				<td><?php echo $i + 1 ?></td>
				<td><?php echo $line['date'] ?></td>
				<td><?php echo $line['label'] ?></td>
				<td align="right"><?php echo price($line['amount']) ?></td>
				<td colspan="5">&nbsp;</td>
				<td><?php echo $langs->trans('BankTransactionWillBeCreatedAndReconciled', $import->numReleve) ?></td>
				<td align="center"><input type="checkbox" checked="checked" name="TLine[new][]" value="<?php echo $i ?>" /></td>
			<?php } ?>
			
			<?php $var = !$var ?>
		</tr>
		<?php } ?>
	</table>
	<br />
	
	<center>
		<input type="submit" class="button" name="import" value="<?php echo dol_escape_htmltag($langs->transnoentities("BankImport")) ?>">
	</center>
</form>