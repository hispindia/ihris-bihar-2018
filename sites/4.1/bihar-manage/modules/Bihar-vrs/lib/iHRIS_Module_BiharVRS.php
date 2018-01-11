<?php
class iHRIS_Module_BiharVRS extends I2CE_Module {

    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_vrs'
                => 'action_vrs' );
    }


    /**
     * Add the vrs form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_vrs( $page ) {
        $page->addChildForms( 'vrs', 'siteContent' );
        return true;
   }

}
?>
