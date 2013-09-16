<?php

dol_include_once('/compta/bank/class/account.class.php');

class BankImport {
	var $db;
	
	var $account;
	var $file;
	
	var $dateStart;
	var $dateEnd;
	
	var $TBank;
	var $TFile;
	
	function __construct($db) {
		$this->db = $db;
		$this->dateStart = strtotime('first day of last month');
		$this->dateEnd = strtotime('last day of last month');
	}
	
	function analyse($accountId, $filename, $dateStart, $dateEnd) {
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
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."bank WHERE fk_account = ".$this->accountId." ";
		$sql.= "AND dateo BETWEEN '".date('Y-m-d', $this->dateStart)."' AND '".date('Y-m-d', $this->dateEnd)."' ";
		
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
		
		$f1 = fopen($this->file, 'r');
		
		$TInfosGlobale = array();
		while($dataline = fgetcsv($f1, 1024, $delimiter, $enclosure)) {
			$this->TFile[] = $dataline;
		}
		
		fclose($f1);
	}
}
