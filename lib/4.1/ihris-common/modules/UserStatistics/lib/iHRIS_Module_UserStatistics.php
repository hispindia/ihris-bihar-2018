<?php
/**
* © Copyright 2012 IntraHealth International, Inc.
* 
* This File is part of iHRIS Common 
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
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version v4.1
* @since v4.1
* @filesource
*/
/**
* Class iHRIS_Module_UserStatistics
*
* @access public
*/


class iHRIS_Module_UserStatistics extends I2CE_Module {

    /**
     * @var MDB2 The database object
     */
    protected static $db;

    /**
     * @var array The list of prepared statements.
     */
    protected static $prepared;


    /**
     * @var array The cache of entry history information.
     */
    protected static $entries;

    /**
     * Return the list of hooks processed by this module.
     * @return array
     */
    public static function getHooks() {
        return array(
                'custom_reports_post_generate_all' => 'custom_reports_post_generate_all',
                'post_page_view_user' => 'post_page_view_user',
                );
    }

    /**
     * Method called when the module is enabled for the first time.
     * @param boolean
     */
    public function action_initialize() {
        I2CE::raiseError( "Initializing User Statistics Module" );
        if ( !I2CE_Util::runSQLScript('initialize_user_statistics.sql') ) {
            I2CE::raiseError( "Could not initialize user statistics cache table." );
            return false;
        }
        if ( !$this->cacheUserStatistics() ) {
            I2CE::raiseError("Failed to cache user statistics so must be done manually.");
        }
        return true;
    }

    /**
     * Upgrade this module if necessary
     * @param string $old_vers
     * @param string $new_vers
     * @return boolean
     */
    public function upgrade( $old_vers, $new_vers ) {
        if ( I2CE_Validate::checkVersion( $old_vers, '<', '4.1.12.0' ) ) {
            if ( !I2CE_Util::runSQLScript('initialize_user_statistics.sql') ) {
                I2CE::raiseError( "Could not initialize user statistics cache table." );
                return false;
            }
            if ( !$this->cacheUserStatistics() ) {
                I2CE::raiseError("Failed to cache user statistics so must be done manually.");
            }
        }
        return true;
    }

    /**
     * Cache the user statistics pseudo report.
     * @param boolean $drop_first
     * @param boolean $force
     * @return boolean
     */
    public function cacheUserStatistics( $drop_first = false, $force = false ) {
        if ( !self::setupDB() ) {
            return false;
        }
        $config = I2CE::getConfig()->modules->UserStatistics->cache;
        $config->volatile(true);

        $start = 0;
        $end = 0;
        $failed = 0;
        $config->setIfIsSet( $start, "start" );
        $config->setIfIsSet( $end, "end" );
        $config->setIfIsSet( $failed, "failed" );

        if ( $force || $start == 0 || ($end != 0 && $end >= $start) || ($failed != 0 && $failed >= $start) ) {

            $config->start = time();

            $res = self::$db->getOne("SHOW TABLES LIKE 'zebra__user_statistics'");
            if ( !$res ) {
                if ( !I2CE_Util::runSQLScript('initialize_user_statistics.sql') ) {
                    I2CE::raiseError( "Could not initialize user statistics cache table." );
                    return false;
                }
            }

            if ( $drop_first ) {
                $res = self::$db->query("truncate table zebra__user_statistics");
                if ( I2CE::pearError( $res, "Unable to truncate user statistics cache table." ) ) {
                    $cache->failed = time();
                    return false;
                }
            }
            $res = self::$db->query("INSERT INTO zebra__user_statistics SELECT entry.change_type AS `change_type`, entry.date AS `date`, (SELECT CONCAT(parent_form,'|',parent_id) FROM record WHERE id = entry.record) AS `parent_id`, entry.record AS `record`, (SELECT name FROM form WHERE id = (SELECT form FROM form_field WHERE id = entry.form_field)) AS `form`, (SELECT name FROM field WHERE id = (SELECT field FROM form_field WHERE id = entry.form_field)) AS `field`, entry.string_value, entry.integer_value, entry.date_value, entry.text_value, entry.blob_value, IF(entry.who = 0, 'I2CE Admin', (SELECT CONCAT_WS( ' ', user.firstname, user.lastname ) FROM user WHERE id = entry.who)) AS `user`, IF(entry.who = 0, 'i2ce_admin', (SELECT user.username FROM user WHERE id = entry.who)) AS `username` FROM entry WHERE date > IFNULL((SELECT MAX(date) FROM zebra__user_statistics ),'0000-00-00 00:00:00')");
            if ( I2CE::pearError( $res, "Unable to populate user statistics cache table." ) ) {
                $cache->failed = time();
                return false;
            }
            $config->end = time();
            I2CE::raiseError("Updated User Statistics report at " . date('r', $config->end ) );
        }
        return true;
    }

