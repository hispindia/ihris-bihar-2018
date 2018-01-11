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


class iHRIS_Page_Form_Bihar_Education extends iHRIS_PageFormParentPerson {


    protected function setDisplayData(){
        parent::setDisplayData();
        $edu = $this->getPrimary();
        $is_specialty = false;
        $is_education = false;
        $is_professional = false;
        if ( $edu instanceof iHRIS_Education && $edu->getId() != '0' ) {
            $degree = $this->getPrimary()->getField('degree')->getMappedFormObject();
            if ( $this->getPrimary()->getField('specialty')->getMappedForm() == 'specialty' ) {
                $is_specialty = true;
            } elseif ( $degree && $degree instanceof iHRIS_Degree ) {
                $edu_type = $degree->getField('edu_type')->getDBValue();
                if ( $edu_type == 'edu_type|1' ) {
                    $is_professional = true;
                } elseif ( $edu_type == 'edu_type|2' ) {
                    $is_education = true;
                }
            }
        }
        if($is_specialty || ($this->request_exists("is_specialty") && $this->request("is_specialty")==1)){
            $this->setDisplayDataImmediate("is_specialty", true);
            $this->setDisplayDataImmediate("is_professional", false);
            $this->setDisplayDataImmediate("is_education", false);
            $this->setDisplayDataImmediate("is_default",false);
        }

        elseif($is_education || ($this->request_exists("is_education") && $this->request("is_education")==1)){
            $this->setDisplayDataImmediate("is_specialty", false);
            $this->setDisplayDataImmediate("is_professional", false);
            $this->setDisplayDataImmediate("is_education", true);
            $this->setDisplayDataImmediate("is_default",false);

        }

        elseif($is_professional || ($this->request_exists("is_professional")&& $this->request("is_professional")==1)){
            $this->setDisplayDataImmediate("is_specialty", false);
            $this->setDisplayDataImmediate("is_professional", true);
            $this->setDisplayDataImmediate("is_education", false);
            $this->setDisplayDataImmediate("is_default",false);

        }

        else{
            $this->setDisplayDataImmediate("is_specialty", false);
            $this->setDisplayDataImmediate("is_professional", false);
            $this->setDisplayDataImmediate("is_education", false);
            $this->setDisplayDataImmediate("is_default",true);

        }
    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
