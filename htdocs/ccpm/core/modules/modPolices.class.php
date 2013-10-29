<?php
/* Copyright (C) 2013      Peter Fontaine <contact@peterfontaine.fr>
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
 * 	\defgroup   ccpm     Module Polices
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/ccpm/core/modules directory.
 *  \file       htdocs/ccpm/core/modules/modPolices.class.php
 *  \ingroup    ccpm
 *  \brief      Description and activation file for module Polices
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module Polices
 */
class modPolices extends DolibarrModules
{
    function __construct($db)
    {
        global $conf, $langs;

        $this->db = $db;
        $this->numero = 120002;
        $this->rights_class = 'ccpm_polices';
        $this->family = "products";
        $this->name = preg_replace('/^mod/i','',get_class($this));
        $this->description = "Description of module Polices";
        $this->version = '0.1';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->special = 3;
        $this->picto='generic';
        $this->dirs = array("/polices/temp");
        $this->config_page_url = array("setuppolices.php@ccpm");
        $this->hidden = false;			// A condition to hide module
        $this->depends = array("modCieAssurances");		// List of modules id that must be enabled if this module is enabled
        $this->requiredby = array();	// List of modules id to disable if this one is disabled
        $this->conflictwith = array();	// List of modules id this module is in conflict with
        $this->phpmin = array(5,0);					// Minimum version of PHP required by module
        $this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
        $this->langfiles = array("polices@ccpm");
        $this->const = array();
        $this->tabs = array();
        if (! isset($conf->polices->enabled))
        {
            $conf->polices=new stdClass();
            $conf->polices->enabled=0;
        }
        $this->dictionnaries=array();
        $this->boxes = array();
        $this->rights = array();
        $r=0;
        $this->menu = array();			// List of menus to add
        $r=0;
    }

    function init($options='')
    {
        $sql = array();

        $result=$this->_load_tables('/ccpm/sql/polices/');

        return $this->_init($sql, $options);
    }

    function remove($options='')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }
}