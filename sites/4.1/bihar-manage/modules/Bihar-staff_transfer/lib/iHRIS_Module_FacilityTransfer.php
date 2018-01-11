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
* @package ihris-common
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v3.2
* @since v3.2
* @filesource
*/
/**
* Class iHRIS_Module_PersonDemographic
*
* @access public
*/


class iHRIS_Module_FacilityTransfer extends I2CE_Module {
  
    /**
     * Return the list of fuzzy methods handled by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_facility_transfer' => 'action_person_facility_transfer'
            );
    }

    /**
     * Return the hooks handled by this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'form_post_save_person_position' => 'form_post_save_person_position',
                );
    }


    /**
     * Set the facility transfer form on the view page.
     * @param iHRIS_PageView $obj
     * @return DOMNode
     */
    public function action_person_facility_transfer($obj) {
        if (!$obj instanceof iHRIS_PageView) {
            return;
        }
        return $obj->addChildForms('person_facility_transfer', 'siteContent');
    }

    /**
     * Update the transfer log when saving a person_position if it exists.
     * @param array $data The form and user saving this form
     */
    public function form_post_save_person_position( $data ) {
        $user = $data['user'];
        $form = $data['form'];
        if ( !$form instanceof iHRIS_PersonPosition ) {
            I2CE::raiseError("Invalid form passed to post save person position hook: " . get_class($form) );
            return;
        }
        $created = $form->getField("created")->getValue();
        $compare = I2CE_Date::now( I2CE_Date::DATE_TIME, time()-60 );
        if ( !$created->isBlank() && $compare->after( $created ) ) {
            return;
        }

        $position = $form->getField("position")->getValue();

        $facility_id = I2CE_FormStorage::lookupField( $position[0], $position[1], array( 'facility' ) );

        $transfer_where = array(
                'operator' => 'FIELD_LIMIT',
                'style' => 'equals',
                'field' => 'transfer_status',
                'data' => array( 'value' => 'transfer_status|pending' ),
                );
        $transfers = I2CE_FormStorage::search( 'person_facility_transfer', $form->getParent(), $transfer_where );
        foreach( $transfers as $transfer ) {
            $transObj = I2CE_FormFactory::instance()->createContainer( "person_facility_transfer|$transfer" );
            $transObj->populate();
            if ( $transObj->getField("facility")->getDBValue() == $facility_id ) {
                $transObj->getField("transfer_status")->setFromDB( "transfer_status|transfered" );
            } else {
                $transObj->getField("transfer_status")->setFromDB( "transfer_status|canceled" );
            }
            $transObj->save( $user );
            $transObj->cleanup();
            unset( $transObj );
        }

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
