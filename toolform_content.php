<?php

$instance = htmlspecialchars($_GET["uri"]);

//get tool info from json files (formats, servers, protocols, capabilities, tooltypes)
//uses utils.php
$formats_full = get_json_data('formats.json','format');
$servers_full = get_json_data('servers.json','server');
$protocols_full = get_json_data('protocols.json','protocol');
$capabilities_full = get_json_data('capabilities.json','capability');
$tooltypes_full = get_json_data('types.json','type');

if( isset( $instance ) && $instance != "" ) { 
	try {
		//sparql query for individual tool (label, description, page, version, and image)
		$query_main = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
				PREFIX foaf: <http://xmlns.com/foaf/0.1/>
				PREFIX dc: <http://purl.org/dc/terms/>
				PREFIX doap: <http://usefulinc.com/ns/doap#>
				PREFIX tool: <http://toolmatch.esipfed.org/schema#>

				SELECT ?label ?description ?page ?version ?image ?type ?capability
				WHERE
				{
					<' . $instance . '> rdfs:label ?label .
					<' . $instance . '> dc:description ?description .
					<' . $instance . '> doap:homepage ?page .
					<' . $instance . '> doap:release ?version .
					OPTIONAL { <' . $instance . '> foaf:depiction ?image . }
				}';

		$content_main = sparqlSelect($query_main);
		$data_main = json_decode($content_main, true);
		
		//retrieve values for form
		foreach($data_main['results']['bindings'] as $item) {
			$toolname_val = $item['label']['value'];
			$toolpage_val = $item['page']['value'];
			$tooldesc_val = $item['description']['value'];
			$toolvers_val = explode("_", $item['version']['value']);
			$toolvers_val = end($toolvers_val);
			$toollogo_val = $item['image']['value'];
		}
		
		/* TOOL INPUT FORMATS */
		//query for all input formats for given tool
		$tool_inputs_selected = array();
		$query = 'PREFIX tool: <http://toolmatch.esipfed.org/schema#>
				  SELECT ?input
				  WHERE { OPTIONAL { <' . $instance . '> tool:hasInputFormat ?input . } }';
		$content = sparqlSelect($query);
		$data = json_decode($content, true);
		foreach ($data['results']['bindings'] as $result) {							
			$result = $result['input']['value'];
			array_push($tool_inputs_selected, $result);
		}
		
		/* TOOL OUTPUT FORMATS */		
		//query for all output formats for given tool
		$tool_outputs_selected = array();
		$query = 'PREFIX tool: <http://toolmatch.esipfed.org/schema#>
				  SELECT ?output
				  WHERE { OPTIONAL { <' . $instance . '> tool:hasOutputFormat ?output . } }';
		$content = sparqlSelect($query);
		$data = json_decode($content, true);
		foreach ($data['results']['bindings'] as $result) {							
			$result = $result['output']['value'];
			array_push($tool_outputs_selected, $result);
		}
		
		/* TOOL SERVERS*/		
		//query for all servers for given tool
		$tool_servers_selected = array();
		$query = 'PREFIX tool: <http://toolmatch.esipfed.org/schema#>
				  SELECT ?server
				  WHERE { OPTIONAL { <' . $instance . '> tool:canUseDataServer ?server . } }';
		$content = sparqlSelect($query);
		$data = json_decode($content, true);
		foreach ($data['results']['bindings'] as $result) {							
			$result = $result['server']['value'];
			array_push($tool_servers_selected, $result);
		}
		
		/* TOOl ACCESS PROTOCOLS*/		
		//query for all servers for given tool
		$tool_protocols_selected = array();
		$query = 'PREFIX tool: <http://toolmatch.esipfed.org/schema#>
				  SELECT ?protocol
				  WHERE { OPTIONAL { <' . $instance . '> tool:canUseAccessProtocol ?protocol . } }';
		$content = sparqlSelect($query);
		$data = json_decode($content, true);
		foreach ($data['results']['bindings'] as $result) {							
			$result = $result['protocol']['value'];
			array_push($tool_protocols_selected, $result);
		}
		
		/* TOOL CAPABILITIES */
		//query for all capabilities for given tool
		$tool_capabilities_selected = array();
		$query = 'PREFIX tool: <http://toolmatch.esipfed.org/schema#>
				  SELECT ?capability
				  WHERE { OPTIONAL { <' . $instance . '> tool:hasCapability ?capability . } }';
		$content = sparqlSelect($query);
		$data = json_decode($content, true);
		foreach ($data['results']['bindings'] as $result) {							
			$result = $result['capability']['value'];
			array_push($tool_capabilities_selected, $result);
		}
		
		/* TOOL TYPES */
		//query for all types for a given tool
		$tool_types_selected = array();
		$query = 'PREFIX tool: <http://toolmatch.esipfed.org/schema#>
				  SELECT ?type
				  WHERE { OPTIONAL { <' . $instance . '> tool:isOfType ?type . } }';
		$content = sparqlSelect($query);
		$data = json_decode($content, true);
		foreach ($data['results']['bindings'] as $result) {							
			$result = $result['type']['value'];
			array_push($tool_types_selected, $result);
		}
		
		
	} catch( Exception $e ) {
		$msg = $e->getMessage() ;
		print( "$msg\n" ) ;
	}
?>
	<form style="display:block" name="tool_input" action="toolform_submit" method="post" enctype="multipart/form-data">
		<span class="page_title">Tool Form<span>Please fill out all required fields.</span></span></br>
		
		<span class="red-star">*</span>Tool Name: </br>
		<textarea name="toolname" rows="1" style="width:100%;" required><?php echo $toolname_val; ?></textarea></br></br>
		
		<span class="red-star">*</span>Tool Page: </br>
		<textarea name="toolpage" rows="1" style="width:100%;" required><?php echo $toolpage_val; ?></textarea></br></br>
		
		<span class="red-star">*</span>Tool Description: </br>
		<textarea name="tooldesc" rows="4" style="width:100%;" required ><?php echo $tooldesc_val;?></textarea></br></br>
		
		<?php if (!empty($toollogo_val)) { ?>
			Current Tool Logo: </br>
			<img id="toollogo" src="<?php echo $toollogo_val; ?>" title="<?php echo $toolname_val; ?>" width="150px">
			<input type="hidden" name="cur_toollogo" value="<?php echo $toollogo_val; ?>">
			<br/><br/>
		<?php } ?>
		Upload New Tool Logo: </br>
		<input type="file" id="toollogo" name="toollogo" accept="image/*" onchange="showImg(this);"></br></br>
		<img id="preview" src="#" alt="your image" style="width:150px;display:none;"/></br></br>		
		
		<span class="red-star">*</span>Tool Version: </br>
		<textarea name="toolvers" rows="1" style="width:100%;" required><?php echo $toolvers_val; ?></textarea></br></br>
		
		<div>
			<div style="display:inline-block;">
				Input Format <span style="font-size:11px";>(select all that apply):</span></br>		
				<select multiple name="toolinput[]" style="height:100px;width:125px;" >
				<?php
					foreach ($formats_full as $instance) {
						$val = explode('/', $instance);
						$val = end($val);
						if (in_array($instance, $tool_inputs_selected)) {
							echo '<option value="' . $instance . '" selected>' . $val . '</option>';
						} else {
							echo '<option value="' . $instance . '">' . $val . '</option>';
						}
					}
				?>
				</select></br></br>
			</div>
		
			<div style="display:inline-block;margin-left:20px;">
				Output Format <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="tooloutput[]" style="height:100px;width:125px;" >
				<?php
					foreach ($formats_full as $instance) {
						$val = explode('/', $instance);
						$val = end($val);
						if (in_array($instance, $tool_outputs_selected)) {
							echo '<option value="' . $instance . '" selected>' . $val . '</option>';
						} else {
							echo '<option value="' . $instance . '">' . $val . '</option>';
						}
					}
				?>
				</select></br></br>
			</div>
		</div>
		
		<div>
			<div style="display:inline-block;">
				Tool Server <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="toolserver[]" style="height:100px;width:125px;" >
				<?php
					//all possible input formats
					foreach ($servers_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						if (in_array($instance, $tool_servers_selected)) {
							echo '<option value="' . $instance . '" selected>' . $val . '</option>';
						} else {
							echo '<option value="' . $instance . '">' . $val . '</option>';
						}
					}
				?>
				</select></br></br>
			</div>
		
			<div style="display:inline-block;margin-left:20px;">
				Data Access Protocol <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="toolprotocol[]" style="height:100px;width:125px;" >
				<?php
					//all possible output formats
					foreach ($protocols_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						if (in_array($instance, $tool_protocols_selected)) {
							echo '<option value="' . $instance . '" selected>' . $val . '</option>';
						} else {
							echo '<option value="' . $instance . '">' . $val . '</option>';
						}
					}
				?>
				</select></br></br>
			</div>
		</div>
		
		<div>
			<div style="display:inline-block;vertical-align:top;">
				Capabilities <span style="font-size:11px;">(select all that apply):</span></br>
				<?php 
					foreach ($capabilities_full as $instance) {
						$val = explode('/', $instance);
						$val = end($val);
						if (in_array($instance, $tool_capabilities_selected)) {
							echo '<input type="checkbox" name="vistype[]" value="' . $instance . '" checked>' . $val . '</option><br/>';
						} else {
							echo '<input type="checkbox" name="vistype[]" value="' . $instance . '">' . $val . '</option><br/>';
						}
					}
				?>
			</div>
			<div style="display:inline-block;vertical-align:top;">
				Tool Type <span style="font-size:11px;">(select all that apply):</span></br>
				<?php
					foreach ($tooltypes_full as $instance) {
						$val = explode('/', $instance);
						$val = end($val);
						if (in_array($instance, $tool_types_selected)) {
							echo '<input type="checkbox" name="tooltype[]" value="' . $instance . '" checked>' . $val . '</option><br/>';
						} else {
							echo '<input type="checkbox" name="tooltype[]" value="' . $instance . '">' . $val . '</option><br/>';
						}
					}
				?>
			</div>
		</div>
		<input type="hidden" name="status" value="edit">

		<div>
			<input class="button" type="submit" value="Update Tool" name="submit" style="clear:both;margin-top:10px;"></br>
			<p style="font-size:10pt;margin-left:5px;">Note: <span class="red-star">*</span> Starred fields are required. </p>
		</div>
		
	</form>
<?php	
} else {
?>
	<form style="display:block" name="tool_input" action="toolform_submit" method="post" enctype="multipart/form-data">
		<span class="page_title">Tool Form<span>Please fill out all required fields.</span></span><br/>
		
		<span class="red-star">*</span>Tool Name: </br>
		<textarea name="toolname" rows="1" style="width:100%;" placeholder="ex: Panoply" required></textarea></br></br>
		
		<span class="red-star">*</span>Tool Page: </br>
		<textarea name="toolpage" rows="1" style="width:100%;" placeholder="ex: http://www.giss.nasa.gov/tools/panoply/" required></textarea></br></br>
		
		<span class="red-star">*</span>Tool Description: </br>
		<textarea name="tooldesc" rows="4" style="width:100%;" placeholder="ex: Panoply is a cross-platform application that plots geo-gridded and other arrays from netCDF, HDF, GRIB, and other datasets." required ></textarea></br></br>
		
		<span>Tool Logo: </span></br>
		<input id="toologo" type="file" name="toollogo" accept="image/*" onchange="showImg(this)"></br></br>
		<img id="preview" src="#" alt="your image" style="width:150px;display:none;"/></br></br>
		
		<span class="red-star">*</span>Tool Version: </br>
		<textarea name="toolvers" rows="1" style="width:100%;" placeholder="ex: 4.0.2" required></textarea></br></br>
		
		<div>
			<div style="display:inline-block;">
				Input Format <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="toolinput[]" style="height:100px;width:125px;" >
				<?php
					//all possible input formats
					foreach ($formats_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						echo '<option value="' . $instance . '">' . $val . '</option>';
					}
				?>
				</select></br></br>
			</div>
		
			<div style="display:inline-block;margin-left:20px;">
				Output Format <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="tooloutput[]" style="height:100px;width:125px;" >
				<?php
					//all possible output formats
					foreach ($formats_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						echo '<option value="' . $instance . '">' . $val . '</option>';
					}
				?>
				</select></br></br>
			</div>
		</div>
		
		<div>
			<div style="display:inline-block;">
				Tool Server <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="toolserver[]" style="height:100px;width:125px;" >
				<?php
					//all possible input formats
					foreach ($servers_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						echo '<option value="' . $instance . '">' . $val . '</option>';
					}
				?>
				</select></br></br>
			</div>
		
			<div style="display:inline-block;margin-left:20px;">
				Data Access Protocol <span style="font-size:11px;">(select all that apply):</span></br>		
				<select multiple name="toolprotocol[]" style="height:100px;width:125px;" >
				<?php
					//all possible output formats
					foreach ($protocols_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						echo '<option value="' . $instance . '">' . $val . '</option>';
					}
				?>
				</select></br></br>
			</div>
		</div>
		
		<div>
			<div style="display:inline-block;vertical-align:top;">
				Capabilities <span style="font-size:11px;">(select all that apply):</span></br>
				<?php
					//all possible tool capabilities
					foreach ($capabilities_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						echo '<input type="checkbox" name="vistype[]" value="' . $instance . '">' . $val . '</option><br/>';
					}
				?>
			</div>
			<div style="display:inline-block;vertical-align:top;">
				Tool Type <span style="font-size:11px;">(select all that apply):</span></br>
				<?php
					//all possible tooltypes
					foreach ($tooltypes_full as $instance) {
						$val = explode('/',$instance);
						$val = end($val);
						echo '<input type="checkbox" name="tooltype[]" value="' . $instance . '">' . $val . '</option><br/>';
					}
				?>
			</div>
		</div>
		<input type="hidden" name="status" value="add">

		<div>
			<input class="button" type="submit" value="Add Tool" name="submit" style="clear:both;margin-top:10px;" ></br>
			<p style="font-size:10pt;margin-left:5px;">Note: <span class="red-star">*</span> Starred fields are required. </p>
		</div>
<?php } ?>
<script>
function hideImage(image) {
	var img = document.getElementById(image);
	window.alert(img)
	img.style.display = 'none';
}

function showImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
			document.getElementById('preview').style.display="block";
            document.getElementById('preview').src=e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>


