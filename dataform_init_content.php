<form name="data_input" action="dataform.php" method="post" onSubmit="return validate()">
	<span class="page_title">Data Collection Form
		<span>Please enter one or more data collection identifiers</span>
	</span></br>
	
	<div id="data_doi">
		Data Collection DOI <span style="font-size:11px;">(enter and submit a valid DOI to present the landing page about the data collection:</span></br>
		<textarea name="datadoi" rows="1" style="width:100%;" placeholder="ex: 10.5067/AQUA/AIRS/DATA301"></textarea></br></br>
	</div> 
	
	<div id="data_gcmd">
		GCMD Entry ID <span style="font-size:11px;">(enter and submit a valid GCMD Entry ID to autofill information about the data collection):</span></br>
		<textarea name="datagcmd" rows="1" style="width:100%;" placeholder="ex: GES_DISC_AIRX3STD_V006" ></textarea></br></br>
	</div>
	
	<div id="data_url">
		Access URL </br>
		<textarea name="dataurl" rows="1" style="width:100%;" placeholder="ex: http://acdisc.sci.gsfc.nasa.gov/opendap/Aqua_AIRS_Level3/AIRX3STD.006/" ></textarea></br></br>
	</div>

	<input class="button" type="submit" value="Submit Identifier" name="submit"></br>
	
	<p style="font-size:10pt;">Note: At least one field is required.</p>
	
</form>


<script>
function validate() {
	//grab values of 3 fields
	var doi = document.forms["data_input"]["datadoi"].value;
    var gcmd = document.forms["data_input"]["datagcmd"].value;
    var url = document.forms["data_input"]["dataurl"].value;

	if ((doi == null || doi == "") && (gcmd == null || gcmd == "") && (url == null || url == "")) {
		alert("Please fill out at least one field.");
		return false;
	}
}
</script>