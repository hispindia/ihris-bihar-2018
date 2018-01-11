<?php
/**
 * © Copyright 2014 IntraHealth International, Inc.
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
 * @package I2CE
 * @author Sovello Hildebrand <sovellohpmgani@gmail.com>
 * @version v4.2.0
 * @since v4.2.0
 * @filesource
 */

/**
 * Class I2CE_Dashboard
 *
 * @access public
 */
class Bihar_PageForm_Monthly_Salary extends I2CE_PageForm
{


    protected function getSalaryBreakdown($personid)
    {
        $where = array(
            'operator' => 'FIELD_LIMIT',
            'field' => 'parent',
            'style' => 'equals',
            'data' => array(
                'value' => $personid
            )
        );


//        $salarybreakdown = I2CE_FormStorage::listFields(
//            'person_position_salarybreakdown',
//            array('gpf', 'gi', 'tds', 'income_tax', 'grade_pay', 'hra', 'medical_allowance',
//                'deputation_allowance','dearness_allowance', 'basic_pay'), false, $where, array('-setup_date'), 1);
//
//        if (count($salarybreakdown) == 1) {
//            $data = current($salarybreakdown);
//            $deduct = $data['gpf'] + $data['gi'] + $data['tds'] + $data['income_tax'];
//                $add = $data['grade_pay'] + $data['hra'] + $data['medical_allowance'] + $data['deputation_allowance']+$data['dearness_allowance'];
//            $net_value = $add - $deduct;
//            return array('basic_pay' => $data['basic_pay'], 'extras' => $net_value);
//        } else {
//            return false;
//        }

        $salarybreakdown = I2CE_FormStorage::listFields(
            'person_position_salarybreakdown',
            array('salary', 'salaryarrear', 'otherarrear', 'basic_pay', 'grade_pay', 'hra', 'medical_allowance', 'deputation_allowance',
                'tds', 'gpf', 'epf', 'professionaltax', 'otherdeductions', 'gi', 'income_tax'), false, $where, array('-setup_date'), 1);
as
        if (count($salarybreakdown) == 1) {
            $data = current($salarybreakdown);
            $basic_pay = $data['basic_pay'];
            $deduct = $data['tds'] + $data['gpf'] + $data['professionaltax'] + $data['otherdeductions'] + $data['gi'] + $data['income_tax'] + $data['epf'];
            $add = $data['salaryarrear'] + $data['otherarrear'] + $data['grade_pay'] + $data['hra'] + $data['medical_allowance'] + $data['deputation_allowance'];
            $net_value = $add - $deduct;
            return array('basic_pay' => $basic_pay, 'extras' => $net_value);
        } else {
            return false;
        }
    }

            protected function getMonthWorkingDays($month)
    {
        $where = array(
            'operator' => 'FIELD_LIMIT',
            'field' => 'month',
            'style' => 'equals',
            'data' => array(
                'value' => $month
            )
        );
        $themonth = I2CE_FormStorage::listFields('working_days', array('days'), false, $where);
        $days = current($themonth);
        return $days['days'];
    }

