<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class IPAddress_IPNetwork : Connection between IPAddress and IPNetwork
/// @since 0.84
class IPAddress_IPNetwork extends CommonDBRelation {

   // From CommonDBRelation
   public $itemtype_1 = 'IPAddress';
   public $items_id_1 = 'ipaddresses_id';

   public $itemtype_2 = 'IPNetwork';
   public $items_id_2 = 'ipnetworks_id';


   /**
    * Update IPNetwork's dependency
    *
    * @param $network IPNetwork object
   **/
   static function linkIPAddressFromIPNetwork(IPNetwork $network) {
      global $DB;

      $linkObject    = new self();
      $linkTable     = $linkObject->getTable();
      $ipnetworks_id = $network->getID();

      // First, remove all links of the current Network
      $query = "SELECT `id`
                FROM `$linkTable`
                WHERE `ipnetworks_id` = '$ipnetworks_id'";
      foreach ($DB->request($query) as $link) {
         $linkObject->delete(array('id' => $link['id']));
      }

      // Then, look each IP address contained inside current Network
      $query = "SELECT $ipnetworks_id as ipnetworks_id, `id` as ipaddresses_id
                FROM `glpi_ipaddresses`
                WHERE ".$network->getWHEREForMatchingElement('glpi_ipaddresses', 'binary',
                                                             'version')."
                GROUP BY `id`";
      foreach ($DB->request($query) as $link) {
         $linkObject->add($link);
      }
   }

   static function addIPAddress(IPAddress $ipaddress) {

      $linkObject = new self();
      $input      = array('ipaddresses_id' => $ipaddress->getID());

      foreach (IPNetwork::searchNetworksContainingIP($ipaddress) as $ipnetworks_id) {
         $input['ipnetworks_id'] = $ipnetworks_id;
         $linkObject->add($input);
      }

   }
}
?>
