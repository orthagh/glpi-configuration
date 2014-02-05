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
   public $dohistory = true;

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
   function canPurgeItem() {
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
    * @param  int $ID : id of the configuration
    * @param  array $options 
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

      
      // find all inheritances for this configuration
      $actives = array();
      if (!$this->isNewId($this->getID())) {
         $compconf_compconf = new ComputerConfiguration_ComputerConfiguration;
         $found_inheritance = $compconf_compconf->find("computerconfigurations_id_1 = ".
                                                        $this->getID());
         foreach ($found_inheritance as $computerconfigurations_id => $inheritance_options) {
            $actives[] = $inheritance_options['computerconfigurations_id_2'];
         }
      }
      
      // find all configuration to display dropdown of inheritance
      $where = "";
      if (!$this->isNewId($this->getID())) {
         $where = "id != ".$this->getID();
      }
      $found_configurations = $this->find($where);
      $inheritance_options = array();
      foreach ($found_configurations as $computerconfigurations_id => $computerconfigurations) {
         $inheritance_options[$computerconfigurations_id] = $computerconfigurations['name'];
      }

      // display dropdown of inheritance
      Dropdown::showFromArray('_inheritance', $inheritance_options, array('values'   => $actives,
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

      $itemtype = "Computer";

      $p = array();
      
      // load saved criterias
      if (!empty($this->fields['criteria'])) {
         parse_str($this->fields['criteria'], $criteria);
         $p['criteria'] = $criteria;
      }
      if (!empty($this->fields['metacriteria'])) {
         parse_str($this->fields['metacriteria'], $metacriteria);
         $p['metacriteria'] = $metacriteria;
      }

      //manage sessions
      $glpisearch_session = $_SESSION['glpisearch'];
      unset($_SESSION['glpisearch']);
      $p = Search::manageParams($itemtype, $p);

      //show generic search form (duplicated from Search class)
      echo "<form name='searchformComputerConfigurationCriteria' method='post'>";
      echo "<input type='hidden' name='id' value='".$this->getID()."'>";   
      
      // add tow hidden fields to permit delete of (meta)criteria
      echo "<input type='hidden' name='criteria' value=''>";     
      echo "<input type='hidden' name='metacriteria' value=''>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>"._n('Criterion', 'Criteria', 2)."</th></tr>";
      echo "<tr><td>";

      echo "<div id='searchcriterias'>";
      $nbsearchcountvar = 'nbcriteria'.strtolower($itemtype).mt_rand();
      $nbmetasearchcountvar = 'nbmetacriteria'.strtolower($itemtype).mt_rand();
      $searchcriteriatableid = 'criteriatable'.strtolower($itemtype).mt_rand();
      // init criteria count
      $js = "var $nbsearchcountvar=".count($p['criteria']).";";
      $js .= "var $nbmetasearchcountvar=".count($p['metacriteria']).";";
      echo Html::scriptBlock($js);

      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";

      echo "<table class='tab_format' id='$searchcriteriatableid'>";

      // Display normal search parameters
      for ($i=0 ; $i<count($p['criteria']) ; $i++) {
         $_POST['itemtype'] = $itemtype;
         $_POST['num'] = $i ;
         include(GLPI_ROOT.'/ajax/searchrow.php');
      }

      $metanames = array();
      $linked =  Search::getMetaItemtypeAvailable($itemtype);
      
      if (is_array($linked) && (count($linked) > 0)) {
         for ($i=0 ; $i<count($p['metacriteria']) ; $i++) {

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

      Html::closeForm();
   }

   /**
    * display tab content, list of computer associated to the current configuration
    * @return nothing, display a table
    */
   function showComputers() {
      global $CFG_GLPI;
      
      // get all computers who match criteria (with inheritance)
      $computers_mismatch = array();
      $criteria_computers = self::getComputerFromCriteria($this->getID(), $computers_mismatch);

      // search and display all computers associated to this configuration (and check if they match criteria)
      $computers_id_list = self::getListofComputersID($this->getID());

      $rand = mt_rand();

      $classname = "ComputerConfiguration_Computer";
      $massiveactionparams
         = array('container'        => 'mass'.$classname.$rand,
                 'specific_actions' => array('MassiveAction'.MassiveAction::CLASS_ACTION_SEPARATOR.
                                                'purge' => _x('button', 'Delete permanently')));

      Html::openMassiveActionsForm('mass'.$classname.$rand);
      Html::showMassiveActions($massiveactionparams);
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr>";
      echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.$classname.$rand)."</th>";
      echo "<th>".__('name')."</th>";
      echo "<th>".__('Results')."</th>";
      echo "<th>".__('Result details')."</th>";
      echo "</tr>";
      $computer = new Computer;
      $configuration = new self;
      foreach ($computers_id_list as $ccompconf_comps_id => $computers_id) {
         $computer->getFromDB($computers_id);
         echo "<tr>";
   
         // display massive actions checkboxes
         echo "<td>";
         Html::showMassiveActionCheckBox($classname, $ccompconf_comps_id);
         echo "</td>";

         echo "<td>".$computer->getLink(array('comments' => true))."</td>";

         // check if current computer match saved criterias
         if (isset($criteria_computers[$computers_id])) {
            $pic = "greenbutton.png";
            $title = __('Yes');
         } else {
            $pic = "redbutton.png";
            $title = __('No');
         }
         echo "<td><img src='".$CFG_GLPI['root_doc']."/pics/$pic' title='$title'></td>";

         //for mismatch computers, display the configuration who trigger
         echo "<td>";
         if (!isset($criteria_computers[$computers_id])) {
            if (isset($computers_mismatch[$computers_id])) {
               $computerconfigurations_id = $computers_mismatch[$computers_id];
            } else {
               $computerconfigurations_id = $this->getID();
            }
            $configuration->getFromDB($computerconfigurations_id);
            echo $configuration->getLink(array('comments' => true));
         }
         echo "</td>";
         echo "</tr>";
      }
      echo "</table>";  
      $massiveactionparams['ontop'] =false;
      Html::showMassiveActions($massiveactionparams);  
   }

   function prepareInputForAdd($input) {
      if (isset($input['_inheritance'])) {
         $input = $this->saveInheritance($input);
      }
      return $input;
   }

   function prepareInputForUpdate($input) {

      //serialize search parameters
      if (isset($input['criteria']) && is_array($input['criteria'])) {
         $input['criteria'] = http_build_query($input['criteria']);
      }

      if (isset($input['metacriteria']) && is_array($input['metacriteria'])) {
         $input['metacriteria'] = http_build_query($input['metacriteria']);
      }

      if (isset($input['_inheritance'])) {
         $input = $this->saveInheritance($input);
      }

      return $input;
   }

   function saveInheritance($input) {
      global $DB;

      //clear all old inheritance for this configuration
      $DB->query("DELETE FROM glpi_computerconfigurations_computerconfigurations 
                         WHERE computerconfigurations_id_1 = ".$input['id']);

      //add new inheritance
      $compconf_compconf = new ComputerConfiguration_ComputerConfiguration;
      foreach ($input['_inheritance'] as $inheritance_options) {
         $compconf_compconf->add(array('computerconfigurations_id_1' => $input['id'], 
                                       'computerconfigurations_id_2' => $inheritance_options));
      }

   }

   /**
    * Retrieve the id of computers associated to this configuration
    * @param  int $computerconfigurations_id : id of the configuration
    * @param  string $filter: - none : no filter
    *                         - match: computers who match criteria,
    *                         - notmatch : computers who not match criteria]
    * @return array : array of computers_id 
    */
   static function getListofComputersID($computerconfigurations_id, $filter = 'none') {
      $compconf_comp = new ComputerConfiguration_Computer;
      $found_comp = $compconf_comp->find("computerconfigurations_id = $computerconfigurations_id");
      $listofcomputers_id = array();
      foreach ($found_comp as $comp) {
         $listofcomputers_id[$comp['id']] = $comp['computers_id'];
      }

      if ($filter === "none") {
         return $listofcomputers_id;
      }

      $computers_criteria = self::getComputerFromCriteria($computerconfigurations_id);
      if ($filter === "match") {
         return array_intersect($listofcomputers_id, $computers_criteria);
      }

      if ($filter === "notmatch") {
         return array_diff($listofcomputers_id, $computers_criteria);
      }
      
      return false;
   }

   /**
    * Return list of computer who match configuration
    * @param  int $computerconfigurations_id :id of the configuration
    * @param  array $computers_mismatch : output param who reference wich configuration 
    *                                       from inheritance mismatch each computer
    * @return array : list of computers_id
    */
   static function getComputerFromCriteria($computerconfigurations_id, &$computers_mismatch = array()) {
      $configuration = new self;
      $configuration->getFromDB($computerconfigurations_id);
      
      // default parameter for search engine
      $p['sort']         = '';
      $p['list_limit']   = 999999999999; // how to get all ?
      $p['is_deleted']   = 0;
      $p['criteria']     = array();
      $p['metacriteria'] = array();
      $p['all_search']   = false;
      $p['no_search']    = false;

      // load saved criterias
      if (!empty($configuration->fields['criteria'])) {
         parse_str($configuration->fields['criteria'], $criteria);
         $p['criteria'] = $criteria;
      }
      if (!empty($configuration->fields['metacriteria'])) {
         parse_str($configuration->fields['metacriteria'], $metacriteria);
         $p['metacriteria'] = $metacriteria;
      }

      // get all computers who match criteria (return only id column)
      $datas = Search::getDatas("Computer", $p, array(1));

      $computers_list = array();
      foreach ($datas['data']['items'] as $computers_id => $row_id) {
         $computers_list[$computers_id] = $computers_id;
      }

      // find all inheritances for this configuration
      $compconf_compconf = new ComputerConfiguration_ComputerConfiguration;
      $found_inheritance = $compconf_compconf->find("computerconfigurations_id_1 = ".
                                                     $computerconfigurations_id);
      foreach ($found_inheritance as $compconf_id => $option) {
         // get all computer for current inheritance
         $computers_inheritance = self::getComputerFromCriteria($option['computerconfigurations_id_2'], 
                                                                $computers_mismatch);

         // populate computer_mismatch to reference which rule mismatch each computer
         $computers_diff = array_diff($computers_list, $computers_inheritance);
         foreach ($computers_diff as $computers_id) {
            if (!isset($computers_mismatch[$computers_id])) {
               $computers_mismatch[$computers_id] = $option['computerconfigurations_id_2'];
            }
         }

         // filter computers list with those from the inheritance
         $computers_list = array_intersect($computers_list, $computers_inheritance);
      }

      return $computers_list;
   }


   /**
    * redirect to computer search and load the saved criterias in this configuration
    * @return nothing, redirect browser
    */
   function preview() {
      parse_str($this->fields['criteria'], $criteria['criteria']);
      parse_str($this->fields['metacriteria'], $metacriteria['metacriteria']);
      $criteria = http_build_query($criteria);
      $metacriteria = http_build_query($metacriteria);
      Html::redirect("computer.php?reset=reset&$criteria&$metacriteria");
   }


}

