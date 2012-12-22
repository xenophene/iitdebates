
$(document).ready(function(){
	
	
/*inPlaceEditing of debate description and save the new value.*/

	$("#desc-data").editInPlace({
	  url: "./includes/save_edit.php",
	  params:"field_type=desc&id="+$('#desc-data').attr('name'),
	  success : function(newEditorContentString){return newEditorContentString;},
	  field_type: "textarea",
	  textarea_rows: "15",
	  textarea_cols: "35",
	  saving_image: "../includes/assets/img/ajax-loader.gif",
	  show_buttons: true
	});

/*inPlaceEditing of debate Topic and save the new value.*/
	$(".topic").editInPlace({
	  url: "./includes/save_edit.php",
	  params:"field_type=topic&id="+$(".topic").attr('name'),
	  success : function(newEditorContentString){return newEditorContentString;},
	  field_type: "textarea",
	  textarea_rows: "5",
	  textarea_cols: "35",
	  saving_image: "../includes/assets/img/ajax-loader.gif",
	  show_buttons: true
	});

/*inPlaceEditing of debate Comment and save the new value.*/
	$(".comment-data").editInPlace({
	  url: "./includes/save_edit.php",
	  params:"field_type=comment&id="+$(".comment").attr('name'),
	  success : function(newEditorContentString){return newEditorContentString;},
	  field_type: "textarea",
	  textarea_rows: "15",
	  textarea_cols: "35",
	  saving_image: "../includes/assets/img/ajax-loader.gif",
	  show_buttons: true
	});

	





	// If you need to remove an already bound editor you can call

	// > $(selectorForEditors).unbind('.editInPlace')

	// Which will remove all events that this editor has bound. You need to make sure however that the editor is 'closed' when you call this.
	
});
