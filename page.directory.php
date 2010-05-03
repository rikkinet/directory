<?php

//check for ajax request and process that immediately 
if(isset($_REQUEST['ajaxgettr'])){//got ajax request
	if($_REQUEST['ajaxgettr']=='all'){
			echo directory_draw_entires_all_users();
	}else{
		$opts=explode('|',$_REQUEST['ajaxgettr']);
		echo directory_draw_entires_tr($opts[1],'',$opts[0],$opts[2]);
	}
	exit;
}

//get vars
$requestvars=array('id','action','entries','newentries');
foreach($requestvars as $var){
	$$var=isset($_REQUEST[$var])?$_REQUEST[$var]:'';
}
//draw right nav bar
directory_drawListMenu();


if($action=='' && $id==''){
	echo '<h2 id="title">Directory</h2>';
	echo '<br /><br /><input type="button" value="'._('Add a new Directory').'" onclick="window.location.href=\'/admin/config.php?type=tool&display=directory&action=add\';"/>';
	echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
}

function directory_drawListMenu(){
	global $db,$id;
	$sql='SELECT id,dirname FROM directory_details ORDER BY dirname';
	$results=$db->getAll($sql,DB_FETCHMODE_ASSOC);
	echo '<div class="rnav"><ul>'."\n";
	echo "\t<li><a href=\"config.php?type=tool&display=directory&action=add\">"._('Add Directory')."</a></li>\n";
	if($results){
		foreach ($results as $key=>$result){
			if(!$result['dirname']){$result['dirname']='Directory '.$result['id'];}
			echo "\t<li><a".($id==$result['id'] ? ' class="current"':''). ' href="config.php?type=tool&display=directory&id='.$result['id'].'">'.$result['dirname']."</a></li>\n";
		}
	}
	echo "</ul>\n<br /></div>";
}

?>

<script type="text/javascript">
$(document).ready(function(){
	//show/hide add button/dropdown
	$('#addbut').click(function(){
		$('#addusersel').val('none');//reset select box
		$(this).fadeOut(250,
		function(){
			$('#addrow').fadeIn(250);
		});
		return false;
	});
	$('#addrow').change(function(){
		$(this).fadeOut(250,
		function(){
			$('#addbut').not("span").fadeIn(250).find("span").hide();
		});
		if($('#addusersel').val()!='none'){
			var rownum=$('[class^=entrie]').length+1;
			//pick anohter id if this one already exists for some reason
			while($('.entrie'+rownum).length==1){
				rownum++;
			}
			addrow($('#addusersel').val()+'|'+rownum);
		}
		return false;
	})		
});

//add a new entry to the table
function addrow(user){
	$.ajax({
	  url: location.href,
	  data: 'ajaxgettr='+user+'&quietmode=1&skip_astman=1',
	  success: function(data) {
	    $('.result').html(data);
	    $('#dir_entires_tbl').last().append(data);
	  }
});

}
</script>

<style type="text/css">
#addrow{display:none;}
#dir_entires_tbl :not(tfoot) tr:nth-child(odd){background-color:#FCE7CE;}
</style>
