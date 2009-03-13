<?php
	// We'll need the tpwproject class.
	require_once(INC_DIR . 'tpwproject.class.php');
	
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
			//$index = "";
			$index = 0;
			foreach($file_list as $line)
			{
				if(preg_match('/'.RE_PROJ.'/',$line,$matches))
				{
					$line = rtrim($line);
					$this->taskpaper[$index] = new TPWProject($line,$index);
					$index++;
				}
				else
				{
					// Just skip blank lines
					if("\n" != $line)
					{
						if(count($this->taskpaper) == 0)
						{
							// We didn't start out with a project line so
							// project 0 is going to be a pseudo project.
							// Just make it a blank line to signal the project object.
							$this->taskpaper[0] = new TPWProject("",0);
							$index = 1;
						}
						
						$line = rtrim($line);
						$this->taskpaper[$index - 1]->addLine($line);
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
			foreach($this->taskpaper as $project_key => $project)
			{
				$project_name = $project->projectName();
				if(isset($project_name) && $project_name != "")
				{
					$project_name = htmlspecialchars($project_name,ENT_QUOTES);
					$project_list_html .= "\t\t<option value=\"" . 
						lineID_PROJECT . ":$project_key:0\">$project_name</option>\n";
				}
			}
			return $project_list_html;
		} // end function GetProjectListHTML()
		
		
		// function GetProjectHTML($in_project_key, $singleProject)
		function GetProjectHTML($in_project_key,$singleProject = false)
		{
			$project_html = $this->taskpaper[$in_project_key]->projectHTML();
			if($singleProject) // We are in "single" project view so use different css.
				$project_html = str_replace("project_widget","project_widget_back",$project_html);
			return $project_html;
		} // end function GetProjectHTML($in_project_key,$singleProject)
		
		
		// GetTaskPaperHTML()
		function GetTaskPaperHTML()
		{
			//print_r($this->taskpaper); exit;
			$taskpaper_html = "";
			foreach($this->taskpaper as $project_key => $project)
			{
				$project_html = $this->GetProjectHTML($project_key);
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
		

		// SaveEditedLine($in_line_type,$in_project_key,$in_line_key,$new_text)
		function SaveEditedLine($in_line_type,$in_project_key,$in_line_key,$new_text)
		{
			$new_text = strip_tags($new_text);
			if($this->taskpaper[$in_project_key]->changeLine($new_text,$in_line_key))
			{
				$this->_updateRawData(); // Update the "raw" variable.
				$this->SaveTaskPaper(); // Save the change to disk.
				return $this->taskpaper[$in_project_key]->oneLine($in_line_key);
			}
			else
			{
				return false; //// RETURN SOME ERROR CONDITION
			}
		} // function SaveEditedLine($in_line_type,$in_project_key,$in_line_key,$new_text)
		
		
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
		function ToggleDoneState($in_id)
		{
			$line_info = explode(":",$in_id);
			// After explosion we should have the following structure:
			$line_id = $line_info[0];		// [0] line ID
			$project_key = $line_info[1];	// [1] project key
			$line_key = $line_info[2];		// [2] line key
			
			if($this->taskpaper[$project_key]->toggleDoneState($line_key))
			{
				// Have to update our raw version since we changed a line!
				$this->_updateRawData();
			
				// Then save the changes out to the file
				$this->SaveTaskPaper();
			}
		} // end function ToggleDoneState($in_line)
		
		
// --------------------------------------- Private Functions -------------------------------------// 

		// _updateRawData()
		function _updateRawData()
		{
			$this->taskpaper_raw = "";
			foreach($this->taskpaper as $project_key => $project)
			{
				$this->taskpaper_raw .= implode("\n",$project->allLines());
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