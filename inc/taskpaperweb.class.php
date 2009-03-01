<?php
	class TaskPaperWeb
	{
		// Main data structure.
		// This is populated on object construction but if it is ever updated
		// by a class function, you must subsequently call _updateRawData()!
		var $taskpaper = array();
		// TaskPaper plain text.
		// Raw version of the task paper - can be updated with _updateRawData()
		var $taskpaper_raw = "";
		var $taskpaper_filepath = ""; // Path to the taskpaper file.
						
		// Constructor
		function TaskPaperWeb($in_file)
		{
			$this->taskpaper_filepath = $in_file;
			if($this->ReadTaskPaper($this->taskpaper_filepath))
				return true;
			else
				return false;
		} // end function TaskPaperWeb($in_file)
		
		// ReadTaskPaper($in_file)
		function ReadTaskPaper($in_file)
		{
			// Read the selected task paper file
			if(! is_file($in_file)) {
				echo "Died: $infile";
				return false;
			}
				
			$file_list = file($in_file);
			$index = "";
			foreach($file_list as $line)
			{
				if(preg_match('/'.RE_PROJ.'/',$line,$matches))
				{
					$line = rtrim($line);
					$index = $this->_cleanProjectName($line);
					$this->taskpaper[$index] = array();
					$this->taskpaper[$index][$line] = $line;
				}
				else
				{
					if("\n" != $line)
					{
						// Throw away blank lines
						$line = rtrim($line);
						$this->taskpaper[$index][$line] = $line;
					}
				}
			}
			$this->_updateRawData();
			return true;
		} // end function ReadTaskPaper($in_file)
		
		// SaveTaskPaper()
		function SaveTaskPaper()
		{
			// Make sure our raw data variable has the latest...
			$this->_updateRawData();
			
			if(file_exists($this->taskpaper_filepath))
				$file_handle = fopen($this->taskpaper_filepath, 'w');
			if(false !== $file_handle)
				fwrite($file_handle, $this->taskpaper_raw);
			fclose($file_handle);
		} // end function SaveTaskPaper()
		
		
		// GetTaskPaperText()
		function GetTaskPaperText()
		{
			return $this->taskpaper_raw;
		} // end function GetTaskPaperText()
		
		// GetProjectListHTML()
		function GetProjectListHTML()
		{
			$project_list_html = "";
			foreach($this->_buildProjectList() as $project)
			{
				$project_list_html .= "\t\t<option value=\"$project\">$project</option>\n";
			}
			return $project_list_html;
		} // end function GetProjectListHTML()
		
		
		// GetTaskPaperHTML()
		function GetTaskPaperHTML()
		{
			//print_r($this->taskpaper); exit;
			$taskpaper_html = "";
			foreach($this->taskpaper as $project => $value)
			{
				$project_html = $this->GetProjectHTML($project);
				if(false !== $project_html)
				{
					$taskpaper_html .= "<div class=\"project\">\n"; // Set up the project div.
					$taskpaper_html .= $project_html;
					$taskpaper_html .= "</div>\n\n"; // Close out project div.
					if(end($this->taskpaper) != $value) // Add a seperator div.
						$taskpaper_html .= "<div class=\"project_seperator\"></div>\n";
				}
			}
			return $taskpaper_html;
		} // end function GetTaskPaperHTML();
		
		
		// GetProjectHTML($in_project,[singleProject])
		function GetProjectHTML($in_project, $singleProject = false)
		{	
			$in_project = $this->_cleanLine($in_project);
			$project = $this->_cleanProjectName($in_project);
			
			foreach($this->taskpaper as $project_key => $value)
			{
				if($project == $project_key)
				{
					$project_html = "";
					foreach($this->taskpaper[$project] as $line)
					{
						$project_html .= $this->_applyMarkup($line);
					}
					if($singleProject) // We are in "single" project view so use different css.
					$project_html = str_replace("project_widget","project_widget_back",$project_html);
					return $project_html;
				}
			}
			return false;
		} // end function GetProjectHTML($in_project)


		// GetTaskpaperName()
		function GetTaskpaperName()
		{
			// The name of the current taskpaper
			$taskpaper_name = basename($this->taskpaper_filepath);
			
			// Remove the file name extension
			$ext = strrchr($taskpaper_name,'.'); 
    		if($ext !== false) 
				$taskpaper_name = substr($taskpaper_name, 0, -strlen($ext)); 
			
			return $taskpaper_name;
		} // end function GetTaskpaperName()
		
		
		// ToggleDoneState($in_line)
		function ToggleDoneState($in_line)
		{
			$in_line = $this->_cleanLine($in_line);			
			foreach($this->taskpaper as $project => $lines)
			{
				foreach($lines as $key => $value)
				{
					if(false !== strpos($value,$in_line))
					{
						if(false !== strpos($value,DONE_TAG))
							$this->taskpaper[$project][$key] = str_replace(' '.DONE_TAG,"",$value);
						else
							$this->taskpaper[$project][$key] .= ' ' . DONE_TAG;
					}
				}
			}
			// Have to update our raw version since we changed a line!
			$this->_updateRawData();
			
			// Then save the changes out to the file
			$this->SaveTaskPaper();
		} // end function ToggleDoneState($in_line)
		
		
// --------------------------------------- Private Functions -------------------------------------// 

		// _buildProjectList()
		function _buildProjectList()
		{
			foreach($this->taskpaper as $project => $list)
			{
				$project_list[] = $project;
			}
			return $project_list;
		} // end function _buildProjectList()

		
		// _cleanProjectName($in_project)
		function _cleanProjectName($in_project)
		{
			// Strip all characters after (and including) the project delim ":"
			$clean_project_name = substr($in_project,0,strpos($in_project,':'));
			if("" != $clean_project_name)
				return $clean_project_name;
			else
				return $in_project;
		} // end function _cleanProjectName($in_project)
		
		
		// _cleanLine($in_line)
		function _cleanLine($in_line)
		{
			$out_line = htmlspecialchars_decode($in_line,ENT_QUOTES);
			$out_line = stripslashes($out_line);
			return $out_line;
		} // end function _cleanLine($in_line)
		
		
		// _applyMarkup($in_line)
		function _applyMarkup($in_line)
		{
			$line = htmlspecialchars($in_line,ENT_QUOTES);
				
			if(preg_match('/'.RE_PROJ.'/',$line,$matches))
			{
				// Markup the project line
				return $this->_markupProject($line);
			}	
			elseif(preg_match('/'.RE_TASK.'/',$line,$matches))
			{
				// Markup the task line
				// Use $matches[3] since it doesn't have leading indentation or the hyphen.
				return $this->_markupTask($matches[3]);
			}
			else
			{
				// Markup the note line
				return $this->_markupNote($line);
			}
			return NULL;
		} // end function _applyMarkup($in_line)


		// _markupProject($in_line)
		function _markupProject($in_line)
		{
			// Markup up the project name
			$project_markup = "<span class=\"project_name\">";
						
			// Save the tags from the end of the line.
			preg_match('/'.RE_ENDTAGS.'/',$in_line,$end_tags);
			// Extract just the project name - remove the end tags completely.
			$project_name = $this->_cleanProjectName($in_line);
			
			// Check for a "done" tag in and act accordingly.
			if($this->_tagsIncludeDoneTag($in_line))							
				$project_markup .= "<span class=\"done\">$project_name</span>: ";
			else
				$project_markup .= $project_name . ":";

			// Reapply the stripped off end tags.
			$project_markup .= $end_tags[0];
			
			// Markup the tags in the line.
			$project_markup = $this->_markupTags($project_markup);
										
			// Close the project line.
			$project_markup .= "</span>\n";
			
			// Add the project widget to the start of the line.
			$project_markup = "<span class=\"project_widget\" title=\"$in_line\"></span>\n" . 
								$project_markup;

			return $project_markup;
		} // end function _markupProject($in_line)


		// _markupTask($in_line)
		function _markupTask($in_line)
		{
			// Markup the task
			$task_markup = "<span class=\"task\">";
			
			// Save the tags from the end of the line.
			preg_match('/'.RE_ENDTAGS.'/',$in_line,$end_tags);
			// Extract just the task text - remove the end tags completely.
			$task_text = preg_replace('/'.RE_ENDTAGS.'/',"",$in_line);
						
			// Notice we've added a leading space to the regex.
			$tagless_task = preg_replace('/ '.RE_TAG.'/',"",$in_line);

			/////////////////$task_markup .= "<input type=\"checkbox\" value=\"$tagless_task\" />";
			
			// Check for a "done" tag in and act accordingly.
			if($this->_tagsIncludeDoneTag($in_line))
				$task_markup .= "<span class=\"done\">$task_text</span>";
			else
				$task_markup .= $task_text;
				
			// Reapply the stripped off end tags.
			$task_markup .= $end_tags[0];
			
			// Markup the tags in the line
			$task_markup = $this->_markupTags($task_markup);
			
			// Close the task line.
			$task_markup .= "</span><br />\n";
			
			// Add the task widget to the start of the line.
			$task_markup = "<span class=\"task_widget\" title=\"$in_line\"></span>\n" . 
								$task_markup;
						
			return $task_markup;
		} // end function _markupTask($in_line)

	
		// _markupNote($in_line)
		function _markupNote($in_line)
		{
			$note_markup = "<span class=\"note\">";
			
			// Save the tags from the end of the line.
			preg_match('/'.RE_ENDTAGS.'/',$in_line,$end_tags);
			// Extract just the note text - remove the end tags completely.
			$note_text = preg_replace('/'.RE_ENDTAGS.'/',"",$in_line);
			
			// Check for a "done" tag in and act accordingly.
			if($this->_tagsIncludeDoneTag($in_line))
				$note_markup .= "<span class=\"done\">$note_text</span>";
			else
				$note_markup .= $note_text;
				
			// Reapply the stripped off end tags.
			$note_markup .= $end_tags[0];
			
			// Markup the tags in the line
			$note_markup = $this->_markupTags($note_markup);
			
			// Close the note line.
			$note_markup .= "</span><br />\n";
						
			// Add the note widget to the start of the line.
			$note_markup = "<span class=\"note_widget\" title=\"$in_line\"></span>\n" . 
								$note_markup;
						
			return $note_markup;
		} // end function _markupNote($in_line)
		

		// _markupTags($in_line)
		function _markupTags($in_line)
		{
			// Markup the tags
			$tag_markup = preg_replace('/'.RE_TAG.'/',"<span class=\"tag\">$1$2</span>",$in_line);			
			$tag_markup = str_replace('<span class="tag">@',
								"<span class=\"at\">@</span><span class=\"tag\">",$tag_markup);
			return $tag_markup;
		} // end function _markupTags($in_line)
		
		
		// _tagsIncludeDoneTag($in_line)
		function _tagsIncludeDoneTag($in_line)
		{
			$done_flag = false;
			if(preg_match_all('/'.RE_TAG.'/',$in_line,$tags))
			{ // We want to take the full match here.
				foreach($tags[1] as $tag)
				{
					if(DONE_TAG == $tag)
						return true;
				}
			}
			return false;
		} // end function _tagsIncludeDoneTag($in_line)
		
		
		// _updateRawData()
		function _updateRawData()
		{
			$this->taskpaper_raw = "";
			foreach($this->taskpaper as $project => $lines)
			{
				$this->taskpaper_raw .= implode("\n",$lines);
				$this->taskpaper_raw .= "\n";
			}
		} // end function _updateRawData()
		
		
		// GetErrorMessages()
		function GetErrorMessages()
		{
// 			if(!file_exists($self)){
// 				$error = "Can't find ajax file. You may need to set its location in settings.php";
// 			} else if(!file_exists('./'.$file)){
// 				$error = "Can't find your taskpaper document. You may need to set its location in settings.php";
// 			} else if(is_writable('./'.$file)){
// 				if($auto_writable && copy($file, $file.".tmp")){
// 					unlink($file);
// 					copy($file.".tmp", $file);
// 					unlink($file.".tmp");
// 					chmod($file, 0777);
// 				} else {
// 					$error = "Your taskpaper document is not writable so you will be unable to save chnages";
// 				}
// 			}
// 				if(isset($error))
// 					return '<div class="error"><img src="error.png"> '.$error.'</div>';
				return '';
		} // end function GetErrorMessages()
		
	}
?>