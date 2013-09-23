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
	
	var $TBank = array(); // Will contain all account lines of the period
	var $TCheckReceipt = array(); // Will contain check receipt made for account lines of the period
	var $TFile = array(); // Will contain all file lines
	
	function __construct($db) {
		$this->db = $db;
		$this->dateStart = strtotime('first day of last month');
		$this->dateEnd = strtotime('last day of last month');
	}
	
	/**
	 * Set vars we will work with
	 */
	function analyse($accountId, $filename, $dateStart, $dateEnd, $numReleve) {
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
		
		// Bank statement file (csv or filename if csv already uploaded)
		if(is_file($filename)) {
			$this->file = $filename;
		} else if(!empty($_FILES[$filename])) {
			if($_FILES[$filename]['error'] != 0) {
				setEventMessage($langs->trans('ErrorFile'.$_FILES[$filename]['error']), 'errors');
				return false;
			} else if($_FILES[$filename]['type'] != 'text/csv') {
				setEventMessage($langs->trans('ErrorFileIsNotCSV'), 'errors');
				return false;
			} else {
				dol_include_once('/core/lib/files.lib.php');
				$upload_dir = $conf->bankimport->dir_output . '/' . dol_sanitizeFileName($this->account->ref);
				dol_add_file_process($upload_dir,0,1,$filename);
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
			$bankline = new AccountLine($this->db);
			$bankline->fetch($bankid);
			$this->TBank[$bankid] = $bankline;
		}
	}
	
	// Load check receipt regarding bank lines
	function load_check_receipt() {
		foreach($this->TBank as $bankline) {
			if($bankline->fk_bordereau > 0 && empty($this->TCheckReceipt[$bankline->fk_bordereau])) {
				$bord = new RemiseCheque($this->db);
				$bord->fetch($bankline->fk_bordereau);
				
				$this->TCheckReceipt[$bankline->fk_bordereau] = $bord;
			}
		}
	}
	
	// Load file lines
	function load_file_transactions() {
		global $conf;
		
		$delimiter = $conf->BANKIMPORT_SEPARATOR;
		$enclosure = '"';
		$dateFormat = $conf->BANKIMPORT_DATE_FORMAT;
		$mapping = $conf->BANKIMPORT_MAPPING;
		
		$f1 = fopen($this->file, 'r');
		
		$TInfosGlobale = array();
		while($dataline = fgetcsv($f1, 1024, $delimiter, $enclosure)) {
			$data = array_combine($mapping, $dataline);
			$data['amount'] = price2num(!empty($data['debit']) ? $data['debit'] : $data['credit']);
			
			$time = strptime($data['date'], $dateFormat);
			$data['datev'] = mktime(0, 0, 0, $time['tm_mon']+1, $time['tm_mday'], $time['tm_year']+1900);
			
			$this->TFile[] = $data;
		}
		
		fclose($f1);
	}
	
	function compare_transactions() {
		// For each file transaction, we search in Dolibarr bank transaction if there is a match by amount
		foreach($this->TFile as &$fileline) {
			$amount = price2num($fileline['amount']); // Transform to numeric string
			if(is_numeric($amount)) {
				$transac = $this->search_dolibarr_transaction_by_amount($amount);
				if($transac === false) $transac = $this->search_dolibarr_transaction_by_receipt($amount);
				$fileline['bankline'] = $transac;
			}
		}
		
		return $this->TFile;
	}
	
	private function search_dolibarr_transaction_by_amount($amount) {
		global $langs;
		$langs->load("banks");
		
		$amount = floatval($amount); // Transform to float
		foreach($this->TBank as $i => $bankline) {
			if($amount == $bankline->amount) {
				unset($this->TBank[$i]);
				
				return array($this->get_bankline_data($bankline));
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
				foreach($this->TBank as $i => $bankline) {
					if($bankline->fk_bordereau == $bordereau->id) {
						unset($this->TBank[$i]);
						
						$TBankLine[] = $this->get_bankline_data($bankline);
					}
				}
				
				return $TBankLine;
			}
		}
		
		return false;
	}

	private function get_bankline_data($bankline) {
		global $langs;
		
		if(!empty($bankline->num_releve)) {
			$result = $langs->trans('AlreadyReconciledWithStatement', $bankline->num_releve);
			$autoaction = false;
		} else {
			$result = $langs->trans('WillBeReconciledWithStatement', $this->numReleve);
			$autoaction = true;
		}
		
		return array(
			'id' => $bankline->id
			,'url' => $bankline->getNomUrl(1)
			,'date' => dol_print_date($bankline->datev,"day")
			,'label' => (preg_match('/^\((.*)\)$/i',$bankline->label,$reg) ? $langs->trans($reg[1]) : dol_trunc($bankline->label,60))
			,'amount' => price($bankline->amount)
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
					$bankline = new AccountLine($this->db);
					$bankline->fetch($bankLineId);
					$this->reconcile_bank_transaction($bankline, $this->TFile[$iFileLine]);
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
		
		return $this->account->addline($fileLine['datev'], 'PRE', $fileLine['label'], $fileLine['amount'], '', '', $user);
	}
	
	private function reconcile_bank_transaction($bankLine, $fileLine) {
		global $user;
		
		// Set conciliation
		$bankLine->num_releve = $this->numReleve;
		$bankLine->update_conciliation($user, 0);
		
		// Update value date
		$dateDiff = ($fileLine['datev'] - strtotime($bankLine->datev)) / 24 / 3600;
		$bankLine->datev_change($bankLine->id, $dateDiff);
	}
}
