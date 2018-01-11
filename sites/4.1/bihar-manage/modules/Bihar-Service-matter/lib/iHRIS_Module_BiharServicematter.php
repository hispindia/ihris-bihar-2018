<?php
class iHRIS_Module_BiharServicematter extends I2CE_Module {

    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_service_matter'
                => 'action_service_matter' );
    }


    /**
     * Add the vrs form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_service_matter( $page ) {
        if ( !$page instanceof iHRIS_PageView ) {
            return;
        }
        $person = $page->getPerson();
        $person->populateChildren( 'service_matter' );
        if ( !array_key_exists( 'service_matter', $person->children ) || !is_array( $person->children['service_matter'] ) ) {
            return true;
        }
        $page->appendChildTemplate( 'service_matter', 'service_matter', false, 'tr' );
        return true;
   }

}
?>
