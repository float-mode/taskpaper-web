<?php 
include('./functions.php');
header('Content-type: text/javascript;');
?>
$(document).ready(function(){
    add_events();
})
function add_events(){
    var current = $("#currently");
    var todoList = $("#todo_list");
    var edit = $("#edit");
    var editArea = $("#edit>textarea");
    
    edit.hide();
    todoList.show();
    if(current.val() != 'index'){
		$("#edit_back").val('Back');
	} else {
		$("#edit_back").val('Edit');
	}
	
    $("#edit_back").click(function(){
        $(this).unbind();
        if(current.val() == 'index'){
            current.val('index:edit');
            $.get("<?=$self?>", 
                { plain: "true", time: "Math.random()" },
                function(data){
                    editArea.val(data);
                }
            );
            todoList.hide();
            edit.show();
        } else {
            current.val("index");
            $.get("<?=$self?>", 
                { ajax: "true" },
                function(data){
                    todoList.html(data);
                    add_events();
                }
            );
        }
    });
    $("#edit>input").click(function(){
        current.val("index");
        $.post("<?=$self?>",
            { todo: editArea.val() },
            function(data){
                todoList.html(data);
                edit.hide();
                todoList.show();
                add_events();
            }
        );
    });
    $(".tag").each(function(){
        $(this).click(function(){
            current.val("tag:"+this.innerHTML);
            $.get("<?=$self?>", 
                { tag: this.innerHTML },
                function(data){
                    todoList.html(data);
                    add_events();
                }
            );
        });
    });
    $("h1").each(function(){
        $(this).click(function(){
            current.val("title:"+this.innerHTML);
            $.get("<?=$self?>", 
                { title: this.innerHTML },
                function(data){
                    todoList.html(data);
                    add_events();
                }
            );
        });
    });

    $(".todo").each(function(){
        var checkbox = $(this).children("input");
        if(this.innerHTML.match('@done') != null)
            checkbox.attr({ checked: "checked"});
        checkbox.click(function(){
            $.get("<?=$self?>", 
                {
					toggle: "true", 
					todo: checkbox.val(), 
					current: current.val() 
				},
                function(data){
                    todoList.html(data);
                    add_events();
                }  
            )
        })
    })
    $("#project_select").change(function(){
        if($(this).val() != ''){
            current.val("title:"+$(this).val());
            $.get("<?=$self?>", 
                { title: $(this).val() },
                function(data){
                    todoList.html(data);
                    add_events();
                }
            );
            this.selectedIndex = 0;
        }
    })
}
function due(days, inclusive, title){
    $.get("<?=$self?>", 
        { days: days, inclusive: inclusive, title: title },
        function(data){
            $("#todo_list").html(data);
            add_events();
        }  
    )
}