<?php
include('./settings.php');


function get_marked_up_todo($todo){
    $todo = htmlspecialchars($todo,ENT_QUOTES)."\n\n";
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
    global $file;
    $todo = stripslashes($todo);
    $f = fopen($file, 'w');
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
function get_projects_list(){
    global $file;
    $todo = file_get_contents($file);
    $todo_list = explode("\n\n", $todo);
    $project_list = array();
    foreach($todo_list as $project){
       preg_match('/(.+:)/',$project,$t);
       $project_list[] = $t[0];
    }
    return $project_list;
}
function toggle_done($item){
    global $file;
    $todo = file_get_contents($file);
    $lines = explode("\n", $todo);
    for($i = 0; $i < count($lines); $i++){
        if(strpos(addslashes($lines[$i]),trim($item)) != ''){
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