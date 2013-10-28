<?php
/* Copyright (C) 2013   	Peter Fontaine		<contact@peterfontaine.fr>
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
 *  \file       htdocs/core/class/thirdpartiestypes.class.php
 *  \ingroup    core
 *  \brief      File of class to manage custom third parties types
 */

/**
 * Class ThirdPartiesTypes
 */
class ThirdPartiesTypes
{
    var $db;
    var $error;

    var $types_position;
    var $types_label;
    var $types_status;
    var $types_numero;
    var $types_inmenu;
    var $types_menuid;
    var $types_module;

    var $last_numero;
    var $last_position;
    var $count;

    var $fetched;


    /**
     * Constructor
     *
     * @param   DoliDB  $db         Database Handler
     */
    function __construct($db)
    {
        $this->db = $db;
        $this->error = array();
        $this->last_numero = 1000000;
        $this->last_position = 0;
        $this->count = 0;
        $this->types_label = array();
        $this->types_position = array();
        $this->types_status = array();
        $this->types_numero = array();
        $this->types_inmenu = array();
        $this->types_menuid = array();
        $this->types_module = array();
        $this->fetched = false;
    }

    /**
     * add a new custom type
     *
     * @param   string  $name       must be unique, alpha only
     * @param   string  $label      label for custom type
     * @param   int     $position   position in list
     * @param   int     $status     1: enabled, 0: disabled
     * @param   int     $numero     unique identifier: 0-99999 Dolibarr usage only, 100000-999999 for module usage, >1000000 for everybody
     * @param   bool    $isModule   true if added by module
     * @return  int                 <0 if KO, >0 if OK
     */
    function addCustomType($name, $label, $position = 0, $status = 1, $numero = 0, $isModule = false, $modulename = '')
    {
        global $conf, $user;
        $entity=(! empty($force_entity) ? $force_entity : $conf->entity);

        $name = $this->db->escape($name);
        $label = $this->db->escape($label);
        $modulename = $this->db->escape($modulename);

        if (!$this->fetched)
            $this->fetch();

        if ($position == 0)
            $position = $this->last_position + 10;

        if (!$isModule && !$this->checkNumero($numero))
            $numero = $this->last_numero+1;

        if ($isModule && $numero < 100000) {
            $this->error[] = "Numero must be > 100000";
        }

        if ($numero == 0)
        {
            $numero = $this->last_numero+1;
        }

        // Insert new societe type
        $sql = "INSERT INTO ".MAIN_DB_PREFIX."societe_types(";
        $sql.= " name, label, position, status, numero, entity, module)";
        $sql.= " VALUES (";
        $sql.= "'".$name."', ";
        $sql.= "'".$label."', ";
        $sql.= "'".$position."', ";
        $sql.= "'".$status."', ";
        $sql.= "'".$numero."', ";
        $sql.= "'".$entity."')";
        $sql.= "'".$modulename."')";

        // Insert rights
        if (!dol_strlen($modulename)) {
            $module = 'societe';
        } else {
            $module = $this->db->escape($modulename);
        }
        $sql2 = "INSERT INTO ".MAIN_DB_PREFIX."rights_def";
        $sql2.= " (id, entity, libelle, module, type, bydefault, perms, subperms)";
        $sql2.= " VALUES ";
        $sql2.= "( ".$numero."001,".$entity.",'Créer nouvelle fiche ".$label."','".$module."','w',0,'".$name."','create'), ";
        $sql2.= "( ".$numero."002,".$entity.",'Voir fiches ".$label."','".$module."','r',0,'".$name."','view')";

        $this->db->begin();

        dol_syslog(get_class($this)."::addCustomType type sql: ".$sql);
        $resql1 = $this->db->query($sql);

        dol_syslog(get_class($this)."::addCustomType rights sql: ".$sql);
        $resql2 = $this->db->query($sql2);

        if ($resql1 && $resql2) {

            $this->types_position[$name] = $position;
            $this->types_status[$name] = $status;
            $this->types_numero[$name] = $numero;
            $this->types_label[$name] = $label;

            $this->db->commit();
            // On ajoute les menus
            if ($this->createMenu($name) < 0) {
                $this->error[] = "Can't create menus";
                return -1;
            }


            // Reload admin rights
            if (! class_exists('User')) {
                require DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
            }
            $sql3="SELECT rowid FROM ".MAIN_DB_PREFIX."user WHERE admin = 1";
            dol_syslog(get_class($this)."::insert_permissions Search all admin users sql=".$sql3);
            $resqlseladmin=$this->db->query($sql3,1);
            if ($resqlseladmin)
            {
                $num=$this->db->num_rows($resqlseladmin);
                $i=0;
                while ($i < $num)
                {
                    $obj2=$this->db->fetch_object($resqlseladmin);
                    dol_syslog(get_class($this)."::insert_permissions Add permission to user id=".$obj2->rowid);
                    $tmpuser=new User($this->db);
                    $tmpuser->fetch($obj2->rowid);
                    if (!empty($tmpuser->id)) {
                        $tmpuser->addrights($numero."001");
                        $tmpuser->addrights($numero."002");
                }
                    $i++;
                }
                if (! empty($user->admin))  // Reload permission for current user if defined
                {
                    // We reload permissions
                    $user->clearrights();
                    $user->getrights();
                }
            }

            return 1;
        } else {
            $this->db->rollback();
            $this->error[] = "SQL error ".$sql;
            return -1;
        }
    }

