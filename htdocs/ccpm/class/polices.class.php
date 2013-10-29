<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2013      Peter Fontaine       <contact@peterfontaine.fr>
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
 *  \file       htdocs/ccpm/class/polices.class.php
 *  \ingroup    ccpm
 *  \brief
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");

class Polices extends CommonObject
{
    var $db;
    var $error;
    var $errors             = array();
    var $element            = 'police';
    var $table_element      = 'polices';

    var $id;
    var $tms;
    var $entity;
    var $fk_user;
    var $fk_apporteur;

    var $date_creation;
    var $date_modification;
    var $date_echeance;
    var $date_sortie;
    var $date_effet;

    var $fk_product;
    var $police_tarif;
    var $police_period_payement;
    var $police_comm_taux;
    var $police_origine;
    var $police_numero_int;
    var $police_numero_ext;
    var $police_data;

    function __construct($db)
    {
        $this->db = $db;
    }
}