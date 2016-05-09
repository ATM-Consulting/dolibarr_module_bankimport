<?php

	require '../config.php';
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/societe/class/societe.class.php');
	dol_include_once('/fourn/class/fournisseur.facture.class.php');
	dol_include_once('/compta/sociales/class/chargesociales.class.php');
	
	$get=GETPOST('get');
	
	switch ($get) {
		case 'pieceList':
			
			print _pieceList(GETPOST('i'),GETPOST('fk_soc'),GETPOST('type'));
			
			break;
		
	}

	
function _pieceList($i, $fk_soc, $type) {
	global $db, $langs, $conf, $langs;
	
	$langs->load('compta');
	
	$r='';
	
	if($type == 'facture') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."facture 
				WHERE fk_statut IN (";
		if(!empty($conf->global->BANKIMPORT_ALLOW_DRAFT_INVOICE)) $sql.= "0,";
		$sql.= "1,3) AND fk_soc=".$fk_soc." ORDER BY datef";
				
		$res = $db->query($sql);
		
		while($obj = $db->fetch_object($res)) {
			
			$f=new Facture($db);
			$f->fetch($obj->rowid);
			
			$s = new Societe($db);
			$s->fetch($f->socid);
			
			$r.='<div style="margin:2px 0;"><span style="width:400px;display:inline-block;">'
				.$f->getNomUrl(1).' ('.date('d/m/Y', $f->date).') '.$s->getNomUrl(1, '', 12).' <strong>'.price($f->total_ttc).'</strong></span>'
				.'<input type="hidden" name="price_TLine[piece]['.$i.'][facture]['.$f->id.']" value="'.price2num($f->total_ttc).'" />'
				.img_picto($langs->trans('AddRemind'),'rightarrow.png', 'id="TLine[piece]['.$i.'][facture]['.$f->id.']" class="auto_price"')
				.'<input type="text" value="" name="TLine[piece]['.$i.'][facture]['.$f->id.']" size="6" class="flat" /></div>';
			
			
		}			
		
	}
	else if($type == 'fournfacture') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."facture_fourn 
				WHERE fk_statut IN (";
		if(!empty($conf->global->BANKIMPORT_ALLOW_DRAFT_INVOICE)) $sql.= "0,";
		$sql.= "1) AND fk_soc=".$fk_soc." ORDER BY datef";
				
		$res = $db->query($sql);
		
		while($obj = $db->fetch_object($res)) {
			
			$f=new FactureFournisseur($db);
			$f->fetch($obj->rowid);
			
			$s = new Societe($db);
			$s->fetch($f->socid);
			
			$r.='<div style="margin:2px 0;"><span style="width:400px;display:inline-block;">'
				.$f->getNomUrl(1).' ('.date('d/m/Y', $f->date).') '.$s->getNomUrl(1, '', 12).' <strong>'.price($f->total_ttc).'</strong></span>'
				.'<input type="hidden" name="price_TLine[piece]['.$i.'][facture]['.$f->id.']" value="'.price2num($f->total_ttc).'" />'
				.img_picto($langs->trans('AddRemind'),'rightarrow.png', 'id="TLine[piece]['.$i.'][facture]['.$f->id.']" class="auto_price"')
				.'<input type="text" value="" name="TLine[piece]['.$i.'][facture]['.$f->id.']" size="6" class="flat" /></div>';
			
			
		}		
	}
	
	else if($type == 'charge') {
		
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."chargesociales 
				WHERE paye=0 AND date_ech<=NOW() ORDER BY date_ech";
				
		$res = $db->query($sql);
		
		while($obj = $db->fetch_object($res)) {
			
			$f=new ChargeSociales($db);
			$f->fetch($obj->rowid);
			
			$r.='<div><span style="width:200px;display:inline-block;">'. $f->getNomUrl(1).' '.$f->lib.' '.price($f->amount) .'</span> <input type="text" value="" name="TLine[piece]['.$i.'][charge]['.$f->id.']" size="5" class="flat" /></div>';
			
			
		}		
	}
	
		
	
	return $r;
	
}
