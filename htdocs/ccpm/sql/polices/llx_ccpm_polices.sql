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

CREATE TABLE llx_ccpm_polices
(
  rowid                     integer           AUTO_INCREMENT NOT NULL PRIMARY KEY,
  tms                       timestamp         DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  entity                    integer           NOT NULL DEFAULT 1,
  fk_user                   integer,
  fk_apporteur              integer,
  date_creation             timestamp         DEFAULT CURRENT_TIMESTAMP,
  date_modification         timestamp,
  date_echeance             timestamp,
  date_sortie               timestamp,
  date_effet                timestamp         DEFAULT CURRENT_TIMESTAMP,
  fk_product                integer           DEFAULT NULL,
  police_tarif              float,
  police_period_payement    integer,
  police_comm_taux          float,
  police_origine            integer,
  police_numero_int         varchar(50),
  police_numero_ext         varchar(50),
  police_data               text
) ENGINE=innodb;
