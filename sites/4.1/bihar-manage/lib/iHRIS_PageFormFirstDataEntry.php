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
 * Manage adding or editing forms associated with a person to the database.
 * 
 * @package iHRIS
 * @subpackage BiharManage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.0
 * @version v4.1.0
 */
class iHRIS_PageFormFirstDataEntry extends I2CE_PageForm {

    /**
     * The list of created forms for this page.
     * @var array
     */
    protected $form_list;
    /**
     * The allowed types for this page.
     * @var array
     */
    protected $allowed_types;
    /**
     * The type of this data entry page.
     * @var string
     */
    protected $page_type;

    /**
     * Create a new instance of this page.
     *
     * @param array $args
     * @param array $request_remainder
     * @param array $get
     * @param array $post
     */
    public function __construct( $args, $request_remainder, $get=null, $post=null ) {
        parent::__construct( $args, $request_remainder, $get, $post );
        $this->page_type = null;
        $this->allowed_types = array( 
                'default' => array( 
                    "person" => self::EDIT_PRIMARY,
                    "father_info" => array( "form" => "family_details", "blank"=>true, "type" => self::EDIT_CHILD, "defaults" => array( "relationship" => "relationship|father" ) ),
                    "mother_info" => array( "form" => "family_details", "blank"=>true, "type" => self::EDIT_CHILD, "defaults" => array( "relationship" => "relationship|mother" ) ),
                    "husband_info" => array( "form" => "family_details", "blank"=>true,"type" => self::EDIT_CHILD ),
                    //"family_details" => array( "repeat" => 3, "type" => self::EDIT_CHILD ),
                    "demographic" => self::EDIT_CHILD,
                    "person_contact_work" => array('type'=>self::EDIT_CHILD,'blank'=>true),
                    "person_id" => array( "type" => self::EDIT_CHILD, "blank" => true, ),
                    "nominee_details" =>  array('type'=>self::EDIT_CHILD,'blank'=>true),
                    "position" => self::EDIT_SECONDARY,
                    "person_position" => self::EDIT_CHILD,
                    "salary" => self::EDIT_CHILD,
                    "joining_job" => array('type'=>self::EDIT_CHILD),
                    "deputation_position" => array( "form" => "position", "blank" => true, "type" => self::EDIT_SECONDARY ),
                    "deputation" => array( "type" => self::EDIT_CHILD, "blank" => true ),
                    "service_history" => array( "type" => self::EDIT_CHILD, "repeat" => 5 , "defaults"=>array("govt_order_date"=>false), ),
                    "education" => array( "type" => self::EDIT_CHILD, "repeat" => 3 ),
                    //"transfer_history" => array( "type" => self::EDIT_CHILD, "blank" => true ),
                    "training_history" => self::EDIT_CHILD,
                    // This MUST be after person_id or bad things can happen. It is a special case and is handled differently.
                    "gpf_cpf_no" => array( "form" => "person_id", "type" => self::EDIT_CHILD, "blank" => true, "defaults" => array( "id_type" => "id_type|1" ) ),
                    ),
                'asha' => array(
                    "person" => self::EDIT_PRIMARY,
                    "demographic" => self::EDIT_CHILD,
                    "person_contact_personal" => self::EDIT_CHILD,
                    "position" => self::EDIT_SECONDARY,
                    "person_position" => self::EDIT_CHILD,
                    "salary" => self::EDIT_CHILD,
                    "asha" => self::EDIT_CHILD,
                    ),
                );
        if ( $this->get_exists( 'type' ) ) {
            $this->page_type = $this->get('type');
        }
        if ( !array_key_exists( $this->page_type, $this->allowed_types ) ) {
            $this->page_type = 'default';
        }

    }

    /**
     * Perform the actions for the page.
     * @returns boolean;
     */
    protected function action() {
        $this->template->addHeaderLink( "single_entry.css" );
        $this->template->setAttribute( "class", "active", "menuDataEntry".$this->page_type, 
                "a[@href='data_entry?type=" . $this->page_type . "']" );
        $head_node = $this->template->addFile( "data_entry_" .$this->page_type . "_base.html" );


        $this->form_list = array();
        foreach( $this->allowed_types[$this->page_type] as $form => $edit ) {
            if ( is_array( $edit ) ) {
                if ( array_key_exists('type', $edit ) ) {
                    $edit_type = $edit['type'];
                } else {
                    continue;
                }
                $details = array();
                if ( array_key_exists( 'form', $edit ) ) {
                    $details['id'] = $form;
                    $form = $edit['form'];
                }
                if ( array_key_exists( 'defaults', $edit ) ) {
                    $details['defaults'] = $edit['defaults'];
                }
                $repeat = 1;
                $count = null;
                if ( array_key_exists( 'repeat', $edit ) ) {
                    $repeat = $edit['repeat'] ;
                    $count = 1;
                }
                while( $repeat-- ) {
                    $this->addForm( $form, $edit_type, $count, $details );
                    if ( $count !== null ) {
                        $count++;
                    }
                }
            } else {
                $this->addForm( $form, $edit );
            }
        }

        return parent::action();
    }

