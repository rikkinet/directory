<?php
function directory_configpageload() {
	global $currentcomponent,$display;
	if ($display == 'directory' && (isset($_REQUEST['action']) && $_REQUEST['action']=='add'|| isset($_REQUEST['id']) && $_REQUEST['id']!='')) { 
		$currentcomponent->addguielem('_top', new gui_pageheading('title', _('Directory')), 0);
					
    if ($_REQUEST['action'] == 'add') {
      $dir['dirname'] = '';
      $dir['description'] = '';
      $dir['repeat_loops'] = 2;
      $dir['announcement'] = 0;
      $dir['valid_recording'] = 0;
      $dir['repeat_recording'] = 0;
      $dir['invalid_recording'] = 0;
      $dir['callid_prefix'] = '';
      $dir['alert_info'] = '';
      $dir['invalid_destination'] = '';
      $dir['retivr'] = '';
    } else {
		  $dir=directory_get_dir_details($_REQUEST['id']);
			$label=sprintf(_("Delete Directory %s"),$dir['dirname']?$dir['dirname']:$dir['id']);
			$label='<span><img width="16" height="16" border="0" title="'.$label.'" alt="" src="images/core_delete.png"/>&nbsp;'.$label.'</span>';
			$currentcomponent->addguielem('_top', new gui_link('del', $label, $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete', true, false), 0);
    }
		//delete link, dont show if we dont have an id (i.e. directory wasnt created yet)
		$currentcomponent->addguielem('', new gui_textbox('dirname', $dir['dirname'], _('Directory Name'), _('Name of this directory.')));
		$currentcomponent->addguielem('', new gui_textbox('description', $dir['description'], _('Directory Description'), _('Description of this directory.')));
		$section = _('Directory Options');
		
		//build recordings select list
		$currentcomponent->addoptlistitem('recordings', 0, _('Default'));
		foreach(recordings_list() as $r){
			$currentcomponent->addoptlistitem('recordings', $r['id'], $r['displayname']);
		}
    $currentcomponent->setoptlistopts('recordings', 'sort', false);
		//build repeat_loops select list and defualt it to 3
		for($i=0; $i <11; $i++){
			$currentcomponent->addoptlistitem('repeat_loops', $i, $i);
		}
		
		//generate page
		$currentcomponent->addguielem($section, new gui_selectbox('announcement', $currentcomponent->getoptlist('recordings'), $dir['announcement'], 'Announcement', 'Greetinging to be played on entry to the directory', false));
		$currentcomponent->addguielem($section, new gui_selectbox('valid_recording', $currentcomponent->getoptlist('recordings'), $dir['valid_recording'], 'Valid Recording', 'Prompt to be played to caller prior to sending them to their requested destination.', false));
		$currentcomponent->addguielem($section, new gui_textbox('callid_prefix', $dir['callid_prefix'], _('CallerID Name Prefix'), _('Prefix to be appended to current CallerID Name.')));
		$currentcomponent->addguielem($section, new gui_textbox('alert_info', $dir['alert_info'], _('Alert Info'), _('ALERT_INFO to be sent with called from this Directory. Can be used for ditinctive ring for SIP devices.')));
		$currentcomponent->addguielem($section, new gui_selectbox('repeat_loops', $currentcomponent->getoptlist('repeat_loops'), $dir['repeat_loops'], 'Invalid Retries', 'Number of times to retry when receving an invalid/unmatched response from the caller', false));
		$currentcomponent->addguielem($section, new gui_selectbox('repeat_recording', $currentcomponent->getoptlist('recordings'), $dir['repeat_recording'], 'Invalid Retry  Recording', 'Prompt to be played when an invalid/unmatched response is received, before prompting the caller to try again', false));
		$currentcomponent->addguielem($section, new gui_selectbox('invalid_recording', $currentcomponent->getoptlist('recordings'), $dir['invalid_recording'], 'Invalid Recording', 'Prompt to be played before sending the caller to an alternate destination due to receiving the maximum amount of invalid/unmatched responses (as determaind by Invalid Retries)', false));
		$currentcomponent->addguielem($section, new gui_drawselects('invalid_destination', 0, $dir['invalid_destination'], _('Invalid Destination'), _('Destination to send the call to after Invalid Recording is played.'), false));
		$currentcomponent->addguielem($section, new gui_checkbox('retivr', $dir['retivr'], 'Return to IVR', 'When selected, if the call passed through an IVR that had "Return to IVR" selected, the call will be returned there instead of the Invalid destination.',true));
		$currentcomponent->addguielem($section, new gui_hidden('id', $dir['id']));
		$currentcomponent->addguielem($section, new gui_hidden('action', 'edit'));
		
    //TODO: the &nbsp; needs to be here instead of a space, guielements freaks for some reason with this specific section name
		$section = _('Directory&nbsp;Entries');
		//draw the entries part of the table. A bit hacky perhaps, but hey - it works!
		$currentcomponent->addguielem($section, new guielement('rawhtml', directory_draw_entires($_REQUEST['id']), ''));
	}
}

function directory_configpageinit($pagename) {
	global $currentcomponent;
	if($pagename=='directory'){
		$currentcomponent->addprocessfunc('directory_configprocess');
		$currentcomponent->addguifunc('directory_configpageload');
	}
}


//prosses received arguments
function directory_configprocess(){
	if($_REQUEST['display']=='directory'){
		global $db,$amp_conf;
		//get variables for directory_details
		$requestvars=array('id','dirname','description','announcement','valid_recording',
												'callid_prefix','alert_info','repeat_loops','repeat_recording',
												'invalid_recording','invalid_destination','retivr');
		foreach($requestvars as $var){
			$vars[$var]=isset($_REQUEST[$var])?$_REQUEST[$var]:'';
		}

		$action=isset($_REQUEST['action'])?$_REQUEST['action']:'';
		$entries=isset($_REQUEST['entries'])?$_REQUEST['entries']:'';
		$entries=(($entries)?array_values($entries):'');//reset keys

		switch($action){
			case 'edit':
				//get real dest
				$vars['invalid_destination']=$_REQUEST[$_REQUEST[$_REQUEST['invalid_destination']].str_replace('goto','',$_REQUEST['invalid_destination'])];
				$vars['id']=directory_save_dir_details($vars);
				directory_save_dir_entries($vars['id'],$entries);
				redirect_standard('id');
			break;
			case 'delete':
				directory_delete($vars['id']);
				redirect_standard();
			break;
		}
	}
}

function directory_get_config($engine) {
	global $ext,$db;
	switch ($engine) {
		case 'asterisk':
			$sql='SELECT id,dirname FROM directory_details ORDER BY dirname';
			$results=$db->getAll($sql,DB_FETCHMODE_ASSOC);
			if($results){
				foreach ($results as $row) {
					$ext->add('directory',$row['id'], '', new ext_agi('directory.agi,dir='.$row['id']));
				}
			}
		break;
	}
}

function directory_get_dir_entries($id){
	global $db;

	$sql = 'SELECT a.name, a.type, a.audio, a.dial, a.foreign_id, b.name foreign_name, IF(a.name != "",a.name,b.name) realname 
		FROM directory_entries a LEFT JOIN users b ON a.foreign_id = b.extension WHERE id = "'.$id.'" ORDER BY realname';
	$results = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
	return $results;
}

function directory_get_dir_details($id){
	global $db;
	$sql='SELECT * FROM directory_details WHERE ID = ?';
	$row=$db->getRow($sql,array($id),DB_FETCHMODE_ASSOC);
	return $row;
}

function directory_delete($id){
	global $db;
	$sql='DELETE FROM directory_details WHERE id = ?';
	$db->query($sql,array($id));
	$sql='DELETE FROM directory_entries WHERE id = ?';
	$db->query($sql,array($id));
}

function directory_destinations(){
	global $db;
	$sql='SELECT id,dirname FROM directory_details ORDER BY dirname';
	$results=$db->getAll($sql,DB_FETCHMODE_ASSOC);

	foreach($results as $row){
		$row['dirname']=($row['dirname'])?$row['dirname']:'Directory '.$row['id'] ;
		$extens[] = array('destination' => 'directory,' . $row['id'] . ',1', 'description' => $row['dirname'], 'category' => _('Directory'));
	}
	return isset($extens)?$extens:null;
}


function directory_draw_entires($id){
	global $db;
	$sql='SELECT id,name FROM directory_details ORDER BY name';
	$results=$db->getAll($sql,DB_FETCHMODE_ASSOC);
	$html='';
	$html.='<table id="dir_entires_tbl">';
	//$html.='<th>User</th><th>Name</th><th>Name Announcement</th><th>Dial</th>';
	$html.='<thead><th>Name</th><th>Name Announcement</th><th>Dial</th></thead>';
	$newuser='<select id="addusersel">';
	$newuser.='<option value="none" selected> == '._('Choose One').' == </option>';
	$newuser.='<option value="all">'._('All Users').'</option>';
	$newuser.='<option value="|">'._('Custom').'</option>';
	foreach(core_users_list() as $user){
		$newuser.='<option value="'.$user[0].'|'.$user[1].'">('.$user[0].') '.$user[1]."</option>\n";
	}
	$newuser.='</select>';
	$html.='<tfoot><tr><td id="addbut"><a href="#" class="info"><img src="images/core_add.png" name="image" style="border:none;cursor:pointer;" /><span>'._('Add new entry.').'</span></a></td><td colspan="3"id="addrow">'.$newuser.'</td></tr></tfoot>';
	$html.='<tbody>';
	$entries=directory_get_dir_entries($id);
	$arraynum=1;
	foreach($entries as $e){
		$realid = $e['type'] == 'custom' ? 'custom' : $e['foreign_id'];
		$foreign_name = $e['foreign_name'] == '' ? 'Custom Entry' : $e['foreign_name'];
		$html.=directory_draw_entires_tr($realid, $e['name'],$foreign_name, $e['audio'],$e['dial'],$arraynum++);
	}
	$html.='</tbody></table>';
	return $html;
}

//used to add row's the entry table
function directory_draw_entires_tr($realid, $name='',$foreign_name, $audio='',$num='',$id, $reuse_audio=false){
	global $directory_draw_recordings_list;//make global, so its only drawn once
	if(!$directory_draw_recordings_list){$directory_draw_recordings_list=recordings_list();}

  // if reuse_audio is true create once, used by all_users to avoid recreating each time
  global $audio_select;
  if (!$audio_select || !$reuse_audio) {
    unset($audio_select);
    $audio_select='<select name="entries['.$id.'][audio]">';
    $audio_select.='<option value="vm" '.(($audio=='vm')?'SELECTED':'').'>'._('Voicemail Greeting').'</option>';
    $audio_select.='<option value="tts" '.(($audio=='tts')?'SELECTED':'').'>'._('Text to Speech').'</option>';
    $audio_select.='<option value="spell" '.(($audio=='spell')?'SELECTED':'').'>'._('Spell Name').'</option>';
    $audio_select.='<optgroup label="'._('System Recordings:').'">';
    foreach($directory_draw_recordings_list as $r){
	    $audio_select.='<option value="'.$r['id'].'" '.(($audio==$r['id'])?'SELECTED':'').'>'.$r['displayname'].'</option>';
    }
    $audio_select.='</select>';
  }

	//$delete='<img src="images/trash.png" style="cursor:pointer;" alt="'._('remove').'" title="'._('Click here to remove this pattern').'" onclick="$(\'.entrie'.$id.'\').fadeOut(500,function(){$(this).remove()})">';
		$delete='<img src="images/trash.png" style="cursor:pointer;" alt="'._('remove').'" title="'._('Click here to remove this pattern').'" class="trash-tr">';
	$t1_class = $name == '' ? ' class = "dpt-title" ' : '';
	$t2_class = $realid == 'custom' ? ' title="Custom Dialstring" ' : ' title="'.$realid.'" ';
	if (trim($num)  == '') {
    $t2_class .= '" class = "dpt-title" ';
  }
  
	//$html='<tr class="entrie'.$id.'"><td><label>'.$realid.'</label><input type="hidden" readonly="readonly" name="entries['.$id.'][foreign_id]" value="'.$realid.'" /></td><td><input type="text" name="entries['.$id.'][name]" title="'.$foreign_name.'"'.$t1_class.' value="'.$name.'" /></td><td>'.$audio_select.'</td><td><input type="text" name="entries['.$id.'][num]" '.$t2_class.' value="'.$num.'" /></td><td>'.$delete.'</td></tr>';
	$html='<tr class="entrie'.$id.'"><td><input type="hidden" readonly="readonly" name="entries['.$id.'][foreign_id]" value="'.$realid.'" /><input type="text" name="entries['.$id.'][name]" title="'.$foreign_name.'"'.$t1_class.' value="'.$name.'" /></td><td>'.$audio_select.'</td><td><input type="text" name="entries['.$id.'][num]" '.$t2_class.' value="'.$num.'" /></td><td>'.$delete.'</td></tr>';
	return $html;
}

//used to add ALL USERS to the entry table
function directory_draw_entires_all_users($id){
	$html='';
	foreach(core_users_list() as $user){
    $html .= directory_draw_entires_tr($user[0], '', $user[1], 'vm', '',$id++, true);
	}
	return $html;
}

function directory_save_dir_details($vals){
	global $db;
	global $amp_conf;

  //TODO: this is very error prone depending on vals being exactly right, this needs some work

  //TODO: need to $db->escapeSimple() all these values

  if ($vals['id']) {
	  $sql='REPLACE INTO directory_details (id,dirname,description,announcement,
				valid_recording,callid_prefix,alert_info,repeat_loops,repeat_recording,
				invalid_recording,invalid_destination,retivr)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $foo=$db->query($sql,$vals);
	  if(DB::IsError($foo)) {
		  die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
	  }
  } else {
    unset($vals['id']);
	  $sql='INSERT INTO directory_details (dirname,description,announcement,
				valid_recording,callid_prefix,alert_info,repeat_loops,repeat_recording,
				invalid_recording,invalid_destination,retivr)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $foo=$db->query($sql,$vals);
	  if(DB::IsError($foo)) {
		  die_freepbx(print_r($vals,true).' '.$foo->getDebugInfo());
	  }
		$sql=(($amp_conf["AMPDBENGINE"]=="sqlite3")?'SELECT last_insert_rowid()':'SELECT LAST_INSERT_ID()');
		$vals['id']=$db->getOne($sql);
	  if (DB::IsError($foo)){
      die_freepbx($foo->getDebugInfo());
    }
	}
	return $vals['id'];
}

