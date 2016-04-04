<?php

$instance = htmlspecialchars($_GET["uri"]);

//get data collection info from json files (formats, servers)
//uses function in utils.php
$formats_full = get_json_data('formats.json','format');
$formats_short = array();
foreach ($formats_full as $format) {
	$format = end(explode('/', $format));
	array_push($formats_short, $format);
}
$conventions_full = get_json_data('conventions.json','convention');
$conventions_short = array();
foreach ($conventions_full as $convention) {
	$convention = end(explode('/', $convention));
	array_push($conventions_short, $convention);
}
$servers_full = get_json_data('servers.json','server');
$servers_short = array();
foreach ($servers_full as $server) {
	$server = end(explode('/', $server));
	array_push($servers_short, $server);
}

//query for all dataset servers
$data_servers_selected = array();
$query = 'PREFIX data: <http://toolmatch.esipfed.org/schema#>
		  SELECT ?server
		  WHERE { OPTIONAL { <' . $instance . '> data:isAccessedBy ?server . } }';
$content = sparqlSelect($query);
$data_servers = json_decode($content, true);

foreach ($data_servers['results']['bindings'] as $result) {							
	$result = $result['server']['value'];
	array_push($data_servers_selected, $result);
}

if( isset( $instance ) && $instance != "" ) { 
	try {	
		//sparql query for individual tool (label, description, page, version, and image)
		$query = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
			  PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
			  PREFIX owl: <http://www.w3.org/2002/07/owl#>
			  PREFIX tw: <http://tw.rpi.edu/schema/>
			  PREFIX twi: <http://tw.rpi.edu/instances/>
			  PREFIX time: <http://www.w3.org/2006/time#>
			  PREFIX foaf: <http://xmlns.com/foaf/0.1/>
			  PREFIX dc: <http://purl.org/dc/terms/>
			  PREFIX doap: <http://usefulinc.com/ns/doap#>
			  PREFIX data: <http://toolmatch.esipfed.org/schema#>
			  PREFIX dcat: <http://www.w3.org/ns/dcat#>
			  PREFIX gcmd: <http://gcmd.gsfc.nasa.gov/Aboutus/xml/dif/>

			  SELECT ?label ?doi ?id ?url ?description ?format ?convention
			  WHERE
			  {
				  <' . $instance . '> a <http://toolmatch.esipfed.org/schema#DataCollection> .
				  <' . $instance . '> rdfs:label ?label .
				  OPTIONAL { <' . $instance . '> dc:identifier ?doi . }
				  OPTIONAL { <' . $instance . '> gcmd:Entry_ID ?id . }
		          OPTIONAL { <' . $instance . '> data:hasAccessURL ?url . }
				  OPTIONAL { <' . $instance . '> dcat:description ?description . }
				  OPTIONAL { <' . $instance . '> data:hasDataFormat ?format . }
			      OPTIONAL { <' . $instance . '> data:usesConvention ?convention . }
			  }';

		$content = sparqlSelect($query);
        if( $content == null )
        {
            echo "Was not able to retrieve information about the data collection<br/>" ;
            exit( 1 ) ;
        }

		$data_info = json_decode($content, true);
        if( $data_info == null )
        {
            echo "Problem translating information about data collection<br/>" ;
            $jerr = json_last_error_msg() ;
            echo "$jerr\n" ;
            exit( 1 ) ;
        }

		foreach($data_info['results']['bindings'] as $result) {
			$label = $result['label']['value'];
			$doi = $result['doi']['value'];
			$id = $result['id']['value'];
			$url = end(explode("#", $result['url']['value']));
			$description = $result['description']['value'];
			$format = end(explode("/", $result['format']['value']));
			$convention = end(explode("/", $result['convention']['value']));
		}
		
		$label_changed = str_replace("_"," ", $label);	
		
	} catch( Exception $e ) {
		$msg = $e->getMessage() ;
		print( "$msg\n" ) ;
	}
	
?>
	<form name="data_input" action="dataform_submit.php" method="post" onSubmit="return validate()">
		<span class="page_title">Data Collection Form<span>Please fill out all required fields. At least one of the following three must be provided (DOI, GCMD Entry ID, Access URL).</span></span></br>
		
		<?php
		//Name
		echo '<span class="red-star">*</span>Data Collection Name: </br>';
		if (!empty($label)) {
			echo '<textarea name="dataname" rows="2" style="width:100%;" required>' . $label_changed . '</textarea></br></br>';
		} else {
			echo '<textarea name="dataname" rows="2" style="width:100%;"placeholder="ex: California Biogeographic Information and Observation System (BIOS)" required></textarea></br></br>';
		}
		
		//DOI
		echo 'Data Collection DOI</br>';
		if (!empty($doi)) {
			echo '<textarea name="datadoi" rows="1" style="width:100%;">' . $doi . '</textarea></br></br>';
		} else {
			echo '<textarea name="datadoi" rows="1" style="width:100%;" placeholder="10.5067/AQUA/AIRS/DATA301"></textarea></br></br>';
		}
		
		//GCMD
		echo 'GCMD Entry ID</br>';
		if (!empty($id)) {
			echo '<textarea name="datagcmd" rows="1" style="width:100%;">' . $id . '</textarea></br></br>';
		} else {
			echo '<textarea name="datagcmd" rows="1" style="width:100%;" placeholder="GES_DISC_AIRX3STD_V006"></textarea></br></br>';
		}

		//Access URL
		echo 'Access URL</br>';
		if (!empty($url)) {
			echo '<textarea name="dataurl" rows="1" style="width:100%;">' . $url . '</textarea></br></br>';
		} else {
			echo '<textarea name="dataurl" rows="1" style="width:100%;" placeholder="http://acdisc.sci.gsfc.nasa.gov/opendap/Aqua_AIRS_Level3/AIRX3STD.006/"></textarea></br></br>';
		} 
		
		//Description
		echo '<span class="red-star">*</span>Data Collection Description: </br>';
		if (!empty($description)) {
			echo '<textarea name="datadesc" rows="4" style="width:100%;" required</textarea>' . $description . '</textarea></br></br>';
		} else {
			$placeholder = "ex: California Department of Fish and Game's central repository for biological observation and distribution info. Contains over 600 individual databases including the CA Natural Heritage Program data. Provides tools for querying and reporting. Useful for site specific project work.";
			echo '<textarea name="datadesc" rows="4" style="width:100%;" placeholder="' . $placeholder . '" required></textarea></br></br>';
		}?>
		
		
		<div>
			<span class="red-star">*</span>Collection Format	
			<?php
			$found = False;
			foreach($formats_full as $instance) {
				$name = end(explode('/', $instance));
				if ($format == $name) {
					echo '<input type="text" name="dataformat" id="formats" value="' . $format . '" required/>';
					$found = True;
				}
			}
			if ($found == False) {
				echo '<input type="text" name="dataformat" id="formats" placeholder="ex: HDF5" required/>';
			}
			?> 
		</div></br>

		<div>
			Collection Convention
			<?php
			$found = False;
			foreach($conventions_full as $instance) {
				$name = end(explode('/', $instance));
				if ($convention == $name) {
					echo '<input type="text" name="dataconv" id="conventions" value="' . $convention . '"/>';
					$found = True;
				}
			}
			if ($found == False) {
				echo '<input type="text" name="dataconv" id="conventions" placeholder="ex: ClimateForecast_CF"/>';
			}
			?>
		</div></br>
		
		Server Accessibility <span style="font-size:11px;">(select all that apply):</span></br>
		<select multiple name="dataserver[]" >
			<?php 
			foreach ($servers_full as $instance) {
				$name = end(explode('/', $instance));
				if (in_array($instance, $data_servers_selected)) {
					echo '<option  value="' . $instance . '" selected>' . $name . '</option>';
				} else {
					echo '<option  value="' . $instance . '">' . $name . '</option>';
				}
			} ?>
		</select></br></br>

		<input type="hidden" name="status" value="edit">
		<input class="button" type="submit" value="Update Data Collection" name="submit" style="display:inline-block;">
		<a class="button" href="/dataform_init.php" style="display:inline-block;">Change Identifier</a>
	</form>
		
		<p style="font-size:10pt;margin-left:5px;">Note: <span class="red-star">*</span> Starred fields are required. </p>
<?php
} elseif(isset($_POST['submit'])) {
	//variables for form data
	$datadoi = $_POST['datadoi'];
	$datagcmd = $_POST['datagcmd'];
	$dataurl = $_POST['dataurl'];
	$doi_curl = $datadoi;

	//performs curl for gcmd (utils.php)
	if (!empty($datagcmd)) {
		$data = gcmd_curl($datagcmd);
		$xml = produce_XML($data);
	}
	
	if (!empty($xml)) {
		$datagcmd = $xml->Entry_ID;
		$dataname = $xml->Entry_Title;
		$datadoi = $xml->Data_Set_Citation->Dataset_DOI;
		$my = $xml->xpath('/DIF/Entry_Title');
		foreach ($xml as $entry) {
			if ((string) $entry->URL_Content_Type->Subtype == 'OPENDAP DIRECTORY (DODS)') {
				$dataurl = $entry->URL;
			}
		}
		$datadesc = $xml->Summary->Abstract;
	}
	
	if (!empty($doi_curl)) {
		$data = doi_curl($doi_curl);
		$xml = produce_XML($data);
		$link = $xml->BODY->A;
	}

	
?>
<form name="data_input" action="dataform_submit.php" method="post" onSubmit="return validate()">
	<span class="page_title">Data Collection Form<span>Please fill out all required fields. Note: At least one data collection identifier is required.</span></span></br>
	
	<?php
	if (!empty($link)) { ?>
		Data Collection Landing Page</br>
		<iframe style="margin-bottom:15px;" height="500x" width="100%" src="<?php echo $link; ?>"></iframe>
	<?php }
	
	//If doi was entered, display it, blank field otherwise
	if (!empty($dataname)) { ?>
		<span class="red-star">*</span>Data Collection Name: </br>
		<textarea name="dataname" rows="1" style="width:100%;" required><?php echo $dataname; ?></textarea></br></br>
	<?php } else { ?>
		<span class="red-star">*</span>Data Collection Name: </br>
		<textarea name="dataname" rows="2" style="width:100%;"placeholder="ex: California Biogeographic Information and Observation System (BIOS)" required></textarea></br></br>
	<?php } ?>

	<?php
	//If doi was entered, display it, blank field otherwise
	if (!empty($datadoi)) { ?>
		Data Collection DOI</br>
		<textarea name="datadoi" rows="1" style="width:100%;"><?php echo $datadoi; ?></textarea></br></br>
	<?php } else { ?>
		Data Collection DOI</br>
		<textarea name="datadoi" rows="1" style="width:100%;" placeholder="10.5067/AQUA/AIRS/DATA301"></textarea></br></br>
	<?php } ?>
	
	<?php
	//If gcmd was entered, display it, blank field otherwise
	if (!empty($datagcmd)) { ?>
		GCMD</br>
		<textarea name="datagcmd" rows="1" style="width:100%;"><?php echo $datagcmd; ?></textarea></br></br>
	<?php } else { ?>
		GCMD</br>
		<textarea name="datagcmd" rows="1" style="width:100%;" placeholder="GES_DISC_AIRX3STD_V006"></textarea></br></br>
	<?php } ?>
	
	<?php
	//If url was entered, display it, blank field otherwise
	if (!empty($dataurl)) { ?>
		Access URL</br>
		<textarea name="dataurl" rows="1" style="width:100%;"><?php echo $dataurl; ?></textarea></br></br>
	<?php } else { ?>
		Access URL</br>
		<textarea name="dataurl" rows="1" style="width:100%;" placeholder="ex: http://acdisc.sci.gsfc.nasa.gov/opendap/Aqua_AIRS_Level3/AIRX3STD.006/"></textarea></br></br>
	<?php } ?>
	
	<?php
	//If url was entered, display it, blank field otherwise
	if (!empty($datadesc)) { ?>
		<span class="red-star">*</span>Data Collection Description: </br>
		<textarea name="datadesc" rows="4" style="width:100%;" required><?php echo $datadesc; ?></textarea></br></br>
	<?php } else { ?>
		<span class="red-star">*</span>Data Collection Description: </br>
		<textarea name="datadesc" rows="4" style="width:100%;" placeholder="ex: California Department of Fish and Game's central repository for biological observation and distribution info. Contains over 600 individual databases including the CA Natural Heritage Program data. Provides tools for querying and reporting. Useful for site specific project work." required></textarea></br></br>
	<?php } ?>	
	
	<div>
		<span class="red-star">*</span>Collection Format	
		<input type="text" name="dataformat" id="formats" placeholder="ex: HDF5" required/>
	</div></br>

	<div>
		Collection Convention
		<input type="text" name="dataconv" id="conventions" placeholder="ex: ClimateForecast_CF"/>
	</div></br>
	
	<span class="red-star">*</span>Server Accessibility <span style="font-size:11px;">(select all that apply):</span></br>
	<select multiple name="dataserver[]" required >
			<?php 
			foreach ($servers_full as $instance) {
				$name = end(explode('/', $instance));
				echo '<option  value="' . $instance . '">' . $name . '</option>';
			} ?>
	</select></br></br>
	
	<input type="hidden" name="status" value="add">
		
	<input class="button" type="submit" value="Add Data Collection" name="submit" style="display:inline-block;">
	<a class="button" href="/dataform_init.php" style="display:inline-block;">Change Identifier</a>
</form>
	
	<p style="font-size:10pt;margin-left:5px;">Note: <span class="red-star">*</span> Starred fields are required. </p>
	
<?php
}
?>
<script src='scripts/jquery.autocomplete.js'></script>
<script>
function validate() {
	//grab values of 3 fields
	var doi = document.forms["data_input"]["datadoi"].value;
    var gcmd = document.forms["data_input"]["datagcmd"].value;
    var url = document.forms["data_input"]["dataurl"].value;

	if ((doi == null || doi == "") && (gcmd == null || gcmd == "") && (url == null || url == "")) {
		alert("Please fill out at least one of the data collection ID fields.");
		return false;
	}
}
function showHideInfo(div){
    var x = document.getElementById(div);
	
    if(x.style.display == 'none'){
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
	return true;
}

var formats = <?php echo json_encode($formats_short); ?>;
var conventions = <?php echo json_encode($conventions_short); ?>;

$('#formats').autocomplete({
    lookup: formats,
});

$('#conventions').autocomplete({
    lookup: conventions,
});
</script>
