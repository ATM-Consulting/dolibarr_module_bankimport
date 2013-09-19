<?php

dol_include_once('/compta/bank/class/account.class.php');

class BankImport {
	var $db;
	
	var $account;
	var $file;
	
	var $dateStart;
	var $dateEnd;
	var $numReleve;
	
	var $TBank;
	var $TFile;
	
	function __construct($db) {
		$this->db = $db;
		$this->dateStart = strtotime('first day of last month');
		$this->dateEnd = strtotime('last day of last month');
	}
	
	function analyse($accountId, $filename, $dateStart, $dateEnd, $numReleve) {
		global $conf, $langs;
		
		// Compte bancaire avec lequel on travaille
		if($accountId <= 0) {
			setEventMessage($langs->trans('ErrorAccountIdNotSelected'), 'errors');
			return false;
		} else {
			$this->account = new Account($this->db);
			$this->account->fetch($accountId);
		}
		
		// Date début et fin de la période observée
		$this->dateStart = $dateStart;
		$this->dateEnd = $dateEnd;
		$this->numReleve = $numReleve;
		
		// Fichier bancaire CSV
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
				$upload_dir = $conf->bankimport->dir_output . '/' . dol_sanitizeFileName(date('Y-m-d H:i:s'));
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
		$this->load_file_transactions();
	}
	
	// Chargement des écritures banque de Dolibarr pour la période
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
			$this->TBank[] = $bankline;
		}
	}
	
	// Chargement des écritures banque du fichier pour la période
	function load_file_transactions() {
		$delimiter = ';';
		$enclosure = '"';
		$mapping = array('date', 'num_ope', 'desc', 'debit', 'credit', 'detail');
		
		$f1 = fopen($this->file, 'r');
		
		$TInfosGlobale = array();
		while($dataline = fgetcsv($f1, 1024, $delimiter, $enclosure)) {
			$this->TFile[] = array_combine($mapping, $dataline);
		}
		
		fclose($f1);
	}
	
	function compare_transactions() {
		// For each file transaction, we search in Dolibarr bank transaction if there is a match by amount
		foreach($this->TFile as &$fileline) {
			$amount = !empty($fileline['debit']) ? $fileline['debit'] : $fileline['credit'];
			$fileline['bankline'] = $this->search_dolibarr_transaction_by_amount($amount);
		}
		
		return $this->TFile;
	}
	
	private function search_dolibarr_transaction_by_amount($amount) {
		global $langs;
		$langs->load("banks");
		
		$amount = price2num($amount); // Transform to numeric string
		if(is_numeric($amount)) {
			$amount = floatval($amount); // Transform to float
			foreach($this->TBank as $i => $bankline) {
				if($amount == $bankline->amount) {
					unset($this->TBank[$i]);
					
					if(!empty($bankline->num_releve)) {
						$result = $langs->trans('AlreadyReconciledWithStatement', $bankline->num_releve);
					} else {
						$result = $langs->trans('WillBeReconciledWithStatement', $this->numReleve);
					}
					
					return array(
						'url' => $bankline->getNomUrl(1)
						,'date' => dol_print_date($bankline->datev,"day")
						,'label' => (preg_match('/^\((.*)\)$/i',$bankline->label,$reg) ? $langs->trans($reg[1]) : dol_trunc($bankline->label,60))
						,'amount' => price($bankline->amount)
						,'result' => $result
					);
				}
			}
		}
		
		return 0;
	}

	private function search_dolibarr_transaction_by_receipt($amount) {
		global $langs;
		$langs->load("banks");
		
		$amount = price2num($amount); // Transform to numeric string
		if(is_numeric($amount)) {
			$amount = floatval($amount); // Transform to float
			foreach($this->TBank as $bankline) {
				if(!empty($bankline->foundinfile)) continue;
				if($amount == $bankline->amount) {
					$bankline->foundinfile = true;
					
					if(!empty($bankline->num_releve)) {
						$result = $langs->trans('AlreadyReconciledWithStatement', $bankline->num_releve);
					} else {
						$result = $langs->trans('WillBeReconciledWithStatement', $this->numReleve);
					}
					
					return array(
						'url' => $bankline->getNomUrl(1)
						,'date' => dol_print_date($bankline->datev,"day")
						,'label' => (preg_match('/^\((.*)\)$/i',$bankline->label,$reg) ? $langs->trans($reg[1]) : dol_trunc($bankline->label,60))
						,'debit' => $bankline->amount < 0 ? price($bankline->amount) : ''
						,'credit' => $bankline->amount > 0 ? price($bankline->amount) : ''
						,'result' => $result
					);
				}
			}
		}
		
		return 0;
	}
}
