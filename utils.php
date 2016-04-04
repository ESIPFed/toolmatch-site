<?php 

///
/// Global Variables
///

//endpoint info
$param = "query";
$format = "application/sparql-results+json";
$endpoint = "http://localhost:8890/sparql?format=" . urlencode( $format ) ;
$dataset_graph_base = "" ;
$dataset_dir = "" ;
$tool_graph_base = "" ;
$tool_dir = "" ;

$debug = true ;
$debug_file = "/var/www/logs/toolmatch.log" ;
global $debug ;
global $debug_file ;

/*
 * to send debug information to /var/www/logs/toolmatch.log use the
 * following function
tm_debug( "This is a test" ) ;
 */

///
/// RDF globals
///
$rdf_beg = '<?xml version="1.0"?>
<!DOCTYPE rdf:RDF [
  <!ENTITY toolmatchi "http://toolmatch.esipfed.org/instances/">
  <!ENTITY toolmatch "http://toolmatch.esipfed.org/schema#">
  <!ENTITY xsd "http://www.w3.org/2001/XMLSchema#">
  <!ENTITY doap "http://usefulinc.com/ns/doap#">
   
]>
<rdf:RDF
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:doap="&doap;"
  xmlns:dcat="http://www.w3.org/ns/dcat#"
  xmlns:foaf="http://xmlns.com/foaf/0.1/"
  xmlns:dc="http://purl.org/dc/terms/"
  xmlns:owl="http://www.w3.org/2002/07/owl#"
  xmlns:toolmatch="&toolmatch;"
  xmlns:toolmatchi="&toolmatchi;"
  xmlns:gcmd="http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/"
  xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
  xmlns="&toolmatch;"
  xml:base="&toolmatch;">';

///
/// Utility Functions
///

/**
 * Builds a basic SPARQL query
 * @param string $query SPARQL query string
 * @return array an array of associative arrays containing the bindings
 */
function sparqlSelect( $query )
{
	global $endpoint ;
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL, $endpoint ) ;
	curl_setopt( $curl, CURLOPT_POST, true ) ;
	curl_setopt( $curl, CURLOPT_POSTFIELDS, getQueryData( $query ) ) ;
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ) ;
	$content = curl_exec( $curl ) ;
    $http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE ) ;
	curl_close( $curl ) ;
	return $content ;
}

/**
 * Builds a basic SPARQL query
 * @param string $query SPARQL query string
 * @param string $suffix other options to append to request
 * @return string a URL to make a SPARQL query request
 */
function getQueryData( $query, $suffix = '' )
{
	global $param ;
	return $param . '=' . urlencode( $query ) ;	
}

/**
 * Write the new rdf to the specified graph
 * @param string $rdf_file the file that the rdf/xml was written
 * @param string $rdf_graph the graph that the rdf is to be written to
 * @param bool $del_graph whether to delete the graph before adding the rdf
 */
function storeRDF( $rdf_file, $rdf_graph = '', $del_graph = true )
{
	#echo $rdf_graph;
	$loader = "/project/virtuoso/scripts/vload" ;
	$deleter = "/project/virtuoso/scripts/vdelete" ;
	$lockfile = "tmp/toolmatch.lck" ;
	$delcmd = $deleter . " " . $rdf_graph . " > /dev/null 2>&1" ;
	$loadcmd = $loader . " rdf " . $rdf_file . " " . $rdf_graph . " > /dev/null 2>&1" ;
	$try_lock = 0 ;

	/* get an exclusive lock on the lock file before trying to write to
	 * the triple store
	 */
	$lockhandle = fopen( $lockfile, "w+" ) ;
	while( !flock( $lockhandle, LOCK_EX ) && $try_lock < 3 )
	{
		$try_lock++ ;
		print( "tring again\n" ) ;
		// FIXME: yes I know, sleeping like this is bad
		sleep( 2 ) ;
	}

	/* we tried 3 times. If try_lock is 3 then we failed to get the lock
	 */
	if( $try_lock == 3 )
	{
		print( "coudn't get lock\n" ) ;
		flock( $lockhandle, LOCK_UN ) ;
		fclose( $lockhandle ) ;
		return false ;
	}

	if( $del_graph )
	{
		exec( $delcmd ) ;
	}

	exec( $loadcmd ) ;

	flock( $lockhandle, LOCK_UN ) ;
	fclose( $lockhandle ) ;
	return true ;
}

function deleteRDF($rdf_graph, $del_graph = true) 
{
	$deleter = "/project/virtuoso/scripts/vdelete" ;
	$delcmd = $deleter . " " . $rdf_graph . " > /dev/null 2>&1" ;
	if( $del_graph )
	{
		tm_debug( "Deleting the graph using $delcmd" ) ;
		exec( $delcmd ) ;
	}
	else
	{
		tm_debug( "NOT deleting the graph $rdf_graph" ) ;
	}
	return true;
}

function tm_debug( $msg )
{
	global $debug, $debug_file ;
	if( $debug )
	{
		error_log( $msg . "\n", 3, $debug_file ) ;
	}
}

function gcmd_curl($gcmd) {
	$username = 'ferrim2';
	$password = 'Toolmatch123';
	$url = 'http://gcmdservices.gsfc.nasa.gov/mws/dif/' . $gcmd;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
	$data = curl_exec($curl);
	curl_close($curl);
	return $data;
}

function doi_curl($doi) {
	$url = 'http://dx.doi.org/' . $doi;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
	$data = curl_exec($curl);
	curl_close($curl);
	return $data;
}


function produce_XML($raw_XML) {
    #libxml_use_internal_errors(true);
    try {
        $xml = new SimpleXMLElement($raw_XML);
    } catch (Exception $e) {
        $error_message = 'SimpleXMLElement threw an exception.';
        foreach(libxml_get_errors() as $error_line) {
            $error_message .= "\t" . $error_line->message;
        }
        trigger_error($error_message);
        return false;
    }
    return $xml;
}

// grab the specified binding from the specified file as a json object.
// Put the results into an array and return the array
function get_json_data($file, $binding) {
	$results = array();
	$content = file_get_contents('/var/www/results/'.$file);
	$data = json_decode($content, true);
	foreach($data['results']['bindings'] as $result) {
		$result = $result[$binding]['value'];
		array_push($results, $result);
	}
	sort($results);
	return $results;
}

if( !function_exists( 'json_last_error_msg' ) )
{
    function json_last_error_msg()
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                echo ' - No errors';
            break;
            case JSON_ERROR_DEPTH:
                echo ' - Maximum stack depth exceeded';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Underflow or the modes mismatch';
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Unexpected control character found';
            break;
            case JSON_ERROR_SYNTAX:
                echo ' - Syntax error, malformed JSON';
            break;
            case JSON_ERROR_UTF8:
                echo ' - Malformed UTF-8 characters, possibly
incorrectly
    encoded';
            break;
            default:
                echo ' - Unknown error';
            break;
        }
    }
}