    /**
     * Save the objects to the database.
     *
     * Save the default object being edited b
     * @global array
     */
    protected function save()
    {
        $ff = I2CE_FormFactory::instance();
        $monthlySalaryObj = $ff->createContainer($this->request('id'));
        $monthlySalaryObj->populate();
        if ($this->request_exists('action')) {
            $breakdown = $this->getSalaryBreakdown($this->request('parent'));
            if ($breakdown !== false) {
                $gross_salary = $breakdown['basic_pay'];
                $month_working_days = $this->getMonthWorkingDays($this->request('month'));
                if (!$month_working_days) {
                    $monthDate = I2CE_Date::fromDB($this->request('month'), I2CE_Date::YEAR_MONTH);
                    $month_working_days = cal_days_in_month(CAL_GREGORIAN, $monthDate->month(), $monthDate->year());
                }
                $daily_salary = round($gross_salary / $month_working_days, 2);

                if ($this->request('action') == 'update_work_days') {
                    $total_days = $monthlySalaryObj->getField('leave_days')->getDBValue() + $this->request('working_days');
                    if ($total_days <= $month_working_days) {
                        $monthlySalaryObj->getField('working_days')->setFromDB($this->request('working_days'));
                        $this->template->loadRootText('<body><span style="color: green">' . $this->request('working_days') . '</span></body>');
                    } else {
                        $this->template->loadRootText('<body><span style="color: red">' . $monthSalaryObj->getField('working_days')->getDBValue() . '</span></body>');
                    }
                }
                else if ($this->request('action') == 'update_leave_days') {
                        $total_days = $this->request('leave_days');
                        if ($total_days <= 23) {
                            $monthlySalaryObj->getField('leave_days')->setFromDB($this->request('leave_days'));
                            $this->template->loadRootText('<body><span style="color: green">' . $this->request('working_days') . '</span></body>');
                        } else {
                            $this->template->loadRootText('<body><span style="color: red">24</span></body>');
                        }
                    }

                if ($this->request('action') == 'update_other_allowance') {
                    $total_other_allowance = $this->request('other_allowance');
                    $monthlySalaryObj->getField('other_allowance')->setFromDB($this->request('other_allowance'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('other_allowance') . '</span></body>');
                }

                if ($this->request('action') == 'update_transport_allowance') {
                    $total_transport_allowance = $this->request('transport_allowance');
                    $monthlySalaryObj->getField('transport_allowance')->setFromDB($this->request('transport_allowance'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('transport_allowance') . '</span></body>');
                }

                if ($this->request('action') == 'update_house_rent_deduction') {
                    $total_house_rent_deduction = $this->request('house_rent_deduction');
                    $monthlySalaryObj->getField('house_rent_deduction')->setFromDB($this->request('house_rent_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('house_rent_deduction') . '</span></body>');
                }
                if ($this->request('action') == 'update_interest_advance_deduction') {
                    $total_interest_advance_deduction = $this->request('interest_advance_deduction');
                    $monthlySalaryObj->getField('interest_advance_deduction')->setFromDB($this->request('interest_advance_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('interest_advance_deduction') . '</span></body>');
                }
                if ($this->request('action') == 'update_gpf_advance_deduction') {
                    $total_gpf_advance_deduction = $this->request('gpf_advance_deduction');
                    $monthlySalaryObj->getField('gpf_advance_deduction')->setFromDB($this->request('gpf_advance_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('gpf_advance_deduction') . '</span></body>');
                }
                if ($this->request('action') == 'update_house_building_advance_deduction') {
                    $total_house_building_advance_deduction = $this->request('house_building_advance_deduction');
                    $monthlySalaryObj->getField('house_building_advance_deduction')->setFromDB($this->request('house_building_advance_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('house_building_advance_deduction') . '</span></body>');
                }
                if ($this->request('action') == 'update_service_tax_deduction') {
                    $total_service_tax_deduction = $this->request('service_tax_deduction');
                    $monthlySalaryObj->getField('service_tax_deduction')->setFromDB($this->request('service_tax_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('service_tax_deduction') . '</span></body>');
                }
                if ($this->request('action') == 'update_computer_advance_deduction') {
                    $total_computer_advance_deduction = $this->request('computer_advance_deduction');
                    $monthlySalaryObj->getField('computer_advance_deduction')->setFromDB($this->request('computer_advance_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('computer_advance_deduction') . '</span></body>');
                }

                if ($this->request('action') == 'update_festival_advance_deduction') {
                    $total_festival_advance_deduction = $this->request('festival_advance_deduction');
                    $monthlySalaryObj->getField('festival_advance_deduction')->setFromDB($this->request('festival_advance_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('festival_advance_deduction') . '</span></body>');
                }
                if ($this->request('action') == 'update_misc_recoveries_deduction') {
                    $total_misc_recoveries_deduction = $this->request('misc_recoveries_deduction');
                    $monthlySalaryObj->getField('misc_recoveries_deduction')->setFromDB($this->request('misc_recoveries_deduction'));
                    $this->template->loadRootText('<body><span style="color: green">' . $this->request('misc_recoveries_deduction') . '</span></body>');
                }

               /*code for values frm db*/

                $transportAllowance =$monthlySalaryObj->getField('transport_allowance')->getDBValue();
                $otherAllowance=$monthlySalaryObj->getField('other_allowance')->getDBValue();

                $houseRentDeduction=$monthlySalaryObj->getField('house_rent_deduction')->getDBValue();
                $interestAdvanceDeduction=$monthlySalaryObj->getField('interest_advance_deduction')->getDBValue();
                $gpfAdvanceDeduction=$monthlySalaryObj->getField('gpf_advance_deduction')->getDBValue();
                $houseBuildingAdvanceDeduction=$monthlySalaryObj->getField('house_building_advance_deduction')->getDBValue();
                $serviceTaxDeduction=$monthlySalaryObj->getField('service_tax_deduction')->getDBValue();
                $computerAdvanceDeduction=$monthlySalaryObj->getField('computer_advance_deduction')->getDBValue();
                $festivalDeduction =$monthlySalaryObj->getField('festival_advance_deduction')->getDBValue();
                $miscDeduction =$monthlySalaryObj->getField('misc_recoveries_deduction')->getDBValue();
                $leaveDays =$monthlySalaryObj->getField('leave_days')->getDBValue();
                $oneDaySalary =$monthlySalaryObj->getField('daily_salary')->getDBValue();

                $add_allowance= $transportAllowance+$otherAllowance ;
                $sub_deduction= $houseRentDeduction+$interestAdvanceDeduction+$gpfAdvanceDeduction+$houseBuildingAdvanceDeduction+$serviceTaxDeduction+$computerAdvanceDeduction+$festivalDeduction+$miscDeduction;

//                $add_allowance = $total_transport_allowance + $total_other_allowance;
    //                $sub_deduction = $total_house_rent_deduction + $total_interest_advance_deduction + $total_gpf_advance_deduction + $total_festival_advance_deduction + $total_computer_advance_deduction + $total_service_tax_deduction + $total_house_building_advance_deduction;


                $monthlySalaryObj->populate();
//               $net_salary = $gross_salary - (round($leaveDays * $oneDaySalary, 2)) + $breakdown['extras'] + $add_allowance - $sub_deduction;
                $net_salary = $gross_salary - (round($leaveDays * $oneDaySalary, 2)) + $breakdown['extras'] + $add_allowance - $sub_deduction;

                $monthlySalaryObj->getField('monthly_net_salary')->setFromDB($net_salary);
                $monthlySalaryObj->getField('daily_salary')->setFromDB($daily_salary);
            }
            $monthlySalaryObj->save($this->user);
        } else {
            return false;
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
