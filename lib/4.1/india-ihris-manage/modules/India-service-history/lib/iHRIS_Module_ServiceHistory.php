<?php
/**
* © Copyright 2010-2011 IntraHealth International, Inc.
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
* @package ihris-manage-india
* @author Robin Tonk <robin.hisp@gmail.com>
* @version v3.2
* @since v3.2
* @filesource
*/
/**
* Class iHRIS_Module_ServiceHistory
*
* @access public
*/


class iHRIS_Module_ServiceHistory extends I2CE_Module {	

    /** 
     * @var boolean A flag to determine if migrate needs to be called during the upgrade method.
     */

      
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_service_history' => 'action_service_history' 
            );
    }


    public function action_service_history($obj) {
        $obj->addChildForms('service_history');
        return true;
    }

 public static function getHooks() {
        return array(
                "validate_form_service_history" => "validate_form_service_history",
                );
    }

 /**
     * Validate the dates in the service_history form.
     * @param I2CE_Form $form
     */
    public function validate_form_service_history( $form ) {
        if ( $form->from_date->isValid() && $form->to_date->isValid() ) {
            if ( $form->from_date->compare( $form->to_date ) < 0 ) {
                $form->getField('to_date')->setInvalid( "The to date must be after the from date." );
            }
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
