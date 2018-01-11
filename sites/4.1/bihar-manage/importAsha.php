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

define( 'iHRIS_DEFAULT_STATE', 'Bihar' );
define( 'iHRIS_DEFAULT_COUNTRY', 'India' );

define( 'iHRIS_PERSON_TITLE', 0 );
define( 'iHRIS_FIRST_NAME', 1 );
define( 'iHRIS_MIDDLE_NAME', 2 );
define( 'iHRIS_SURNAME', 3 );
define( 'iHRIS_NATIONALITY', 4 );
define( 'iHRIS_RESIDENCE_VILLAGE', 5 );
define( 'iHRIS_RESIDENCE_THANA', 6 );
define( 'iHRIS_RESIDENCE_BLOCK', 7 );
define( 'iHRIS_RESIDENCE_DISTRICT', 8 );
define( 'iHRIS_RESIDENCE_STATE', 9 );

define( 'iHRIS_VOTER_ID_NUM', 10);
define( 'iHRIS_RATION_CARD_NUM', 11 );
define( 'iHRIS_DRIVING_LICENSE_NUM', 12 );
define( 'iHRIS_OTHER_ID_NUM', 13 );

define( 'iHRIS_DATE_OF_BIRTH', 15 );
define( 'iHRIS_GENDER', 16 );
define( 'iHRIS_BIRTH_BLOCK', 17 );
define( 'iHRIS_BIRTH_DISTRICT', 18 );
define( 'iHRIS_BIRTH_STATE', 19 );
define( 'iHRIS_MARITAL_STATUS', 20 );
define( 'iHRIS_DEPENDENTS', 21 );
define( 'iHRIS_CASTE', 22 );
define( 'iHRIS_FATHER_FIRSTNAME', 23 );
define( 'iHRIS_FATHER_SURNAME', 24 );
define( 'iHRIS_MOTHER_FIRSTNAME', 25 );
define( 'iHRIS_MOTHER_SURNAME', 26 );
define( 'iHRIS_HUSBAND_FIRSTNAME', 27 );
define( 'iHRIS_HUSBAND_SURNAME', 28 );
define( 'iHRIS_ADDRESS', 29 );
define( 'iHRIS_WORK_PHONE', 30 );

define( 'iHRIS_MOBILE_PHONE', 31 );
define( 'iHRIS_EMAIL_ADDRESS', 32 );
define( 'iHRIS_DESIGNATION', 34 );

define( 'iHRIS_START_DATE', 35 );

define( 'iHRIS_FACILITY_TYPE_PHC', 46 );
define( 'iHRIS_FACILITY_TYPE_HSC', 57 );
define( 'iHRIS_FACILITY_TYPE_APHC', 58 );
define( 'iHRIS_FACILITY_BLOCK', 59 );
define( 'iHRIS_FACILITY_DISTRICT', 60 );

define( 'iHRIS_LANG_ENGLISH', 61 );
define( 'iHRIS_LANG_HINDI', 62 );
define( 'iHRIS_LANG_OTHER', 63 );
define( 'iHRIS_ACADEMIC_QUALIFICATION', 64 );
define( 'iHRIS_ASHA_TRAINING', 65 );
define( 'iHRIS_NOTES', 66 );


$person_id_types = array(  
        iHRIS_VOTER_ID_NUM => 'Voter ID Card No.',
        iHRIS_RATION_CARD_NUM => 'Ration Card No.', 
        iHRIS_DRIVING_LICENSE_NUM => 'Driving License No.',
        iHRIS_OTHER_ID_NUM => 'Other ID Number' );

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


global $user;

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
  $value = ucwords( strtolower( trim($value) ) );
}

$fh = fopen( $argv[0], "r" );
if ( $fh === false ) {
    die( "Couldn't update file: $argv[0].  Syntax: importCSV.php [erase] file.csv\n" );
}

