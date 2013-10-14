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
?>

<!-- BEGIN PHP TEMPLATE admin_customtype_edit.tpl.php -->

<form action="<?php echo $_SERVER["PHP_SELF"]; ?>?customtypename=<?php echo $customtypename; ?>" method="post">
    <input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>">
    <input type="hidden" name="customtypename" value="<?php echo $customtypename; ?>">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="rowid" value="<?php echo $rowid ?>">

    <table summary="listofattributes" class="border centpercent">

        <!-- Position -->
        <tr><td class="fieldrequired"><?php echo $langs->trans("Position"); ?></td><td class="valeur"><input type="text" name="customtypepos" size="5" value="<?php  echo $customtype->customtypes_position[$customtypename];  ?>"></td></tr>
        <!-- Label -->
        <tr><td class="fieldrequired"><?php echo $langs->trans("Label"); ?></td><td class="valeur"><input type="text" name="customtypelabel" size="40" value="<?php echo $customtype->customtypes_label[$customtypename]; ?>"></td></tr>
        <!-- Code -->
        <tr><td class="fieldrequired"><?php echo $langs->trans("CustomTypeName"); ?></td><td class="valeur"><?php echo $customtypename; ?></td></tr>

    </table>

    <div align="center"><br><input type="submit" name="button" class="button" value="<?php echo $langs->trans("Save"); ?>"> &nbsp;
        <input type="submit" name="button" class="button" value="<?php echo $langs->trans("Cancel"); ?>"></div>

</form>

<!-- END PHP TEMPLATE admin_customtype_add.tpl.php -->
