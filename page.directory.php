<?php

//check for ajax request and process that immediately 
if(isset($_REQUEST['ajaxgettr'])){//got ajax request
  $opts = $opts=explode('|',urldecode($_REQUEST['ajaxgettr']));
	if($opts[0] == 'all') {
    echo directory_draw_entires_all_users($opts[1]);
	}else{
		if ($opts[0] != '') {
			$real_id = $opts[0];
			$name = '';
			$realname = $opts[1];
			$audio = 'vm';
		} else {
			$real_id = 'custom';
			$name = $opts[1];
			$realname = 'Custom Entry';
			$audio = 'tts';
		}
		echo directory_draw_entires_tr($real_id, $name, $realname, $audio,'',$opts[2]);
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
	echo '<br /><br /><input type="button" value="'._('Add a new Directory').'" onclick="window.location.href=\'/admin/config.php?type='.$type.'&display=directory&action=add\';"/>';
	echo '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
}

function directory_drawListMenu(){
	global $db,$id,$type;
	$sql='SELECT id,dirname FROM directory_details ORDER BY dirname';
	$results=$db->getAll($sql,DB_FETCHMODE_ASSOC);
	echo '<div class="rnav"><ul>'."\n";
	echo "\t<li><a href=\"config.php?type=$type&display=directory&action=add\">"._('Add Directory')."</a></li>\n";
	if($results){
		foreach ($results as $key=>$result){
			if(!$result['dirname']){$result['dirname']='Directory '.$result['id'];}
			echo "\t<li><a".($id==$result['id'] ? ' class="current"':''). ' href="config.php?type='.$type.'&display=directory&id='.$result['id'].'">'.$result['dirname']."</a></li>\n";
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
	
	//add row button
	$('#addrow').change(function(){
		$(this).fadeOut(250,
		function(){
			$('#addbut').not("span").fadeIn(250).find("span").hide();
		});
		if($('#addusersel').val()!='none'){
			var rownum=$('[class^=entrie]').length+1;
			//increment id untill we find one that isnt being used
			while($('.entrie'+rownum).length==1){
				rownum++;
			}
			addrow($('#addusersel').val()+'|'+rownum);
		}
		return false;
	});
	
	//set toggle value for text-box hint text
  $(".dpt-title").toggleVal({
    populateFrom: "title",
    changedClass: "text-normal",
    focusClass: "text-normal"
  });
	$("form").submit(function() {
    $(".dpt-title").each(function() {
      if($(this).val() == $(this).data("defText")) {
        $(this).val("");
      }
    });
	});
	
	//delete row when trash can is clicked
	$('.trash-tr').live('click', function(){
	$(this).parents('tr').fadeOut(500,
		function(){
			$(this).remove()
		})
	});
	
	
});


//add a new entry to the table
function addrow(user){
	$.ajax({
		type: 'POST',
	  url: location.href,
	  data: 'ajaxgettr='+encodeURIComponent(user)+'&quietmode=1&skip_astman=1&restrictmods=directory/core/recordings',
	  success: function(data) {
	    $('#dir_entires_tbl > tbody:last').append(data);
      /* now re-apply toggleval - redundant but they may have appended multipe values so... */
      $(".dpt-title").not('.text-normal').toggleVal({
        populateFrom: "title",
        changedClass: "text-normal",
        focusClass: "text-normal"
      });
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
      var msg = "<?php echo _("An Error occurred trying to contact the server adding a row, no reply.")?>";
      alert(msg);
    }
  });
}
</script>

<style type="text/css">
#addrow{display:none;}
#dir_entires_tbl :not(tfoot) tr:nth-child(odd){background-color:#FCE7CE;}
.dpt-title {color: #CCCCCC;}
.text-normal {color: inherit;}
</style>
