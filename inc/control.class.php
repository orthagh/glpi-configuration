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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Control class
 *
 * @since version 0.85
**/
class Control extends CommonGLPI {


   static function getTypeName($nb=0) {
      return _n('Check', 'Checks', $nb);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   /**
    * @see CommonGLPI::getAdditionalMenuOptions()
   **/
   static function getAdditionalMenuOptions() {

      if (static::canView()) {
         $options['FieldUnicity']['title']           = __('Fields unicity');
         $options['FieldUnicity']['page']            = '/front/fieldunicity.php';
         $options['FieldUnicity']['links']['add']    = '/front/fieldunicity.form.php';
         $options['FieldUnicity']['links']['search'] = '/front/fieldunicity.php';

         $options['ComputerConfiguration']['title']           = __('Computer Configuration');
         $options['ComputerConfiguration']['page']            = '/front/computerconfiguration.php';
         $options['ComputerConfiguration']['links']['add']    = '/front/computerconfiguration.form.php';
         $options['ComputerConfiguration']['links']['search'] = '/front/computerconfiguration.php';

         return $options;
      }
      return false;
   }
}
?>
