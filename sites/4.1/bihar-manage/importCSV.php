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

global $dictionary;
$dictionary = array();

$dictionary['gender'] = array( 'm' => 'Male', 'f' => 'Female', 
        'Femalee' => 'Female', 'FIMALE' => 'Female' );
$dictionary['caste'] = array( 'OBC' => 'Other Backward Caste' );
$dictionary['specialty'] = array( 'None' => '' );
$dictionary['language'] = array( 'no' => '' );
$dictionary['position_type'] = array( 'Other' => '' );
$dictionary['marital_status'] = array( 
        'Widow' => 'Widowed',
        'Widower' => 'Widowed',
        );
$dictionary['domain_of_study'] = array(
        'Other (specify)' => '',
        );
$dictionary['job'] = array( 
        'Nurse "A"Grade' => 'Nurse "A Grade"',
        'Medical Officer-in Charge' => 'Medical Officer -in -Charge',
        'Data Operator/Assistant ' => 'Data Entry Operator/Data Operator',
        'Male Room Attendant' => 'Male Ward Attendant',
        );
$dictionary['country'] = array( 'Indian' => 'India' );
$dictionary['facility'] = array( 
        'Block Forbisganj RH PHC' => 'Block Forbisganj RH/PHC',
        'Block Raniganj RH PHC' => 'Block Raniganj RH/PHC',
        'Bairakh' => 'Bairakh HSC',
        'Raniganj' => 'Block Raniganj RH/PHC',
        'Gidwas' => 'Gidwas HSC',
        'Belsara' => 'Belsara APHC',
        'Mirzapur' => 'Mirzapur APHC',
        'Parsahat' => 'Parsahat HSC',
        'Basaithi' => 'Basaithi HSC',
        );
$dictionary['district'] = array(
        'Samstipur, Bihar, India' => 'Samastipur, Bihar, India',
        'Bachhwara, Bihar, India' => 'Begusarai, Bihar, India',
        'Purnea, Bihar, India' => 'Purnia, Bihar, India',
        'Supol, Bihar, India' => 'Supaul, Bihar, India',
        'Supoul, Bihar, India' => 'Supaul, Bihar, India',
        'Khagariya, Bihar, India' => 'Khagaria, Bihar, India',
        'Khagadia, Bihar, India' => 'Khagaria, Bihar, India',
        'Purwee Champaran, Bihar, India' => 'East Champaran, Bihar, India',
        'Est Champaran, Bihar, India' => 'East Champaran, Bihar, India',
        'Madhuwani, Bihar, India' => 'Madhubani, Bihar, India',
        'Sikandra, Bihar, India' => 'Jamui, Bihar, India',
        'Kaomur (Bhabhua), Bihar, India' => 'Kaimur, Bihar, India',
        'Rohtash, Bihar, India' => 'Rohtas, Bihar, India',

        'Dhanbad, Bihar, India' => 'Dhanbad, Jharkhand, India',
        'Pakud, Jharkhand, India' => 'Pakur, Jharkhand, India',
        'Poda Hat, Jharkhand, India' => 'Godda, Jharkhand, India',
        );
