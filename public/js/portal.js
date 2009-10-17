// runs when the DOM finish loading
$(document).ready(function() {
	
	$('body').addClass('javascript-on');
	
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
	
	/**
	 * maxlen-field
	 */
	$(".maxlen-field").each(function() {
		
		this.pt_counter = $(this).parent().find("span.maxlen-counter:eq(0)")[0];
		this.x_maxlen = $(this.pt_counter).text();
	});
	$(".maxlen-field").bind('keyup keydown', function() {
		
		$(this.pt_counter).text( this.x_maxlen - this.value.length );
	})
	
	/**
	 * autoExpand
	 */
	$('textarea.autoexpand').autoExpand();
	
	
	/**
	 * #get_update_notification
	 */
	if (!$('#get_update_notification').attr('checked'))
		$('#update_notification_email').hide();
	$('#get_update_notification').bind('change click', function() {
		
		if ($(this).attr('checked'))
			$('#update_notification_email').show(500, function() {
				$(this).find("input").focus();
			});
		else
			$('#update_notification_email').hide(500);
	});
	
	
	/**
	 * CC tooltip
	 * see http://www.kriesi.at/archives/create-simple-tooltips-with-css-and-jquery-part-2-smart-tooltips
	 */
	$(".cc-tooltip[title!=]").append("<sup>?</sup>");
	$(".cc-tooltip[title!=]").each(function(i) {
	
		var tooltip = $('<div class="cc-tooltip-box"><p>'+$(this).attr('title')+'</p></div>').appendTo('body');

		$(this).removeAttr("title").mouseover(function(){
		    	tooltip.css({opacity:0.9, display:"none"}).fadeIn(250);
		}).mousemove(function(e){
		    	tooltip.css({left:e.pageX, top:e.pageY+15});
		}).mouseout(function(){
		    	tooltip.hide();
		});
	});

	/**
	* CC Slider Set-up
	*/
	// markup replacement
	$(".cc-slider-control").each(function() {
		var name = $(this).find("input").attr('name'),
		    defaultVal = $(this).find("input:checked").val() || 3,
		    input = document.createElement('input'),
		    slider = document.createElement('div'),
		    thumbImg;
		
		slider.className = "cc-slider yui-skin-sam";
		
		if ($(this).hasClass("leveled-slider"))
		{
			var marks = document.createElement('div');
			marks.className = "cc-slider-marks";
			for (var i = 0; i < 5; i++)
			{
				var mark = document.createElement('div');
				
				mark.appendChild(document.createTextNode(i + 1));
				// NOTE: already taken care of by the widget, commented to avoid lag effects
				//if (i + 1 == defaultVal)
				//	mark.className = "selected";
				$(mark).css({left: (9 + i * 50) + "px"})
					
				marks.appendChild(mark);
			}
			slider.appendChild(marks);
			
			thumbImg = "img/slider/thumb-n.png";
		}
		else
			thumbImg = "img/slider/centered-thumb.png";
		
		input.setAttribute('type', 'hidden');
		input.setAttribute('name', name);
		input.setAttribute('id', name + '-slider-converted-value');
		input.value = defaultVal;
		
		var SliderMarkup =
			'<div id="' + name + '-slider-bg" class="yui-h-slider" tabindex="0">\
        		<div id="' + name + '-slider-thumb" class="yui-slider-thumb"><img src="' + thumbImg + '"></div>\
	    		<div class="cc-slider-bar"></div>\
	    	</div>';
	    
	    $(slider).append(SliderMarkup);
	    slider.appendChild(input);
	    
	    // replacement
	    this.replaceChild(slider, this.firstChild);
	});
	// initialization
	$(".cc-slider").each(function() {
		var bg = $(this).find(".yui-h-slider").attr('id'),
		    thumb = $(this).find(".yui-slider-thumb").attr('id'),
		    textfield = $(this).find("input").attr('id');
		CCSliderSetup(bg, thumb, textfield);
	});
	
	
	/**
	 * bubble-tip
	 */
	$(".bubble-tip").hide(); // non-js support
	$(".bubble-tip").each(function() {
		
		var bubble = $(this);
		var next = $(this).next();
		var field = (next.is("input, textarea")) ? next : next.find("input[type=text], .yui-h-slider").eq(0);
		
		field.focus(function() {
			bubble.fadeIn(500);
		}).blur(function() {
			bubble.fadeOut(250);
		});
	});
});


