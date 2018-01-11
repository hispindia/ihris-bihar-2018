<?php
/**
* © Copyright 2010-2011 IntraHealth International, Inc.
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
*
* @package ihris-manage-india
* @author Robin Tonk <robin.hisp@gmail.com>
* @version v3.2
* @since v3.2
* @filesource
*/
/**
* Class iHRIS_Module_ServiceHistory
*
* @access public
*/


class iHRIS_Module_Bihar_ServiceHistory extends I2CE_Module {

    /** 
     * Return the fuzzy methods implemented by this module.
     * @return array
     */


    public static function getMethods() {
        return array(
            'iHRIS_PageView->action_service_history' => 'action_service_history' 
            );
    }

    /**
     * Return the hooks for this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                //'post_add_child_form_person_position' => 'post_add_child_form_person_position',
                "validate_form_service_history_field_to_date"=>"validate_to_date",
                "validate_form_service_history_field_from_date"=>"validate_from_date"
                );
    }

    public function validate_from_date( $form_field ) {
        $parent = $form_field->getContainer()->getParent();
        if ( $parent == '' ) {
            return;
        }
        $where = array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'start_date',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=> $form_field->getDBValue()
                    )
                );
        $results = I2CE_FormStorage::search('person_position',$parent, $where);
        if (count($results) > 0 ){
            $form_field->setInvalid("Field should be unique");
        }
    }

    public function validate_to_date( $form_field ) {
        $parent = $form_field->getContainer()->getParent();
        if ( $parent == '' ) {
            return;
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'end_date',
            'style'=>'equals',
            'data'=>array(
                'value'=> $form_field->getDBValue()
            )
        );
        $results = I2CE_FormStorage::search('person_position',$parent, $where);
        if (count($results) > 0 ){
            $form_field->setInvalid("Field should be unique");
        }

    }

    /**
     * Add the service history section including from the person_position history.
     */
    public function action_service_history($obj) {
        //$obj->addChildForms('person_position',NULL, 'view_person_position_history.html','tr','service_history');
        if ( $obj->person instanceof iHRIS_Person ) {
            $obj->person->populateChildren('person_position');
            $obj->person->populateChildren('service_history', array( 'service_history' => '-from_date' ) );
        }
        if ( is_array( $obj->person->children['person_position'] ) ) {
            $appendNode = $obj->getTemplate()->getElementById('service_history');
            foreach( $obj->person->children['person_position'] as $ppChild ) {
                if ( $ppChild->end_date->isBlank() ) {
                    continue;
                }
                $node = $obj->getTemplate()->appendFileByNode('view_person_position_history.html', 'tr', $appendNode );
                $obj->getTemplate()->setForm($ppChild, $node );
            }
        }
        $obj->appendChildTemplate( 'service_history', null, false, 'tr' );

        return true;
    }
    
    /**
     * Check to see if the person position should be added to service history or not and remove it if not.
     * @param array $data The form and node data.
     */
    public function post_add_child_form_person_position( $data ) {
        if ( $data['append_node'] instanceof DOMNode && $data['append_node']->getAttribute('id') == "service_history" ) {
            if ( $data['form']->end_date->isBlank() ) {
                $data['append_node']->removeChild( $data['node'] );
            }
        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