$dictionary['county'] = array(
        'Katihar, Katihar, Bihar, India' => 'Block Katihar Sadar, Katihar, Bihar, India',
        'Araria, Araria, Bihar, India' => 'Block Araria Sadar, Araria, Bihar, India',
        'Sikti, Araria, Bihar, India' => 'Block Sikty, Araria, Bihar, India',
        'Sikti, , Bihar, India' => 'Block Sikty, Araria, Bihar, India',
        'Mansoorchak, Begusarai, Bihar, India' => 'Block Mansurchak, Begusarai, Bihar, India',
        'Joki Hat, Araria, Bihar, India' => 'Block Jokihat, Araria, Bihar, India',
        'Singwara, Darbhanga, Bihar, India' => 'Block Singhwara, Darbhanga, Bihar, India',
        'Bhabua, Kaimur, Bihar, India' => 'Block Bhabua Sadar, Kaimur, Bihar, India',
        'Bhabhua, Kaimur, Bihar, India' => 'Block Bhabua Sadar, Kaimur, Bihar, India',
        'plasi, Araria, Bihar, India' => 'Block Palasi, Araria, Bihar, India',
        'Bhargana, Araria, Bihar, India' => 'Block Bhargama, Araria, Bihar, India',
        'Baikundpur, Gopalganj, Bihar, India' => 'Block Baikunthpur, Gopalganj, Bihar, India',
        'Akarbarpur, Nawada, Bihar, India' => 'Block Akbarpur, Nawada, Bihar, India',
        'Ven, Nalanda, Bihar, India' => 'Block Ben, Nalanda, Bihar, India',
        'Karaypashuray, Nalanda, Bihar, India' => 'Block Karaiparsurai, Nalanda, Bihar, India',
        'Bakebazzar, Gaya, Bihar, India' => 'Block Bankebazar, Gaya, Bihar, India',
        'Bachhwara, Begusarai, Bihar, India' => 'Block Bachwara, Begusarai, Bihar, India',
        'Bachhwara, Bachhwara, Bihar, India' => 'Block Bachwara, Begusarai, Bihar, India',
        'Kararat, Rohtas, Bihar, India' => 'Block Karakat, Rohtas, Bihar, India',
        'Purnea, Purnea, Bihar, India' => 'Block Purnia East, Purnia, Bihar, India',
        'Chhatapur, Supol, Bihar, India' => 'Block Chhatapur, Supaul, Bihar, India',
        'Chireya, Purwee Champaran, Bihar, India' => 'Block Chiraiya, East Champaran, Bihar, India',
        'Suryagadha, Lakhisarai, Bihar, India' => 'Block Surajgadha, Lakhisarai, Bihar, India',
        'Suryagadha, Araria, Bihar, India' => 'Block Surajgadha, Lakhisarai, Bihar, India',
        'Khusarupur, Patna, Bihar, India' => 'Block Khusrupur, Patna, Bihar, India',
        'Nawada, Nawada, Bihar, India' => 'Block Nawada Sadar, Nawada, Bihar, India',
        'Tarrid, Darbhanga, Bihar, India' => 'Block Tardih, Darbhanga, Bihar, India',
        'Pandol, Madhuwani, Bihar, India' => 'Block Pandaul, Madhubani, Bihar, India',
        'Forbesganj, Araria, Bihar, India' => 'Block Forbisganj, Araria, Bihar, India',
        'Forbishganj, Araria, Bihar, India' => 'Block Forbisganj, Araria, Bihar, India',
        'Kursakanta, Araria, Bihar, India' => 'Block Kursa Kanta, Araria, Bihar, India',
        'Begusarai, Begusarai, Bihar, India' => 'Block Begusarai Sadar, Begusarai, Bihar, India',
        'Begusari, Begusarai, Bihar, India' => 'Block Begusarai Sadar, Begusarai, Bihar, India',
        'Begusari, Araria, Bihar, India' => 'Block Begusarai Sadar, Begusarai, Bihar, India',
        'Sikandra, Sikandra, Bihar, India' => 'Block Sikandra, Jamui, Bihar, India',
        'Bahadurganj, Darbhanga, Bihar, India' => 'Block Bahadurganj, Kishanganj, Bihar, India',
        'Dehri on Son, Rohtash, Bihar, India' => 'Block Dehri, Rohtas, Bihar, India',
        'Dori Hat, Rohtash, Bihar, India' => 'Block Dehri, Rohtas, Bihar, India',
        'Dulhin Bazzar, Patna, Bihar, India' => 'Block Dulhin Bazar, Patna, Bihar, India',
        'Lokahi, Madhuwani, Bihar, India' => 'Block Laukahi, Madhubani, Bihar, India',
        'Nawgachhiya, Bhagalpur, Bihar, India' => 'Block Naugachhia, Bhagalpur, Bihar, India',
        'Saharsa, Saharsa, Bihar, India' => 'Block Kahara (Saharsa), Saharsa, Bihar, India',
        'Barhara kothi, Purnea, Bihar, India' => 'Block Barharakothi, Purnia, Bihar, India',
        'Pakridayal, Est Champaran, Bihar, India' => 'Block Pakridayal, East Champaran, Bihar, India',
        'Tirbeniganj, Supoul, Bihar, India' => 'Block Trivaniganj, Supaul, Bihar, India',
        'Tiweniganj, Supoul, Bihar, India' => 'Block Trivaniganj, Supaul, Bihar, India',
        'Baraharakothi, Purnia, Bihar, India' => 'Block Barharakothi, Purnia, Bihar, India',
        'Barahara Kothi, Purnia, Bihar, India' => 'Block Barharakothi, Purnia, Bihar, India',
        'Ramgadh, Kaomur (Bhabhua), Bihar, India' => 'Block Ramgarh, Kaimur, Bihar, India',
        'Ramghadh, Kaimur, Bihar, India' => 'Block Ramgarh, Kaimur, Bihar, India',
        'Kahalgown, Bhagalpur, Bihar, India' => 'Block Kahalgaon, Bhagalpur, Bihar, India',
        'Kahalgwn, Bhagalpur, Bihar, India' => 'Block Kahalgaon, Bhagalpur, Bihar, India',
        'Sadar, Patna, Bihar, India' => 'Block Patna Sadar, Patna, Bihar, India',
        'Patna, Patna, Bihar, India' => 'Block Patna Sadar, Patna, Bihar, India',
        'Ratani Faritpur, Jahanabad, Bihar, India' => 'Block Ratni-Fridpur, Jahanabad, Bihar, India',
        'Gopal Pur, Bhagalpur, Bihar, India' => 'Block Gopalpur, Bhagalpur, Bihar, India',
        'Khagadia, Khagadia, Bihar, India' => 'Block Khagaria, Khagaria, Bihar, India',
        'Ekangar Sarai, Nalanda, Bihar, India' => 'Block Ekangarsarai, Nalanda, Bihar, India',
        'Kashba, Purnia, Bihar, India' => 'Block Kasba, Purnia, Bihar, India',
        'Dagarwa, Purnia, Bihar, India' => 'Block Dagruwa, Purnia, Bihar, India',
        'Chhatapur, Araria, Bihar, India' => 'Block Chhatapur, Supaul, Bihar, India',
        'Bihar sarif, Nawada, Bihar, India' => 'Block Biharsharif, Nalanda, Bihar, India',
        'Jamalpur, Khagariya, Bihar, India' => 'Block Jamalpur, Munger, Bihar, India',
        'Narayanpur, Araria, Bihar, India' => 'Block Narainpur, Bhagalpur, Bihar, India',
        'Bathnaha, Araria, Bihar, India' => 'Block Bathnaha, Sitamarhi, Bihar, India',
        'Kochadhaman, Araria, Bihar, India' => 'Block Kochadhaman, Kishanganj, Bihar, India',
        'Teragach, Araria, Bihar, India' => 'Block Terhagachh, Kishanganj, Bihar, India',

        'Dhanbad, Dhanbad, Bihar, India' => 'Block Dhanbad Sadar, Dhanbad, Jharkhand, India',
        'Pakharta, Khagariya, Bihar, India' => 'Block Parbatta, Khagaria, Bihar, India',
        'Top Chabi, Dhanbad, Bihar, India' => 'Block Topchanchi, Dhanbad, Jharkhand, India',
        'maheshpur, Pakud, Jharkhand, India' => 'Maheshpur, Pakur, Jharkhand, India',
        'Maheshpur Raj, Pakud, Jharkhand, India' => 'Block Maheshpur, Pakur, Jharkhand, India',
        'Poda Hat, Poda Hat, Jharkhand, India' => 'Block Poraiyahat, Godda, Jharkhand, India',
        'Saror, Bhagalpur, Bihar, India' => 'Block Sabour, Bhagalpur, Bihar, India',
        'Lal darwaza, Munger, Bihar, India' => 'Block Munger Sadar, Munger, Bihar, India',
        'Akama, Saran, Bihar, India' => 'Block Ekma, Saran, Bihar, India',
        'Baliabelone, Katihar, Bihar, India' => 'Block Barsoi, Katihar, Bihar, India',
        'Poda Hat, Godda, Jharkhand, India' => 'Block Poraiyahat, Godda, Jharkhand, India',
        );

