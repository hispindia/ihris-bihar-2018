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


class iHRIS_Module_Bihar_Education extends I2CE_Module {

    /**
     * Return the list of methods handled by this module.
     * @return array
     */
    public static function getMethods() {
        return array( 
                'iHRIS_PageView->action_education' => array( 
                    'method'=> 'action_education',
                    'priority' => 100 ),
                );
    }

    public static function getHooks() {
        return array(
            "post_add_child_form_education"=>"biharEducation",
            "validate_form_education"=>"validate_form_education"
        );
    }

    public function action_education( $page ) {
        if ( !$page instanceof iHRIS_PageView ) {
            return;
        }
        $page->person->populateChildren( 'education' );
        $page->appendChildTemplate( 'education', null, false, 'tr' );
        return true;
    }

    public function validate_form_education( $form ) {
        if (! $form->getField("specialty")->isValid() && !$form->getField("degree")->isValid()) {
                $form->getField('degree')->setInvalid( "Please fill the Degree field" );
                $form->getField('specialty')->setInvalid( "Please fill the Specialty field" );
        }
    }


    public function biharEducation ($args){
        if($args["form"]->getField("specialty")->issetValue()){
            $args["page"]->setDisplayDataImmediate("is_specialty", true,$args["node"]);
            $args["page"]->setDisplayDataImmediate("is_professional", false,$args["node"]);
            $args["page"]->setDisplayDataImmediate("is_education", false,$args["node"]);
            $args["page"]->setDisplayDataImmediate("is_default",false,$args["node"]);
        }

        elseif(($args["form"]->getField("degree")->issetValue())){
            $args["page"]->setDisplayDataImmediate("is_specialty", false,$args["node"]);
            $degree = $args["form"]->getField("degree")->getMappedFormObject();
            $args["page"]->setDisplayDataImmediate("is_default",false,$args["node"]);
            if($degree->getField("edu_type")->getDBValue()=="edu_type|1"){
                $args["page"]->setDisplayDataImmediate("is_professional", true,$args["node"]);
                $args["page"]->setDisplayDataImmediate("is_education", false,$args["node"]);
            }
            elseif($degree->getField("edu_type")->getDBValue()=="edu_type|2"){
                $args["page"]->setDisplayDataImmediate("is_education", true,$args["node"]);
                $args["page"]->setDisplayDataImmediate("is_professional", false,$args["node"]);
            }
        }
        else{
            $args["page"]->setDisplayDataImmediate("is_specialty", false,$args["node"]);
            $args["page"]->setDisplayDataImmediate("is_professional", false,$args["node"]);
            $args["page"]->setDisplayDataImmediate("is_education", false,$args["node"]);
            $args["page"]->setDisplayDataImmediate("is_default", true,$args["node"]);
        }

    }


}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
