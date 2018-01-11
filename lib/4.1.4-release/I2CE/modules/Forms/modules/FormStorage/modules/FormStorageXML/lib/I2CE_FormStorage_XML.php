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
* @package i2ce
* @subpackage forms
* @author Luke Duncan <lduncan@intrahealth.org>
* @author Carl Leitner <litlfred@ibiblio.org>
* @version v4.0.0
* @since v4.0.0
* @filesource 
*/ 
/** 
* Class I2CE_FormStorage_XML
* Storage mechanism for reading XML code lists.
* 
* @access public
*/


class I2CE_FormStorage_XML extends   I2CE_FormStorage_XML_BASE{



    /**
     *$var protected array $basequery array of the basequerys used index by the form name
     */
    protected $basequery = array();



    /**
     *$var protected array $dataquery array of the dataquerys used index by the form name
     */
    protected $dataquery = array();



    /**
     *$var protected array $searchcat array of the searchcats used index by the form name
     */
    protected $searchcat = array();


    /**
     * Tries to get the DOM Data for the SDMX-HD file
     * @param string $form 
     * @return DOMDocument
     */
    protected function getDOMData( $form ) {
        if ( array_key_exists( $form, $this->dom_data ) 
             && is_array( $this->dom_data[$form] ) ) {
            return $this->dom_data[$form];
        }
        $options = $this->getStorageOptions($form);
        if ( !$options instanceof I2CE_MagicDataNode ) {
            I2CE::raiseError( "Invalid storage options for $form" );
            return false;
        }
        $this->searchcat[$form] = false;
        if (!$options->setIfIsSet($this->search[$form], "search")) {
            I2CE::raiseError( "search is not set" );
            return false;
        }
        $this->basequery[$form] = false;
        if (!$options->setIfIsSet($this->search[$form], "basequery")) {
            I2CE::raiseError( "base query is not set" );
            return false;
        }
        $this->dataquery[$form] = false;
        if (!$options->setIfIsSet($this->search[$form], "dataquery")) {
            I2CE::raiseError( "data query is not set" );
            return false;
        }
        return  parent::getDOMData($form);
    }


    /**
     * Get the xpath query for the base node containing the data
     * @param string $form
     * @returns string
     */
    protected function getBaseQuery($form) {
        return $this->basequery[$form];
    }

    /**
     * Get the xpath query for the data nodes relative to the  the containing data
     * @param string $form
     * @returns string
     */
    protected function getDataNodesQuery($form) {        
        return $this->dataquery[$form];
    }

    /**
     * Get the search category
     * @param string $form
     * @returns string
     */
    protected function getSearchCategory($form) {
        return $this->searchcat[$form];
    }





}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