define( 'iHRIS_DEFAULT_STATE', 'Bihar' );
define( 'iHRIS_DEFAULT_COUNTRY', 'India' );

define( 'iHRIS_PERSON_TITLE', 0 );
define( 'iHRIS_FIRST_NAME', 1 );
//define( 'iHRIS_MIDDLE_NAME', 2 );
define( 'iHRIS_SURNAME', 2 );
define( 'iHRIS_NATIONALITY', 3 );
define( 'iHRIS_RESIDENCE_VILLAGE', 4 );
define( 'iHRIS_RESIDENCE_THANA', 5 );
define( 'iHRIS_RESIDENCE_BLOCK', 6 );
define( 'iHRIS_RESIDENCE_DISTRICT', 7 );
define( 'iHRIS_RESIDENCE_STATE', 8 );

define( 'iHRIS_DESIGNATION', 9 );
define( 'iHRIS_START_DATE_DAY', 10 );
define( 'iHRIS_START_DATE_MONTH', 11 );
define( 'iHRIS_START_DATE_YEAR', 12 );
define( 'iHRIS_PAYSCALE', 13 );

define( 'iHRIS_GPF_CPF_NUM', 14 );
define( 'iHRIS_VOTER_ID_NUM', 15 );
define( 'iHRIS_RATION_CARD_NUM', 16 );
define( 'iHRIS_DRIVING_LICENSE_NUM', 17 );
define( 'iHRIS_OTHER_ID_NUM', 18 );
define( 'iHRIS_PHOTO', 19 );

define( 'iHRIS_DATE_OF_BIRTH_DAY', 20 );
define( 'iHRIS_DATE_OF_BIRTH_MONTH', 21 );
define( 'iHRIS_DATE_OF_BIRTH_YEAR', 22 );
define( 'iHRIS_GENDER', 23 );
define( 'iHRIS_BIRTH_VILLAGE', 24 );
define( 'iHRIS_BIRTH_BLOCK', 25 );
define( 'iHRIS_BIRTH_DISTRICT', 26 );
define( 'iHRIS_BIRTH_STATE', 27 );
define( 'iHRIS_MARITAL_STATUS', 28 );
define( 'iHRIS_DEPENDENTS', 29 );
define( 'iHRIS_CASTE', 30 );

define( 'iHRIS_FATHER_FIRSTNAME', 31 );
define( 'iHRIS_FATHER_SURNAME', 32 );
define( 'iHRIS_MOTHER_FIRSTNAME', 33 );
define( 'iHRIS_MOTHER_SURNAME', 34 );
define( 'iHRIS_HUSBAND_FIRSTNAME', 35 );
define( 'iHRIS_HUSBAND_SURNAME', 36 );

define( 'iHRIS_ADDRESS', 37 );
define( 'iHRIS_WORK_PHONE', 38 );
define( 'iHRIS_WORK_FAX', 39 );
define( 'iHRIS_MOBILE_PHONE', 40 );
define( 'iHRIS_EMAIL_ADDRESS', 41 );

define( 'iHRIS_FACILITY_NAME', 42 );
define( 'iHRIS_FACILITY_TYPE', 43 );
define( 'iHRIS_FACILITY_BLOCK', 44 );
define( 'iHRIS_FACILITY_DISTRICT', 45 );
define( 'iHRIS_CLASSIFICATION', 46 );
define( 'iHRIS_DEPARTMENT', 47 );

define( 'iHRIS_JOIN_DATE_DAY', 48 );
define( 'iHRIS_JOIN_DATE_MONTH', 49 );
define( 'iHRIS_JOIN_DATE_YEAR', 50 );
define( 'iHRIS_JOIN_DESIGNATION', 51 );
define( 'iHRIS_POSITION_TYPE', 52 );
define( 'iHRIS_RETIREMENT_DATE_DAY', 53 );
define( 'iHRIS_RETIREMENT_DATE_MONTH', 54 );
define( 'iHRIS_RETIREMENT_DATE_YEAR', 55 );

define( 'iHRIS_ON_DEPUTATION', 56 );
define( 'iHRIS_DEPUTATION_FACILITY_NAME', 57 );
define( 'iHRIS_DEPUTATION_DESIGNATION', 58 );
define( 'iHRIS_DEPUTATION_START_DATE_DAY', 59 );
define( 'iHRIS_DEPUTATION_START_DATE_MONTH', 60 );
define( 'iHRIS_DEPUTATION_START_DATE_YEAR', 61 );
define( 'iHRIS_DEPUTATION_FACILITY_TYPE', 62 );
define( 'iHRIS_DEPUTATION_BLOCK', 63 );
define( 'iHRIS_DEPUTATION_DISTRICT', 64 );
//define( 'iHRIS_DEPUTATION_DEPARTMENT', 48 );

define( 'iHRIS_LANG_ENGLISH', 65 );
define( 'iHRIS_LANG_HINDI', 66 );
define( 'iHRIS_LANG_OTHER', 67 );

