<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * ComputerConfiguration class
**/
class ComputerConfiguration_Computer extends CommonDBTM {
   static function getTypeName($nb=0) {
      return _n('Computer', 'Computers', $nb);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      switch ($item->getType()) {
         case "ComputerConfiguration":
            $compconf_comp = new self;
            $found_comp = $compconf_comp->find("computerconfigurations_id = ".$item->getId());
            $nb = count($found_comp);
            return self::createTabEntry(self::getTypeName($nb), $nb);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch ($item->getType()) {
         case "ComputerConfiguration" :
            $item->showComputers();
            return true;
      }
      return false;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $CFG_GLPI;

      switch ($ma->getAction()) {
         case 'add' :
            ComputerConfiguration::dropdown();
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'))."</span>";
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      global $DB;

      switch ($ma->getAction()) {
         case 'add' :
            $compconf_comp = new self;
            foreach ($ids as $computers_id) {
               $compconf_comp->add(array('computerconfigurations_id' => $_POST['computerconfigurations_id'], 
                                         'computers_id'              => $computers_id));
            }
      }

      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }
}