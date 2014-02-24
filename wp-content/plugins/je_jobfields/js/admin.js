(function($){
$(document).ready(function(){
	new jobFieldView();
});

var jobFieldView = Backbone.View.extend({
	el 			: '#job-fields-list',
	events 		: {
		'click #lst_fields .item .act-del' : 'deleteField'
	},

	initialize 	: function(){
		// sortable
		jQuery('.sortable').sortable({
			items: '> li.item',
			update: this.onSortUpdate
		});
	},

	onSortUpdate: function(event, ui){
		var params = {
			url : et_fields.ajaxUrl,
			type: 'post',
			data: {
				action: 'et_sort_fields',
				content: {
					data: jQuery('.sortable').sortable('serialize'),
					'_nonce' : et_fields.nonceDelete
				}
			},
			beforeSend: function(){},
			success: function(resp){
				
			}
		}
		$.ajax(params);
	},

	deleteField : function(event){
		event.preventDefault();
		var id 		= $(event.currentTarget).attr('data'),
			target 	= $(event.currentTarget);
		$.ajax({
			url 	: et_fields.ajaxUrl,
			type	: 'post',
			data 	: {
				action : 'et_delete_field',
				content : {
					id : id,
					'_nonce' : et_fields.nonceDelete
				}
			},
			beforeSend: function(){
				LoadingBlock.load(target.closest('li.item'));
			},
			success: function(resp){
				LoadingBlock.clear();
				if (resp.success){
					target.closest('li.item').fadeOut('normal', function(){ $(this).remove(); });
				}else {
					alert(resp.msg);
				}
			}
		});
	},
});

var LoadingBlock = {
	element : null,
	overlay : null,
	load 	: function(e){
		var $e 	 	= $(e),
			overlay = $('<div>').addClass('loading-overlay').append('<div class="loading-inner">');

		this.overlay = overlay;

		this.overlay.css({
			top 	: $e.offset().top,
			left 	: $e.offset().left,
			width 	: $e.outerWidth(),
			height 	: $e.outerHeight()
		});
		this.overlay.appendTo('body');
	},
	clear: function(element){
		$(this.overlay).remove();
	}
}

})(jQuery)