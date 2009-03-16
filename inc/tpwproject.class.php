<?php
	// We require tpwline.class.php - included in includes.inc.php.
	// We rely on the global defines - in defines.inc.php
	
	class TPWProject
	{
		private $project_key = null;
		private $project_name = null; // The "cleaned" project "name"
		private $task_lines = array();
		
		
		function TPWProject($in_project_line,$in_project_key)
		{
			$this->project_key = $in_project_key;
			// Strip all characters after (and including) the project delim ":"
			$this->project_name = substr($in_project_line,0,strpos($in_project_line,':'));
			$this->addLine($in_project_line); // The original project line.
		}
				
		public function addLine($in_line)
		{
			$line_key = count($this->task_lines);
			$this->task_lines[] = new TPWLine($in_line,$line_key,$this->project_key);
		}
		
		public function projectName()
		{
			return $this->project_name;
		}
	
		public function projectLine()
		{
			return $this->task_lines[0]->rawLine();
		}
		
		public function projectLineHTML()
		{
			return $this->task_lines[0]->htmlLine();
		}

		public function oneLine($in_key)
		{
			// Bounds check
			if($this->_isGoodLineKey($in_key))
			{
				return $this->task_lines[$in_key]->rawLine();
			}
			else
			{
				return false;
			}
		}
		
		public function oneLineHTML($in_key)
		{
			// Bounds check
			if($this->_isGoodLineKey($in_key))
			{
				return $this->task_lines[$in_key]->htmlLine();
			}
			else
			{
				return false;
			}
		}
		
		public function allLines()
		{
			$all_lines = array();
			foreach($this->task_lines as $this_line)
			{
				$all_lines[] = $this_line->rawLine();
			}
			return $all_lines;
		}
		
		// function projectHTML()
		public function projectHTML()
		{	
			foreach($this->task_lines as $line_key => $line)
			{
				$project_html .= $line->htmlLine();
			}
			return $project_html;
					
		} // end function projectHTML()
		
		
		// changeLine($in_new_text,$in_line_key)
		public function changeLine($in_new_text,$in_line_key)
		{
			if($this->_isGoodLineKey($in_line_key))
			{
				$this->task_lines[$in_line_key]->newValue($in_new_text);
				return true;
			}
			else
			{
				return false;
			}
		} // end public function changeLine($in_new_text,$in_line_key)
		
		
		// public function toggleDoneState($in_line_key,$in_should_add_date = false)
		public function toggleDoneState($in_line_key,$in_should_add_date = false)
		{
			// Bounds check
			if(! $this->_isGoodLineKey($in_line_key))
			{
				return false; // This is a failure condition.
			}
			
			if($this->task_lines[$in_line_key]->toggleDoneState($in_should_add_date))
			{
				return true;
			}
		} // end public function toggleDoneState($in_line_key,$in_should_add_date = false)


// --------------------------------------- Private Functions -------------------------------------//

				
		// _isGoodLineKey($in_key)
		private function _isGoodLineKey($in_key)
		{
			if(isset($in_key) && is_numeric($in_key) &&
				$in_key < count($this->task_lines) &&
				$in_key >= 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		} // end private function _isGoodLineKey($in_key)
		
	}
?>