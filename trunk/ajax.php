<?php
include('./functions.php');

if(isset($_POST['todo']) && $_POST['todo'] != ''){
    echo get_marked_up_todo(save($_POST['todo']));
} else if(!empty($_GET['tag'])){
    $items = get_items_tagged($_GET['tag']);
    print "<h1>".$_GET['tag']."</h1>\n";
    foreach($items as $item){
        print get_marked_up_todo($item);
    }
} else if(!empty($_GET['days'])){
    $items = get_items_due($_GET['days'], $_GET['inclusive']);
    print "<h1>".$_GET['title']."</h1>\n";
    foreach($items as $item){
        print get_marked_up_todo($item);
    }
} else if(!empty($_GET['title'])){
    $project = get_project($_GET['title']);
    print get_marked_up_todo($project);
} else if(!empty($_GET['toggle'])){
    $with_done = toggle_done($_GET['todo']);
    $current = explode(":",$_GET['current']);
    if($current[0] == "tag") header("Location: ".$self."?tag=".$current[1]);
    if($current[0] == "title") header("Location: ".$self."?title=".$current[1].":");
    echo get_marked_up_todo($with_done);
} else if(!empty($_GET['ajax'])){
    echo get_marked_up_todo(file_get_contents($file));
}
?>