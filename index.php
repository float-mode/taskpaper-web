<?php 
	require_once(dirname(__FILE__) . '/inc/defines.inc.php');
	require_once(INC_DIR . 'includes.inc.php');
	
	// Create a TaskPaper object.
	$taskpaper = new TaskPaperWeb($cfg_taskPapers[0]);

	// Handle "ajax" (javascript) requests.
	if(isset($_POST['task']) && $_POST['task'] != '')
	{
		echo get_marked_up_todo(save($_POST['task']));
	}
	else if(!empty($_GET['plain']))
	{
		print $taskpaper->GetTaskPaperText();
	}
	else if(!empty($_GET['tag']))
	{
		$items = get_items_tagged($_GET['tag']);
		print "<h1>".$_GET['tag']."</h1>\n";
		foreach($items as $item)
		{
			print get_marked_up_todo($item);
		}
	}
	else if(!empty($_GET['days']))
	{
		$items = get_items_due($_GET['days'], $_GET['inclusive']);
		print "<h1>".$_GET['title']."</h1>\n";
		foreach($items as $item)
		{
			print get_marked_up_todo($item);
		}
	}
	else if(!empty($_GET['title']))
	{
		print "Made it!!";
		$project_html = $taskpaper->GetProjectHTML($_GET['title'],true);
		// Make sure the title value got is good by making sure project html output is good.
		if(false !== $project_html)
			print $project_html;
		else
			include(INC_DIR . 'index.inc.php');
	}
	else if(!empty($_GET['toggle']))
	{
		$view = explode(":",$_GET['view']);
		if($view[0] == "tag") header("Location: ?tag=".stripslashes($view[1]));
		if($view[0] == "title") header("Location: ?title=".stripslashes($view[1]).":");
		$taskpaper->ToggleDoneState($_GET['toggle']);
		print $taskpaper->GetTaskPaperHTML();
	}
	else if(!empty($_GET['ajax']))
	{
		print $taskpaper->GetTaskPaperHTML();
	}
	else
	{
		include(INC_DIR . 'index.inc.php');
	}




?>
