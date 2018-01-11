<?php
/**
* © Copyright 2011 IntraHealth International, Inc.
* 
* This File is part of iHRIS 
* 
* iHRIS is free software; you can redistribute it and/or modify 
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
* @subpackage Bihar
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1.3
* @since v4.1.3
* @filesource 
*/ 
/** 
* Class iHRIS_Module_BiharPositionLock
* 
* @access public
*/


class iHRIS_Module_BiharPositionLock extends I2CE_Module {

    /**
     * @var array Cache of allowed location types for a user.
     */
    protected static $geo_cache;

    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                "validate_form_position_field_job" => "validate_position_job",
                "validate_form_person_position_field_position" => "validate_person_position",
                'post_page_view_person' => 'post_page_view_person',
                'post_page_action' => 'post_page_action',
                );
    }

    /**
     * Return the where data for a job if given
     * @param string $role
     * @param string $job
     * @return array
     */
    public static function getWhereData( $role, $job = null, $type='both' ) {
        $where_data = array( 
                'operator' => 'AND',
                'operand' => array(
                    0 => array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'enabled',
                        'style' => 'yes',
                        ),
                    1 => array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'role',
                        'style' => 'in',
                        'data' => array( 
                            'value' => array( "role|$role", "role|all" ),
                            ),
                        ),
                    )
                );
        if ( $job !== null ) {
            $where_data['operand'][] = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'job',
                        'style' => 'in',
                        'data' => array( 
                            'value' => $job,
                            ),
                        );
        }
        $geo_level = self::getGeographicLevels();
        if ( count( $geo_level ) > 0 ) {
            $geo_level[] = 'all';
            $geo_level[] = '';
            $where_data['operand'][] = array(
                    'operator' => 'FIELD_LIMIT',
                    'field' => 'geo_level',
                    'style' => 'in',
                    'data' => array( 
                        'value' => $geo_level,
                        ),
                    );
        }
        switch( $type ) {
            case "update" :
            case "new" :
                $where_data['operand'][] = array(
                        'operator' => 'FIELD_LIMIT',
                        'field' => 'lock_type',
                        'style' => 'in',
                        'data' => array(
                            'value' => array( $type, 'both' )
                            ),
                        );
                break;
        }
        return $where_data;
     }

    /**
     * Validate the job field for positions
     * @param I2CE_FormField $formfield
     */
    public function validate_position_job( $formfield ) {
        $role = I2CE_UserAccess_Mechanism::getSessionRole();
        $where_data = self::getWhereData( $role, $formfield->getDBValue() );
        $locks = I2CE_FormStorage::search('position_lock', false, $where_data );
        if ( count($locks) > 0 ) {
            $formfield->setInvalid("This designation is currently locked and cannot be assigned to new positions.");
        }

    }

    /**
     * Validate the position field for person position forms.
     * @param I2CE_FormField $formfield
     */
    public function validate_person_position( $formfield ) {
        $form_id = $formfield->getContainer()->getId();
        if ( $form_id != '0' ) {
            return;
        }
        $role = I2CE_UserAccess_Mechanism::getSessionRole();
        $pos = I2CE_FormFactory::instance()->createContainer( $formfield->getDBValue() );
        if ( !$pos instanceof iHRIS_Position ) {
            return;
        }
        $pos->populate();
        $job = $pos->getField("job")->getDBValue();
        // This is a special case from the single data entry page.  The position validation will handle
        // any incorrect values, but since it hasn't been saved yet then there is nothing to look up here.
        if ( $job == '' && $formfield->getDBValue() == 'position|1' ) {
            return;
        }
        $where_data = self::getWhereData( $role, $job );
        $locks = I2CE_FormStorage::search('position_lock', false, $where_data );
        if ( count($locks) > 0 ) {
            $formfield->setInvalid("This designation is currently locked and cannot be assigned to a person.");
        }

    }

    /**
     * Return the list of geographic levels linked to the current user.
     * @return array
     */
    public static function getGeographicLevels() {
        if ( !is_array( self::$geo_cache ) ) {
            self::$geo_cache = array();
        }
        $username = I2CE_UserAccess_Mechanism::getSessionUserName();
        if ( !array_key_exists( $username, self::$geo_cache ) ) {
            $user = I2CE_FormFactory::instance()->createContainer( "user|" . $username );
            $user->populateChild( 'access_facility' );
            $locations = array();
            if ( array_key_exists( 'access_facility', $user->children ) 
                    && is_array( $user->children['access_facility'] ) ) {
                foreach( $user->children['access_facility'] as $access ) {
                    $locations[ $access->location[0] ] = true;
                }
            }
            return self::$geo_cache[$username] = array_keys( $locations );
        }
        return self::$geo_cache[$username];
     }

    /**
     * Display the locked values for this person.
     */
    public function displayLocked( $node, $template ) {
        
        $locations = $this->getGeographicLevels();

        $role = I2CE_UserAccess_Mechanism::getSessionRole();
        $type = 'both';
        if ( $node->hasAttribute("class") ) {
            $type = $node->getAttribute("class");
            if ( !in_array( $type, array( 'both', 'update', 'new' ) ) ) {
                $type = 'both';
            }
        }
        $where_data = self::getWhereData( $role, null, $type );
        $locks = I2CE_FormStorage::search('position_lock', false, $where_data );
        if ( count($locks) > 0 ) {
            foreach( $locks as $locked ) {
                $lockObj = I2CE_FormFactory::instance()->createContainer("position_lock|$locked");
                $lockObj->populate();
                $node->appendChild( $template->createTextNode("The following designations are locked for " . $lockObj->getField("lock_type")->getDisplayValue() . ": " . $lockObj->getField("job")->getDisplayValue() ) );
            }
        }

    }

    /**
     * Post process all pages.
     * @param I2CE_Page $page
     */
    public function post_page_action( $page ) {
        switch( get_class($page) ) {
            case "iHRIS_PageViewPosition" :
                $position = $page->getForm( 'position' );
                $job = $position->getField("job")->getDBValue();
                $role = I2CE_UserAccess_Mechanism::getSessionRole();
                $where_data = self::getWhereData( $role, $job, 'update' );
                $locks = I2CE_FormStorage::search('position_lock', false, $where_data );
                if ( count($locks) > 0 ) {
                    $template = $page->getTemplate();
                    $links = $template->query("//*[@class='updateLink']");
                    for( $i = 0; $i < $links->length; $i++ ) {
                        $links->item($i)->parentNode->removeChild( $links->item($i) );
                    }
                }
                break;
        }
    }

    /**
     * Post process the view page.
     * @param iHRIS_PageView $page
     */
    public function post_page_view_person( $page ) {
        if ( !$page instanceof iHRIS_PageView ) {
            return;
        }
        if ( $this->isPageLocked( $page ) ) {
            $template = $page->getTemplate();
            $links = $template->query("//*[@class='updateLink']");
            for( $i = 0; $i < $links->length; $i++ ) {
                $links->item($i)->parentNode->removeChild( $links->item($i) );
            }
        }
    }

    /**
     * Check to see if the current view person page should be locked or not.
     * @param iHRIS_PageView $page
     * @return boolean
     */
    public function isPageLocked( $page ) {
        if ( !$page instanceof iHRIS_PageView ) {
            return;
        }
        $person = $page->getPerson();
        if ( count($person->children['person_position']) < 1 ) {
            return;
        }

        reset( $person->children['person_position'] );
        $pers_pos = current( $person->children['person_position'] );
        $position = $pers_pos->getField("position")->getMappedFormObject();
        if ( !$position instanceof iHRIS_Position ) {
            return;
        }

        $job = $position->getField("job")->getDBValue();
        $role = I2CE_UserAccess_Mechanism::getSessionRole();
        $where_data = self::getWhereData( $role, $job, 'update' );
        $locks = I2CE_FormStorage::search('position_lock', false, $where_data );


        if ( count($locks) > 0 ) {
            return true;
        }
        return false;
     }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
