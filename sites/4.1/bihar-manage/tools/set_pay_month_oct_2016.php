#!/usr/bin/php
<?php
/*
 * © Copyright 2007, 2008 IntraHealth International, Inc.
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
 * The page wrangler
 *
 * This page loads the main HTML template for the home page of the site.
 * @package iHRIS
 * @subpackage DemoManage
 * @access public
 * @author Sovello Hildebrand Mgani <sovellohpmgani@gmail.com>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc.
 * @version Demo-v4.a
 */


$in_file = "/home/" . trim(`whoami`) . "/Master_Sheet.csv";

$in_file_sep = "\t";
$bad_file = dirname($in_file) . DIRECTORY_SEPARATOR . substr(basename($in_file),0,-4) . ".bad." . date('M_d_Y_H_i') . ".csv";


$dir = getcwd();
chdir("../pages");
$i2ce_site_user_access_init = null;
$i2ce_site_user_database = null;
require_once( getcwd() . DIRECTORY_SEPARATOR . 'config.values.php');

$local_config = getcwd() . DIRECTORY_SEPARATOR .'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

I2CE::raiseMessage("Connecting to DB");
putenv('nocheck=1');
if (isset($i2ce_site_dsn)) {
    @I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);
} else if (isset($i2ce_site_database_user)) {
    I2CE::initialize($i2ce_site_database_user,
                     $i2ce_site_database_password,
                     $i2ce_site_database,
                     $i2ce_site_user_database,
                     $i2ce_site_module_config
        );
} else {
    die("Do not know how to configure system\n");
}

I2CE::raiseMessage("Connected to DB");

require_once($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CLI.php');

$ff = I2CE_FormFactory::instance();
$user = new I2CE_User();


$date = "2016-10-00 00:00:00";

function getMonthWorkingDays($month){
    $where = array(
        'operator'=>'FIELD_LIMIT',
        'field'=>'month',
        'style'=>'equals',
        'data'=>array(
           'value'=>$month
        )
    );
    $month = I2CE_FormStorage::listFields('working_days', array('days'), false, $where);
    $days = current($month);
    return $days['days'];
}

$date = "2016-10-00 00:00:00";

//generate pay month for all employees in the database
$personids = I2CE_FormStorage::search('person');
$parents = pay_month_isset($date);

$month_days = getMonthWorkingDays( $date );
foreach( $personids as $id ){
   if ( ! in_array("person|$id", $parents)){
       
       $salary_breakdown = I2CE_FormStorage::listFields( 'person_position_salarybreakdown',
               array( 'salary', 'salaryarrear', 'otherarrear', 'basic_pay', 'grade_pay', 'hra', 'medical_allowance', 'deputation_allowance', 
                   'tds', 'gpf', 'epf', 'professionaltax', 'otherdeductions', 'gi', 'income_tax' ),
               "person|$id", array(), array( "-setup_date" ), 1 );

	   $monthly_salary = $ff->createContainer('person_monthly_salary');
	   $monthly_salary->getField('month')->setFromDB($date);
	   $monthly_salary->getField('parent')->setFromDB("person|$id");
       if ( is_array($salary_breakdown) && count($salary_breakdown) == 1 ) {
           $breakdown = current($salary_breakdown);

           $deduct = $breakdown['tds'] + $breakdown['gpf'] + $breakdown['professionaltax'] 
               + $breakdown['otherdeductions'] + $breakdown['gi'] + $breakdown['income_tax'] 
               + $breakdown['epf'];
           $add = $breakdown['basic_pay'] + $breakdown['salaryarrear'] + $breakdown['otherarrear']
               + $breakdown['grade_pay'] + $breakdown['hra'] + $breakdown['medical_allowance'] 
               + $breakdown['deputation_allowance'];
           $daily_salary = round( $breakdown['basic_pay'] / $month_days, 2 );
           $salary = $add - $deduct;
           $monthly_salary->getField("monthly_net_salary")->setFromDB( $salary );
           $monthly_salary->getField("daily_salary")->setFromDB( $daily_salary );
       }
	   $monthly_salary->save($user);
	   $monthly_salary->cleanup();
       unset( $monthly_salary );
   }
	else{
		echo "Pay month for this employee with id person|$id has been set\n";
	}
} 

function pay_month_set($personid, $month){
     $where = array(
            'operator'=>'AND',
            'operand'=>array(
                0=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'parent',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>"person|$personid"
                    )				
                ),					
                1=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'month',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$month
                    )						
                ),
				2=>array(
					'operator'=>'FIELD_LIMIT',
					'field'=>'created',
					'style'=>'greaterthan',
					'data'=>array(
						'value'=>date('Y-01-01 00:00:00')
					)
				)
			)
     );
     $monthly_salary = I2CE_FormStorage::search('person_monthly_salary', false, $where);
     if(count($monthly_salary) > 0){
         return true;
     }
     else{
         return false;
     }
}

function pay_month_isset($month){
     $where = array(
            'operator'=>'AND',
            'operand'=>array(                					
                0=>array(
                    'operator'=>'FIELD_LIMIT',
                    'field'=>'month',
                    'style'=>'equals',
                    'data'=>array(
                        'value'=>$month
                    )						
                ),
				1=>array(
					'operator'=>'FIELD_LIMIT',
					'field'=>'created',
					'style'=>'greaterthan',
					'data'=>array(
						'value'=>date('Y-01-01 00:00:00')
					)
				)
			)
     );
    $monthly_salary = I2CE_FormStorage::listFields('person_monthly_salary', array('parent'), false, $where);

	$parents = array();
	foreach($monthly_salary as $id=>$data){
		$parents[] = $data['parent'];
	}
	return $parents;
}
