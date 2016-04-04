<h2 class="page_title">Presentations</h2>
<?php
try
{
	include_once( "twsparql/TWSparqlHTML.inc" ) ;
    TWSparqlHTML::init( "twsparql.conf" ) ;

	$query = "https://tw.rpi.edu/queries/project-presentations.rq" ;
	$xslt = "https://tw.rpi.edu/xslt/presentation-list.xsl" ;
	$uri = "http://tw.rpi.edu/instances/project/ToolMatch" ;
	$endpoint="http://tw.rpi.edu/endpoint/books" ;

	$sparql = "<sparql endpoint=\"$endpoint\" query=\"$query\" xslt=\"$xslt\" uri=\"$uri\"/>" ;

	$contents = TWSparql::getEngine()->render( 0, $sparql ) ;

	print( "$contents" ) ;
}
catch( Exception $e )
{
	$msg = $e->getMessage() ;
	print( "$msg\n" ) ;
}

?>
