<?php

dol_include_once('/compta/bank/class/account.class.php');
dol_include_once('/compta/paiement/cheque/class/remisecheque.class.php');

class BankImport {
	var $db;
	
	var $account;
	var $file;
	
	var $dateStart;
	var $dateEnd;
	var $numReleve;
	var $hasHeader;
	
	var $TBank = array(); // Will contain all account lines of the period
	var $TCheckReceipt = array(); // Will contain check receipt made for account lines of the period
	var $TFile = array(); // Will contain all file lines
	
	var $nbCreated = 0;
	var $nbReconciled = 0;
	
	function __construct($db) {
		$this->db = &$db;
		$this->dateStart = strtotime('first day of last month');
		$this->dateEnd = strtotime('last day of last month');
	}
	
	/**
	 * Set vars we will work with
	 */
	function analyse($accountId, $filename, $dateStart, $dateEnd, $numReleve, $hasHeader) {
		global $conf, $langs;
		
		// Bank account selected
		if($accountId <= 0) {
			setEventMessage($langs->trans('ErrorAccountIdNotSelected'), 'errors');
			return false;
		} else {
			$this->account = new Account($this->db);
			$this->account->fetch($accountId);
		}
		
		// Start and end date regarding bank statement
		$this->dateStart = $dateStart;
		$this->dateEnd = $dateEnd;
		
		// Statement number
		$this->numReleve = $numReleve;
		$this->hasHeader = $hasHeader;
		
		// Bank statement file (csv or filename if csv already uploaded)
		if(is_file($filename)) {
			$this->file = $filename;
		} else if(!empty($_FILES[$filename])) {
			
			if($_FILES[$filename]['error'] != 0) {
				setEventMessage($langs->trans('ErrorFile'.$_FILES[$filename]['error']), 'errors');
				return false;
			}/* else if($_FILES[$filename]['type'] != 'text/csv' && $_FILES[$filename]['type'] != 'text/plain' &&  && $_FILES[$filename]['type'] !='application/octet-stream') {
				setEventMessage($langs->trans('ErrorFileIsNotCSV').' '.$_FILES[$filename]['type'], 'errors');
				return false;
			}*/ 
			else {
				
				dol_include_once('/core/lib/files.lib.php');
				dol_include_once('/core/lib/images.lib.php');
				$upload_dir = $conf->bankimport->dir_output . '/' . dol_sanitizeFileName($this->account->ref);
				
				dol_add_file_process($upload_dir,1,1,$filename);
				$this->file = $upload_dir . '/' . $_FILES[$filename]['name'];
				
				if(!is_file($this->file)) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	function load_transactions() {
		$this->load_bank_transactions();
		$this->load_check_receipt();
		$this->load_file_transactions();
	}
	
	// Load bank lines
	function load_bank_transactions() {
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bank WHERE fk_account = ".$this->account->id." ";
		$sql.= "AND dateo BETWEEN '".date('Y-m-d', $this->dateStart)."' AND '".date('Y-m-d', $this->dateEnd)."' ";
		$sql.= "ORDER BY datev DESC";
		
		$resql = $this->db->query($sql);
		$TBankLineId = array();
		while($obj = $this->db->fetch_object($resql)) {
			$TBankLineId[] = $obj->rowid;
		}
		
		foreach($TBankLineId as $bankid) {
			$bankLine = new AccountLine($this->db);
			$bankLine->fetch($bankid);
			$this->TBank[$bankid] = $bankLine;
		}
	}
	
	// Load check receipt regarding bank lines
	function load_check_receipt() {
		foreach($this->TBank as $bankLine) {
			if($bankLine->fk_bordereau > 0 && empty($this->TCheckReceipt[$bankLine->fk_bordereau])) {
				$bord = new RemiseCheque($this->db);
				$bord->fetch($bankLine->fk_bordereau);
				
				$this->TCheckReceipt[$bankLine->fk_bordereau] = $bord;
			}
		}
	}
	
	// Load file lines
	function load_file_transactions() {
		global $conf, $langs;
		
		$delimiter = $conf->global->BANKIMPORT_SEPARATOR;
		$enclosure = '"';
		$dateFormat = strtr( $conf->global->BANKIMPORT_DATE_FORMAT, array('%'=>''));
		
		$mapping = explode($delimiter, $conf->global->BANKIMPORT_MAPPING);
		
		$f1 = fopen($this->file, 'r');
		if($this->hasHeader) fgetcsv($f1, 1024, $delimiter, $enclosure);
		
		$TInfosGlobale = array();
		while($dataline = fgetcsv($f1, 1024, $delimiter, $enclosure)) {
			if(count($dataline) == count($mapping)) {
				$data = array_combine($mapping, $dataline);
				
				// Gestion du montant débit / crédit
				if(!empty($data['debit'])) {
					$data['debit'] = price2num($data['debit']);
					if($data['debit'] > 0) $data['debit'] *= -1;
				}
				if(!empty($data['credit'])) {
					$data['credit'] = price2num($data['credit']);
				}
				if(empty($data['debit']) && empty($data['credit'])) {
					$amount = price2num($data['amount']);
					if($amount >= 0) $data['credit'] = $amount;
					if($amount < 0) $data['debit'] = $amount;
				}
				
				$data['amount'] = (!empty($data['debit']) ? $data['debit'] : $data['credit']);
				
				//$time = date_parse_from_format($dateFormat, $data['date']);
				//$data['datev'] = mktime(0, 0, 0, $time['month'], $time['day'], $time['year']+2000);
					
				$datetime = new DateTime;
				// TODO : Apparemment createFromFormat ne fonctionne pas si PHP < 5.3 .... 
				$datetime= DateTime::createFromFormat($dateFormat, $data['date']);
				
				$data['datev'] = ($datetime===false) ? 0 : $datetime->getTimestamp() ;
				
				$data['error'] = '';
			} else {
				$data = array();
				$data['error'] = $langs->trans('LineDoesNotMatchWithMapping');
			}
			
			$this->TFile[] = $data;
		}
		
		fclose($f1);
	}
	
	function compare_transactions() {
		// For each file transaction, we search in Dolibarr bank transaction if there is a match by amount
		foreach($this->TFile as &$fileLine) {
			$amount = price2num($fileLine['amount']); // Transform to numeric string
			if(is_numeric($amount)) {
				$transac = $this->search_dolibarr_transaction_by_amount($amount);
				if($transac === false) $transac = $this->search_dolibarr_transaction_by_receipt($amount);
				$fileLine['bankline'] = $transac;
			}
		}
	}
	
	private function search_dolibarr_transaction_by_amount($amount) {
		global $langs;
		$langs->load("banks");
		
		$amount = floatval($amount); // Transform to float
		foreach($this->TBank as $i => $bankLine) {
			if($amount == $bankLine->amount) {
				unset($this->TBank[$i]);
				
				return array($this->get_bankline_data($bankLine));
			}
		}
		
		return false;
	}

	private function search_dolibarr_transaction_by_receipt($amount) {
		global $langs;
		$langs->load("banks");
		
		$amount = floatval($amount); // Transform to float
		foreach($this->TCheckReceipt as $bordereau) {
			if($amount == $bordereau->amount) {
				$TBankLine = array();
				foreach($this->TBank as $i => $bankLine) {
					if($bankLine->fk_bordereau == $bordereau->id) {
						unset($this->TBank[$i]);
						
						$TBankLine[] = $this->get_bankline_data($bankLine);
					}
				}
				
				return $TBankLine;
			}
		}
		
		return false;
	}

	private function get_bankline_data($bankLine) {
		global $langs;
		
		if(!empty($bankLine->num_releve)) {
			$link = '<a href="'.dol_buildpath('/compta/bank/releve.php?num='.$bankLine->num_releve.'&account='.$bankLine->fk_account, 2).'">'.$bankLine->num_releve.'</a>';
			$result = $langs->trans('AlreadyReconciledWithStatement', $link);
			$autoaction = false;
		} else {
			$result = $langs->trans('WillBeReconciledWithStatement', $this->numReleve);
			$autoaction = true;
		}
		
		return array(
			'id' => $bankLine->id
			,'url' => $bankLine->getNomUrl(1)
			,'date' => dol_print_date($bankLine->datev,"day")
			,'label' => (preg_match('/^\((.*)\)$/i',$bankLine->label,$reg) ? $langs->trans($reg[1]) : dol_trunc($bankLine->label,60))
			,'amount' => price($bankLine->amount)
			,'result' => $result
			,'autoaction' => $autoaction
		);
	}
	
	/**
	 * Actions made after file check by user
	 */
	public function import_data($TLine) {
		if(!empty($TLine['new'])) {
			foreach($TLine['new'] as $iFileLine) {
				$bankLineId = $this->create_bank_transaction($this->TFile[$iFileLine]);
				if($bankLineId > 0) {
					$bankLine = new AccountLine($this->db);
					$bankLine->fetch($bankLineId);
					$this->reconcile_bank_transaction($bankLine, $this->TFile[$iFileLine]);
				}
			}
			
			unset($TLine['new']);
		}
		foreach($TLine as $bankLineId => $iFileLine) {
			$this->reconcile_bank_transaction($this->TBank[$bankLineId], $this->TFile[$iFileLine]);
		}
	}
	
	private function create_bank_transaction($fileLine) {
		global $user;
		
		$bankLineId = $this->account->addline($fileLine['datev'], 'PRE', $fileLine['label'], $fileLine['amount'], '', '', $user);
		$this->nbCreated++;
		
		return $bankLineId;
	}
	
	private function reconcile_bank_transaction($bankLine, $fileLine) {
		global $user;
		
		// Set conciliation
		$bankLine->num_releve = $this->numReleve;
		$bankLine->update_conciliation($user, 0);
		
		// Update value date
		$dateDiff = ($fileLine['datev'] - strtotime($bankLine->datev)) / 24 / 3600;
		$bankLine->datev_change($bankLine->id, $dateDiff);
		
		$this->nbReconciled++;
	}
}
