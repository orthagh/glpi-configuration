<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

/**
 * Update from 0.84.5 to 0.84.6
 *
 * @return bool for success (will die for most error)
**/
function update0845to0846() {
   global $DB, $migration;

   $updateresult     = true;
   $ADDTODISPLAYPREF = array();

   //TRANS: %s is the number of new version
   $migration->displayTitle(sprintf(__('Update to %s'), '0.84.6'));
   $migration->setVersion('0.84.6');


   $backup_tables = false;
   $newtables     = array();

   foreach ($newtables as $new_table) {
      // rename new tables if exists ?
      if (TableExists($new_table)) {
         $migration->dropTable("backup_$new_table");
         $migration->displayWarning("$new_table table already exists. ".
                                    "A backup have been done to backup_$new_table.");
         $backup_tables = true;
         $query         = $migration->renameTable("$new_table", "backup_$new_table");
      }
   }
   if ($backup_tables) {
      $migration->displayWarning("You can delete backup tables if you have no need of them.",
                                 true);
   }


   // correct entities_id in documents_items
   $query_doc_i = "UPDATE `glpi_documents_items` as `doc_i`
                   INNER JOIN `glpi_documents` as `doc`
                     ON  `doc`.`id` = `doc_i`.`documents_id`
                   SET `doc_i`.`entities_id` = `doc`.`entities_id`,
                       `doc_i`.`is_recursive` = `doc`.`is_recursive`";
   $DB->queryOrDie($query_doc_i, "0.84.6 change entities_id in documents_items");

   // must always be at the end
   $migration->executeMigration();

   return $updateresult;
}
?>