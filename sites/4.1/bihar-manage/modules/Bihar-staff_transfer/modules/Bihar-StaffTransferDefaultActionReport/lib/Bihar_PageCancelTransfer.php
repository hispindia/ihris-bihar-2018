<?php
/*
 * © Copyright 2012 IntraHealth International, Inc.
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
 */
/**
 * Edit participants action for a training
 * @package iHRIS
 * @subpackage Manage
 * @access public
 * @author Sovello Hildebrand Mgani <smgani@intrahealth.org>
 * @copyright Copyright &copy; 2012 IntraHealth International, Inc. 
 * @since v4.1.0
 * @version v4.1.0
 */

/**
 * The action page class for editing particpants for a training
 * @package iHRIS
 * @subpackage Bihar Manage
 * @access public
 */
class Bihar_PageCancelTransfer extends I2CE_Page { 

    /**
     * Perform the main actions of the page.
     * @return boolean
     */
    protected function action() {
        if ( !parent::action() ) {
            I2CE::raiseError("Base action failed");
            return false;
        }/*
        if (!$this->hasPermission("task(person_can_edit_child_form_person_scheduled_training_course)")) {
            $no_edit = "You do not have permission to edit students for this course instance.";
            I2CE::getConfig()->setIfIsSet($no_edit,"/modules/training-course/translatable-strings/no_edit_students");
            $this->userMessage($no_edit);
            I2CE::raiseError("Cannot edit");
            return false;
        }*/
        
        if ($this->request_exists('transfer_log_id')) {
          $transferObject = I2CE_FormFactory::instance()->createContainer( $this->request('transfer_log_id') );
          $transferObject->populate();
          $transferObject->transfer_status = array('transfer_status','canceled');
            //$this->template->addFile('action_students_error.html');
            //return false;
          return $transferObject->save( $this->user );
        }
    }
  
  protected function getTransferLogId($personId){
      $where = array(
        'operator'=>'FIELD_LIMIT',
        'field'=>'parent',
        'style'=>'equals',
        'data'=>array(
          'value'=>$personId
          )
        );
    }

}



# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
