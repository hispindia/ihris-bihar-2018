<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
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
 */
/**
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the report list
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_PageDistrictStaffList extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='report_list']" );
        parent::action();
        $district = I2CE_FormFactory::instance()->createContainer( "district" );
        $district->getField("region")->setFromDB( "region|JH" );
        $districts = I2CE_List::listOptions( "district", $false, array( $district->getField("region") ) );
        foreach( $districts as $district ) {
            $name_only = str_replace( ", Jharkhand, India", "", $district['display'] );
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( urlencode( "limits:+facility_district:equals:value" ) 
                        => $district['value'] ), $node );
        }
    }
        
}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
