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


$i2ce_site_user_access_init = null;
require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.values.php');

$local_config = dirname(__FILE__) . DIRECTORY_SEPARATOR .
'local' . DIRECTORY_SEPARATOR . 'config.values.php';
if (file_exists($local_config)) {
    require_once($local_config);
}

if(!isset($i2ce_site_i2ce_path) || !is_dir($i2ce_site_i2ce_path)) {
    echo "Please set the \$i2ce_site_i2ce_path in $local_config";
    exit(55);
}

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');
@I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);         


unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);



$rel = I2CE::getConfig()->traverse("/modules/CustomReports/relationships");
foreach( $rel as $relationship => $data ) {
    $reports[$relationship] = array( 'name' => $data->display_name, 'reports' => array() );
}

$rep = I2CE::getConfig()->traverse("/modules/CustomReports/reports");
$rels = array();
foreach( $rep as $report => $data ) {
    $rels[$report] = $data->relationship;
    $reports[$data->relationship]['reports'][$report] = array( 'name' => $data->meta->display_name, 'category' => $data->meta->category, 'views' => array() );
}

$views = I2CE::getConfig()->traverse("/modules/CustomReports/reportViews");
foreach( $views as $view => $data ) {
    if ( $data->report == '' ) {
        //echo "No report for $view!\n";
        echo ",,,,$view," . $data->display_name . "\n";
        continue;
    }
    if ( !array_key_exists( $data->report, $rels ) ) {
        //echo "No report for $view (" . $data->report.")\n";
        echo ",,,,$view," . $data->display_name . "\n";
        continue;
    }
    $reports[ $rels[$data->report] ]['reports'][ $data->report ]['views'][$view] = array( 'name' => $data->display_name );
}
foreach( $reports as $relationship => $rel_data ) {
    $output = array();
    $output[0] = $relationship;
    if ( !array_key_exists( 'name', $rel_data ) ) {
        $output[1] = "ERR";
    } else {
        $output[1] = $rel_data['name'];
    }
    if ( count( $rel_data['reports'] ) > 0 ) {
        foreach( $rel_data['reports'] as $report => $rep_data ) {
            $output[2] = $report;
            if ( !array_key_exists( 'name', $rep_data ) ) {
                $output[3] = 'ERR';
            } else {
                $output[3] = $rep_data['name'];
            }
            if ( count( $rep_data['views'] ) > 0 ) {
                foreach( $rep_data['views'] as $view => $view_data ) {
                    $output[4] = $view;
                    $output[5] = $view_data['name'];
                    fputcsv( STDOUT, $output );
                } 
            } else {
                $ouput[4] = '';
                $ouput[5] = '';
                fputcsv( STDOUT, $output );
            }
        }
    } else {
        $output[2] = '';
        $output[3] = '';
        fputcsv( STDOUT, $output );
    }
}

# Local Variables:
# mode: php
# c-default-style: "bsd"
# indent-tabs-mode: nil
# c-basic-offset: 4
# End:
