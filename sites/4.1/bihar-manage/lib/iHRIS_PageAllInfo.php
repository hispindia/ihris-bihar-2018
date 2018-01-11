<?php
/*
 * © Copyright 2013 IntraHealth International, Inc.
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
 * @subpackage Bihar-Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2013 IntraHealth International, Inc. 
 * @since v4.1.5
 * @version v4.1.5
 */

/**
 * The page class for displaying the report list
 * @package iHRIS
 * @subpackage Bihar-Manage
 * @access public
 */
class iHRIS_PageAllInfo extends I2CE_Page {
        
    /**
     * Perform the main actions of the page.
     */
    protected function action() {
        $page = "default";
        if ( count( $this->request_remainder ) > 0 ) {
            $page = array_shift( $this->request_remainder );
        }
        if ( $page != 'default' && $page != 'promotion' ) {
            $page = 'default';
        }
        if ( $this->get_exists('all') && $this->get('all') == 'true' ) {
            $this->action_display( null, null, false, $page );
        } elseif ( $this->get_exists('district') ) {
            $this->action_display( mysql_real_escape_string($this->get('district')) );
        } elseif ( $this->get_exists('division') ) {
            $this->action_display( null, mysql_real_escape_string($this->get('division')) );
        } elseif ( $this->get_exists('state') && $this->get('state') == 'true' ) {
            $this->action_display( null, null, true );
        } else {
            $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='report_list']" );
            parent::action();
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
        $district = I2CE_FormFactory::instance()->createContainer( "district" );
        $reg_field = $district->getField("region");
        $reg_field->setFromDB("region|BR");

        $districts = I2CE_List::listOptions( "district", false, array( $reg_field ) );
        usort( $districts, "self::sort_district" );
        foreach( $districts as $district ) {
            $name_only = str_replace( ", Bihar, India", "", $district['display'] );
            $node = $this->template->appendFileById( "allinfo_line.html",
                    "li", "district_allinfo" );
            $this->template->setDisplayDataImmediate( "district_allinfo_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_allinfo_link", array("district" => $district['value'] ), $node );
        }

        $divisions = I2CE_List::listOptions( "division", false, array( $reg_field ) );
        usort( $divisions, "self::sort_district" );
        foreach( $divisions as $division ) {
            $name_only = str_replace( ", Bihar, India", "", $division['display'] );
            $node = $this->template->appendFileById( "allinfo_line.html",
                    "li", "division_allinfo" );
            $this->template->setDisplayDataImmediate( "district_allinfo_name",
                    $name_only, $node );
            $this->template->setDisplayDataImmediate( "district_allinfo_link", array("division" => $division['value'] ), $node );
        }
    }

