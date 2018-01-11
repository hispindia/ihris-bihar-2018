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
 * Manage editing person position departure in the database.
 *
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc.
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the editing of person position departure in the database.
 *
 * @package iHRIS
 * @subpackage Manage
 * @access public
 */
class iHRIS_Module_Payroll extends I2CE_Module  {

    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_person_position_salarybreakdown' => 'action_person_position_salarybreakdown',
            'iHRIS_PageView->action_person_monthly_salary' => 'action_person_monthly_salary',
            'iHRIS_Person->getLastSalaryBreakdown' => 'getLastSalaryBreakdown',
            'iHRIS_Person->getLastMonthlySalary' => 'getLastMonthlySalary'
            );
    }

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
            'validate_form_person_position_salarybreakdown' => 'validate_form_person_position_salarybreakdown',
			'validate_form_person_position_salarybreakdown_field_bankaccnum' => 'validate_form_person_position_salarybreakdown_field_bankaccnum',
			'validate_form_person_position_salarybreakdown_field_tanofdeductor' => 'validate_form_person_position_salarybreakdown_field_tanofdeductor',
			'validate_form_person_position_salarybreakdown_field_panofdeductor' => 'validate_form_person_position_salarybreakdown_field_panofdeductor',
        );
    }

	/**
	 * Validate bank account number to only contain 9 to 18 numeric characters only
	 * @param I2CE_FormField $formfield
	 */
	public function validate_form_person_position_salarybreakdown_field_bankaccnum($formfield){
		$value = $formfield->getValue();
		if(!preg_match("/\b\d{9,18}\b/", $value) && !empty($value)){
			$formfield->setInvalidMessage('invalid_bank_account');
		}
	}