    /**
     * Process the hook when custom reports are generated.
     * @param boolean $force
     * @return mixed True on success, string with error message on failure.
     */
    public function custom_reports_post_generate_all( $force ) {
        if ( $this->cacheUserStatistics( false, $force ) ) {
            return true;
        } else {
            return "Failed to generate user statistics report.";
        }
    }

    /**
     * Set up the database and prepared statements if necessary.
     */
    protected static function setupDB() {
        if ( !self::$db instanceof MDB2_Driver_Common ) {
            self::$db = MDB2::singleton();
            self::$prepared = array();
            self::$prepared['user_log'] = self::$db->prepare("SELECT login,logout,activity FROM user_log WHERE user = ? ORDER BY activity DESC LIMIT ?", 
                    array( 'integer', 'integer' ), array( 'date', 'date', 'date' ) );
            if ( I2CE::pearError( self::$prepared['user_log'], "Error preparing user_log statement: " ) ) {
                return false;
            }
            self::$prepared['entry_history'] = self::$db->prepare("SELECT DISTINCT record,DATE(`date`) AS `date`,form,parent_id FROM `zebra__user_statistics` WHERE `date` >= IFNULL((SELECT DISTINCT DATE(`date`) FROM zebra__user_statistics WHERE username = ? ORDER BY `date` DESC LIMIT ?,1),(SELECT MIN(DATE(`date`)) FROM zebra__user_statistics WHERE username = ?)) AND username = ? ORDER BY `date` DESC",
                    array( 'text', 'integer', 'text', 'text' ), array( 'integer', 'date', 'text', 'text' ) );
            if ( I2CE::pearError( self::$prepared['entry_history'], "Error preparing entry_history statement: " ) ) {
                return false;
            }

        }
        return true;
    }

