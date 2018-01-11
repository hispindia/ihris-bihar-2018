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
 * Manage adding or editing salary details to the database.
 * 
 * @package iHRIS
 * @subpackage India-Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v2.0.0
 * @version v2.0.0
 */

/**
 * Page object to handle the adding or editing salary details to the database.
 * 
 * @package iHRIS
 * @subpackage India-Manage
 * @access public
 */
class iHRIS_PageFormDeputation extends I2CE_PageForm {
    /**
     * Return the default HTML file used by this page.
     * @return string
     */
    protected function getDefaultHTMLFile() { return "form_person_base.html"; }
        
    /**
     * Return the title for this page.
     * @return string
     */
    public function getTitle() { return "Add/Edit Deputation"; }


    /**
     * Return the form name for this page.
     * @param boolean $html Set to true if this is to be used for the html template page.
     * @return string
     * @see PersonPageForm
     */
    protected function getForm( $html=false ) {
        if ( $html && $this->id !== 0 && $this->id != "deputation|0" ) {
            return "deputation_update";
        } else {
            return "deputation"; 
        }
    }
    /**
     * @var integer The record id number of the object being edited.
     */
    protected $id;
    /**
     * @var integer The record id number of the parent person_position of the object being edited
     */
    protected $position_id;

    /**
     * @var integer The record id number of the person of the object being edited
     */
    protected $person_id;


    /**
     * @var Person person  The person of the object being edited
     */
    protected $person;

