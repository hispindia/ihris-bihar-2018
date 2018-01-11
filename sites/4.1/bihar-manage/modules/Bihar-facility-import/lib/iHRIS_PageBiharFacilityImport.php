<?php
/*
 * © Copyright 2014 IntraHealth International, Inc.
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
 * Import CSV facilities
 * @package iHRIS
 * @subpackage Bihar-Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2014 IntraHealth International, Inc. 
 * @since v4.1.9
 * @version v4.1.9
 */

/**
 * The page class for importing data from CSV
 * @package iHRIS
 * @subpackage Bihar-Manage
 * @access public
 */
class iHRIS_PageBiharFacilityImport extends I2CE_PageFormCSV { 

    /**
     * @var I2CE_CLI The CLI object for this page.
     */
    protected $cli;

    /**
     * @var resource File handle for failed imports
     */
    protected $failed;

    /**
     * Create a new instance of this page.
     * @param array $args
     * @param array $request_remainder
     * @param array $get
     * @param array $post
     */
    public function __construct( $args, $request_remainder, $get=null, $post=null ) {

        $this->cli = new I2CE_CLI();
        $this->cli->addUsage("--csv=FILENAME: The CSV file to upload.\n");
        parent::__construct( $args, $request_remainder, $get, $post );
        $this->cli->processArgs();
        if ( !$this->cli->hasValue('csv') ) {
            $this->cli->usage();
        }
    }

    /**
     * Load the objects for the page.
     * @return boolean
     */
    protected function loadObjects() {
        $file = $this->cli->getValue('csv');
        if ( !file_exists( $file ) ||
                ($upload = fopen( $file, "r" )) === false ) {
            $this->displayMessage("Could not read " . $file );
            return false;
        }
        $failed_file = preg_replace( '/.csv$/', '.invalid.csv', $file );
        $this->failed = fopen( $failed_file, "w" );
        if ( $this->failed === false ) {
            die( "Couldn't open $failed_file for invalid rows.\n" );
        }
        $this->files['import'] = array();
        $this->files['import']['file'] = $upload;
        $this->files['import']['header'] = true;
        return true;
    }

    /**
     * Call the action for this page.
     * @param array $args
     * @param array $request_remainder
     */
    protected function actionCommandLine( $args, $request_remainder ) {
        if ( $this->validate() ) {
            if ( $this->save() ) {
                $this->displayMessage( "The CSV file was imported." );
            } else {
                $this->displayMessage( "Unable to save file." );
            }
        } else {
            $this->displayMessage( "There was invalid data in the file." );
        }
    }

    /**
     * Display the given message to the user.
     * @param string $message;
     */
    protected function displayMessage($message) {
        echo $message . "\n";
    }

    /**
     * Validate the CSV file.
     */
    protected function validate() {
        // Just check the headers for now.
        if ( !$this->checked_validation ) {
            if ( !$this->processHeaderRow( 'import' ) ) {
                $this->displayMessage("Unable to read headers from CSV file.");
                $this->invalid = true;
                return false;
            }
            $required_headers = array( 
                    'Facility Name', 'Facility Type', 
                    'Block', 'District', 'Division', 'State',
                    );
            $invalid_headers = array();
            foreach( $required_headers as $header ) {
                if ( !in_array( $header, $this->current['import']['header'] ) ) {
                    $invalid_headers[] = $header;
                }
            }
            if ( count( $invalid_headers ) > 0 ) {
                $this->displayMessage( "There are missing headers from the CSV file: " . implode( ', ', $invalid_headers ) );
                $this->invalid = true;
                return false;
            }

            $this->checked_validation = true;
        }
        return true;
    }

