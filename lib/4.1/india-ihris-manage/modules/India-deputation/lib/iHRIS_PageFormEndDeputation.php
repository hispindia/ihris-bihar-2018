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
 * @subpackage India-Manage
 * @access public
 * @author Luke Duncan <lduncan@intrahealth.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since v4.0.7
 * @version v4.0.7
 */

/**
 * Page object to handle ending a deputation.
 * 
 * @package iHRIS
 * @subpackage India-Manage
 * @access public
 */
class iHRIS_PageFormEndDeputation extends iHRIS_PageFormParentPerson  {
        
    /**
     * @var I2CE_Form The position object this person has been assigned to.
     */
    private $position;
    /**
     * @var I2CE_Form The person object.
     */
    private $person;

    /**
     * Create and load data for the objects used for this form.
     * 
     * Create the list object and if this is a form submission load
     * the data from the form data.  It determines the type based on the
     * {@link $type} member variable.
     */
    protected function loadObjects() {
        $deputation = $this->factory->createContainer('deputation|0');
        if (!$deputation instanceof I2CE_Form) {
            return;
        }
        if ($this->isPost()) {
            $deputation->load($this->post);
            if (!$deputation->end_date->isValid() ) {
                $deputation->end_date = I2CE_Date::now();
            }
        } else {
            $deputation->setID($this->get('id'));
            $deputation->populate();
        }
        $pers_pos = $this->factory->createContainer( $deputation->getParent());
        if (!$pers_pos instanceof I2CE_Form) {
            return;
        }
        $pers_pos->populate();
        $this->person = $this->factory->createContainer( $pers_pos->getParent());
        if (!$this->person instanceof I2CE_Form) {
            return;
        }
        $this->person->populate();

        $this->setObject($deputation);
        $this->setObject($this->person,I2CE_PageForm::EDIT_PARENT);        

        $this->position = $this->factory->createContainer( $deputation->getField("position")->getDBValue() );
        $this->position->statusOnly();
        if ( $this->isPost() ) {
            $this->position->setStatus( "position_status|open" );
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
            $node->setAttribute('href','view?id=person|' 
                    . $this->person->getNameId()
                    . "&person_position=" ); //cheating a bit here.
        }
    }


    /**
     * Run extra validation for the fields being edited.
     */
    protected function validate() {
        parent::validate();
        if ( $this->isPost() ) {
            if ( !$this->getPrimary()->getField('end_date')->isValid() ) {
                $this->getPrimary()->getField('end_date')->setInvalid(  "Required Field" );
            }
            if ( $this->getPrimary()->end_date->before( $this->getPrimary()->start_date ) ) {
                $this->getPrimary()->getField('end_date')->setInvalid(  "The end date must be after the start date." );
            }
        }
    }

    /**
     * Update the position for this to mark it as open and then save the object.
     */ 
    public function save() {
        parent::save();
        $this->position->save( $this->user );

        $personPosition = $this->factory->createContainer( $this->getPrimary()->getParent() );
        if ($personPosition instanceof iHRIS_PersonPosition) {
            $personPosition->populateChildren('deputation');
            $still_deputed = false;
            foreach ($personPosition->children['deputation'] as  $i=>$deputed) {
                if ($deputed->end_date->isBlank()) {
                    $still_deputed = true;
                }
            }
            if ( !$still_deputed ) {
                $primary_position = $this->factory->createContainer( $personPosition->getField("position")->getDBValue() );
                $primary_position->statusOnly();
                $primary_position->setStatus( "position_status|closed" );
                $primary_position->save( $this->user );
            }
            $this->setRedirect( "view?id=person|" . $this->person->getNameId() );
        }

    }
                                
}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
