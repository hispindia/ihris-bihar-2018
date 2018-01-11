<?php

$version = "4.1.7.0";

$doc = new DOMDocument();
$doc->load($argv[2]);
$xpath = new DOMXPath( $doc );

$csv = fopen( $argv[1], "r" );

while( $data = fgetcsv( $csv ) ) {
    $results = $xpath->query( "//configurationGroup[@name='" . $data[0] . "']/configurationGroup[@name='fields']/configuration[@name='facility_type']/value" );
    if( $results->length > 0 ) {
        $results->item(0)->nodeValue = $data[1];
        $vers_check = $xpath->query( "./version", $results->item(0)->parentNode );
        fwrite( STDERR, "Updating facility_type to $data[0].!\n" );
        if ( $vers_check->length > 0 ) {
            $vers_check->item(0)->nodeValue = $version;
        } else {
            $vers = $doc->createElement("version", $version);
            $results->item(0)->parentNode->insertBefore( $vers, $results->item(0) );
        }
    } else {
        $results = $xpath->query( "//configurationGroup[@name='" . $data[0] . "']/configurationGroup[@name='fields']" );
        if ( $results->length > 0 ) {
            $fac = $doc->createElement("configuration");
            $fac->setAttribute("name", "facility_type");
            $val = $doc->createElement("value", $data[1]);
            $vers = $doc->createElement("version", $version);
            $fac->appendChild($vers);
            $fac->appendChild( $doc->createTextNode("\n") );
            $fac->appendChild($val);
            $results->item(0)->appendChild($fac);
            fwrite( STDERR, "Adding facility_type to $data[0].\n" );
        } else {
            fwrite( STDERR, "Couldn't find $data[0]!\n" );
        }
    }
}

echo $doc->saveXML();

?>
