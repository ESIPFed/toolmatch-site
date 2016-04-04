<?php

$tool = htmlspecialchars($_GET["tool"]);
$data = htmlspecialchars($_GET["data"]);

//If given a tool, find matching data collections
if (isset($tool) && $tool != "") { 

	$gname = "http://toolmatch.esipfed.org/tools/graph/" . $tool ;
	
	try {
	
	
	}
	catch( Exception $e )
	{
		$msg = $e->getMessage() ;
		print( "$msg\n" ) ;
	}
	
	echo '<span class="page_title">Matching Data Collections
		    <span>Click on a data collection to see more info</span>
	      </span></br>';
		  
//If given a data collection, find matching tools	
} else if (isset($data) && $data != "") { 

	$gname = "http://toolmatch.esipfed.org/datasets/graph/" . $data ;
	$instance = "http://toolmatch.esipfed.org/instances/" . $data;
	
	echo '<span class="page_title">Matching Tools
		    <span>Click on a tool to see more info</span>
	      </span></br>';
	
	try {
		$query = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
			  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
			  PREFIX owl: <http://www.w3.org/2002/07/owl#>
			  PREFIX tw: <http://tw.rpi.edu/schema/>
			  PREFIX twi: <http://tw.rpi.edu/instances/>
			  PREFIX time: <http://www.w3.org/2006/time#>
			  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
			  PREFIX dc: <http://purl.org/dc/terms/>
			  PREFIX doap: <http://usefulinc.com/ns/doap#>
			  PREFIX tool: <http://toolmatch.esipfed.org/schema#>

			  SELECT ?tool ?label ?description ?page ?version ?image ?format ?server ?protocol
			  WHERE
			  {
				  ?tool a <http://toolmatch.esipfed.org/schema#Tool>.
				  ?tool rdfs:label ?label .
				  ?tool dc:description ?description .
				  ?tool doap:homepage ?page .
				  ?tool doap:release ?version .
				  ?tool tool:hasInputFormat ?format .
				  <' . $instance . '> tool:hasDataFormat ?format .
				  ?tool tool:canUseDataServer ?server .
				  <' . $instance . '> tool:isAccessedBy ?server .
				  ?tool tool:canUseAccessProtocol ?protocol .
				  OPTIONAL { ?tool foaf:depiction ?image . }
			  } ORDER BY ?label';
			  
		$content = sparqlSelect($query);
		$info = json_decode($content, true);	

		foreach($info['results']['bindings'] as $result) {
			$label = $result['label']['value'];
			$description = $result['description']['value'];
			$page = $result['page']['value'];
			$version = $result['version']['value'];
			$version = end(explode("_", $version));
			$image = $result['image']['value'];
			
			
			?>
			<div class="instance_row">
			  <?php #echo $tool; ?>
			  <a href="#" class="instance_title" onClick="showHideInstanceInfo('<?php echo $label; ?>')"> <?php echo $label; ?></a>
			  <div id="<?php echo $label; ?>" style="display:none;"><?php echo $description; ?><br/><br/>
			  <img id="<?php echo $image; ?>" src="<?php echo $image; ?>" title="<?php echo $image; ?>" width="150px"></br>
			  <strong>Version: </strong><?php echo $version; ?></br></br></div>
		    </div>
		<?php
		}	
	}
		
	catch( Exception $e )
	{
		$msg = $e->getMessage() ;
		print( "$msg\n" ) ;
	}
}
?>
<script>
function showHideInstanceInfo(div){
    var x = document.getElementById(div);
	
    if(x.style.display == 'none'){
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
	return true;
}
</script>