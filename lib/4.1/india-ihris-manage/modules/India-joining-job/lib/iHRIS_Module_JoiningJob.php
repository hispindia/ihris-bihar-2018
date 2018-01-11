<?php
class iHRIS_Module_JoiningJob extends I2CE_Module {

    /**
     * Set up the fuzzy methods for this module.
     * @return array
     */
	static function getMethods() {
        return array( 'iHRIS_PageView->action_joining_job'
                => 'action_joining_job' );
    }


    /**
     * Add the joining_job form to the view person page.
     * @param I2CE_Page $page
     * @return boolean
     */
    function action_joining_job( $page ) {
        $page->addChildForms( 'joining_job', 'siteContent' );
        return true;
   }

}
?>
