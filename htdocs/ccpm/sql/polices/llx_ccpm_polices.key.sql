-- ===================================================================
-- Copyright (C) 2013      Peter Fontaine       <contact@peterfontaine.fr>
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

ALTER TABLE llx_ccpm_polices ADD INDEX idx_ccpm_polices_fk_user (fk_user);
ALTER TABLE llx_ccpm_polices ADD INDEX idx_ccpm_polices_fk_apporteur (fk_apporteur);
ALTER TABLE llx_ccpm_polices ADD INDEX idx_ccpm_polices_fk_product (fk_product);

ALTER TABLE llx_ccpm_polices ADD CONSTRAINT fk_ccpm_polices_fk_id FOREIGN KEY (police_origine) REFERENCES llx_ccpm_polices (rowid);

-- todo ajouter foreign key product