define( 'iHRIS_ACADEMIC_QUALIFICATION', 68 );
//define( 'iHRIS_ACADEMIC_INSTITUTION', 58 );
//define( 'iHRIS_ACADEMIC_DISTRICT', 59 );
//define( 'iHRIS_ACADEMIC_STATE', 60 );
//define( 'iHRIS_ACADEMIC_YEAR', 61 );
define( 'iHRIS_ACADEMIC_DOMAIN1', 69 );
define( 'iHRIS_ACADEMIC_DOMAIN2', 70 );
define( 'iHRIS_ACADEMIC_DOMAIN3', 71 );
define( 'iHRIS_SPECIALTY1', 72 );
define( 'iHRIS_SPECIALTY2', 73 );
define( 'iHRIS_SPECIALTY_IN_PROGRESS', 74 );

define( 'iHRIS_ASHA_TRAINING_STATUS', 75 );

define( 'iHRIS_NOTES', 76 );

$person_id_types = array( iHRIS_GPF_CPF_NUM => 'GPF/CPF No.',
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

$i2ce_site_i2ce_path = "/var/lib/iHRIS/lib/4.1/I2CE";

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
  $value = trim($value);
}

$fh = fopen( $argv[0], "r" );
if ( $fh === false ) {
    die( "Couldn't update file: $argv[0].  Syntax: importCSV.php [erase] file.csv\n" );
}
$bad_filename = preg_replace( '/.csv$/', '.invalid.csv', $argv[0] );
$bh = fopen( $bad_filename, "w" );
if ( $bh === false ) {
    die( "Couldn't open new file $bad_filename for invalid rows.\n" );
}

function validate_location( $type, $row, $state, $district, $block ) {
    $errors = 0;
    if ( !$state ) {
        $state = iHRIS_DEFAULT_STATE;
    }
    $lookup_state = $state . ", " . iHRIS_DEFAULT_COUNTRY;
    if ( !find_or_create( $lookup_state, "region", false, false, true ) ) {
        echo "Row $row: $type State is invalid: '" . $lookup_state . "'\n";
        $errors++;
    }
    $lookup_district = $district . ", " . $lookup_state;
    if ( $district && !find_or_create( $lookup_district, "district", false, false, true ) ) {
        echo "Row $row: $type District is invalid: '" . $lookup_district . "'\n";
        $errors++;
    }
    $lookup_block = $block . ", " . $lookup_district;
    if ( $block && !find_or_create( $lookup_block, "county", false, false, true ) ) {
        echo "Row $row: $type Block is invalid: '" . $lookup_block . "'\n";
        $errors++;
    }
    return $errors;
}
function validate_field( $field, $form, $row, $desc, $ignore_blank=false ) {
    if ( $ignore_blank && $field == "" ) return 0;
    if( !find_or_create( $field, $form, false, false, true ) ) {
        echo "Row $row: $desc is invalid: '" . $field . "'\n";
        return 1;
    }
    return 0;
}


function find_or_create( $value, $form, $fields=false, $do_create=false, $validate=false, $ignore_error=false ) {
    global $user, $cache, $dictionary;
    if ( $value == "" ) return "";
    $upperval = strtoupper( $value );
    if ( array_key_exists( $form, $dictionary ) && array_key_exists( $upperval, $dictionary[$form] ) ) {
        $value = $dictionary[$form][$upperval];
        $upperval = strtoupper( $value );
        if ( $value == '' ) {
            if ( $validate ) {
                return true;
            } else {
                return '';
            }
        }
    }
    if ( !array_key_exists( $form, $cache ) ) {
        $cache[$form] = array();
    }
    $is_valid = true;
    if ( $form == 'county' && !array_key_exists( $upperval, $cache[$form] ) 
      && array_key_exists( 'BLOCK ' . $upperval, $cache[$form] ) ) {
        $value = 'Block ' . $value;
        $upperval = strtoupper( $value );
    }
    if ( !array_key_exists( $upperval, $cache[$form] ) ) {
        if ( $do_create ) {
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
            $cache[$form][$upperval] = $obj->getNameId();
            $obj->cleanup();
            unset( $obj );
        } else {
            if ( $validate ) {
                $is_valid = false;
            } elseif ( $ignore_error ) {
                echo "Invalid value for form: $form, value: $value.\n";
                return '';
            } else {
                die( "Invalid value for form: $form, value: $value. Can't continue." );
            }
        }
    }
    if ( $validate ) {
        return $is_valid;
    } else {
        return $cache[$form][$upperval];
    }
}

function arrange_date( $date ) {
    list( $day, $month, $year ) = explode( '/', $date );
    return sprintf( "%04d-%02d-%02d", $year, $month, $day );
}
function arrange_date3( $day, $month, $year ) {
    if ( $year <= 20 ) $year += 2000;
    elseif ( $year <= 99 ) $year += 1900;
    return sprintf( "%04d-%02d-%02d", $year, $month, $day );
}

function getCacheList( $form ) {
    $list = I2CE_List::listOptions( $form );
    $results = array();
    foreach( $list as $entry ) {
        $results[ strtoupper( $entry['display'] ) ] = $entry['value'];
    }
    return $results;
}