    /**
     * Load custom types
     *
     * @return int                  <0 if KO, >0 if OK
     */
    function fetch()
    {
        global $conf;
        $entity=(! empty($force_entity) ? $force_entity : $conf->entity);

        $sql = "SELECT * FROM ".MAIN_DB_PREFIX."societe_types";
        $sql.= " WHERE entity IN (0, ".$entity.")";
        $sql.= " ORDER BY position ASC";

        $resql = $this->db->query($sql);

        if($resql) {
            $this->count = $this->db->num_rows($resql);

            if ($this->count > 0) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $this->types_position[$obj->name] = $obj->position;
                    $this->types_label[$obj->name] = $obj->label;
                    $this->types_status[$obj->name] = $obj->status;
                    $this->types_numero[$obj->name] = $obj->numero;
                    $this->types_inmenu[$obj->name] = $obj->inmenu;
                    $this->types_menuid[$obj->name][0] = $obj->menuid1;
                    $this->types_menuid[$obj->name][1] = $obj->menuid2;
                    $this->types_module[$obj->name] = $obj->module;

                    if ($obj->numero > $this->last_numero)
                        $this->last_numero = $obj->numero;
                }

                if ($this->last_numero < 1000000)
                    $this->last_numero = 1000000;

                $this->last_position = $this->types_position[$this->count-1];

                return 1;
            }

