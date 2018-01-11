<?php
class iHRIS_Module_ParentInformation extends I2CE_Module {


    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_parent_information'
                => 'action_parent_information' );
    }


    /**
     * Add the parent_information form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_parent_information( $page ) {
        $page->addChildForms( 'parent_information', 'siteContent' );
        return true;
    }


    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_parent_information_field_father_surname" => "validate_form_parent_information_field_names",
                "validate_form_parent_information_field_father_firstname" => "validate_form_parent_information_field_names",
                "validate_form_parent_information_field_mother_surname" => "validate_form_parent_information_field_names",
                "validate_form_parent_information_field_mother_firstname" => "validate_form_parent_information_field_names",
                "validate_form_parent_information_field_husband_surname" => "validate_form_parent_information_field_names",
                "validate_form_parent_information_field_husband_firstname" => "validate_form_parent_information_field_names",
                );
    }

    /**
     * Validate the name fields for the person form.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_parent_information_field_names( $formfield ) {
        $value = $formfield->getValue();
        if ( preg_match( "/[^A-Za-z ]/", $value ) > 0 ) {
            $formfield->setInvalid( "Please only use alphabet characters." );
        } 
    }


}


?>
