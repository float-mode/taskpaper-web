<?php
	// We rely on the global defines - in defines.inc.php
	
	class TPWLine
	{
		private $raw_line = null;
		private $line_key = null;
		private $project_key = null;
		
		function TPWLine($in_line,$in_line_key,$in_project_key)
		{
			$this->raw_line		= $in_line;
			$this->line_key		= $in_line_key;
			$this->project_key	= $in_project_key;
		}
		
		function rawLine()
		{
			return $this->raw_line;
		}
		
		function htmlLine()
		{
			return $this->_applyMarkup();
		}
		
		// public function newValue($in_new_text)
		public function newValue($in_new_text)
		{
			// Make the line the new value we got.
			$this->raw_line = stripslashes($in_new_text);
			return true; // Return success
		} // end public function newValue($in_new_text)
		
		
		// public function toggleDoneState($in_should_add_date = false)
		public function toggleDoneState($in_should_add_date = false)
		{
			$line = $this->raw_line;
			
			// See if the line already has a done tag.
			if(false !== strpos($line,DONE_TAG))
			{
				// We've found a @done tag so we need to remove it.
				$this->raw_line = preg_replace('/ '.RE_DONE_TAG.'/',"",$line);
			}
			else
			{
				// The is no done tag so we'll add one.
				$done_tag = DONE_TAG;
				if($in_should_add_date)
				{
					// Add a date to the done tag per the config file.
					// The recommended date format for TaskPaper is YYYY-MM-DD, where
					// YYYY’s are for year, MM’s are for month, and DD’s are for day.
					$done_tag .= "(" . date(DATE_FORMAT_STRING) . ")";
				}
				$this->raw_line .= ' ' . $done_tag;
			}
			return true; // We're successful!
		} // end public function toggleDoneState($in_should_add_date = false)

		
// --------------------------------------- Private Functions -------------------------------------//


		// _applyMarkup()
		private function _applyMarkup()
		{
			$line = htmlspecialchars($this->raw_line,ENT_QUOTES);

			if(preg_match('/'.RE_PROJ.'/',$line,$matches))
			{
				// Markup the project line
				return $this->_markupProject($line);
			}	
			elseif(preg_match('/'.RE_TASK.'/',$line,$matches))
			{
				// Markup the task line
				// Use $matches[3] since it doesn't have leading indentation or the hyphen.
				return $this->_markupTask($matches[3],$in_line_key);
			}
			elseif("" == $line)
			{
				// In a rare occasion (there are tasks or notes before the 
				// first project line) we will get a blank line. The taskpaper
				// object creates a project "pseudo" project to handle this.
				// Don't apply any markup to it. Just return it as is.
				return $line;
			}
			else
			{
				// Markup the note line
				return $this->_markupNote($line);
			}
			return NULL;
		} // end function _applyMarkup()


		// _markupProject($in_line)
		private function _markupProject($in_line)
		{
			// Markup up the project name
			$project_markup = "<p class=\"editable project_name\" id=\"" .
				lineID_PROJECT . ":$this->project_key:$this->line_key\">";
						
			// Strip off and save the tags from the end of the line.
			preg_match('/'.RE_ENDTAGS.'/',$in_line,$end_tags);
			
			// Capture just the project name part of the line (no tags)
			preg_match('/'.RE_PROJ.'/',$in_line,$matches);
			
			// Check for a "done" tag in and act accordingly.
			if($this->_tagsIncludeDoneTag($in_line))							
				$project_markup .= "<span class=\"done\">$matches[1]</span> ";
			else
				$project_markup .= $matches[1];

			// Reapply the stripped off end tags.
			$project_markup .= $end_tags[0];
			
			// Markup the tags in the line.
			$project_markup = $this->_markupTags($project_markup);
										
			// Close the project line.
			$project_markup .= "</p>\n";
			
			// Add the project widget to the start of the line.
			$project_markup = "<span class=\"project_widget\" id=\"" . 
				lineID_PROJECT_WIDGET . ":$this->project_key:$this->line_key\"></span>\n" . 
				$project_markup;

			return $project_markup;
		} // end function _markupProject($in_line)


		// _markupTask($in_line)
		private function _markupTask($in_line)
		{
			// Markup the task
			$task_markup = "<p class=\"editable task\" id=\"" . 
				lineID_TASK . ":$this->project_key:$this->line_key\">";
			
			// Save the tags from the end of the line.
			preg_match('/'.RE_ENDTAGS.'/',$in_line,$end_tags);
			// Extract just the task text - remove the end tags completely.
			$task_text = preg_replace('/'.RE_ENDTAGS.'/',"",$in_line);
						
			// Notice we've added a leading space to the regex.
			$tagless_task = preg_replace('/ '.RE_TAG.'/',"",$in_line);
			
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
			$task_markup .= "</p><br />\n";
			
			// Add the task widget to the start of the line.
			$task_markup = "<span class=\"task_widget\" id=\"" . 
				lineID_TASK_WIDGET . ":$this->project_key:$this->line_key\"></span>\n" . 
				$task_markup;
						
			return $task_markup;
		} // end function _markupTask($in_line)

	
		// _markupNote($in_line)
		private function _markupNote($in_line)
		{
			$line = $in_line;
			
			$note_markup = "<p class=\"editable note\" id=\"" . 
				lineID_NOTE . ":$this->project_key:$this->line_key\">";
			
			// Save the tags from the end of the line.
			preg_match('/'.RE_ENDTAGS.'/',$line,$end_tags);
			// Extract just the note text - remove the end tags completely.
			$note_text = preg_replace('/'.RE_ENDTAGS.'/',"",$line);
			
			// Check for a "done" tag in and act accordingly.
			if($this->_tagsIncludeDoneTag($line))
				$note_markup .= "<span class=\"done\">$note_text</span>";
			else
				$note_markup .= $note_text;
				
			// Reapply the stripped off end tags.
			$note_markup .= $end_tags[0];
			
			// Markup the tags in the line
			$note_markup = $this->_markupTags($note_markup);
			
			// Close the note line.
			$note_markup .= "</p><br />\n";
						
			// Add the note widget to the start of the line.
			$note_markup = "<span class=\"note_widget\" id=\"" . 
				lineID_NOTE_WIDGET . ":$this->project_key:$this->line_key\"></span>\n" . 
				$note_markup;
						
			return $note_markup;
		} // end function _markupNote()
		

		// _markupTags($in_line)
		private function _markupTags($in_line)
		{
			// Markup the tags
			$tag_markup = preg_replace('/'.RE_TAG.'/',"<span class=\"tag\">$1$2</span>",$in_line);			
			$tag_markup = str_replace('<span class="tag">@',
								"<span class=\"at\">@</span><span class=\"tag\">",$tag_markup);
			return $tag_markup;
		} // end function _markupTags($in_line)
		
		
		// _tagsIncludeDoneTag($in_line)
		private function _tagsIncludeDoneTag($in_line)
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

	}
?>