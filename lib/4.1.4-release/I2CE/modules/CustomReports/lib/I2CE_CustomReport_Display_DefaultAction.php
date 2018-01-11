<?php
/**
 * @copyright © 2012 Intrahealth International, Inc.
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
*  I2CE_CustomReport_Display_DefaultAction -- the default HTML display of a report view
*  with an action to be performed in an additional cell
* @package I2CE
* @subpackage Core
* @author Luke Duncan <lduncan@intrahealth.org>
* @version 4.1.4
* @access public
*/


class I2CE_CustomReport_Display_DefaultAction extends I2CE_CustomReport_Display_Default {

     /**
     * The constuctor
     * @param I2CE_Page $page
     * @param string $view
     * @throws Excecption on error
     */
    public function __construct($page,$view) {
        parent::__construct($page,$view);
        $this->display='Default';
    }
    

    protected function doHeaderRow($contentNode) {
        parent::doHeaderRow($contentNode);
        $head = $this->template->appendFileByName( "customReports_table_head_cell.html",
                "th", "report_header", 0, $contentNode );
        if (!$head instanceof DOMNode) {
            I2CE::raiseError("Could not add head cell to table");
            return false;
        }
        $text = $this->template->createTextNode( $this->page->getActionHeader() );
        $this->template->appendNode( $text, $head );
    }

    /**
     * Process a result row.
     * @param array $row
     * @param int $row_num The current row number when processing results.  If there was a result limit, it starts the count from the beginning of the
     * result offset.  Othwerwise, it starts counting form zero.
     * @param DOMNode $contentNode. Default to null. A node to append the result onto
     */
    protected function processResultRow($row,$row_num,$contentNode=null) {
        parent::processResultRow($row, $row_num, $contentNode);

        $cellNode = $this->template->appendFileByName( "customReports_table_data_cell.html", "td", "report_row", $row_num );
        if (!$cellNode instanceof DOMNode) {
            I2CE::raiseError("Could not add data cell to table");
            return false;
        }
        $field_args = array();
        foreach( $this->page->getActionFields() as $field ) {
            $field_args[] = $row->$field;
        }
        $actionNode = $this->page->getActionNode( $field_args );
        $this->template->appendNode( $actionNode, $cellNode );
        return true;
    }
            



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
