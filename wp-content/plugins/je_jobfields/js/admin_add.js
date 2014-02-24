(function($){

$(document).ready(function(){
	new jobFieldView();
});

var jobFieldView = Backbone.View.extend({
	el 			: '#job-fields-add',
	events 		: {
		'change #fadd input[name=type]' : 'changeType',
		'keyup .form-options li input:text:last' : 'addNewOption',
		'click #lst_fields .item .act-del' : 'deleteField',
		'click .form-option .del-opt' : 'deleteOption'
	},

	initialize 	: function(){
		console.log('abcxyz');
		//this.validator = 
	},

	changeType : function(event){
		var a = $(event.currentTarget).val();

		if ( ( a == 'select' )  || ( a == 'checkbox') )
			$('.form-item.form-drop').show();
		else 
			$('.form-item.form-drop').hide();
	},

	addNewOption: function(event){
		var target 		= $(event.currentTarget),
			container 	= $('ul.form-options'),
			isLast 		= $(event.currentTarget).closest('li').is(':last-child');

		if ( $(event.currentTarget).val() != '' && isLast){
			var clone = _.template($('#tl_option').html());
			container.append( clone({ id : container.find('li').length }) );
		}
	},

	deleteField : function(event){
		event.preventDefault();
	},

	deleteOption: function(event){
		if ( !confirm(et_fields.confirm_del) ) return false;
		var container = $(event.currentTarget).closest('li');

		container.fadeOut('normal', function(){ $(this).remove() });
	}
});

})(jQuery)