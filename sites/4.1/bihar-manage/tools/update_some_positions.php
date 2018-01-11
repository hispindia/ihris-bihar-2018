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
 * @author Carl Leitner <litlfred@ibiblio.org>
 * @copyright Copyright &copy; 2007, 2008 IntraHealth International, Inc. 
 * @since Demo-v2.a
 * @version Demo-v2.a
 */

$dir = getcwd();
chdir("../pages");
$i2ce_site_user_access_init = null;
$wd = getcwd();
require_once( $wd . DIRECTORY_SEPARATOR . 'config.values.php');

$local_config = $wd . DIRECTORY_SEPARATOR .'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');
@I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);

require_once $i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'CLI.php';


$ff = I2CE_FormFactory::instance();
$user = new I2CE_User();


/*
 * delete person_position|944462, person_position|1445560
 * update_end_date person_position|948064 and reson_for_change
 * no position details person_position|1422206, position|124065 (title='Medical Officer(job|44)', facility|6028='Block Bidupur PHC', position_type|1='Regular Staff'

 */
 

foreach( array('person_position|1444834') as $id ){
  $posObj = $ff->createContainer($id);
  $posObj->populate();
  $positionID = $posObj->getField('position')->getDBValue();
  $positionObj = $ff->createContainer($positionID);
  $positionObj->populate();
  $positionObj->status = array('position_status','closed');
  $positionObj->save($user);
  $posObj->delete();
}

/*$pPosObj = $ff->createContainer('person_position|948064');
$pPosObj->populate();
$pPosObj->getField('end_date')->setFromDB('0000-00-00 00:00:00');
$pPosObj->reason = '';
$pPosObj->save($user);
*
$positionObject = $ff->createContainer('position|124065');
$positionObject->populate();
echo $positionObject->getField('title')->getValue();
echo $positionObject->getField('facility')->getDBValue();
echo $positionObject->getField('pos_type')->getDBValue();
$positionObject->title = 'Medical Officer';
$positionObject->facility = array('facility',6028);
$positionObject->job = array('job',44);
$positionObject->pos_type = array('position_type',1);
echo '';
$positionObject->save($user);
echo $positionObject->getField('title')->getValue();
*/
# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
