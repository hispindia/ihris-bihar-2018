<?php
class iHRIS_Module_FamilyDetails extends I2CE_Module {


    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_family_details'
                => 'action_family_details' );
    }


    /**
     * Add the parent_information form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_family_details( $page ) {
        $page->addChildForms( 'family_details' );
        return true;
    }

    /**
     * Initialize this module the first time it is enabled.
     * Since this is a new module and replaces parent information, have it migrate
     * any data to the new forms.
     * @param string $old_vers the old version of this module
     * @param string $new_vers the new version of this module
     * @return boolean
     */

    public function post_update( $old_vers, $new_vers ) {
        if ( $old_vers == '0' ) {
            if ( !$this->migrateParentInformation() ) {
                return false;
            }
        }
        return parent::post_update( $old_vers, $new_vers );
    }

    /**
     * Migrate any data from parent_information forms to this form with the same parent.
     */
    protected function migrateParentInformation() {
return true;
        I2CE::longExecution();
        I2CE::raiseMessage( "Migrating parent information to family details." );
        $form_factory = I2CE_FormFactory::instance();
        $user = new I2CE_User( 1, false, false, false );
        $old_data = I2CE_FormStorage::listFields( "parent_information", array( "parent", 
                    "father_firstname", "father_surname",
                    "mother_firstname", "mother_surname",
                    "husband_firstname", "husband_surname" ) );
        I2CE::raiseMessage("Migrating " . count($old_data) . " records." );
        foreach( $old_data as $id => $data ) {
            $father = $form_factory->createContainer( "family_details" );
            $father->setParent( $data['parent'] );
            $father->firstname = $data['father_firstname'];
            $father->surname = $data['father_surname'];
            $father->getField("relationship")->setFromDB( "relationship|father" );
            $father->save( $user );
            $father->cleanup();
            unset( $father );
            $mother = $form_factory->createContainer( "family_details" );
            $mother->setParent( $data['parent'] );
            $mother->firstname = $data['mother_firstname'];
            $mother->surname = $data['mother_surname'];
            $mother->getField("relationship")->setFromDB( "relationship|mother" );
            $mother->save( $user );
            $mother->cleanup();
            unset( $mother );
            $husband = $form_factory->createContainer( "family_details" );
            $husband->setParent( $data['parent'] );
            $husband->firstname = $data['husband_firstname'];
            $husband->surname = $data['husband_surname'];
            $husband->getField("relationship")->setFromDB( "relationship|husband" );
            $husband->save( $user );
            $husband->cleanup();
            unset( $husband );
        }
        return true;
    }


    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_family_details_field_surname" => "validate_form_family_details_field_names",
                "validate_form_family_details_field_firstname" => "validate_form_family_details_field_names",
                "validate_form_family_details_field_middlename" => "validate_form_family_details_field_names",
                "validate_form_family_details_field_relationship" => "validate_form_family_details_relationship",
                );
    }

    /**
     * Validate the name fields for the person form.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_family_details_field_names( $formfield ) {
        $value = $formfield->getValue();
        if ( preg_match( "/[^A-Za-z ]/", $value ) > 0 ) {
            $formfield->setInvalid( "Please only use alphabet characters." );
        } 
    }

    /**
     * Validate the relationship field to make sure there are no duplicates for certain
     * values.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_family_details_relationship( $formfield ) {
        if ( I2CE_ModuleFactory::instance()->isEnabled('forms-storage') &&
                I2CE_Validate::checkMap( $formfield->getValue() ) &&
                $formfield->getContainer()->getParent() ) {
            $existing = I2CE_FormStorage::listFields( 'family_details', array( 'relationship' ), $formfield->getContainer()->getParent() );
            $found = array();
            $my_id = $formfield->getContainer()->getId();
            foreach( $existing as $det_id => $det_data ) {
                if ( $my_id == $det_id ) {
                    continue;
                }
                $found[ $det_data['relationship'] ] = true;
            }
            $value = $formfield->getDBValue();
            switch( $value ) {
                case "relationship|father" :
                case "relationship|mother" :
                case "relationship|husband" :
                case "relationship|wife" :
                    if ( array_key_exists( $value, $found ) && $found[$value] ) {
                        $formfield->setInvalid( "Family details for this relationship have already been created.  Please go back and edit that to make changes or change the relationship." );
                    }
                    break;
                case "relationship|wife" :
                    $check = "relationship|husband";
                    if ( array_key_exists( $check, $found ) && $found[$check] ) {
                        $formfield->setInvalid( "You can not add a wife relationship when a husband relationship already exists." );
                    }
                    break;
                case "relationship|husband" :
                    $check = "relationship|wife";
                    if ( array_key_exists( $check, $found ) && $found[$check] ) {
                        $formfield->setInvalid( "You can not add a wife relationship when a husband relationship already exists." );
                    }
                    break;
            }
        }
    }


}


?>
