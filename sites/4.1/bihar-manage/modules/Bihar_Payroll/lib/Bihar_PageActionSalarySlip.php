<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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
 * Edit participants action for a training
 * @package iHRIS
 * @subpackage Common
 * @access public
 * @author  Sovello Hildebrand <smgani@gmail.com> 
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.3
 * @version v4.1.3
 */

/**
 * The action page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Train
 * @access public
 */
class Bihar_PageActionSalarySlip extends I2CE_PageGenerateRelationshipTemplate {

    protected $person_id = array();
    
    protected $monthly_salary_id;	
	protected $primObj;
	protected $print_all = false;
	
    protected function loadPrimary() {
		if( $this->request_exists('all') && $this->request_exists('all') === true){
			$this->print_all = true;
		}
		 
		if( $this->request_exists('parent')){
			$this->person_id[] = $this->request('parent');
		}
		if($this->request_exists('monthly_salary_id')
			&& in_array('person',$this->formRelationship->getFormNames())
			&& ($monthly_salary_id = $this->request('monthly_salary_id'))
			&& ($pos = strpos($monthly_salary_id,'|')) !== false
			&& ($id = substr($monthly_salary_id,$pos + 1)) != ''){
			$formFactory = I2CE_FormFactory::instance();
			$this->primObj = $formFactory->createContainer($monthly_salary_id);
			if (! $this->primObj instanceof I2CE_Form
			 || $this->formRelationship->getPrimaryForm() != $this->primObj->getName()
			) {
				I2CE::raiseError("invalid form id :" . print_r($this->request('monthyl_salary_id'),true) . "\ndoes not match " . $this->formRelationship->getPrimaryForm());
				return false;
			}
			$this->primObj->populate();
		}
		
    }

	
    /**
     *Loads in the requeted data from the relationship
     * @returns boolean  True on success
     */
    protected function loadData($as_iterator = true) {
		if ( !$this->print_all) {
    		if(count($this->person_id)>0){
				$p_where = array(
					'operator'=>'FIELD_LIMIT',
					'field'=>'parent',
					'style'=>'in',
					'data'=>array(
						'value'=>$this->person_id
                    )
                );
				$this->formRelationship->setAdditionalLimit('person_monthly_salary',$p_where);
      
			}		
			$salary_month = $this->primObj->getField('month')->getDBValue();
			// in case we can include the second limit from here, we will need to modify this,		

                        /*
			   $where_breakdown = array(
				   'operator'=>'FIELD_LIMIT',
					   'field'=>'setup_date',
					   'style'=>'lessthan_equals',
					   'data'=>array(
						   'value'=>$this->zeroHours($salary_month)
					   )				
			   );			
                        */
			   $where_breakdown = array(
				   'operator'=>'FIELD_LIMIT',
					   'field'=>'setup_date',
					   'style'=>'max_parent',
					   'data'=>array(
						   'extra_where'=>"DATE(setup_date) <= '" . $this->zeroHours($salary_month) . "'",
                                                   //'value'=>$this->person_id,
                                                   'parent_id' => $this->primObj->getParent(),
					   )				
			   );			
			$this->formRelationship->setAdditionalLimit('person_position_salarybreakdown', $where_breakdown);
		} else {
       			   $where_breakdown = array(
				   'operator'=>'FIELD_LIMIT',
					   'field'=>'setup_date',
					   'style'=>'max_parent_form',
			   );			
			$this->formRelationship->setAdditionalLimit('person_position_salarybreakdown', $where_breakdown);
         }
		return parent::loadData($as_iterator);
    }

	protected function zeroHours($date){		
		$formatted_date = preg_split('/[\-]/', $date, 3);
		$number = cal_days_in_month(CAL_GREGORIAN, $formatted_date[1], 2003);		
		return $formatted_date[0]."-".$formatted_date[1]."-".$number." 00:00:00";
	}

    protected function getPositionType(){
        
    	$where=array(
            "operator"=>"FIELD_LIMIT",
            "field"=>"parent",
            "style"=>"equals",
            "data"=>array(
                "value"=>$this->request("parent")
            )          
        );
        $position_type = I2CE_FormStorage::listFields('person_position_salarybreakdown',array('position_type'), false, $where, array('-setup_date'), 1);
        return current($position_type);
    }

    protected function getFields() {
        $fields = parent::getFields();
        $fields['primary_form'][] = 'leave_days';
        $fields['primary_form'][] = 'daily_salary';
        return $fields;
    }

    protected function generateTemplate_global( $odf ) {
        $retVal = parent::generateTemplate_global( $odf );

        try {
            $leave_amount = 0;
            foreach( $this->data as $formfields ) {
                $leave_amount = $formfields['primary_form']['leave_days'] * $formfields['primary_form']['daily_salary'];
            }
            $odf->setVars( "++leave_amount", $leave_amount, $this->encoding, $this->charset );
        } catch( OdfException $e ) {
            // It's ok if it's not there so don't do anything.
        }

        return $retVal;
    }

    protected function getODTTemplate() {
		$formFactory = I2CE_FormFactory::instance();
		if (!$this->print_all){
			if( count($this->person_id) > 0 ){
            //first we see if we have a certificate uploaded for the position type
				$have_salary_slip =false;				
				$posType = current($this->getPositionType());
				I2CE::raiseError("The position type is $posType");
				$postypeObj = $formFactory->createContainer($posType);
				$postypeObj->populate();				            
			}
		}
		else {
			$postypeObj = $formFactory->createContainer($this->request('position_type'));
			$postypeObj->populate();
		}
		if ( ($docField = $postypeObj->getField('salary_receipt')) instanceof I2CE_FormField_DOCUMENT
		&& $docField->isValid()
		&& ($content = $docField->getValue())
			&& ($name = $docField->getFileName())) {
			I2CE::raiseError("Populate worked");
			$pos = strrpos($name,'.');
			$ext ='';                
			if ($pos !== false) {
				$ext = substr($name,$pos);
				$name = substr($name, 0,$pos);
				I2CE::raiseError("The template file name is $name");
			}
			$this->template_file = tempnam(sys_get_temp_dir(), basename($name .'_' ))  . $ext;
			I2CE::raiseError($this->template_file);
			file_put_contents($this->template_file,$content);
            return true;
		}
		return parent::getODTTemplate();
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
