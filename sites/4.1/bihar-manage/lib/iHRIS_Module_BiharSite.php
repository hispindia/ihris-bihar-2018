<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
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
* @package iHRIS
* @subpackage Bihar
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.3
* @since v4.1.3
* @filesource 
*/ 
/** 
* Class iHRIS_Module_BiharSite
* 
* @access public
*/


class iHRIS_Module_BiharSite extends I2CE_Module {
    
    /**
     * Add any customized templates to a template based on the user
     * @param DOMNode $node
     * @param I2CE_Template $template
     */
    public function addUserTemplate( $node, $template ) {
        $role = I2CE_UserAccess_Mechanism::getSessionRole();
        $type = '';
        if ( $node->hasAttribute('class') ) {
            $type = $node->getAttribute('class');
        }
        if ( $type == '' ) {
            $type == 'index';
        }
        switch( $role ) {
            case "limited_view" :
                switch( $type ) {
                    case "index" :
                        $template->appendFileByNode( 'limited_view_report.html', 'div', $node );
                        break;
                }
                break;
        }
    }

    /**
     * Return the fuzzy methods provided by this module.
     * @return array
     */
    public static function getMethods() {
        return array(
                //'iHRIS_PageView->action_person_id' => 'action_person_id',
                'iHRIS_PageView->action_person_id' => array( 'method' => 'action_person_id', 'priority' => 10 ),
                'iHRIS_PageView->action_person_language' => array( 'method' => 'action_person_language', 'priority' => 10 ),
                'iHRIS_PageView->action_family_details' => array( 'method' => 'action_family_details', 'priority' => 10 ),
                'iHRIS_PageView->action_nominee_details' => array( 'method' => 'action_nominee_details', 'priority' => 10 ),
                'iHRIS_PageView->action_demographic' => array( 'method' => 'action_demographic', 'priority' => 10 ),
                'iHRIS_PageView->action_disciplinary_action' => array( 'method' => 'action_disciplinary_action', 'priority' => 10 ),
                );
    }


    /**
     * Return the hooks set up by this module.
     * @return array
     */
    public static function getHooks() {
        return array(
            "validate_form_demographic_field_birth_date"=>"validate_form_demographic_field_birth_date",
            "validate_form_person_position_field_start_date"=>"validate_job_start_dates",
            "validate_form_person_field_birth_mark"=>"validate_form_person_field_birth_mark",
            "validate_form_service_history_field_from_date"=>"validate_job_start_dates",
            "validate_form_joining_job_field_date_of_joining"=>"validate_form_joining_job_field_date_of_joining",
            "validate_form_position" => "validate_form_position",
            "validate_form_salary_field_salary"=>"validate_form_salary_field_salary",
            "validate_form_person_position_field_startDate"=>"validate_form_person_position_field_start_date",
        );
    }


    /**
     * Validate the birth date field for demographics.
     * @param I2CE_FormField $field
     */
    public function validate_form_demographic_field_birth_date( $field ) {
        $now = I2CE_Date::now();
        $then = I2CE_Date::getDate( $now->day(), $now->month(), $now->year()- 70 );
        if ( $field->getValue()->before( $then ) ) {
            $field->setInvalid( "Birth Date must be within the last 70 years." );
        } else {
            $then = I2CE_Date::getDate( $now->day(), $now->month(), $now->year()- 15 );
            if ( $field->getValue()->after( $then ) ) {
                $field->setInvalid( "Birth Date must be at least 15 years ago." );
            }
        }
     }

    /**
     * Validate the birth date field for demographics.
     * @param I2CE_FormField $field
     */
    public function validate_form_person_field_birth_mark( $field ) {
        if ( preg_match( "/[^a-zA-z ]/", $field->getValue() ) ) {
            $field->setInvalid("Only use letters and spaces for this field.  No numbers or special characters.");
        }
    }


    /**
     * Validate the start date field for person position.
     * @param I2CE_FormField $field
     */
    public function validate_job_start_dates( $field ) {
        $now = I2CE_Date::now();
        if ( $field->getValue()->after( $now ) ) {
            $field->setInvalid( "Start Date must not be in the future." );
        }

        $pers_pos = $field->getContainer();
        if ( !$pers_pos->getParent() ) {
            // Must be single entry page so can't do anything.  This will be handled in that page.
            return;
        }
        $person = I2CE_FormFactory::instance()->createContainer( $pers_pos->getParent() );
        $person->populate();
        $person->populateChildren('joining_job');
        if ( array_key_exists( 'joining_job', $person->children ) && is_array( $person->children['joining_job'] ) && count( $person->children['joining_job'] ) > 0 ) {
            $jj = current( $person->children['joining_job'] );
            $join_date = $jj->date_of_joining;
            if ( $join_date instanceof I2CE_Date && !$join_date->isBlank() ) {
                if ( $field->getValue()->before( $join_date ) ) {
                    $field->setInvalid( "The start date must be after the Regular Appointment Date." );
                }
            }
        }

    }

