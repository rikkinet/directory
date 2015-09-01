<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2015 Sangoma Technologies.
?>


<table id="dirgrid" data-url="?display=directory&amp;action=getJSON&amp;jdata=grid&amp;quietmode=1" data-cache="false" data-height="299" data-toggle="table" class="table table-striped">
	<thead>
			<tr>
			<th data-field="name"><?php echo _("Directory")?></th>
			<th data-field="default" data-formatter="defaultFormatter"><?php echo _("Default Direcrory")?></th>
			<th data-field="link" data-formatter="linkFormatter"><?php echo _("Actions")?></th>
		</tr>
	</thead>
</table>
