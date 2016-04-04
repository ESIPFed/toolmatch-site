<?php

try {
	if(isset($_POST['submit'])) {
		//variables for form data
		$dataname = trim($_POST['dataname']);
		$dataname = preg_replace("~[\W]~","_", $dataname);
		$datadesc = trim($_POST['datadesc']);
		$dataformat = $_POST['dataformat'];
		$dataconv = $_POST['dataconv'];
		$dataservers = $_POST['dataserver'];
						
		$datadoi = trim($_POST['datadoi']);
		$datagcmd = trim($_POST['datagcmd']);
		$dataurl = trim($_POST['dataurl']);
		
		//status is either add or edit
		$status = $_POST['status'];
		
		//rdf for data collection title
		$rdf_dataname = '<toolmatch:DataCollection rdf:about="&toolmatchi;' . $dataname . '">
  <rdfs:label rdf:datatype="http://www.w3.org/2001/XMLSchema#string">' . $dataname . '</rdfs:label>
  <dcat:title rdf:datatype="http://www.w3.org/2001/XMLSchema#string">' . $dataname . '</dcat:title>';
  
		//rdf for DOI
		$rdf_datadoi = '';
		if (!empty($datadoi)) {
			$rdf_datadoi = '
			<dc:identifier rdf:parseType="Literal">' . $datadoi . '</dc:identifier>';
		}
		
		//rdf for GCMD
		$rdf_datagcmd = '';
		if (!empty($datagcmd)) {
			$rdf_datagcmd = '
			<gcmd:Entry_ID rdf:parseType="Literal">'.$datagcmd.'</gcmd:Entry_ID>';
		}
		
		//rdf for access url
		$rdf_dataurl = '';
		if (!empty($dataurl)) {
			$rdf_dataurl = '
			<toolmatch:hasAccessURL rdf:resource="' . $dataurl . '"/>';
		}
		
		//rdf for description (optional)
		$rdf_datadesc = '';
		if (!empty($datadesc)) {
			$rdf_datadesc = '
			<dcat:description rdf:parseType="Literal">' . $datadesc . '</dcat:description>';
		}
		
		//rdf for data format (optional)
		$rdf_dataformat = '';
		if (!empty($dataformat)) {
			$rdf_dataformat = '
			<toolmatch:hasDataFormat rdf:resource="' . $dataformat . '"/>';
		}
		
		//rdf for data convention (optional)
		$rdf_dataconv = '';
		if (!empty($dataconv)) {
			$rdf_dataconv = '
			<toolmatch:usesConvention rdf:resource="' . $dataconv . '"/>';
		}
		
		//rdf for data server(s) (optional)
		$rdf_dataservers = '';
		if (!empty($dataservers)) {
			foreach ($dataservers as $server) {
				$rdf_dataservers = $rdf_dataservers . '
    <toolmatch:isAccessedBy rdf:resource="' . $server . '"/>';
			}
		}
				
		//end of rdf
		$rdf_end = '
		</toolmatch:DataCollection>
</rdf:RDF>';
		
		//concatenates rdf together into one string
		$rdf = $rdf_beg . $rdf_dataname . $rdf_datadoi . $rdf_datagcmd . $rdf_dataurl. $rdf_datadesc . $rdf_dataformat . $rdf_dataconv . $rdf_dataservers . $rdf_end;
		
		//creates RDF file name (dataname or dataid?)
		$fname = $dataname . ".rdf";
		$gname = "http://toolmatch.esipfed.org/datasets/graph/" . $dataname ;

		//creates new RDF file, writes to it, and closes it
		$new_file = '/project/toolmatch/toolmatch-svn/trunk/data/added_datasets/'. $fname;
		$file = fopen($new_file,"w+");
		$fwrite = fwrite($file, $rdf);
		if ($fwrite === false) {
            throw new RuntimeException('<p class="failure">Submission failed! Please try again.</p>');
        }
		fclose($file);
		
		//store the rdf in the triple store
		$stored = storeRDF( $new_file, $gname, true );
		if ($stored == false) {
			throw new RuntimeException('<p class="failure">Submission failed! Please try again.</p>');
		}
		
		echo '<div class="submission_response">';
		if ($status == 'add') {
			echo '<a class="instance_title" href="#" onClick="showHideInstanceInfo(\'instances\')">' . $dataname . ' has been successfully added! Click to see matching tools. <img class="check" src="/images/success.png" alt="Success" height="30px" width="30px"></a>';
		} else if ($status == 'edit') {
			echo '<a class="instance_title" href="#" onClick="showHideInstanceInfo(\'instances\')">' . $dataname . ' has been successfully updated! Click to see matching tools. <img class="check" src="/images/success.png" alt="Success" height="30px" width="30px"></a>';
		}

		echo '</br></br></div>';

		echo '<div id="instances" style="display:none;"><span class="page_title">Matching Tools
		    <span>Click on a tool to see more info</span>
	      </span></br>';
		  
		try {
			$instance = "http://toolmatch.esipfed.org/instances/" . $dataname ;
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

				  SELECT ?tool ?label ?description ?page ?version ?image ?format
				  WHERE
				  {
					  ?tool a <http://toolmatch.esipfed.org/schema#Tool>.
					  ?tool rdfs:label ?label .
					  ?tool dc:description ?description .
					  ?tool doap:homepage ?page .
					  ?tool doap:release ?version .
					  ?tool tool:hasInputFormat ?format .
					  <' . $instance . '> tool:hasDataFormat ?format .
					  OPTIONAL { ?tool foaf:depiction ?image . }
				  } ORDER BY ?label';
				  
			$content = sparqlSelect($query);
			$info = json_decode($content, true);
			#var_dump($info);
			

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
			echo '</div>';
		}
		
		catch( Exception $e )
		{
			$msg = $e->getMessage() ;
			print( "$msg\n" ) ;
		}
		
		echo '<div class="submission_response"><div class="button_div">
				<form action="dataform_init.php" style="display:inline-block;">
					<input class="success_button" type="submit" value="Add Another Data Collection" >
				</form>';
		echo '</div></div>';
		
	}
} catch(RuntimeException $e) {

    echo $e->getMessage();
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

