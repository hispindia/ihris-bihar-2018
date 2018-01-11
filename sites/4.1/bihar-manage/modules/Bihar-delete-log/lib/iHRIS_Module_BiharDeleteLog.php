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
* Class iHRIS_Module_BiharDeleteLog
* 
* @access public
*/


class iHRIS_Module_BiharDeleteLog extends I2CE_Module {

    /**
     * @var array Form relationship for lookups
     */
    protected static $relationship;

    /**
     * Method called when the module is enabled for the first time.
     * @return boolean
     */
    public function action_initialize() {
        $query = 'CREATE TABLE IF NOT EXISTS delete_log_person ( '
                .' `id` INT(11) AUTO_INCREMENT,'
                .' `date` DATETIME,'
                .' `person_id` VARCHAR(32),'
                .' `firstname` VARCHAR(64),'
                .' `surname` VARCHAR(64),'
                .' `job` VARCHAR(32),'
                .' `job_title` VARCHAR(128),'
                .' `birth_date` DATETIME,'
                .' `facility` VARCHAR(64),'
                .' `facility_name` VARCHAR(128),'
                .' `location_id` VARCHAR(64),'
                .' `location_name` VARCHAR(128),'
                .' PRIMARY KEY (`id`)'
                .' KEY `date` (`date`)'
                .' KEY `person_id` (`person_id`)'
                .' ) ENGINE=InnoDB DEFAULT CHARSET=utf8';
        $db = MDB2::singleton();
        if ( I2CE::pearError( $db->query($query), "Cannot create delete log table for person records:" ) ) {
            return false;
        }
        return true;
    }

    /**
     * Retrn the array of hooks available in this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'pre_mass_delete_person' => 'pre_mass_delete_person',
                );
    }

    /**
     * Post process all pages.
     * @param array $people
     * @param array $post
     */
    public function pre_mass_delete_person( $people, $post ) {
        if ( !self::$relationship instanceof I2CE_FormRelationship ) {
            self::$relationship = new I2CE_FormRelationship( 'search_people' );
        }
        $db = MDB2::singleton();
        $stmt = $db->prepare( "INSERT INTO delete_log_person ( id, `date`, person_id, firstname, surname, job, job_title, birth_date, facility, facility_name, location_id, location_name ) VALUES ( 0, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )", array( 'text', 'text', 'text', 'text', 'text', 'date', 'text', 'text', 'text', 'text' ), MDB2_PREPARE_MANIP );
        if ( I2CE::pearError( $stmt, "Unable to prepare statement to log deletions! " ) ) {
            return;
        }
        foreach( $people as $person ) {
            $full_info = self::$relationship->getFormsSatisfyingRelationship( $person );
            $data = array( $full_info['primary_form']->getNameId(),
                        $full_info['primary_form']->firstname,
                        $full_info['primary_form']->surname,
                        $full_info['last_job']->getNameId(),
                        $full_info['last_job']->title,
                        $full_info['demographic']->getField('birth_date')->getDBValue(),
                        $full_info['last_facility']->getNameId(),
                        $full_info['last_facility']->name,
                        $full_info['last_facility']->getField('location')->getDBValue(),
                        $full_info['last_facility']->getField('location')->getDisplayValue(),
                    );
            $res = $stmt->execute( $data );
            I2CE::pearError( $res, "Failed to log deletion details! " . print_r($data,true));
        }
        $stmt->free();

    }

}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