    /**
     * Validate the joining date field for joining job.
     * @param I2CE_FormField $field
     */
    public function validate_form_joining_job_field_date_of_joining( $field ) {
        if ( $field->getValue()->isBlank() ) {
            return;
        }
        $now = I2CE_Date::now();
        if ( $field->getValue()->after( $now ) ) {
            $field->setInvalid( "Regular Appointment Date must not be in the future." );
        }

        $jj = $field->getContainer();
        if ( !$jj->getParent() ) {
            // Must be single entry page so can't do anything.  This will be handled in that page.
            return;
        }
        $person = I2CE_FormFactory::instance()->createContainer( $jj->getParent() );
        $person->populate();
        $person->populateChildren('demographic');
        if ( array_key_exists( 'demographic', $person->children ) && is_array( $person->children['demographic'] ) && count( $person->children['demographic'] ) > 0 ) {
            $demo = current( $person->children['demographic'] );
            $birth_date = $demo->birth_date;
            if ( $birth_date instanceof I2CE_Date && !$birth_date->isBlank() ) {
                $first = I2CE_Date::getDate( $birth_date->day(), $birth_date->month(), $birth_date->year() + 18 );
                if ( $field->getValue()->before( $first ) ) {
                    $field->setInvalid( "The Regular Appointment Date must be after the person turns 18." );
                }
            } else {
                $field->setInvalid("You must enter a date of birth in the demographic form before adding this data.");
            }
        } else {
            $field->setInvalid("You must enter a date of birth in the demographic form before adding this data.");
        }

    }


    /**
     * Validate the position form to check department allowances.
     * @param I2CE_Form $form
     */
    public function validate_form_position( $form ) {
        if ( $form->getField("job")->issetValue() ) {
            $allowed = array( "job|256", "job|364", "job|96",
                    "job|147", "job|142", "job|143", "job|146",
                    "job|138", "job|145", "job|144", "job|211",
                    "job|245", "job|139", "job|141", "job|140",
                    "job|216", "job|148" );
            if ( !in_array( $form->getField("job")->getDBValue(), $allowed ) ) {
                $form->getField("department")->setFromDB("department|73");
            }
        }
    }

    /**
     * Validate the salary field.
     * @param I2CE_FormField $field
     */
    public function validate_form_salary_field_salary ( $field ) {
        $salary_data = $field->getValue();
        if ( !is_numeric( $salary_data[1] ) ) {
            $field->setInvalid( "Only numeric characters are allowed for salary." );
        }
    }

    public function validate_form_person_position_field_startDate ( $field ) {
        if ( $field->getValue()->isBlank() ) {
            return;
        }
    }

