<?php
/**
 * @copyright © 2013 Intrahealth International, Inc.
 * This File is part of I2CE
 *
 * I2CE is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
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
*/
/**
*  iHRIS_PageBiharSearch
* @package iHRIS
* @subpackage Common
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.6
* @access public
*/


class iHRIS_PageBiharSearch extends iHRIS_PageSearch {

    /**
     * Set the active menu
     */
    protected function setActiveMenu() {
        $this->template->setAttribute( "class", "active", "menuCustomReports", "a[@href='CustomReports/view/reportViews']" );
        $this->template->setAttribute( "class", "active", "menuSearch", "a[@href='search']" );

        $report_config = I2CE::getConfig()->traverse( "/modules/CustomReports", true );

        $role = $this->getUser()->getRole();
        if ( $role == 'search_user' || $role == 'limited_view' ) {
            $recent = $this->template->query("//li/a[@href='recent']");
            if ( $recent->length > 0 ) {
                $recent->item(0)->parentNode->removeChild( $recent->item(0) );
            }
        }
        foreach( $report_config->search_reports as $report => $report_info ) {
            if ( ($role == 'search_user'  || $role == 'limited_view' ) && $report != 'search_people' ) {
                continue;
            }
            $node = $this->template->appendFileById( "menu_search_report.html", "li", "menu_search_reports" );
            $this->template->setDisplayDataImmediate( "menu_search_link", array( "href" => "search/" . $report, "no_results" => 1 ), $node );
            $this->template->setDisplayDataImmediate( "menu_search_name", $report_info->name, $node );
            $this->template->setAttribute( "name", "menu_" . $report, null, "//a[@name='menu_search_link']", $node );
        }
        $this->template->setDisplayData( "limit_description", false );

        if (count($this->request_remainder) > 0) {
            reset($this->request_remainder);
            $view = current($this->request_remainder);
            $this->template->setAttribute( "class", "active", "menuSearch", "ul/li/a[@name='menu_" . $view . "']" );
        }

    }

    /**
     * Perform any actions for the page
     * 
     * @returns boolean.  true on sucess
     */
    public function actionMenu() {
        $this->template->addFile('search.html');
        $role = $this->getUser()->getRole();
        if ( ($role != 'search_user' && $role != 'limited_view') && I2CE_ModuleFactory::instance()->isEnabled( 'ihris-common-RecentForm' ) ) {
            $this->template->appendFileById( "search_recent.html", "div", "search_reports" );
        }
        $report_config = I2CE::getConfig()->traverse( "/modules/CustomReports", true );
        foreach( $report_config->search_reports as $report => $report_info ) {
            if ( ($role == 'search_user' || $role == 'limited_view') && $report != 'search_people' ) {
                continue;
            }
            $node = $this->template->appendFileById( "search_report.html", "div", "search_reports" );
            $this->template->setAttribute( "id", $report, "search", null, $node );
            $this->template->setAttribute( "id", $report . "_replace", "search_replace", null, $node );
            $this->template->setDisplayDataImmediate( "search_link", array( "href" => "search/" . $report, "no_results" => 1 ), $node );
            $this->template->setDisplayDataImmediate( "search_name", $report_info->name, $node );
            $this->template->setDisplayDataImmediate( "search_desc", $report_info->description, $node );
        }

        if ( $this->request_exists('id') ) {
            $id = $this->request('id');
            if ( strpos( $id, "person|" ) !== false ) {
                $id = str_replace( "person|", "", $id );
            }
            $this->setDisplayDataImmediate('id', $id);
            $this->template->setAttribute("style", "border-color: red;", "person_id");
            if ( !is_numeric($id) ) {
                $this->template->addFile("search_quick_error_invalid.html");
            } elseif ( I2CE_FormFactory::instance()->hasRecord( "person", $id ) ) {
                $this->redirect("view?id=person|$id");
            } else {
                $this->template->addFile("search_quick_error_notfound.html");
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
