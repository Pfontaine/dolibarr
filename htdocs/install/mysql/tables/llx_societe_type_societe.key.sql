-- ===================================================================
-- Copyright (C) 2013 Peter Fontaine <contact@peterfontaine.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================


ALTER TABLE llx_societe_types_societe ADD INDEX ik_societe_types_societe_socid(socid);
ALTER TABLE llx_societe_types_societe ADD INDEX ik_societe_types_societe_typid(typid);
ALTER TABLE llx_societe_types_societe ADD CONSTRAINT fk_societe_rowid FOREIGN KEY (socid) REFERENCES  llx_societe (rowid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE llx_societe_types_societe ADD CONSTRAINT fk_societe_types_numero FOREIGN KEY (typid) REFERENCES  llx_societe_types (numero) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE llx_societe_types_societe ADD UNIQUE uk_societe_type_societe_socid_typ_id(socid, typid);
