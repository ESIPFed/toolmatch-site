<span class="page_title">Tool List
	<span>Click on a tool to see more info. Click the corresponding icon to edit, delete, or find matching data collections.</span>
</span></br>
<?php
try
{
	//query for all info but servers
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

			  SELECT ?tool (str(?l) as ?label) ?env
			  WHERE
			  {
				  ?tool rdfs:subClassOf tool:Tool .
                  OPTIONAL { ?tool rdfs:label ?l . }
                  OPTIONAL { ?tool rdfs:subClassOf ?env . ?env rdfs:subClassOf tool:ToolEnvironment . }
			  }';
			
	$content = sparqlSelect($query);
	$data = json_decode($content, true);
	
	foreach($data['results']['bindings'] as $result) {
		$tool = $result['tool']['value'];
		$label = $result['label']['value'];

		//truncate and remove underscores from label
		$label_changed = str_replace("_"," ", $label);
		$label_limit = 100;
		if (strlen($label_changed) > $label_limit) {
			$label_short = substr($label_changed, 0, $label_limit);
			$label_changed = substr($label_short, 0, strrpos($label_short, ' ')) . "... "; 
		}
        $tool_encoded = urlencode( $tool ) ;
?>
		
		<div style="width:350px" class="instance_row">
			  <a href="http://toolmatch.esipfed.org/tool.php?uri=<?php echo $tool_encoded; ?>" class="instance_title"><?php echo $label_changed; ?></a>
			  <div style="float:right;"><a href="/match.php?tool=<?php echo $tool; ?>"><img src="/images/magnifying_glass.png" alt="Find Matching Data Collections" title="Find Matching Data Collections" height="24px" width="24px" /></a></div>
			  <div style="float:right;"><a href="/delete.php?tool=<?php echo $tool; ?>"><img src="/images/delete_icon.png" alt="Delete Tool" title="Delete Tool" height="24px" width="24px" onClick="return confirmDelete()" /></a></div>
			  <div style="float:right;"><a href="/toolform.php?uri=<?php echo $tool; ?>"><img src="/images/pencil-icon-128.png" alt="Edit Tool" title="Edit Tool" height="24px" width="24px" /></a></div>
		  </div>
	<?php }
} 
catch( Exception $e )
{
	$msg = $e->getMessage() ;
	print( "$msg\n" ) ;
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

function confirmDelete() {
		var x = window.confirm("Are you sure you want to delete this tool?");
		return x;
	}
</script>
