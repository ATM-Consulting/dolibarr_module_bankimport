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
	
	var $TBank = array();
	var $TCheckReceipt = array();
	var $TFile = array();
	
	function __construct($db) {
		$this->db = $db;
		$this->dateStart = strtotime('first day of last month');
		$this->dateEnd = strtotime('last day of last month');
		
		$this->dateStart = strtotime('07/15/13');
		$this->dateEnd = strtotime('09/14/13');
		$this->numReleve = 14;
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
		$this->load_check_receipt();
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
	
	// Chargement des écritures banque de Dolibarr pour la période
	function load_check_receipt() {
		foreach($this->TBank as $bankline) {
			if($bankline->fk_bordereau > 0 && empty($this->TCheckReceipt[$bankline->fk_bordereau])) {
				$bord = new RemiseCheque($this->db);
				$bord->fetch($bankline->fk_bordereau);
				
				$this->TCheckReceipt[$bankline->fk_bordereau] = $bord;
			}
		}
	}
	
	function compare_transactions() {
		// For each file transaction, we search in Dolibarr bank transaction if there is a match by amount
		foreach($this->TFile as &$fileline) {
			$amount = !empty($fileline['debit']) ? $fileline['debit'] : $fileline['credit'];
			$amount = price2num($amount); // Transform to numeric string
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
}
