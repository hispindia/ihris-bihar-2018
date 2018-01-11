<?php
class iHRIS_Module_Asha extends I2CE_Module {

    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_asha'
                => 'action_asha' );
    }


    /**
     * Add the joining_job form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_asha( $page ) {
        $page->addChildForms( 'asha', 'siteContent' );
        return true;
   }

}
?>
