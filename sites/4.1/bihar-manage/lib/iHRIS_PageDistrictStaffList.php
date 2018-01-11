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
        $page = "district";
        if ( count( $this->request_remainder ) > 0 ) {
            $page = array_shift( $this->request_remainder );
        }
        switch( $page ) {
            case "division" :
                $this->action_division();
                break;
            case "facility" :
                $this->action_facility();
                break;
            default :
                $this->action_district();
        }
    }

    /**
     * Sort function for districts
     * @param array $a
     * @param array $b
     * @return integer
     */
    protected static function sort_district( $a, $b ) {
        return strcmp( $a['display'], $b['display'] );
    }

    /**
     * Display the district list for this page.
     */
    protected function action_district() {
        $this->template->setDisplayDataImmediate( "district_staff_title", "District Staff Lists" );
        $district = I2CE_FormFactory::instance()->createContainer( "district" );
        $reg_field = $district->getField("region");
        $reg_field->setFromDB("region|BR");

        $districts = I2CE_List::listOptions( "district", false, array( $reg_field ) );
        usort( $districts, "self::sort_district" );
        foreach( $districts as $district ) {
            $name_only = str_replace( ", Bihar, India", "", $district['display'] );
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( urlencode( "limits:+facility_district:equals:value" ) 
                        => $district['value'] ), $node );
        }
    }

    /**
     * Display the division list for this page.
     */
    protected function action_division() {
        $this->template->setDisplayDataImmediate( "district_staff_title", "Division Staff Lists" );
        $division = I2CE_FormFactory::instance()->createContainer( "division" );
        $reg_field = $division->getField("region");
        $reg_field->setFromDB("region|BR");
        $divisions = I2CE_List::listOptions( "division", false, array( $reg_field ) );
        foreach( $divisions as $division ) {
            $name_only = str_replace( ", Bihar, India", "", $division['display'] );
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( urlencode( "limits:+facility_division:equals:value" ) 
                        => $division['value'] ), $node );
        }
    }

    /**
     * Display facility list by selecting division, district, then facility.
     */
    protected function action_facility() {
        $this->template->setDisplayDataImmediate( "district_staff_title", "Facility Staff Lists" );
        $page = "top";
        if ( count( $this->request_remainder ) > 0 ) {
            $page = array_shift( $this->request_remainder );
        }
        switch( $page ) {
            case "district" :
                $this->action_facility_district();
                break;
            case "block" :
                $this->action_facility_block();
                break;
            default :
                $this->action_facility_division();
        }
     }
         
    /**
     * Display the division list for the facilities.
     */
    protected function action_facility_division() {
        $division = I2CE_FormFactory::instance()->createContainer( "division" );
        $reg_field = $division->getField("region");
        $reg_field->setFromDB("region|BR");
        $divisions = I2CE_List::listOptions( "division", false, array( $reg_field ) );
        foreach( $divisions as $division ) {
            $name_only = str_replace( ", Bihar, India", "", $division['display'] );
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( "href" => "district_staff/facility/district/" . urlencode( $division['value'] ) ),
                    $node );
        }
    }

    /**
     * Display the district list for the facilities.
     */
    protected function action_facility_district() {
        if ( count( $this->request_remainder ) < 1 ) {
            $this->action_facility_division();
            return;
        }
        $division_id = array_shift( $this->request_remainder );
        $district = I2CE_FormFactory::instance()->createContainer( "district" );
        $div_field = $district->getField("division");
        $div_field->setFromDB($division_id);

        $districts = I2CE_List::listOptions( "district", false, array( $div_field ) );

        list( $div_form, $div_id ) = explode( '|', $division_id, 2 );
        $division_name = I2CE_List::lookup( $div_id, $div_form );
        $this->template->setDisplayDataImmediate( "district_staff_subtitle", $division_name );

        foreach( $districts as $district ) {
            $name_only = str_replace( ", " . $division_name, "", $district['display'] );
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( "href" => "district_staff/facility/block/" . urlencode( $district['value'] ) ),
                    $node );
        }
    }

    /**
     * Display the facility list for the facilities.
     */
    protected function action_facility_block() {
        if ( count( $this->request_remainder ) < 1 ) {
            $this->action_facility_division();
            return;
        }
        $district = array_shift( $this->request_remainder );
        /*
        $district = I2CE_FormFactory::instance()->createContainer("district");
        $div_field = $district->getField("division");
        $div_field->setFromDB( $division );
        */
        $block = I2CE_FormFactory::instance()->createContainer("county");
        $dis_field = $block->getField("district");
        $dis_field->setFromDB( $district );
        $blocks = I2CE_List::listOptions( "county", false, array( $dis_field ) );

        list( $dist_form, $dist_id ) = explode( '|', $district, 2 );
        $district_name = I2CE_List::lookup( $dist_id, $dist_form );
        $this->template->setDisplayDataImmediate( "district_staff_subtitle", $district_name );

        foreach( $blocks as $block ) {
            $name_only = str_replace( ", " . $district_name, "", $block['display'] );
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( urlencode( "limits:+facility_county:equals:value" ) 
                        => $block['value'] ), $node );
        }

        $facility = I2CE_FormFactory::instance()->createContainer("facility");
        $loc_field = $facility->getField("location");
        $loc_field->setFromDB( $district );
        $facilities = I2CE_List::listOptions( "facility", false, array( $loc_field ) );

        list( $dist_form, $dist_id ) = explode( '|', $district, 2 );

        foreach( $facilities as $facility ) {
            //$name_only = str_replace( ", " . $district_name, "", $facility['display'] );
            $name_only = $facility['display'];
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( urlencode( "limits:position+facility:contains:value" ) 
                        => $facility['value'] ), $node );
        }
     }

    /**
     * Display the facility list for the facilities.
     */
    protected function action_facility_facility() {
        if ( count( $this->request_remainder ) < 1 ) {
            $this->action_facility_division();
            return;
        }
        $district = array_shift( $this->request_remainder );
        $facility = I2CE_FormFactory::instance()->createContainer("facility");
        $loc_field = $district->getField("location");
        $loc_field->setFromDB( $district );
        $facilities = I2CE_List::listOptions( "facility", false, array( $loc_field ) );

        list( $dist_form, $dist_id ) = explode( '|', $district, 2 );
        $district_name = I2CE_List::lookup( $dist_id, $dist_form );
        $this->template->setDisplayDataImmediate( "district_staff_subtitle", $district_name );

        foreach( $facilities as $facility ) {
            $node = $this->template->appendFileById( "district_staff_line.html",
                    "li", "district_staff_list" );
            $this->template->setDisplayDataImmediate( "district_staff_name",
                    $facility['display'], $node );
            $this->template->setDisplayDataImmediate( "district_staff_link",
                    array( urlencode( "limits:position+facility:equals:value" ) 
                        => $facility['value'] ), $node );
        }
    }



}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
