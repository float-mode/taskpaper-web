<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<!--
	<meta name="viewport" content="width=device-width, user-scalable=yes" />
	<meta name="format-detection" content="telephone=no" />
-->
    <title><?php echo TITLE ?></title>
    <link rel="stylesheet" type="text/css" href="thm/<?php echo $cfg_theme ?>/css/style.css" />
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/editinplace.js"></script>
    <script type="text/javascript" src="js/taskpaperweb.js"></script>
</head>
<body>

<!-- Toolbar -->
<div id="toolbar_left">
	<div id="toolbar_right">

		<div id="toolbar">
			<div id="paper_name">
			<div id="paper_icon"></div>
			<?php print $taskpaper->GetTaskpaperName() ?></div>
    
    		<input type="button" id="edit_back" value="Edit" /> 
    		<select id="project_select">
        		<option disabled="disabled" value=''>Go to Project...</option>
				<?php print $taskpaper->GetProjectListHTML() ?>
			</select>
			
			<select id="tag_select">
				<option disabled="disabled" value=''>Go to Tag...</option>
				<?php //print $taskpaper->GetTagListHTML() ?>
			</select>
		</div>
		
	</div><!-- end toolbar_right -->
</div><!-- end toolbar_left -->


<!-- Content -->
<div id="content_left">
	<div id="content_right">

		<div id="content">
			<? print $taskpaper->GetErrorMessages() ?>
			<div id="task_list">
				<?php print $taskpaper->GetTaskPaperHTML(); ?>
			</div><!-- end task_list -->
			<div id="edit">
				<textarea rows="20" cols="50"></textarea><br />
				<input type="submit" value="Save" />
			</div>
			<input type="hidden" id="view" value="index" />
		</div><!-- end content -->

	</div><!-- end content_right -->
</div><!-- cend ontent_left -->


<!-- Footer -->
<div id="footer_left">
	<div id="footer_right">
		<div id="footer_wrapper">
			<div id="footer">
				<?php echo CREDITS ?>
			</div><!-- end footer -->
		</div><!-- end footer_wrapper -->
	</div><!-- end footer_right -->
</div><!-- end footer_left -->

</body>
</html>
