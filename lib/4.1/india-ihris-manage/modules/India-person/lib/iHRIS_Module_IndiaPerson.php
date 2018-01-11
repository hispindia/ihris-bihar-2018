<?php
/**
* © Copyright 2010,2011 IntraHealth International, Inc.
* 
* This File is part of iHRIS India Manage
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
* @package iHRIS
* @subpackage IndiaManage
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.0.9
* @since v4.0.9
* @filesource
*/
/**
* Class iHRIS_Module_IndiaPerson
*
* @access public
*/


class iHRIS_Module_IndiaPerson extends I2CE_Module {

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_person_field_surname" => "validate_form_person_field_names",
                "validate_form_person_field_othername" => "validate_form_person_field_names",
                "validate_form_person_field_firstname" => "validate_form_person_field_names",
                "validate_form_person_field_residence_village" => "no_validate",
                "validate_form_person_field_residence_thana" => "validate_form_person_field_names",
                "validate_form_person_field_pin" => "validate_form_person_field_numbers",
                "validate_form_person_field_seniority_no" => "validate_form_person_field_numbers",

                );
    }

    /**
     * Validate the name fields for the person form.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_person_field_names( $formfield ) {
        $value = $formfield->getValue();
        if ( preg_match( "/[^A-Za-z ]/", $value ) > 0 ) {
            $formfield->setInvalid( "Please only use alphabet characters." );
        }
        $formfield->setValue( ucwords( strtolower($value) ) );
    }
    /**
     * Validate the number fields for the person form.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_person_field_numbers( $formfield ) {
        $value = $formfield->getValue();
        if ( preg_match( "/[^0-9 ]/", $value ) > 0 ) {
            $formfield->setInvalid( "Please only use numeric value." );
        }
        $formfield->setValue( ucwords( strtolower($value) ) );
    }


    /**
     * Don't do any validation.  For use when a field no longer requires
     * any validation but used to have one.
     * @param I2CE_FormField $formfield
     */
    public function no_validate( $formfield ) {
        //do nothing
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
