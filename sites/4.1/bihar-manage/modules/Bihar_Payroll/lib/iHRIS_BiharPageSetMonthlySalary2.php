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


class iHRIS_BiharPageSetMonthlySalary extends Bihar_CustomReport_Display_DefaultAction {

        /*
     * This method is actually to read data from magicdata, but since we failed, I will hard code it here.
     */
    protected function getActionHeader(){
        $config = I2CE::getConfig()->modules->CustomReports;
        $header = $config->getAsArray("reportViews/1432260225/default_display_options/header");
        return $header;
       //return "Action";
      }

    protected function getActionNode($field_args){
        $input_field = $this->template->createElement( "input", 
                array( "type" => "text" ) );
        $js = "savePayableMonthlySalary(this,'".addslashes($personid)."');";
        $input_field->setAttribute('onchange',$js);
        return array($input_field );
      }
    
    protected function getActionFields(){
        $config = I2CE::getConfig()->modules->CustomReports;
        return $config->getAsArray("reportViews/1432260225/default_display_options/fields");
      }

    
    /**
     * main actions for the page
     * 
     */
    public function action() {
      parent::action();
      $this->template->addHeaderLink("payroll.css");
      $this->template->addHeaderLink("mootools-core.js");
      $this->template->addHeaderLink("mootools-more.js");
      $this->template->addHeaderLink("payroll_functions.js");
      $this->getFacilityEmployees();
    }
    
    /**
     * fields
     */
    protected $monthly_salary_fields = array(
        'month','working_days','daily_salary','monthyl_salary'
      );
    /**
     * Get a list of all employees from a facility this person is working
     * @return boolean
     */
     
    public function getFacilityEmployees(){
      if (! ($listNode = $this->template->getElementByID("staff_list")) instanceof DOMNode) {
            return ;
        }
        $whereAccessFacility = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'parent',
            'style'=>'equals',
            'data'=>array(
                'value'=>'user|'.$this->getUser()->username
                )
          );
        $positions = array();
        $location = I2CE_FormStorage::listFields('accesss_facility', array('location'), false, $whereAccessFacility);
        if( count($location) == 0 ){
            //list all employees found in here when the user is not limited to access only a certain facility
            $wherePosition = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'end_date',
                'style'=>'null'
              );    
            $persons = I2CE_FormStorage::listFields('person_position',array('parent'), false, $wherePosition);
            foreach($persons as $id => $personid){
                //$personObj = I2CE_FormFactory::instance()->createContainer($personid['parent']);
                //$personObj->populate();
                  $parent = explode("|",$personid['parent']);
                  $employee_name = I2CE_FormStorage::lookupField("person",$parent[1],array('firstname' , 'surname'), " ");
                  $aNode =$this->template->createElement("a",array('href'=>"view?id=" . $personid['parent']),$employee_name);
                  $trNode =$this->template->createElement("tr");
                  $tdNode =$this->template->createElement("td");
                  $staffName = $this->template->appendNode($aNode,$tdNode);
                  $this->template->appendNode($aNode,$tdNode);
                  $this->template->appendNode($tdNode,$trNode);
                  foreach($this->monthly_salary_fields as $field){
                      $tdsNode =$this->template->createElement("td");
                      $node =$this->template->createElement("input",array('size'=>"10", 'type'=>"text", 'name'=>$field),'');
                      $this->template->appendNode($node,$tdsNode);
                      $this->template->appendNode($tdsNode,$trNode);
                    }
                  $this->template->appendNode($trNode,$listNode);
              }
          }
        else{
          //fetching all positions found in each of the locations/facilities this user has access to
          foreach($location as $id => $locationid){
              if( in_array('facility', explode("|",$locationid) ) ){
                //if the user is directly linked to a facility, then return all positions that are OPEN AND under this facility
                  $wherePosition = array(
                      'operator'=>'FIELD_LIMIT',
                      'field'=>'facility',
                      'style'=>'equals',
                      'data'=>array(
                        'value'=>$locationid
                        )
                    );
                }
              elseif( in_array('district', explode("|",$locationid) ) ){
                  //get all counties in this district
                  $wherePosition = array(
                      'operator'=>'FIELD_LIMIT',
                      'field'=>'district',
                      'style'=>'equals',
                      'data'=>array(
                        'value'=>'user|'.$locationid
                        )
                    );
                }
            }
          }
      }
    
    protected $allowed_forms = array(
      'region' => array('division|region','district|division','county|district'),
      'division' => array('district|division','county|district'),
      'district' => array('county|district'),
      'county' => array('facility|location')
    );
    
    /**
     * get facilities from a location.
     * $location is a form name like district
     */
    
    protected function getFacilities($location, $id){
        $facilities = array();
        if( $location = 'county' ){
            $where = array(
                'operator'=>'FIELD_LIMIT',
                'field'=>'location',
                'style'=>'equals',
                'data'=>array(
                  'value'=>'county|'.$id
                  )
              );
            $facilities = I2CE_FormStorage::search('facility', false, $where);
          }
        else{
          if( in_array($location, array_keys($this->allowed_forms ) ) ){
              $start_search_at = $this->allowed_forms[$location];
              foreach($start_search_at as $forms){
                  $forms = explode("|",$forms);
                  $form = $forms[0];
                  $mapped_form = $forms[1];
                  $where_location = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>$mapped_form,
                    'style'=>'equals',
                    'data'=>array(
                      'value'=>$form."|".$id
                      )
                  );
                  $where_facility = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'location',
                    'style'=>'equals',
                    'data'=>array(
                      'value'=>$location."|".$id
                      )
                  );
                $facilities[] = I2CE_FormStorage::search('facility', false, $where_facility);
                $mapped_form_instances = I2CE_FormStorage::search($form, false, $where_location);
                foreach($mapped_form_instances as $form_id){
                    self::getFacilities($form, $form_id);
                  }
                }
                
              //look for facilities found under counties
            }
          }
        return $facilities;
      }
    
}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
