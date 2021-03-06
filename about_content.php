<h2 class="page_title">About ToolMatch</h2>
<?php
try
{
	include_once( "twsparql/TWSparqlHTML.inc" ) ;
    TWSparqlHTML::init( "twsparql.conf" ) ;

	$query = "https://tw.rpi.edu/queries/project.rq" ;
	$xslt = "https://tw.rpi.edu/xslt/generate/project-embed.xsl" ;
	$uri = "http://tw.rpi.edu/instances/project/ToolMatch" ;
	$endpoint="http://tw.rpi.edu/endpoint/books" ;

	$sparql = "<sparql endpoint=\"$endpoint\" query=\"$query\" xslt=\"$xslt\" uri=\"$uri\"/>" ;

	$contents = TWSparql::getEngine()->render( 0, $sparql ) ;

	print( "$contents" ) ;
	print( "<br/><br/>\n" ) ;
	print( "Project Page: <a href=\"http://wiki.esipfed.org/index.php/ToolMatch\" target=\"_blank\">http://wiki.esipfed.org/index.php/ToolMatch</a>\n" ) ;
}
catch( Exception $e )
{
	$msg = $e->getMessage() ;
	print( "$msg\n" ) ;
}

?>

