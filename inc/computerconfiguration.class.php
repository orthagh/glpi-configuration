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
class ComputerConfiguration extends CommonDropdown {

   // From CommonDBTM
   public $dohistory                   = true;

   /**
    * Name of the type
    *
    * @param $nb  integer  number of item in the type (default 0)
   **/
   static function getTypeName($nb=0) {
      return _n('Computer Configuration', 'Computer Configurations', $nb);
   }

   //TODO : define a right for this item
   static function canCreate() {
      return true;
   }
   static function canUpdate() {
      return true;
   }
   static function canDelete() {
      return true;
   }
   static function canPurge() {
      return true;
   }
   static function canView() {
      return true;
   }

   function defineTabs($options=array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab("ComputerConfiguration_Computer", $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      switch ($item->getType()) {
         case __CLASS__:
            $ong = array();
            $ong[1] = _n('Criterion', 'Criteria', 2);
            return $ong;
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch ($item->getType()) {
         case __CLASS__ :
            switch ($tabnum) {
               case 1 :
                  $item->showCriteria();
                  return true;
            }
      }
      return false;
   }

   /**
    * Configuration principal form
    * @param  [int] $ID     id of the configurationj
    * @param  [array]  $options 
    * @return nothing, display a form
    */
   function showForm($ID, $options = array()) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $canedit = $this->can($ID, UPDATE);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_2'><td>".__('Name')."</td>";
      echo "<td>";
      if ($canedit) {
         Html::autocompletionTextField($this, "name");
      } else {
         echo $this->fields['name'];
      }
      echo "</td>\n";

      echo "<td rowspan='2'>". __('Comments')."</td>";
      echo "<td rowspan='2'>
            <textarea cols='55' rows='5' name='comment' >".$this->fields["comment"];
      echo "</textarea></td></tr>\n";

      echo "<tr class='tab_bg_2'><td>".__('Inheritance')."</td>";
      echo "<td>";
      $actives = array();
      Dropdown::showFromArray('_inheritance', array(), array('values'   => $actives,
                                                            'multiple' => true,
                                                            'readonly' => !$canedit));
      echo "</td>\n";

      echo "<td>";
      echo "</td>";
      echo "</tr>";



      $this->showFormButtons($options);

      return true;
   }


   /**
    * Display tab content
    * This function adapted from Search::showGenericSearch with controls removed
    * @return nothing, display a seach form
    */
   function showCriteria() {
      global $CFG_GLPI;

      // Default values of parameters
      $p['sort']         = '';
      $p['is_deleted']   = 0;
      $p['criteria']     = array();
      $p['metacriteria'] = array();
      $itemtype = "Computer";
      

      // save search session variables
      $glpisearch_session = $_SESSION['glpisearch'];
      $glpisearchcount_session = $_SESSION['glpisearchcount'];
      $glpisearchcount2_session = $_SESSION['glpisearchcount2'];
      unset($_SESSION['glpisearch'], $_SESSION['glpisearchcount'], $_SESSION['glpisearchcount2']);

      // load saved criterias
      if (!empty($this->fields['criteria'])) {
         parse_str($this->fields['criteria'], $criteria);
         $_GET['criteria'] = $p['criteria'] = $criteria;
      }
      if (!empty($this->fields['metacriteria'])) {
         parse_str($this->fields['metacriteria'], $metacriteria);
         $_GET['metacriteria'] = $p['metacriteria'] = $metacriteria;
      }

      //store in session GET values
      Search::manageGetValues($itemtype);

      //show generic search form (duplicated from Search class)
      echo "<form name='searchformComputerConfigurationCriteria' method='post'>";
      echo "<input type='hidden' name='id' value='".$this->getID()."'>";     
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>"._n('Criterion', 'Criteria', 2)."</th></tr>";
      echo "<tr><td>";

      echo "<div id='searchcriterias'>";
      $nbsearchcountvar = 'nbcriteria'.strtolower($itemtype).mt_rand();
      $nbmetasearchcountvar = 'nbmetacriteria'.strtolower($itemtype).mt_rand();
      $searchcriteriatableid = 'criteriatable'.strtolower($itemtype).mt_rand();
      // init criteria count
      $js = "var $nbsearchcountvar=".$_SESSION["glpisearchcount"][$itemtype].";";
      $js .= "var $nbmetasearchcountvar=".$_SESSION["glpisearchcount2"][$itemtype].";";
      echo Html::scriptBlock($js);

      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";

      echo "<table class='tab_format' id='$searchcriteriatableid'>";

      // Display normal search parameters
      for ($i=0 ; $i<$_SESSION["glpisearchcount"][$itemtype] ; $i++) {
         $_POST['itemtype'] = $itemtype;
         $_POST['num'] = $i ;
         include(GLPI_ROOT.'/ajax/searchrow.php');
      }

      $metanames = array();
      $linked =  Search::getMetaItemtypeAvailable($itemtype);
      
      if (is_array($linked) && (count($linked) > 0)) {
         for ($i=0 ; $i<$_SESSION["glpisearchcount2"][$itemtype] ; $i++) {

            $_POST['itemtype'] = $itemtype;
            $_POST['num'] = $i ;
            include(GLPI_ROOT.'/ajax/searchmetarow.php');
         }
      }
      echo "</table>\n";
      echo "</td></tr>";
      echo "</table>\n";

      // For dropdown
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";

      // Reset to start when submit new search
      echo "<input type='hidden' name='start' value='0'>";
      echo "</div>";

      // add new button to search form (to store and preview)
      echo "<div class='center'>";
      echo "<input type='submit' value=\" "._sx('button', 'Save')." \" class='submit' name='update'>&nbsp;";
      echo "<input type='submit' value=\" ".__('Preview')." \" class='submit' name='preview'>";
      echo "</div>";
      echo "</td></tr></table>";

      //restore search session variables
      $_SESSION['glpisearch'] = $glpisearch_session;
      $_SESSION['glpisearchcount'] = $glpisearchcount_session;
      $_SESSION['glpisearchcount2'] = $glpisearchcount2_session;

      Html::closeForm();
   }

   /**
    * display tab content, list of computer associated to the current configuration
    * @return nothing, display a table
    */
   function showComputers() {
      //search computers associated to this configuration
      $computers_id_list = self::getListofComputersID($this->getID());

      //search computers who match stored criteria
      $p['sort']         = '';
      $p['is_deleted']   = 0;
      $p['criteria']     = array();
      $p['metacriteria'] = array();
      $p['all_search']   = false;
      $p['no_search']    = false;

      // load saved criterias
      if (!empty($this->fields['criteria'])) {
         parse_str($this->fields['criteria'], $criteria);
         $p['criteria'] = $criteria;
      }
      if (!empty($this->fields['metacriteria'])) {
         parse_str($this->fields['metacriteria'], $metacriteria);
         $p['metacriteria'] = $metacriteria;
      }

            
      $datas = Search::getDatas("Computer", $p/*, array(1, 31)*/);
      Html::printCleanArray($datas);

      //display computers (and check if they match criteria)
      echo "<ul class=''>";
      foreach ($computers_id_list as $computers_id) {
         $name = Dropdown::getDropdownName("glpi_computers", $computers_id);
         echo "<li><a href='computer.form.php?id=$computers_id'>$name</a></li>";
      }
      echo "</ul>";
      
   }


   function prepareInputForUpdate($input) {

      //serialize search parameters
      if (isset($input['criteria']) && is_array($input['criteria'])) {
         $input['criteria'] = http_build_query($input['criteria']);
      }

      if (isset($input['metacriteria']) && is_array($input['metacriteria'])) {
         $input['metacriteria'] = http_build_query($input['metacriteria']);
      }

      return $input;
   }

   /**
    * Retrieve the id of computers associated to this configuration
    * @param  [int] $computerconfigurations_id  id of the configuration
    * @param  [string] $filter                    [none : no filter
    *                                            match: computers who match criteria,
    *                                            notmatch : computers who not match criteria]
    * @return [array] array of computers_id 
    */
   static function getListofComputersID($computerconfigurations_id, $filter = 'none') {
      $compconf_comp = new ComputerConfiguration_Computer;
      $found_comp = $compconf_comp->find("computerconfigurations_id = $computerconfigurations_id");
      $listofcomputers_id = array();
      foreach ($found_comp as $comp) {
         $listofcomputers_id[] = $comp['id'];
      }

      if ($filter === "none") {
         return $listofcomputers_id;
      }

      //TODO : filter param
      
      return false;
   }


}

