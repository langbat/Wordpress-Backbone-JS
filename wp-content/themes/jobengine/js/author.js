
(function($) {
    jQuery(document).ready(function($) {

// app view
        JobEngine.Views.AuthorView = Backbone.View.extend({
            el: $('body'),
            events: {
                'click #job_list_container div.button-more button': 'loadMore'
            },
            initialize: function() {
                this.jobs = new JobEngine.Collections.Jobs(JSON.parse(this.$('#jobs_list_data').html()));
                this.jobs.setData({author: this.$('input[name=companyid]').val()});
                this.jobsView = new JobEngine.Views.JobListView({el: $('#job_list_container'), collection: this.jobs});
            },
            loadMore: function(event) {
                var self = this,
                        $target = $(event.currentTarget);

                event.preventDefault();

                if (!$target.hasClass('et_processing')) {

                    $target.addClass('et_processing');

                    this.jobs.nextPage({success: function(col, res) {

                            // remove loadmore if all jobs are fetched
                            if (col.paginateData.paged >= col.paginateData.total_pages) {
                                $('div.button-more').hide();
                            }
                            else {
                                $('div.button-more').show();
                            }

                            $target.removeClass('et_processing');

                        }
                    });
                }
            }
        });

        new JobEngine.Views.AuthorView();

    });
})(jQuery);