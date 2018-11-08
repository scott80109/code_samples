$(document).ready(function(){
	
	$(function() {
		$( "#summaryStartDate" ).datepicker();
		
	});
	
	$(function() {
		$( "#summaryEndDate" ).datepicker();
	});
	
	$('#poTable').floatThead({
	    position: 'absolute'
	});
	
	$('textarea[data-limit-rows=true]')
    	.on('keypress', function (event) {
	        var textarea = $(this),
	            text = textarea.val(),
	            numberOfLines = (text.match(/\n/g) || []).length + 1,
	            maxRows = parseInt(textarea.attr('rows'));
	
	        if (event.which === 13 && numberOfLines === maxRows ) {
	          return false;
	        }
    });
	
	$( "#dialog-ajax-spinner" ).dialog({
		autoOpen:false,
		resizable: false,
		height: 200,
		width: 200,
		modal: true,
	});
	
	$( ".tRfqBtn" ).live('click', function(event){
		event.preventDefault();

		var theName = this.name;
		var parts = theName.split("_");
		var theId = parts[1];
		
		$.ajax({
			type:'POST',
			url: '/dashboard/telcorfq',
			data: { id: theId},
			success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 alert('Your RFQ has been sent! A CSG representative will contact you with a quote soon.');
				 } else {
					 alert(result.message);
				 }
			}
			});
	});
	
	$( ".allocateButton" ).live('click', function(event){
		event.preventDefault();
		
		var theId = this.id;
		var qtyInputId = 'wantedQty_'+theId;
		var qty = $('#'+qtyInputId).val();
				
		$.ajax({
			type:'POST',
			url: '/dashboard/allocate',
			data: { id: theId, q: qty},
			success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 alert('Your Allocation request has been sent!');
					 var queryString = 'id = ' + theId;
				     $('.flexme099').flexOptions({ query: queryString }).flexReload();
				 } else {
					 alert(result.message);
				 }
			}
			});
	});
	
	$( ".rfqButton" ).live('click', function(event){
		event.preventDefault();
		
		var theId = this.id;
		
		$.ajax({
			type:'POST',
			url: '/dashboard/rfq',
			data: { id: theId},
			success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 alert('Your RFQ request has been sent! A CSG representative will contact you soon.');
				 } else {
					 alert(result.message);
				 }
			}
			});
	});
	
	$(document).ajaxSend(function(event, jqxhr, settings) {
		if (settings.url == '/dashboard') {
			//do nothing
		} else {
			//$( "#dialog-ajax-spinner" ).dialog( "open" );
		}
	});

	$("#dialog-ajax-spinner").ajaxStop(function(){
		$( "#dialog-ajax-spinner" ).dialog( "close" );
		$('.csgrow').closest('tr')
    	.addClass('csgyellow');
		$('.relatedrow').closest('tr')
	    	.addClass('csgpowderblue');
		$('.otherrow').closest('tr')
	    	.addClass('csgcornsilk');
		$('.firstrow').closest('tr')
	    	.addClass('csgseparator');
	});
	
	$( "#dialog-weeklyOnTime" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:450,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#dialog-monthlyOnTime" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:450,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#dialog-csrTime" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:550,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#dialog-iccTime" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:600,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#dialog-escalationTime" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:600,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#dialog-onTimePickup" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:550,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#dialog-dockToStock" ).dialog({
		autoOpen:false,
		resizable: false,
		height:480,
		width:450,
		modal: true,
		buttons: {
			Close: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	
	
	
	
	$( "#poNumberCsr" ).change(function(e){
		
		poSelected = $("#poNumberCsr").val();
				
		$.ajax({
			type:'POST',
			url: '/dashboard/csrpopicker',
			data: { po: poSelected },
			success: function(response) {
				 var result = jQuery.parseJSON(response);
				 var adr = result.adr;
				 $("#csrAdr").val(adr);
			}
		});
	});
	
	$('.redeployBtn').live('click', function(event){
		event.preventDefault();
		
		var theName = this.name;
		var parts = theName.split("_");
		var theid = parts[1];
		
		//make ajax call
		$.ajax({
			 type:'POST', 
			 url: '/dashboard/requestredeploy', 
			 data: { id: theid}, 
			 success: function(response) {
				 
				 var result = jQuery.parseJSON(response);
				 
				 //if success, refresh the table
				 if (result.success == true) {
					 
					$("#list1").trigger("reloadGrid", [{current:true}]);
					 
					alert('Your redeploy has been successfully requested.')
					 
				 } else {
					alert("Error requesting redeploy!");
				 }
			}
		 });
		
		
	});
	
	$('.dashQuoteBtn').live('click', function(event){
		event.preventDefault();
		
		var theName = this.name;
		var parts = theName.split("_");
		var theid = parts[1];
		
		//make ajax call
		$.ajax({
			 type:'POST', 
			 url: '/dashboard/requestquote', 
			 data: { id: theid}, 
			 success: function(response) {
				 
				 var result = jQuery.parseJSON(response);
				 
				 //if success, refresh the table
				 if (result.success == true) {
					 
					$("#list1").trigger("reloadGrid", [{current:true}]);
					 
					alert('Your quote request has been sent.')
					 
				 } else {
					alert("Error requesting quote!");
				 }
			}
		 });
		
		
	});
	
	$( ".totalWeightLink" ).click(function(e){
		e.preventDefault();
		
		var theid = this.id;
		
		//delete all table rows
		 $("#detailedWeights").find("tr:gt(0)").remove();
		
		//make ajax call
		$.ajax({
			 type:'POST', 
			 url: '/dashboard/getweightdetails', 
			 data: { id: theid}, 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 
					 $.each(result.weightDetails, function(key, value) { 
						 $('#detailedWeights tr:last').after('<tr><td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+
								 value.class+'</td><td style=\"padding:10px 20px 10px 10px; text-align:left; font-size:13px; font-weight:300;border: 1px solid black; background:white;\">'+
								 value.weight+'</td></tr>');
					});
					 
				 } else {
					alert("Error retrieving data!");
				 }
			}
		 });
    	
    	//pop dialog
    	$( "#dialog-show-weight-details" ).dialog( "open" );
	});
	
	$( "#dialog-show-weight-details" ).dialog({
		autoOpen:false,
		height: 450,
		width: 350,
		resizable: false,
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$( "#summarySearchBtn" ).click(function(e){
		e.preventDefault();
		
		var startDate = $("#summaryStartDate").val();
		var endDate = $("#summaryEndDate").val();
		
		if (startDate == '') {
			alert('You must enter a valid start date');
			return false;
		}
		if (endDate == '') {
			alert('You must enter a valid end date');
			return false;
		}
		
    	var queryString = "manualsearch|" + startDate + "|"+ endDate;
    	$('.flexme007').flexOptions({ query: queryString }).flexReload();		
		
	});
	
	$( "#submitCsr" ).click(function(e){
		e.preventDefault();
		
		poSelected = $("#poNumberCsr").val();
		adrVal = $("#csrAdr").val();
		
		if (poSelected == 0) {
			alert('You must select a PO number.');
			return false;
		}
		
		details = $("#csrDetails").val();
		
		if (details == "") {
			alert('You must enter a message.');
			return false;
		}
		
		$.ajax({
			dataType: "json",
			url: '/dashboard/csr',
			data: { csrtext: details, po: poSelected, adr: adrVal},
			success: function () {
				$('#csrDetails').val('');
				alert('Your CSR has been sent. You will receive a response within 48 hours.');
			}
			});
	});
	
	
	$( "#weeklyOnTime" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-weeklyOnTime" ).dialog( "open" );
	});
	
	$( "#monthlyOnTime" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-monthlyOnTime" ).dialog( "open" );
	});
	
	$( "#csrTime" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-csrTime" ).dialog( "open" );
	});
	
	$( "#iccTime" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-iccTime" ).dialog( "open" );
	});
	
	$( "#esclationResponse" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-escalationTime" ).dialog( "open" );
	});
	
	$( "#onTimePickup" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-onTimePickup" ).dialog( "open" );
	});
	
	$( "#dockToStock" ).click(function(e){
		e.preventDefault();
		
		$( "#dialog-dockToStock" ).dialog( "open" );
	});
	
	
	
	$('.consigneeCostsLink').live( "click", function(e) {
		e.preventDefault();
		
		po = this.id;
		
		//populate the popup table data
		//make ajax call
		$.ajax({
			 type:'POST', 
			 url: '/dashboard/getconsigneecostsdata', 
			 data: { id: po}, 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 
					 $("#revConsigneeCostsTable").find("tr:gt(0)").remove();
					 
					 $.each(result.costs, function(key, value) { 
						 $('#revConsigneeCostsTable tr:last').after(
								 '<tr><td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+value.type+'</td> + <td style=\"padding:10px 20px 10px 10px; text-align:left; font-size:13px; font-weight:300;border: 1px solid black; background:white;\">'+value.amount+'</td> + <td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+value.date+'</td> + </tr>');
					});
					 
				 } else {
					alert("Error retrieving data!");
				 }
			}
		 });
		
		$( "#rev-consignee-cost-dialog" ).dialog( "open" );
	});
	
	$( "#rev-consignee-cost-dialog" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:400,
		modal: true,
		buttons: {
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$('.consigneeCostsAppliedLink').live( "click", function(e) {
		e.preventDefault();
		
		po = this.id;
		
		//populate the popup table data
		//make ajax call
		$.ajax({
			 type:'POST', 
			 url: '/dashboard/getconsigneecostsapplied', 
			 data: { id: po}, 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 
					 $("#consigneeAppliedCostsTable").find("tr:gt(0)").remove();
					 
					 $.each(result.deductions, function(key, value) { 
						 $('#consigneeAppliedCostsTable tr:last').after(
								 '<tr><td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+value.po+'</td><td style=\"padding:10px 20px 10px 10px; text-align:left; font-size:13px; font-weight:300;border: 1px solid black; background:white;\">'+value.applied_from_po+'</td><td style=\"padding:10px 20px 10px 10px; text-align:left; font-size:13px; font-weight:300;border: 1px solid black; background:white;\">'+value.amount+'</td> <td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+value.date+'</td></tr>');
					});
					 
				 } else {
					alert("Error retrieving data!");
				 }
			}
		 });
		
		$( "#applied-consignee-cost-dialog" ).dialog( "open" );
	});
	
	$( "#applied-consignee-cost-dialog" ).dialog({
		autoOpen:false,
		resizable: true,
		height:400,
		width:500,
		modal: true,
		buttons: {
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	
	$('.totalPaymentClass').live( "click", function(e) {
		e.preventDefault();
		
		payment = this.id;
		
		$("#paymentBreakdownTable").find("tr:gt(0)").remove();
		
		//populate the popup table data
		//make ajax call
		$.ajax({
			 type:'POST', 
			 url: '/dashboard/getpaymentdetails', 
			 data: { id: payment}, 
			 success: function(response) {
				 var result = jQuery.parseJSON(response);
				 //if success, refresh the table
				 if (result.success == true) {
					 
					 $("#paymentBreakdownTable").find("tr:gt(0)").remove();
					 
					 $.each(result.payments, function(key, value) { 
						 $('#paymentBreakdownTable tr:last').after(
								 '<tr><td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+value.formattedDate+'</td><td style=\"padding:10px 20px 10px 10px; text-align:left; font-size:13px; font-weight:300;border: 1px solid black; background:white;\">'+value.payment_type+'</td><td style=\"padding:10px 20px 10px 10px; text-align:left; font-size:13px; font-weight:300;border: 1px solid black; background:white;\">'+value.formattedAmount+'</td> <td style=\"padding:10px; font-size:13px; font-weight:300; border: 1px solid black; background:white;\">'+value.po+'</td></tr>');
					});
					 
				 } else {
					alert("Error retrieving data!");
				 }
			}
		 });
		
		$( "#payment-breakdown-dialog" ).dialog( "open" );
	});
	
	$( "#payment-breakdown-dialog" ).dialog({
		autoOpen:false,
		resizable: true,
		height:500,
		width:800,
		modal: true,
		buttons: {
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	
	
	
});