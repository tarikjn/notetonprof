// runs when the DOM finish loading
$(document).ready(function() {
	
	/**
	 * comments mod-table
	 */
	
	// load js class on mod-table (hide radio buttons, show big buttons hide notes)
	$("table.mod-table").addClass("js");
	
	// init big buttons
	$(".mod-table .action label:not(.disabled)").click(function(e) {
		
		var action = $(this).find("input:eq(0)").val();
		var block = $(this).parents("tbody:eq(0)")[0];
		var notes = $(block).find(".extra-tr:eq(0)")[0];
		
		// switch selected
		$(this).parent().children(".selected").removeClass("selected");
		$(this).addClass("selected");
		
		// select radio button
		$(this).find("input:eq(0)").attr("checked", "checked");
		
		// color comment
		$(block).attr("class", action);
		
		// show/hide notes
		((action == "accept")? $(notes).hide() : $(notes).show());
	});
	
	// init notes, TODO: write as a form widget
	$(".mod-table .extra-tr input").each(function() {
		
		var label = $(this).parent().find("span").text();
	
		$(this).val(label);
		$(this).addClass("label");
		
		$(this).focus(function() {
			
			if ($(this).hasClass("label")) {
			
				$(this).removeClass("label");
				$(this).val("");
			}
		});
		
		$(this).blur(function() {
			
			if ($(this).val() == "") {
			
				$(this).addClass("label");
				$(this).val(label);
			}
		});
	});
	$("form.has-labels").submit(function() {
		
		$(this).find("input.label").val("");
	});
	
	/**
	 * highlight-select
	 */
	$(".highlight-select input").click(function() {
		$(this).parents("form:eq(0)").find("input[name='"+this.name+"']").parent().removeClass("selected");
		$(this).parent().addClass("selected");
	});
	// enable or disable update/delete form buttons when a special action is checked
	$(".highlight-select input").change(function() {
		if ($(this).filter(":checked"))
		{
			var upBtn = $(this).parents("form:eq(0)").find("input.update-btn");
			var delBtn = $(this).parents("form:eq(0)").find("input.delete-btn");
			
			if ($(this).val() == 'delete')
			{
				upBtn.attr("disabled", "disabled");
				delBtn.removeAttr("disabled");
			}
			else if ($(this).val() == '')
			{
				upBtn.removeAttr("disabled");
				delBtn.removeAttr("disabled");
			}
			else
			{
				delBtn.attr("disabled", "disabled");
				upBtn.removeAttr("disabled");
			}
		}
	});
});