function find_or_create( $value, $form, $fields=false ) {
    global $user, $cache;
    if ( !array_key_exists( $form, $cache ) ) {
        $cache[$form] = array();
    }
    if ( !array_key_exists( $value, $cache[$form] ) ) {
        $obj = I2CE_FormFactory::instance()->createContainer( $form );
        if ( !$fields ) {
            $fields = array( 'name' => $value );
        } 
        foreach( $fields as $key => $val ) {
            $obj->getField($key)->setFromDB($val);
        }
        $obj->save( $user );
        echo "Creating new form ($form) " . $obj->getId() . " ";
        print_r( $fields );
        $cache[$form][$value] = $obj->getNameId();
        $obj->cleanup();
        unset( $obj );
    }
    return $cache[$form][$value];
}

function arrange_date( $date ) {
    if ( $date == '' ) {
        return "";
    }
    list( $day, $month, $year ) = preg_split( "/[\/\-]/", $date );
    if ( $year < 11 ) {
        $year += 2000;
    } elseif ( $year < 100 ) {
        $year += 1900;
    }
    return sprintf( "%04d-%02d-%02d", $year, $month, $day );
}

$cache = array();
$cache['person_title'] = array_flip( I2CE_List::listOptions( "person_title" ) );
$cache['county'] = array_flip( I2CE_List::listOptions( "county" ) );
$cache['district'] = array_flip( I2CE_List::listOptions( "district" ) );
$cache['region'] = array_flip( I2CE_List::listOptions( "region" ) );
$cache['country'] = array_flip( I2CE_List::listOptions( "country" ) );
$cache['id_type'] = array_flip( I2CE_List::listOptions( "id_type" ) );
$cache['caste'] = array_flip( I2CE_List::listOptions( "caste" ) );
$cache['gender'] = array_flip( I2CE_List::listOptions( "gender" ) );
$cache['marital_status'] = array_flip( I2CE_List::listOptions( "marital_status" ) );
$cache['facility'] = array_flip( I2CE_List::listOptions( "facility" ) );
$cache['facility_type'] = array_flip( I2CE_List::listOptions( "facility_type" ) );
$cache['job'] = array_flip( I2CE_List::listOptions( "job" ) );
$cache['language'] = array_flip( I2CE_List::listOptions( "language" ) );
$cache['degree'] = array_flip( I2CE_List::listOptions( "degree" ) );
$cache['edu_type'] = array_flip( I2CE_List::listOptions( "edu_type" ) );
$cache['asha_training'] = array_flip( I2CE_List::listOptions( "asha_training" ) );

$p_code = 0;

