<?php
class iHRIS_Module_BiharDeath extends I2CE_Module {

    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_death'
                => 'action_death' );
    }


    /**
     * Add the death form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_death( $page ) {
        $page->addChildForms( 'death', 'siteContent' );
        return true;
   }

}
?>
