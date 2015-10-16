<?php

	require '../config.php';
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/fourn/class/fournisseur.facture.class.php');
	dol_include_once('/compta/sociales/class/chargesociales.class.php');
	
	$get=GETPOST('get');
	
	switch ($get) {
		case 'pieceList':
			
			print _pieceList(GETPOST('i'),GETPOST('fk_soc'),GETPOST('type'));
			
			break;
		
	}

	
function _pieceList($i, $fk_soc, $type) {
	global $db, $langs;
	
	
	
	$r='';
	
	if($type == 'facture') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."facture 
				WHERE fk_statut IN (1,3) AND fk_soc=".$fk_soc." ORDER BY datef";
				
		$res = $db->query($sql);
		
		while($obj = $db->fetch_object($res)) {
			
			$f=new Facture($db);
			$f->fetch($obj->rowid);
			
			$r.='<div><span style="width:200px;display:inline-block;">'. $f->getNomUrl(1).' '.price($f->total_ttc) .'</span> <input type="text" value="" name="TPiece['.$i.'][facture]['.$f->id.'][reglement]" size="5" class="flat" /></div>';
			
			
		}				
		
	}
	else if($type == 'fournfacture') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."facture_fourn 
				WHERE fk_statut IN (1) AND fk_soc=".$fk_soc." ORDER BY datef";
				
		$res = $db->query($sql);
		
		while($obj = $db->fetch_object($res)) {
			
			$f=new FactureFournisseur($db);
			$f->fetch($obj->rowid);
			
			$r.='<div><span style="width:200px;display:inline-block;">'. $f->getNomUrl(1).' '.price($f->total_ttc) .'</span> <input type="text" value="" name="TPiece['.$i.'][fournfacture]['.$f->id.'][reglement]" size="5" class="flat" /></div>';
			
			
		}		
	}
	
	else if($type == 'charge') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."chargesociales 
				WHERE paye=0 AND date_ech<=NOW() ORDER BY date_ech";
				
		$res = $db->query($sql);
		
		while($obj = $db->fetch_object($res)) {
			
			$f=new ChargeSociales($db);
			$f->fetch($obj->rowid);
			
			$r.='<div><span style="width:200px;display:inline-block;">'. $f->getNomUrl(1).' '.$f->lib.' '.price($f->amount) .'</span> <input type="text" value="" name="TPiece['.$i.'][charge]['.$f->id.'][reglement]" size="5" class="flat" /></div>';
			
			
		}		
	}
	
		
	
	return $r;
	
}
