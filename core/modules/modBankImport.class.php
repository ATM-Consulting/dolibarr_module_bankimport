<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   bankimport     Module BankImport
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/bankimport/core/modules directory.
 *  \file       htdocs/bankimport/core/modules/modBankImport.class.php
 *  \ingroup    bankimport
 *  \brief      Description and activation file for module BankImport
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module BankImport
 */
class modBankImport extends DolibarrModules
{
	/**
	 *   Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *   @param      DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 104020; // 104000 to 104999 for ATM CONSULTING
		$this->editor_name = 'ATM Consulting';
		$this->editor_url = 'https://www.atm-consulting.fr';
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'bankimport';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = 'ATM-Consulting - financial';
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Allow to import csv files to reconcile bank accounts";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version

		$this->version = '2.8.4';

		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 2;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='module.svg@bankimport';

		$this->module_parts = [];

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/bankimport/temp");
		$this->dirs = [];

		// Config pages. Put here list of php page, stored into bankimport/admin directory, to use to setup module.
		$this->config_page_url = ["bankimport_setup.php@bankimport"];

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = [];		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = [];	// List of modules id to disable if this one is disabled
		$this->conflictwith = [];	// List of modules id this module is in conflict with
		$this->phpmin = [7,0];					// Minimum version of PHP required by module
		$this->need_dolibarr_version = [16,0];	// Minimum version of Dolibarr required by module
		$this->langfiles = ["bankimport@bankimport"];

		// Url to the file with your last numberversion of this module
		require_once __DIR__ . '/../../class/techatm.class.php';
		$this->url_last_version = \bankimport\TechATM::getLastModuleVersionUrl($this);


		$this->const = [
			0 => ['BANKIMPORT_MAPPING', 'chaine', 'date;label;debit;credit', 'CSV file mapping for bank import', 1, 'current', 0],
			1 => ['BANKIMPORT_SEPARATOR', 'chaine', ';', 'Data separator for bank import', 1, 'current', 0],
			2 => ['BANKIMPORT_DATE_FORMAT', 'chaine', 'd/m/Y', 'Date format in CSV file', 1, 'current', 0],
			3 => ['BANKIMPORT_HEADER', 'bool', true, 'File header line presence', 1, 'current', 0]
		];

        $this->tabs = [
			'bank:+bankimport_statement:'.$langs->trans('AccountStatements').':bankimport@bankimport:isModEnabled(\'bankimport\') && getDolGlobalString("BANKIMPORT_HISTORY_IMPORT"):/bankimport/releve.php?account=__ID__'
			,'bank:-statement:NU:isModEnabled(\'bankimport\') && getDolGlobalString("BANKIMPORT_HISTORY_IMPORT")'
		];

        // Dictionaries
	    if (!isModEnabled('bankimport'))
        {
        	$conf->bankimport=new stdClass();
        	$conf->bankimport->enabled=0;
        }
		$this->dictionaries=[];

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = [];			// List of boxes
		// Example:
		//$this->boxes=array(array(0=>array('file'=>'myboxa.php','note'=>'','enabledbydefaulton'=>'Home'),1=>array('file'=>'myboxb.php','note'=>''),2=>array('file'=>'myboxc.php','note'=>'')););

		// Permissions
		$this->rights = [];		// Permission array used by this module
		$r=0;


		$this->rights[$r][0] = 104021; 				// Permission id (must not be already used)
		$this->rights[$r][1] = 'Import bancaire';	// Permission label
		$this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->hasRight('permkey', 'level1', 'level2'))
		$r++;


		// Main menu entries
		$this->menu = [];			// List of menus to add
		$r=0;


		$this->menu[$r]= ['fk_menu'=>'fk_mainmenu=bank',		    // Use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
									'type'=>'left',			                // This is a Left menu entry
									'titre'=>'LeftMenuBankImport',
									'mainmenu'=>'bank',
									'leftmenu'=>'bankimport',
									'url'=>'/bankimport/import.php',
									'langs'=>'bankimport@bankimport',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
									'position'=>100,
									'enabled'=>'isModEnabled(\'bankimport\')',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
									'perms'=>'$user->rights->bankimport->read',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
									'target'=>'',
									'user'=>0];				                // 0=Menu for internal users, 1=external users, 2=both
		$r++;


		// Exports
		$r=1;


	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function init($options='')
	{
		$sql = [];

		define('INC_FROM_DOLIBARR',true);

		dol_include_once('/bankimport/config.php');
		dol_include_once('/bankimport/script/create-maj-base.php');

		$result=$this->_load_tables('/bankimport/sql/');

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
	 *      Remove from database constants, boxes and permissions from Dolibarr database.
	 *		Data directories are not deleted
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	function remove($options='')
	{
		$sql = [];

		return $this->_remove($sql, $options);
	}

}
