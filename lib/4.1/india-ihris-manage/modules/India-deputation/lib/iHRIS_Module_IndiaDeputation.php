<?php
/**
* © Copyright 2009 IntraHealth International, Inc.
* 
* This File is part of I2CE 
* 
* I2CE is free software; you can redistribute it and/or modify 
* it under the terms of the GNU General Public License as published by 
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License 
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
* @package iHRIS
* @subpackage India-Manage
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.0.7
* @since v4.0.7
* @filesource 
*/ 
/** 
* Class iHRIS_Module_IndiaDeputation
* 
* @access public
*/


class iHRIS_Module_IndiaDeputation extends I2CE_Module{

    /**
     * Return the list of hooks supported by this module.
     * @return array
     */
    public static function getHooks() {
        return array(
            'post_add_child_form_person_position' => 'action_deputation',
            );
    }


    /*
     * Add the deputation forms to the person_position section of the
     * view page.
     * @param array $args
     * @return boolean
     */
    public function action_deputation( $args ) {

        if ( $args['set_on_node'] != 'siteContent' ) {
            // Don't do the add if it's from service_history
            return;
        }
        $page = $args['page'];
        $pers_pos = $args['form'];

        if (!$page instanceof iHRIS_PageView) {
            return false;
        }
        $person = $page->getPerson();
        if (!$person instanceof iHRIS_Person) {
            return false;
        }
        $template=$page->getTemplate();

        $pers_pos->populateChildren( "deputation" );
        if ( array_key_exists( 'deputation', $pers_pos->children ) && count( $pers_pos->children['deputation'] ) > 0 ) {
            foreach( $pers_pos->children['deputation'] as $child ) {
                $child->getField( "position" )->setHref( "view_position?id=" );
                $node = $template->appendFileById( "view_deputation.html", "div", "deputation" );
                if ( !$node instanceof DOMNode ) {
                    I2CE::raiseError( "Could not find template view_deputation.html for child form deputation of person_position" );
                    return false;
                }
                $template->setForm( $child, $node );
            }
        }

        return true;
    }


    


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
