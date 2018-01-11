<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License 
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* @package indian-ihris
* @author Sovello Hildebrand <sovellohpmgani@gmail.com>
* @version v4.2
* @filesource
*/
/**
* Class iHRIS_FacilityTransferDashboard
*
* @access public
*/


class iHRIS_FacilityTransferDashboard extends I2CE_PageDashboard {
  
  /**
     * main actions for the page
     * 
     */
    public function action() {
      parent::action();
      $this->template->addHeaderLink('transfer_dashboard.css');
      $this->template->addHeaderLink('view.js');
      $this->template->addHeaderLink('staff_transfer.js');
      
    }
    
    public function addIncomingTransferAction(){
        $contentNode = $this->template->getElementById("report_view_incoming_transfer");
        if ( !$contentNode instanceof DOMNode ) {
            I2CE::raiseError( "Report view is not available for this dashboard." );
            return false;
        }
        $form = $this->template->query( ".//*[@id='limit_form']", $contentNode );
          if ( $form->length == 1 ) {
              $form = $form->item(0)->setAttribute('action', $this->page() . "?$query");
            }
        
      }
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