$cache = array();
$cache['person_title'] = getCacheList( "person_title" );
$cache['county'] = getCacheList( "county" );
$cache['district'] = getCacheList( "district" );
$cache['region'] = getCacheList( "region" );
$cache['country'] = getCacheList( "country" );
$cache['id_type'] = getCacheList( "id_type" );
$cache['gender'] = getCacheList( "gender" );
$cache['marital_status'] = getCacheList( "marital_status" );
$cache['caste'] = getCacheList( "caste" );
$cache['facility'] = getCacheList( "facility" );
$cache['facility_type'] = getCacheList( "facility_type" );
$cache['salary_grade'] = getCacheList( "salary_grade" );
$cache['position_type'] = getCacheList( "position_type" );
$cache['classification'] = getCacheList( "classification" );
$cache['job'] = getCacheList( "job" );
$cache['department'] = getCacheList( "department" );
$cache['language'] = getCacheList( "language" );
$cache['degree'] = getCacheList( "degree" );
$cache['edu_type'] = getCacheList( "edu_type" );
$cache['domain_of_study'] = getCacheList( "domain_of_study" );
$cache['specialty'] = getCacheList( "specialty" );
$cache['asha_training'] = getCacheList( "asha_training" );


foreach( $dictionary as $form => $data ) {
    $dictionary[$form] = array_change_key_case( $data, CASE_UPPER );
}

$p_code = 0;
$skip_no_desig = 0;

fseek( $fh, 0 );
$row = 0;

