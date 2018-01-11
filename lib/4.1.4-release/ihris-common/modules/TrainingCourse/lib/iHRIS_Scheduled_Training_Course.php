<?php
/**
* © Copyright 2008 IntraHealth International, Inc.
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
*/
/**
*  iHRIS_Scheduled_Training_Course
* @package I2CE
* @subpackage Core
* @author Carl Leitner <litlfred@ibiblio.org>
* @copyright Copyright &copy; 2008 IntraHealth International, Inc. 
* This file is part of I2CE. I2CE is free software; you can redistribute it and/or modify it under 
* the terms of the GNU General Public License as published by the Free Software Foundation; either 
* version 3 of the License, or (at your option) any later version. I2CE is distributed in the hope 
* that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have 
* received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.
* @version 2.1
* @access public
*/


class iHRIS_Scheduled_Training_Course extends I2CE_List{
            


    public function enrolledStudentsExpander($node,$template) {
        $node->appendChild(
            $template->createElement(
                'a',
                array('title'=>'Expand', 
                      'onclick'=>"return hideDiv('students_" . $this->getNameId() . "', this );"
                    ),
                'Show Students'));
    }

    public function enrolledStudentsList($node,$template) {
        $list_node = $template->createElement('span',array('id'=>'students_' . $this->getNameId(), 'style'=>'display:none'));
        $node->appendChild($list_node);
        $list = $this->getEnrolledStudents();
        foreach ($list as $id=>$data) {
            $studentNode = $template->appendFileByNode('enrolled_students.html','div',$list_node);
            if (!$studentNode instanceof DOMNode) {
                return false;
            }
            $template->setDisplayDataImmediate('id','id=' . $id, $studentNode);
            $template->setDisplayDataImmediate('firstname',$data['firstname'], $studentNode);
            $template->setDisplayDataImmediate('surname',$data['surname'], $studentNode);
        }
    }

    public function numberEnrolledStudents($node,$template) {
        $node->appendChild(
            $template->createTextNode(count ($this->getScheduledStudentIds()))
            );
    }

    public function getEnrolledStudents() {
        if ($this->id == 0 ) {
            return array();
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'scheduled_training_course',
            'style'=>'equals',
            'data'=>array(
                'value'=>$this->getNameId()
                )
            );
        $pstcs =  I2CE_FormStorage::listFields('person_scheduled_training_course' ,array(), true,$where);
        $list = array();
        foreach ($pstcs as $pstc) {
            if (strpos($pstc['parent'],'|')===false) {
                continue;
            }
            list($parent,$id) = explode('|',$pstc['parent'],2);
            if ($parent != 'person') {
                continue;
            }            
            $list[$pstc['parent']] = I2CE_FormStorage::lookupField('person',$id,array('surname','firstname'),false);
        }
        return $list;
    }
    


    public function getScheduledStudentIds() {
        if ($this->id == 0 ) {
            return array();
        }
        $where = array(
            'operator'=>'FIELD_LIMIT',
            'field'=>'scheduled_training_course',
            'style'=>'equals',
            'data'=>array(
                'value'=>$this->getNameId()
                )
            );
        return I2CE_FormStorage::search('person_scheduled_training_course' ,false,$where);
    }
    
    public function enrolledStudents($node,$template,$args) {
        $node->appendChild($template->createTextNode(count($this->getEnrolledStudents())));
    }




    /**
     * Return the list of scheduled course for the given course id.
     * @param integer $course_id.  Defaults to zero meaning we get all courses
     * @param boolean $flat.  defaults to false
     * @return array the keys are the id of the scheduled course, the values are the string "$start_date -- $end_date"
     */
    public static function getScheduledCourses($course_id =0, $flat = false) {
        if ($course_id > 0) {
            $flat =true;
        }
        $values = array();
        foreach (array('start_date','end_date') as $field) {
            $data = I2CE_FormField::getFormFieldIdAndType('scheduled_training_course',$field);
            if (!is_array($data)) {
                I2CE::raiseError("Could not available courses b/c could not find field $field in form scheduled_training_course");
                return array();
            }
            $values[] = $data['id'];
        }
        $query = "SELECT le_start_date.record AS id, le_start_date.date_value AS start_date, le_end_date.date_value AS end_date,  r.parent AS parent ";
        $query.= "FROM last_entry le_start_date ";
        $query.= "JOIN last_entry le_end_date ON le_start_date.record = le_end_date.record ";
        $query .= "JOIN record r ON le_start_date.record = r.id ";
        $query.= "WHERE le_start_date.form_field = ? ";
        $query.= "AND le_end_date.form_field = ? ";
        if ($course_id > 0) {
            $query .= "AND r.parent = ? ";
            $values[] = $course_id;
        }
        $query .= "ORDER BY le_start_date.date_value DESC, le_end_date.date_value ASC";
        $db = MDB2::singleton();
        $sth = $db->prepare($query,array('integer','integer'), MDB2_PREPARE_RESULT );
        if (I2CE::pearError($sth,"Could not setup statement to get available courses")) {
            return array();
        }
        $results = $sth->execute($values);
        if (I2CE::pearError($results,"Could not get available courses")) {
            return array();
        }
        $scheduled_courses = array();
        
        while ( $result =& $results->fetchRow() ) {
            $start_date = I2CE_Date::fromDB($result->start_date);
            $end_date = I2CE_Date::fromDB($result->end_date);
            if ($flat) {
                $scheduled_courses[$result->id] = $start_date->displayDate() . " - " . $end_date->displayDate();
            } else {
                $scheduled_courses[$result->parent][$result->id] = $start_date->displayDate() . " - " . $end_date->displayDate();
            }
        }
        return $scheduled_courses;
    }



}
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
