jQuery(document).ready(function($) {
	var
		slideshowCanvasSize = 240 // px
	; 
	
	// Style the images
	/* $('#gm_scrnshots img').each(function() {
		var
			$img = $(this),
			w = $img.width(),
			h = $img.height(),
			aspectRatio = w / h
		;
		
		// Resize and position the image depending on its aspect ratio format
		if (aspectRatio > 1.0) { // Landscape format
			$img.width(slideshowCanvasSize);
			$img.height(slideshowCanvasSize / aspectRatio);
		}
		else { // Portrait format
			$img.height(slideshowCanvasSize);
			$img.width(slideshowCanvasSize * aspectRatio);
		}
	}); */
	
    $('#gm_scrnshots ul').cycle({
		fx: 'fade' // choose your transition type, ex: fade, scrollUp, shuffle, etc...
	});
});
