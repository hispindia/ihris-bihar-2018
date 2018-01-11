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


class iHRIS_BiharPageSetMonthlySalary extends  I2CE_Page {

    protected $display;
    /**
     * main actions for the page
     *
     */
    protected function action() {
        parent::action();
        /*$this->template->addHeaderLink("view.js");*/
        $this->actionReport();
        $this->template->addHeaderLink("payroll_functions.js");
        return true;
    }

    /**
     * Load the HTML template files for editing and confirming the index and demographic information.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->setAttribute( "class", "active", "menuPayroll", "a[@href='setmonthlysalary']" );
        $this->template->appendFileById( "menu_payroll.html", "ul", "menuPayroll" );
        $this->template->setAttribute( "class", "active", "menuPayroll", "ul/li/a[@href='setmonthlysalary']" );
        //}
    }
    public function getActionFields(){
        $config = I2CE::getConfig()->modules->CustomReports;
        return $config->getAsArray("reportViews/1432260225/default_display_options/fields");
    }

    /*
 * This method is actually to read data from magicdata, but since we failed, I will hard code it here.
 */
    public function getActionHeader(){
            $config = I2CE::getConfig()->modules->CustomReports;
        return array("Unpaid Leave Days","Transport allowance","Other Allowance","House Rent Deduction","Interest Advance Deduction","GPF Advance deduction","House Building Advance Deduction","Service Tax Deduction","Computer Advance Deduction","Festival Advance Deduction","Miscellaneous Recovery");

    }

    public function getActionNode($field_args){
        #$fields = $this->getActionFields();

                $working_days_field = $this->template->createElement( "input",
                                                               array( "type" => "text", "name" => "work_days", "id" => "work_days" ) );
                $js_work_days = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
                $this->template->setDisplayData('work_days', $field_args[3], $working_days_field);
                $working_days_field->setAttribute('onchange',$js_work_days);

        $leave_days_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "leave_days", "id" => "leave_days" ) );
        $js_leave_days = "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('leave_days', $field_args[4], $leave_days_field);
        $leave_days_field->setAttribute('onchange',$js_leave_days);

        $transport_allowance_field=$this->template->createElement("input",
            array("type"=>"text","name"=>"transport_allowance","id"=>"transport_allowance"));
        $js_transport_allowance ="isNumeric(this, '".$field_args[0]."','".$field_args[1]."','".$field_args[2]."');";
        $this->template->setDisplayData('transport_allowance', $field_args[4], $transport_allowance_field);
        $transport_allowance_field->setAttribute('onchange',$js_transport_allowance);



        $other_allowance_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "other_allowance", "id" => "other_allowance" ) );
        $js_other_allowance= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('other_allowance', $field_args[4], $other_allowance_field);
        $other_allowance_field->setAttribute('onchange',$js_other_allowance);

        $house_rent_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "house_rent_deduction", "id" => "house_rent_deduction" ) );
        $js_house_rent_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('house_rent_deduction', $field_args[4], $house_rent_deduction_field);
        $house_rent_deduction_field->setAttribute('onchange',$js_house_rent_deduction);

        $interest_advance_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "interest_advance_deduction", "id" => "interest_advance_deduction" ) );
        $js_interest_advance_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('interest_advance_deduction', $field_args[4], $interest_advance_deduction_field);
        $interest_advance_deduction_field->setAttribute('onchange',$js_interest_advance_deduction);

        $gpf_advance_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "gpf_advance_deduction", "id" => "gpf_advance_deduction" ) );
        $js_gpf_advance_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('gpf_advance_deduction', $field_args[4], $gpf_advance_deduction_field);
        $gpf_advance_deduction_field->setAttribute('onchange',$js_gpf_advance_deduction);

        $house_building_advance_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "house_building_advance_deduction", "id" => "house_building_advance_deduction" ) );
        $js_house_building_advance_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('house_building_advance_deduction', $field_args[4], $house_building_advance_deduction_field);
        $house_building_advance_deduction_field->setAttribute('onchange',$js_house_building_advance_deduction);

        $service_tax_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "service_tax_deduction", "id" => "service_tax_deduction" ) );
        $js_service_tax_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('service_tax_deduction', $field_args[4], $service_tax_deduction_field);
        $service_tax_deduction_field->setAttribute('onchange',$js_service_tax_deduction);

        $computer_advance_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "computer_advance_deduction", "id" => "computer_advance_deduction" ) );
        $js_computer_advance_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('computer_advance_deduction', $field_args[4], $computer_advance_deduction_field);
        $computer_advance_deduction_field->setAttribute('onchange',$js_computer_advance_deduction);

        $festival_advance_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "festival_advance_deduction", "id" => "festival_advance_deduction" ) );
        $js_festival_advance_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('festival_advance_deduction', $field_args[4], $festival_advance_deduction_field);
        $festival_advance_deduction_field->setAttribute('onchange',$js_festival_advance_deduction);

        $misc_recoveries_deduction_field = $this->template->createElement( "input",
            array( "type" => "text", "name" => "misc_recoveries_deduction", "id" => "misc_recoveries_deduction" ) );
        $js_misc_recoveries_deduction= "isNumeric(this, '".$field_args[0]."', '".$field_args[1]."', '".$field_args[2]."');";
        $this->template->setDisplayData('misc_recoveries_deduction', $field_args[4], $misc_recoveries_deduction_field);
        $misc_recoveries_deduction_field->setAttribute('onchange',$js_misc_recoveries_deduction);

        //return array($working_days_field, $leave_days_field);
        return array($leave_days_field,$transport_allowance_field,$other_allowance_field,$house_rent_deduction_field,$interest_advance_deduction_field,$gpf_advance_deduction_field,$house_building_advance_deduction_field,$service_tax_deduction_field,$computer_advance_deduction_field,$festival_advance_deduction_field,$misc_recoveries_deduction_field);

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
