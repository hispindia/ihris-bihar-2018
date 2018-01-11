<?php
/**
 * @copyright © 2008, 2009 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*  I2CE_CustomReport_Display_PDF
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @since 4.1
* @version 4.1
* @access public
*/


class I2CE_CustomReport_Display_ODT extends I2CE_CustomReport_Display{

    /**
     * The odf file object.
     * @var I2CE_Odf
     */
    protected $odf;

    /**
     * The odf row for each report row
     * @var Segment
     */
    protected $odf_row;

    /**
     * The header variable names and values
     * @var array
     */
    protected $header_vars;

    protected function canView() {
        return true;
    }

    /**
     * Display the report
     * @param DOMNode $contentNode The DOM node we wish to display into
     * @param boolean $processResults Defaults to true meaning we run through the results
     * @param mixed $controls.  If null (default), we display all the report controsl.  If string or an array of string, we only display the indicated controls
     * @returns boolean. true on sucess
     */
    public function display($contentNode,$processResults=true, $controls = null) {
        I2CE::longExecution( array( "max_execution_time" => 1800 ) );
        $this->saveDefaultView();


        $template = $this->defaultOptions['odt_template'];
        $template_file = null;
        $this->config->setIfIsSet( $template_file, "printed_forms/$template/template" );
        if ( !$template_file ) {
            I2CE::raiseError( "No template file set for $template" );
            return false;
        }
        $template_file = $this->config->printed_forms->$template->template;
        $template_loc = I2CE::getFileSearch()->search( 'ODT_TEMPLATES', $template_file );
        if ( !$template_loc ) {
            I2CE::raiseError( "No template file found from $template_file" );
            return false;
        }

        $this->header_vars = array();
        foreach( $this->getDisplayFieldsData() as $formfield=>$data ) {
            $this->header_vars["++header+$formfield"] = $data['header'];
        }

        $odf_config = array( 'DELIMITER_LEFT' => '{{{',
                'DELIMITER_RIGHT' => '}}}',
                'ZIP_PROXY' => 'PhpZipProxy',
                );
        $this->odf = new I2CE_Odf( $template_loc, $odf_config );

        try {
            $this->odf->setVars( '++report_title', $this->config->display_name );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
        try {
            $this->odf->setVars( '++report_description', $this->config->description );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        foreach( $this->header_vars as $key => $val ) {
            try {
                $this->odf->setVars( $key, $val );
            } catch ( OdfException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }

        $user = new I2CE_User();
        $name = $user->firstname . ' ' . $user->lastname;
        try {
            $this->odf->setVars( '++user_name', $name );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        $time = strftime("%c");
        try {
            $this->odf->setVars( '++time', $time );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        $limitsDesc = $this->getReportLimitsDescription();
        try {
            $this->odf->setVars( '++report_limit', $limitsDesc );
        } catch ( OdfException $e ) {
            //It's ok if it's not there so don't do anything.
        }
 
        try {
            $this->odf_row = $this->odf->setSegment( 'report_row' );
        } catch ( OdfException $e ) {
            I2CE::raiseError( "Couldn't find report_row in ODT template $template_loc." );
            return false;
        }

        $data = $this->getResults();
        if (!$this->processResults($data,$contentNode)) {
            I2CE::raiseError("Could not get results");
            return false;
        }        

        $this->odf->mergeSegment( $this->odf_row );


        if ( ($errors = I2CE_Dumper::cleanlyEndOutputBuffers())) {
            I2CE::raiseError("Errors:\n" . $errors);
        }

        $this->odf->exportAsAttachedFile( $template_file );

        exit; // we want to make sure there is no further output or that the $this->page->display() method is not called

    }




    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        $mapped_row = $this->mapResults($row);


        try {
            $this->odf_row->setVars( '++row_num', $row_num+1 );
        } catch( SegmentException $e ) {
            //It's ok if it's not there so don't do anything.
        }

        foreach( $this->header_vars as $key => $val ) {
            try {
                $this->odf_row->setVars( $key, $val );
            } catch ( SegmentException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }


        foreach( $mapped_row as $key => $val ) {
            try {
                $this->odf_row->setVars( $key, $val );
            } catch( SegmentException $e ) {
                //It's ok if it's not there so don't do anything.
            }
        }
        $this->odf_row->merge();

        return true;
    }
   

    /**
     * Adds any controls for this display to the content node.
     * @param DOMNode $contentNode
     * @returns boolean
     */
    protected function displayReportControl( $contentNode ) {
        if ( !parent::displayReportControl( $contentNode ) ) {
            return false;
        }
        $this->template->setDisplayData( "has_odt_templates", false );
        if ( $this->config->is_parent( "printed_forms" ) ) {
            $print_forms = $this->config->printed_forms->getAsArray();
            foreach( $print_forms as $print_form => $print_data ) {
                $this->template->setDisplayDataImmediate( "has_odt_templates", " " );
                $this->template->addOption( "odt_template", $print_form, 
                        $print_data['displayName'], $contentNode );
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
