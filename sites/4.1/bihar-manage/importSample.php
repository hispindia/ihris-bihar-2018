<?php

/**
 * The best way to run this is:
 * php importCSV.php 2> convert.log
 * There's lots of notice messages you probably want to ignore for the most
 * part.
 * You'll need to change the include file to find the right config file
 * as well as the path to I2CE which may not work right using the one
 * from the config file.
 * The ID for the User object should be valid in your user table.
 * The $forms array is an associative array with the value being
 * an array of forms that are required for the given form to work e.g. 
 * region needs country first since it uses country as a map for a field.
 *
 * 
 *
 */

$i2ce_site_user_access_init = null;
$script = array_shift( $argv );
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php')) {
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/local' . DIRECTORY_SEPARATOR . 'config.values.php');
} else {
	require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'pages/config.values.php');
}

$i2ce_site_i2ce_path = "../../lib/4.0/I2CE";

require_once ($i2ce_site_i2ce_path . DIRECTORY_SEPARATOR . 'I2CE_config.inc.php');

@I2CE::initializeDSN($i2ce_site_dsn,   $i2ce_site_user_access_init,    $i2ce_site_module_config);

unset($i2ce_site_user_access_init);
unset($i2ce_site_dsn);
unset($i2ce_site_i2ce_path);
unset($i2ce_site_module_config);



$user = new I2CE_User(1, false, false, false);
$db = MDB2::singleton();
if ( PEAR::isError( $db ) ) {
	die( $db->getMessage() );
}
$form_factory = I2CE_FormFactory::instance();

echo "Memory Limit: " . ini_get( "memory_limit" ) . "\n";
echo "Execution Time: " . ini_get( "max_execution_time" ) . "\n";

if ( $argv[0] == "erase" ) {
    echo "Erasing all records and entries...";
    $db->query( "TRUNCATE TABLE record" );
    $db->query( "TRUNCATE TABLE entry" );
    $db->query( "TRUNCATE TABLE last_entry" );
    echo "Done\n";
    $tmp = array_shift( $argv );
}

function dotrim(&$value){
  $value = trim($value);
}

$fh = fopen( $argv[0], "r" );
if ( $fh === false ) {
    die( "Couldn't update file: $argv[0].  Syntax: importCSV.php [erase] file.csv\n" );
}

$districts = array_flip( I2CE_List::listOptions( "district" ) );
$facilities = array_flip( I2CE_List::listOptions( "facility", "facility_type", "facility_type|6" ) );
$jobs = array_flip( I2CE_List::listOptions( "job" ) );
$degrees = array_flip( I2CE_List::listOptions( "degree" ) );
$competencies = array_flip( I2CE_List::listOptions( "competency" ) );


$p_code = 0;

