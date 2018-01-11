<?php
/**
* © Copyright 2014 IntraHealth International, Inc.
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
* @package I2CE
* @author Sovello Hildebrand <sovellohpmgani@gmail.com>
* @version v4.2.0
* @since v4.2.0
* @filesource
*/
/**
* Class I2CE_Dashboard
*
* @access public
*/


class iHRIS_BiharPagePrintSalarySlips extends  I2CE_Page {

    protected $display;
    /**
     * main actions for the page
     *
     */
    protected function action() {
      parent::action();
      $this->template->addHeaderLink("view.js");
      $this->template->addHeaderLink("payroll_functions.js");
      $this->actionReport();
      return true;
    }

    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->setAttribute( "class", "active", "menuPayroll", "a[@href='setmonthlysalary']" );
        $this->template->appendFileById( "menu_payroll.html", "ul", "menuPayroll" );
        $this->template->setAttribute( "class", "active", "menuPayroll", "ul/li/a[@href='print_salary_slips']" );                        
            //}
    }
    
        /*
     * This method is actually to read data from magicdata, but since we failed, I will hard code it here.
     */
    public function getActionHeader(){        
       return "Receipt Download Link";
      }

    public function getActionNode($field_args){        
        $input_field = $this->template->createElement( "a",
            array( "href" => "employee_salary_slip?monthly_salary_id=".$field_args[1]."&parent=".$field_args[0]), "Download Salary Receipt");
        return $input_field;
      }

      public function getActionFields(){
        $config = I2CE::getConfig()->modules->CustomReports;
        return $config->getAsArray("reportViews/1432260225/default_display_options/fields");
      }

    /**
     * Create the report display and add it to the page.
     * @param string $query The query string to pass to the action for applying limits.
     * @return boolean
     */
    public function actionReport( $query='' ) {
        try {
            $this->display = new I2CE_CustomReport_Display_DefaultAction( $this, $this->args['report_view'] );
        } catch (Exception $e) {
            I2CE::raiseError("Could not get for " . $this->args['report_view'] . "\n" . $e);
            return false;
        }

        $this->template->addHeaderLink("CustomReports.css");
        $this->template->addHeaderLink("CustomReports_iehacks.css", array('ie6' => true));
        $this->template->setDisplayData( "limit_description", false );

        $contentNode = $this->template->getElementById("siteContent");
        if ( !$contentNode instanceof DOMNode || !$this->display->display( $contentNode ) ) {
            I2CE::raiseError( "Couldn't display report.  Either no content node or an error occurred displaying the report." );
            return false;
        }
		$reportLimitsNode = $this->template->getElementById('report_limits');
        				
        if ( !$reportLimitsNode instanceof DOMNode ) {
            I2CE::raiseError("Unable to find report_limits node.");
        } else {
            $applyNode = $this->template->appendFileByNode(
                    "customReports_display_limit_apply_Default.html", "tr",
						$reportLimitsNode );	
            $form = $this->template->query( ".//*[@id='limit_form']", $contentNode );
            if ( $form->length == 1 ) {
                $form = $form->item(0)->setAttribute('action', $this->page() . "?$query");
            }
        }
		
        return true;
    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
