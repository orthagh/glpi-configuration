<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class ChangeTask extends CommonITILTask {


   function canCreate() {
      return (haveRight('show_my_change', '1') || haveRight('edit_all_change', '1'));
   }


   function canView() {
      return (haveRight('show_all_change', 1) || haveRight('show_my_change', 1));
   }


   function canUpdate() {
      return (haveRight('edit_all_change', 1) || haveRight('show_my_change', 1));
   }


   function canViewPrivates () {
      return true;
   }


   function canEditAll () {
      return haveRight('edit_all_change', 1);
   }


   /**
    * Is the current user have right to show the current task ?
    *
    * @return boolean
   **/
   function canViewItem() {
      return parent::canReadITILItem();
   }


   /**
    * Is the current user have right to create the current task ?
    *
    * @return boolean
   **/
   function canCreateItem() {

      if (!parent::canReadITILItem()) {
         return false;
      }

      return (haveRight("edit_all_change","1")
              || (haveRight("show_my_change","1")
                  && ($ticket->isUser(CommonITILObject::ASSIGN, getLoginUserID())
                      || (isset($_SESSION["glpigroups"])
                          && $ticket->haveAGroup(CommonITILObject::ASSIGN,
                                                 $_SESSION['glpigroups'])))));
   }


   /**
    * Is the current user have right to update the current task ?
    *
    * @return boolean
   **/
   function canUpdateItem() {

      if (!parent::canReadITILItem()) {
         return false;
      }

      if ($this->fields["users_id"] != getLoginUserID() && !haveRight('edit_all_change',1)) {
         return false;
      }

      return true;
   }


   /**
    * Is the current user have right to delete the current task ?
    *
    * @return boolean
   **/
   function canDeleteItem() {
      return $this->canUpdateItem();
   }


   /**
    * Populate the planning with planned ticket tasks
    *
    * @param $options options array must contains :
    *    - who ID of the user (0 = undefined)
    *    - who_group ID of the group of users (0 = undefined)
    *    - begin Date
    *    - end Date
    *
    * @return array of planning item
   **/
   static function populatePlanning($options=array()) {
      return parent::genericPopulatePlanning('ChangeTask', $options);
   }


   /**
    * Display a Planning Item
    *
    * @param $val Array of the item to display
    *
    * @return Already planned information
   **/
   static function getAlreadyPlannedInformation($val) {
      return parent::genericGetAlreadyPlannedInformation('ChangeTask', $val);
   }


   /**
    * Display a Planning Item
    *
    * @param $val Array of the item to display
    * @param $who ID of the user (0 if all)
    * @param $type position of the item in the time block (in, through, begin or end)
    * @param $complete complete display (more details)
    *
    * @return Nothing (display function)
   **/
   static function displayPlanningItem($val, $who, $type="", $complete=0) {
      return parent::genericDisplayPlanningItem('ChangeTask',$val, $who, $type, $complete);
   }


}

?>