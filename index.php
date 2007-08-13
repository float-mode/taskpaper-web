<?php 
// public path to this file
$self = "/todo/index.php";
// relative path to your taskpaper document
$file = 'todo-txt.txt';
// Display instructions
$instructions = true;

// everything below can be left
if(isset($_POST['todo']) && $_POST['todo'] != ''){
    echo get_marked_up_todo(save($_POST['todo']));
    die();
} else if(!empty($_GET['tag'])){
    $items = get_items_tagged($_GET['tag']);
    print "<h1>".$_GET['tag']."</h1>\n";
    foreach($items as $item){
        print get_marked_up_todo($item);
    }
    print "<a id=\"back\">&larr; Back</a>";
    die();
} else if(!empty($_GET['days'])){
    $items = get_items_due($_GET['days'], $_GET['inclusive']);
    print "<h1>".$_GET['title']."</h1>\n";
    foreach($items as $item){
        print get_marked_up_todo($item);
    }
    print "<a id=\"back\">&larr; Back</a>";
    die();
} else if(!empty($_GET['title'])){
    $project = get_project($_GET['title']);
    print get_marked_up_todo($project);
    print "<a id=\"back\">&larr; Back</a>";
    die();
} else if(!empty($_GET['toggle'])){
    $with_done = toggle_done($_GET['todo']);
    $current = explode(":",$_GET['current']);
    if($current[0] == "tag") header("Location: ".$self."?tag=".$current[1]);
    if($current[0] == "title") header("Location: ".$self."?title=".$current[1].":");
    echo get_marked_up_todo($with_done);
    die();
} else if(!empty($_GET['ajax'])){
    echo get_marked_up_todo(file_get_contents($file));
    die();
}

function get_marked_up_todo($todo){
    $todo = $todo."\n\n";
    $search = array('/(.+:)(.+)\n\n/sU',     // Get projects
                    '/(- ([^\@\n]+).+)/',    // Get todos
                    '/(.+:)/',               // Get headings
                    '/\n([^\<\n].+)/',       // Get notes
                    '/- (.+@done)/',         // Get done
                    '/(@due\([^\)]+\))/',    // Get due tags
                    '/(@(?!due)[^\s]+)/',    // Get tags
                    );
    $replace = array("<div class=\"project\">\n$1$2\n</div>\n\n",
                    '<span class="todo"><input type="checkbox" value="'.trim('\2').'"> \1</span>',
                    '<h1>\1</h1>',
                    "\n\t<span class=\"note\">$1</span>",
                    '- <strike>\1</strike>',
                    '<span class="tag due">\1</span>',
                    '<span class="tag">\1</span>',
                    );

    return preg_replace($search, $replace, $todo);
}

