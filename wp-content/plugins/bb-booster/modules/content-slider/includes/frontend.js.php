(function($) {

	$(function() {
	
		var slider = $('.fl-node-<?php echo $id; ?> .fl-content-slider-wrapper').bxSlider({
			adaptiveHeight: true,
			auto: <?php if($settings->auto_play) echo 'true'; else echo 'false'; ?>,
			autoHover: true,
			autoControls: <?php if($settings->play_pause) echo 'true'; else echo 'false'; ?>,
			pause: <?php echo $settings->delay * 1000; ?>,
			mode: '<?php echo $settings->transition; ?>',
			speed: <?php echo $settings->speed * 1000; ?>,
			controls: false,
			infiniteLoop: <?php echo $settings->loop; ?>,
			pager: <?php if($settings->dots) echo 'true'; else echo 'false'; ?>,
			video: true,
			onSliderLoad: function() { 
				$('.fl-node-<?php echo $id; ?> .fl-content-slider-wrapper').addClass('fl-content-slider-loaded'); 
			},
			onSlideBefore: function(ele, oldIndex, newIndex) {
				$('.fl-node-<?php echo $id; ?> .fl-content-slider-navigation a').addClass('disabled');
				$('.fl-node-<?php echo $id; ?> .bx-viewport > .bx-controls .bx-pager-link').addClass('disabled');
			},
			onSlideAfter: function( ele, oldIndex, newIndex ) {
				$( '.fl-node-<?php echo $id; ?> .fl-slide-' + oldIndex + ' iframe[src*="youtube"]' ).each( function(){
					var src = $( this ).attr( 'src' );
					$( this ).attr( 'src', '' );
					$( this ).attr( 'src', src );
				} );

				$('.fl-node-<?php echo $id; ?> .fl-content-slider-navigation a').removeClass('disabled');
				$('.fl-node-<?php echo $id; ?> .bx-viewport > .bx-controls .bx-pager-link').removeClass('disabled');
			}
		});
	   
		// Store a reference to the slider.
		slider.data('bxSlider', slider);

		<?php if($settings->arrows) : ?>

			$('.fl-node-<?php echo $id; ?> .slider-prev').on( 'click', function( e ){
				e.preventDefault();
				slider.goToPrevSlide();
			} );

			$('.fl-node-<?php echo $id; ?> .slider-next').on( 'click', function( e ){
				e.preventDefault();
				slider.goToNextSlide();
			} );
			
		<?php endif; ?>

	});

})(jQuery);