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


if (($handle = fopen($in_file, "r")) === FALSE) {
    die("Could not open $in_file\n");
}
$in_file_sep = false;
foreach (array("\t",",") as $sep) {
    if ( ($headers = fgetcsv($handle, 1000, $sep)) === FALSE|| count($headers) < 3) {
        continue;
    }
    $in_file_sep = $sep;
}
if (!$in_file_sep) {
    die("Could not get headers\n");
}
foreach ($headers as &$head) {
    $head  = trim($head);        
}
unset($head);


//Sr No Person ID District


$expected_headers = array(
    'sr_no'=>'Sr No',
    'person_id'=>'Persion ID',
    'district'=>'District'
    );

$headers = loadHeadersFromCSV($handle);
mapHeaders($headers);
$personIds = $ff->getRecords('person');
$personId = array();

foreach( $personIds as $record => $id ){
    $foundId[] = 'person|'. $id;
  }

$row = 0;
while (($data = fgetcsv($handle)) !== FALSE) {
      $row++;
      echo "Now processing row # $row\n";
      $ff = I2CE_FormFactory::instance();
      $datalower = strtolower($data[1]);
      if( !in_array($datalower, $foundId) ){
        echo "This record is not found in the database\n";
        continue;
      }else{
        $formObj = $ff->createContainer( $datalower );
        $formObj->populate();
        $child_forms = $formObj->getChildForms();
        $formObj->populateChildren($child_forms);
        foreach ($formObj->getChildren() as $child_form_name=>$child_form_data) {
            foreach ($child_form_data as $child_form_id=>$child_form) {
                if (!$child_form instanceof I2CE_Form) {
                    continue;
                }
                echo "\tDeleting: " . $child_form->getFormID() . "\n";
                if ($child_form_name == 'person_position' && ($posObj= $ff->createContainer($child_form->getField('position')->getValue())) instanceof iHRIS_Position) {
                    echo "\t\tDeleting linked position with ID=" . $posObj->getFormID() . "\n";
                    $posObj->delete(false, true);
                }
                $child_form->delete(false, true);
            }
        }  
      //echo "Parent form is = ".$data[1]."\n";
        $formObj->delete(false, true);
      }
}

/*********************************************
*
*      Helper functions
*
*********************************************/

function loadHeadersFromCSV($fp) {
    $in_file_sep = false;
    foreach (array("\t",",") as $sep) {
        $headers = fgetcsv($fp, 4000, $sep);
        if ( $headers === FALSE|| count($headers) < 3) {
            fseek($fp,0);
            continue;
        }
        $in_file_sep = $sep;
    }
    if (!$in_file_sep) {
        die("Could not get headers\n");
    }
    foreach ($headers as &$header) {
        $header = trim($header);
    }
    unset($header);
    return $headers;
}

function mapHeaders($headers) {
    global $expected_headers;
    global $header_map;
    $header_map = array();
    foreach ($expected_headers as $expected_header_ref => $expected_header) {
        if (($header_col = array_search($expected_header,$headers)) === false) {
            I2CE::raiseError("Could not find $expected_header in the following headers:\n\t" . implode(" ", $expected_headers). "\nFull list of found headers is:\n\t" . implode(" ", $headers));
            die();
        }
        $header_map[$expected_header_ref] = $header_col;
    }
}
