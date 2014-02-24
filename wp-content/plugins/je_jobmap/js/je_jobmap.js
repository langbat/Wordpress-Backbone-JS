(function($) {
	JobEngine.Views.MapModal	=	JobEngine.Views.Modal_Box.extend({ 
		el		: jQuery('div#modal_job_map'),
		template : _.template($('#je_jobmap_template').html()),
		events  : {
			// 'change select'					: 'changeCat',
			'change input.search-box'		: 'searchJob',
		},
		initialize	: function(){
			JobEngine.Views.Modal_Box.prototype.initialize.apply(this, arguments );
			this.search_params	=	{};

		},

		closeModal : function (time, callback) {
			pubsub.trigger('je_jobmap:modalSearchJob', this.search_params );
			var modal = this;
			time = time || 200,
			this.$overlay.fadeOut(200, function(){
				modal.$el.hide();
				if (typeof callback === 'function'){
					callback();
				}
			});
			return false;
		},

		openModalMap : function ( params ) {
			this.openModal();
			this.renderMap(params);
		},

		renderMap : function (params) {
			var view	=	this;
			view.center	=	params.center;

			view.map_options = {
				'zoom': parseInt(je_jobmap.zoom),
				'center': params.center,
				'mapTypeId': google.maps.MapTypeId.ROADMAP
			};

			view.markers		=	_.clone (params.markers);
			if(view.markerCluster)
				view.markerCluster.clearMarkers ();

			view.infoWindow 	= new google.maps.InfoWindow();

			this.map = new google.maps.Map(document.getElementById("modal_map_inner"), view.map_options);

			// view.markerCluster	=	new MarkerClusterer(view.map, view.markers);

		},

		clearMap : function () {
			for (var i = 0, marker; marker = this.markers[i]; i++) {
			    marker.setMap(null);
			}
		},
		
		changeCat : function (event) {
			event.preventDefault();
			//this.filterMap ();

		},

		searchJob : function(event){
			event.preventDefault();

			this.filterMap();
		},

		filterMap : function () {
			var view 	=	this;
			var params	=	{
				'job_category' : this.$el.find('.select_category select').val(),
				'job_type' : this.$el.find('.select_jobtype select').val(),
				'location' : this.$el.find('.center').val()
			};

			view.search_params	=	_.extend (params, {action : 'je_jobmap_filter'});

			//

			$.ajax ({
				type 	: 'get',
				url 	: et_globals.ajaxURL,
				data 	: params ,
				beforeSend  : function () {

				},
				success : function (resp) {		
					view.clearMap();
					view.markerCluster.clearMarkers ();				
					if(typeof resp.data !== 'undefined' && resp.data.length > 0 ) {						
						//console.log(resp.data);
						var data	=	resp.data;
						view.markers = [];				
						//google.maps.event.trigger(view.map, 'resize');
						for (var i = 0; i < data.length; i++) {
							var content	=	view.template (data[i]);
							// console.log(content);
							
							var latLng = new google.maps.LatLng(data[i].lat, data[i].lng);
							if (i == 0) {
								var center	=	latLng;
							}

							var marker = new google.maps.Marker({ 'position': latLng , title : data[i]['post_title'] });

							view.attachMarkerInfowindow ( marker , content );

							view.markers.push(marker);
							
						}	
						
						view.map.setCenter(center);
						view.center = center;
					
						view.markerCluster = new MarkerClusterer(view.map, view.markers);
					}
				}
			});
		
		},

		clearMap : function () {
			for (var i = 0, marker; marker = this.markers[i]; i++) {
			    marker.setMap(null);
			}
		},

		attachMarkerInfowindow : function ( marker, content ) {
			
			var view	=	this;
			google.maps.event.addListener(marker,'click',function() {
				view.infoWindow.setContent(content);
				view.infoWindow.open(this.map,marker);
			});
		}


	});



	JobEngine.Views.JobMap	=	Backbone.View.extend({
		el : 'div.wrapper' ,
		template : _.template($('#je_jobmap_template').html()),
		map : null,
		map_options	: {},
		events : {
			'change .jobmap form input.center' : 'changeCenter',
			'change .jobmap form input.zoom' : 'changeZoom',
			'click #zoom-control-plus' : 'increaseZoom'
		},

		initialize : function () {


			pubsub.on('je:indexFilter', this.filterMap, this);
			pubsub.on('je_jobmap:modalSearchJob', this.fillParams , this );
			
			var view	=	this;
			view.map_options = {
				'zoom': parseInt(je_jobmap.zoom),
				'scrollwheel': false,  
			//	'center': view.center,
				'mapTypeId': google.maps.MapTypeId.ROADMAP,
				'maxZoom'  : 13 ,
				'minZoom'  : 3 ,
 				streetViewControl:false,
				panControl: false,
				zoomControlOptions: {
			        style: google.maps.ZoomControlStyle.LARGE,
			        position: google.maps.ControlPosition.LEFT_BOTTOM
			    }
			}

			view.markers		=	[];
			view.markerCluster	=	[];
			view.infoWindow 	= new google.maps.InfoWindow({autoPan : true });

			this.map = new google.maps.Map(document.getElementById("je_jobmap"), view.map_options);
 	



			if($('.jobmap').find('form').length > 0) {
				// view.map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push( $('.jobmap').find('form').get(0) );
			}

			if(!je_jobmap.lat) {
				GMaps.geocode({address: je_jobmap.center , callback: function(results, status) {	
						if (status == 'OK') {
							view.center = results[0].geometry.location;
							view.map.setCenter(view.center);
							//view.map_options.center =	view.center;
						}	
					}
				});
			} else {
				var latLng	=	new google.maps.LatLng(je_jobmap.lat, je_jobmap.lng );
				view.map.setCenter( latLng );
				view.center = latLng;
			}



			var data	=	{action : 'je_jobmap_fetch_jobs'}
			if( je_jobmap.is_single_job ) {
				data	= {action : 'je_jobmap_fetch_jobs_insingle' , 'job' : JSON.parse(this.$('#job_data').html()) }
			}


			this.renderMap(data);

// --


			 // slider target
		    var target = $('#zoom-slider #zoom-path');

		    // create the slider
		    target.slider({  // after load of jquery-UI)
		        orientation: 'horizontal',
		        value: parseInt(je_jobmap.zoom),
		        min: 6,
		        max: 13,
		        step: 1,
		        animate: true,
		        stop: function() {
		            this.map.setZoom( parseInt( target.slider('option','value')) );
		        }
		    });

		    // update slider on zoom with double click
			  google.maps.event.addListener( this.map, 'zoom_changed', function() {

		    	target.slider('option','value', this.map.getZoom()); 
		    	});

		    // maximum slider value
		    // var maxValue = parseInt(target.slider('option', 'max'));

		    // minimum slider value
		    // var minValue = parseInt(target.slider('option', 'min'));
//--



		},


		increaseZoom : function() {
			event.preventDefault;
			// current slider value
	        var currentValue = parseInt(target.slider('option','value'));

	        // current slider value increased by 1
	        var newValue = currentValue+1;

	        // is new value greater than max value?
	        if(newValue = minValue) {
	            // increase slider value
	            target.slider('option', 'value', newValue);
	            map.setZoom(newValue);
	        } else {
	            // slider is at max value
	            target.slider('option', 'value', minValue);
	            map.setZoom(minValue);
	        }
	        return false;
		},

		renderMap : function (data) {
			var view	=	this;
			$.ajax ({
				type 	: 'get',
				url 	: et_globals.ajaxURL,
				data 	: data ,
				beforeSend  : function () {

				},
				success : function (resp) {						
					if(typeof resp.data !== 'undefined') {
						//console.log(resp.data);
						var data	=	resp.data;
						view.markers = [];				
						
						for (var i = 0; i < data.length; i++) {
							var content	=	view.template (data[i]);
							// console.log(content);
							var latLng = new google.maps.LatLng(data[i].lat, data[i].lng);
							var marker = new google.maps.Marker({ map:view.map, 'position': latLng , title : data[i]['post_title'] });

							view.attachMarkerInfowindow ( marker , content );

							view.markers.push(marker);
							
						}				
						// view.markerCluster = new MarkerClusterer(view.map, view.markers);

						if(typeof resp.center != 'undefined') {
							var latLng	=	new google.maps.LatLng( resp.center.lat, resp.center.lng );
							view.map.setCenter( latLng );
							view.center = latLng;
						}
					}
				}

			});
		},

		fillParams : function (params) {
			if($('#header-filter').length > 0 ) {
				$('.filter-jobcat li a').removeClass('active');
				$('.filter-jobcat li a[data='+params.job_category).click();
				
				$('.filter-jobtype li a').removeClass('active');
				$('.filter-jobtype li a[data='+params.job_type).click();

				$('#header-filter').find('input.center').val(params.location);
			}
		},

		filterMap : function (params) {
			var view = this,
				param	=	_.clone(params);
			var param	=	_.extend (param, {action : 'je_jobmap_filter'});
			
			$.ajax ({
				type 	: 'get',
				url 	: et_globals.ajaxURL,
				data 	: param ,
				beforeSend  : function () {

				},
				success : function (resp) {						
					if(typeof resp.data !== 'undefined' && resp.data.length > 0 ) {
					
						view.clearMap();
						view.markerCluster.clearMarkers ();
						//console.log(resp.data);
						var data	=	resp.data;
						view.markers = [];				
						//google.maps.event.trigger(view.map, 'resize');
						for (var i = 0; i < data.length; i++) {
							var content	=	view.template (data[i]);
							// console.log(content);
							
							var latLng = new google.maps.LatLng(data[i].lat, data[i].lng);
							if (i == 0) {
								var center	=	latLng;
							}

							var marker = new google.maps.Marker({ 'position': latLng , title : data[i]['post_title'] });

							view.attachMarkerInfowindow ( marker , content );

							view.markers.push(marker);
							
						}	
						
						view.map.setCenter(center);
						view.center = center;
					
						view.markerCluster = new MarkerClusterer(view.map, view.markers);
					}
				}
			});
			
		},

		clearMap : function () {
			for (var i = 0, marker; marker = this.markers[i]; i++) {
			    marker.setMap(null);
			}
		},

		enlargeMap : function (e) {
			e.preventDefault();
			// if( typeof this.modal === 'undefined') {
			// 	this.modal	=	new JobEngine.Views.MapModal ();
			// }
			// this.modal.openModalMap( { markers : this.markers , center : this.center } );
		},

		updateWidget : function  (e) {
			// if($('.jobmap form').length > 0 ) {
				// $.ajax({
				// 	type : 'post',
				// 	data : $('.jobmap form').serialize () + '&action=save-widget&sidebar='+ $(e.currentTarget).parents('.ui-sortable').attr('id'),
				// 	url : et_globals.ajaxURL,
				// 	beforeSend : function () {},
				// 	success : function () {}
				// });
			// }
			
		},

		changeCenter : function  (e) {
			var $target	=	$(e.currentTarget);
			var view	=	this;
			if( typeof this.map !== 'undefined' ) { 
				GMaps.geocode({address: $target.val() , callback: function(results, status) {	
					if (status == 'OK') {
						view.center = results[0].geometry.location;
						view.map.setCenter(view.center);
						$('.jobmap input.lat').val(view.center.lat());
						$('.jobmap input.lng').val(view.center.lng());
						view.markerCenter = new google.maps.Marker({
							map : view.map,
							position : results[0].geometry.location,
							draggable : true,
							title : 'Drag me to specify your correct center'
						});

						google.maps.event.addListener(view.markerCenter,'dragend',function (e) {
							$('.jobmap input.lat').val(this.position.lat());
							$('.jobmap input.lng').val(this.position.lng());

							// view.updateWidget(e);
							// console.log(e.position);
						});

						// view.updateWidget(e);
					}	
				}
				});
			}
		},

		changeZoom : function (e) {
			if( typeof this.map !== 'undefined' ) {
				this.map.setZoom (parseInt( $(e.currentTarget).val() ));
				this.updateWidget(e);
			}
		},

		attachMarkerInfowindow : function ( marker, content ) {
			
			var view	=	this;
			google.maps.event.addListener(marker,'click',function() {
				view.infoWindow.setContent(content);
				view.infoWindow.open(this.map,marker);
			});
		}
	});

	$(document).ready(function () {
		
		JobEngine.Jobmap	=	new JobEngine.Views.JobMap();
		
	});
} (jQuery));