function directory_save_dir_entries($id,$entries){
	global $db;
	$sql='DELETE FROM directory_entries WHERE id =?';
	$foo=$db->query($sql,array($id));
	if (DB::IsError($foo)){
    die_freepbx($foo->getDebugInfo());
	}
	if($entries){
		$insert='';
    // TODO: should we change to perpare/execute ?
		foreach($entries as $idx => $row){
			if($row['foreign_id'] == 'custom' && trim($row['name']) == '' || $row['foreign_id']==''){
				continue;//dont insert a blank row
			}
			if ($row['foreign_id'] == 'custom') {
				$type = 'custom';
				$foreign_id = '';
			} else {
				$type = 'user';
				$foreign_id = $row['foreign_id'];
			}
      $audio = $row['audio'] != '' ? $row['audio'] : ($row['foreign_id'] == 'custom' ? 'tts' : 'vm');
			$insert.='("'.$id.'","'.trim($row['name']).'","'.$type.'","'.$foreign_id.'","'.$audio.'","'.trim($row['num']).'")';
			if(count($entries) != $idx+1){//add a , if its not the last entrie
				$insert.=',';
			}
		}		
		$sql='INSERT INTO directory_entries (id,name,type,foreign_id,audio,dial) VALUES '.$insert;
		$foo=$db->query($sql);
		if (DB::IsError($foo)){
	    die_freepbx($foo->getDebugInfo());
		}
	}
}
?>
