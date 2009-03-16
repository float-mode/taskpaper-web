<?php 
	require_once(dirname(__FILE__) . '/inc/defines.inc.php');
	require_once(INC_DIR . 'includes.inc.php');
	
	// Create a TaskPaper object.
	$taskpaper = new TaskPaperWeb($cfg_taskPapers[0]); // <------- handle the array here somehow

	
	
	$action = "";
	if(isset($_POST['action']) && $_POST['action'] != '')
	{
		$action = $_POST['action'];
	}
	elseif(!empty($_GET['action']))
	{
		$action = $_GET['action'];
	}
	
	// Handle ajax requests.
	switch($action)
	{
		case act_EDIT_FIELD :
			$line_info = lineInfo($_POST['item']);
			print $taskpaper->SaveEditedLine($line_info[0],$line_info[1],
				$line_info[2],$_POST['value']);
			break;
		case act_EDIT_PLAINTEXT :
			print $taskpaper->GetTaskPaperText();
			break;
		case act_PROJECT_VIEW :
			$line_info = lineInfo($_GET['proj']);
			$project_html = $taskpaper->GetProjectHTML($line_info[1],true);
			// Make sure the title value we got is good by checking GetProjectHTML result.
			if(false !== $project_html)
				print $project_html;
			//else
				//GetError?????????????
			break;
		case act_TOGGLE_DONE :
			// Config flag specifies if we add a date value to done tags.
			global $cfg_addDateToDone;

			$taskpaper->ToggleDoneState($_GET['item'],$cfg_addDateToDone);
			$view = explode(":",$_GET['view']);
			// After explosion we should have the following structure:
			// [0] view name
			// [1] line ID
			// [2] project key
			// [3] line key
			if($view[0] == "tag")
			{
				header("Location: ?action=tag&tag=".stripslashes($view[1]));
				print $taskpaper->GetTaskPaperHTML(); // <--------------------------- This should be tag!!
			}
			elseif($view[0] == "proj")
			{
				header("Location: ?action=proj&proj=".$view[2].":".$view[3]);
				print $taskpaper->GetProjectHTML($view[2],true);
			}
			else
			{
				print $taskpaper->GetTaskPaperHTML();
			}
			break;
		case act_GENERIC_AJAX :
			print $taskpaper->GetTaskPaperHTML();
			break;
		default :
			include(INC_DIR . 'index.inc.php');
	}
	
	function lineInfo($in_info)
	{
		$line_info = explode(":",$in_info);
		// After explosion we should have the following structure:
		// [0] Line ID
		// [1] Project key
		// [2] Line key
		
		return $line_info;
	}
	
	if(isset($_POST['task']) && $_POST['task'] != '')
	{
		echo get_marked_up_todo(save($_POST['task']));
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


?>
