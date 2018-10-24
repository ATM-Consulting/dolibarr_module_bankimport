<?php

require 'config.php';
dol_include_once('/bankimport/class/bankimport.class.php');

if(empty($user->rights->bankimport->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('bankimport@bankimport');


$object = new Bankimportdet($db);

$hookmanager->initHooks(array('bankimportdetlist'));


/*
 * Actions
 */
$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
if (empty($reshook))
{
    // do action from GETPOST ...
    $massaction = GETPOST('massaction');
    $confirmmassaction = GETPOST('confirmmassaction');
    $toselect = GETPOST('toselect', 'array');
    
    if(!empty($confirmmassaction))
    {
        if($massaction == 'delete')
        {
            $deleteCount = 0 ;
            $deleteCountError = 0 ;
            foreach ($toselect as $value) 
            {
                $deleteObject = new Bankimportdet($db);
                if($deleteObject->fetch($value) > 0){
                    if($deleteObject->delete($user)>0){
                        $deleteCount++;
                    }
                    else {
                        $deleteCountError ++;
                    }
                }
            }
            
            if($deleteCount>0){
                setEventMessage($langs->trans('BankInDeleted', $deleteCount));
            }
            
            if($deleteCountError>0){
                setEventMessage($langs->trans('BankInDeleted'),'errors');
            }
        }
    }
    
    
    
    
}
/*
 * View
 */
$arrayofjs='';
$arrayofcss=array('bankimport/css/style.css');
llxHeader('', $langs->trans('BankimportdetList'), '', '', 0, 0, $arrayofjs, $arrayofcss);

//$type = GETPOST('type');
//if (empty($user->rights->bankimportdet->all->read)) $type = 'mine';
// TODO ajouter les champs de son objet que l'on souhaite afficher
$sqlSelect =  'SELECT ';
$sqlSelect.= '  bi.rowid rowid';
$sqlSelect.= ', bi.datev';
$sqlSelect.= ', bi.dateo';
$sqlSelect.= ', bi.amount';
$sqlSelect.= ', bi.num_releve';
$sqlSelect.= ', bi.fk_statut';
$sqlSelect.= ', bi.label';
$sqlSelect.= ', bi.fk_bank_account';
$sqlSelect.= ', bi.element';
$sqlSelect.= ', bi.fk_element';
$sqlSelect.= ', bi.fk_user_modif';
$sqlSelect.= ', bi.fk_user_author';
$sqlSelect.= ', bi.fk_bank';
$sqlSelect.= ', bi.date_linked';
$sqlSelect.= ', bi.import_key';
$sqlSelect.= ', bi.note';
$sqlSelect.= ', ba.label bankAccountName';

$sql= $sqlSelect.' FROM '.MAIN_DB_PREFIX.$object->table_element.' bi ';
$sql.= ' LEFT JOIN  '.MAIN_DB_PREFIX.'bank_account ba ON (bi.fk_bank_account = ba.rowid ) ';
$sql.= ' WHERE ';
$sql.= '  bi.entity IN ('.getEntity('Bank', 1).')';




$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_Bankimportdet', 'GET');
$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;
$r = new Listview($db, 'bankimportdet');
echo $r->render($sql, array(
    'view_type' => 'list' // default = [list], [raw], [chart]
    ,'limit'=>array(
        'nbLine' => $nbLine
    )
    ,'subQuery' => array()
    ,'link' => array(
        'fk_bank_account' => '<a href="'.dol_buildpath('compta/bank/card.php',1).'?id=@fk_bank_account@" >@bankAccountName@</a>',
    )
    ,'type' => array(
        'datev' => 'date' // [datetime], [hour], [money], [number], [integer]
        ,'dateo' => 'date'
        ,'debit' => 'money'
        ,'credit' => 'money'
        ,'amount' => 'money'
        ,'note'=> 'text'
    )
    ,'search' => array(
        'datev' => array('search_type' => 'calendars', 'allow_is_null' => false)
        ,'dateo' => array('search_type' => 'calendars', 'allow_is_null' => false)
        ,'label' => array('search_type' => true, 'table' => 'bi', 'field' => 'label')
        ,'debit' => array('search_type' => true, 'table' => 'bi', 'field' => 'debit')
        ,'credit' => array('search_type' => true, 'table' => 'bi', 'field' => 'credit')
        ,'amount' => array('search_type' => true, 'table' => 'bi', 'field' => 'amount')
        ,'import_key' => array('search_type' => true, 'table' => 'bi', 'field' => 'import_key')
        ,'num_releve' => array('search_type' => true, 'table' => 'bi', 'field' => 'num_releve')
        ,'fk_statut' => array('search_type' => Bankimportdet::listStatus())
        ,'note' => array('search_type' => true, 'table' => 'bi', 'field' => 'note')
        ,'fk_bank_account' => array('search_type' => _getBankAccountList(), 'table' => 'bi', 'field' => 'fk_bank_account')
        
    )
    ,'allow-fields-select' => 1 // allow to select hidden fields
    
    ,'hide' => array(
        'dateo'
        ,'import_key'
        ,'date_linked'
        ,'fk_element'
        ,'num_releve'
        ,'fk_statut'
        ,'fk_user_modif'
        ,'fk_user_author'
        ,'date_linked'
        ,'import_key'
        ,'rowid'
    )
    ,'list' => array(
        'title' => $langs->trans('BankimportdetList')
        ,'image' => 'title_generic.png'
        ,'picto_precedent' => '<'
        ,'picto_suivant' => '>'
        ,'noheader' => 0
        ,'messageNothing' => $langs->trans('NoBankimportdet')
        ,'picto_search' => img_picto('','search.png', '', 0)
        ,'massactions' => array(
            'linktobank' => $langs->trans('applyTraitement')
            ,'delete' => $langs->trans('Delete')
          )
    )
    ,'title'=>array(
        'rowid' => '#'
        ,'fk_bank_account' => $langs->trans('Bank')
        ,'datev' => $langs->trans('Datev')
        ,'dateo' => $langs->trans('Dateo')
        ,'label' => $langs->trans('Label')
        ,'debit' => $langs->trans('Debit')
        ,'credit' => $langs->trans('Credit')
        ,'amount' => $langs->trans('Amount')
        ,'fk_element' => $langs->trans('Document')
        ,'num_releve' => $langs->trans('NumReleve')
        ,'fk_statut' => $langs->trans('Status')
        ,'fk_user_modif' => $langs->trans('EditBy')
        ,'fk_user_author' => $langs->trans('Author')
        ,'date_linked' => $langs->trans('LinkedDate')
        ,'import_key' => $langs->trans('ImportKey')
        ,'note' => $langs->trans('Note')
    )
    ,'eval'=>array(
        'fk_statut' => '_convertStatus(\'@val@\', \'@note@\')'
        ,'debit' => '_convertAmount(\'@amount@\',\'debit\')'
        ,'credit' => '_convertAmount(\'@amount@\',\'credit\')'
        ,'amount' => '_convertAmount(\'@val@\')'
        ,'fk_user_modif' => '_getUserNomUrl(\'@val@\')'
        ,'fk_user_author' => '_getUserNomUrl(\'@val@\')'
        ,'date_linked' => '_getDateLinked(\'@bank@\', \'@val@\')'
        ,'fk_element' => '_documents(\'@fk_element@\', \'@element@\')'
        
    )
));
$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
$formcore->end_form();
llxFooter('');

function _convertStatus($fk_status = 0, $note = '')
{
    global $form;
    
    $class = '';
    
    if($fk_status == BankImportDet::STATUS_DISABLE)
    {
        $class = 'label label-danger';
    }
    elseif($fk_status == BankImportDet::STATUS_DRAFT || $fk_status == BankImportDet::STATUS_DRAFT_AUTO)
    {
        $class = 'label label-default';
    }
    elseif($fk_status == BankImportDet::STATUS_LINKED)
    {
        $class = 'label label-info';
    }
    
    
    
    // Sanitize tooltip
    return '<span class="'.$class.'" >'.$form->textwithtooltip( Bankimportdet::translateTypeConst($fk_status) , $note,2,1,img_help(1,'')).'</span>';

}

function _getObjectNomUrl($ref)
{
    global $db;
    $o = new Bankimportdet($db);
    $res = $o->load('', $ref);
    if ($res > 0)
    {
        return $o->getNomUrl(1);
    }
    return '';
}


function _getUserNomUrl($fk_user = 0)
{
    if(empty($fk_user)) return '';
    
    global $db;
    $u = new User($db);
    if ($u->fetch($fk_user) > 0)
    {
        return $u->getNomUrl(1);
    }
    return '';
}

function _convertAmount($amount , $type = 'amount'){
    
   
    
    if($type=='credit')
    {
        $html = $amount>0?price(abs($amount)):'';
    }
    elseif($type=='debit')
    {
        $html = $amount<0?price(abs($amount)):'';
    }
    else
    {
        $html = price($amount);
    }
    
    $class = 'price-positive';
    if($amount < 0)
    {
        $class = 'price-negative';
    }
        
    return '<span class="price '.$class.'" >'.$html.'</span>';
}

function _getDateLinked($fk_bank = 0, $timeStamp = '')
{
    if(empty($fk_bank)) return '';
    
    return dol_print_date($timeStamp);
}

$checkboxLoopCount = 0;
function _checkbox($id=0){
    global $checkboxLoopCount;
    if(empty($id)) return '';
    $checkboxLoopCount ++;
    $checked = 'unchecked';
    if(isset($_REQUEST['order_pt']) && in_array($id, $_REQUEST['order_pt'])){$checked = 'checked';}
    return '<input type="checkbox" class="order-checkbox" name="order_pt['.$checkboxLoopCount.']" data-order="'.(int)$id.'" '.$checked.'" value="'.(int)$id.'" '.$checked.' />';
}

function _documents($fk_element = 0, $element = '')
{
    global $db;
    BankImportDet::staticGetDocument($db, $fk_element, $element);
}

function _getBankAccountList($statut=0,$filtre='')
{
    global $db;
    
    $array = array();
    
    $sql = "SELECT rowid, label";
    $sql.= " FROM ".MAIN_DB_PREFIX."bank_account";
    $sql.= " WHERE entity IN (".getEntity('bank_account').")";
    if ($statut != 2) $sql.= " AND clos = '".$statut."'";
    if ($filtre) $sql.=" AND ".$filtre;
    $sql.= " ORDER BY label";
    
    $result = $db->query($sql);
    if ($result)
    {
        while($obj = $db->fetch_object($result))
        {
            $array[$obj->id] = $obj->label;
        }
    }
    
    return $array;
}
