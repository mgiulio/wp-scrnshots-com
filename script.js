//jQuery(document).ready(function($) {
jQuery(window).load(function($) {
	// All following sizes are expressed in pixels.
	
	var
		slideSize = 240 // A slide is a square.
		, $ = jQuery
	; 
	
	/*
	 * Center the pictures.
	 *
	 * A lansdscape format picture has to be centerd vertically,
	 * a portrait format one horizontally.
	 * The centering is done setting the padding of the parent <li>.
	 */
	// 
	$('.widget_gm_scrnshots_widget ul li').each(function() {
		var
			$img = $(this.firstChild.firstChild),
			w = $img.width(),
			h = $img.height(),
			hPad = vPad = 30 // px
		;
		
		if (w > h) { // Landscape format
			vPad = slideSize - h;
		}
		else { // Portrait format
			hPad = slideSize - w;
		}
		
		$(this).css('padding', vPad/2.0 + 'px, ' + hPad/2.0 + 'px');
	});
	
    $('.widget_gm_scrnshots_widget ul').cycle({
		fx: 'fade' // choose your transition type, ex: fade, scrollUp, shuffle, etc...
	});
});
