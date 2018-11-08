$(document).ready(function(){
	
	$('#cleiSearch').focus();
	
	$('#cleiSearch').blur(function(e){

		e.preventDefault();
		
		if ($('#cleiSearch').val() == '') {
			return;
		}
		
		$.ajax({
			 type:'POST', 
			 url: '/admin/webcam/searchclei', 
			 data: 'clei=' + $('#cleiSearch').val(), 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 var partNumber = result.partNumber;
				 var clei = result.clei;
				 var desc = result.desc;
				 var success = result.success;
				 $("#partNumber").val(partNumber);
				 $("#clei").val(clei);
				 $("#description").val(desc);
				 if (success == 1) {
					 $('#weight').focus();
				 } else {
					 $('#pnSearch').focus(); 
				 }
			}
		 });
		
	});
	
	$('#weight').blur(function(e) {
		$('#picButton').trigger('click');
		$('#submitBtn').focus();
	});
	
	$('#pnSearch').blur(function(e){

		e.preventDefault();
		
		if ($('#pnSearch').val() == '') {
			return;
		}
		
		$.ajax({
			 type:'POST', 
			 url: '/admin/webcam/searchpn', 
			 data: 'pn=' + $('#pnSearch').val(), 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 var partNumber = result.partNumber;
				 var clei = result.clei;
				 var desc = result.desc;
				 $("#partNumber").val(partNumber);
				 $("#clei").val(clei);
				 $("#description").val(desc);
				 var success = result.success;
				 if (success == 1) {
					 $('#weight').focus();
				 } else {
					 $('#partNumber').focus(); 
				 }
			}
		 });
		
	});
	
	$('#searchBtn').live('click', function(e){
		
		e.preventDefault();
		
		var searchTerm = $('#searchTerm').val();
		
		if (searchTerm.length < 4) {
			alert('Please enter at least 4 characters in the search box.');
		} else {
			$('#searchImages').submit();
		}
	
	});

	$('#searchCleiBtn').live('click', function(e){
		
		e.preventDefault();
		
		$.ajax({
			 type:'POST', 
			 url: '/admin/webcam/searchclei', 
			 data: 'clei=' + $('#cleiSearch').val(), 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 var partNumber = result.partNumber;
				 var clei = result.clei;
				 var desc = result.desc;
				 $("#partNumber").val(partNumber);
				 $("#clei").val(clei);
				 $("#description").val(desc);
			}
		 });
	
	});
	
	$('#searchPnBtn').live('click', function(e){
		
		e.preventDefault();
		
		$.ajax({
			 type:'POST', 
			 url: '/admin/webcam/searchpn', 
			 data: 'pn=' + $('#pnSearch').val(), 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 var partNumber = result.partNumber;
				 var clei = result.clei;
				 var desc = result.desc;
				 $("#partNumber").val(partNumber);
				 $("#clei").val(clei);
				 $("#description").val(desc);
			}
		 });
	
	});
	
	$('#submitBtn').live('click', function(e){
		
		e.preventDefault();
		
		var partNumber = $('#partNumber').val();
		var clei = $('#clei').val();
		var description = $('#description').val();
		var weight = $('#weight').val();
		var data_uri = $('#imageUri').val();

		if (partNumber.length < 5 && clei.length < 5) {
			alert('You must enter either a Part Number or a CLEI. Minimum 5 characters.');
			return;
		}
		
		//pass placeholder value if we're missing either PN or CLEI
		if (partNumber == '') {
			partNumber = 'null';
		}
		if (clei == '') {
			clei = 'null';
		}
		if (description == '') {
			description = 'null';
		}
		if (weight == '') {
			weight = '0';
		}
		
		if ($('#imageUrl').val() == '') {
			//no URL image upload
			
			if (data_uri.length == 0) {
				alert('Please take a picture first!');
				return;
			}
			
			
			var url = '/admin/webcam/index/pn/' + partNumber + '/clei/' + clei + '/description/' + description + '/weight/' + weight;
			Webcam.upload( data_uri, url, function(code, text) {
				
				if (text == 'error') {
					alert('There was an error processing your request. Please check your form data, and try again');
				} else {
					$('#partNumber').val('');
					$('#clei').val('');
					$('#description').val('');
					$('#weight').val('');
					$('#imageUri').val('');
					$('#cleiSearch').val('');
					$('#pnSearch').val('');
					$('#cleiSearch').focus();
					document.getElementById('results').innerHTML = 
						'Your captured image will appear here...';
					alert('Success! Your image has been saved.');
					$('#cleiSearch').focus();
				}
				
	            // Upload complete!
	            // 'code' will be the HTTP response code from the server, e.g. 200
	            // 'text' will be the raw response content
	        } );
		} else {
			//URL image upload
			$.ajax({
				 type:'POST', 
				 url: '/admin/webcam/upload', 
				 data: $('#photoUpload').serialize(),
				 success: function(response) {
					 var result = jQuery.parseJSON(response);
					 //if success, refresh the table
					 if (result.success == true) {
						 $('#partNumber').val('');
							$('#clei').val('');
							$('#description').val('');
							$('#weight').val('');
							$('#imageUri').val('');
							$('#cleiSearch').val('');
							$('#pnSearch').val('');
							$('#cleiSearch').focus();
							document.getElementById('results').innerHTML = 
								'Your captured image will appear here...';
							alert('Success! Your image has been saved.');
							$('#cleiSearch').focus();
					 } else {
						 alert('There was a problem uploading your image.');
					 }
				 }
			 });
			
		}
	
	});
	
	$('#imageUrl').focus(function() {
		document.getElementById('results').innerHTML = 
			'Your captured image will appear here...';
	});
	
	
});