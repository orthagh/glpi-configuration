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

if (in_array('--help', $_SERVER['argv'])) {
   die("usage: ".$_SERVER['argv'][0]."  [ --upgrade | --force ] [ --optimize ] [ --fr ]\n");
}

chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', realpath('..'));
}

include_once (GLPI_ROOT . "/inc/autoload.function.php");
include_once (GLPI_ROOT . "/inc/db.function.php");
include_once (GLPI_CONFIG_DIR . "/config_db.php");
Config::detectRootDoc();

// Old itemtype for compatibility
define("GENERAL_TYPE",         0);
define("COMPUTER_TYPE",        1);
define("NETWORKING_TYPE",      2);
define("PRINTER_TYPE",         3);
define("MONITOR_TYPE",         4);
define("PERIPHERAL_TYPE",      5);
define("SOFTWARE_TYPE",        6);
define("CONTACT_TYPE",         7);
define("ENTERPRISE_TYPE",      8);
define("INFOCOM_TYPE",         9);
define("CONTRACT_TYPE",       10);
define("CARTRIDGEITEM_TYPE",  11);
define("TYPEDOC_TYPE",        12);
define("DOCUMENT_TYPE",       13);
define("KNOWBASE_TYPE",       14);
define("USER_TYPE",           15);
define("TRACKING_TYPE",       16);
define("CONSUMABLEITEM_TYPE", 17);
define("CONSUMABLE_TYPE",     18);
define("CARTRIDGE_TYPE",      19);
define("SOFTWARELICENSE_TYPE",20);
define("LINK_TYPE",           21);
define("STATE_TYPE",          22);
define("PHONE_TYPE",          23);
define("DEVICE_TYPE",         24);
define("REMINDER_TYPE",       25);
define("STAT_TYPE",           26);
define("GROUP_TYPE",          27);
define("ENTITY_TYPE",         28);
define("RESERVATION_TYPE",    29);
define("AUTHMAIL_TYPE",       30);
define("AUTHLDAP_TYPE",       31);
define("OCSNG_TYPE",          32);
define("REGISTRY_TYPE",       33);
define("PROFILE_TYPE",        34);
define("MAILGATE_TYPE",       35);
define("RULE_TYPE",           36);
define("TRANSFER_TYPE",       37);
define("BOOKMARK_TYPE",       38);
define("SOFTWAREVERSION_TYPE",39);
define("PLUGIN_TYPE",         40);
define("COMPUTERDISK_TYPE",   41);
define("NETWORKING_PORT_TYPE",42);
define("FOLLOWUP_TYPE",       43);
define("BUDGET_TYPE",         44);

// Old devicetype for compatibility
define("MOBOARD_DEVICE",   1);
define("PROCESSOR_DEVICE", 2);
define("RAM_DEVICE",       3);
define("HDD_DEVICE",       4);
define("NETWORK_DEVICE",   5);
define("DRIVE_DEVICE",     6);
define("CONTROL_DEVICE",   7);
define("GFX_DEVICE",       8);
define("SND_DEVICE",       9);
define("PCI_DEVICE",      10);
define("CASE_DEVICE",     11);
define("POWER_DEVICE",    12);


if (is_writable(GLPI_SESSION_DIR)) {
   Session::setPath();
} else {
   die("Can't write in ".GLPI_SESSION_DIR."\n");
}
Session::start();

// Init debug variable
Toolbox::setDebugMode(Session::DEBUG_MODE, 0, 0, 1);
$_SESSION['glpilanguage']  = (in_array('--fr', $_SERVER['argv']) ? 'fr_FR' : 'en_GB');

Session::loadLanguage();

$DB = new DB();
if (!$DB->connected) {
   die("No DB connection\n");
}

/* ----------------------------------------------------------------- */
/**
 * Extends class Migration to redefine display mode
**/
class CliMigration extends Migration {


   function __construct($ver) {
      $this->deb = time();
      $this->setVersion($ver);
   }


   function setVersion($ver) {
      $this->version = $ver;
   }


   function displayMessage ($msg) {

      $msg .= " (".Html::clean(Html::timestampToString(time()-$this->deb)).")";
      echo str_pad($msg, 100)."\r";
   }


   function displayTitle($title) {
      echo "\n".str_pad(" $title ", 100, '=', STR_PAD_BOTH)."\n";
   }