while ( ( $data = fgetcsv( $fh ) ) !== false ) {
    array_walk( $data, "dotrim" );
    $row++;

    $errors = 0;

    $errors += validate_field( $data[iHRIS_DESIGNATION], "job", $row, "Designation" );
    validate_field( $data[iHRIS_JOIN_DESIGNATION], "job", $row, "Join Designation", true );
    validate_field( $data[iHRIS_DEPUTATION_DESIGNATION], "job", $row, "Deputation Designation", true );
    validate_field( $data[iHRIS_GENDER], "gender", $row, "Gender", true );
    validate_field( $data[iHRIS_CASTE], "caste", $row, "Caste", true );
    $errors += validate_field( $data[iHRIS_FACILITY_NAME], "facility", $row, "Facility" );
    validate_field( $data[iHRIS_DEPUTATION_FACILITY_NAME], "facility", $row, "Deputation Facility", true );
    validate_field( $data[iHRIS_PERSON_TITLE], "person_title", $row, "Person Title", true );
    validate_field( $data[iHRIS_MARITAL_STATUS], "marital_status", $row, "Marital Status", true );
    validate_location( "Residence", $row,
            $data[iHRIS_RESIDENCE_STATE], 
            $data[iHRIS_RESIDENCE_DISTRICT], 
            $data[iHRIS_RESIDENCE_BLOCK] );
    validate_location( "Birth", $row,
            $data[iHRIS_BIRTH_STATE],
            $data[iHRIS_BIRTH_DISTRICT],
            $data[iHRIS_BIRTH_BLOCK] );
    validate_location( "Facility", $row,
            iHRIS_DEFAULT_STATE,
            $data[iHRIS_FACILITY_DISTRICT],
            $data[iHRIS_FACILITY_BLOCK] );
    validate_location( "Deputation Facility", $row,
            iHRIS_DEFAULT_STATE,
            $data[iHRIS_DEPUTATION_DISTRICT],
            $data[iHRIS_DEPUTATION_BLOCK] );

    if ( $errors > 0 ) {
        echo "Couldn't do anything for " . $data[iHRIS_SURNAME] . " " 
            . $data[iHRIS_FIRST_NAME] . " because of invalid data!\n";
        fputcsv( $bh, $data );
        $skip_no_desig++;
        continue;
    }


    /* 
     * Create the person form and save it.
     */
    $person = $form_factory->createContainer( "person" );

    $title_id = find_or_create( $data[iHRIS_PERSON_TITLE], "person_title", false, false, false, true );
    $person->getField('person_title')->setFromDB( $title_id );
    $person->firstname = ucwords( strtolower( $data[iHRIS_FIRST_NAME] ) );
    //$person->othername = $data[iHRIS_MIDDLE_NAME];
    $person->surname = ucwords( strtolower( $data[iHRIS_SURNAME] ) );
    if ( !$data[iHRIS_NATIONALITY] ) {
        $data[iHRIS_NATIONALITY] = iHRIS_DEFAULT_COUNTRY;
    }
    $nationality_id = find_or_create( $data[iHRIS_NATIONALITY], "country", false, false, false, true );
    $person->getField('nationality')->setFromDB( $nationality_id );
    $person->residence_thana = $data[iHRIS_RESIDENCE_THANA];
    $person->residence_village = $data[iHRIS_RESIDENCE_VILLAGE];

    if ( !$data[iHRIS_RESIDENCE_STATE] ) {
        $data[iHRIS_RESIDENCE_STATE] = iHRIS_DEFAULT_STATE;
    }
    $country_id = find_or_create( iHRIS_DEFAULT_COUNTRY, "country", false, false, false, true );
    $region_id = find_or_create( 
            $data[iHRIS_RESIDENCE_STATE] . ", " . iHRIS_DEFAULT_COUNTRY, 
            "region",
            array( 'name' => $data[iHRIS_RESIDENCE_STATE],
                'country' => $country_id ), false, false, true );
    if ( $data[iHRIS_RESIDENCE_DISTRICT] ) {
        $district_id = find_or_create(
                $data[iHRIS_RESIDENCE_DISTRICT] . ", " 
                . $data[iHRIS_RESIDENCE_STATE] . ", " . iHRIS_DEFAULT_COUNTRY, 
                "district",
                array( 'name' => $data[iHRIS_RESIDENCE_DISTRICT],
                    'region' => $region_id ), false, false, true );
        if ( $data[iHRIS_RESIDENCE_BLOCK] ) {
            $county_id = find_or_create(
                    $data[iHRIS_RESIDENCE_BLOCK] . ", " 
                    . $data[iHRIS_RESIDENCE_DISTRICT] . ", " 
                    . $data[iHRIS_RESIDENCE_STATE] . ", " 
                    . iHRIS_DEFAULT_COUNTRY, 
                    "county",
                    array( 'name' => $data[iHRIS_RESIDENCE_BLOCK],
                        'district' => $district_id ), false, false, true );
            $person->getField('residence')->setFromDB( $county_id );
        } else {
            $person->getField('residence')->setFromDB( $district_id );
        }
    }

    $person->save( $user );
    echo "created row $row " . $person->getId() . "\n";


    /*
     * Create the person id forms and save them.
     */
    foreach ( $person_id_types as $csv_idx => $id_type ) {
        if( $data[$csv_idx] == '' ) {
            continue;
        }
        $id_type_id = find_or_create( $id_type, "id_type", false, true, false, true );
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

    if ( $data[iHRIS_DATE_OF_BIRTH_YEAR] ) {
        $demographic->getField('birth_date')->setFromDB( arrange_date3( 
                    $data[iHRIS_DATE_OF_BIRTH_DAY],
                    $data[iHRIS_DATE_OF_BIRTH_MONTH],
                    $data[iHRIS_DATE_OF_BIRTH_YEAR] ) );
    }
    if ( $data[iHRIS_GENDER] ) {
        $gender_id = find_or_create( $data[iHRIS_GENDER], "gender", false, false, false, true );
        $demographic->getField('gender')->setFromDB( $gender_id );
    }
    $demographic->birth_location_village = $data[iHRIS_BIRTH_VILLAGE];
    if ( !$data[iHRIS_BIRTH_STATE] ) {
        $data[iHRIS_BIRTH_STATE] = iHRIS_DEFAULT_STATE;
    }
    $region_id = find_or_create(
            $data[iHRIS_BIRTH_STATE] . ", "
            . iHRIS_DEFAULT_COUNTRY,
            "region",
            array( 'name' => $data[iHRIS_BIRTH_STATE],
                'country' => $country_id ), false, false, true );
    if ( $data[iHRIS_BIRTH_DISTRICT] ) {
        $district_id = find_or_create(
                $data[iHRIS_BIRTH_DISTRICT] . ", " 
                . $data[iHRIS_BIRTH_STATE] . ", " . iHRIS_DEFAULT_COUNTRY,
                "district",
                array( 'name' => $data[iHRIS_BIRTH_DISTRICT],
                    'region' => $region_id ), false, false, true );
        if ( $data[iHRIS_BIRTH_BLOCK] ) {
            $county_id = find_or_create(
                    $data[iHRIS_BIRTH_BLOCK] . ", " 
                    . $data[iHRIS_BIRTH_DISTRICT] . ", " 
                    . $data[iHRIS_BIRTH_STATE] . ", " 
                    . iHRIS_DEFAULT_COUNTRY, 
                    "county",
                    array( 'name' => $data[iHRIS_BIRTH_BLOCK],
                        'district' => $district_id ), false, false, true );
            $demographic->getField('birth_location')->setFromDB( $county_id );
        } else {
            $demographic->getField('birth_location')->setFromDB( $district_id );
        }
    }
    if ( $data[iHRIS_MARITAL_STATUS] ) {
        $marital_status_id = find_or_create( $data[iHRIS_MARITAL_STATUS], "marital_status", false, false, false, true );
        $demographic->getField('marital_status')->setFromDB( $marital_status_id );
    }
    if ( $data[iHRIS_DEPENDENTS] != '' ) {
        $demographic->dependents = $data[iHRIS_DEPENDENTS];
    }
    if ( $data[iHRIS_CASTE] ) {
        $caste_id = find_or_create( $data[iHRIS_CASTE], "caste", false, false, false, true );
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
    $contact->fax = $data[iHRIS_WORK_FAX];
    $contact->alt_telephone = $data[iHRIS_MOBILE_PHONE];
    $contact->email = $data[iHRIS_EMAIL_ADDRESS];
    $contact->save( $user );
    $contact->cleanup();
    unset( $contact );

    /*
     * Create the position form and save it.
     */
    // First get the facility details and create or find it.
    $facility_fields = array( 'name' => $data[iHRIS_FACILITY_NAME] );
    /*
    $region_id = find_or_create( iHRIS_DEFAULT_STATE . ", " .
            iHRIS_DEFAULT_COUNTRY,
            "region",
            array( 'name' => iHRIS_DEFAULT_STATE,
                'country' => iHRIS_DEFAULT_COUNTRY ) );
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
    $facility_fields['facility_type'] = find_or_create( $data[iHRIS_FACILITY_TYPE], "facility_type" );
    */

    $facility_id = find_or_create( $facility_fields['name'],
            "facility" );

    // Now we create the job (designation).

    //$salary_grade_id = find_or_create( $data[iHRIS_PAYSCALE], "salary_grade" );
    //$classification_id = find_or_create( $data[iHRIS_CLASSIFICATION], "classification" );
    $job_code = preg_replace( "/[^A-Z]/", "", $data[iHRIS_DESIGNATION] );
    $job_id = find_or_create( $data[iHRIS_DESIGNATION], "job",
            array( 'title' => $data[iHRIS_DESIGNATION],
                //'salary_grade' => $salary_grade_id,
                'code' => $job_code,
                //'classification' => $classification_id 
                ) );

    // Now we create the position.
    $p_code++;
    $post_code = sprintf( "%s-%05d", $job_code, $p_code );
    $position = $form_factory->createContainer( "position" );
    
    $position->code = $post_code;
    $position->getField('job')->setFromDB( $job_id );
    $department_id = find_or_create( $data[iHRIS_DEPARTMENT], "department",
            false, false, false, true );
    $position->title = $data[iHRIS_DESIGNATION];
    $position->getField('department')->setFromDB( $department_id );
    $position->getField('facility')->setFromDB( $facility_id );
    $position_type_id = find_or_create( $data[iHRIS_POSITION_TYPE], "position_type", false, false, false, true );
    $position->getField('pos_type')->setFromDB( $position_type_id );
    $position->getField('status')->setFromDB( 'position_status|closed' );

    $position->save( $user );

    // Now we assign the position to the person.
    $person_position = $form_factory->createContainer( "person_position" );
    $person_position->setParent( $person->getNameId() );

    $person_position->getField("position")->setFromDB( $position->getNameId() );
    $start_date = arrange_date3( $data[iHRIS_START_DATE_DAY],
            $data[iHRIS_START_DATE_MONTH],
            $data[iHRIS_START_DATE_YEAR] );
    $person_position->getField("start_date")->setFromDB( $start_date );

    $person_position->save( $user );

    $salary = $form_factory->createContainer( "salary" );
    $salary->setParent( $person_position->getNameId() );
    $salary->getField('start_date')->setFromDB( $start_date );
    $salary->getField('salary')->setFromDB( 'currency|1=' . $data[iHRIS_PAYSCALE] );

    $salary->save( $user );
    $salary->cleanup();
    unset( $salary );


    if ( strtolower( $data[iHRIS_ON_DEPUTATION] ) == "yes" 
            && $data[iHRIS_DEPUTATION_DESIGNATION] != "" ) {

        $facility_fields = array( 'name' => $data[iHRIS_DEPUTATION_FACILITY_NAME] );
        /*
        $region_id = find_or_create( iHRIS_DEFAULT_STATE . ", " .
                iHRIS_DEFAULT_COUNTRY,
                "region",
                array( 'name' => iHRIS_DEFAULT_STATE,
                    'country' => iHRIS_DEFAULT_COUNTRY ) );
        $district_id = find_or_create(
                $data[iHRIS_DEPUTATION_DISTRICT] . ", " 
                . iHRIS_DEFAULT_STATE . ", " . iHRIS_DEFAULT_COUNTRY,
                "district",
                array( 'name' => $data[iHRIS_DEPUTATION_DISTRICT],
                    'region' => $region_id ) );
        $county_id = find_or_create(
                $data[iHRIS_DEPUTATION_BLOCK] . ", " 
                . $data[iHRIS_DEPUTATION_DISTRICT] . ", " 
                . iHRIS_DEFAULT_STATE . ", " 
                . iHRIS_DEFAULT_COUNTRY, 
                "county",
                array( 'name' => $data[iHRIS_DEPUTATION_BLOCK],
                    'district' => $district_id ) );
        $facility_fields['location'] = $county_id;
        $facility_fields['facility_type'] = find_or_create( $data[iHRIS_DEPUTATION_FACILITY_TYPE], "facility_type" );
        */
    
        $facility_id = find_or_create( $facility_fields['name'],
                "facility", false, false, false, true );

        $dep_job_code = preg_replace("/[^A-Z]/", "", 
                $data[iHRIS_DEPUTATION_DESIGNATION] );
        $dep_job_id = find_or_create( $data[iHRIS_DEPUTATION_DESIGNATION], "job",
                array( 'title' => $data[iHRIS_DEPUTATION_DESIGNATION] 
                    ), false, false, true );

        $p_code++;
        $post_code = sprintf( "%s-%05d", $dep_job_code, $p_code );
        $dep_position = $form_factory->createContainer( "position" );
        $dep_position->code = $post_code;
        $dep_position->getField('job')->setFromDB( $dep_job_id );
        $dep_position->title = $data[iHRIS_DEPUTATION_DESIGNATION];
        //$dep_dept_id = find_or_create( $data[iHRIS_DEPUTATION_DEPARTMENT], "department" );
        //$dep_position->getField('department')->setFromDB( $dep_dept_id );
        $dep_position->getField('facility')->setFromDB( $facility_id );
        $dep_position->getField('status')->setFromDB( 'position_status|deputed_to' );

        $dep_position->save( $user );

        $position->getField('status')->setFromDB( 'position_status|deputed_from' );
        $position->save( $user );

        $deputation = $form_factory->createContainer( "deputation" );
        $deputation->setParent( $person_position->getNameId() );
        $deputation->getField('position')->setFromDB( $dep_position->getNameId() );
        $deputation->getField('start_date')->setFromDB( arrange_date3( 
                    $data[iHRIS_DEPUTATION_START_DATE_DAY],
                    $data[iHRIS_DEPUTATION_START_DATE_MONTH],
                    $data[iHRIS_DEPUTATION_START_DATE_YEAR] ) );
        
        $deputation->save( $user );

        $deputation->cleanup();
        unset( $deputation );

        $dep_position->cleanup();
        unset( $dep_position );
        
    }


    $person_position->cleanup();
    unset( $person_position );

    $position->cleanup();
    unset( $position );

    /*
     * Create the joining_job form and save it.
     */
    $joining_job = $form_factory->createContainer( "joining_job" );
    $joining_job->setParent( $person->getNameId() );

    $joining_job->getField("date_of_joining")->setFromDB( arrange_date3( 
                $data[iHRIS_JOIN_DATE_DAY],
                $data[iHRIS_JOIN_DATE_MONTH],
                $data[iHRIS_JOIN_DATE_YEAR] ) );
    $jj_job_id = find_or_create( $data[iHRIS_JOIN_DESIGNATION], "job",
            array( 'title' => $data[iHRIS_JOIN_DESIGNATION],
                'code' => preg_replace( "/[^A-Z]/", "", $data[iHRIS_JOIN_DESIGNATION] ),
                ), false, false, true );
    $joining_job->getField("job")->setFromDB( $jj_job_id );
    $joining_job->getField("retirement_date")->setFromDB( arrange_date3( 
                $data[iHRIS_RETIREMENT_DATE_DAY],
                $data[iHRIS_RETIREMENT_DATE_MONTH],
                $data[iHRIS_RETIREMENT_DATE_YEAR] ) );

    $joining_job->save( $user );
    $joining_job->cleanup();
    unset( $joining_job );

    if ($data[iHRIS_LANG_ENGLISH] == 'yes') {
        $language = $form_factory->createContainer( "person_language" );
        $language->setParent( $person->getNameId() );
    
        $language_id = find_or_create( 'English', "language" );                       
        $language->getField("language")->setFromDB( $language_id );
    
        $language->save( $user );
        $language->cleanup();
        unset( $language );
    }
    if ($data[iHRIS_LANG_HINDI] == 'yes') {
        $language = $form_factory->createContainer( "person_language" );
        $language->setParent( $person->getNameId() );
    
        $language_id = find_or_create( 'Hindi', "language" );                       
        $language->getField("language")->setFromDB( $language_id );
    
        $language->save( $user );
        $language->cleanup();
        unset( $language );
    }
    if ($data[iHRIS_LANG_OTHER] != '') {
        $langs = explode( ',', $data[iHRIS_LANG_OTHER] );
        foreach( $langs as $lang ) {
            $lang = trim( $lang );
            $language = $form_factory->createContainer( "person_language" );
            $language->setParent( $person->getNameId() );
        
            $language_id = find_or_create( $lang, "language",
                false, true ); 
            $language->getField("language")->setFromDB( $language_id );
        
            $language->save( $user );
            $language->cleanup();
            unset( $language );
        }
    }


    $education = $form_factory->createContainer( "education" );
    $education->setParent( $person->getNameId() );
    
    $degree_id = find_or_create( $data[iHRIS_ACADEMIC_QUALIFICATION], "degree",
         array( 'name' => $data[iHRIS_ACADEMIC_QUALIFICATION],
                'edu_type' => find_or_create( 'Others', 'edu_type' ),
         ), false, false, true );                       
        
    $education->getField("degree")->setFromDB( $degree_id );
    //$education->institution = $data[iHRIS_ACADEMIC_INSTITUTION];
    //$education->location = $data[iHRIS_ACADEMIC_DISTRICT] . ", " . $data[iHRIS_ACADEMIC_STATE];
    //$education->getField("year")->setFromDB( $data[iHRIS_ACADEMIC_YEAR]);
    $domain_id = find_or_create( $data[iHRIS_ACADEMIC_DOMAIN1], "domain_of_study", false, false, false, true );
        
    $education->getField("domain_of_study")->setFromDB( $domain_id );       

    $education->save( $user );
    $education->cleanup();
    unset( $education );

    if ( $data[iHRIS_ACADEMIC_DOMAIN2] != "" ) {
        $education = $form_factory->createContainer( "education" );
        $education->setParent( $person->getNameId() );
    
        $domain_id = find_or_create( $data[iHRIS_ACADEMIC_DOMAIN2], "domain_of_study", false, false, false, true );
        $education->getField("domain_of_study")->setFromDB( $domain_id );       

        $education->save( $user );
        $education->cleanup();
        unset( $education );
    }

    if ( $data[iHRIS_ACADEMIC_DOMAIN3] != "" ) {
        $education = $form_factory->createContainer( "education" );
        $education->setParent( $person->getNameId() );
    
        $domain_id = find_or_create( $data[iHRIS_ACADEMIC_DOMAIN3], "domain_of_study", false, false, false, true );
        $education->getField("domain_of_study")->setFromDB( $domain_id );       

        $education->save( $user );
        $education->cleanup();
        unset( $education );
    }

    if ( $data[iHRIS_SPECIALTY1] != "" ) {
        $education = $form_factory->createContainer( "education" );
        $education->setParent( $person->getNameId() );
    
        $specialty_id = find_or_create( $data[iHRIS_SPECIALTY1], "specialty", false, false, false, true );
        $education->getField("specialty")->setFromDB( $specialty_id );       
        //$education->getField("year")->setFromDB( $data[iHRIS_ACADEMIC_YEAR]);

        $education->save( $user );
        $education->cleanup();
        unset( $education );
    }

    if ( $data[iHRIS_SPECIALTY2] != "" ) {
        $education = $form_factory->createContainer( "education" );
        $education->setParent( $person->getNameId() );
    
        $specialty_id = find_or_create( $data[iHRIS_SPECIALTY2], "specialty", false, false, false, true );
        $education->getField("specialty")->setFromDB( $specialty_id );       
        //$education->getField("year")->setFromDB( $data[iHRIS_ACADEMIC_YEAR]);

        $education->save( $user );
        $education->cleanup();
        unset( $education );
    }

    if ( $data[iHRIS_SPECIALTY_IN_PROGRESS] != "" ) {
        $education = $form_factory->createContainer( "education" );
        $education->setParent( $person->getNameId() );
    
        $specialty_id = find_or_create( $data[iHRIS_SPECIALTY_IN_PROGRESS], "specialty", false, false, false, true );
        $education->getField("specialty")->setFromDB( $specialty_id );       

        $education->save( $user );
        $education->cleanup();
        unset( $education );
    }

    if ( $data[iHRIS_ASHA_TRAINING_STATUS] != '' ) {
        $asha_training = explode( ',', $data[iHRIS_ASHA_TRAINING_STATUS] );
        foreach( $asha_training as $module ) {
            $module = $module;
            $asha = $form_factory->createContainer( "asha" );
            $asha->setParent( $person->getNameId() );

            $training_id = find_or_create( $module, "asha_training", false, false, false, true );
            $asha->getField( "asha_training" )->setFromDB( $training_id );

            $asha->save( $user );
            $asha->cleanup();
            unset( $asha );
        }
    }

    if ( array_key_exists( iHRIS_NOTES, $data ) 
            && $data[iHRIS_NOTES] != "" ) {
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

echo "Skipped $skip_no_desig people because of invalid data (see $bad_filename).\n";

?>
