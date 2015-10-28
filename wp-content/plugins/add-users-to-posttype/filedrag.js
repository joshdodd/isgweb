/*
filedrag.js - HTML5 File Drag & Drop demonstration
Featured on SitePoint.com
Developed by Craig Buckler (@craigbuckler) of OptimalWorks.net
*/
(function() {

	// getElementById
	function $id(id) {
		return document.getElementById(id);
	}


	// output information
	function Output(msg) {
		var m = $id("messages");
		//m.innerHTML = msg + m.innerHTML;
	}


	// file drag hover
	function FileDragHover(e) {
		e.stopPropagation();
		e.preventDefault();
		e.target.className = (e.type == "dragover" ? "hover" : "");
	}


	// file selection
	function FileSelectHandler(e) {

		// cancel event and hover styling
		FileDragHover(e);

		// fetch FileList object
		var files = e.target.files || e.dataTransfer.files;

		// process all File objects
		for (var i = 0, f; f = files[i]; i++) {
			ParseFile(f);
			UploadFile(f);
		}

	}


	// output file information
	function ParseFile(file) {

		Output(
			"<p>File information: <strong>" + file.name +
			"</strong> type: <strong>" + file.type +
			"</strong> size: <strong>" + file.size +
			"</strong> bytes</p>"
		);

		// display text
		if (file.type.indexOf("text") == 0) {
			var reader = new FileReader();
			reader.onload = function(e) {
				Output(
					"<p><strong>" + file.name + ":</strong></p><pre>" +
					e.target.result.replace(/</g, "&lt;").replace(/>/g, "&gt;") +
					"</pre>"
				);
			}
			reader.readAsText(file);
		}

	}


	// upload files
	function UploadFile(file) {


		var xhr = new XMLHttpRequest();
		if (xhr.upload && (file.type == "text/csv" || file.type == "application/vnd.ms-excel") && file.size <= $id("MAX_FILE_SIZE").value ) {
			//&& file.type == "text/csv"  && file.size <= $id("MAX_FILE_SIZE").value
			//var tester = file.type;
			//alert(tester); alert("!!!!!!!!!");
			// create progress bar
			var o = $id("progress");
			var progress = o.appendChild(document.createElement("p"));
			progress.appendChild(document.createTextNode("upload " + file.name));


			// progress bar
			xhr.upload.addEventListener("progress", function(e) {
				var pc = parseInt(100 - (e.loaded / e.total * 100));
				progress.style.backgroundPosition = pc + "% 0";
			}, false);

			// file received/failed
			xhr.onreadystatechange = function(e) {
				if (xhr.readyState == 4) {
					progress.className = (xhr.status == 200 ? "success" : "failure");
					$response = xhr.responseText
					//comb through orders
					jQuery('#autpWrapper').append($response);
					$updatesort = '';
					$repeatIDs = [];

					jQuery('ul#autpWrapper li.user').each(function(i){
					    $index = i+1;
						$thisid = jQuery(this).find('input:eq(0)').val();
						jQuery(this).attr('id','user_'+$index);
						jQuery(this).find('input:eq(0)').attr('id','currentuser_'+$index+'_id').attr('name','currentuser_'+$index+'_id');
						jQuery(this).find('div.deleteWrapper > a').attr('href','javascript:DeleteClientUserUser("'+$index+'")');
						jQuery(this).find('ul.currentuser_bio textarea').attr('id','currentuser_'+$index+'_info').attr('name','currentuser_'+$index+'_info');

						$updatesort += 'user[]='+$index+'&';

						if(jQuery.inArray($thisid, $repeatIDs) > -1){
						    jQuery(this).remove();
						}else{
							$repeatIDs.push($thisid);
						}


					});
					if ($updatesort.substring($updatesort.length-1) == "&"){
				        $updatesort = $updatesort.substring(0, $updatesort.length-1);
				    }
					jQuery('input#currentuser_lastsort').val(jQuery('#autpWrapper li.user').size());
					jQuery('input#currentuser_updatedsort').val($updatesort);
				}
			};

			// start upload
			xhr.open("POST", "http://essentialhospitals.org/wp-content/plugins/add-users-to-posttype/upload.php", true);
			xhr.setRequestHeader("X_FILENAME", file.name);
			xhr.send(file);


		}


	}


	// initialize
	function Init() {

		var fileselect = $id("fileselect"),
			filedrag = $id("filedrag"),
			submitbutton = $id("submitbutton");

		// file select
		fileselect.addEventListener("change", FileSelectHandler, false);

		// is XHR2 available?
		var xhr = new XMLHttpRequest();
		if (xhr.upload) {

			// file drop
			filedrag.addEventListener("dragover", FileDragHover, false);
			filedrag.addEventListener("dragleave", FileDragHover, false);
			filedrag.addEventListener("drop", FileSelectHandler, false);
			filedrag.style.display = "block";

			// remove submit button
			submitbutton.style.display = "none";
		}

	}

	// call initialization file
	if (window.File && window.FileList && window.FileReader) {
		Init();
	}


})();