while ( ( $data = fgetcsv( $fh ) ) !== false ) {

    array_walk( $data, "dotrim" );

    $name = explode( ' ', strtoupper( $data[1] ) );
    $surname = array_pop( $name );
    $firstname = array_shift( $name );
    $othernames = implode( ' ', $name );

    if ( !array_key_exists( $data[4], $jobs ) ) {
        echo "Couldn't find job: $data[4]\n";
    } else {
        $p_code++;
        $post_code = sprintf( "%05d", $p_code );
  
        $post = $form_factory->createContainer( "position" );
        $post->code = $post_code;
        $post->getField("job")->setFromDB( "job|" . $jobs[$data[4]] );
        $post->title = $data[4];
    
        if ( array_key_exists( $data[3] . " District Hospital", $facilities ) ) {
            $post->getField("facility")->setFromDB( "facility|" 
                    . $facilities[ $data[3] . " District Hospital" ] );
        }
        $post->proposed_hiring_date = I2CE_Date::blank();
        $post->proposed_end_date = I2CE_Date::blank();
        $post->posted_date = I2CE_Date::blank();
        $post->getField("status")->setFromDB("position_status|closed");
        $post->save( $user );
    }
  
    $person = $form_factory->createContainer( "person" );
    $person->surname = $surname;
    $person->firstname = $firstname;
    $person->othername = $othernames;
    $person->getField("nationality")->setFromDB( "country|IN" );
    if ( array_key_exists( $data[2], $districts ) ) {
        $person->getField("residence")->setFromDB( "district|" . $districts[ $data[2] ] );
    }
    $person->surname_ignore = true;
    $person->save( $user );
  
    if ( array_key_exists( $data[4], $jobs ) ) {
        $pers_pos = $form_factory->createContainer( "person_position" );
        $pers_pos->setParent( $person->getFormId() );
        $pers_pos->getField("position")->setFromDB( $post->getFormId() );
        $appt_date = explode( '-', $data[7] );
        if ( count( $appt_date ) == 3 ) {
            $appt_date_obj = I2CE_Date::getDate( $appt_date[0], $appt_date[1], $appt_date[2] );
        } else {
            $appt_date_obj = I2CE_Date::getDate( 1, 1, 1900 );
        }
        $pers_pos->start_date = $appt_date_obj;
        $pers_pos->save( $user );
  
        $salary = $form_factory->createContainer("salary");
        $salary->setParent( $pers_pos->getFormId() );
        $salary->start_date = $appt_date_obj;
        $salary->end_date = I2CE_Date::blank();
        $salary->save( $user );
        $salary->cleanup();
        unset( $salary );
    
        $pers_pos->cleanup();
        unset( $pers_pos );
    }
  
    if ( array_key_exists( $data[8], $degrees ) ) {
        $education = $form_factory->createContainer("education");
        $education->setParent( $person->getFormId() );
        $education->getField("degree")->setFromDB( "degree|" . $degrees[ $data[8] ] );
        $education->year = I2CE_Date::blank( I2CE_Date::YEAR_ONLY );
        $education->save( $user );
        $education->cleanup();
        unset( $education );
    }
  
    $first_date = explode( '-', $data[7] );
    if ( count( $first_date ) == 3 && array_key_exists( $data[4], $jobs ) ) {
        $employment = $form_factory->createContainer("joining_job");
        $employment->setParent( $person->getFormId() );
        $first_date_obj = I2CE_Date::getDate( $first_date[0], $first_date[1], $first_date[2] );
        $employment->date_of_joining = $first_date_obj;
        $employment->getField("job")->setFromDB( "job|" . $jobs[$data[4]] );
        $employment->save( $user );
        $employment->cleanup();
        unset( $employment );
    }
  
    if ( $data[6] != "" ) {
        $demo = $form_factory->createContainer( "demographic" );
        $demo->setParent( $person->getFormId() );
  
        $dob = explode( '-', $data[6] );
        if ( count( $dob ) == 3 ) {
            $demo->birth_date = I2CE_Date::getDate( $dob[0], $dob[1], $dob[2] );
        }
        $demo->save( $user );
        $demo->cleanup();
        unset( $demo );
    }
  
    if ( $data[5] != "" ) {
        $id = $form_factory->createContainer( "person_id" );
        $id->getField("id_type")->setFromDB("id_type|BR.1");
        $id->id_num = $data[5];
        $id->setParent( $person->getFormId() );
        $id->save( $user );
        $id->cleanup();
        unset( $id );
    }

    for( $i = 9; $i <= 11; $i++ ) {
        if ( array_key_exists( $data[$i], $competencies ) ) {
            $comp = $form_factory->createContainer( "person_competency" );
            $comp->setParent( $person->getFormId() );
            $comp->evaluation_date = I2CE_Date::blank();
            $comp->getField("competency_evaluation")->setFromDB("competency_evaluation|competent");
            $comp->getField("competency")->setFromDB("competency|" . $competencies[$data[$i]] );
            $comp->save( $user );
            $comp->cleanup();
            unset( $comp );
        }
    }
  
    
    if ( array_key_exists( $data[4], $jobs ) ) {
        $post->cleanup();
        unset( $post );
    }

    $person->cleanup();
    unset( $person );
    echo "Added $firstname $surname\n";
                
}

?>