    /**
     * Handle any additional actions after all the child forms have
     * been loaded on the user view page.
     * @param iHRIS_PageViewUser $page
     */
    public function post_page_view_user( $page ) {
        if ( !$page instanceof iHRIS_PageViewUser ) {
            I2CE::raiseError("post_page_view_user hook called on a page that isn't the View User page.");
            return;
        }
        $user = $page->getViewUser();
        $template = $page->getTemplate();
        $defaults = I2CE::getConfig()->modules->UserStatistics->defaults;
        $login_limit = 10;
        $defaults->setIfIsSet( $login_limit, "login_limit" );


        $userAccess = I2CE::getUserAccess();
        $username = $user->getId();
        $userid = $userAccess->getUserId( $username );
        $logins = self::getLoginHistory( $userid, $login_limit );
        $template->addHeaderLink( "view_user_statistics.css" );
        $stats_node = $template->appendFileById( "view_user_statistics_login_history.html", "div", "user_details" );
        $template->setDisplayDataImmediate( "history_limit", $login_limit, $stats_node );
        if ( $logins ) {

            while( $row = $logins->fetchRow() ) {
                $node = null;
                if ( $row->logout ) {
                    $node = $template->appendFileById("view_user_statistics_logged_out.html", "tr", "user_stats_login_history" );
                    $logout = I2CE_Date::fromDB($row->logout);
                    $template->setDisplayDataImmediate( "user_stats_logout", $logout->displayDate(), $node );
                } else {
                    $node = $template->appendFileById("view_user_statistics_logged_in.html", "tr", "user_stats_login_history" );
                    $activity = I2CE_Date::fromDB($row->activity);
                    $template->setDisplayDataImmediate( "user_stats_activity", $activity->displayDate(), $node );
                }
                $login = I2CE_Date::fromDB($row->login);
                $template->setDisplayDataImmediate( "user_stats_login", $login->displayDate(), $node );
            }
        }

        $days_limit = 5;
        $defaults->setIfIsSet( $days_limit, "days_forms_limit" );

        if ( !self::setupEntryHistory( $userid, $username, $days_limit ) ) {
            I2CE::raiseError( "Unable to set up entry history for $userid ($days_limit days)" );
            return;
        }

        if ( self::$entries[$userid]['has_person'] ) {
            $person_node = $template->appendFileById( "view_user_statistics_person_history.html", "div", "user_details" );
            $template->setDisplayDataImmediate( "days_limit", $days_limit, $person_node );

            foreach( self::$entries[$userid]['dates'] as $date => $data ) {
                if ( count( $data['person'] ) > 0 ) {
                    $node = $template->appendFileById( "view_user_statistics_person_row.html", "tr", "user_stats_person_history" );
                    $dateObj = I2CE_Date::fromDB( $date );
                    $template->setDisplayDataImmediate( "user_stats_person_date", $dateObj->displayDate(), $node );
                    $template->setDisplayDataImmediate( "user_stats_person_count", count($data['person']), $node );
                }
            }
        }

        if ( self::$entries[$userid]['has_forms'] ) {
            $forms_node = $template->appendFileById( "view_user_statistics_form_history.html", "div", "user_details" );
            $template->setDisplayDataImmediate( "days_limit", $days_limit, $forms_node );

            $displays = array();
            $formConfig = I2CE::getConfig()->modules->forms->forms;
            foreach( self::$entries[$userid]['dates'] as $date => $data ) {
                $date_node = $template->appendFileById( "view_user_statistics_form_date.html", "tr", "user_stats_form_history" );
                $dateObj = I2CE_Date::fromDB( $date );
                $template->setDisplayDataImmediate( "form_date", $dateObj->displayDate(), $date_node );
                $total = 0;
                ksort($data['forms']);
                foreach( $data['forms'] as $form => $count ) {
                    if ( !array_key_exists( $form, $displays ) ) {
                        if ( !empty( $formConfig->$form->display ) ) {
                            $displays[$form] = $formConfig->$form->display;
                        } else {
                            $displays[$form] = $form;
                        }
                    }
                    $form_node = $template->appendFileById( "view_user_statistics_form_row.html", "tr", "user_stats_form_history" );
                    $template->setDisplayDataImmediate( "form_form", $displays[$form], $form_node );
                    $template->setDisplayDataImmediate( "form_count", $count, $form_node );
                    $total += $count;
                }
                $total_node = $template->appendFileById( "view_user_statistics_form_total.html", "tr", "user_stats_form_history" );
                $template->setDisplayDataImmediate( "form_date", $dateObj->displayDate(), $total_node );
                $template->setDisplayDataImmediate( "total_count", $total, $total_node );
            }
        }

    }

    /**
     * Return a database rowset for the user login history.
     * @param integer $userid The user id
     * @param integer $limit The number of records to return.
     * @return MDB2_Result
     */
    protected static function getLoginHistory( $userid, $limit=10 ) {
        if ( !self::setupDB() ) {
            return false;
        }
        $result = self::$prepared['user_log']->execute( array( $userid, $limit ) );
        if ( I2CE::pearError( $result, "Error getting login history: " ) ) {
            return false;
        }
        return $result;
    }

