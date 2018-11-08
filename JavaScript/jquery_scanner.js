function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}

$(function() {
	
	
	var audioElementScan = document.createElement('audio');
	audioElementScan.setAttribute('src', '/version3/mp3/ScanItem_4.mp3');
    //audioElementScan.load()
    audioElementScan.addEventListener("load", function() {
    	audioElementScan.play();
    }, true);
    
    var audioElementKeep = document.createElement('audio');
    audioElementKeep.setAttribute('src', '/version3/mp3/KeepItem_4.mp3');
    //audioElementKeep.load()
    audioElementKeep.addEventListener("load", function() {
    	audioElementKeep.play();
    }, true);
    
    var audioElementScrap = document.createElement('audio');
    audioElementScrap.setAttribute('src', '/version3/mp3/ScrapItem_4.mp3');
    //audioElementScrap.load()
    audioElementScrap.addEventListener("load", function() {
    	audioElementScrap.play();
    }, true);
    
    var audioElementNotFound = document.createElement('audio');
    audioElementNotFound.setAttribute('src', '/version3/mp3/NotFound_4.mp3');
    //audioElementNotFound.load()
    audioElementNotFound.addEventListener("load", function() {
    	audioElementNotFound.play();
    }, true);
    
    var audioElementError = document.createElement('audio');
    audioElementError.setAttribute('src', '/version3/mp3/Error_4.mp3');
    //audioElementError.load()
    audioElementError.addEventListener("load", function() {
    	audioElementError.play();
    }, true);
    
    var audioElementEnterDetails = document.createElement('audio');
    audioElementEnterDetails.setAttribute('src', '/version3/mp3/EnterDetails_4.mp3');
    //audioElementError.load()
    audioElementEnterDetails.addEventListener("load", function() {
    	audioElementEnterDetails.play();
    }, true);

    var audioElementPartSaved = document.createElement('audio');
    audioElementPartSaved.setAttribute('src', '/version3/mp3/PartSaved.mp3');
    //audioElementError.load()
    audioElementPartSaved.addEventListener("load", function() {
    	audioElementPartSaved.play();
    }, true);


	$('a.drill').live('click', function(event){
    	event.preventDefault();
    	var surId = this.id;
    	queryStr = "surId = "+surId;
    	
	});
	
	$('#search').live('click', function(event){
    	event.preventDefault();
    	
    	
    	
    	//audioElementNotFound.play();
    	
	});
	
	$('#identifier').blur(function(){
		
		//what kind of identifier?
		var identifier = $('#identifier').val();
		var res = identifier.replace("--", "");
		identifier = res.trim();
		
		//figure out which scanning method we're using
		var method = $('input[name=method]:checked').val();
		var collectDetails = $('input[name=collectDetails]:checked').val();
		
		var cleiLength = $("input[name='cleiLength']:checked").val();
		
		if (identifier == '') {
			return false;
		}
		
		//ajax call for scrap or keep
		$.ajax({
			dataType: "json",
			url: '/admin/keepscrapqty/search',
			data: { id: identifier, cl: cleiLength, m : method},
			success: function(response) {
				 var result = jQuery.parseJSON(response);

				 if (response.success == true) {
					 if (response.status == -1) {
						 $('#identifier').val('');
						 $('#identifier').focus();
						 $('#partText').text(identifier);
						 $('#statusText').text("ERROR");
						 audioElementError.play();
					 } else if (response.status == 1) {
						 $('#partInput').val(identifier);
						 $('#identifier').val('');
						 $('#identifier').focus();
						 $('#partText').text(identifier);
						 $('#cleiLengthVal').val(cleiLength);
						 $('#statusText').text("KEEP");
						 audioElementKeep.play();
						 //if enter details is set to on, show the popup
						 if (collectDetails == 1) {
							//pause before next audio clip
							 setTimeout(function(){
								 audioElementEnterDetails.play();
								}, 1000);
							 $( "#dialog-capture-details" ).dialog( "open" );
						 } else {
							 //update the kept quantities without details
							 $.ajax({
								 type:'POST', 
								 url: '/admin/keepscrapqty/keepitem', 
								 data: { id: identifier, cl: cleiLength, m : method}, 
								 success: function(response) {
									 if (result.success == true) {
									 } else {
									 }
								}
							 });
							 jQuery('#list1').trigger("reloadGrid",[{page:1}]);
						 }
						 $('#serialNumber').focus();
					 } else if (response.status == 2) {
						 $('#identifier').val('');
						 $('#identifier').focus();
						 $('#partText').text(identifier);
						 $('#statusText').text("SCRAP");
						 audioElementScrap.play(); 
					 } else if (response.status == 3) {
						 $('#identifier').val('');
						 $('#identifier').focus();
						 $('#partText').text(identifier);
						 $('#statusText').text("Not Found");
						 audioElementNotFound.play(); 
					 }
				 } else {
					 $('#identifier').val('');
					 $('#identifier').focus();
					 $('#partText').text(identifier);
					 $('#statusText').text("ERROR");
					 audioElementError.play();
				 }
			}
		});
		
		//update status and play sound
		

		//reload the grid
		
		
		
	});
	
	$(".loadFileInfo").live('click', function(event){
    	event.preventDefault();
    	$( "#dialog-load-message" ).dialog( "open" );
	});
	
	$(".scanPartsInfo").live('click', function(event){
    	event.preventDefault();
    	$( "#dialog-scan-message" ).dialog( "open" );
	});
	
	$(".keptItemsInfo").live('click', function(event){
    	event.preventDefault();
    	$( "#dialog-kept-message" ).dialog( "open" );
	});
	
	$(".cleiLengthInfo").live('click', function(event){
    	event.preventDefault();
    	$( "#dialog-length-message" ).dialog( "open" );
	});
	
	$(".itemDetailsInfo").live('click', function(event){
    	event.preventDefault();
    	$( "#dialog-collect-details-message" ).dialog( "open" );
	});
	
	$(".scanMethodInfo").live('click', function(event){
    	event.preventDefault();
    	$( "#dialog-method-message" ).dialog( "open" );
	});
	
	$( "#dialog-length-message" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:700,
		modal: true,
		buttons: {
			  Ok: function() {
		          $( this ).dialog( "close" );
		        }
		}
	});
	
	$( "#dialog-collect-details-message" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:700,
		modal: true,
		buttons: {
			  Ok: function() {
		          $( this ).dialog( "close" );
		        }
		}
	});
	
	$( "#dialog-method-message" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:700,
		modal: true,
		buttons: {
			  Ok: function() {
		          $( this ).dialog( "close" );
		        }
		}
	});

	$( "#dialog-load-message" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:700,
		modal: true,
		buttons: {
			  Ok: function() {
		          $( this ).dialog( "close" );
		        }
		}
	});
	
	$( "#dialog-scan-message" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:700,
		modal: true,
		buttons: {
			  Ok: function() {
		          $( this ).dialog( "close" );
		        }
		}
	});
	
	$( "#dialog-kept-message" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:700,
		modal: true,
		buttons: {
			  Ok: function() {
		          $( this ).dialog( "close" );
		        }
		}
	});

    
	$( "#dialog-capture-details" ).dialog({
		
		autoOpen: false,
		height: 390,
		width: 300,
		modal: true,
		buttons: {
			"Save Item": function() {
				
				//add the new user via Ajax call
				$.ajax({
					 type:'POST', 
					 url: '/admin/keepscrapqty/capturedetails', 
					 data:$('#captureDeailsForm').serialize(), 
					 success: function(response) {
						 var result = jQuery.parseJSON(response);
						 //if success, refresh the table
						 if (result.success == true) {
							 
							 audioElementPartSaved.play();
							 $('#partInput').val('');
							 $('#serialNumber').val('');
							 $('#location').val('');
							 $('#identifier').focus();
							 
							 jQuery( "#dialog-capture-details" ).dialog( "close" );
							 jQuery('#list1').trigger("reloadGrid",[{page:1}]);
						 } else {
							 audioElementError.play();
						 }
					}
				 });
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			//$( this ).dialog( "close" );
		}
	});
	
	$( "#dialog-confirm" ).dialog({
		autoOpen:false,
		resizable: false,
		height:330,
		width:700,
		modal: true,
		buttons: {
			"Yes, restore": function() {
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
    
//	$("#uploadBtn").on("click", function(e) {
//	    var link = this;
//
//	    e.preventDefault();
//	    
//	    $( "#dialog-confirm" ).data('link', link).dialog( "open" );
//
//	});
	
});