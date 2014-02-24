(function ($) {
	
jQuery(document).ready(function($){
	JobEngine.Views.Index = Backbone.View.extend({
		el : $('body'),

		events: {
			'click div.button-more button'	: 'loadMore',
			'keyup input.search-box'		: 'searchJob',
			'change .select_category' 		: 'searchCategory', //added category filtering - SM
			'change .select_jobtype'		: 'searchJobtype', //added jobtype filtering - SM
			'click ul.filter-joblist li a:not(".et_processing")' : 'filterHandler'
		},

		initialize: function(){
			var latest_jobs_data	= JSON.parse( this.$('#latest_jobs_data').html() ),
				pending_jobs_data	= this.$('#pending_jobs_data'),
				comparator;

			this.previous = {
				's' 			: this.$('input.search-box[name=s]').val(),
				'job_category'	: this.$('input.select_category').val(),
				'job_type'		: this.$('input.select_jobtype').val(),
				'job_location' 	: this.$('input.search-box[name=job_location]').val()
			};

			_.bindAll(this);

			this.companies_data		= JSON.parse( this.$('#companies_data').html() );

			if ( pending_jobs_data.length !== 0 ){
				pending_jobs_data	= JSON.parse( pending_jobs_data.html() );

				// setup comparator function for pending job collection
				comparator	= function(job){
					var jobDate	= new Date(job.get('post_date'));
					return -(parseInt(job.get('job_paid') + "" + jobDate.getTime(),10));
				};

				// init pending job collection & matching pending job view
				this.pendingJobs	= new JobEngine.Collections.Jobs( pending_jobs_data, {comparator:comparator} );
			}
			else{

				// init an empty pending job collection
				this.pendingJobs	= new JobEngine.Collections.Jobs();

				// set the comparator for this empty collection
				this.pendingJobs.comparator = comparator;
			}

			this.pendingJobsView	= new JobEngine.Views.JobListView( {el: $('#pending_jobs_container'), collection: this.pendingJobs} );

			// init the active job collection & the other job collection
			$isPublishedList = (latest_jobs_data.status === 'publish');
			if ( $isPublishedList ){
				this.activeJobs	= new JobEngine.Collections.Jobs(latest_jobs_data.jobs);
				this.curJobs	= this.activeJobs;
				this.otherJobs	= new JobEngine.Collections.Jobs();
			}
			else{
				this.otherJobs	= new JobEngine.Collections.Jobs(latest_jobs_data.jobs);
				this.curJobs	= this.otherJobs;
				this.activeJobs	= new JobEngine.Collections.Jobs();
			}

			// init two view bind to the same DOM element but work with different collections
			this.activeJobsView	= new JobEngine.Views.JobListView({el: $('#latest_jobs_container'), collection: this.activeJobs});
			this.otherJobsView	= new JobEngine.Views.JobListView({el: $('#latest_jobs_container'), collection: this.otherJobs});

			// temporarily pause other view than active one
			if ( $isPublishedList ){
				this.otherJobsView.undelegateEvents();
			}
			else{
				this.activeJobsView.undelegateEvents();
			}

			// initialize the condition of the current list
			this.curJobs.setData(this.buildParams());

			// define events handlers for app view
			pubsub.on('je:job:afterRemoveJobView', this.afterRemoveJobView, this);
			pubsub.on('je:job:afterApproveJob', this.afterApproveJob, this);
			pubsub.on('je:job:onReject', this.onRejectJob, this);
			pubsub.on('je:job:afterRejectJob', this.afterRejectJob, this);
			pubsub.on('je:job:afterToggleFeature', this.afterToggleFeature, this);
			pubsub.on('je:job:afterArchiveJob', this.afterArchiveJob, this);
			pubsub.on('je:job:afterRemoveJob', this.afterRemoveJob, this);

			if( this.$('#modal_edit_job').length > 0 && ( typeof this.editModalView === 'undefined' || !(this.editModalView instanceof JobEngine.Views.Modal_Edit_Job) ) ){
				this.editModalView	= new JobEngine.Views.Modal_Edit_Job();
				pubsub.on('je:job:onEdit', this.onEditJob, this);
				pubsub.on('je:job:afterEditJob', this.afterEditJob, this);
			}

		},
		
		// after removing a job view, we check its status and add it to the correct collection
		afterRemoveJobView	: function(job){
			var status	= job.get('status'),
				$status_con	= this.$('ul.filter-jobstatus');

			switch(status){
				case 'pending':
					this.pendingJobs.add(job);
					if (this.pendingJobs.length > 0 ){
						if(this.pendingJobsView.$el.is(':hidden')){
							this.pendingJobsView.$el.fadeIn('fast');
						}
					}
					break;
				case 'publish':
					if($status_con.find('li a.active').length === 0){
						this.activeJobs.unshift(job);
					}
					break;
				default:
					if($status_con.find('li.status-' + status + ' a.active').length > 0){
						this.otherJobs.add(job);
					}
					break;
			}
		},

		// after a job is approved, we remove it from the pending job collection
		afterApproveJob	: function(model,res){
			var cats	= model.get('categories'),
				self	= this,
				$status_con	= this.$('ul.filter-jobcat'),
				i;

			this.pendingJobs.remove(model);

			// add new count to the status list
			if(_.isArray(cats)){
				for(i=0;i<cats.length;i++){
					if('slug' in cats[i]){
						$status_con.find('li.cat-' + cats[i].slug + ' span.count').each(self.countUp);
					}
				}
			}
		},

		// open the modal Reject job, init it if not having any instance yet
		onRejectJob	: function( args ){
			if( typeof this.rejectModalView === 'undefined' || !(this.rejectModalView instanceof JobEngine.Views.ModalReject) ){
				this.rejectModalView = new JobEngine.Views.ModalReject();
			}
			this.rejectModalView.onReject(args);
		},

		onEditJob	: function(model){
			var author_id	= model.get('author_id'),
				author		= ( author_id in this.companies_data ) ? this.companies_data[author_id] : null;

			this.editModalView.onEdit(model,author);
		},

		// after a job is rejected, remove it from the pending job collection & count up the number of rejected jobs
		afterRejectJob : function(model,res){
			this.pendingJobs.remove(model);

			// increase number of rejected jobs
			$('ul.filter-jobstatus > li.status-reject').each(function(){
				var current = $(this),
					counter	= current.find('span.count'),
					count	= parseInt(counter.html(),10);

				count++;
				counter.html(count);
			});
		},

		afterArchiveJob	: function(model,res,prevStatus){
			var $filter_jobstatus	= $('ul.filter-jobstatus > li.status-archive'),
				$isArchivedList	= $filter_jobstatus.find('a.active').length;
			if($isArchivedList === 0){
				this.curJobs.remove(model);
			}

			// add new count to the status list
			$filter_jobstatus.each(function(){
				var current = $(this),
					counter	= current.find('span.count'),
					count	= parseInt(counter.html(),10);

				count++;
				counter.html(count);
			});

			if(prevStatus === 'reject'){
				// add new count to the status list
				$('ul.filter-jobstatus > li.status-reject').each(function(){
					var current = $(this),
						counter	= current.find('span.count'),
						count	= parseInt(counter.html(),10);

					count--;
					counter.html(count);
				});
			} 
			else if (prevStatus === 'pending'){
				this.pendingJobs.remove(model);
			}
		},
		afterRemoveJob : function (model,res,prevStatus) {

		}, 

		afterToggleFeature	: function(model,res){
			var col	= model.collection;
			col.remove(model);
		},

		filterHandler	: function(event){
			event.preventDefault();
			var $target	= $(event.currentTarget);

			if(!$target.hasClass('et_processing')){

				$target.addClass('et_processing');

				var opened = $target.hasClass('active');

				// remove active class in other value
				$target.closest('aside').find('li a.active').removeClass('active');

				// style the status of the filter
				if (!opened)
					$target.toggleClass('active');		

				// location
				if ($target.attr('rel') == 'location'){
					if ($target.hasClass('active')) 
						$('#header-filter input[name=job_location]').val($target.attr('data'));
					else 
						$('#header-filter input[name=job_location]').val('');
				}
				
				this.filter({
					success : function(){
						setTimeout(function(){
							$target.removeClass('et_processing');
						},50);
					}
				});
			}
		},

		searchJob : function(event){
			event.preventDefault();
			var	appView 	= this,
				element 	= $(event.currentTarget),
				key 		= element.attr('name')
				value 		= $(event.currentTarget).val();
			if ( value == this.previous[key] )
				return false;
			else {
				this.previous[key] = value;
			}
			// hide demonstration when type a keywork
			if (!$('.headline').is(':hidden'))
				$('.headline').slideUp();

			if ( typeof this.t !== 'undefined'){
				clearTimeout(this.t);
			}

			if (element.attr('name') == 'job_location'){
				$('#location_filter li a').removeClass('active');
				$('#location_filter li a[data]').filter(function(){
					return $(this).attr('data').toLowerCase() == element.val().toLowerCase();
				}).toggleClass('active');
			}


			this.t = setTimeout(function(){
				appView.filter();
			}, 700);
		},

		searchCategory : function(event){
			event.preventDefault();
			var appView = this,
				element 	= $(event.currentTarget),
				key 		= element.attr('name')
				value 		= $(event.currentTarget).val();

			if ( value == this.previous[key] )
				return false;
			else {
				this.previous[key] = value;
			}	

			if(!element.hasClass('et_processing')){

				element.addClass('et_processing');

				this.filter({
					success : function(){
						setTimeout(function(){
							element.removeClass('et_processing');
						},50);
					}
				});
			}

		},

		searchJobtype : function() {
			event.preventDefault();
			var appView = this,
				element 	= $(event.currentTarget),
				key 		= element.attr('name')
				value 		= $(event.currentTarget).val();
				
			if ( value == this.previous[key] )
				return false;
			else {
				this.previous[key] = value;
			}	

			if(!element.hasClass('et_processing')){

				element.addClass('et_processing');

				this.filter({
					success : function(){
						setTimeout(function(){
							element.removeClass('et_processing');
						},50);
					}
				});
			}


		},


		buildParams : function(){
			var params	= {},
				$activeStatus	= this.$('ul#status_filter a.active'),
				$activeJobType	= this.$('input.select_jobtype'),
				$activeJobCat	= this.$('input.select_category'),
				$activeLocation = this.$('ul#location_filter a.active'),
				$filterInput	= this.$('.job-searchbox');


			var $joblist_filter	=	this.$('ul.filter-joblist');

			_.each (this.$('.widget > ul.job-filter'), function (tax) { 
				var etax		=	$(tax).attr('data-tax');

				if(etax) {
					var $activeTax 	=	 $(tax).find('a.active');
					var arg	=	$.map( $activeTax, function(item){
						return $(item).attr('data');
					});
					arg	= arg.join(',');
					if(arg != '')
					params[etax] = arg;
				}
			});



//
			if($activeJobType.length > 0){
				params.job_type	= $.map( $activeJobType, function(item){
					return $(item).attr('data');
				});
			}


			if($activeJobCat.length > 0){
				params.job_category	= $.map( $activeJobCat, function(item){
					return $(item).attr('data');
				});


			}
//
			if($activeStatus.length > 0){
				params.status	= $.map( $activeStatus, function(item){
					return $(item).attr('data');
				});
				this.curJobs	= this.otherJobs;
				this.activeJobsView.undelegateEvents();
				this.otherJobsView.delegateEvents();
			}
			else{
				params.status	= ['publish'];
				this.curJobs	= this.activeJobs;
				this.otherJobsView.undelegateEvents();
				this.activeJobsView.delegateEvents();
			}

			$.each( $filterInput, function(){
		
				var $this = jQuery(this),
					inputName	= $this.attr('name'),
					placeholder	= $this.attr('placeholder'),
					inputVal	= $this.val();

				if( inputVal !== '' && inputVal !== placeholder ){
					if( inputName === 'job_location' ){
						params.location	= inputVal;
					}
					else{
						params[inputName]	= inputVal;
					}
				}
			});

			return params;
		},

		filter : function(options){
			var params = this.buildParams();
			

			// set the condition to query & get the new collection
			this.curJobs.setData(params);
			this.curJobs.filter(options);


			pubsub.trigger( 'je:indexFilter' , params );
//
			if ('job_type' in params){
				params.job_type	= params.job_type.join(',');
			}


			if ('job_category' in params){
				params.job_category	= params.job_category.join(',');
			}
//
			if ('status' in params){
				params.status	= params.status.join(',');
				// not showing status=publish in link
				if(params.status === 'publish'){
					delete params.status;
				}
			}
			if ('paged' in params && params.paged === 1){
				delete params.paged;
			}

			if(params && Modernizr && Modernizr.history){
				Backbone.history.navigate("?" + $.param(params, true) );
			}
		},

		loadMore: function(event){
			var $target	= $(event.currentTarget);

			event.preventDefault();
			
			if(!$target.hasClass('et_processing')){

				$target.addClass('et_processing');

				this.curJobs.nextPage({
					success : function(col,res){

						// remove loadmore if all jobs are fetched
						if ( col.paginateData.paged >= col.paginateData.total_pages ){
							$('div.button-more').hide();
						}
						else{
							$('div.button-more').show();
						}

						$target.removeClass('et_processing');

					}
				});
			}
		},

		afterEditJob	: function(model){
			var cur_cats	= $.map(model.get('categories'),function(cur,i){
					return cur.slug;
				}), // get the array of new category slugs
				prev_cats	= model.get('prev_cats'),
				$cat_con	= this.$('ul.filter-jobcat'),
				$status_con	= this.$('ul.filter-jobstatus'),
				prev_status	= model.get('prev_status'),
				cur_status	= model.get('status'),
				self		= this,
				$target,noOfActiveStatus,countDown,countUp;

			if(prev_status !== cur_status){
				// remove jobs in pending section and move to right section
				if (prev_status == 'pending'){
					if (cur_status == 'publish')
						pubsub.trigger('je:job:afterApproveJob', model);
					else if (cur_status == 'reject')
						pubsub.trigger('je:job:afterRejectJob', model);
					else if (cur_status == 'archive')
						this.pendingJobs.remove(model);
				}

				noOfActiveStatus	= $status_con.find('li a.active').length;

				// remove the job from current collection in these conditions
				if( (prev_status==='publish' && noOfActiveStatus===0) || noOfActiveStatus===1 ||
					(noOfActiveStatus===2 && cur_status!=='reject' && cur_status!=='archive')
				){
					this.curJobs.remove(model);
				}

				// add new count to category list
				if(prev_status === 'publish'){
					$.each(prev_cats,function(i,val){
						$cat_con.find('li.cat-' + val + ' > a span.count').each(self.countDown);
					});
				}

				if(cur_status === 'publish'){
					$.each(cur_cats,function(i,val){
						$cat_con.find('li.cat-' + val + ' > a span.count').each(self.countUp);
					});
				}

				// add new count to the status list
				$status_con.find('li.status-' + prev_status + ' span.count').each(this.countDown);

				$status_con.find('li.status-' + cur_status + ' span.count').each(this.countUp);
			}
			else{
				if( cur_status === 'publish' ){
					countDown	= _.difference( prev_cats, cur_cats );
					$.each(countDown,function(i,val){
						$cat_con.find('li.cat-' + val + ' > a span.count').each(self.countDown);
					});

					countUp		= _.difference( cur_cats, prev_cats );
					$.each(countUp,function(i,val){
						$cat_con.find('li.cat-' + val + ' > a  span.count').each(self.countUp);
					});
				}
			}
		},

		countUp	: function(index,element){
			var $this	= $(element),
				count	= parseInt($this.html(),10);
			count++;
			$this.html(count);
		},

		countDown	: function(index,element){
			var $this	= $(element),
				count	= parseInt($this.html(),10);
			count--;
			$this.html(count);
		}
	});

	JobEngine.Routers.Index = Backbone.Router.extend({
		routes : {
			"filter/:value" : 'filterCompany'
		},
		initialize: function(){},
		filterCompany : function(value){
			$('ul.list-alphabet li a[data=' + value.toUpperCase() + ']').trigger('click');
		}
	});

	

	new JobEngine.Views.Index();

	if ( typeof Modernizr !== 'undefined' && Modernizr.history === true ){
		new JobEngine.Routers.Index();
		Backbone.history.start({pushState: true, hashChange: false, root : et_index.routerRootIndex});
	}


});
})(jQuery);