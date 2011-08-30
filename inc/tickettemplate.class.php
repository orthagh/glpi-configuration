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

/// Ticket Template class
class TicketTemplate extends CommonDBTM {

   // From CommonDBTM
   public $dohistory = true;


   static function getTypeName($nb=0) {
      global $LANG;

      if ($nb>1) {
         return $LANG['job'][59];
      }
      return $LANG['job'][58];
   }


   function canCreate() {
      return Session::haveRight('tickettemplate', 'w');
   }


   function canView() {
      return Session::haveRight('tickettemplate', 'r');
   }


   function defineTabs($options=array()) {
      global $LANG, $CFG_GLPI;

      $ong = array();

      $ong['empty'] = $this->getTypeName(1);
      $this->addStandardTab(__CLASS__, $ong, $options); // ticket templates ones

      return $ong;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (Session::haveRight("tickettemplate","r")) {
         $nb = 0;
         switch ($item->getType()) {
            case __CLASS__ :
               $ong = array();

               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = countElementsInTable('glpi_tickettemplatemandatoryfields',
                                          "`tickettemplates_id` = '".$item->getID()."'");
               }
               $ong[1] = self::createTabEntry($LANG['job'][62], $nb);

               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = countElementsInTable('glpi_tickettemplatepredefinedfields',
                                          "`tickettemplates_id` = '".$item->getID()."'");
               }
               $ong[2] = self::createTabEntry($LANG['job'][61], $nb);

               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = countElementsInTable('glpi_tickettemplatehiddenfields',
                                          "`tickettemplates_id` = '".$item->getID()."'");
               }
               $ong[3] = self::createTabEntry($LANG['job'][60], $nb);
               return $ong;
               break;
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch ($item->getType()) {
         case __CLASS__ :
            switch ($tabnum) {
               case 1 :
                  break;

               case 2 :
                  break;

               case 3 :
                  break;

               case 4 :
                  break;
            }
            break;
      }
      return true;
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['common'][32];

      $tab[1]['table']         = $this->getTable();
      $tab[1]['field']         = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = $this->getType();
      $tab[1]['massiveaction'] = false;

      $tab[2]['table']         = $this->getTable();
      $tab[2]['field']         = 'is_helpdeskvisible';
      $tab[2]['name']          = $LANG['tracking'][39];
      $tab[2]['datatype']      = 'bool';

      $tab[3]['table']         = $this->getTable();
      $tab[3]['field']         = 'is_default';
      $tab[3]['name']          = $LANG['job'][28];
      $tab[3]['datatype']      = 'bool';
      $tab[3]['massiveaction'] = false;

      $tab[16]['table']     = $this->getTable();
      $tab[16]['field']     = 'comment';
      $tab[16]['name']      = $LANG['common'][25];
      $tab[16]['datatype']  = 'text';

      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = $LANG['entity'][0];
      $tab[80]['massiveaction'] = false;

      $tab[86]['table']    = $this->getTable();
      $tab[86]['field']    = 'is_recursive';
      $tab[86]['name']     = $LANG['entity'][9];
      $tab[86]['datatype'] = 'bool';

      return $tab;
   }

   /**
    * Print the version form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target for the Form
    *     - computers_id ID of the computer for add process
    *
    * @return true if displayed  false if item not found or not right to display
   **/
   function showForm($ID, $options=array()) {
      global $CFG_GLPI,$LANG;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['common'][16]."&nbsp;:</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td><td rowspan='3' class='middle'>".$LANG['common'][25]."&nbsp;:</td>";
      echo "<td  rowspan='3' >";
      echo "<textarea cols='45' rows='5' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>\n";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['tracking'][39]."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("is_helpdeskvisible",$this->fields["is_helpdeskvisible"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".$LANG['job'][28]."&nbsp;:</td>";
      echo "<td>";
      Dropdown::showYesNo("is_default",$this->fields["is_default"]);
      echo "</td></tr>";

      $this->showFormButtons($options);
      $this->addDivForTabs();

      return true;

   }


   /**
    * Print the computers disks
    *
    * @param $comp Computer
    * @param $withtemplate=''  boolean : Template or basic item.
    *
    * @return Nothing (call to classes members)
   **/
   static function showForComputer(Computer $comp, $withtemplate='') {
      global $DB, $LANG;

      $ID = $comp->fields['id'];

      if (!$comp->getFromDB($ID) || !$comp->can($ID, "r")) {
         return false;
      }
      $canedit = $comp->can($ID, "w");

      echo "<div class='center'>";

      $query = "SELECT `glpi_filesystems`.`name` AS fsname,
                       `glpi_computerdisks`.*
                FROM `glpi_computerdisks`
                LEFT JOIN `glpi_filesystems`
                          ON (`glpi_computerdisks`.`filesystems_id` = `glpi_filesystems`.`id`)
                WHERE (`computers_id` = '$ID')";

      if ($result=$DB->query($query)) {
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='7'>";
         if ($DB->numrows($result)==1) {
            echo $LANG['computers'][0];
         } else {
            echo $LANG['computers'][8];
         }
         echo "</th></tr>";

         if ($DB->numrows($result)) {
            echo "<tr><th>".$LANG['common'][16]."</th>";
            echo "<th>".$LANG['computers'][6]."</th>";
            echo "<th>".$LANG['computers'][5]."</th>";
            echo "<th>".$LANG['computers'][4]."</th>";
            echo "<th>".$LANG['computers'][3]."</th>";
            echo "<th>".$LANG['computers'][2]."</th>";
            echo "<th>".$LANG['computers'][1]."</th>";
            echo "</tr>";

            Session::initNavigateListItems('ComputerDisk',
                                           $LANG['help'][25]." = ".
                                             (empty($comp->fields['name']) ? "($ID)"
                                                                           : $comp->fields['name']));

            while ($data=$DB->fetch_assoc($result)) {
               echo "<tr class='tab_bg_2'>";
               if ($canedit) {
                  echo "<td><a href='computerdisk.form.php?id=".$data['id']."'>".
                             $data['name'].(empty($data['name'])?$data['id']:"")."</a></td>";
               } else {
                  echo "<td>".$data['name'].(empty($data['name'])?$data['id']:"")."</td>";
               }
               echo "<td>".$data['device']."</td>";
               echo "<td>".$data['mountpoint']."</td>";
               echo "<td>".$data['fsname']."</td>";
               echo "<td class='right'>".Html::formatNumber($data['totalsize'], false, 0)."&nbsp;".
                      $LANG['common'][82]."<span class='small_space'></span></td>";
               echo "<td class='right'>".Html::formatNumber($data['freesize'], false, 0)."&nbsp;".
                      $LANG['common'][82]."<span class='small_space'></span></td>";
               echo "<td>";
               $percent = 0;
               if ($data['totalsize']>0) {
                  $percent=round(100*$data['freesize']/$data['totalsize']);
               }
               Html::displayProgressBar('100', $percent, array('simple'       => true,
                                                               'forcepadding' => false));
               echo "</td>";

               Session::addToNavigateListItems('ComputerDisk',$data['id']);
            }

         } else {
            echo "<tr><th colspan='7'>".$LANG['search'][15]."</th></tr>";
         }

         if ($canedit &&!(!empty($withtemplate) && $withtemplate == 2)) {
            echo "<tr class='tab_bg_2'><th colspan='7'>";
            echo "<a href='computerdisk.form.php?computers_id=$ID&amp;withtemplate=".
                   $withtemplate."'>".$LANG['computers'][7]."</a></th></tr>";
         }
         echo "</table>";
      }
      echo "</div><br>";
   }

}

?>