    /**
     * Return the entry history for this person as an array
     * @param integer $userid The user id
     * @param string $username The user name
     * @param integer $days The number of days to include
     * @return array
     */
    protected static function setupEntryHistory( $userid, $username, $days = 5 ) {
        if ( !self::setupDB() ) {
            return false;
        }
        if ( !is_array( self::$entries ) ) {
            self::$entries = array();
        }
        if ( array_key_exists( $userid, self::$entries ) ) {
            return self::$entries[$userid];
        } else {
            self::$entries[$userid] = array( 'has_person' => false, 'has_forms' => false, 'dates' => array() );
            if ( !self::setupDB() ) {
                return false;
            }
            $result = self::$prepared['entry_history']->execute( array( $username, $days-1, $username, $username ) );
            if ( I2CE::pearError( $result, "Error getting entry history: " ) ) {
                unset( self::$entries[$userid] );
                return false;
            }
            $records = array();
            $tally = array();

            $forms = array();
            $person = array();
            $parents = array();
            while ( $row = $result->fetchRow() ) {
                $tally[$row->date][] = $row->record;
                $records[$row->record] = 1;
                $forms[$row->record] = $row->form;
                if ( $row->parent_id == '0|0' ) {
                    continue;
                }
                list( $pform, $pid ) = explode( '|', $row->parent_id );
                if ( $pform == 'person' ) {
                    $person[$row->record] = $pid;
                } else {
                    if ( !array_key_exists( $pid, $parents ) ) {
                        $parents[ $pid ] = array();
                    }
                    $parents[$pid][] = $row->record;
                }
            }
            $result->free();
            if ( count($records) == 0 ) {
                return true;
            }
            $loop_check = 0;
            while ( count($parents) > 0 ) {
                if ( $loop_check++ > 50 ) {
                    I2CE::raiseError( "Too many loops for the entry history for $userid ($days days)" );
                    return false;
                }
                $parent_query = "SELECT id,parent_form,parent_id FROM record WHERE id IN ( " . implode( ',', array_keys($parents) ) . " )";
                $result = self::$db->query( $parent_query );
                if ( $result->numRows() == 0 ) {
                    $parents = array();
                } else {
                    while ( $row = $result->fetchRow() ) {
                        if ( !$row->parent_form || !$row->parent_id ) {
                            unset( $parents[$row->id] );
                            continue;
                        }
                        if ( $row->parent_form == 'person' ) {
                            foreach( $parents[$row->id] AS $record ) {
                                $person[$record] = $row->parent_id;
                            }
                            unset( $parents[$row->id] );
                        } else {
                            if ( !array_key_exists( $row->parent_id, $parents ) ) {
                                $parents[$row->parent_id] = array();
                            }
                            foreach( $parents[$row->id] AS $record ) {
                                $parents[$row->parent_id][] = $record;
                            }
                            unset( $parents[$row->id] );
                        }
                    }
                }
            }
            foreach( $tally as $date => $records ) {
                if ( !array_key_exists( $date, self::$entries[$userid]['dates'] ) ) {
                    self::$entries[$userid]['dates'][$date] = array( 'forms' => array(), 'person' => array() );
                }
                foreach( $records as $record ) {
                    if ( !array_key_exists( $record, $forms ) ) {
                        I2CE::raiseMessage( "$record not in forms array." );
                        continue;
                    }
                    if ( !array_key_exists( $forms[$record], self::$entries[$userid]['dates'][$date]['forms'] ) ) {
                        self::$entries[$userid]['dates'][$date]['forms'][$forms[$record]] = 0;
                    }
                    self::$entries[$userid]['has_forms'] = true;
                    self::$entries[$userid]['dates'][$date]['forms'][$forms[$record]]++;
                    if ( array_key_exists( $record, $person ) ) {
                        if ( !array_key_exists( $person[$record], self::$entries[$userid]['dates'][$date]['person'] ) ) {
                            self::$entries[$userid]['dates'][$date]['person'][$person[$record]] = 0;
                        }
                        self::$entries[$userid]['has_person'] = true;
                        self::$entries[$userid]['dates'][$date]['person'][$person[$record]]++;
                    }
                }
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