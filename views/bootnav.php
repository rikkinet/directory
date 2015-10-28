<div id="toolbar-dirbootnav">
	<a href="?display=directory&view=form" class="btn btn-default"><i class="fa fa-plus"></i>&nbsp;<?php echo _("Add Directory")?></a>
  <a href="config.php?display=directory" class="btn btn-default"><i class="fa fa-list"></i>&nbsp; <?php echo _("List Directories") ?></a>
</div>
<table id="dirgrid" data-url="ajax.php?module=directory&command=getJSON&jdata=grid" data-cache="false" data-toggle="table" data-search="true" data-pagination="true" data-toolbar="#toolbar-dirbootnav" class="table table-striped">
	<thead>
			<tr>
			<th data-field="name" data-formatter="dirlinkformatter"><?php echo _("Directory")?></th>
		</tr>
	</thead>
</table>
<script type="text/javascript">
  function dirlinkformatter(v,r){
    return '<a href="?display=directory&view=form&id='+r['id']+'">'+v+'</a>';
  }
</script>
