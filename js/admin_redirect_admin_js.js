var llama_redirect = {
	init:function($) {
		if ($('#fs_connect').length == 0) {
			$('#wpbody-content > [class*="llama_redirect_"]:not([class*="notice"])').fadeIn();
		}
		if ($('form#llama_redirect_add_page #red_code').length > 0) {
			llama_redirect.select_helper($);
			$('body').on('change','form#llama_redirect_add_page #red_code',function() {
				llama_redirect.select_helper($);
			});
		}
		llama_redirect.append_freemius_menu($);
		$('body').on('click','.js-llama_redirect_delete:not(.button-disabled)',function(e) {
			e.preventDefault();
			var obj = $(this), id = obj.data('id');
			d = {
				id:id,
				_key:'redirect_id',
				_val:id,
				action:'js_llama_redirect_delete',
				nonce:obj.data('nonce')	
			};
			obj.addClass('button-disabled');
			$.when(llama_redirect.call($,'remove',d)).done(function(s) {
				obj.removeClass('button-disabled');
				llama_redirect.toast($,s.toast);
				if (s.status) {
					$('tr[data-id="'+id+'"]').remove();
				}
			});
		});
		$('body').on('keyup keypress blur change','#llama_redirect_filter',function() {
			llama_redirect.filter($);
		});
		$('body').on('click','.js-llama_redirect_filter',function(e) {
			e.preventDefault();
			llama_redirect.filter($);
		});
		llama_redirect.datatables($);
		llama_redirect.timepicker($);
		//auto focus:
		llama_redirect.auto_focus($);
		//assistent:
		llama_redirect.assistant_init($);
	},
	auto_focus:function($) {
		if ($('form#llama_redirect_add_page').length > 0) {
			$('form#llama_redirect_add_page input:not([type="hidden"])').eq(0).focus();
		}
	},
	timepicker:function($) {
		$('div[class*="llama_redirect"] input[data-type="time"]').each(function(index, element) {
			if ($(this).val() == '00:00:00' || $(this).val().length == 0) {
				$(this).val($(this).data('def'));
			}
			$(this).timepicker({
				timeFormat: 'H:i:s',
			});
			
        });
	},
	datatables:function($) {
		$('[id].js-init-data-table').each(function(index, element) {
            var id = $(this).attr('id');
			$('#'+id+' thead tr').clone(true).appendTo( '#'+id+' thead' );
			$('#'+id+' thead tr:eq(1) th').each( function (i) {
				var title = $(this).text();
				if (title.length > 0) {
					$(this).html( '<input type="text" placeholder="Search '+title+'" />' );
					$( 'input', this ).on( 'keyup change', function () {
						if ( table.column(i).search() !== this.value ) {
							table.column(i).search( this.value ).draw();
						}
					});
				}
			});
			var data_table_settings = {};
			if ($(this).find('tbody > tr:first-child > th, tbody > tr:first-child > td').length > 2) {
				data_table_settings['order'] = [[3,'desc']];
			}
			var table = $('#'+id).DataTable(data_table_settings);
        });
	},
	filter:function($) {
		var v = $('#llama_redirect_filter').val().toLowerCase();
		$('table tr[data-filter]').each(function(index, element) {
            if ($(this).data('filter').indexOf(v) > -1) {
				$(this).show();	
			}
			else {
				$(this).hide();	
			}
        });
	},
	call:function($,f,d) {
		var def = $.Deferred();
		$.ajax({
			type:'POST',
			url:ajax_object.ajax_url,
			dataType:'json',
			data: d,
			success:function(s) {
				def.resolve(s);
			},
			error:function(s) {
				def.resolve({status:0,toast:'Error'});
			},
			complete:function(s) {
				def.resolve({status:0,toast:'Error'});
			}
		});
		return def.promise();
	},
	select_helper:function($) {
		var t = $('form#llama_redirect_add_page #red_code option:selected').data('helper');
		if (t) {
			$('.llama_redirect_-select-helper[data-for="red_code"]').text(t);	
		}
	},
	toast:function($,t) {
		var c = 'llama_redirect_-toast';
		$('body').append('<div class="'+c+'" style="display:none;"><span>'+t+'</span></div>');
		$('.'+c).stop().fadeIn().delay(3000).fadeOut(400);
		window.setTimeout(function() {
			$('.'+c).remove();
		},3400);
	},
	append_freemius_menu:function($) {
		//1) plugins menu:
		var menu = {};
		$('.llama_redirect_-tabs .nav-tab').each(function(index, element) {
            menu[$(this).prop('href')] = $(this).text();
        });
		//2) append menu:
		$('#toplevel_page_llama-redirect ul li a').each(function(index, element) {
			var href = $(this).prop('href'), title = $(this).text();
            if (!(href in menu)) {
				var new_item = '<a class="nav-tab" href="'+href+'">'+title+'</a>';
				$('.llama_redirect_-tabs').append(new_item);
			}
        });
	},
	assistant_form_loaded:function(ml_box, hide) {
		// Remove pleloader:
		if (document.querySelector(ml_box + ' .ml-preloader')) {
			document.querySelector(ml_box + ' .ml-preloader').style.display = 'none';
		}
			
		// Show form:
		if (document.querySelector(ml_box + ' form')) {
			if (hide) {
				document.querySelector(ml_box + ' form').style.display = 'none';
			}
			else {
				document.querySelector(ml_box + ' form').style.display = 'block';	
			}
		}
	},
	assistant_init:function($) {
		
		var ml_box = '#llama_redirect_ml_assistant';
		
		if ($(ml_box).length > 0) {
			// Keep track of the iframe height.
			var if_height,
			
			// Pass the parent page URL into the Iframe in a meaningful way (this URL could be
			// passed via query string or hard coded into the child page, it depends on your needs).
			src = $(ml_box).data('src') + '#' + encodeURIComponent(document.location.href),
			
			// Append the Iframe into the DOM.
			param_onload = 'onload="llama_redirect.assistant_form_loaded(' + "'" + ml_box + "'" + ')"',
			param_onerror = 'onerror="llama_redirect.assistant_form_error()"',
			iframe = $( '<i' + 'frame " src="' + src + '" width="100%" height="500" scrolling="no" frameborder="0" ' + param_onload + ' ' + param_onerror + ' ><\/i' + 'frame>' ).prependTo(ml_box);
			
			// Setup a callback to handle the dispatched MessageEvent event. In cases where
			// window.postMessage is supported, the passed event will have .data, .origin and
			// .source properties. Otherwise, this will only have the .data property.
			$.receiveMessage(function(e){
				// Get the height from the passsed data.
				if (e.data.indexOf('if_height') > -1) {
					var h = Number( e.data.replace( /.*if_height=(\d+)(?:&|$)/, '$1' ) );
				
					if ( !isNaN( h ) && h > 0 && h !== if_height ) {
						// Height has changed, update the iframe.
						iframe.height( if_height = h );
					}
				}
				// Get error message:
				else if (e.data.indexOf('load_error') > -1) {
					// Hide all page elements, because some error occured:
					llama_redirect.assistant_form_loaded(ml_box,true);
				}
				// Get max width message
				else if (e.data.indexOf('set_max_width') > -1) {
					// Set width of settings tab:
					if (document.querySelector('.llama_redirect_-ml-chat-container')) {
						var w = Number( e.data.replace( /.*set_max_width=(\d+)(?:&|$)/, '$1') );
						document.querySelector('.llama_redirect_-ml-chat-container').style.maxWidth = w + 'px';
					}
				}
			});
		}
	},
	assistant_form_error:function() {
		console.log('ERROR');
	},
	assistant_toggle_settings:function(button_object) {
		var settings_box = '#llama_redirect_ml_settings';
		if (!jQuery(settings_box).is(':visible')) {
			jQuery(button_object).removeClass('is_close').addClass('is_open');
			//jQuery(button_object).text(jQuery(button_object).data('is_open'));
		}
		else {
			jQuery(button_object).removeClass('is_open').addClass('is_close');
			//jQuery(button_object).text(jQuery(button_object).data('is_close'));
		}
		jQuery(settings_box).slideToggle("fast");
	}
}
jQuery(document).ready(function($) {
	llama_redirect.init($);
});

