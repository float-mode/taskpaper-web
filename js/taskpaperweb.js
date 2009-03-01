
// JSLint global declaration for jQuery shorthand $ and doc.
/*global $ document */

function fn_loadTaskList(data)
{
	// Load the new html we receive in the task_list div.
	$("#task_list").html(data);
}

function fn_projMenuChanged(e)
{
	if(this.value != '')
	{
		var curView = $("#view");
		curView.val("title:"+this.value);
		$.get(".",{title:this.value},fn_loadTaskList);
		this.selectedIndex = 0;
	}
}

function fn_projWidgetClicked(e)
{
	var curView = $("#view");
	
	if(curView.val() == "index")
	{
		curView.val("title:"+this.title);
		$.get(".",{title:this.title},fn_loadTaskList);
	}
	else
	{
		curView.val("index");
		$.get(".",{ajax:"true"},fn_loadTaskList);
	}
}

function fn_projNameDblClicked(e)
{
	// Make a line editable.

}

function fn_taskOrNoteWidgetClicked()
{
	$.get(".",{toggle:this.title,view:$("#view").val()},fn_loadTaskList);
}

function fn_eventBinder()
{
	// Live bind some selectors to javascript functions.
	$(".project_widget").live("click",fn_projWidgetClicked);
	$(".project_widget_back").live("click",fn_projWidgetClicked);
	$("#project_select").bind("change",fn_projMenuChanged);
	$(".project_name").live("dblclick",fn_projNameDblClicked);
	$(".task_widget").live("click",fn_taskOrNoteWidgetClicked);
	$(".note_widget").live("click",fn_taskOrNoteWidgetClicked);
}

$(document).ready(fn_eventBinder);
