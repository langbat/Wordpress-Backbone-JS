(function($) {

    $(document).ready(function() {
        jobField.initDatepicker();
        // $('.input-date input').change (function (event) {
        // 	$('.input-date').parents('.input-date-alter').find('input').val($(event.currentTarget).val());
        // });
    });

    var jobField = {
        initDatepicker: function() {
            this.dpPost();
            pubsub.on('je:job:modal_edit:afterSetupFields', this.dpEdit);
            pubsub.on('je:post:validate', this.postValidate);
        },
        dpPost: function() {
            // init post job date picker
            var dateFields = $('#step_job .input-date input');
            dateFields.datepicker({
                dateFormat: jep_field.dateFormat,
                onClose: function(date, object) {
                    console.log($(object.input));
                    $(object.input).trigger('focusout');
                }
            });
        },
        dpEdit: function(model, $jobinfo) {
            var inputFields = $('#modal_edit_job .input-date input');
            console.log(model);
            // fill all the fields value
            var fields = model.get('fields');
            _.each(fields, function(e, i, list) {
                var field = $('#modal_edit_job .cfield-' + e.ID);
                if (e.value !== '' && e.value !== 0) {
                    field.val(e.value);
                    // trigger change if field is select tag
                    field.trigger('change');
                }
            });

            inputFields.datepicker({
                dateFormat: jep_field.dateFormat,
                onClose: function(date, object) {
                    $(object.input).trigger('change');
                    $(object.input).trigger('focusout');
                }
            });
        },
        postValidate: function() {
            var fields = $('#modal_edit_job .input-field.input-required');
            var validateResult = true;

            _.each(fields, function(e, i, list) {
                if (e.val() == '') {
                    this.markError(e);
                    validateResult = false;
                }
            });
            return false;
        },
        markError: function(e) {
            var container = e.parent();
            container.addClass('error');
            container.append('<div for="title" generated="true" class="message" style="">this is is required</div><span class="icon" data-icon="!"></span>')
        }
    }

})(jQuery)