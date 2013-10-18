<?php
/* Copyright (C) 2013		Peter Fontaine		<contact@peterfontaine.fr>
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
 *      \file       htdocs/societe/admin/societe_type.php
 *		\ingroup    societe
 *		\brief      Page to setup custom type of third party
 */


require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/thirdpartiestypes.class.php';


$langs->load("companies");
$langs->load("admin");
$langs->load("members");

$customtype = new ThirdPartiesTypes($db);
$form = new Form($db);

$action = GETPOST('action','alpha');
$customtypename = GETPOST('customtypename','alpha');

if (!$user->admin) accessforbidden();

/*
 * Action
 */

// Add
if ($action == 'add') {
    if ($_POST['button'] != $langs->trans("Cancel")) {
        // Check value
        if (!isset($_POST['customtypename']) || !preg_match("/^\w[a-zA-Z0-9-_]*$/",$_POST['customtypename'])) {
            $error++;
            $langs->load("errors");
            $mesg[] = $langs->trans("ErrorFieldCanNotContainSpecialCharacters",$langs->transnoentities("CustomTypeName"));
            $action = 'create';
        }

        if (!isset($_POST['customtypepos']) || !preg_match("/^\w[0-9]*$/",$_POST['customtypepos'])) {
            $error++;
            $langs->load("errors");
            $mesg[] = $langs->trans("ErrorFieldMustBeIntType",$langs->transnoentities("Position"));
            $action = 'create';
        }

        if (!isset($_POST['customtypelabel']) || trim($_POST['customtypelabel']) == '') {
            $error++;
            $langs->load("errors");
            $mesg[] = $langs->trans("ErrorFieldCanNotBeEmpty",$langs->transnoentities("Label"));
            $action = 'create';
        }

        if (!$error) {
            $result = $customtype->addCustomType($_POST['customtypename'], trim($_POST['customtypelabel']), $_POST['customtypepos']);
            if ($result > 0) {
                setEventMessage($langs->trans('SetupSaved'));
                header("Location: ".$_SERVER["PHP_SELF"]);
                exit;
            } else {
                $error++;
                setEventMessage($customtype->error,'errors');
            }
        } else {
            setEventMessage($mesg,'errors');
        }
    }
}