    /**
     * @var PersonPosition $personPosition The record id number of the person of the object being edited
     */
    protected $person_position;

    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link Template template}.  It also sets up the values
     * for the member variables.
     */
    /**
     * Create a new instance of this page.
     * 
     * This will call the parent constructor and then setup the base
     * template pages for the {@link Template template}.  It also sets up the values
     * for the member variables.
     */
    public function __construct($args,$request_remainder) {
        parent::__construct($args,$request_remainder);
        $this->id = 0;
        if ( $this->isPost() && $this->post_exists( 'id' ) ) {
            $this->id = $this->post('id');
        } elseif ( $this->get_exists( 'id' ) ) {
            $this->id = $this->get('id');
        }
        $this->position_id = "";
        $this->person_id = "";

        if ( $this->isPost() ) {
            $deputation = $this->factory->createContainer( $this->getForm() );
            if ( $deputation instanceof I2CE_Form ) {
                $deputation->load( $this->post );
                $this->person_position = $this->factory->createContainer( $deputation->getParent() );
            }
        } elseif ( $this->get_exists( 'parent' ) ) {
            $this->position_id = $this->get('parent');
            if (strlen($this->position_id) > 0) {
                $this->person_position = $this->factory->createContainer($this->position_id);
            }
        } 
        if ($this->person_position instanceof iHRIS_PersonPosition) {
            $this->person_position->populate();
            $this->person_id = $this->person_position->getParentID();
        } 

    }

    /**
     * Load the HTML template files for editing.
     */
    protected function loadHTMLTemplates() {
        parent::loadHTMLTemplates();
        $this->template->appendFileById( "menu_view_link.html", "li", "navBarUL", true );
        $this->template->appendFileById( "form_" . $this->getForm( true ) . ".html", "tbody", "person_form" );
    }

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
        if ( $this->factory->exists( $this->getForm() ) ) {
            $this->setObject( $this->factory->createContainer( $this->getForm().'|'. $this->id ), I2CE_PageForm::EDIT_PRIMARY );
            $this->getPrimary()->populate(); 
        }
        if ($this->person_position instanceof iHRIS_PersonPosition) {
            $this->person_position->populate();
            $this->setObject($this->person_position, I2CE_PageForm::EDIT_PARENT);
        }
        $this->person = $this->factory->createContainer( "person".'|'. $this->person_id );
        if ($this->person instanceof iHRIS_Person) {
            $this->person->populate();
        }
        if (!$this->hasPermission('task(person_can_edit_child_form_deputation)')) {
            $this->userMessage("You do not have access to edit the deputation.",'notice',false);
            return false;
        }

        parent::loadObjects();

    }

    /**
     * Perform the action of the page.
     */
    protected function action() {
        if ( $this->get_exists('delete') && $this->get('delete') == 1 ) {
            if ( !$this->getPrimary() instanceof iHRIS_Deputation || $this->getPrimary()->getId() != '0' ) {
                $parent_id = $this->getPrimary()->getParent();
                $form_id = $this->getPrimary()->getNameId();
                $child_forms = $this->getPrimary()->getChildForms();
                if ( count( $child_forms ) > 0 ) {
                    I2CE::raiseMessage("Tried to delete a child form $form_id when there are possible child forms.");
                    $message = "This form can not be deleted.";
                    I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_not_allowed" );
                } else {
                    $allowable = false;
                    I2CE::getConfig()->setIfIsSet( $allowable, "/modules/Person/deleteable_children/deputation" );
                    if ( $allowable ) { 
                        if ( $this->checkActionPermission('delete') ) { 
                            if ( $this->getPrimary()->end_date->isBlank() ) {
                                $message = "The end date hasn't been set for this deputation so it can't be deleted.";
                            } else {
                                I2CE::raiseMessage("deleting $form_id under $parent_id requested by user " . $this->getUser()->getId());
                                if ( $this->getPrimary()->delete() ) { 
                                    $message = "The requested form has been deleted.";
                                    I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_success" );
                                } else {
                                    $message = "An error occurred deleting this form.";
                                    I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_fail" );
                                }   
                            }
                        } else {
                            $message = "You do not have permission to delete this form.";
                            I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_not_permitted" );
                        }
                    } else {
                        $message = "This form can not be deleted.";
                        I2CE::getConfig()->setIfIsSet( $message, "/modules/forms/page_feedback_messages/person_child_delete_not_allowed" );
                    } 
                }
            } else {
                $message = "Invalid ID or Form passed to delete so nothing done.";
            }

            $this->userMessage($message);
            $this->setRedirect("view?id=person|" . $this->person_id );
            return true;
        }
        return parent::action();
    }
        
        
    /**
     * Set the data to be displayed for the page.
     */
    protected function setDisplayData() {
        parent::setDisplayData();
        $this->template->setDisplayData( "person_header", $this->getTitle() );
        $this->template->setDisplayData( "person_form", $this->getForm()  );
    }

    /**
     * Set the I2CE_Form object in the page template.
     * 
     * This method will pass the edit object to the page template so that it can process all the form variables.
     */
    protected function setForm() {
        parent::setForm();
        if ($this->person instanceof iHRIS_Person) {
            $this->template->setForm($this->person);
        }

    }


    /**
     * Run the validation methods for all the objects being edited.
     * 
     * If this is a form submit then run the validation methods for the default object being edited.  The default method
     * calls the {@link I2CE_Form::validate() validate} method on the {@link $edit_obj} object.
     */
    protected function validate() {
        parent::validate();
        if ( $this->isPost() ) {
            if ( $this->getPrimary()->start_date->before( $this->person_position->start_date ) ) {
                $this->getPrimary()->getField('start_date')->setFormInvalid( "The start date must be after the start date of the position deputed from." );
            }
        }
    }



    /**
     * Display the save or confirm buttons as needed.
     * 
     * If the page is a confirmation view then the save / edit button template will be displayed.  
     * Otherwise the confirm and return buttons will be shown.
     * @param boolean $save Flag to show the save button. (Defaults to false)
     * @param boolean $show_edit (defaults to true)
     * @global array
     */
    protected  function displayControls( $save = false, $show_edit = true ) {
        parent::displayControls($save,$show_edit);
        $node = $this->template->getELementById('button_return');
        if ($node instanceof DOMElement) {
            $node->setAttribute('href','view?id=person|' . $this->person_id 
                    . "&person_position=" ); //cheating a bit here.
        }
    }


    /**
     * Save the objects to the database.
     * 
     * Save the default object being edited and return to the view page.  If the action needs to be 
     * logged then the {@link log} method is also called.  Any pages overriding this default save method
     * will need to include any logging necessary.
     */
    protected function save() { 
        parent::save();
        $main_position = $this->factory->createContainer( $this->person_position->getField("position")->getDBValue() );
        $main_position->statusOnly();
        $main_position->setStatus( "position_status|deputed_from" );
        $main_position->save( $this->user );

        $position = $this->factory->createContainer( $this->getPrimary()->getField("position")->getDBValue() );
        $position->statusOnly();
        $position->setStatus( "position_status|deputed_to" );
        $position->save( $this->user );
        $this->setRedirect( "view?id=person|" . $this->person_id );
    }   

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
