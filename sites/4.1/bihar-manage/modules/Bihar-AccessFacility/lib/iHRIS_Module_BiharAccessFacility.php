<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
* 
* This File is part of iHRIS 
* 
* iHRIS is free software; you can redistribute it and/or modify 
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
* @package iHRIS
* @subpackage Bihar
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.3
* @since v4.1.3
* @filesource 
*/ 
/** 
* Class iHRIS_Module_BiharAccessFacility
* 
* @access public
*/


class iHRIS_Module_BiharAccessFacility extends iHRIS_Module_ManageAccessFacility {


    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'get_report_module_limit_options' => 'get_report_limit_options',
                );
    }

    /**
     * Return the array of details for report limit options for this
     * module.
     * @return array
     */
    public function get_report_limit_options() {
        if ( !self::$report_limit_options 
                && !is_array( self::$report_limit_options ) ) {
            $factory = I2CE_FormFactory::instance();
            $facAccess = $factory->createContainer( "access_facility" );
            $forms = $facAccess->getField('location')->getSelectableForms();
            $fields = array();
            foreach( $forms as $form ) {
                $formObj = $factory->createContainer( $form );
                if ( $formObj instanceof I2CE_Form ) {
                    $fields[$form] = $formObj->getDisplayName();
                }
                $formObj->cleanup();
                unset( $formObj );
            }
            $facAccess->cleanup();
            unset( $facAccess );
            self::$report_limit_options = array(
                    'module' => "Bihar-AccessFacility",
                    'fields' => $fields,
                    );
        }
        return self::$report_limit_options;
    }

    /**
     * Special check to see if the user can do deputations to restrict
     * block level users
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return boolean
     */
    public function userAccessDeputation( $node, $template ) {
        // First check the main function, then if allowed access, check
        // for block level and restrict that.
        if ( $this->userAccessFacility( $node, $template ) ) {
            $access = self::getAccessLocation( $template->getUser() ); // a list of locations a user is allowed to access
            if ( is_array($access) && array_key_exists( 'county', $access )
                    && count( $access['county'] ) > 0 ) {
                // Don't give block level access deputation access.
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Template function to see if person_can_view_child_forms
     * @param DOMNode $node
     * @param I2CE_Template $template
     * @return boolean
     */
    public function userAccessFacility($node,$template) {
        // This should only be used for facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "facility_manager") {
            return false;
        }
        if ( !$template instanceof I2CE_Template) {
            return false;
        }
        if (!$node instanceof DOMNode) {
            $node = null;
        }
        if ( ! ($person = $template->getForm('person',$node)) instanceof iHRIS_Person) {
            //No person associated with this node.  so this user can have permission
            return true;
        }

        $access = self::getAccessLocation( $template->getUser() ); // a list of locations a user is allowed to access
        if ( count( $access ) == 0 ) {
            return false;
        }
        
        //look at the positions this person has had sorted by start date
        $person->populateLast( array( "person_position" => "start_date" ) );
        if ( !array_key_exists( 'person_position', $person->children ) || !is_array( $person->children['person_position'] ) || count($person->children['person_position']) == 0 ) {
            // If there is not person position then access is granted.
            return true;
        }
        $fieldWalks = array(
            'facility'=> 'location',
            'county' => 'district',
            'district'=> 'division',
            'division'=> 'region',
            'region' => 'country',
            'country' => false
            );
        $formObjs = $this->getTemplateForms($template,$node,array_keys($fieldWalks));
        $fieldWalks['position']='facility';
                
        foreach( $person->children['person_position'] as $pers_pos ) {
            if ( $pers_pos->end_date->isValid() 
                && $pers_pos->end_date->before( I2CE_Date::now() ) ) {
                // Not a current employee so access is granted.
                return true;
            }
            $position = $pers_pos->getField("position")->getMappedFormObject();
            if (!$position instanceof iHRIS_Position) {
                $no_cached = I2CE_FormFactory::instance()->createContainer( $pers_pos->getNameId(), true );
                $no_cached->populate(true);
                if ( $no_cached->end_date->isValid() 
                    && $no_cached->end_date->before( I2CE_Date::now() ) ) {
                    // Not a current employee so access is granted.
                    return true;
                }
                $position = $no_cached->getField("position")->getMappedFormObject();
                $no_cached->cleanup();
                unset($no_cached);
                if (!$position instanceof iHRIS_Position) {
                    continue;
                }
            }
            $t_formObjs = $formObjs;
            $t_formObjs['position'] =$position;
            if ($this->ValidateAccessAgainstWalkableForms($fieldWalks,$access, $t_formObjs)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Template function to see if  can_edit_database_list_position for the 
     *  @param DOMNode $node
     * @param I2CE_Template $template
     * @returns boolean
     */
    public function userAccessFacilityList($node,$template) {
        // This should only be used for facility_manager role so ignore any others.
        if ( $template->getUser()->getRole() != "facility_manager") {
            return false;
        }
        $fieldWalks = array(
            'position'=> 'facility',
            'facility'=> 'location',
            'county' => 'district',
            'district'=> 'division',
            'division'=> 'region',
            'region' => 'country',
            'country' => false
            );
        return $this->userAccessWalkableForms($node,$template,$fieldWalks);
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