// Delete
if ($action == 'delete')
{
    if(isset($_GET["customtypename"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_GET["customtypename"]))
    {
        $result=$customtype->delete($_GET["customtypename"],$elementtype);
        if ($result >= 0)
        {
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else $mesg=$extrafields->error;
    }
    else
    {
        $error++;
        $langs->load("errors");
        $mesg=$langs->trans("ErrorFieldCanNotContainSpecialCharacters",$langs->transnoentities("CustomTypeName"));
    }
}

// Update
if ($action == 'update') {
    if ($_POST['button'] != $langs->trans("Cancel")) {
        if (!isset($_POST['customtypepos']) || !preg_match("/^\w[0-9]*$/",$_POST['customtypepos'])) {
            $error++;
            $langs->load("errors");
            $mesg[] = $langs->trans("ErrorFieldMustBeIntType",$langs->transnoentities("Position"));
            $action = 'edit';
        }

        if (!isset($_POST['customtypelabel']) || trim($_POST['customtypelabel']) == '') {
            $error++;
            $langs->load("errors");
            $mesg[] = $langs->trans("ErrorFieldCanNotBeEmpty",$langs->transnoentities("Label"));
            $action = 'edit';
        }

        if (!$error) {
            $result=$customtype->updateCustomType($_POST['customtypename'], $_POST['customtypepos'], $_POST['customtypelabel']);
            if ($result > 0) {
                setEventMessage($langs->trans('SetupSaved'));
                header("Location: ".$_SERVER["PHP_SELF"]);
                exit;
            } else {
                $error++;
                setEventMessage($customtype->error,'errors');
            }
        } else {
            setEventMessage($mesg,'errors');
        }
    }
}

// Enable Type
if ($action == 'enable') {
    if(isset($_GET["customtypename"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_GET["customtypename"]))
    {
        $result=$customtype->changeStatus($_GET['customtypename'], 1);
        if ($result >= 0)
        {
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else $mesg=$extrafields->error;
    }
}

// Disable Type
if ($action == 'disable') {
    if(isset($_GET["customtypename"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_GET["customtypename"]))
    {
        $result=$customtype->changeStatus($_GET['customtypename'], 0);
        if ($result >= 0)
        {
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else $mesg=$extrafields->error;
    }
}

// Enable Menu
if ($action == 'enablemenu') {
    if(isset($_GET["customtypename"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_GET["customtypename"]))
    {
        $result=$customtype->changeInMenuStatus($_GET['customtypename'], 1);
        if ($result >= 0)
        {
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else $mesg=$extrafields->error;
    }
}

// Disable Menu
if ($action == 'disablemenu') {
    if(isset($_GET["customtypename"]) && preg_match("/^\w[a-zA-Z0-9-_]*$/",$_GET["customtypename"]))
    {
        $result=$customtype->changeInMenuStatus($_GET['customtypename'], 0);
        if ($result >= 0)
        {
            header("Location: ".$_SERVER["PHP_SELF"]);
            exit;
        }
        else $mesg=$extrafields->error;
    }
}

/*
 * View
 */

$help_url='EN:Module Third Parties setup|FR:Paramétrage_du_module_Tiers';
llxHeader('',$langs->trans("CompanySetup"),$help_url);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("CompanySetup"),$linkback,'setup');

$head = societe_admin_prepare_head(null);

dol_fiche_head($head, 'customtype', $langs->trans("ThirdParty"), 0, 'company');

print $langs->trans("DefineHereThirdPartyCustomTypes")."<br>\n";
print "<br>";

print_titre($langs->trans('TypeTitre'));

print "<table summary=\"listofdolibarrtype\" class=\"noborder\" width=\"100%\">";

print '<tr class="liste_titre">';
print '<td align="center" width="80">'.$langs->trans("Position").'</td>';
// todo Supprimer la partie débug
if ($conf->global->CUSTOMTYPES_DEBUG)
    print '<td>'.$langs->trans("Numero").'</td>';
print '<td>'.$langs->trans("Nom").'</td>';
print '<td align="center" width="140">'.$langs->trans("Status").'</td>';
print '<td align="center" width="140">'.$langs->trans("MenuStatus").'</td>';
print '<td width="80">&nbsp;</td>';
print "</tr>\n";

$customtype->fetch();

$var = false;
foreach ($customtype->types_position as $key => $value) {
    $var = !$var;
    print '<tr '.$bc[$var].'>'."\n";
    print '<td align="center">'.$customtype->types_position[$key].'</td>'."\n";
    // todo Supprimer la partie débug
    if ($conf->global->CUSTOMTYPES_DEBUG)
        print '<td>'.$customtype->types_numero[$key].'</td>'."\n";
    print '<td>'.$customtype->types_label[$key].'</td>'."\n";
    if ($customtype->types_numero[$key] >= 1000000 ) {
        //print '<td align="center">'.$customtype->types_status[$key].'</td>'."\n";
        if ($customtype->types_status[$key] == 1)
            print '<td align=center><a href="'.$_SERVER['PHP_SELF'].'?action=disable&customtypename='.$key.'">'.img_picto($langs->trans("Enabled"),'switch_on').'</a></td>';
        if ($customtype->types_status[$key] == 0)
            print '<td align=center><a href="'.$_SERVER['PHP_SELF'].'?action=enable&customtypename='.$key.'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a></td>';
        if ($customtype->types_inmenu[$key] == 1)
            print '<td align=center><a href="'.$_SERVER['PHP_SELF'].'?action=disablemenu&customtypename='.$key.'">'.img_picto($langs->trans("Enabled"),'switch_on').'</a></td>';
        if ($customtype->types_inmenu[$key] == 0)
            print '<td align=center><a href="'.$_SERVER['PHP_SELF'].'?action=enablemenu&customtypename='.$key.'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a></td>';
        print '<td align="right"><a href="'.$_SERVER["PHP_SELF"].'?action=edit&customtypename='.$key.'">'.img_edit().'</a>';
        print "&nbsp; <a href=\"".$_SERVER["PHP_SELF"]."?action=delete&customtypename=$key\">".img_delete()."</a></td>\n";
    } else {
        print '<td align="center"></td>'."\n";
        print '<td align="center"></td>'."\n";
        print '<td align="right"></td>'."\n";
    }
    print '</tr>'."\n";
}

print "</table>\n";

dol_fiche_end();

// Buttons
if ($action != 'create' && $action != 'edit')
{
    print '<div class="tabsAction">';
    print "<a class=\"butAction\" href=\"".$_SERVER["PHP_SELF"]."?action=create\">".$langs->trans("NewCustomType")."</a>";
    print "</div>";
}

// Create
if ($action == 'create')
{
    print "<br>";
    print_titre($langs->trans('NewCustomType'));

    require DOL_DOCUMENT_ROOT.'/societe/tpl/admin_customtype_add.tpl.php';
}

// Edit
if ($action == 'edit')
{
    print "<br>";
    print_titre($langs->trans('EditCustomType'));

    require DOL_DOCUMENT_ROOT.'/societe/tpl/admin_customtype_edit.tpl.php';
}

llxFooter();

$db->close();

?>
