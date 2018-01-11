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
* Class iHRIS_Module_BiharRecentForm
* 
* @access public
*/


class iHRIS_Module_BiharRecentForm extends I2CE_Module {


    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "recent_form_person_display" => "recent_form_person_display",
                );
    }

    /**
     * Add some extra display info to the recent forms.
     * @param array $data
     */
    public function recent_form_person_display( $data ) {
        $demo = I2CE_FormStorage::listDisplayFields( "demographic", array( "birth_date" ), "person|".$data['id'] );
        $demo1 = current($demo);
        if ( array_key_exists( 'birth_date', $demo1 ) ) {
            return ' - DOB: ' . $demo1['birth_date'];
        } else {
            return null;
        }
        I2CE::raiseMessage("demo: " . print_r($demo,true));
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
