<?php
class iHRIS_Module_BiharTrainingHistory extends I2CE_Module {

    /**
     * Return the array of methods available in this module.
     * @return array
     */
    public static function getMethods() {
        return array(
                'iHRIS_PageView->action_training_history' => array(
                    'method' => 'action_training_history',
                    'priority' => 100
                    ),
                );
    }

    /**
     * Return the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_training_history_field_training_name" => "validate_form_training_history_field_names",
                "validate_form_training_history_field_agency_name" => "validate_form_training_history_field_names",
                );
    }

    /**
     * Display the training history on the page.
     * @param iHRIS_PageView $page
     */
    public function action_training_history( $page ) {
        if ( !$page instanceof iHRIS_PageView ) {
            return;
        }
        $page->person->populateChildren( 'training_history' );
        $page->appendChildTemplate( 'training_history', null, false, 'tr' );
        return true;
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

}


?>
