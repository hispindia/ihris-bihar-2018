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
 * @author Sovello Hildebrand <smgani@intrahealth.org>
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

class iHRIS_PageFormSalarybreakdown extends iHRIS_PageFormParentPerson  {

    /**
     * @var I2CE_Form The position object this person has been assigned to.
     */
    private $person_id;


    /**
     * main actions for the page
     *
     */
    public function action() {        
        $this->template->addHeaderLink('payroll_functions.js');
		$this->template->addFile('form_salary_breakdown_base.html');
		$this->loadFormTemplates();
		return parent::action();
      }

    protected function loadFormTemplates(){
        if( !$this->isPost() ){
			$last_pos_type = $this->getLastPositionType(I2CE_FormFactory::instance()->createContainer($this->request('parent')));        
			#$salary_breakdown->populate();
			#$last_pos_type = $salary_breakdown->getField('position_type')->getDBValue();
        }
		elseif ( $this->isPost() ) {
			$primaryObj = $this->factory->createContainer($this->getForm());
			$primaryObj->load($this->post);
			$personObject = I2CE_FormFactory::instance()->createContainer($this->request('parent'));
			$last_pos_type = $primaryObj->getField('position_type')->getDBValue();
		}
		
		if ( $last_pos_type == $this->regular_position_type ){
		  $this->template->addFile( "form_salarybreakdown_regular.html", 'tbody', 'person_form');
		}
		else{
		  $this->template->addFile( "form_salarybreakdown_contract.html", 'tbody', 'person_form');
		  }
      }

    protected $contractual_position_type = 'position_type|2'; //there is a need to set this automatically
    protected $regular_position_type = 'position_type|1'; //there is a need to set this automatically

    /**
     * Create and load data for the objects used for this form.
     *
     * Set the default position_type to be same as one for the position this person is holding
     * training.
     */
    protected function loadObjects() {
		parent::loadObjects();		
      $salaryBreakdown = $this->factory->createContainer('person_position_salarybreakdown|0');
      if ( !$this->isPost() ){
        $last_position_type = $this->getLastPositionType(I2CE_FormFactory::instance()->createContainer($this->request('parent')));        
        $this->getPrimary()->position_type = explode('|', $last_position_type);
        $this->person_id = $this->request('parent');
      }else if($this->isPost() ){
        $salaryBreakdown->load($this->post);
        $this->person_id = $this->getPrimary()->getParent();
      }
		return true;
    }


	/**
     * Find and return the most recent position held by this person.
     *
     * @return I2CE_Form or null
     */
    protected function getLastPositionType($person) {
        if (!$person instanceof iHRIS_Person) {
            return null;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'end_date',
            'style'=>'null'
            );
        $per_pos_id = I2CE_FormStorage::search('person_position', $person->getNameId(),$where,'-start_date',1);
        if (!$per_pos_id) {
            return null;
        }
        $personPos = I2CE_FormFactory::instance()->createContainer('person_position'.'|'.$per_pos_id);
        if (!$personPos instanceof iHRIS_PersonPosition) {
            return null;
        }
        $personPos->populate();
        $position = $personPos->getField('position')->getDBValue();
        $posObj = I2CE_FormFactory::instance()->createContainer($position);
        $posObj->populate();
        return $posObj->getField('pos_type')->getDBValue();
        //return $pos_type;
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
