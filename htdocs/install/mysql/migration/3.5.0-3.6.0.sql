--
-- Be carefull to requests order.
-- This file must be loaded by calling /install/index.php page
-- when current version is 3.5.0 or higher.
--
-- To rename a table:       ALTER TABLE llx_table RENAME TO llx_table_new;
-- To add a column:         ALTER TABLE llx_table ADD COLUMN newcol varchar(60) NOT NULL DEFAULT '0' AFTER existingcol;
-- To rename a column:      ALTER TABLE llx_table CHANGE COLUMN oldname newname varchar(60);
-- To drop a column:        ALTER TABLE llx_table DROP COLUMN oldname;
-- To change type of field: ALTER TABLE llx_table MODIFY COLUMN name varchar(60);
-- To drop a foreign key:   ALTER TABLE llx_table DROP FOREIGN KEY fk_name;
-- To restrict request to Mysql version x.y use -- VMYSQLx.y
-- To restrict request to Pgsql version x.y use -- VPGSQLx.y
-- To make pk to be auto increment (mysql):   VMYSQL4.3 ALTER TABLE llx_c_shipment_mode CHANGE COLUMN rowid rowid INTEGER NOT NULL AUTO_INCREMENT;
-- To make pk to be auto increment (postgres) VPGSQL8.2 NOT POSSIBLE. MUST DELETE/CREATE TABLE

-- -- VPGSQL8.2 DELETE FROM llx_usergroup_user      WHERE fk_user      NOT IN (SELECT rowid from llx_user);
-- -- VMYSQL4.1 DELETE FROM llx_usergroup_user      WHERE fk_usergroup NOT IN (SELECT rowid from llx_usergroup);


-- Third Parties types
CREATE TABLE llx_societe_types (
  numero     INTEGER UNIQUE PRIMARY KEY,
  tms        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  name       VARCHAR(255) NOT NULL,
  label      VARCHAR(255) NOT NULL,
  position   INTEGER NOT NULL,
  status     TINYINT NOT NULL DEFAULT 1,
  entity     INTEGER NOT NULL DEFAULT 0,
  inmenu     INTEGER NOT NULL DEFAULT 1,
  menuid1    INTEGER,
  menuid2    INTEGER
)ENGINE=innodb;
create table llx_societe_types_societe (
  rowid      INTEGER AUTO_INCREMENT PRIMARY KEY,
  socid      INTEGER NOT NULL,
  typid      INTEGER NOT NULL
)ENGINE=innodb;
ALTER TABLE llx_societe_types ADD UNIQUE uk_societe_types_name(name);
ALTER TABLE llx_societe_types_societe ADD INDEX ik_societe_types_societe_socid(socid);
ALTER TABLE llx_societe_types_societe ADD INDEX ik_societe_types_societe_typid(typid);
ALTER TABLE llx_societe_types_societe ADD CONSTRAINT fk_societe_rowid FOREIGN KEY (socid) REFERENCES  llx_societe (rowid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE llx_societe_types_societe ADD CONSTRAINT fk_societe_types_numero FOREIGN KEY (typid) REFERENCES  llx_societe_types (numero) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE llx_societe_types_societe ADD UNIQUE uk_societe_type_societe_socid_typ_id(socid, typid);
INSERT INTO llx_societe_types (numero, name, label, position, status) VALUES (0, "aucun", "Aucun", 1, 1);
INSERT INTO llx_societe_types (numero, name, label, position, status) VALUES (1, "client", "Client", 2, 1);
INSERT INTO llx_societe_types (numero, name, label, position, status) VALUES (2, "prospect", "Prospect", 3, 1);
INSERT INTO llx_societe_types (numero, name, label, position, status) VALUES (3, "fournisseur", "Fournisseur", 4, 1);