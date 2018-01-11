<?php
class iHRIS_Module_NomineeDetails extends I2CE_Module {


    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_nominee_details'
                => 'action_nominee_details' );
    }


    /**
     * Add the nominee_details form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_nominee_details( $page ) {
        $page->addChildForms( 'nominee_details', 'siteContent' );
        return true;
    }


    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_nominee_details_field_surname" => "validate_form_nominee_details_field_names",
                "validate_form_nominee_details_field_firstname" => "validate_form_nominee_details_field_names",
                "validate_form_nominee_details_field_middle_name" => "validate_form_nominee_details_field_names",
              
                );
    }

    /**
     * Validate the name fields for the person form.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_nominee_details_field_names( $formfield ) {
        $value = $formfield->getValue();
        if ( preg_match( "/[^A-Za-z ]/", $value ) > 0 ) {
            $formfield->setInvalid( "Please only use alphabet characters." );
        } 
    }


}


?>