   function displayWarning($msg, $red=false) {

      if ($red) {
         $msg = "** $msg";
      }
      echo str_pad($msg, 100)."\n";
   }
}

/*---------------------------------------------------------------------*/

if (!TableExists("glpi_configs")) {
   // Get current version
   // Use language from session, even if sometime not reliable
   $query = "SELECT `version`, 'language'
             FROM `glpi_config`";
   $result = $DB->queryOrDie($query, "get current version");

   $current_version = trim($DB->result($result,0,0));
   $glpilanguage    = trim($DB->result($result,0,1));
// < 0.85
} else if (FieldExists('glpi_configs', 'version')) {
   // Get current version and language
   $query = "SELECT `version`, `language`
             FROM `glpi_configs`";
   $result = $DB->queryOrDie($query, "get current version");

   $current_version = trim($DB->result($result,0,0));
   $glpilanguage    = trim($DB->result($result,0,1));
} else {
   $configurationValues = Config::getConfigurationValues('core', array('version', 'language'));

   $current_version     = $configurationValues['version'];
   $glpilanguage        = $configurationValues['language'];
}

$migration = new CliMigration($current_version);

$migration->displayWarning("Current GLPI Data version: $current_version");
$migration->displayWarning("Current GLPI Code version: ".GLPI_VERSION);
$migration->displayWarning("Default GLPI Language: $glpilanguage");


// To prevent problem of execution time
ini_set("max_execution_time", "0");

if (version_compare($current_version, GLPI_VERSION, 'ne')
    && !in_array('--upgrade', $_SERVER['argv'])) {
   die("Upgrade required\n");
}

switch ($current_version) {
   case "0.72.3" :
   case "0.72.4" :
      include("../install/update_0723_078.php");
      update0723to078();

   case "0.78" :
      include("../install/update_078_0781.php");
      update078to0781();

   case "0.78.1" :
      include("../install/update_0781_0782.php");
      update0781to0782();

   case "0.78.2":
   case "0.78.3":
   case "0.78.4":
   case "0.78.5":
      include("../install/update_0782_080.php");
      update0782to080();

   case "0.80" :
      include("../install/update_080_0801.php");
      update080to0801();
      // nobreak;

   case "0.80.1" :
   case "0.80.2" :
      include("../install/update_0801_0803.php");
      update0801to0803();
      // nobreak;

   case "0.80.3" :
   case "0.80.4" :
   case "0.80.5" :
   case "0.80.6" :
   case "0.80.61" :
   case "0.80.7" :
      include("../install/update_0803_083.php");
      update0803to083();
      // nobreak;

   case "0.83" :
      include("../install/update_083_0831.php");
      update083to0831();
      // nobreak;

   case "0.83.1" :
   case "0.83.2" :
      include("../install/update_0831_0833.php");
      update0831to0833();

   case "0.83.3" :
   case "0.83.31" :
   case "0.83.4" :
   case "0.83.5" :
   case "0.83.6" :
   case "0.83.7" :
   case "0.83.8" :
   case "0.83.9" :
   case "0.83.91" :
      include("../install/update_0831_084.php");
      update0831to084();

   case "0.84" :
      include("../install/update_084_0841.php");
      update084to0841();
      
   case "0.84.1" :
   case "0.84.2" :
      include("../install/update_0841_0843.php");
      update0841to0843();

   case "0.84.3" :
      include("../install/update_0843_0844.php");
      update0843to0844();
      
   case "0.84.4" :
      include("../install/update_084_085.php");
      update084to085();

   case GLPI_VERSION :
      break;

   default :
      die("Unsupported version ($current_version)\n");
}

if (version_compare($current_version, GLPI_VERSION, 'ne')) {

   // Update version number and default langage and new version_founded ---- LEAVE AT THE END
   Config::setConfigurationValues('core', array('version'             => GLPI_VERSION,
                                                'founded_new_version' => ''));

   // Update process desactivate all plugins
   $plugin = new Plugin();
   $plugin->unactivateAll();

   $migration->displayWarning("\nMigration Done.");

} else if (in_array('--force', $_SERVER['argv'])) {

   include("../install/update_084_085.php");
   update084to085();

   $migration->displayWarning("\nForced migration Done.");

} else {
   $migration->displayWarning("No migration needed.");
}


if (in_array('--optimize', $_SERVER['argv'])) {

   DBmysql::optimize_tables($migration);
   $migration->displayWarning("Optimize done.");
}
?>
