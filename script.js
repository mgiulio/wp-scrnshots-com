//jQuery(document).ready(function($) {
jQuery(window).load(function($) {
	// All following sizes are expressed in pixels.
	
	var
		slideSize = 270 // A slide is a square.
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
	$('.widget_gm_scrnshots ul li').each(function() {
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
		
		hPad /= 2;
		vPad /= 2;
		
		$(this).css({
			'padding-top': vPad + 'px',
			'padding-right': hPad + 'px',
			'padding-bottom': vPad + 'px',
			'padding-left': hPad + 'px'
		});
	});
	
    $('.widget_gm_scrnshots ul').cycle({
		fx: 'scrollLeft'
	});
});