    /**
     * Add the person_id forms to the person page.
     * @param iHRIS_PageView $obj
     */
    public function action_person_id( $obj ) {


        if( !$obj instanceof iHRIS_PageView ) {
            return;
        }
        $person = $obj->getPerson();
        $person->populateChildren( 'person_id' );
        if ( !array_key_exists('person_id', $person->children) || !is_array($person->children['person_id']) ) {

            return true;
        }
        $template = 'view_person_id.html';

        $appendNode = $obj->getTemplate()->getElementById('person_id');
        if ( !$appendNode instanceof DOMNode ) {
            I2CE::raiseError("Do not know where to add child form 'person_id'");

            return false;
        }

        foreach( $person->children['person_id'] as $child ) {
            //changes for hiding adar number on page view
            //made by ifhaam on 05-01-2018

            $role = I2CE_UserAccess_Mechanism::getSessionRole();
            if($role!='admin'){
                if($child->id_type[1]==5 ) {// type 1 is adar
                    $child->id_num = '******' . substr($child->id_num, 8);

                }else if($child->id_type[1]==8){//type 8 is pan number
                    $stars = "******************************";//size is 30
                    $val = $child->id_num;
                    $len = strlen($val);
                    if($len>0){
                        $child->id_num = substr($stars,34-$len).substr($val,$len-4);
                    }
                }
            }




            //changs for adhar card ends here
            I2CE_ModuleFactory::callHooks( 'pre_add_child_form_person_id',
                    array( 'form' => $child,
                        'page' => $obj, 'set_on_node' => null,
                        'append_node' => $appendNode ) );
            $id_type = $child->id_type;
            if ( $id_type[1] == 1 || $id_type[1] == 7 ) {
                $obj->getTemplate()->setForm( $child, "individual" );
            } else {
                $node = $obj->getTemplate()->appendFileByNode( $template, 'tr', $appendNode );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError("Could not find template $template for child form 'person_id' of person.");
                    return false;
                }
                $obj->getTemplate()->setForm( $child, $node );
            }
            I2CE_ModuleFactory::callHooks( 'post_add_child_form_person_id',
                    array( 'form' => $child,
                        'page' => $obj, 'set_on_node' => null,
                        'append_node' => $appendNode ) );

        }
        return true;
    }

    /**
     * Add the person_language forms to the person page.
     * @param iHRIS_PageView $obj
     */
    public function action_person_language( $obj ) {
        if( !$obj instanceof iHRIS_PageView ) {
            return;
        }
        $person = $obj->getPerson();
        $person->populateChildren( 'person_language' );
        if ( !array_key_exists('person_language', $person->children) || !is_array($person->children['person_language']) ) {
            return true;
        }
        $obj->appendChildTemplate( 'person_language', null, false, 'tr' );

        return true;
    }

    /**
     * Add the family_details forms to the person page.
     * @param iHRIS_PageView $obj
     */
    public function action_family_details( $obj ) {
        if( !$obj instanceof iHRIS_PageView ) {
            return;
        }
        $person = $obj->getPerson();
        $person->populateChildren( 'family_details' );
        if ( !array_key_exists('family_details', $person->children) || !is_array($person->children['family_details']) ) {
            return true;
        }
        $appendNode = $obj->getTemplate()->getElementById('family_details');
        if ( !$appendNode instanceof DOMNode ) {
            I2CE::raiseError("Do not know where to add child form 'family_details'");
            return false;
        }
        foreach( $person->children['family_details'] as $child ) {
            I2CE_ModuleFactory::callHooks( 'pre_add_child_form_family_details',
                    array( 'form' => $child,
                        'page' => $obj, 'set_on_node' => null,
                        'append_node' => $appendNode ) );
            $relationship = $child->relationship;
            switch ( $relationship[1] ) {
                case "husband" :
                case "wife" :
                    $template = 'view_spouse_details.html';
                    break;
                default :
                    $template = 'view_family_details.html';
            }
            $node = $obj->getTemplate()->appendFileByNode( $template, 'tr', $appendNode );
            if ( !$node instanceof DOMNode ) {
                I2CE::raiseError("Could not find template $template for child form 'family_details' of person.");
                return false;
            }
            $obj->getTemplate()->setForm( $child, $node );
            I2CE_ModuleFactory::callHooks( 'post_add_child_form_family_details',
                    array( 'form' => $child,
                        'page' => $obj, 'set_on_node' => null,
                        'append_node' => $appendNode ) );
        }
        return true;
    }

    /**
     * Add the nominee_details forms to the person page.
     * @param iHRIS_PageView $obj
     */
    public function action_nominee_details( $obj ) {
        if( !$obj instanceof iHRIS_PageView ) {
            return;
        }
        $person = $obj->getPerson();
        $person->populateChildren( 'nominee_details' );
        if ( !array_key_exists('nominee_details', $person->children) || !is_array($person->children['nominee_details']) ) {
            return true;
        }
        $obj->appendChildTemplate( 'nominee_details', 'siteContent', false, 'tr' );

        return true;
    }

    /**
     * Add the demographic forms to the person page.
     * @param iHRIS_PageView $obj
     */
    public function action_demographic( $obj ) {
        if( !$obj instanceof iHRIS_PageView ) {
            return;
        }
        $person = $obj->getPerson();
        $person->populateChildren( 'demographic' );
        if ( !array_key_exists('demographic', $person->children) || !is_array($person->children['demographic']) ) {
            return true;
        }
        $obj->appendChildTemplate( 'demographic', 'siteContent', false, 'tr' );

        return true;
    }

    /**
     * Add the disciplinary_action forms to the person page.
     * @param iHRIS_PageView $obj
     */
    public function action_disciplinary_action( $obj ) {
        if( !$obj instanceof iHRIS_PageView ) {
            return;
        }
        $person = $obj->getPerson();
        $template = $obj->getTemplate();

        $discActions = iHRIS_Module_DisciplinaryAction::getDisciplinaryActions( $person, true );
        foreach( $discActions as $discAction ) {
            $discAction->populate();
            $actionNode = $template->appendFileById( "view_disciplinary_action_table.html", "tr", 'disciplinary_action' );
            if ( !$actionNode instanceof DOMNode ) {
                continue;
            }
            $template->setForm($discAction, $actionNode);
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
