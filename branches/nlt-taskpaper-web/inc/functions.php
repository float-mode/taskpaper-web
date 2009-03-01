<?php
include('./settings.php');


function get_marked_up_todo($project){
    $todo = htmlspecialchars($project,ENT_QUOTES)."\n\n";
    $search = array(//'/(.+:)(.+)\n\n/sU',     // Get projects
    				'/(.+:\n)/sU',
                    '/(- ([^\@\n]+).+)/',    // Get todos
                    '/(.+:\n)/',               // Get headings
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

	if(is_array($project)) {
		$html = "<div class=\"project\">\n" . key($project) . "\n</div>\n\n";
	
		return $html;
	}

    return preg_replace($search, $replace, $project);
}

function save($todo){
    global $file,$webdav;
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
    //$todo = file_get_contents($file);
	$todo_list = explode("\n\n", $todo);
	
	
	$todo_list = file($file);
	
    foreach($todo_list as $line) {
    	if(preg_match('/(.+:\n)/',$line,$t)) {
    		$project = substr_replace($t[0],"",-1);
    		$project_list[$project] = array();
    	}
    	else {
    		$project_list[$project][] = $line;
    	}
    }
    
   // print $title; exit;
    print_r($project_list["$title"]); exit;
    return $project_list[$title];
}

function get_projects_list(){
    global $file;
    $todo_list = file($file);
    foreach($todo_list as $line) {
		if(preg_match('/(.+):\n/',$line,$t))
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
function get_errors(){
    global $self, $file, $auto_writable;
    if(!file_exists($self)){
        $error = "Can't find ajax file. You may need to set its location in settings.php";
    } else if(!file_exists('./'.$file)){
        $error = "Can't find your taskpaper document. You may need to set its location in settings.php";
    } else if(is_writable('./'.$file)){
        if($auto_writable && copy($file, $file.".tmp")){
            unlink($file);
            copy($file.".tmp", $file);
            unlink($file.".tmp");
            chmod($file, 0777);
        } else {
            $error = "Your taskpaper document is not writable so you will be unable to save chnages";
        }
    }
    if(isset($error))
         return '<div class="error"><img src="error.png"> '.$error.'</div>';
    return '';
}
?>