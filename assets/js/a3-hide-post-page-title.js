
(function($) {

	function showHideTitlePostPage( el ){
		if( el.is( ":checked" ) ) {
			$('body').find(a3hpt_paramaters.a3hpt_adminselector).hide();
		} else {
		  	$('body').find(a3hpt_paramaters.a3hpt_adminselector).show();
	   	}
	}

	$(window).on( 'load', function(){

		$('input[name="a3hpt_headertitle"]').on( 'click', function(){

			showHideTitlePostPage( $(this) );

		});

		setTimeout(function() {

			showHideTitlePostPage( $('input[name="a3hpt_headertitle"]') );

	   	}, 1);

   	});

   	$(document).ready( function(){
		
		if( $('body').find(a3hpt_paramaters.a3hpt_selector).length != 0 ) {
			$(	a3hpt_paramaters.a3hpt_selector + ' span.'+a3hpt_paramaters.a3hpt_slug).parents(a3hpt_paramaters.a3hpt_selector+':first').hide();
	    } else {
		  	$('h1 span.'+a3hpt_paramaters.a3hpt_slug).parents('h1:first').hide();
		  	$('h2 span.'+a3hpt_paramaters.a3hpt_slug).parents('h2:first').hide();
	   	}
	});

})(jQuery);