
// JSLint (http://www.JSLint.com/) 
// global declaration for jQuery shorthand $ and doc.
/*global $ document url */


function fn_loadItem(item,data,status)
{
	// Load html for the specified item
	// as long as the request was successful.
	if(status == "success")
	{
		item.innerHTML = data;
	}
}

function fn_submitEdit(item)
{
	$.post(url,{action: "edit", item: item.id, value: item.innerHTML},
		function(data,status){fn_loadItem(item,data,status);});
}

function fn_loadTaskList(data,status)
{
	// Load the new html we receive in the task_list div.
	// but only if the request is successful.
	if(status == "success")
	{
		$("#task_list").html(data);
	}
	// Activate "editable" again.
	$(".editable").editable(function(item){fn_submitEdit(item);});
}

function fn_projMenuChanged(e)
{
	if(this.value !== '')
	{
		var curView = $("#view");
		curView.val("proj:"+this.value);
		$.get(url,{action: "proj", proj: this.value},fn_loadTaskList);
		this.selectedIndex = 0;
	}
}

function fn_projWidgetClicked(e)
{
	var curView = $("#view");
	
	if(curView.val() == "index")
	{
		// Send the id of the line that was clicked. It contains
		// the "item" that was click and the location of the line.
		// The format is a:X:Y. See defines.inc.php "Line IDs" for more info.
		curView.val("proj:"+this.id);
		$.get(url,{action: "proj",proj: this.id},fn_loadTaskList);
	}
	else
	{
		curView.val("index");
		$.get(url,{action: "ajax"},fn_loadTaskList);
	}
}

function fn_taskOrNoteWidgetClicked()
{
	$.get(url,{action: "toggle" ,item: this.id,view: $("#view").val()},fn_loadTaskList);
}

function fn_eventBinder()
{
	// Live bind some selectors to javascript functions.
	$(".project_widget").live("click",fn_projWidgetClicked);
	$(".project_widget_back").live("click",fn_projWidgetClicked);
	$("#project_select").bind("change",fn_projMenuChanged);
	$(".task_widget").live("click",fn_taskOrNoteWidgetClicked);
	$(".note_widget").live("click",fn_taskOrNoteWidgetClicked);
	$(".editable").editable(function(item){fn_submitEdit(item);});
}

var url = "index.php";
$(document).ready(fn_eventBinder);