while ( ( $data = fgetcsv( $fh ) ) !== false ) {

    array_walk( $data, "dotrim" );


    /* 
     * Create the person form and save it.
     */
    $person = $form_factory->createContainer( "person" );

    $title_id = find_or_create( $data[iHRIS_PERSON_TITLE], "person_title" );
    $person->getField('person_title')->setFromDB( $title_id );
    $person->firstname = $data[iHRIS_FIRST_NAME];
    $person->othername = $data[iHRIS_MIDDLE_NAME];
    $person->surname = $data[iHRIS_SURNAME];
    if ( !$data[iHRIS_NATIONALITY] ) {
        $data[iHRIS_NATIONALITY] = iHRIS_DEFAULT_COUNTRY;
    }
    $nationality_id = find_or_create( $data[iHRIS_NATIONALITY], "country" );
    $person->getField('nationality')->setFromDB( $nationality_id );
    $person->residence_thana = $data[iHRIS_RESIDENCE_THANA];
    $person->residence_village = $data[iHRIS_RESIDENCE_VILLAGE];

    if ( !$data[iHRIS_RESIDENCE_STATE] ) {
        $data[iHRIS_RESIDENCE_STATE] = iHRIS_DEFAULT_STATE;
    }
    $country_id = find_or_create( iHRIS_DEFAULT_COUNTRY, "country" );
    $region_id = find_or_create( 
            $data[iHRIS_RESIDENCE_STATE] . ", " . iHRIS_DEFAULT_COUNTRY, 
            "region",
            array( 'name' => $data[iHRIS_RESIDENCE_STATE],
                'country' => $country_id ) );
    if ( $data[iHRIS_RESIDENCE_DISTRICT] ) {
        $district_id = find_or_create(
                $data[iHRIS_RESIDENCE_DISTRICT] . ", " 
                . $data[iHRIS_RESIDENCE_STATE] . ", " . iHRIS_DEFAULT_COUNTRY, 
                "district",
                array( 'name' => $data[iHRIS_RESIDENCE_DISTRICT],
                    'region' => $region_id ) );
        if ( $data[iHRIS_RESIDENCE_BLOCK] ) {
            $county_id = find_or_create(
                    $data[iHRIS_RESIDENCE_BLOCK] . ", " 
                    . $data[iHRIS_RESIDENCE_DISTRICT] . ", " 
                    . $data[iHRIS_RESIDENCE_STATE] . ", " 
                    . iHRIS_DEFAULT_COUNTRY, 
                    "county",
                    array( 'name' => $data[iHRIS_RESIDENCE_BLOCK],
                        'district' => $district_id ) );
            $person->getField('residence')->setFromDB( $county_id );
        } else {
            $person->getField('residence')->setFromDB( $district_id );
        }
    }

    $person->save( $user );
    echo "created " . $person->getId() . "\n";


    /*
     * Create the person id forms and save them.
     */
    foreach ( $person_id_types as $csv_idx => $id_type ) {
        if( $data[$csv_idx] == '' ) {
            continue;
        }
        $id_type_id = find_or_create( $id_type, "id_type" );
        $person_id = $form_factory->createContainer( "person_id" );
        $person_id->setParent( $person->getNameId() );

        $person_id->getField( 'id_type' )->setFromDB( $id_type_id );
        $person_id->id_num = $data[ $csv_idx ];
        $person_id->save( $user );

        $person_id->cleanup();
        unset( $person_id );
    }


    /*
     * Create the demographic form and save it.
     */
    $demographic = $form_factory->createContainer( "demographic" );
    $demographic->setParent( $person->getNameId() );

    if ( $data[iHRIS_DATE_OF_BIRTH] != '' ) {
        $demographic->getField('birth_date')->setFromDB( arrange_date( $data[iHRIS_DATE_OF_BIRTH] ) );
    }
    if ( $data[iHRIS_GENDER] ) {
        $gender_id = find_or_create( $data[iHRIS_GENDER], "gender" );
        $demographic->getField('gender')->setFromDB( $gender_id );
    }
    if ( !$data[iHRIS_BIRTH_STATE] ) {
        $data[iHRIS_BIRTH_STATE] = iHRIS_DEFAULT_STATE;
    }
    $region_id = find_or_create(
            $data[iHRIS_BIRTH_STATE] . ", "
            . iHRIS_DEFAULT_COUNTRY,
            "region",
            array( 'name' => $data[iHRIS_BIRTH_STATE],
                'country' => $country_id ) );
    if ( $data[iHRIS_BIRTH_DISTRICT] ) {
        $district_id = find_or_create(
                $data[iHRIS_BIRTH_DISTRICT] . ", " 
                . $data[iHRIS_BIRTH_STATE] . ", " . iHRIS_DEFAULT_COUNTRY,
                "district",
                array( 'name' => $data[iHRIS_BIRTH_DISTRICT],
                    'region' => $region_id ) );
        if ( $data[iHRIS_BIRTH_BLOCK] ) {
            $county_id = find_or_create(
                    $data[iHRIS_BIRTH_BLOCK] . ", " 
                    . $data[iHRIS_BIRTH_DISTRICT] . ", " 
                    . $data[iHRIS_BIRTH_STATE] . ", " 
                    . iHRIS_DEFAULT_COUNTRY, 
                    "county",
                    array( 'name' => $data[iHRIS_BIRTH_BLOCK],
                        'district' => $district_id ) );
            $demographic->getField('birth_location')->setFromDB( $county_id );
        } else {
            $demographic->getField('birth_location')->setFromDB( $district_id );
        }
    }
    if ( $data[iHRIS_MARITAL_STATUS] ) {
        $marital_status_id = find_or_create( $data[iHRIS_MARITAL_STATUS], "marital_status" );
        $demographic->getField('marital_status')->setFromDB( $marital_status_id );
    }
    if ( $data[iHRIS_DEPENDENTS] != '' ) {
        $demographic->dependents = $data[iHRIS_DEPENDENTS];
    }
    if ( $data[iHRIS_CASTE] ) {
        $caste_id = find_or_create( $data[iHRIS_CASTE], "caste" );
        $demographic->getField('caste')->setFromDB( $caste_id );
    }
    
    $demographic->save( $user );
    $demographic->cleanup();
    unset( $demographic );


    /*
     * Create the parent information form and save it.
     */
    $parent_information = $form_factory->createContainer( "parent_information" );
    $parent_information->setParent( $person->getNameId() );

    $parent_information->father_surname = $data[iHRIS_FATHER_SURNAME];
    $parent_information->father_firstname = $data[iHRIS_FATHER_FIRSTNAME];
    $parent_information->mother_surname = $data[iHRIS_MOTHER_SURNAME];
    $parent_information->mother_firstname = $data[iHRIS_MOTHER_FIRSTNAME];
    $parent_information->husband_surname = $data[iHRIS_HUSBAND_SURNAME];
    $parent_information->husband_firstname = $data[iHRIS_HUSBAND_FIRSTNAME];

    $parent_information->save( $user );
    $parent_information->cleanup();
    unset( $parent_information );
    

    /*
     * Create the work contact form and save it.
     */
    $contact = $form_factory->createContainer( "person_contact_work" );
    $contact->setParent( $person->getNameId() );

    $contact->address = $data[iHRIS_ADDRESS];
    $contact->telephone = $data[iHRIS_WORK_PHONE];
    $contact->alt_telephone = $data[iHRIS_MOBILE_PHONE];
    $contact->email = $data[iHRIS_EMAIL_ADDRESS];
    $contact->save( $user );
    $contact->cleanup();
    unset( $contact );

    /*
     * Create the position form and save it.
     */
    // First get the facility details and create or find it.

    $facility_type = "";
    if ( $data[iHRIS_FACILITY_TYPE_PHC] != '' ) {
        $facility_type = strtoupper( $data[iHRIS_FACILITY_TYPE_PHC] );
    } elseif ( $data[iHRIS_FACILITY_TYPE_HSC] != '' ) {
        $facility_type = stroupper( $data[iHRIS_FACILITY_TYPE_HSC] );
    } elseif ( $data[iHRIS_FACILITY_TYPE_APHC] != '' ) {
        $facility_type = strtoupper( $data[iHRIS_FACILITY_TYPE_APHC] );
    }
    if ( $facility_type != "" ) {
        $facility_name = $data[iHRIS_FACILITY_BLOCK] . " " . $facility_type;
    } else {
        $facility_name = $data[iHRIS_FACILITY_BLOCK];
    }

    $region_id = find_or_create( iHRIS_DEFAULT_STATE . ", " .
            iHRIS_DEFAULT_COUNTRY,
            "region",
            array( 'name' => iHRIS_DEFAULT_STATE,
                'country' => iHRIS_DEFAULT_COUNTRY ) );
    $facility_fields = array( 'name' => $facility_name );
    $district_id = find_or_create(
            $data[iHRIS_FACILITY_DISTRICT] . ", " 
            . iHRIS_DEFAULT_STATE . ", " . iHRIS_DEFAULT_COUNTRY,
            "district",
            array( 'name' => $data[iHRIS_FACILITY_DISTRICT],
                'region' => $region_id ) );
    $county_id = find_or_create(
            $data[iHRIS_FACILITY_BLOCK] . ", " 
            . $data[iHRIS_FACILITY_DISTRICT] . ", " 
            . iHRIS_DEFAULT_STATE . ", " 
            . iHRIS_DEFAULT_COUNTRY, 
            "county",
            array( 'name' => $data[iHRIS_FACILITY_BLOCK],
                'district' => $district_id ) );
    $facility_fields['location'] = $county_id;
    if ( $facility_type != "" ) {
        $facility_fields['facility_type'] = find_or_create( $facility_type, "facility_type" );
    }
    $facility_id = find_or_create( $facility_fields['name'],
            "facility", $facility_fields );

    // Now we create the job (designation).

    $job_code = preg_replace( "/[^A-Z]/", "", $data[iHRIS_DESIGNATION] );
    $job_id = find_or_create( $data[iHRIS_DESIGNATION], "job",
            array( 'title' => $data[iHRIS_DESIGNATION],
                'code' => $job_code ) );

    // Now we create the position.
    $p_code++;
    $post_code = sprintf( "%s-%05d", $job_code, $p_code );
    $position = $form_factory->createContainer( "position" );
    
    $position->code = $post_code;
    $position->getField('job')->setFromDB( $job_id );
    $position->title = $data[iHRIS_DESIGNATION];
    $position->getField('facility')->setFromDB( $facility_id );
    $position->getField('status')->setFromDB( 'position_status|closed' );

    $position->save( $user );

    // Now we assign the position to the person.
    $person_position = $form_factory->createContainer( "person_position" );
    $person_position->setParent( $person->getNameId() );

    $person_position->getField("position")->setFromDB( $position->getNameId() );
    $person_position->getField("start_date")->setFromDB( arrange_date( $data[iHRIS_START_DATE] ) );

    $person_position->save( $user );

    $salary = $form_factory->createContainer( "salary" );
    $salary->setParent( $person_position->getNameId() );
    $salary->getField('start_date')->setFromDB( arrange_date( $data[iHRIS_START_DATE] ) );

    $salary->save( $user );
    $salary->cleanup();
    unset( $salary );


    $person_position->cleanup();
    unset( $person_position );

    $position->cleanup();
    unset( $position );


    if (strtolower($data[iHRIS_LANG_ENGLISH]) == 'yes') {
        $language = $form_factory->createContainer( "person_language" );
        $language->setParent( $person->getNameId() );
    
        $language_id = find_or_create( 'English', "language" );                       
        $language->getField("language")->setFromDB( $language_id );
    
        $language->save( $user );
        $language->cleanup();
        unset( $language );
    }
    if (strtolower($data[iHRIS_LANG_HINDI]) == 'yes') {
        $language = $form_factory->createContainer( "person_language" );
        $language->setParent( $person->getNameId() );
    
        $language_id = find_or_create( 'Hindi', "language" );                       
        $language->getField("language")->setFromDB( $language_id );
    
        $language->save( $user );
        $language->cleanup();
        unset( $language );
    }
    if ($data[iHRIS_LANG_OTHER] != '') {
        $language = $form_factory->createContainer( "person_language" );
        $language->setParent( $person->getNameId() );
    
        $language_id = find_or_create( $data[iHRIS_LANG_OTHER], "language" );                       
        $language->getField("language")->setFromDB( $language_id );
    
        $language->save( $user );
        $language->cleanup();
        unset( $language );
    }


    $education = $form_factory->createContainer( "education" );
    $education->setParent( $person->getNameId() );
    
    $degree_id = find_or_create( $data[iHRIS_ACADEMIC_QUALIFICATION], "degree",
         array( 'name' => $data[iHRIS_ACADEMIC_QUALIFICATION],
                'edu_type' => find_or_create( 'Others', 'edu_type' ),
         ) );                       
        
    $education->getField("degree")->setFromDB( $degree_id );

    $education->save( $user );
    $education->cleanup();
    unset( $education );



    if ( $data[iHRIS_ASHA_TRAINING] != '' ) {
        $asha_training = explode( ',', $data[iHRIS_ASHA_TRAINING] );
        foreach( $asha_training as $module ) {
            $module = "Module " . $module;
            $asha = $form_factory->createContainer( "asha" );
            $asha->setParent( $person->getNameId() );
     
            $training_id = find_or_create( $module, "asha_training" );                           
            $asha->getField("asha_training")->setFromDB( $training_id );       
    
            $asha->save( $user );
            $asha->cleanup();
            unset( $asha );
        }
    }
    


    if ( $data[iHRIS_NOTES] != "" ) {
    $notes = $form_factory->createContainer( "notes" );
    $notes->setParent( $person->getNameId() );
    $notes->note=$data[iHRIS_NOTES];
    $notes->date_added=I2CE_Date::now(); 
   
    $notes->save( $user );
    $notes->cleanup();
    unset( $notes );
    }


    $person->cleanup();
    unset( $person );

}

?>
