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

    public function fetch($id)
    {
        global $langs;
        $langs->load('polices_error@ccpm');

        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."_ccpm_polices as p";
        $sql.= " WHERE p.id = ".$id;

        dol_syslog(get_class($this).'::fetch id = '.$id.' sql: '.$sql);

        $result = $this->db->query($sql);

        if (!$result) {
            $this->error++;
            $this->errors[] = $this->db->lasterror;
            return 0;
        } else {
            $num_rows = $this->db->num_rows($result);
            if ($num_rows == 1) {
                $obj = $this->db->fetch_object($result);

                $this->id                       = $obj->rowid;
                $this->tms                      = $obj->tms;
                $this->entity                   = $obj->entity;
                $this->fk_user                  = $obj->fk_user;
                $this->fk_apporteur             = $obj->fk_apporteur;
                $this->date_creation            = $obj->date_creation;
                $this->date_modification        = $obj->date_modification;
                $this->date_echeance            = $obj->date_echeance;
                $this->date_sortie              = $obj->date_sortie;
                $this->date_effet               = $obj->date_effet;
                $this->fk_product               = $obj->fk_product;
                $this->police_tarif             = $obj->police_tarif;
                $this->police_period_payement   = $obj->police_period_payement;
                $this->police_comm_taux         = $obj->police_comm_taux;
                $this->police_origine           = $obj->police_origine;
                $this->police_numero_int        = $obj->police_numero_int;
                $this->police_numero_ext        = $obj->police_numero_ext;
                $this->police_data              = $obj->police_data;

            } else {
                $this->error++;
                $this->errors[] = $langs->trans("CantGetPolicesId", $id);
                return 0;
            }
        }

        return 1;
    }

    public function search($terme = '', $one = false)
    {
        global $conf, $user, $langs;

        $terme = trim($terme);

        $sql = "SELECT p.rowid FROM ".MAIN_DB_PREFIX."ccpm_polices as p";
        if (!$user->rights->ccpm_polices->polices->voirtous) $sql.= ", ".MAIN_DB_PREFIX."ccpm_polices_commerciaux as pc, ".MAIN_DB_PREFIX."ccpm_polices_apporteurs as pa";
        $sql.= " WHERE ";
        $sql.= " p.entity IN (".getEntity('police', 1)."), ";
        if ($terme != '') {
            $sql.= " AND (p.rowid = ".$terme;
            $sql.= " OR p.police_numero_int LIKE '%".$terme."%'";
            $sql.= " OR p.police_numero_ext LIKE '%".$terme."%')";
        }
        if (!$user->rights->ccpm_polices->polices->voirtous) {
            $sql.= " AND (p.fk_user = pc.user OR p.fk_user = pa.user)";
            $sql.= " AND p.rowid = pc.policeid";
            $sql.= " AND p.rowid = pa.policeid";
        }

        $result = $this->db->query($sql);

        if (!$result) {
            $this->error++;
            $this->errors[] = $this->db->lasterror;
            return 0;
        } else {
            $ret = array();
            $num_rows = $this->db->num_rows($result);
            if ($num_rows) {
                while ($obj = $this->db->fetch_object($result)) {
                    $police = new Polices($this->db);
                    $police->fetch($obj->rowid);
                    $ret[] = $police;
                }
            }
            elseif (!$num_rows && $one)
                return -1;

            if ($one)
                return $ret[0];
            else
                return $ret;
        }
    }

    public function delete($id)
    {
        global $user, $langs;

        $langs->load("polices_error@ccpm");

        if (!$user->rights->ccpm_polices->polices->suppression) {
            $this->error++;
            $this->errors[] = $langs->trans("NoDeleteRights");
            return 0;
        }

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."ccpm_polices";
        $sql.= " WHERE id = ".$id;

        $result = $this->db->query($sql);

        if (!$result) {
            $this->error++;
            $this->errors[] = $this->db->lasterror;
            return 0;
        }

        return 1;
    }
}

