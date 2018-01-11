<?php
class iHRIS_Module_TrainingHistory extends I2CE_Module {


    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_training_history'
                => 'action_training_history' );
    }


    /**
     * Add the parent_information form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_training_history( $page ) {
        $page->addChildForms( 'training_history', 'siteContent' );
        return true;
    }


    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_training_history_field_training_name" => "validate_form_training_history_field_names",
                "validate_form_training_history_field_agency_name" => "validate_form_training_history_field_names",
                "validate_form_training_history" => "validate_form_training_history",
                );
    }

    /**
     * Validate the name fields for the training_history form.
     * @param I2CE_FormField $formfield
     */
    public function validate_form_training_history_field_names( $formfield ) {
        $value = $formfield->getValue();
        if ( preg_match( "/[^A-Za-z ]/", $value ) > 0 ) {
            $formfield->setInvalid( "Please only use alphabet characters." );
        } 
    }

    /**
     * Validate the dates in the training_history form.
     * @param I2CE_Form $form
     */
    public function validate_form_training_history( $form ) {
        if ( $form->from_date->isValid() && $form->to_date->isValid() ) {
            if ( $form->from_date->compare( $form->to_date ) < 0 ) {
                $form->getField('to_date')->setInvalid( "The to date must be after the from date." );
            }
        }
    }


}


?>