    /**
     * Display the CSV data for this page.
     * @param string $district 
     * @param string $division 
     * @param string $state 
     */
    protected function action_display( $district=null, $division=null, $state=false, $page='default' ) {
        $fields = array ( 
                'default' => array (
                    'ep' => array (
                        'person+id' => 'Person Id',
                        'person+person_title' => 'Title',
                        'person+firstname' => 'First Name',
                        'person+othername' => 'Middle Name',
                        'person+surname' => 'Surname',
                        'person+seniority_no' => 'Seniority No.',
                        'person+seniority_year' => 'Seniority Year',
                        'person+nationality' => 'Nationality',
                        'demographic+birth_date' => 'Date of Birth',
                        'demographic+gender' => 'Gender',
                        'demographic+handicap' => 'Handicap',
                        'demographic+marital_status' => 'Marital Status',
                        'demographic+dependents' => 'Number of Dependents',
                        'demographic+minorities' => 'Religion',
                        'person+blood_group' => 'Blood Group',
                        'person+birth_mark' => 'Identification Mark',
                        'demographic+caste' => 'Category',
                        'person+residence_village' => 'Village/Mohalla',
                        'person+residence_thana' => 'Thana',
                        'person+perm_residence' => 'Home District',
                        //'person+residence' => 'Home District',
                        'person+pin' => 'Pin',
                        //'gpf+id_type' => 'GPF Type',
                        'gpf+id_num' => 'GPF Number',
                        //'familydetails+relationship' => 'Family Relationship',
                        'familydetails+firstname' => 'Father\'s First Name',
                        'familydetails+middlename' => 'Father\'s Middle Name',
                        'familydetails+surname' => 'Father\'s Surname',
                        'mother+firstname' => 'Mother\'s First Name',
                        'mother+middlename' => 'Mother\'s Middle Name',
                        'mother+surname' => 'Mother\'s Surname',
                        'family2+firstname' => 'Husband/Wife  First Name',
                        'family2+middlename' => 'Husband/Wife  Middle Name',
                        'family2+surname' => 'Husband/Wife Surname',
                        'family2+spouse_employee' => 'Husband/Wife a regular bihar governmentemployee ?',
                        'family2+spouse_doctor' => 'Is he/she a regular doctor?',
                        'family2_facility+name' => 'husband/wife is a regular employee Posting facility',
                        'work+address' => 'Mailing address',
                        'work+email' => 'Email',
                        'work+fax' => 'Fax Number',
                        'work+telephone' => 'Work phone',
                        'work+mobile_phone' => 'Mobile Phone',
                        'id+id_type' => 'Identification Type',
                        'id+id_num' => 'Identification Number',
                        'nominee+firstname' => 'Nominee First Name',
                        'nominee+surname' => 'Nominee Surname',
                        'nominee+middle_name' => 'Nominee Middle Name',
                        'nominee+date_of_birth' => 'Nominee Date of Birth',
                        'nominee+relationship' => 'Nominee Relationship',
                        'job+title' => 'Current Designation',
                        'position+facility' => 'Current Posting Facility/Dept.',
                        'facility_district+name' => 'Current Posting District',
                        'primary_form+start_date' => 'Current Posting Date',
                        'position+pos_type' => 'Position Type',
                        'current_salary+salary' => 'Salary',
                        'primary_form+govt_order_date' => 'Current Posting Government Order Date',
                        'primary_form+govt_order_number' => 'Current Posting Government Order Number',
                        'primary_form+govt_order_authority' => 'Current Posting Government Order Authority',
                        //'job+salary_grade' => 'Salary Grade',
                        'primary_form+designation_grade' => 'Designation Grade',
                        'joining+ad_hoc_date' => 'Ad-hoc Appointment Date (if applicable)',
                        'joining+date_of_joining' => 'Regular Appointment/Regularisation Date',
                        'joining+confirmation_date' => 'Confirmation Date',
                        'joining+job' => 'Designation on Appointment',
                        'joining+confirmation_order_date' => 'Confirmation Order Date',
                        'joining+confirmation_order_number' => 'Confirmation Order Number',
                        //'home+notes' => 'Are you on Deputation?',
                        'deputation+position' => 'Deputation Designation',
                        'deputation+govt_order_date' => 'Deputation Government Order Date',
                        'deputation+govt_order_number' => 'Deputation Government Order Number',
                        'deputation+govt_order_authority' => 'Deputation Government Order Authority',
                        'deputation+start_date' => 'Deputation Start Date',
                        ),
                    'ep2' => array(
                            'service+from_date' => 'Posting  From Date',
                            'service+to_date' => 'Posting  To Date',
                            'service+job' => 'Designation',
                            'service+facility' => 'Posting  Facility/Dept. Name',
                            'service+district' => 'Posting   Block & District',
                            'service+govt_order_date' => 'Government Order Date ',
                            'service+govt_order_number' => 'Government Order Number ',
                            'service+pos_change_reason' => 'Reason for Change',
                            'service2+from_date' => 'Posting  From Date',
                            'service2+to_date' => 'Posting  To Date',
                            'service2+job' => 'Designation ',
                            'service2+facility' => 'Posting  Facility/Dept. Name',
                            'service2+district' => 'Posting  Block & District',
                            'service2+govt_order_date' => 'Government Order Date ',
                            'service2+govt_order_number' => 'Government Order Number ',
                            'service2+pos_change_reason' => 'Reason for Change ',
                            'service3+from_date' => 'Posting  From Date',
                            'service3+to_date' => 'Posting  To Date',
                            'service3+job' => 'Designation ',
                            'service3+facility' => 'Posting  Facility/Dept. Name',
                            'service3+district' => 'Posting  Block & District',
                            'service3+govt_order_date' => 'Government Order Date ',
                            'service3+govt_order_number' => 'Government Order Number ',
                            'service3+pos_change_reason' => 'Reason for Change ',
                            'servicehistory4+from_date' => 'Posting  From Date',
                            'servicehistory4+to_date' => 'Posting  To Date',
                            'servicehistory4+job' => 'Designation ',
                            'servicehistory4+facility' => 'Posting  Facility/Dept. Name',
                            'servicehistory4+district' => 'Posting  Block & District',
                            'servicehistory4+govt_order_date' => 'Government Order Date ',
                            'servicehistory4+govt_order_number' => 'Government Order Number ',
                            'servicehistory4+pos_change_reason' => 'Reason for Change ',
                            'servicehistory5+to_date' => 'Posting  To Date',
                            'servicehistory5+from_date' => 'Posting  From Date',
                            'servicehistory5+job' => 'Designation ',
                            'servicehistory5+facility' => 'Posting  Facility/Dept. Name',
                            'servicehistory5+district' => 'Posting  Block & District',
                            'servicehistory5+govt_order_date' => 'Government Order Date ',
                            'servicehistory5+govt_order_number' => 'Government Order Number ',
                            'servicehistory5+pos_change_reason' => 'Reason for Change ',
                            'servicehistory6+from_date' => 'Posting  From Date',
                            'servicehistory6+to_date' => 'Posting  To Date',
                            'servicehistory6+job' => 'Designation ',
                            'servicehistory6+facility' => 'Posting  Facility/Dept. Name',
                            'servicehistory6+district' => 'Posting  Block & District',
                            'servicehistory6+govt_order_date' => 'Government Order Date ',
                            'servicehistory6+govt_order_number' => 'Government Order Number ',
                            'servicehistory6+pos_change_reason' => 'Reason for Change ',
                            'edu+degree' => 'Highest Professional Qualification Degree',
                            'edu+board' => 'Highest Professional Qualification Board/University',
                            'edu+year' => 'Professional Qualification Completion Year',
                            'edu+institution' => 'Highest Professional Qualification Name of Institute',
                            'edu+location' => 'Highest Professional Qualification Institute Address',
                            'education+board' => 'Board/University HEQ',
                            'education+degree' => 'Highest Education Qualification',
                            'education+institution' => 'Name of Institute HEQ',
                            'education+location' => 'Institute Address HEQ',
                            'education+year' => 'Year of Graduation HEQ',
                            'specialty1+institution' => 'First Specialty Institute',
                            'specialty1+location' => 'First Specialty Institute Address',
                            'specialty1+year' => 'First Specialty Year of Graduation',
                            'specialty1+specialty' => 'First Specialty',
                            'specialty2+institution' => 'Second Specialty Institute',
                            'specialty2+location' => 'Second Specialty Institute Address',
                            'specialty2+year' => 'Second Specialty Year of Graduation',
                            'specialty2+specialty' => 'Second Specialty',
                            'specialty3+institution' => 'Third Specialty Institute',
                            'specialty3+location' => 'Third Specialty Institute Address',
                            'specialty3+year' => 'Third Specialty Year of Graduation',
                            'specialty3+specialty' => 'Third Specialty',
                            'training+from_date' => 'Year',
                            'training+number_of_days' => 'Duration',
                            'training+training_name' => 'Training Name',
                            'training+agency_name' => 'Sponsored By',
                            'training+training_subject' => 'Subject/Theme',
                            'training+level_of_training' => 'Level of Training',
                            ),
                        ),
                    'promotion' => array( 
                            'pp' => array (
                            'person+id' => 'Person Id',
                            'person+person_title' => 'Title',
                            'person+firstname' => 'First Name',
                            'person+othername' => 'Middle Name',
                            'person+surname' => 'Surname',
                            'job+title' => 'Current Designation',
                            'position+facility' => 'Current Posting Facility/Dept.',
                            'division+name' => 'Current Division',
                            'district+name' => 'Current District',
                            'service+from_date' => 'Posting  From Date1',
                            'service+to_date' => 'Posting  To Date1',
                            'service+job' => 'Designation1',
                            'service+facility' => 'Posting  Facility/Dept. Name1',
                            'service+district' => 'Posting   Block & District1',
                            'service+govt_order_date' => 'Government Order Date1 ',
                            'service+govt_order_number' => 'Government Order Number1 ',
                            'service+pos_change_reason' => 'Reason for Change1',
                            'service2+from_date' => 'Posting  From Date2',
                            'service2+to_date' => 'Posting  To Date2',
                            'service2+job' => 'Designation2 ',
                            'service2+facility' => 'Posting  Facility/Dept. Name2',
                            'service2+district' => 'Posting  Block & District2',
                            'service2+govt_order_date' => 'Government Order Date2 ',
                            'service2+govt_order_number' => 'Government Order Number2 ',
                            'service2+pos_change_reason' => 'Reason for Change2 ',
                            'service3+from_date' => 'Posting  From Date3',
                            'service3+to_date' => 'Posting  To Date3',
                            'service3+job' => 'Designation3 ',
                            'service3+facility' => 'Posting  Facility/Dept. Name3',
                            'service3+district' => 'Posting  Block & District3',
                            'service3+govt_order_date' => 'Government Order Date3 ',
                            'service3+govt_order_number' => 'Government Order Number3 ',
                            'service3+pos_change_reason' => 'Reason for Change3 ',
                            'servicehistory4+from_date' => 'Posting  From Date4',
                            'servicehistory4+to_date' => 'Posting  To Date4',
                            'servicehistory4+job' => 'Designation4 ',
                            'servicehistory4+facility' => 'Posting  Facility/Dept. Name4',
                            'servicehistory4+district' => 'Posting  Block & District4',
                            'servicehistory4+govt_order_date' => 'Government Order Date 4',
                            'servicehistory4+govt_order_number' => 'Government Order Number4 ',
                            'servicehistory4+pos_change_reason' => 'Reason for Change4 ',
                            'servicehistory5+from_date' => 'Posting  From Date5',
                            'servicehistory5+to_date' => 'Posting  To Date5',
                            'servicehistory5+job' => 'Designation5 ',
                            'servicehistory5+facility' => 'Posting  Facility/Dept. Name5',
                            'servicehistory5+district' => 'Posting  Block & District5',
                            'servicehistory5+govt_order_date' => 'Government Order Date5 ',
                            'servicehistory5+govt_order_number' => 'Government Order Number5 ',
                            'servicehistory5+pos_change_reason' => 'Reason for Change ',
                           'servicehistory6+from_date' => 'Posting  From Date6',
                            'servicehistory6+to_date' => 'Posting  To Date6',
                            'servicehistory6+job' => 'Designation6 ',
                            'servicehistory6+facility' => 'Posting  Facility/Dept. Name6',
                            'servicehistory6+district' => 'Posting  Block & District6',
                            'servicehistory6+govt_order_date' => 'Government Order Date6 ',
                            'servicehistory6+govt_order_number' => 'Government Order Number6 ',
                            'servicehistory6+pos_change_reason' => 'Reason for Change6 ',
                            'servicehistory7+from_date' => 'Posting  From Date7',
                            'servicehistory7+to_date' => 'Posting  To Date7',
                            'servicehistory7+job' => 'Designation7 ',
                            'servicehistory7+facility' => 'Posting  Facility/Dept. Name7',
                            'servicehistory7+district' => 'Posting  Block & District7',
                            'servicehistory7+govt_order_date' => 'Government Order Date7 ',
                            'servicehistory7+govt_order_number' => 'Government Order Number7 ',
                            'servicehistory7+pos_change_reason' => 'Reason for Change7 ',
                            'service_history8+from_date' => 'Posting  From Date8',
                            'service_history8+to_date' => 'Posting  To Date8',
                            'service_history8+job' => 'Designation8 ',
                            'service_history8+facility' => 'Posting  Facility/Dept. Name8',
                            'service_history8+district' => 'Posting  Block & District8',
                            'service_history8+govt_order_date' => 'Government Order Date8 ',
                            'service_history8+govt_order_number' => 'Government Order Number8 ',
                            'service_history8+pos_change_reason' => 'Reason for Change8 ',
                            'service_history9+from_date' => 'Posting  From Date9',
                            'service_history9+to_date' => 'Posting  To Date9',
                            'service_history9+job' => 'Designation9 ',
                            'service_history9+facility' => 'Posting  Facility/Dept. Name9',
                            'service_history9+district' => 'Posting  Block & District9',
                            'service_history9+govt_order_date' => 'Government Order Date9 ',
                            'service_history9+govt_order_number' => 'Government Order Numbe9r ',
                            'service_history9+pos_change_reason' => 'Reason for Change9 ',
                            'service_history10+from_date' => 'Posting  From Date10',
                            'service_history10+to_date' => 'Posting  To Date10',
                            'service_history10+job' => 'Designation10 ',
                            'service_history10+facility' => 'Posting  Facility/Dept. Name10',
                            'service_history10+district' => 'Posting  Block & District10',
                            'service_history10+govt_order_date' => 'Government Order Date10 ',
                            'service_history10+govt_order_number' => 'Government Order Number10 ',
                            'service_history10+pos_change_reason' => 'Reason for Change10 ',
                            'service_history11+from_date' => 'Posting  From Date11',
                            'service_history11+to_date' => 'Posting  To Date11',
                            'service_history11+job' => 'Designation 11',
                            'service_history11+facility' => 'Posting  Facility/Dept. Name11',
                            'service_history11+district' => 'Posting  Block & District11',
                            'service_history11+govt_order_date' => 'Government Order Date 11',
                            'service_history11+govt_order_number' => 'Government Order Number11 ',
                            'service_history11+pos_change_reason' => 'Reason for Change11 ',
                            'service_history12+from_date' => 'Posting  From Date12',
                            'service_history12+to_date' => 'Posting  To Date12',
                            'service_history12+job' => 'Designation12 ',
                            'service_history12+facility' => 'Posting  Facility/Dept. Name12',
                            'service_history12+district' => 'Posting  Block & District12',
                            'service_history12+govt_order_date' => 'Government Order Date12 ',
                            'service_history12+govt_order_number' => 'Government Order Number12 ',
                            'service_history12+pos_change_reason' => 'Reason for Change12 ',
                            

                                ),
                            'pp2' => array(
                            'service_history13+from_date' => 'Posting  From Date13',
                            'service_history13+to_date' => 'Posting  To Date13',
                            'service_history13+job' => 'Designation13 ',
                            'service_history13+facility' => 'Posting  Facility/Dept. Name13',
                            'service_history13+district' => 'Posting  Block & District13',
                            'service_history13+govt_order_date' => 'Government Order Date13 ',
                            'service_history13+govt_order_number' => 'Government Order Number 13',
                            'service_history13+pos_change_reason' => 'Reason for Change 13',
                           'service_history14+from_date' => 'Posting  From Date14',
                            'service_history14+to_date' => 'Posting  To Date14',
                            'service_history14+job' => 'Designation14 ',
                            'service_history14+facility' => 'Posting  Facility/Dept. Name14',
                            'service_history14+district' => 'Posting  Block & District14',
                            'service_history14+govt_order_date' => 'Government Order Date14 ',
                            'service_history14+govt_order_number' => 'Government Order Number14 ',
                            'service_history14+pos_change_reason' => 'Reason for Change14 ',
                            'service_history15+from_date' => 'Posting  From Date15',
                            'service_history15+to_date' => 'Posting  To Date15',
                            'service_history15+job' => 'Designation15 ',
                            'service_history15+facility' => 'Posting  Facility/Dept. Name15',
                            'service_history15+district' => 'Posting  Block & District15',
                            'service_history15+govt_order_date' => 'Government Order Date15 ',
                            'service_history15+govt_order_number' => 'Government Order Number15 ',
                            'service_history15+pos_change_reason' => 'Reason for Change15 ',
                            'service_history16+from_date' => 'Posting  From Date16',
                            'service_history16+to_date' => 'Posting  To Date16',
                            'service_history16+job' => 'Designation 16',
                            'service_history16+facility' => 'Posting  Facility/Dept. Name16',
                            'service_history16+district' => 'Posting  Block & District16',
                            'service_history16+govt_order_date' => 'Government Order Date 16',
                            'service_history16+govt_order_number' => 'Government Order Number16 ',
                            'service_history16+pos_change_reason' => 'Reason for Change16 ',
                            'service_history17+from_date' => 'Posting  From Date17',
                            'service_history17+to_date' => 'Posting  To Date17',
                            'service_history17+job' => 'Designation 17',
                            'service_history17+facility' => 'Posting  Facility/Dept. Name17',
                            'service_history17+district' => 'Posting  Block & District17',
                            'service_history17+govt_order_date' => 'Government Order Date17 ',
                            'service_history17+govt_order_number' => 'Government Order Number17 ',
                            'service_history17+pos_change_reason' => 'Reason for Change17 ',
                             'service_history18+from_date' => 'Posting  From Date18',
                            'service_history18+to_date' => 'Posting  To Date18',
                            'service_history18+job' => 'Designation18 ',
                            'service_history18+facility' => 'Posting  Facility/Dept. Name18',
                            'service_history18+district' => 'Posting  Block & District18',
                            'service_history18+govt_order_date' => 'Government Order Date18 ',
                            'service_history18+govt_order_number' => 'Government Order Number18 ',
                            'service_history18+pos_change_reason' => 'Reason for Change18 ',
                            'service_history18+from_date' => 'Posting  From Date19',
                            'service_history19+to_date' => 'Posting  To Date19',
                            'service_history19+job' => 'Designation19 ',
                            'service_history19+facility' => 'Posting  Facility/Dept. Name19',
                            'service_history19+district' => 'Posting  Block & District19',
                            'service_history19+govt_order_date' => 'Government Order Date19 ',
                            'service_history19+govt_order_number' => 'Government Order Number19 ',
                            'service_history19+pos_change_reason' => 'Reason for Change 19',
                            'service_history20+from_date' => 'Posting  From Date20',
                            'service_history20+to_date' => 'Posting  To Date20',
                            'service_history20+job' => 'Designation20 ',
                            'service_history20+facility' => 'Posting  Facility/Dept. Name20',
                            'service_history20+district' => 'Posting  Block & District20',
                            'service_history20+govt_order_date' => 'Government Order Date20 ',
                            'service_history20+govt_order_number' => 'Government Order Number20 ',
                            'service_history20+pos_change_reason' => 'Reason for Change20 ',


                                ),
     ),
        );
        $module_limits = array( 
                'default' => array( 
                    'ep.`facility+id`' => 'facility',
                    'ep.`facility_county+id`' => 'county',
                    'ep.`facility_district+id`' => 'district',
                    'ep.`facility_division+id`' => 'division' 
                    ),
                'promotion' => array( 
                    ),
                 );

        $lookups = array( 
                'default' => array( 
                    'person+person_title' => 'person_title',
                    'person+nationality' => 'country',
                    'person+blood_group' => 'blood_group',
                    'person+perm_residence' => 'district',
                    'demographic+marital_status' => 'marital_status',
                    'demographic+caste' => 'caste',
                    'demographic+gender' => 'gender',
                    'demographic+minorities' => 'minorities',
                    'id+id_type' => 'id_type',
                    'nominee+relationship' => 'relationship',
                    'position+facility' => 'facility',
                    'position+pos_type' => 'position_type',
                    'joining+job' => array( 'job', 'title' ),
                    'deputation+position' => array( 'position', 'title' ),
                    'primary_form+govt_order_authority' => 'govt_order_authority',
                    'deputation+govt_order_authority' => 'govt_order_authority',
                    'service+job' => array( 'job', 'title' ),
                    'service+facility' => 'facility',
                    'service+district' => 'district',
                    'service+pos_change_reason' => 'pos_change_reason',
                    'service2+job' => array( 'job', 'title' ),
                    'service2+facility' => 'facility',
                    'service2+district' => 'district',
                    'service2+pos_change_reason' => 'pos_change_reason',
                    'service3+job' => array( 'job', 'title' ),
                    'service3+facility' => 'facility',
                    'service3+district' => 'district',
                    'service3+pos_change_reason' => 'pos_change_reason',
                    'servicehistory4+job' => array( 'job', 'title' ),
                    'servicehistory4+facility' => 'facility',
                    'servicehistory4+district' => 'district',
                    'servicehistory4+pos_change_reason' => 'pos_change_reason',
                    'servicehistory5+job' => array( 'job', 'title' ),
                    'servicehistory5+facility' => 'facility',
                    'servicehistory5+district' => 'district',
                    'servicehistory5+pos_change_reason' => 'pos_change_reason',
                    'servicehistory6+job' => array( 'job', 'title' ),
                    'servicehistory6+facility' => 'facility',
                    'servicehistory6+district' => 'district',
                    'servicehistory6+pos_change_reason' => 'pos_change_reason',
                    'edu+degree' => 'degree',
                    'education+degree' => 'degree',
                    'primary_form+designation_grade' => 'designation_grade',
                    'job+salary_grade' => 'salary_grade',
                    'specialty1+specialty' => 'specialty',
                    'specialty2+specialty' => 'specialty',
                    'specialty3+specialty' => 'specialty',
                    'training+training_subject' => 'training_subject',
                    'training+level_of_training' => 'level_of_training',
                    ),
                'promotion' => array( 
                       
                        'position+job' => array( 'job', 'title'),
                        'position+facility' => 'facility',
                        'person+person_title' => 'person_title',
                        'service+job' => array( 'job', 'title'),
                        'service+facility' => 'facility',
                        'service+pos_change_reason' => 'pos_change_reason',
                        'service+district' => 'county||district',
                        'service2+job' => array( 'job', 'title'),
                        'service2+facility' => 'facility',
                        'service2+pos_change_reason' => 'pos_change_reason',
                        'service2+district' => 'county||district',
                        'service3+job' => array( 'job', 'title'),
                        'service3+facility' => 'facility',
                        'service3+pos_change_reason' => 'pos_change_reason',
                        'service3+district' => 'county||district',
                        'servicehistory4+job' => array( 'job', 'title'),
                        'servicehistory4+facility' => 'facility',
                        'servicehistory4+pos_change_reason' => 'pos_change_reason',
                        'servicehistory4+district' => 'county||district',
                        'servicehistory5+job' => array( 'job', 'title'),
                        'servicehistory5+facility' => 'facility',
                        'servicehistory5+pos_change_reason' => 'pos_change_reason',
                        'servicehistory5+district' => 'county||district',
                        'servicehistory6+job' => array( 'job', 'title'),
                        'servicehistory6+facility' => 'facility',
                        'servicehistory6+pos_change_reason' => 'pos_change_reason',
                        'servicehistory6+district' => 'county||district',
                        'servicehistory7+job' => array( 'job', 'title'),
                        'servicehistory7+facility' => 'facility',
                        'servicehistory7+pos_change_reason' => 'pos_change_reason',
                        'servicehistory7+district' => 'county||district',
                        'service_history8+job' => array( 'job', 'title'),
                        'service_history8+facility' => 'facility',
                        'service_history8+pos_change_reason' => 'pos_change_reason',
                        'service_history8+district' => 'county||district',
                        'service_history9+job' => array( 'job', 'title'),
                        'service_history9+facility' => 'facility',
                        'service_history9+pos_change_reason' => 'pos_change_reason',
                        'service_history9+district' => 'county||district',
                        'service_history10+job' => array( 'job', 'title'),
                        'service_history10+facility' => 'facility',
                        'service_history10+pos_change_reason' => 'pos_change_reason',
                        'service_history10+district' => 'county||district',
                        'service_history11+job' => array( 'job', 'title'),
                        'service_history11+facility' => 'facility',
                        'service_history11+pos_change_reason' => 'pos_change_reason',
                        'service_history11+district' => 'county||district',
                        'service_history12+job' => array( 'job', 'title'),
                        'service_history12+facility' => 'facility',
                        'service_history12+pos_change_reason' => 'pos_change_reason',
                        'service_history12+district' => 'county||district',
                        'service_history13+job' => array( 'job', 'title'),
                        'service_history13+facility' => 'facility',
                        'service_history13+pos_change_reason' => 'pos_change_reason',
                        'service_history13+district' => 'county||district',
                        'service_history14+job' => array( 'job', 'title'),
                        'service_history14+facility' => 'facility',
                        'service_history14+pos_change_reason' => 'pos_change_reason',
                        'service_history14+district' => 'county||district',
                        'service_history15+job' => array( 'job', 'title'),
                        'service_history15+facility' => 'facility',
                        'service_history15+pos_change_reason' => 'pos_change_reason',
                        'service_history15+district' => 'county||district',
                        'service_history16+job' => array( 'job', 'title'),
                        'service_history16+facility' => 'facility',
                        'service_history16+pos_change_reason' => 'pos_change_reason',
                        'service_history16+district' => 'county||district',
                        'service_history17+job' => array( 'job', 'title'),
                        'service_history17+facility' => 'facility',
                        'service_history17+pos_change_reason' => 'pos_change_reason',
                        'service_history17+district' => 'county||district',
                        'service_history18+job' => array( 'job', 'title'),
                        'service_history18+facility' => 'facility',
                        'service_history18+pos_change_reason' => 'pos_change_reason',
                        'service_history18+district' => 'county||district',
                        'service_history19+job' => array( 'job', 'title'),
                        'service_history19+facility' => 'facility',
                        'service_history19+pos_change_reason' => 'pos_change_reason',
                        'service_history19+district' => 'county||district',
                        'service_history20+job' => array( 'job', 'title'),
                        'service_history20+facility' => 'facility',
                        'service_history20+pos_change_reason' => 'pos_change_reason',
                        'service_history20+district' => 'county||district',
                        ),
                );


        $db = MDB2::singleton();
        $moduleObj = I2CE_ModuleFactory::instance()->getClass( "ManageAccessFacility" );
        $or_wheres = array();
        foreach( $module_limits[$page] as $field => $link ) {
            $allowed = $moduleObj->getLimitsByForm( $link );
            if ( $allowed === true ) {
                continue;
            } elseif ( is_array( $allowed ) ) {
                if ( count($allowed) > 0 ) {
                    $or_wheres[] = "$field IN ( '" . implode( "','", $allowed ) . "' )";
                }
            }
        }

        $wheres = array();
        if ( $district !== null ) {
            $wheres[] = "ep.`facility_district+id` = '$district'";
        } elseif ( $division !== null ) {
            $wheres[] = "ep.`facility_division+id` = '$division'";
        } elseif ( $state ) {
            $wheres[] = "ep.`facility+location` = 'region|BR'";
        }
        $where = '';
        if ( $page == 'promotion' ) {
            $wheres[] = "pp.`position+job` NOT IN ( 'job|11', 'job|26' )";
        }
        if ( count( $wheres ) > 0 ) {
            $where = ' WHERE ( ' . implode( ' AND ', $wheres ) . ') ';
        }
        if ( count( $or_wheres ) > 0 ) {
            if ( $where == '' ) {
                $where = ' WHERE ';
            } else {
                $where .= ' AND ';
            }
            $where .= '( ' . implode( ' OR ', $or_wheres ) . ' )';
        }
        $selects = array();
        $headers = array( 'Last Modified' );
        $joins = array();
        $join_aliases = array();
        //added by ifhaam for masking privacy related data;
        $fieldMap = array();
        $index = 0;
        foreach( $fields[$page] as $table => $list ) {
            foreach( $list as $field => $header ) {
                //changes for hiding privacy related data made by ifhaam
                // on 8-1-2017 starts here
                switch($field){
                    case "id+id_num":
                        $fieldMap['id_num'] = $index;
                        break;

                    case "id+id_type":
                        $fieldMap['id_type'] = $index;
                        break;

                    case "work+mobile_phone":
                        $fieldMap['mobile'] = $index;
                        break;


                }
                $index++;
                //change by ifhaam ends here
                if ( array_key_exists( $field, $lookups[$page] ) ) {
                    $lookup_form = $lookups[$page][$field];
                    if ( is_array( $lookup_form ) ) {
                        list( $lookup_form, $lookup_field ) = $lookup_form;
                    } else {
                        $lookup_field = 'name';
                    }
                    if ( strpos( $lookup_form, '||' ) !== false ) {
                        $form_list = explode( '||', $lookup_form );
                    } else {
                        $form_list = array( $lookup_form );
                    }
                    foreach( $form_list as $lookup_form ) {
                        if ( !array_key_exists( $lookup_form, $join_aliases ) ) {
                            $cachedForm = new I2CE_CachedForm( $lookup_form );
                            $cachedForm->generateCachedTable();
                            $join_aliases[$lookup_form] = 1;
                        }
                        $join_table = "$field+$lookup_form";
                    }
                    if ( count($form_list) == 1 ) {
                        $lookup_form = $form_list[0];
                        //$joins[] = "LEFT JOIN `hippo_" . $lookup_form . "` AS `" . $join_table . "` ON `" . $join_table . "`.id = `$table`.`$field`"; 
                        //$selects[] = "`$field+$lookup_form`.`$lookup_field` AS `$field`";
                        $selects[] = "(SELECT `$lookup_field` FROM `hippo_$lookup_form` WHERE id = `$table`.`$field`) AS `$field`";
                    } else {
                        $end = count($form_list) - 1;
                        $select = "";
                        for( $i = $end; $i >= 0; $i-- ) {
                            $lookup_form = $form_list[$i];
                            if ( $i == $end ) {
                                $select = "(SELECT `$lookup_field` FROM `hippo_$lookup_form` WHERE id = `$table`.`$field`)";
                            } else {
                                $select = "IFNULL( (SELECT `$lookup_field` FROM `hippo_$lookup_form` WHERE id = `$table`.`$field`), $select )";
                            }
                        }
                        $selects[] = "$select AS `$field`";
                    }
                } elseif ( $field == 'training+from_date' ) {
                    $selects[] = "IF(`$table`.`$field` = '0000-00-00 00:00:00', '', YEAR( `$table`.`$field` ) ) AS `$field`";
                } elseif ( strpos( $field, 'date' ) !== false ) {
                    $selects[] = "IF(`$table`.`$field` = '0000-00-00 00:00:00', '', DATE_FORMAT( `$table`.`$field`, '%d/%m/%Y' )) AS `$field`";
                } elseif ( strpos( $field, 'year' ) !== false ) {
                    $selects[] = "IF(`$table`.`$field` = '0000-00-00 00:00:00', '', YEAR( `$table`.`$field` ) ) AS `$field`";
                } elseif ( $field == 'family2+spouse_doctor' || $field == 'family2+spouse_employee' || $field == 'demographic+handicap' ) {
                    $selects[] = "IF(`$table`.`$field` = 1, 'Yes', 'No') AS `$field`";
                } elseif( $field == 'current_salary+salary' ) {
                    $selects[] = "SUBSTRING(`$table`.`$field`, 12) AS `$field`";
                } else {
                    $selects[] = "`$table`.`$field`";
                }
                $headers[] = $header;
            }
        }
        $queries = array( 'default' => "SELECT DATE_FORMAT(GREATEST(ep.last_modified,ep2.last_modified), '%%d/%%m/%%Y %%h:%%m:%%s') AS last_modified,%s FROM zebra_employee_profile ep LEFT JOIN zebra_Employeeprofile2 ep2 ON ep.`primary_form+id` = ep2.`primary_form+id` %s %s ORDER BY `person+surname`, `person+firstname`",
                'promotion' => "SELECT DATE_FORMAT(GREATEST(pp.last_modified,pp2.last_modified), '%%d/%%m/%%Y %%h:%%m:%%s') AS last_modified,%s FROM zebra_Promotionandpostingfinal pp LEFT JOIN zebra_Promotionandpostingfinal2 pp2 ON pp.`primary_form+id` = pp2.`primary_form+id` %s %s ORDER BY `person+firstname`",
                );
        //$qry = "SELECT DATE_FORMAT(GREATEST(ep.last_modified,ep2.last_modified), '%d/%m/%Y %h:%m:%s') AS last_modified," . implode( ',', $selects ) . " FROM zebra_employee_profile ep JOIN zebra_Employeeprofile2 ep2 ON ep.`primary_form+id` = ep2.`primary_form+id` " . implode( ' ', $joins ) . " $where ORDER BY `person+surname`, `person+firstname`";
        $qry = sprintf( $queries[$page], implode( ',', $selects ), implode( ' ', $joins ), $where );
$tf = fopen( "/tmp/prom_query.sql", "w" );
fwrite( $tf, $qry );
fclose( $tf );
        I2CE::raiseMessage("Query: " . $qry);
        $results = $db->query( $qry );

        if ( !I2CE::pearError( $results, "Unable to get profile results: " ) ) {
            $filename = 'EmployeeProfile_' . date('dMY') . '.csv';
            if ( $page == 'promotion' ) {
            $filename = 'PromotionAndPosting_' . date('dMY') . '.csv';
            }
            header("Content-disposition: attachment; filename=\"$filename\"");
            if (preg_match('/\s+MSIE\s+\d\.\d;/',$_SERVER['HTTP_USER_AGENT'])) {
                header("Content-type: application/vnd.ms-excel");
            } else{
                header("Content-type: text/csv; charset=UTF-8");
            }       
            flush();
            $out = fopen( "php://output", 'w' );
            fputcsv( $out, $headers );
            $row_num = 0;
            //change for hiding privacy data made by ifhaam on 8-1-2017
            //starts here
            $role = I2CE_UserAccess_Mechanism::getSessionRole();
            if($role!='admin'){
                while( $data = $results->fetchRow( MDB2_FETCHMODE_ORDERED) ) {
                    foreach($fieldMap as $item=>$value){
                        $stars = "******************************";

                        switch($item){

                            case "id_num":

                                if($data[$fieldMap["id_type"]+1]=="PAN No." || $data[$fieldMap["id_type"]+1]=="National ID No. (Aadhar Card No.)"){

                                        $val = $data[$value +1];
                                        $len = strlen($val);
                                        $data[$value+1] = substr($stars,34-$len).substr($val,$len-4);
                                }

                                break;

                            case "mobile":
                                $val = $data[$value +1];
                                $len = strlen($val);
                                $data[$value+1] = substr($stars,34-$len).substr($val,$len-4);
                                break;
                        }
                    }
                    if ( ++$row_num % 500 == 0 && array_key_exists( 'HTTP_HOST', $_SERVER )
                        && I2CE_Dumper::usesDefaultOutputBuffering() ) {
                        ob_flush();
                    }
                    fputcsv( $out, $data );

                }
            }else{
                while( $data = $results->fetchRow( MDB2_FETCHMODE_ORDERED) ) {

                    if ( ++$row_num % 500 == 0 && array_key_exists( 'HTTP_HOST', $_SERVER )
                        && I2CE_Dumper::usesDefaultOutputBuffering() ) {
                        ob_flush();
                    }
                    fputcsv( $out, $data );

                }
            }


            //ends here
            //lines commented below were the old code
//            while( $data = $results->fetchRow( MDB2_FETCHMODE_ORDERED) ) {
//
//                if ( ++$row_num % 500 == 0 && array_key_exists( 'HTTP_HOST', $_SERVER )
//                        && I2CE_Dumper::usesDefaultOutputBuffering() ) {
//                    ob_flush();
//                }
//                fputcsv( $out, $data );
//
//            }
            flush();
            exit();
        } else {
            $this->userMessage("An error has occurred: " . $results->getMessage() );
            $this->action_district();
        }

    }

}


# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