var CCSlider =
{
	moveBar: function(bg, pos, scaleFactor)
	{
		var func = ($(bg).closest(".cc-slider-control").hasClass("centered-slider")) ?
			this.moveCenteredBar
			: this.moveLeveledBar;
		
		func(bg, pos, scaleFactor);
	},
	moveLeveledBar: function(bg, pos, scaleFactor)
	{
		// select right ruler mark
	    $(bg).closest(".cc-slider").find(".cc-slider-marks").children()
	        .removeClass("selected")
	        .eq(pos).addClass("selected");
	    
	    // update background bar
	    $(bg).find(".cc-slider-bar").width(2 + pos * scaleFactor)
	        .css('background-color', "#" + leveledSliderColors[pos]);
	},
	moveCenteredBar: function(bg, pos, scaleFactor)
	{
		// update background bar
	    $(bg).find(".cc-slider-bar").css({left: (4 + pos * scaleFactor) + 'px'})
	    	.css('background-color', "#" + centeredSliderColors[pos]);
	},
	
	// hack for YUI Slider
	// required for smooth sync of slider-bar
	moveThumb: function(x, y, skipAnim, midMove)
	{
		var t = this.thumb,
		    self = this,
		    p,_p,anim;
		
		if (!t.available) {
		    return;
		}
		
		
		t.setDelta(this.thumbCenterPoint.x, this.thumbCenterPoint.y);
		
		_p = t.getTargetCoord(x, y);
		p = [Math.round(_p.x), Math.round(_p.y)];
		
		// no animation
		t.setDragElPos(x, y);
		if (!midMove) { // hack is on this line
		    this.endMove();
		}
	}		
}
	
/**
* CC Slider Initialization
*/
function CCSliderSetup(bg, thumb, textfield)
{
    var Event = YAHOO.util.Event,
        Dom   = YAHOO.util.Dom,
        lang  = YAHOO.lang,
        slider;

    // The slider can move 0 pixels up
    var topConstraint = 0;

    // The slider can move 200 pixels down
    var bottomConstraint = 200;

    // Custom scale factor for converting the pixel offset into a real value
    var scaleFactor = 50;

    // The amount the slider moves when the value is changed with the arrow
    // keys
    var keyIncrement = 50;

    var tickSize = 50;

    // what follows should always be executed on DOM Ready

	slider = YAHOO.widget.Slider.getHorizSlider(bg, 
	                 thumb, topConstraint, bottomConstraint, tickSize);
	slider.keyIncrement = keyIncrement;
	slider.moveThumb = CCSlider.moveThumb;
	
	slider.getRealValue = function() {
	    return Math.round(this.getValue() / scaleFactor + 1);
	}
	
	slider.subscribe("change", function(offsetFromStart) {
	    
	    var fld = Dom.get(textfield);
	
	    // use the scale factor to convert the pixel offset into a real
	    // value
	    var actualValue = slider.getRealValue();
	
	    // update the text box with the actual value
	    fld.value = actualValue;
	
	    // Update the title attribute on the background.  This helps assistive
	    // technology to communicate the state change
	    //Dom.get(bg).title = "slider value = " + actualValue;
	    
	    CCSlider.moveBar(Dom.get(bg), actualValue - 1, scaleFactor);
	});
	
	// set slider default value
	slider.setValue(Math.round((Dom.get(textfield).value - 1) * scaleFactor));
}

/**
* colors
*/
var leveledSliderColors = ['ff0000', 'ff8000', 'ffff00', '80ff00', '00ff00'],
    centeredSliderColors = ['ff0000', 'ffff00', '00ff00', 'ffff00', 'ff0000'];
