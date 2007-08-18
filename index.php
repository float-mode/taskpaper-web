<?php 
include('./functions.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>TaskPaper.web</title>
    <link rel="stylesheet" type="text/css" href="./style.css">
    <script type="text/javascript" src="./jquery.js"></script>
    <script type="text/javascript" src="./javascript.php"></script>
</head>
<body>
<div id="wrapper">
<div id="menu">
    <input type="button" id="edit_back" value="Edit"> 
    <select id="project_select">
        <option value=''>Select Project</option>
 <?php 
    $projects = get_projects_list();
    foreach($projects as $project){
        print "\t\t<option value=\"$project\">$project</option>\n";
    }?>
</select>
        <!-- <div class="due_menu">Item due: <a href="#" onclick="due(1,0,'Today'); return false">today</a>, <a href="#" onclick="due(2,0,'Tomorrow'); return false">tomorrow</a>, <a href="#" onclick="due(7,1,'This Week'); return false">this week</a>.</div> -->
</div>
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
</div>
<p class="footer"><a href="http://taskpaper-web.googlecode.com">TaskPaper.Web</a> created by <a href="http://e26.co.uk/">Eddie Sowden</a>.</p>
</body>
</html>