    /**
     * Validate the current row for the given key
     * @param string $key
     */
    protected function validateRow( $key ) {
        // Don't do individual row validations for now
        return true;
        /*
        $error = false;
        $noblank = array(
                    'Facility Name', 'Facility Type', 
                    'Office/ Facility Parent',
                    'Location',
                );
        foreach( $noblank as $check ) {
            if ( !$this->current[$key]['row'][$check] ) {
                $this->displayMessage("$check is blank which isn't allowed.");
                $error = true;
            }
        }
        $location = $this->lookupList( "county", $this->current[$key]['row']['Location'] );
        if ( !$location || $location == '' ) {
            $location = $this->lookupList( "district", $this->current[$key]['row']['Location'] );
        }
        $parent = $this->lookupList( "facility", $this->current[$key]['row']['Office/ Facility Parent'] );
        $facility_type = $this->lookupList( "facility_type", $this->current[$key]['row']['Facility Type'] );
        if ( !$location || $location == '' ) { 
            $this->displayMessage("Unable to find Location: " . $this->current[$key]['row']['Location'] );
            $error = true;
        }
        if ( !$parent || $parent == '' ) { 
            $this->displayMessage("Unable to find Facility Parent " . $this->current[$key]['row']['Office/ Facility Parent'] );
            $error = true;
        }
        if ( !$facility_type || $facility_type == '' ) {
            $this->displayMessage("Unable to find Facility Type " . $this->current[$key]['row']['Facility Type'] );
            $error = true;
        }

        if ( $error ) {
            fputcsv( $this->failed, $this->current[$key]['row'] );
        }
        return !$error;
        */
    }

    /**
     * Save the current row for the given key
     * @param string $key
     */
    protected function saveRow( $key ) {
        $district = $this->lookupList( "district", $this->current[$key]['row']['District'] );
        if ( $district ) {
            if ( $this->current[$key]['row']['Block'] ) {
                $location = $this->lookupBlock( $this->current[$key]['row']['Block'], $district );
            } else {
                $location = $district;
            }
        }
        $facility_type = $this->lookupList( "facility_type", $this->current[$key]['row']['Facility Type'] );

        if ( !$location || !$facility_type || !$this->current[$key]['row']['Facility Name'] ) {
            $this->displayMessage("Unable to match required data $key: $location - $facility_type");
            array_push( $this->current[$key]['row'], 'not found' );
            fputcsv( $this->failed, $this->current[$key]['row'] );
            return true;
        }
        $facility = $this->factory->createContainer( "facility" );
        $facility->getField('location')->setFromDB( $location );
        $facility->name = trim($this->current[$key]['row']['Facility Name']);
        $facility->getField('facility_type')->setFromDB( $facility_type );
        $facility->validate();
        if ( $facility->hasInvalid() ) {
            I2CE::raiseError( "Invalid facility data." );
            array_push( $this->current[$key]['row'], 'invalid (duplicate)' );
            fputcsv( $this->failed, $this->current[$key]['row'] );
            return true;
        } elseif ( !$facility->save( $this->user ) ) {
            I2CE::raiseError( "Failed to save facility for CSV import." );
            array_push( $this->current[$key]['row'], 'failed' );
            fputcsv( $this->failed, $this->current[$key]['row'] );
            return true;
        }

        echo "Saved " . $facility->getNameId() . " $location - $parent - $facility_type " . memory_get_usage() . "\n";
        $facility->cleanup();
        unset( $facility );

        return true;
    }

    /**
     * Lookup a block based on the block and district ID
     * @param string $block
     * @param string $district_id
     * @return string
     */
    protected function lookupBlock( $block, $district_id ) {
        $block = trim( strtolower( $block ) );
        if ( !array_key_exists( 'county', $this->cache ) ) {
            $this->cache['county'] = array();
        }
        $cacheID = $district_id .'-'. $block;
        if ( !array_key_exists( $cacheID, $this->cache['county'] ) ) {
            $where = array(
                    'operator' => 'AND',
                    'operand' => array(
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'style' => 'lowerequals',
                            'field' => 'name',
                            'data' => array( 'value', $block )
                            ),
                        array(
                            'operator' => 'FIELD_LIMIT',
                            'style' => 'equals',
                            'field' => 'district',
                            'data' => array( 'value', $district_id )
                            ),
                        ),
                    );
            $id = I2CE_FormStorage::search( 'county', false, $where, array(), true );
            if ( $id ) {
                $this->cache['county'][$cacheID] = "county|$id";
            } else {
                $this->cache['county'][$cacheID] = "";
            }
        }
        return $this->cache['county'][$cacheID];
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