function save($todo){
    $f = fopen('todo-txt.txt', 'w');
    fwrite($f, $todo);
    fclose($f);
    return $todo;
}
function get_items_tagged($tag){
    global $file;
    $todo = file_get_contents($file);
    $todo_list = preg_replace('/(.+:)/', '', $todo);
    preg_match_all("|(-[^\-]+)|", $todo_list, $out, PREG_PATTERN_ORDER);
    $todos = array();
    foreach($out[1] as $todo_item){
        if(strpos($todo_item, $tag) != '')
            $todos[] = $todo_item;
    }
    return $todos;
}
function get_items_due($days, $inclusive=0){
    global $file;
    $todo = file_get_contents($file);
    $todo_list = preg_replace('/(.+:)/', '', $todo);
    preg_match_all("|(-[^\-]+)|", $todo_list, $out, PREG_PATTERN_ORDER);
    $todos = array();
    foreach($out[1] as $todo_item){
        if(preg_match('/@due\(([^\)]+)\)/',$todo_item,$date) == 1){
            $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $time = strtotime($date[1], $today);
            $search = $today + (60 * 60 * 24 * $days);
            if($time == $search || 
                ($inclusive == 1 && $time <= $search && $time > time())){
                     $todos[] = $todo_item;
            }
        }   
    }
    return $todos;
}
function get_project($title){
    global $file;
    $todo = file_get_contents($file);
    $todo_list = explode("\n\n", $todo);
    foreach($todo_list as $project){
        preg_match('/(.+:)/',$project,$t);
        if($title == $t[0])
            return $project;
    }
    return "";
}
function toggle_done($item){
    global $file;
    $todo = file_get_contents($file);
    $lines = explode("\n", $todo);
    for($i = 0; $i < count($lines); $i++){
        if(strpos($lines[$i],trim($item)) != ''){
            if(strpos($lines[$i], "@done") != ''){
                $lines[$i] = preg_replace('/( @done)/', '', $lines[$i]);
            } else {
                $lines[$i] = $lines[$i] . " @done";
            }
        }
    }
    $todo = implode("\n", $lines);
    return save($todo);
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">

<title>TaskPaper.web</title>
<style type="text/css">
body {
    font: 14px helvetica,sans-serif;
    padding: 10px 100px;
}
h1 {
    font: 2em "Baskerville","Garamond","Caslon",georgia,serif;
    margin: 20px 0 0 20px;
}
h1:hover {
    cursor: pointer;
}
.tag {
    color: #77d;
}
.tag:hover, #back:hover {
    color: #99f;
    cursor: pointer;
}
.todo {
    display: block;
    padding-left: .2em;
}
.note {
    padding: 0 0 0 40px;
    color: #777;
}
strike {
    color: #999;
}
#back {
    display: block;
    padding: 10px 0 0 0;
    color: #77d;
}
#edit {
    display: none;
}
#edit textarea {
    font: 14px helvetica,sans-serif;
    width: 300px;
    height: 300px;
    border: 1px solid;
    padding: 3px;
}
.instructions {
    display: block;
    width: 300px;
    margin: 50px 0 20px 0;
    background: #ccf;
    border: 2px solid #77d;
    padding: 10px;
}
.footer {
    color: #888;
    padding: 30px 50px;
}
</style>
<script type="text/javascript" src="/todo/jquery.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        add_events();
    })
    function add_events(){
        var current = $("#currently");
        var todoList = $("#todo_list");
        var edit = $("#edit");
        var editArea = $("#edit>textarea");
        
        todoList.dblclick(function(){
            if(current.val() == "index"){
                editArea.load("<?=$file?>");
                todoList.hide();
                edit.show();
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
        $("#back").click(function(){
            current.val("index");
            $.get("<?=$self?>", 
                { ajax: "true" },
                function(data){
                    todoList.html(data);
                    add_events();
                }
            );
        });
        $(".todo").each(function(){
            var checkbox = $(this).children("input");
            if(this.innerHTML.match('@done') != null)
                checkbox.attr({ checked: "checked"});
            checkbox.click(function(){
                $.get("<?=$self?>", 
                    { toggle: "true", todo: checkbox.val(), current: current.val() },
                    function(data){
                        todoList.html(data);
                        add_events();
                    }  
                )
            })
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
</script>
<!-- <div class="due_menu">Item due: <a href="#" onclick="due(1,0,'Today'); return false">today</a>, <a href="#" onclick="due(2,0,'Tomorrow'); return false">tomorrow</a>, <a href="#" onclick="due(7,1,'This Week'); return false">this week</a>.</div> -->
<div id="todo_list">
<?php 
echo get_marked_up_todo(file_get_contents($file));
?>
 
</div>
<div id="edit">
<textarea></textarea><br>
<input type="submit" value="Save">
</div>
<input type="hidden" id="currently" value="index">
<?php if($instructions){ ?>
<p class="instructions">
    You can double click on the main page to edit the content. 
    You can also click any context/tag to show you all the todo's 
    with that same context. You can click a projects heading to show
    you just that project.
</p>
<?php } ?>
<p class="footer"><a href="http://taskpaper-web.googlecode.com">TaskPaper.Web</a> created by <a href="http://e26.co.uk/">Eddie Sowden</a>.</p>
