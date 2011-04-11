(function($) {
	$(window).load(function() {
		log('Awaken');
		
		var
			$slides = $('.gm_scrnshots_widget_class ul'),
			stack = [],
			slideSize = 270 // A slide is a square.
		;
		
		/*
		 * 
		 */
		log('Getting feed data');
		$.ajax({
			url: 'http://mgiulio.altervista.org/wp-admin/admin-ajax.php', 
			data: {
				action: 'gm_scrnshots_ajax_get_feed'
			},
			beforeSend: function(jqXHR, settings) {
				log('beforeSend');
				log(jqXHR);
				log(settings);
			},
			//dataType: 'json',
			success: function(data, textStatus, jqXHR) {
				log('Success callback');
				log(data);
				
				log('Parsing JSON');
				try {
					var shots = $.parseJSON(data);
				} catch (e) {
					log('Fatal Error: parsing of JSON failed');
					log(data);
				}
				log(shots);
				
				/*
				 * Build markup and start loading images
				 */
				 var 
					i = 0, 
					numShots = shots.length,
					imgToGo = 2
				;
				 for (i; i < numShots; ++i) {
					s = shots[i];
					$li = $('<li></li>');
					$a = $('<a href="' + s[2] + '" title="' + s[1] + '"></a>');
					$a.appendTo($li);
					$img = $('<img>');
					$img.load(function() {
						log('Image loaded');
						
						var 
							$img = $(this),
							$li = $img.parent().parent(),
							w = this.width, //$img.width(),
							h = this.height, //$img.height(),
							hPad = vPad = 30 // px,
						;
		
						if (w > h) // Landscape format
							vPad = slideSize - h;
						else // Portrait format
							hPad = slideSize - w;
						
						hPad /= 2;
						vPad /= 2;
						
						$li.css({
							'padding-top': vPad + 'px',
							'padding-right': hPad + 'px',
							'padding-bottom': vPad + 'px',
							'padding-left': hPad + 'px'
						});

						// Add the slide to the slideshow
						if (imgToGo > 0) {
							$slides.append($li);
							log('Appended slide in ul');
							if (--imgToGo == 0) {
								log('Starting jCycle');
								$slides.cycle({
									fx: 'scrollLeft',
									before: function(curr, next, opts) {
										 if (opts.addSlide) // <-- important! 
											while(stack.length) {
												opts.addSlide(stack.pop());
												log('addslide');
											}
									}					
								});
							}
						}
						else
							stack.push($li);
					});
					$img.appendTo($a);
					$img.attr('src', s[0]);
				 }
			},
			error: function(jqXHR, textStatus, errorThrown) {
				log('Could not load feed data');
				/* $.n.warning('Could not load collections, retry: ' + retryCounter + '/' + ajaxSettings.maxRetry);
				if (retryCounter >= ajaxSettings.maxRetry) {
					$.n.error('Could not load collections');
				}
				else {
					retryCounter++;
					$.ajax('php/get-collection.php', this);
				}
 */			}
		});
	});
	
	function log(data) {
		if (typeof console === "undefined")
			return;
		console.log('gm_scrnshots: ', data);
	}
	
})(jQuery);