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
 * View a person's record.
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * The page class for displaying the a person's record.
 * @package iHRIS
 * @subpackage Qualify
 * @access public
 */
class iHRIS_PageViewBihar extends iHRIS_PageView { 

    /**
     * Set to true if this person is ASHA or Mamta
     * @var boolean
     */
    protected $is_asha;

    /**
     * Initializes any data for the page
     * @returns boolean
     */
    protected function initPage() {
        if ( !parent::initPage() ) {
            I2CE::raiseMessage("failed to init");
            return false;
        }
        $this->person->populateLast( array( 'person_position' => 'start_date', 'person_position_salarybreakdown' => 'setup_date', 'person_monthly_salary' => 'month' ) );
        if ( !$this->hasChildForm( "person_position" ) ) {
            return true;
        }
        foreach( $this->person->children['person_position'] as $pers_pos ) {
            if ( $pers_pos->end_date->isBlank() ) {
                $pos_field = $pers_pos->getField("position");
                if ( !$pos_field instanceof I2CE_FormField_MAP ) {
                    I2CE::raiseError( "Couldn't get position field from person position form." );
                    return false;
                }
                $position = $pos_field->getMappedFormObject();
                if ( !$position instanceof iHRIS_Position ) {
                    I2CE::raiseError( "Couldn't get position object from person position form." );
                    return false;
                }
                $job_field = $position->getField("job");
                if ( !$job_field instanceof I2CE_FormField_MAP ) {
                    I2CE::raiseError( "Couldn't get job field from position form." );
                    return false;
                }
                $job_value = $job_field->getDBValue();
                $this->is_asha = ( $job_value == 'job|11' || $job_value == 'job|26' );
                return true;
            }
        }
        return true;
    }

    public function action_person_position() {
        $person = $this->getPerson();
        // Clear out anthing that may have been already populated like the service history display.
        unset( $person->children['person_position'] );
        if ( parent::action_person_position() ) {
            if ( ($pers_pos = current($person->children['person_position']) ) instanceof iHRIS_PersonPosition ) {
                if ( ( $pos_field = $pers_pos->getField("position") ) instanceof I2CE_FormField_MAP ) {
                    if ( ( $lockMod = I2CE_ModuleFactory::instance()->getClass( 'Bihar-position-lock' ) ) instanceof I2CE_Module ) {
                        if ( $lockMod->isPageLocked( $this ) ) {
                            $pos_field->setHref('');
                        }
                    }
                    if ( ( $position = $pos_field->getMappedFormObject() ) instanceof iHRIS_Position ) {
                        $this->template->setForm( $position, 'person_position' );
                    }
                    $pos_link = true;
                    if ( $this->user->getRole() == 'search_user' ) {
                        $pos_link = false;
                    }
                    if ( ( $pp_node = $this->template->getElementById("person_position") ) instanceof DOMNode ) {
                        if ( !$this->hasPermission('task(can_edit_database_list_position)', $pp_node ) ) {
                            $pos_link = false;
                        }
                    } elseif ( $this->user->getRole() == 'facility_manager' ) {
                        $pos_link = false;
                    }
                    if ( !$pos_link ) {
                        $pos_field->setHref('');
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the list of forms to display on this page.
     * @return array
     */
    protected function getUsedChildForms() {
        $combined = array( 'person_id', 'demographic', 
                //'parent_information', 
                'family_details',
                'person_photo_passport', 'person_resume',
                'person_contact_work' ,
                //'person_record_status' 
                );
        if ( $this->is_asha ) {
            return array_merge( $combined, array( 'person_position', 'person_facility_transfer','person_monthly_salary','person_position_salarybreakdwon', 'salary', 'deputation', 
                        'benefit', 'joining_job', 'service_history', 'transfer_history',
                        'asha' ) );
        } else {
            return array_merge( $combined, array( 'dependent', 'nextofkin',
                                                  'person_position','person_facility_transfer','person_monthly_salary','person_position_salarybreakdown', 'salary', 'deputation', 'benefit', 
                        'nominee_details',
                        'joining_job', 'service_history', 'vrs', 'death',
                        //'transfer_history',
                        'confirmation', 'qualification', 'person_language',
                        'person_scheduled_training_course',
                        'disciplinary_action',
                        'accidents',
                        'education', 'training_history',
                        'notes', 'service_matter',
                        'person_archive_scan' ) );
        }
    }


    /**
     * Load the  template (HTML or XML) files to the template object.
     *  
     * 
     */  
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        if ( $this->is_asha ) {
            $this->template->appendFileById( "custom_view_asha.html", "div", "siteContent" );
        } else {
            $this->template->appendFileById( "custom_view_default.html", "div", "siteContent" );
        }
    }

    /**
     * Return the child form data for this page.
     * @return array
     */
    protected function getChildFormData() {
        $children = parent::getChildFormData();
        $allowed_children = $this->getUsedChildForms();
        foreach( $children as $key => $data ) {
            if ( !in_array( $key, $allowed_children ) ) {
                unset( $children[$key] );
            }
        }
        return $children;
    }

    /**
     * Perform the main actions of the page.
     */
    /*
    protected function action() {
        I2CE::raiseMessage("here1");
        if (!$this->hasPermission("task(person_can_view)")) {
            $this->userMessage("You do not have permission to view this person",true);
            return false;
        }
        
        I2CE_ModuleFactory::callHooks( "pre_page_view_person", $this );
        //$child_forms = $this->person->getChildForms();
        $child_forms = $this->getUsedChildForms();
        foreach($child_forms as $form) {
            if (!$this->hasPermission("task(person_can_view_child_forms|person_can_view_child_form_{$form})")) {
                continue;
            }
            $method = 'action_' . $form;
            if ($this->_hasMethod($method)) {
                if (!$this->$method()) {
                    I2CE::raiseError("Could not do action for $form");
                }
            }
        }
        I2CE_ModuleFactory::callHooks( "post_page_view_person", $this );
        I2CE::raiseMessage("here2");
        return true;
    }
    */
    

  }



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