    /**
     * Check to see if given a particular instance of a form in the post if it's filled out 
     * or not.
     * @param string $form
     * @param integer $repeat_count
     * @param array $defaults array with keys field names and values default (db) field values
     * @return boolean
     */
    protected function isFilledOut( $form, $repeat_count, $defaults ) {
         if ( !$this->isPost() ) {
            return false;
         }
         if (!is_array($defaults)) {
             $defaults = array();
         }


         if ( $form == 'person_id' && array_key_exists('id_type', $defaults) && $defaults['id_type'] == 'id_type|1' ) {
             if ( I2CE_Validate::checkString( $this->post['gpf_cpf_no']['fields']['id_num'] ) ) {
                 return true;
             }
         }

         //$post_idx = ($repeat_count === null ? 0 : $repeat_count - 1 );
        if ( !array_key_exists( $form, $this->post['forms'] ) 
                || !array_key_exists( 0, $this->post['forms'][$form] )
            ) {
            return false;
        }  
        //|| !array_key_exists( $post_idx, $this->post['forms'][$form][0] )) {
        $keys = array_keys($this->post['forms'][$form][0]);
        sort($keys);
        if (count($keys) == 0) {
            return;
        }
        if ($repeat_count === null) {
            $post_idx = 0;
        } else {
            //$repeat_count--;
            if (!array_key_exists($repeat_count,$keys)) {
                return false;
            }
            $post_idx = $keys[$repeat_count];
        }

        if ( !array_key_exists( 'fields', $this->post['forms'][$form][0][$post_idx] ) ) {
            return false;
        }
        $obj = I2CE_FormFactory::instance()->createContainer( $form );

        foreach( $this->post['forms'][$form][0][$post_idx]['fields'] as $field => $value ) {
            // If the field is filled out and isn't the same as the default value then
            // return true for this form
            if ( is_array( $value ) ) {
                foreach( $value as $val_key => $val_val ) {
                    if ( I2CE_Validate::checkString($val_val) ) {
                        return true;
                    }
                }
            } else {
                if (array_key_exists($field,$defaults)) {
                    $def_value = $defaults[$field];
                    if ($def_value === false) {
                        continue;
                    }

                } else {
                    $def_value = $obj->getField($field)->getDBValue();
                }
                if ( I2CE_Validate::checkString($value) && $value !=  $def_value) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Display the save or confirm buttons and handle the special case for gpf/cpf no.
     * @param boolean $save
     * @param boolean $show_edit
     */
    protected function displayControls( $save=false, $show_edit=true ) {
        if ( $save ) {
            $this->template->setAttribute("style", "display: none;", "gpf_cpf_no:fields:id_num" );
            $this->template->setAttribute("style", "", "gpf_cpf_no_display" );
        }
        parent::displayControls( $save, $show_edit );
    }

    /**
     * Check to see if this form can be blank to avoid required fields errors.
     * @param string $form_type
     * @return boolean
     */
    protected function allowBlank( $form_type ) {
        if ( is_array( $this->allowed_types[ $this->page_type ][ $form_type ] ) 
                && array_key_exists( 'blank', $this->allowed_types[$this->page_type][$form_type] )
                && $this->allowed_types[$this->page_type][$form_type]['blank'] ) {
            return true;
        } 
        return false;
    }

    protected $skip_blank_forms = array();

    /**
     * Add a form to the given page as a certain type.
     * @param string $form
     * @param integer $type
     * @param integer $repeat_count
     * @param array $details Additional details for this form
     */
    protected function addForm( $form, $type = self::EDIT_PRIMARY, $repeat_count = null, $details = array() ) {
        if (is_array($details) && array_key_exists('id',$details)) {
            $form_type = $details['id'];
        } else {
            $form_type = $form;
        }
        if ( $this->isPost() ) {
            if (array_key_exists('defaults',$details) && is_array($details['defaults'])) {
                $defaults = $details['defaults'];
            } else {
                $defaults = array();
            }
            if (array_key_exists($form,$this->form_list)) {
                if ( is_array($this->form_list[$form])) {
                    $repeat = count($this->form_list[$form]);
                    if (array_key_exists($form,$this->skip_blank_forms)) {
                        $repeat += $this->skip_blank_forms[$form];
                    }
                } else {
                    $repeat = 1; //WHY?
                }
            } else {
                $repeat = 0;
            }
     
            if ( ( $repeat_count !== null || $this->allowBlank( $form_type ) ) 
                    && !$this->isFilledOut( $form, $repeat, $defaults)) {
                if (!array_key_exists($form,$this->skip_blank_forms)) {
                    $this->skip_blank_forms[$form] = 0;
                }
                $this->skip_blank_forms[$form]++;
                if (!array_key_exists($form,$this->form_list)) {
                    $this->form_list[$form] = array();
                }
                // If it's a post, and this is a repeated form, then allow there to be blank 
                // forms and don't include any completely blank entries.
                return;
            }

        }
        if ( array_key_exists( $form, $this->form_list ) && !is_array( $this->form_list[$form] ) ) {
            $this->form_list[$form] = array( $this->form_list[$form] );
        }
        $factory = I2CE_FormFactory::instance();
        $obj = $factory->createContainer( $form );
        if ( array_key_exists( $form, $this->form_list ) && is_array( $this->form_list[$form] ) ) {
            $this->form_list[$form][] = $obj;
        } else {
            $this->form_list[$form] = $obj;
        }
        if ( array_key_exists( 'defaults', $details ) ) {
            foreach( $details['defaults'] as $field => $value ) {
                if ( $obj->hasField($field) ) {
                    $obj->getField($field)->setFromDB( $value );
                }
            }
        }
        if ( $this->isPost() ) {
            //$post_idx = ($repeat_count === null ? 0 : $repeat_count - 1 );
            if ( $form_type == "gpf_cpf_no" ) {
                if (array_key_exists('defaults', $details)) {
                    foreach ($details['defaults'] as $field=>$dbval) {
                        if ($dbval === false) {
                            continue;
                        }
                        if ( ! ($fieldObj = $obj->getField($field)) instanceof I2CE_FormField) {
                            continue;
                        }
                        $fieldObj->setFromDB($dbval);
                    }
                }
                $obj->setFromPost( $this->post['gpf_cpf_no'], false );
                if ( I2CE_Validate::checkString($obj->id_num) ) {
                    $this->template->setDisplayDataImmediate('gpf_cpf_no:fields:id_num', $obj->id_num);
                    $obj->validate();
                    if ( $obj->hasInvalid() ) {
                        $this->template->setDisplayDataImmediate('gpf_cpf_no_error', 'This GPF/CPF No. is already in use.');
                        $this->template->setAttribute('class', 'error', 'gpf_cpf_no:fields:id_num');
                    }
                }
                $this->setObject( $obj, $type, 'gpf_cpf_no' );
                return;
            }
            if ( is_array( $this->form_list[$form] ) ) {
                $post_idx = count( $this->form_list[$form] ) - 1;
                if (array_key_exists($form,$this->skip_blank_forms)) {
                    $post_idx += $this->skip_blank_forms[$form];
                }
            } else {
                $post_idx = 0;
            }
            
            if ( !array_key_exists( $form, $this->post['forms'] ) ) {
                return;
            }
            $keys = array_keys($this->post['forms'][$form][0]);
            sort($keys);
            if (count($keys) == 0) {
                return;
            }
            if (!array_key_exists($post_idx,$keys)) {
                return;
            }
            $post_idx = $keys[$post_idx];


            //$obj->load( $this->post, false );
            if (array_key_exists('defaults', $details)) {
                foreach ($details['defaults'] as $field=>$dbval) {
                    if ($dbval === false) {
                        continue;
                    }
                    if ( ! ($fieldObj = $obj->getField($field)) instanceof I2CE_FormField) {
                        continue;
                    }
                    $fieldObj->setFromDB($dbval);
                }
            }
            $obj->setFromPost( $this->post['forms'][$form][0][$post_idx], false );
            if ( $form == "person_position" ) {
               $obj->getField("position")->setFromDB( "position|1" );
            }
            if ( $form == "deputation" ) {
               $obj->getField("position")->setFromDB( "position|1" );
            }
            if ( $form == "salary" ) {
                if ( array_key_exists( 'person_position', $this->form_list ) ) {
                    $obj->getField("start_date")->setFromDB( $this->form_list['person_position']->getField('start_date')->getDBValue() );
                    //I2CE::raiseMessage("setting start date on salary");
                } else {
                    $obj->getField("start_date")->setFromDB( I2CE_Date::now()->dbFormat() );
                }
            }
            if ( $form == "person" ) {
                $surname_ignore = $obj->getField('surname_ignore');
                $ignore_path = array( 'forms', 'person', $obj->getId(), 'ignore', 'surname' );
                if ( $surname_ignore instanceof I2CE_FormField && $this->post_exists( $ignore_path ) ) {
                    $surname_ignore->setFromPost( $this->post($ignore_path) );
                }
            }
        }
        if ( array_key_exists( 'id', $details ) ) {
            $template_id = $details['id'];
        } else {
            $template_id = $form;
        }
        if ( $repeat_count !== null ) {
            $template_id .= $repeat_count;
        }
        $form_node = $this->template->appendFileById( "data_entry_" .$this->page_type . "_" 
                . $template_id . ".html", "tbody", "data_entry_forms" );
        //I2CE::raiseError("adding $form $template_id");
        $this->setObject( $obj, $type, $template_id );
    }


    /**
     * Run extra validation for the family details forms to make sure nothing
     * would be invalid if saved one at a time.
     */
    protected function validate() {
        if ( $this->checked_validation ) {
            parent::validate();
            return;
        }

        if ( array_key_exists( 'family_details', $this->form_list ) 
                && is_array( $this->form_list['family_details'] ) ) {
            $used_relationship = array();
            foreach( $this->form_list['family_details'] as $family ) {
                $relationship = $family->getField("relationship");
                $relationship_value = $relationship->getDBValue();
                switch( $relationship_value ) {
                    case "relationship|father" :
                    case "relationship|mother" :
                    case "relationship|husband" :
                    case "relationship|wife" :
                        if ( array_key_exists( $relationship_value, $used_relationship )
                                && $used_relationship[ $relationship_value ] ) {
                            $relationship->setInvalid( "Family details for this relationship have already been set.  Please only use a relationship once." );
                        }
                    case "relationship|wife" :
                        $check = "relationship|husband";
                        if ( array_key_exists( $check, $used_relationship ) &&
                                $used_relationship[$check] ) {
                            $relationship->setInvalid( "You can not add a wife relationship and a husband relationship." );
                        }
                        break;
                    case "relationship|husband" :
                        $check = "relationship|wife";
                        if ( array_key_exists( $check, $used_relationship ) &&
                                $used_relationship[$check] ) {
                            $relationship->setInvalid( "You can not add a husband relationship and a wife relationship." );
                        }
                        break;
                }
                $used_relationship[ $relationship_value ] = true;
            }
        }

        if ( array_key_exists( 'joining_job', $this->form_list ) ) {
            $jj = $this->form_list['joining_job'];
            if ( is_array( $jj ) ) {
                $jj = current($jj);
            }
            $join_date = $jj->date_of_joining;
            if ( $join_date instanceof I2CE_Date && !$join_date->isBlank() ) {
                if ( array_key_exists( 'demographic', $this->form_list ) ) {
                    $demo = $this->form_list['demographic'];
                    if ( is_array( $demo ) ) {
                        $demo = current($demo);
                    }
                    if ( $demo->birth_date instanceof I2CE_Date && !$demo->birth_date->isBlank() ) {
                        $first = I2CE_Date::getDate( $demo->birth_date->day(), $demo->birth_date->month(), $demo->birth_date->year() + 18 );
                        if ( $jj->date_of_joining->before( $first ) ) {
                            $jj->getField("date_of_joining")->setInvalid( "The Regular Appointment Date must be after the person turns 18." );
                        }
                    }
                }
                if ( array_key_exists( 'person_position', $this->form_list ) ) {
                    $pers_pos = $this->form_list['person_position'];
                    if ( is_array( $pers_pos ) ) {
                        $pers_pos = current( $pers_pos );
                    }
                    if ( $pers_pos->start_date->before( $join_date ) ) {
                        $pers_pos->getField("start_date")->setInvalid( "The start date must be after the Regular Appointment Date." );
                    }
                }
                if ( array_key_exists( 'service_history', $this->form_list ) ) {
                    if ( is_array( $this->form_list['service_history'] ) ) {
                        $history = $this->form_list['service_history'];
                    } else {
                        $history = array( $this->form_list['service_history'] );
                    }
                    foreach( $history as $obj ) {
                        if ( !$obj->from_date->isBlank() && $obj->from_date->before( $join_date ) ) {
                            $obj->getField("from_date")->setInvalid( "The from date must be after the Regular Appointment Date." );
                        }
                    }
                }
            }
        }
        

        parent::validate();
    }


    /**
     * Save the objects to the database.
     *
     * Save the default object being edited and return to the view person page.
     */
    protected function save () {
        if ( !$this->getPrimary()->save( $this->user ) ) {
            I2CE::raiseError("Couldn't save primary object " . get_class($this->getPrimary() ) );
            $this->userMessage("Failed to save form " . $this->getPrimary()->form() );
            return false;
        }
        $this->setRedirect( "view?id=" . $this->getPrimary()->getNameId() );
        foreach( $this->objects[ self::EDIT_SECONDARY] as $obj ) { 
            if (!$obj->save( $this->user )) {
                I2CE::raiseError("Could not save secondary object" . get_class($obj));
                $this->userMessage("Failed to save form " . $obj->form() );
                return false;
            }   
        } 
        if ( array_key_exists( "person_position", $this->form_list ) ) {
            if ( array_key_exists( "position", $this->form_list ) ) {
                if ( is_array( $this->form_list['position'] ) ) {
                    $set_position = $this->form_list['position'][0];
                } else {
                    $set_position = $this->form_list['position'];
                }
                $this->form_list['person_position']->getField( 'position' )->setFromDB( $set_position->getNameId() );
                $set_position->setStatus("position_status|closed");
                if ( !$set_position->save($this->user) ) {
                    I2CE::raiseError("Could not update status for position " . $set_position->getNameId() );
                    $this->userMessage("Unable to assign the new position, please manually edit this user.", 'notice', true );
                    return false;
                }
            } else {
                $this->userMessage("Unable to assign the new position, please manually edit this user.", 'notice', true );
                return false;
            }
            $this->form_list['person_position']->validate();
            if ( $this->form_list['person_position']->hasInvalid() ) {
                $this->userMessage("Unable to assign the new position, please manually edit this user.", 'notice', true );
                return false;
            }
        }
        if ( array_key_exists( "deputation", $this->form_list ) && !is_array($this->form_list['deputation']) ) {
            if ( array_key_exists( "position", $this->form_list ) ) {
                if ( is_array( $this->form_list['position'] ) ) {
                    $set_position = $this->form_list['position'][1];
                } else {
                    $this->userMessage("Unable to assign the new position for deputation, please manually edit this user.", 'notice', true );
                    return false;
                }
                $this->form_list['deputation']->getField( 'position' )->setFromDB( $set_position->getNameId() );
                $set_position->setStatus("position_status|deputed_to");
                $this->form_list['position'][0]->setStatus("position_status|deputed_from");
                if ( !$this->form_list['position'][0]->save( $this->user ) || !$set_position->save( $this->user ) ) {
                    $this->userMessage("Unable to assign the new position for deputation, please manually edit this user.", 'notice', true );
                    return false;
                }
            } else {
                $this->userMessage("Unable to assign the new position for deputation, please manually edit this user.", 'notice', true );
                return false;
            }
            $this->form_list['deputation']->validate();
            if ( $this->form_list['deputation']->hasInvalid() ) {
                $this->userMessage("Unable to assign the new position for deputation, please manually edit this user.", 'notice', true );
                return false;
            }
        }
         foreach( $this->objects[ self::EDIT_CHILD] as $obj ) { 
            if ( $obj->getId() == '0' ) { 
                if ( $obj->form() == "salary" ) {
                    if ( array_key_exists( "person_position", $this->form_list )
                            && $this->form_list['person_position']->getId != "0" ) {
                        $obj->setParent( $this->form_list['person_position'] );
                    } else {
                        I2CE::raiseError("Unable to set parent of salary for " . $this->getPrimary()->getNameId );
                        continue;
                    }
                } elseif ( $obj->form() == "deputation" ) {
                    if ( array_key_exists( "person_position", $this->form_list )
                            && $this->form_list['person_position']->getId != "0" ) {
                        $obj->setParent( $this->form_list['person_position'] );
                    } else {
                        I2CE::raiseError("Unable to set parent of deputation for " . $this->getPrimary()->getNameId );
                        continue;
                    }
                } else {
                    $obj->setParent( $this->getPrimary() );
                }
            }   
            if (!$obj->save( $this->user )) {
                I2CE::raiseError("Could not save child object" . get_class($obj));
                $this->userMessage("Failed to save form " . $obj->form() );
                return false;
            }   
        }   
        $message = "This record has been saved.";
        I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_save" );
        $this->userMessage($message);
        return true;
    }

}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
