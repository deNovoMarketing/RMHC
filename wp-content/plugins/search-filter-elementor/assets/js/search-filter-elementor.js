(function ( $ ) {

	"use strict";
	
	$(function () {
		
		// re init layout after ajax request
		$(document).on("sf:ajaxfinish", ".searchandfilter", function( e, data ) {
			if ( window.elementorFrontend ) {
				if ( elementorFrontend.elementsHandler ) {
					if ( elementorFrontend.elementsHandler.runReadyTrigger ) {
						elementorFrontend.elementsHandler.runReadyTrigger(data.targetSelector);
					}
				}
			}
		});
	});

	// load search forms in popups
	$( window ).on( 'elementor/frontend/init', function() {
		elementorFrontend.elements.$document.on( 'elementor/popup/show', ( e, id, document ) => {
			if ( $().searchAndFilter ) {
				
				var $sliders = $('.elementor-popup-modal .searchandfilter .meta-slider');
				if ( $sliders.length > 0 ) {
					$sliders.empty();
				}

				$('.elementor-popup-modal .searchandfilter').searchAndFilter();
				
			}
		} );
	});

}(jQuery));
