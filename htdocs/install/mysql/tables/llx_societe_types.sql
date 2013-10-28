-- =============================================================================
-- Copyright (C) 2013	   Peter Fontaine        <contact@peterfontaine.fr>
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
-- =============================================================================

create table llx_societe_types (
  numero     integer PRIMARY KEY,
  tms        timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  name       varchar(255) NOT NULL,
  label      varchar(255) NOT NULL,
  position   integer not null,
  status     tinyint not null default 1,
  entity     integer not null default 0,
  module     varchar(255) NOT NULL DEFAULT '';
  inmenu     integer not null default 1,
  menuid1    integer,
  menuid2    integer
)ENGINE=innodb;

