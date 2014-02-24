(function($) {
    jQuery(document).ready(function($) {

        // modify validator: add new rule for username
        jQuery.validator.addMethod("username", function(value, element) {
            var ck_username = /^[A-Za-z0-9_]{1,20}$/;
            return ck_username.test(value);
        });



        // VIEW: POST JOB //////////////////////////////////////////////////////////////
        // define the view for this post_job page
        JobEngine.Views.Post_Job = Backbone.View.extend({
            el: $('div#post_job'),
            job: {},
            tpl_login_success: null,
            // event handlers for user interactions
            events: {
                // general
                'click div.toggle-title': 'selectStep',
                // step: auth
                'click div#step_auth .tab-title > div': 'selectTab',
                'submit div#step_auth form': 'submitAuth',
                'click div.login_success a#logout_link': 'logoutCompany',
                // step: job
                'keyup input#full_location': 'geocoding',
                'blur input#full_location': 'resetLocation',
                //'submit form#job_form': 'submitJob',
                'click form#job_form .save_job': 'submitJob',
                // step: package
                'click button.select_plan': 'selectPlan',
                //choose Job_Type (AA /FPJS )	
                'change #job_types': 'selectJobType',
                'change .payment_plan .announcement input[type="checkbox"]': 'selectTypePostJob',
                // step: duration
                'click button.select_duration': 'selectDuration',
                // step: payment
                'click div.select_payment': 'selectPayment',
                'click div.select_payment_debit': 'showFormDebit',
                'click div.payment_debit': 'PaymentDebit',
                // 'click #add_sample' 					: 'editSample',
                // 'change #add_sample_input' 				: 'changeAddress',
                // 'keyup #add_sample_input' 				: 'keyupAddress',
                'click .apply input:radio': 'switchApplyMethod',
                'change #user_url': 'autoCompleteUrl'
            },
            // run once when initialize the view
            initialize: function() {
                _.bindAll(this);

                // setup the post-job view
                this.setupView();

                var job_data = this.$('#job_data').html();	// job_data: ID of current Job
                if (!!job_data) {
                    job_data = JSON.parse(job_data);
                }
                // get plans
                this.plans = JSON.parse($('#package_plans').html());

                // get helperobject with new job-allocation
                this.jobhandler = JSON.parse($('#jobhandler').html());

                // initialize the job model
                this.job = new JobEngine.Models.Job(job_data);
                this.job.author = JobEngine.app.currentUser;

                // bind the event again because we assign this.job.author to current user
                this.job.author.on('change', this.job.updateJobAuthor, this.job);

                // if the current user model of the app has changed, update it to the view
                this.job.author.on('change:id', this.updateAuthor, this);

                this.job.on('change:is_free', this.updateProcess, this);

                // hide step 3 if free plan is selected
                if (this.job.get('job_paid') === "2") {
                    this.removePaymentStep();
                }

                var amount = $('#step_package').find('.selected').find('button').attr('data-price');
                if (parseFloat(amount) == 0) { // is free remmove payment process
                    this.job.set({is_free: 1});
                    this.removePaymentStep();
                } else {
                    this.showPaymentStep();
                }
                // set author param for uploader
                if (this.job.author.has('id')) {
                    this.logo_uploader.updateConfig({
                        'multipart_params': {
                            'author': this.job.author.get('id')
                        }
                    });
                }
                // if job package is changed and it is not a new job, sync to server
                this.job.on("change:job_package", function() {
                    if (!this.isNew()) {
                        console.log('after change callback:' + this.get('featured'));
                        this.save();
                    }
                });

                //
                // this.loadingPostJob = new JobEngine.Views.LoadingButton({el : '#submit_job'});
                // this.loadingLogin = new JobEngine.Views.LoadingButton({el : '#submit_login'});
                // this.loadingRegister = new JobEngine.Views.LoadingButton({el : '#submit_register'});
                this.loadingBtn = null;

                // reset nonce for user logo upload
                pubsub.on('je:response:auth', this.updateLogoNonce, this);
                this.bind('waitingPostJob', this.waitingPostJob, this);
                this.bind('endWaitingPostJob', this.endWaitingPostJob, this);
                this.bind('waitingAuth', this.waitingAuth, this);
                this.bind('endWaitingAuth', this.endWaitingAuth, this);
                this.bind('waitingPayment', this.waitingPayment, this);
                this.bind('endwaitingPayment', this.endWaitingPayment, this);

                // var ed	=	new tinymce.Editor('applicant_detail', {
                // 	   	mode : 'none',
                // 		theme : "advanced",
                // 		dialog_type : 'modal',
                // 		plugins : "paste,etLink,autolink,inlinepopups,wordcount,etHeading",
                // 		//language : "",
                // 		// Theme options
                // 		theme_advanced_buttons1 : "bold,|,italic,|,numlist,|,et_heading,|,etlink",
                // 		theme_advanced_buttons2	: "",
                // 		theme_advanced_buttons3 : "",
                // 		theme_advanced_buttons4  : "",
                // 		theme_advanced_toolbar_location : "top",
                // 		theme_advanced_toolbar_align : "left",
                // 		theme_advanced_resizing : true,
                // 		theme_advanced_statusbar_location : "bottom",
                // 		theme_advanced_resizing_use_cookie : false,
                // 		skin : "o2k7",
                // 		skin_variant : 'black',
                // 		height : "80",
                // 		content_css : et_globals.jsURL+"/lib/tiny_mce/content.css",
                // 		theme_advanced_resizing_max_width : 470,
                // 		onchange_callback : function (ed) {
                // 			tinyMCE.triggerSave();
                // 			$("#" + ed.id).valid();
                // 		},
                // 		setup	: function(ed){
                // 			ed.onChange.add(function(ed, l) {
                // 				var content	= ed.getContent();
                // 				if(ed.isDirty() || content === '' ){
                // 					ed.save();
                // 					$(ed.getElement()).blur(); // trigger change event for textarea

                // 				}
                // 			});

                // 			// We set a tabindex value to the iframe instead of the initial textarea
                // 			ed.onInit.add(function() {
                // 				var editorId = ed.editorId,
                // 					textarea = $('#'+editorId);
                // 				$('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
                // 				textarea.attr('tabindex', null);
                // 			});
                // 		}
                // 	});
                // ed.render();

            },
            // PAGE BEHAVIORS //////////////////////////////////////////////////////////////
            // helper function: setup the view on 1st load
            setupView: function() {

                // init logo upload
                var that = this,
                        $user_logo = this.$('#user_logo_container');

                // init the map for location input only when it is not initialized
                if (typeof this.map === 'undefined' && typeof GMaps !== 'undefined') {
                    this.map = new GMaps({
                        div: '#map',
                        lat: 73.96487765189111,
                        lng: -133.6312064,
                        zoom: 1,
                        panControl: false,
                        zoomControl: false,
                        mapTypeControl: false
                    });
                    if ($('#location_lat').val() !== '' && $('#location_lng').val() !== '') {
                        var lat = $('#location_lat').val(),
                                lng = $('#location_lng').val();
                        this.map.setCenter(lat, lng);
                        this.map.addMarker({
                            lat: lat,
                            lng: lng,
                            draggable: true,
                            dragend: function(e) {
                                that.$('#location_lat').val(e.position.$a);
                                that.$('#location_lng').val(e.position.ab);
                            }
                        });
                    }
                }

                var blockUi = new JobEngine.Views.BlockUi();
                this.logo_uploader = new JobEngine.Views.File_Uploader({
                    el: $user_logo,
                    uploaderID: 'user_logo',
                    thumbsize: 'company-logo',
                    multipart_params: {
                        _ajax_nonce: $user_logo.find('.et_ajaxnonce').attr('id'),
                        action: 'et_logo_upload'
                    },
                    cbUploaded: function(up, file, res) {
                        if (res.success) {
                            //that.job.author.set('user_logo',res.data,{silent:true});
                        } else {
                            pubsub.trigger('je:notification', {
                                msg: res.msg,
                                notice_type: 'error'
                            });
                            // console.log (res);
                        }
                    },
                    beforeSend: function(element) {
                        blockUi.block($user_logo.find('.company-thumbs'));
                    },
                    success: function() {
                        blockUi.unblock();
                    }
                });

                this.register_validator = this.$('form#register').validate({
                    rules: {
                        reg_user_name: {
                            required: true,
                            username: true
                        },
                        reg_email: {
                            required: true,
                            email: true
                        },
                        reg_pass: 'required',
                        reg_pass_again: {
                            required: true,
                            equalTo: "#reg_pass"
                        }
                    },
                    messages: {
                        reg_user_name: {
                            username: et_post_job.reg_user_name
                        }
                    }

                });

                this.login_validator = this.$('form#login').validate({
                    rules: {
                        log_email: "required",
                        log_pass: "required"
                    }
                });

                this.job_form_validator = this.$('form#job_form').validate({
                    ignore: "",
                    rules: {
                        title: "required",
                        //full_location	: "required",
                        display_name: "required",
                        content: "required",
                        user_url: {
                            required: true,
                            url: true
                        }
                    },
                    errorPlacement: function(label, element) {
                        // position error label after generated textarea
                        if (element.is("textarea")) {
                            label.insertAfter(element.next());
                        } else {
                            label.insertAfter(element)
                        }
                    }
                });

                // init tinyMCE
                // tinyMCE.execCommand('mceAddControl', false, 'content');
                // tinyMCE.execCommand('mceAddControl', false, 'applicant_detail');

                // setup the template login success for step 2
                this.tpl_login_success = new _.template(
                        '<div class="login_success">' +
                        et_globals.msg_login_ok +
                        '<span><a id="logout_link" href="#">' + et_globals.msg_logout + '</a></span>' +
                        '</div>');

                // hide all tabs except the active one
                this.$('div#step_auth')
                        .find('.tab-content > div:not(.current)').hide();

                // collapse all step & show the active step
                this.$('div.step:not(.current)')
                        .find('.toggle-content').hide();

                // don't break the chain
                return this;
            },
            // event handler: run when a user click on a step
            selectStep: function(event) {
                var step = this.$(event.currentTarget).closest('div.step'),
                        curStepIndex = this.$('div.step.current').index(),
                        flag = true; // flag all previous steps having been completed

                if (!step.hasClass('current')) {

                    // check if all previous steps having been completed or not
                    step.prevAll().each(function(i, ele) {
                        if (!jQuery(ele).hasClass('completed')) {
                            flag = false;
                            return false;
                        }
                    });
                    if (flag || step.index() < curStepIndex) {
                        this.showStep(step);
                    }
                    else {
                        pubsub.trigger('je:notification', {
                            msg: et_post_job.notice_step_not_allowed,
                            notice_type: 'error'
                        });
                    }
                }
            },
            reloadMap: function(args) {
                var that = this,
                        params = _.extend({}, args),
                        $locationtxt = this.$('#location');

                params.callback = function(results, status) {
                    var latlng, location_lat, location_lng;

                    if (status == 'OK') {
                        location_lat = that.$('#location_lat'),
                                location_lng = that.$('#location_lng');
                        latlng = results[0].geometry.location;
                        that.map.setZoom(12);
                        that.map.setCenter(latlng.lat(), latlng.lng());
                        that.map.removeMarkers();
                        that.map.addMarker({
                            lat: latlng.lat(),
                            lng: latlng.lng(),
                            draggable: true,
                            dragend: function(e) {
                                location_lat.val(e.position.$a);
                                location_lng.val(e.position.ab);
                            }
                        });
                        location_lat.val(latlng.lat());
                        location_lng.val(latlng.lng());

                        /*
                         // console.log(results[0].address_components); 
                         // display sample address 
                         length			=	results[0].address_components.length;
                         address			=	results[0].address_components;
                         full_address	=	results[0].formatted_address;
                         district		=	' ';
                         city			=	' ';
                         
                         
                         // find address area level 1 and level 2
                         
                         for ( i =0; i< length; i++) {
                         if(address[i].types[0] == 'administrative_area_level_2' && address[i].long_name !== 'undefined') {
                         district = address[i].long_name + ', ';
                         }
                         if(address[i].types[0] == 'administrative_area_level_1' && address[i].long_name !== 'undefined') {
                         city = address[i].long_name;
                         }
                         }
                         $locationtxt.html('"' + district + city + '"');
                         if ($locationtxt.is(':hidden'))
                         $locationtxt.show();
                         */
                    }
                    if (typeof args.callback === 'function') {
                        args.callback();
                    }
                };

                GMaps.geocode(params);
            },
            // helper function: show the needed step id
            showStep: function(step) {
                var that = this;

                // close all contents & remove active class of current title
                this.$('div.step')
                        .removeClass('current')
                        .find('.toggle-content').slideUp(200)
                        .end().find('.toggle-title').removeClass("bg-toggle-active");

                // show the selected step
                step.addClass('current')
                        .find('.toggle-title').addClass('bg-toggle-active')
                        .next().slideToggle(300);

                // show step check amount of job package to specify free
                var amount = $('#step_package').find('.selected').find('button').attr('data-price');
                if (parseFloat(amount) === 0) { // is free remmove payment process
                    this.job.set({is_free: 1});
                    this.removePaymentStep();
                } else {
                    this.showPaymentStep();
                }
                // refresh the map to fix its wrong display when we init the map in a hidden div
                if (step.attr('id') === 'step_job' && typeof GMaps !== 'undefined' && typeof this.map.refresh === 'function') {
                    // refresh map
                    this.map.refresh();

                    //reset center map if have lat and lng
                    if ($('#location_lat').val() !== '' && $('#location_lng').val() !== '') {

                        this.map.setZoom(12);
                        this.map.setCenter($('#location_lat').val(), $('#location_lng').val());

                    }

                    if (this.job.has('location_lat') && this.job.has('location_lng')) {

                        this.map.setZoom(12);
                        this.reloadMap({lat: this.job.get('location_lat'), lng: this.job.get('location_lng')});

                    }

                }

                // don't break the chain
                return this;
            },
            geocoding: function(event) {
                var that = this,
                        $location = $(event.currentTarget);

                if (typeof this.t !== 'undefined') {
                    clearTimeout(this.t);
                }

                this.t = setTimeout(function() {
                    that.reloadMap({address: $.trim($location.val())});
                }, 500);
            },
            resetLocation: function(event) {
                var $full = $(event.currentTarget),
                        $lat = this.$('#location_lat'),
                        $lng = this.$('#location_lng'),
                        $location = this.$('#location'),
                        $locationtxt = this.$('.address-note span');
                //console.log($location);
                // prevent loading geocode when user have just deleted full location
                if ($.trim($full.val()) === '') {
                    $lat.val('');
                    $lng.val('');
                    $location.val('');
                }
                else {
                    GMaps.geocode({
                        lat: $lat.val(),
                        lng: $lng.val(),
                        callback: function(results, status) {
                            var length, address, full_address, district, city, i;
                            if (status == 'OK') {
                                /*
                                 length			=	results[0].address_components.length;
                                 address			=	results[0].address_components;
                                 full_address	=	results[0].formatted_address;
                                 district		=	' ';
                                 city			=	' ';
                                 
                                 // find address area level 1 and level 2
                                 for ( i =0; i< length; i++) {
                                 if(address[i].types[0] == 'administrative_area_level_2' && address[i].long_name !== 'undefined') {
                                 district = address[i].long_name + ', ';
                                 }
                                 if(address[i].types[0] == 'administrative_area_level_1' && address[i].long_name !== 'undefined') {
                                 city = address[i].long_name;
                                 }
                                 }
                                 
                                 $location.val(district + city);
                                 $locationtxt.html('"' + district + city + '"');
                                 //$full.val(full_address);
                                 */
                            }
                        }
                    });
                }
            },
            markStepCompleted: function(step) {
                if (!step.hasClass('completed')) {
                    step.addClass('completed')
                            .find('.toggle-title').addClass('toggle-complete');
                }
                return this;
            },
            markStepIncompleted: function(step) {
                if (step.hasClass('completed')) {
                    step.removeClass('completed')
                            .find('.toggle-title').removeClass('toggle-complete');
                }
                return this;
            },
            /**
             * Display input field for company to change their address
             */
            editSample: function(event) {
                event.preventDefault();
                var target = $(event.currentTarget),
                        container = target.parent(),
                        content = target.html().substring(1, target.html().length - 1),
                        input = container.find('input').hide().val(content);

                target.hide();
                container.append(input.show()).addClass('editing');
            },
            changeAddress: function(event) {
                $('#location').val($(event.currentTarget).val());
            },
            keyupAddress: function(event) {
                var input = $('#add_sample_input'),
                        label = $('#add_sample'),
                        container = $('#add_sample_input').parent();
                // if trigger escape key
                if (event.which == 27) {
                    input.hide();
                    label.html('"' + input.val() + '"').show();
                    container.removeClass('editing');
                } else if (event.which == 13) { // if trigger enter key
                    input.hide();
                    label.html('"' + input.val() + '"').show();
                    container.removeClass('editing');
                }
            },
            // END PAGE BEHAVIORS //////////////////////////////////////////////////////////////

            // STEP: PACKAGE //////////////////////////////////////////////////////////////
            // run when a user select a plan
            // set the job model to selected plan
            // change the step status to completed
            // change to step 2

            selectJobType: function(event) {
                var that = this,
                        plan = '';
                this.$('div#step_package').find('input[type="checkbox"]').removeAttr('checked');
                this.$('div#step_package').find('.pricetag').fadeOut(function() {
                    //Todo :  job_type-6 -> 'job_type-'.ID  instead of 'assistenzarzt'
                    $(".type_regular").hide();
                    $(".type_reduced").hide();
                    $('#price_basis').html('kostenlos');
                    $('#price_premium').html('399.00 €');
                    if ($('select#job_types').val() == 'assistenzarzt') {
                        //update plan in jobhandler
                        plan = that.plans[ that.jobhandler['top']['regular']['weeks4'] ]['price'];
                        that.job.set({is_regular: 'regular'});
                        $(".type_regular").fadeIn();
                        // update prices in plan-overview

                        if (plan == parseInt(plan))
                            $('#price_top').html(plan + '.00 €');
                        else
                            $('#price_top').html(plan + '€');
                        // $('button[data-basis="top"]').attr( 'data-price', plan ); 	 

                    }
                    else {
                        plan = that.plans[that.jobhandler['top']['reduced']['weeks4']] ['price'];
                        that.job.set({is_regular: 'reduced'});
                        $(".type_reduced").fadeIn();

                        if (plan == parseInt(plan))
                            // update prices in plan-overview
                            $('#price_top').html(plan + '.00 €');
                        else
                            $('#price_top').html(plan + '€');
                        // $( 'button[data-basis="top"]').attr( 'data-price', plan ); 						
                    }


                });

                // this.job.author.on('change:id', this.updateAuthor, this);

                this.$('div#step_package').find('.pricetag').fadeIn();


            },
            selectTypePostJob: function(event) {
                event.preventDefault();
                var that = this,
                        plan = '',
                        price = '';
                that.$(event.currentTarget).parent().parent().parent().find('.pricetag').fadeOut(function() {

                    plan = that.$(event.currentTarget).parent().parent().parent().find('.pricetag').html();

                    if (plan == "kostenlos")
                        plan = 0;
                    plan = parseFloat(plan);

                    if ($('select#job_types').val() == 'assistenzarzt') {
                        var data = that.$(event.currentTarget).attr('data-regular');

                        if (that.$(event.currentTarget).is(':checked')) {
                            plan = parseFloat(plan) + parseFloat(data);
                        } else {
                            plan = parseFloat(plan) - parseFloat(data);
                        }

                        if (plan == parseInt(plan))
                            that.$(event.currentTarget).parent().parent().parent().find('.pricetag').html(plan + '.00 €');
                        else
                            that.$(event.currentTarget).parent().parent().parent().find('.pricetag').html(plan + ' €');
                        //console.log(plan);
                        that.$(event.currentTarget).parent().parent().parent().find('button').attr('data-job', plan);
                        that.$(event.currentTarget).parent().parent().parent().find('button').attr('data-price', plan);

                    } else {
                        var data = that.$(event.currentTarget).attr('data-reduced');

                        if (that.$(event.currentTarget).is(':checked')) {
                            plan = parseFloat(plan) + parseFloat(data);
                        } else {
                            plan = parseFloat(plan) - parseFloat(data);
                        }

                        if (plan == parseInt(plan))
                            that.$(event.currentTarget).parent().parent().parent().find('.pricetag').html(plan + '.00 €');
                        else
                            that.$(event.currentTarget).parent().parent().parent().find('.pricetag').html(plan + ' €');
                        //console.log(plan);
                        that.$(event.currentTarget).parent().parent().parent().find('button').attr('data-job', plan);
                        that.$(event.currentTarget).parent().parent().parent().find('button').attr('data-price', plan);
                    }
                });

                that.$(event.currentTarget).parent().parent().parent().find('.pricetag').fadeIn();

            },
            selectPlan: function(event) {
                var that = this;
                var $target = $(event.currentTarget),
                        $container = $target.closest('div.payment_plan'),
                        $step = $container.closest('div.step'),
                        nextStep = $step.nextAll().not(".completed").first(),
                        amount = $target.attr('data-price'),
                        planBasis = $target.attr('data-basis'),
                        planID = $target.attr('data-package'), // get the selected plan (need to update)
                        plan = this.plans[planID],
                        data_price_FTB = $target.attr('data-job');


                if (that.$(event.currentTarget).parent().parent().find(".announcement input.price_facebook").is(':checked'))
                    $("#checkout_form #post_facebook").attr('checked', 'checked');

                if (that.$(event.currentTarget).parent().parent().find(".announcement input.price_newsletter").is(':checked'))
                    $("#checkout_form #post_newsletter").attr('checked', 'checked');

                if (that.$(event.currentTarget).parent().parent().find(".announcement input.price_blog").is(':checked'))
                    $("#checkout_form #post_blog").attr('checked', 'checked');

                // mark the selected plan as selected
                $container.addClass('selected')
                        .siblings().removeClass('selected');

                if (!this.job.get('is_regular')) {
                    this.job.set({is_regular: 'regular'})
                } // 'assistenzarzt is default'

                // set plan if basis, top, premium
                this.job.set({job_basis: planBasis});

                // set job package 
                this.job.set({job_package: planID, featured: plan.featured});
                console.log(amount);
                // set the job package of job model & free status
                if (parseFloat(amount) == 0) {
                    //console.log(amount);
                    this.job.set({is_free: 1});
                }
                else {
                    this.job.set({is_free: 0});
                }
                console.log(this.job.toJSON());
                // ??
                if (typeof(this.job.author.get('payment_plans')) != 'undefined' &&
                        typeof(this.job.author.get('payment_plans')[planID]) != 'undefined' &&
                        this.job.author.get('payment_plans')[planID] > 0) {
                    this.job.set({is_free: 1});
                }


                // if edit job
                //if (typeof this.job.get('ID') != 'undefined'){
                //this.job.set({featured: plan.featured});
                //}


                // if a plan has been selected
                // change the step status to "completed"
                if (!!this.job.get('job_package')) {
                    // console.log( $container.find('select#categories') );
                    this.markStepCompleted($step);
                }

                //show discount prices based on duration
                //!!this.job.get('is_free')
                console.log("get JOB:" + this.job.get('is_free'));
                if (parseFloat(amount) == 0) {
                    //hide 4weeks, 8weeks plan
                    $('#step_plan li.w4, #step_plan li.w8').hide();

                    $("#job_form .agb_for_free #agb_check_free").removeAttr('checked');
                    $("#job_form .agb_for_free").show();
                    //console.log(data_price_FTB);
                    //console.log(amount);
                    if (data_price_FTB == 0 || data_price_FTB == "") {
                        $('#step_plan .discount').hide();
                        $('#step_plan .pricetag').html('kostenlos');
                    } else {
                        $('#step_plan .discount').hide();

                        $("#step_plan .weeks4 button.select_duration").attr('data-price', data_price_FTB);
                        $("#step_plan .weeks8 button.select_duration").attr('data-price', data_price_FTB);
                        $("#step_plan .weeks12 button.select_duration").attr('data-price', data_price_FTB);

                        if (data_price_FTB == parseInt(data_price_FTB)) {
                            $('.discount4weeks').html(data_price_FTB + '.00 €');
                            $('.discount8weeks').html(data_price_FTB + '.00 €');
                            $('.discount12weeks').html(data_price_FTB + '.00 €');
                        } else {
                            $('.discount4weeks').html(data_price_FTB + ' €');
                            $('.discount8weeks').html(data_price_FTB + ' €');
                            $('.discount12weeks').html(data_price_FTB + ' €');
                        }
                    }

                } else {
                    //show 4weeks, 8weeks plan
                    $('#step_plan li.w4, #step_plan li.w8').show();

                    $("#job_form .agb_for_free #agb_check_free").attr('checked', 'checked');
                    $("#job_form .agb_for_free").hide();
                    $('#step_plan .discount').show();

                    var discount4weeks = this.jobhandler[this.job.get('job_basis')][this.job.get('is_regular')]['weeks4'];
                    discount4weeks = this.plans[ discount4weeks  ];

                    var discount8weeks = this.jobhandler[this.job.get('job_basis')][this.job.get('is_regular')]['weeks8'];
                    discount8weeks = this.plans[ discount8weeks  ];

                    var discount12weeks = this.jobhandler[this.job.get('job_basis') ][ this.job.get('is_regular') ][ 'weeks12'];
                    discount12weeks = this.plans[ discount12weeks ];
                    //console.log(price_FTB);
                    //console.log(discount4weeks['price']);
                    if (data_price_FTB != 0) {
                        var price_FTB = parseFloat(data_price_FTB) - parseFloat(discount4weeks['price']);
                        var new_price_4w = parseFloat(price_FTB) + parseFloat(discount4weeks['price']);

                        //4weeks
                        $("#step_plan .weeks4 button.select_duration").attr('data-price', new_price_4w);
                        if (new_price_4w == parseInt(new_price_4w))
                            $('.discount4weeks').html(new_price_4w + '.00 €');
                        else
                            $('.discount4weeks').html(new_price_4w + ' €');

                        //8weeks
                        //console.log(discount8weeks['price']);
                        var new_price_8w = parseFloat(price_FTB) + parseFloat(discount8weeks['price']);
                        $("#step_plan .weeks8 button.select_duration").attr('data-price', new_price_8w);

                        if (new_price_8w == parseInt(new_price_8w))
                            $('.discount8weeks').html(new_price_8w + '.00 €');
                        else
                            $('.discount8weeks').html(new_price_8w + ' €');


                        //12weeks
                        //console.log(discount12weeks['price']);
                        var new_price_12w = parseFloat(price_FTB) + parseFloat(discount12weeks['price']);
                        $("#step_plan .weeks12 button.select_duration").attr('data-price', new_price_12w);
                        if (new_price_12w == parseInt(new_price_12w))
                            $('.discount12weeks').html(new_price_12w + '.00 €');
                        else
                            $('.discount12weeks').html(new_price_12w + ' €');

                    } else {
                        //4weeks 
                        $("#step_plan .weeks4 button.select_duration").attr('data-price', 0);
                        if (discount4weeks['price'] == parseInt(discount4weeks['price']))
                            $('.discount4weeks').html(discount4weeks['price'] + '.00 €');
                        else
                            $('.discount4weeks').html(discount4weeks['price'] + ' €');

                        //8weeks 
                        $("#step_plan .weeks8 button.select_duration").attr('data-price', 0);
                        if (discount8weeks['price'] == parseInt(discount8weeks['price']))
                            $('.discount8weeks').html(discount8weeks['price'] + '.00 €');
                        else
                            $('.discount8weeks').html(discount8weeks['price'] + ' €');

                        //12weeks 
                        $("#step_plan .weeks12 button.select_duration").attr('data-price', 0);
                        if (discount12weeks['price'] == parseInt(discount12weeks['price']))
                            $('.discount12weeks').html(discount12weeks['price'] + '.00 €');
                        else
                            $('.discount12weeks').html(discount12weeks['price'] + ' €');
                    }


                }


                if (nextStep.is(':visible')) {
                    this.showStep(nextStep);
                }
                else {
                    this.showStep($step.next());
                }
                var job_types = [{slug: $('#step_package').find('select#job_types').val()}];
                var loading = new JobEngine.Views.LoadingButton({el: $(event.currentTarget)});
                var params = {
                    type: 'POST',
                    dataType: 'html',
                    url: et_globals.ajaxURL,
                    data: {
                        action: 'et_conditions_sync',
                        job_types: job_types[0]['slug']
                    },
                    beforeSend: function() {
                        loading.loading();
                    },
                    success: function(data) {
                        loading.finish();
                        $("#load_conditions_by_job_type").html(data);
                    }
                };
                jQuery.ajax(params);
            },
            selectDuration: function(event) {
                var $target = $(event.currentTarget),
                        $container = $target.closest('li'),
                        $step = $container.closest('div.step'),
                        nextStep = $step.nextAll().not(".completed").first(),
                        duration = $target.attr('data-duration'),
                        planID = this.job.get('job_package'), // get the selected plan 	
                        // var planID	=	$('#step_package').find('.selected').find('button').attr('data-package');

                        plan = this.plans[planID];




                // set chosen duration to update the planID.
                this.job.set({selectedDuration: duration});

                // calculate actual choosen plan based on duration and update planID
                this.updatePricing(duration);

                // mark the selected duration as selected
                $container.addClass('selected').siblings().removeClass('selected');

                // if a duration has been selected
                // change the step status to "completed"
                if (!!this.job.get('selectedDuration')) {
                    this.markStepCompleted($step);
                }

                if (nextStep.is(':visible')) {
                    this.showStep(nextStep);
                }
                else {
                    this.showStep($step.next());
                }

            },
            updatePricing: function(period) {

                // 1 select planID based on duration
                // 	this.$('#step_job #submit_job').html(et_post_job.button_submit);
                // 2nd update planIDs in $target.attr('data-package');



                var cond_one = this.job.get('job_basis');
                var cond_two = this.job.get('is_regular');
                var cond_three = this.job.get('selectedDuration');


                // select new plan based on duration and choosing
                var planID = this.jobhandler[cond_one][cond_two][cond_three];
                var plan = this.plans[planID];


                // update prices in checkout_overview
                $('#step_payment .checkout_package span.entry').html(this.plans[planID]['title']);
                $('#step_payment .checkout_duration span.entry').html(this.plans[planID]['duration'] / 7);


                var vat = parseFloat(this.plans[planID]['price'] * 0.19).toFixed(2),
                        checkout_cost = parseFloat(this.plans[planID]['price'] * 1.19).toFixed(2);

                var new_price = $("." + period + " button.select_duration").attr('data-price');
                console.log(new_price);
                if (new_price == 0) {

                    $("#checkout_form div.select_payment_debit").attr('data-price', this.plans[planID]['price']);
                    $("#checkout_form #inputAmount").val((this.plans[planID]['price'] * 1.19).toFixed(2));

                    if (this.plans[planID]['price'] == parseInt(this.plans[planID]['price']))
                        $('#step_payment .checkout_total span.price_entry').html(this.plans[planID]['price'] + '.00 €');
                    else
                        $('#step_payment .checkout_total span.price_entry').html(this.plans[planID]['price'] + ' €');
                    if (vat == parseInt(vat))
                        $('#step_payment .checkout_total span.vat_entry').html(vat + '.00 €');
                    else
                        $('#step_payment .checkout_total span.vat_entry').html(vat + ' €');
                    if (checkout_cost == parseInt(checkout_cost))
                        $('#step_payment .checkout_total span.total_entry').html(checkout_cost + '.00 €');
                    else
                        $('#step_payment .checkout_total span.total_entry').html(checkout_cost + ' €');
                } else {
                    var new_vat = parseFloat(new_price * 0.19).toFixed(2);
                    var new_total_price = parseFloat(new_price * 1.19).toFixed(2);

                    $("#checkout_form div.select_payment").attr('data-price', new_price);
                    $("#checkout_form div.select_payment_debit").attr('data-price', new_price);

                    $("#checkout_form #inputAmount").val((new_price * 1.19).toFixed(2));

                    if (new_price == parseInt(new_price))
                        $('#step_payment .checkout_total span.price_entry').html(new_price + '.00 €');
                    else
                        $('#step_payment .checkout_total span.price_entry').html(new_price + ' €');
                    if (new_vat == parseInt(new_vat))
                        $('#step_payment .checkout_total span.vat_entry').html(new_vat + '.00 €');
                    else
                        $('#step_payment .checkout_total span.vat_entry').html(new_vat + ' €');
                    if (new_total_price == parseInt(new_total_price))
                        $('#step_payment .checkout_total span.total_entry').html(new_total_price + '.00 €');
                    else
                        $('#step_payment .checkout_total span.total_entry').html(new_total_price + ' €');
                }
                //update total amount in checkout including VAT 

                // 2 update planID +  Update job_package
                this.job.set({job_package: planID, featured: plan.featured});



                // var totalPrice	= this.$(event.currentTarget).attr('data-gateway');

            },
            updateProcess: function() {
                var isFree = this.job.get('is_free');

                if (isFree == 0) {
                    console.log("isFree:" + isFree);
                    this.showPaymentStep();
                    //this.removePaymentStep();
                }
                else {
                    this.removePaymentStep();
                    //this.showPaymentStep();
                }
            },
            removePaymentStep: function() {
                this.$('#step_payment').hide();
                //this.$('#step_job #submit_job').html(et_post_job.button_submit);
                this.$('#step_job #save_job').html(et_post_job.button_submit);
            },
            showPaymentStep: function() {
                this.$('#step_payment').show();
                //this.$('#step_job #submit_job').html(et_post_job.button_continue);
                this.$('#step_job #save_job').html(et_post_job.button_continue);
            },
            // END STEP: PACKAGE //////////////////////////////////////////////////////////////

            // STEP: AUTH //////////////////////////////////////////////////////////////
            // event handler: run when a user select a tab
            selectTab: function(event) {
                var $target = this.$(event.currentTarget),
                        index = $target.index();

                if (!$target.hasClass('active')) {
                    // change style for tab title
                    $target.siblings().removeClass('active')
                            .end().addClass('active');

                    // show the selected tab & focus to the first input
                    this.$(".tab-content > div").hide()
                            .eq(index).fadeIn(200)
                            .find('input:first').focus();
                }
                return false;
            },
            // step: AUTH
            // event handler for when user submit the form
            submitAuth: function(event) {
                event.preventDefault();
                // get the submitted form & its id
                var $target = this.$(event.currentTarget),
                        $container = $target.closest('div.form'),
                        form_type = $target.attr('id'),
                        view = this,
                        result;

                if (this[form_type + '_validator'].form()) {
                    // update the auth model before submiting form
                    JobEngine.app.auth.setUserName($container.find('input.is_user_name').val());
                    JobEngine.app.auth.setEmail($container.find('input.is_email').val());
                    JobEngine.app.auth.setPass($container.find('input.is_pass').val());
                    result = JobEngine.app.auth.doAuth(form_type, {
                        renew_logo_nonce: true,
                        beforeSend: function() {
                            view.loadingBtn = new JobEngine.Views.LoadingButton({el: $target.find('button[type=submit]')});
                            $('button#submit_login').addClass('disabled_save');
                            view.loadingBtn.loading();
                            //view.trigger('waitingAuth', $container.find('button[type=submit]'));s
                        },
                        success: function(res) {
                            $('button#submit_login').removeClass('disabled_save');
                            view.loadingBtn.finish();
                            //$('.et_ajaxnonce').attr('id', res.data.logo_nonce);
                            //view.trigger('endWaitingAuth', $container.find('button[type=submit]'));
                        }
                    });
                }
            },
            waitingAuth: function(e) {
                //this.authButton = $(e).html();
                //console.log($(e));
                //$(e).html(et_globals.loading);
            },
            endWaitingAuth: function(e) {
                this.loadingBtn.finish();
                //$(e).html(this.authButton);
            },
            // step: AUTH
            // event handler
            // when user click log out in step authentication
            logoutCompany: function(e) {
                e.preventDefault();
                var image = $('<img>').attr({
                    'alt': 'loading',
                    'src': et_globals.imgURL + '/loading.gif',
                    'class': 'loading'
                });
                JobEngine.app.auth.doLogout({
                    beforeSend: function() {
                        image.insertAfter($(e.currentTarget));
                    },
                    success: function() {
                        image.remove();
                    }
                });
            },
            // step: AUTH
            // this function the handler for when the currentUser (this job author) is changed
            updateAuthor: function() {

                var authStep = this.$('div#step_auth'),
                        jobStep = this.$('div#step_job'),
                        prevSteps = authStep.prevAll(),
                        stepToShow = null;

                if (authStep.length > 0) {
                    prevSteps.each(function(i, ele) {
                        var $ele = jQuery(ele);
                        if (!$ele.hasClass('completed') && stepToShow === null) {
                            stepToShow = $ele;
                            return false;
                        }
                    });
                    // if this currentUser has an id && the auth step existed, the user has just been logged in
                    if (!this.job.author.isNew()) {
                        // change the view in auth step
                        if (!authStep.hasClass('completed')) {

                            this.$('div#step_auth .toggle-content').children('div').hide()
                                    .end().append(this.tpl_login_success({company: this.job.author.get('display_name')}));

                            this.markStepCompleted(authStep);
                            if (stepToShow === null) {
                                this.showStep(authStep.nextAll().not('.completed').first());
                            }
                        }
                        // update the author param for logo uploader
                        this.logo_uploader.updateConfig({
                            'multipart_params': {
                                'author': this.job.author.id
                            },
                            'updateThumbnail': true,
                            'data': this.job.author.get('user_logo')
                        });
                    }
                    else {
                        // mark both steps Auth & Job incompleted
                        this.markStepIncompleted(authStep)
                                .markStepIncompleted(jobStep);
                        if (stepToShow === null) {
                            this.showStep(authStep);
                        }

                        this.$('div#step_auth .toggle-content').children('div.login_success')
                                .fadeOut(200, function() {
                            $(this).remove();
                        })
                                .end().children('div').fadeIn(400);
                    }

                }
                // the user has already logged in at the beginning, so there is no auth step
                else {
                    // if the user login & then log out, there is no auth step, so reload the page
                    if (this.job.author.isNew()) {
                        window.location.reload();
                    }
                }

                // if company has purchase current plan before, set it free
                var purchase = this.job.author.get('payment_plans');
                if (!!purchase && purchase[this.job.get('job_package')] && purchase[this.job.get('job_package')] > 0) {
                    this.removePaymentStep();
                    this.job.set({is_free: 1});
                }
                // console.log('plan object: ' + this.job.author.get('payment_plans'));
                // console.log('plan id: ' + this.job.get('job_package'));
                //console.log('job left: ' + purchase[this.job.get('job_package')])

                // update or clear the company fields
                this.updateCompany();
            },
            updateLogoNonce: function(data, status, jqXHR) {
                if (data.logo_nonce) {
                    this.logo_uploader.updateConfig({
                        'multipart_params': {
                            '_ajax_nonce': data.logo_nonce
                        }
                    });
                }
            },
            updateCompany: function() {
                if (!this.job.author.get('ID'))
                    return;
                var user_logo = this.job.author.get('user_logo'),
                        $form = this.$('form#job_form'),
                        location = this.job.author.getLocation();

                // update the job form with new value of currentUser
                if (user_logo)
                    $form
                            .find('input#display_name').val(this.job.author.getName())
                            .end()
                            .find('input#user_url').val(this.job.author.getUrl())
                            .end()
                            .find('input#full_location').val(location.full_location)
                            .end()
                            .find('input#location').val(location.location)
                            .end()
                            .find('input#location_lat').val(location.location_lat)
                            .end()
                            .find('input#location_lng').val(location.location_lng)
                            .end()
                            .find('input#apply_email').val(this.job.author.getApplyEmail())
                            .end()
                            .find('textarea#applicant_detail').val(this.job.author.getApplicantDetail())
                            ;

                if (!!user_logo && 'company_logo' in user_logo && !!user_logo['company_logo']) {
                    $form.find('img#user_logo_thumb').attr({
                        src: user_logo['company_logo'][0]
                    });
                }
                if (this.job.author.getApplyMethod() == 'ishowtoapply') {
                    $('#ishowtoapply').attr('checked', true);
                    $('#applicant_detail').addClass('required');
                } else {
                    $('#isapplywithprofile').attr('checked', true);
                    $('#apply_email').addClass('required email');
                }
                if (this.job.author.getApplicantDetail() && $('#applicant_detail').length > 0)
                    tinyMCE.get('applicant_detail').setContent(this.job.author.getApplicantDetail());

            },
            // END STEP: AUTH //////////////////////////////////////////////////////////////

            // STEP: JOB //////////////////////////////////////////////////////////////
            // event handler for when user submit step 3 form
            // submit the job form to create DRAFT post
            submitJob: function(event) {
                event.preventDefault();

                this.job_form_validator = this.$('form#job_form').validate({
                    ignore: "",
                    rules: {
                        title: "required",
                        //full_location	: "required",
                        display_name: "required",
                        content: "required",
                        agb_check_free: "required",
                        user_url: {
                            required: true,
                            url: true
                        }
                    },
                    errorPlacement: function(label, element) {
                        // position error label after generated textarea
                        if (element.is("textarea")) {
                            label.insertAfter(element.next());
                        } else {
                            label.insertAfter(element)
                        }
                    }
                });


                // get the submitted form & its id
                var that = this,
                        $container = this.$(event.currentTarget).closest('div.form'),
                        $jobinfo = $container.find('div#job_info'),
                        jobStep = $container.closest('div.step'),
                        companyData = {},
                        jobData = {};

                var applicant_detail_ok = true;
                $('.applicant_detail').removeClass('error');
                if ($('#ishowtoapply').is(':checked')) {
                    var applicant_detail = tinyMCE.get('applicant_detail').getContent();
                    $('#applicant_detail').val(applicant_detail);
                }
                var isFree = this.job.get('is_free');
                //tinyMCE.triggerSave();

                // validate the job before submiting
                if (this.$('form#job_form').valid()) {

                    // validate other form
                    pubsub.trigger('je:post:validate');


                    // get all input value in the company form to generate an array
                    $container.find('div#company_info input').each(function() {
                        var $this = $(this);
                        companyData[$this.attr('id')] = $this.val();
                    });

                    // use that array to set data for the author
                    this.job.author.set(companyData, {silent: true});
                    this.job.author.trigger('change');
                    //this.job.author.save();


                    // get all input value in job form to generate an array
                    $jobinfo.find('input,textarea,select').each(function() {
                        var $this = $(this);
                        jobData[$this.attr('id')] = $this.val();
                    });


                    // add total price including all addon-options to this array (?)
                    // xxx
                    // jobData['total'] =  ... 


                    // get the job type & category & add to the array
                    jobData['job_types'] = [{slug: $('#step_package').find('select#job_types').val()}];
                    jobData['categories'] = [{slug: $jobinfo.find('select#categories').val()}];
                    // jobData[''] = [] ; 

                    jobData['raw'] = $('#job_form').serialize();

                    this.job.set(jobData, {silent: true})
                            .save({}, {
                        author_sync: true,
                        beforeSend: function() {
                            //that.loadingBtn = new JobEngine.Views.LoadingButton({el: $('button#submit_job')});
                            that.loadingBtn = new JobEngine.Views.LoadingButton({el: $('div#save_job')});
                            that.loadingBtn.loading();
                            $('div#save_job').removeClass('save_job').addClass('disabled_save');
                        },
                        success: function(model, resp) {
                            that.loadingBtn.finish();
                            if (resp.success) {
                                if (resp.success_url) {
                                    if (isFree == 0)
                                        that.markStepCompleted(jobStep)
                                                .showStep(jobStep.nextAll().not('.completed').first());
                                    else
                                        window.location = resp.success_url;
                                }
                                else {
                                    that.markStepCompleted(jobStep)
                                            .showStep(jobStep.nextAll().not('.completed').first());
                                }
                            }
                        }
                    });
                } else { // trigger event to show error message
                    pubsub.trigger('je:notification', {
                        msg: et_post_job.error_msg,
                        notice_type: 'error'
                    });
                }

            },
            switchApplyMethod: function(event) {
                //event.preventDefault();
                var apply_method = $(event.currentTarget).val();
                if (apply_method == 'isapplywithprofile') {
                    $('#apply_email').addClass('required email');
                    $('#applicant_detail').removeClass('required');
                    $('.applicant_detail').removeClass('error');
                }

                if (apply_method == 'ishowtoapply') {
                    $('#applicant_detail').addClass('required');
                    $('#apply_email').removeClass('required');
                    $('.email_apply').removeClass('error');
                }
                $('.apply').find('.icon').remove();
                $('.apply').find('.message').remove();
                $('#apply_method').val(apply_method);
            },
            waitingPostJob: function() {

                // this.continueButton = this.$el.find('button#submit_job').html();
                // this.$el.find('button#submit_job').html(et_globals.loading);
            },
            endWaitingPostJob: function() {
                this.loadingBtn.finish();
                // this.$el.find('button#submit_job').html(this.continueButton);
            },
            // END STEP: JOB //////////////////////////////////////////////////////////////

            // STEP: PAYMENT //////////////////////////////////////////////////////////////
            // send a request to payment process in back-end
            // receive response and redirect to the returned URL
            selectPayment: function(event) {
                event.preventDefault();
                var view = this;

                // AGB CHECK
                this.checkout_form_validator = this.$('form#checkout_form').validate({
                    rules: {
                        agb_check: "required"

                    },
                    errorPlacement: function(label, element) {
                        // position error label after generated textarea
                        if (element.is("textarea")) {
                            label.insertAfter(element.next());
                        } else {
                            label.insertAfter(element)
                        }
                    }
                });

                var facebook = 0;
                var newsletter = 0;
                var blog = 0;
                if (this.$('#post_facebook').is(':checked')) {
                    facebook = 1;
                }
                if (this.$('#post_newsletter').is(':checked')) {
                    newsletter = 1;
                }
                if (this.$('#post_blog').is(':checked')) {
                    blog = 1;
                }
                var paymentType = this.$(event.currentTarget).attr('data-gateway');
                
                var price = this.$(event.currentTarget).attr('data-price');
                if (paymentType == 'debit'){
                    price = jQuery('.select_payment_debit:first').attr('data-price');
                }

                var loading = new JobEngine.Views.LoadingButton({el: $(event.currentTarget)});
                var params = {
                    type: 'POST',
                    dataType: 'json',
                    url: et_globals.ajaxURL,
                    contentType: 'application/x-www-form-urlencoded;charset=UTF-8',
                    data: {
                        action: 'et_payment_process',
                        jobID: this.job.id,
                        authorID: this.job.get('author_id'),
                        packageID: this.job.get('job_package'),
                        paymentType: paymentType,
                        price: price,
                        coupon_code: $('#coupon_code').val(),
                        facebook: facebook,
                        newsletter: newsletter,
                        blog: blog,
                        debit: (paymentType == 'debit')?this.getDebitData():''
                    },
                    beforeSend: function() {
                        loading.loading();
                        $(event.currentTarget).removeClass("select_payment").addClass("disabled_payment");
                    },
                    success: function(response) {

                        loading.finish();

                        if (response.success) {
                            if (response.data.ACK) {
                                // console.log (response.data.url);

                                /**
                                 * process for another payment gateway
                                 */
                                $('#checkout_form').attr('action', response.data.url);
                                if (typeof response.data.extend !== "undefined") {
                                    $('#checkout_form .payment_info').html('').append(response.data.extend.extend_fields);
                                }

                                $('#payment_submit').click();	// do form


                            }
                        } else {
                            pubsub.trigger('je:notification', {
                                msg: response.errors[0],
                                notice_type: 'error'
                            });
                        }

                    }
                };


                // check agb_checkbox before submiting
                if (this.$('form#checkout_form').valid()) {

                    // if valid submit form
                    jQuery.ajax(params);

                } else { // trigger event to show error message
                    pubsub.trigger('je:notification', {
                        msg: et_post_job.error_msg,
                        notice_type: 'error'
                    });
                }


            },
            showFormDebit: function(event) {
                //console.log(this.job.toJSON());
                $(event.currentTarget).hide();
                $("#form_payment_debit #inputPayer").val(this.job.get('author'));
                $("#form_payment_debit").slideDown();
            },
            getDebitData: function(){
                var price = $("#inputAmount").val();
                var accountNumber = $("#accountNumber").val();
                var bankNumber = $("#bankNumber").val();

                return {
                    action: 'payment_debit',
                    jobID: this.job.id,
                    authorID: this.job.get('author_id'),
                    packageID: this.job.get('job_package'),
                    payer: this.job.get('author_data')['display_name'],
                    price: price,
                    accountNumber: accountNumber,
                    bankNumber: bankNumber
                };
            },
            PaymentDebit: function(event) {
                this.checkout_form_validator = this.$('form#checkout_form').validate({
                    ignore: "",
                    rules: {
                        bankNumber: "required",
                        accountNumber: "required"
                    },
                    errorPlacement: function(label, element) {
                        label.insertAfter(element);
                    }
                });
                
                return this.selectPayment(event);
            },
            waitingPayment: function(e) {
                this.loadingBtn = new JobEngine.Views.LoadingButton({el: $(e)});
                this.loadingBtn.loading();
                // this.paymentButton = $(e).html();
                // $(e).html( et_globals.loading );
            },
            endWaitingPayment: function() {
                this.loadingBtn.finish();
            },
            autoCompleteUrl: function(event) {
                var val = $(event.currentTarget).val();
                if (val.length == 0) {
                    return true;
                }

                // if user has not entered http:// https:// or ftp:// assume they mean http://
                if (!/^(https?|ftp):\/\//i.test(val)) {
                    val = 'http://' + val; // set both the value
                    $(event.currentTarget).val(val); // also update the form element
                    $(event.currentTarget).focus();
                }
            }
            // END STEP: PAYMENT //////////////////////////////////////////////////////////////

        });

        // initialize the job posting view
        JobEngine.post_job = new JobEngine.Views.Post_Job();

        // console.log('JobEngine.post_job.job:');
        // console.log(JobEngine.post_job.job);
    });
})(jQuery);