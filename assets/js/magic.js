var smu_clearError;	//need to forward declare this function so it's avail outside of doc.ready scope
						// (but still need doc to be ready before function can run)

jQuery(document).ready(function($) {

	//Clear error message from server once user clicks to type
	smu_clearError = function () {
		$('.smu-server-response').html("");
	}
				
	//Validate the form and send; Use jQuery Validation plugin	
	var form = 'form.smu-subscribe-form';	//form element's class/id (e.g. <form class="subscribe-form">			
	$(form).validate({
	/* Not needed - use defaults
		rules: {
			attribute1: {
				required: true
			},
			email: {
				required: true,
				email: true
			}
		},
	*/
		messages: {
			//attribute1: "Required Field",
			email: {
				required: "Valid email required"
			}
		},

		//errorContainer: $('#smu-jquery-error'),
		errorLabelContainer: $('#smu-jquery-error'),
		focusInvalid: false,		//to prevent invoking keyboard on mobile
		
		//perform an AJAX post
		submitHandler: function(
			
		) {

			$('img.wait-img').show();									//indicate network activity to user
			var params = '&' + $(form).serialize();
			var url = $(form).attr('action'); //this.action;
    		var ajax_url = url.replace(/subscribe/,'asubscribe');	//explicity specify AJAX parser in PHPList
			//alert (params);return false;	//debug only
			//alert (submit_handler_url);return false;	//debug only
			//alert (ajax_url);return false;	//debug only
			$.ajax({
				type: 'POST',
				url: ajax_url,
				data: params,
				success: function( data, textStatus, jQxhr ){ 	
					$('img.wait-img').hide();						//stop network activity indicator
					//$('.smu-server-response').html( data );	//debug only
					if (data.search(/FAIL/) >= 0) {
						$('.smu-server-response').css( "color", 'red' );
						$('.smu-server-response').html( "Please check that you have entered a valid email address and try again." );
					} else {
						$('.smu-server-response').css( "color", 'green' );
						$('.smu-server-response').html(data)
						.hide()
						.fadeIn(500);
						$(".smu-subscribe-form").hide();
					}
				}, 						   
				error: function(){
					$('img.wait-img').hide();						//stop network activity indicator
					$('.smu-server-response').css( "color", 'red' );
					$('.smu-server-response').html( "Unfortunately a network error occurred. Please try again. If this problem persists, please contact the webmaster." );
				}
			
			});
		}
	});	//end validate

}); //end document ready