            $this->fetched = true;
        } else {
            $this->error[] = "SQL error".dol_print_error($this->db);
            return -1;
        }
    }

    /**
     * Change Status
     *
     * @param   string   $name      unique identifier of type
     * @param   int      $status    new status 1: enable, 2: disable
     * @return  int      <0 if KO, >0 if OK
     */
    function changeStatus($name, $status)
    {
        if (!$this->fetched)
            $this->fetch();
        if (!in_array($status, array(0,1))) {
            $this->error[] = 'Status must be equal to 0 or 1';
            return -1;
        }

        $sql = "UPDATE ".MAIN_DB_PREFIX."societe_types";
        $sql.= " SET";
        $sql.= " status = '".$status."'";
        $sql.= " WHERE";
        $sql.= " name = '".$name."'";

        $resql = $this->db->query($sql);

        if ($resql) {
            if ($this->types_inmenu[$name])
                $this->changeInMenuStatus($name, 0);
            return 1;
        } else {
            $this->error[] = dol_print_error($this->db);
            return -1;
        }

    }

    /**
     * Show/Hide type in lefmenu
     *
     * @param   string   $name      unique identifier of type
     * @param   int      $status    new status 1: enable, 2: disable
     * @return  int      <0 if KO, >0 if OK
     */
    function changeInMenuStatus($name, $status)
    {
        if (!$this->fetched)
            $this->fetch();
        if (!in_array($status, array(0,1))) {
            $this->error[] = 'Status must be equal to 0 or 1';
            return -1;
        }

        $sql = "UPDATE ".MAIN_DB_PREFIX."societe_types";
        $sql.= " SET";
        $sql.= " inmenu = '".$status."'";
        $sql.= " WHERE";
        $sql.= " name = '".$name."'";

        $resql = $this->db->query($sql);

        if ($resql) {
            if ($status == 1) {
                $this->createMenu($name);
            } else {
                $this->deleteMenu($name);
            }
            return 1;
        } else {
            $this->error[] = dol_print_error($this->db);
            return -1;
        }

    }

    /**
     * Update type
     *
     * @param   string   $name      Unique identifier of type
     * @param   int      $position  New Position
     * @param   string   $label     New label
     * @return  int                 <0 if KO, >0 if OK
     */
    function updateCustomType($name, $position, $label)
    {
        $this->fetch();

        $name = $this->db->escape($name);
        $label = $this->db->escape($label);

        $sql = "UPDATE ".MAIN_DB_PREFIX."societe_types";
        $sql.= " SET";
        $sql.= " position = '".$position."',";
        $sql.= " label = '".$label."'";
        $sql.= " WHERE";
        $sql.= " name = '".$name."'";

        $sql2 = "UPDATE ".MAIN_DB_PREFIX."rights_def";
        $sql2.= " SET";
        $sql2.= " libelle = 'Créer nouvelle fiche ".$label."'";
        $sql2.= " WHERE";
        $sql2.= " id = ".$this->types_numero[$name]."001";

        $sql3 = "UPDATE ".MAIN_DB_PREFIX."rights_def";
        $sql3.= " SET";
        $sql3.= " libelle = 'Voir fiches ".$label."'";
        $sql3.= " WHERE";
        $sql3.= " id = ".$this->types_numero[$name]."002";

        $this->db->begin();

        dol_syslog(get_class($this)."::updateCustomType sql=".$sql);
        $resql1=$this->db->query($sql);

        dol_syslog(get_class($this)."::updateCustomType rights créer sql=".$sql2);
        $resql2=$this->db->query($sql2);

        dol_syslog(get_class($this)."::updateCustomType rights voir sql=".$sql3);
        $resql3=$this->db->query($sql3);

        if ($resql1 && $resql2 && $resql3) {
            if ($this->types_inmenu[$name]) {
                $this->types_position[$name] = $position;
                $this->types_label[$name] = $label;
                if (
                $this->deleteMenu($name) &&
                $this->createMenu($name)) {
                    $this->db->commit();
                    return 1;
                } else {
                    $this->db->rollback();
                    $this->error[] = "Can't update menus";
                    return -1;
                }
            } else {
                $this->db->commit();
                return 1;
            }
        } else {
            $this->db->rollback();
            $this->error[] = dol_print_error($this->db);
            return -1;
        }

    }

    /**
     * Delete type
     *
     * @param   string    $name             Unique identifier of type
     * @return  int                         <0 if KO, >0 if OK
     */
    function delete($name)
    {
        $this->fetch();

        $name = $this->db->escape($name);

        $sql = "DELETE FROM ".MAIN_DB_PREFIX."societe_types";
        $sql.= " WHERE name = '".$name."'";

        $sql2 = "DELETE FROM ".MAIN_DB_PREFIX."rights_def";
        $sql2.= " WHERE id IN (".$this->types_numero[$name]."001, ".$this->types_numero[$name]."002)";

        $sql3 = "DELETE FROM ".MAIN_DB_PREFIX."user_rights";
        $sql3.= " WHERE fk_id IN (".$this->types_numero[$name]."001, ".$this->types_numero[$name]."002)";

        $sql4 = "DELETE FROM ".MAIN_DB_PREFIX."usergroup_rights";
        $sql4.= " WHERE fk_id IN (".$this->types_numero[$name]."001, ".$this->types_numero[$name]."002)";

        $this->db->begin();

        dol_syslog(get_class($this)."::delete_label sql=".$sql);
        $resql1=$this->db->query($sql);

        dol_syslog(get_class($this)."::delete_label rights sql=".$sql2);
        $resql2=$this->db->query($sql2);

        dol_syslog(get_class($this)."::delete_label user rights sql=".$sql3);
        $resql3=$this->db->query($sql3);

        dol_syslog(get_class($this)."::delete_label user groups rights sql=".$sql4);
        $resql4=$this->db->query($sql4);

        if ($resql1 && $resql2 && $resql3 && $resql4)
        {
            if ($this->types_inmenu[$name]) {
               if (!$this->deleteMenu($name)) {
                   $this->db->rollback();
                   $this->error[] = "Can't delete menus";
                   return -1;
               }
            }
            $this->db->commit();
            return 1;
        }
        else
        {
            $this->db->rollback();
            $this->error[] = dol_print_error($this->db);
            return -1;
        }
    }

    /**
     * Add type to third party
     *
     * @param   int     $socid      id of third party
     * @param   string  $type       name of type
     * @return  int                 <0 if KO, >0 if OK
     */
    function addType($socid, $type) {
        if (!$this->fetched)
            $this->fetch();

        $numero = $this->types_numero[$type];

        if ($numero >= 0) {
            $sql = "INSERT INTO ".MAIN_DB_PREFIX."societe_types_societe";
            $sql.= "(socid,typid) VALUES(";
            $sql.= $socid.", ";
            $sql.= $numero.")";

            $resql = $this->db->query($sql);

            if (!$resql) {
                $this->error[] = "SQL Error: ".$this->db->error;
                return -1;
            }

            return 1;
        } else {
            $this->error[] = 'Type not found';
            return -1;
        }
    }

    /**
     * Delete Type from third party
     *
     * @param   int     $socid      third party id
     * @param   string  $type       name of type
     * @return  int                 <0 if KO, >0 if OK
     */
    function delType($socid, $type) {
        if ($this->fetched)
            $this->fetch();

        $numero = $this->types_numero[$type];

        if ($numero >= 0) {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX."societe_types_societe";
            $sql.= " WHERE";
            $sql.= " socid = ".$socid;
            $sql.= " AND typid = ".$numero;

            $resql = $this->db->query($sql);

            if (!$resql) {
                $this->error[] = "SQL Error: ".$this->db->error;
                return -1;
            }

            return 1;
        } else {
            $this->error[] = 'Type not found';
            return -1;
        }
    }

    /**
     * Compare to list of types
     *
     * @param   array   $types1     first list
     * @param   array   $types2     second list
     * @return  bool                true if types are equals, else false
     */
    function isEqual($types1, $types2) {
        if (!(count($types1) == count($types2))) {
            return false;
        }

        foreach ($types1 as $type) {
            if (!in_array($type, $types2))
                return false;
        }

        return true;
    }

    /**
     * get types of one third party
     *
     * @param   int         $socid      third party id
     * @return  array|int               <0 if KO, array of types if OK
     */
    function getTypes($socid) {
        $sql = "SELECT t.name AS name FROM";
        $sql.= " ".MAIN_DB_PREFIX."societe_types AS t,";
        $sql.= " ".MAIN_DB_PREFIX."societe_types_societe AS ts";
        $sql.= " WHERE ts.socid = ".$socid;
        $sql.= " AND ts.typid = t.numero";

        $resql = $this->db->query($sql);

        if ($resql) {
            $count = $this->db->num_rows($resql);
            $types = array();
            if ($count > 0) {
                while ($obj = $this->db->fetch_object($resql)) {
                    $types[] = $obj->name;
                }
            }

            return $types;
        } else {
            $this->error[] = "SQL Error: ".$this->db->error;
            return -1;
        }
    }

    /**
     * Update Types
     * @param   int     $socid      Third party id
     * @param   array   $types      array of types
     * @return  bool                <0 if KO, >0 if OK
     */
    function updateTypes($socid, $types)
    {
        if (!$this->fetched)
            $this->fetch();
        $this->db->begin();
        $sql = "DELETE FROM ".MAIN_DB_PREFIX."societe_types_societe WHERE socid = ".$socid;
        $resql1 = $this->db->query($sql);
        $sql2 = "INSERT INTO ".MAIN_DB_PREFIX."societe_types_societe(socid, typid) VALUES";
        $c = count($types) - 1;
        foreach ($types as $type) {
            $sql2.= "(".$socid.", ".$this->types_numero[$type].")";
            if ($c != 0) $sql2.=", ";
            $c--;
        }
        $resql2 = $this->db->query($sql2);

        if ($resql1 && $resql2) {
            $this->db->commit();
            return 1;
        } else {
            $this->error = $sql.$sql2;
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Check if type exists
     *
     * @param   string  $type       name of type
     * @return  bool                true if type exists, false otherwise
     */
    function exists($type)
    {
        if (!$this->fetched)
            $this->fetch();

        $numero = $this->types_numero[$type];

        if ($numero >= 0)
            return true;
        else
            return false;
    }

    /**
     * get allowed types for user
     *
     * @param   User    $user           User
     * @param   string  $rightstype     'view' or 'create'
     * @param   bool    $selecthtml     return a select html code
     * @param   bool    $multiple       allow multiple selection in select
     * @param   array   $preselect      array of preselected types in select
     * @param   string  $nameselect     name of select element
     * @return  array|int|string        return array of types if selecthtml = false
     *                                  return html code if selecthtml = true
     *                                  return 0 if error
     */
    function getAllowedTypes($user, $rightstype = 'view', $selecthtml = false, $multiple = false, $preselect = array(), $nameselect= "search_type" )
    {
        global $conf;

        if (!$this->fetched)
            $this->fetch();

        if (!in_array($rightstype, array('view','create'))) return 0;

        $types = array('aucun');

        if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $types[] = 'client';
        if (empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $types[] = 'prospect';
        if ($conf->fournisseur->enabled) $types[] = 'fournisseur';
        foreach ($this->types_label as $key => $val)
        {
            if ($user->rights->societe->$key->$rightstype) {
                $types[] = $key;
            }
        }
        // todo add support of module types

        if ($selecthtml) {
            $html = '<select name="'.$nameselect.($multiple?'[]':'').'" class="flat" '.($multiple?'multiple=multiple':'').'>';
            foreach ($types as $type) {
                $html.= '<option value="'.$this->types_numero[$type].'" '.(in_array($type,$preselect)?'selected=selected':'').'>'.$this->types_label[$type].'</option>';
            }
            $html.= '</select>';

            return $html;
        }
        else return $types;
    }

    /**
     * get type from numero
     *
     * @param   int     $numero     numero
     * @return  int|string          name of type if OK, 0 if KO
     */
    function getTypeFromNumero($numero)
    {
        if (!$this->fetched)
            $this->fetch();

        $name = '';

        if (in_array($numero, array('f','c','p'))) {
            switch($numero) {
                case 'f':
                    $name = 'fournisseur';
                    break;
                case 'c':
                    $name = 'client';
                    break;
                case 'p':
                    $name = 'prospect';
                    break;
                default:
            }
        }
        else {
            foreach($this->types_numero as $key => $val)
            {
                if ($val == $numero)
                    $name = $key;
            }
        }

        if ($name != '')
            return $name;
        else
            return 0;
    }

    /**
     * get types array from numeros
     *
     * @param   string|array    $numeros        list of numero (in array or comma separated)
     * @return  array|int                       array of types names if OK, 0 if KO
     */
    function getTypesFromNumeros($numeros)
    {
        if (!$this->fetched)
            $this->fetch();

        if (!is_array($numeros))
            $numeros = explode(',',$numeros);

        $names = array();

        foreach ($numeros as $numero)
        {
            if ($tmpname = $this->getTypeFromNumero($numero))
                $names[] = $tmpname;
        }

        if (count($names))
            return $names;
        else
            return 0;
    }

    private function checkNumero($numero)
    {
        if ($numero < 1000000)
            return false;
        else
            return true;
    }

    private function createMenu($name)
    {
        global $conf, $user, $langs;

        require_once DOL_DOCUMENT_ROOT.'/core/class/menubase.class.php';

        if ($this->types_module[$name] == '')
            $module = 'societe';
        else
            $module = $this->types_module[$name];

        $menu = new Menubase($this->db, 'all');
        $menu->module = $module;
        $menu->type = 'left';
        $menu->mainmenu = 'companies';
        $menu->leftmenu = $name;
        $menu->fk_menu = -1;
        $menu->fk_mainmenu = 'companies';
        $menu->fk_leftmenu = 'thirdparties';
        $menu->position = $this->types_position[$name];
        $menu->url = '/societe/societe.php?search_type='.$this->types_numero[$name];
        $menu->titre = $langs->transnoentities("List")." ".strtolower($this->types_label[$name]);
        $menu->perms = '$user->rights->'.$module.'->'.$name.'->view';
        $menu->enabled = '$user->rights->'.$module.'->'.$name.'->view';
        $menu->user = 0;

        $idmenu = $menu->create($user);

        if ($idmenu > 0) {
            $this->types_menuid[$name][0] = $idmenu;
            $menu2 = new Menubase($this->db, 'all');
            $menu2->module = $module;
            $menu2->type = 'left';
            $menu2->mainmenu = '';
            $menu2->leftmenu = '';
            $menu2->fk_menu = -1;
            $menu2->fk_mainmenu = 'companies';
            $menu2->fk_leftmenu = $name;
            $menu2->position = $this->types_position[$name];
            $menu2->url = '/societe/soc.php?action=create&type='.$this->types_numero[$name];
            $menu2->titre = $langs->transnoentities("Add")." ".strtolower($this->types_label[$name]);
            $menu2->perms = '$user->rights->'.$module.'->'.$name.'->create';
            $menu2->enabled = '$user->rights->'.$module.'->'.$name.'->view';
            $menu2->user = 0;

            $idmenu2 = $menu2->create($user);

            if ($idmenu2 < 0) {
                return -1;
            } else {
                $this->types_menuid[$name][1] = $idmenu2;

                $sql = "UPDATE ".MAIN_DB_PREFIX.'societe_types';
                $sql.= " SET";
                $sql.= " menuid1 = ".$idmenu.", ";
                $sql.= " menuid2 = ".$idmenu2;
                $sql.= " WHERE name = '".$name."'";

                $res = $this->db->query($sql);

                if ($res)
                    return $idmenu;
                else
                    $this->error[] = "Erreur bizare ".$sql;
                    return -1;
            }
        } else
            return -1;
    }

    private function deleteMenu($name)
    {
        global $conf, $user;

        require_once DOL_DOCUMENT_ROOT.'/core/class/menubase.class.php';

        $menu = new Menubase($this->db);
        $menu->fetch($this->types_menuid[$name][0]);
        if ($menu->delete($user) > 0) {
            $menu->fetch(($this->types_menuid[$name][1]));
            if ($menu->delete($user) > 0) {
                return 1;
            } else
                return -1;
        } else
            return -1;

    }
}

?>