//	public function validate_from_person_position_salarybreakdown_field_bankname($formfield){
//	    $value =$formfield-getvalue();
//        if($value!=0&&empty($value)){
//            $formfield->setInvalidMessage('Please select Bank name');
//        }
//    }

	/**
	 * Validate bank account number to only contain 4 alphabets then 5 numerals then one alphabet
	 * @param I2CE_FormField $formfield
	 */
	public function validate_form_person_position_salarybreakdown_field_tanofdeductor($formfield){
		$value = $formfield->getValue();
		if(!preg_match("/\b[a-zA-Z]{4}\d{5}[a-zA-Z]{1}\b/", $value) && !empty($value)){
			$formfield->setInvalidMessage('invalid_tan_structure');
		}
	}

	/**
	 * Validate PAN number to only contain 5 alphabets then 4 numerals and then 1 alphabet
	 */
	public function validate_form_person_position_salarybreakdown_field_panofdeductor($formfield){
		$value = $formfield->getValue();
		if(!preg_match("/\b[a-zA-Z]{5}\d{4}[a-zA-Z]{1}\b/", $value) && !empty($value)){
			$formfield->setInvalidMessage('invalid_pan_structure');
		}
	}

    protected $contractual_position_type = 'position_type|2'; //there is a need to set this automatically
    protected $regular_position_type = 'position_type|1'; //there is a need to set this automatically

    /**
     * Return the most recent monthly salary this person was paid
     *
     * @return I2CE_Form or null
     */
    public function getLastMonthlySalary($person) {
        if (!$person instanceof iHRIS_Person) {
            return null;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'month',
            'style'=>'not_null'
            );
        $last_monthly_salary_id = I2CE_FormStorage::search('person_monthly_salary', $person->getNameId(),$where,array('-month'),1);
        if (!$last_monthly_salary_id) {
            return null;
        }
        $last_monthly_salary = I2CE_FormFactory::instance()->createContainer('person_monthly_salary|'.$last_monthly_salary_id);
        if (!$last_monthly_salary instanceof iHRIS_Bihar_MonthlySalary) {
            return null;
        }
        $last_monthly_salary->populate();
        return $last_monthly_salary;
    }

    /**
     * Return the most recent monthly salary this person was paid
     *
     * @return I2CE_Form or null
     */
    public function getLastSalaryBreakdown($person) {
        if (!$person instanceof iHRIS_Person) {
            return null;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'setup_date',
            'style'=>'not_null'
            );
        $breakdown = I2CE_FormStorage::search('person_position_salarybreakdown', $person->getNameId(),$where,array('-setup_date'),1);
        if (!$breakdown) {
            return null;
        }
        $salary_breakdown = I2CE_FormFactory::instance()->createContainer('person_position_salarybreakdown|'.$breakdown);
        if (!($salary_breakdown instanceof iHRIS_Bihar_SalaryBreakdown)) {
            return null;
        }
        $salary_breakdown->populate();
        //change for bank account number hiding by ifhaam on 5-1-2018 starts
        //TODO: Hide pan num also
        $role = I2CE_UserAccess_Mechanism::getSessionRole();
        if($role!='admin'){
            $val = $salary_breakdown->getField('bankaccnum')->getValue();
            $salary_breakdown->bankaccnum = '*******'.substr($val,strlen($val)-4);
        }

        //change ends
        return $salary_breakdown;
    }


    public function action_person_position_salarybreakdown($page) {

        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
		$person = $page->getPerson();
		if( !$person instanceof iHRIS_Person){
			return false;
		}

		$template = $page->getTemplate();

		if (  ($lastPos = $this->getLastSalaryBreakdown($person)) instanceof iHRIS_Bihar_SalaryBreakdown) {
			$person->addChildForm($lastPos);

			$childNode = $page->appendChildTemplate('person_position_salarybreakdown','siteContent');
			//$childNode->appendChild();
/* Don't do this as it adds a blank child form and breaks things.  The above should handle what is necessary.
		} else {
			$childNode = $page->addLastChildForm('person_position_salarybreakdown','setup_date', 'siteContent');
*/
		}

        return $childNode;
    }

    public function action_person_monthly_salary($page) {
        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
		$person = $page->getPerson();
		if( !$person instanceof iHRIS_Person){
			return false;
		}

		$template = $page->getTemplate();
		if (  ($lastPos = $this->getLastMonthlySalary($person)) instanceof iHRIS_Bihar_MonthlySalary) {
			$person->addChildForm($lastPos);
			$childNode = $page->appendChildTemplate('person_monthly_salary','siteContent');
		} else {
			$childNode = $page->addLastChildForm('person_monthly_salary','month', 'siteContent');
		}
        return $childNode;
    }

    public function validate_form_person_position_salarybreakdown($form){
        $float_number = '/\d+\.?d*/';
        /**This is for the columns validation on salary breakdown of regular employee and contractual Employees**/
       /* $required_fields = array(
                "regular" => array('empcode','epfaccnum','bankaccnum','pannumber','bankname','branch','branch_code','ifsc','medical_allowance','dearness_allowance','deputation_allowance','gpf','gi','income_tax','grade_pay','hra',),
                "contract" => array('tds')
                );*/
        $required_fields = array(
            "regular" => array('bankaccnum','pannumber','bankname','branch','branch_code','ifsc'),
            "contract" => array('bankaccnum','pannumber','bankname','branch','branch_code','ifsc')
        );
   //     $contract_fields = array('epfaccnum','bankaccnum','pannumber','bankname','branch','branch_code','ifsc',);
   //     $regular_fields = array('epfaccnum','bankaccnum','pannumber','bankname','branch','branch_code','ifsc',);
        $position_type = implode("|",$form->position_type);

//       if( $position_type == $this->contractual_position_type ){
//            foreach($required_fields['contract'] as $field){
//                if( empty($form->$field) ){
//                    $form->setInvalidMessage($field,'required');
//                }
//            }
//            /*foreach($contract_fields as $field){
//                if( !I2CE_Validate::checkNumber($form->$field, -1)){
//                    $form->setInvalidMessage($field, 'numeric');
//                }
//            }*/
//        }
//        else

        if( $position_type == $this->regular_position_type ){
            foreach( $required_fields['regular'] as $field ){
                if( empty($form->$field) ){
                    $form->setInvalidMessage($field, 'required');
                }
            }
        }
        elseif($position_type==$this->contractual_position_type){
            foreach ($required_fields['contract'] as $field){
                if(empty($form->$field)){
                    $form->setInvalidMessage($field,'required');
                }

            }
        }
        $gross_pay = $form->salary + $form->salaryarrear + $form->otherarrear + $form->basic_pay
            + $form->grade_pay + $form->hra + $form->medical_allowance + $form->deputation_allowance+$form->dearness_allowance;
        $deductions = $form->tds + $form->epf + $form->gpf + $form->professionaltax + $form->otherdeductions
            + $form->gi + $form->income_tax;

        $form->gross_salary = $gross_pay;
        $form->net_salary_payable = $gross_pay - $deductions;


    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
