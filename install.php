<?php
global $db;
global $amp_conf;

if (! function_exists("out")) {
	function out($text) {
		echo $text."<br />";
	}
}

if (! function_exists("outn")) {
	function outn($text) {
		echo $text;
	}
}

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

outn(_('Adding directory_details table if needed...'));

$sql = "CREATE TABLE IF NOT EXISTS directory_details (
    id INT NOT NULL PRIMARY KEY $autoincrement,
    dirname varchar(50),
    description varchar(150),    
    announcement INT,
    valid_recording INT,
    callid_prefix varchar(10),
    alert_info varchar(50),
    repeat_loops varchar(3),
    repeat_recording INT,
    invalid_recording INT,
    invalid_destination varchar(50),
    retivr varchar(5),
    say_extension varchar(5)
)";

$check = $db->query($sql);
if (DB::IsError($check)) {
  out(_('failed'));
	out(_('Can not create `directory_details` table: ') . $check->getMessage());
  return false;
}
out(_('ok'));
outn(_('Adding directory_entries table if needed...'));

$sql = "CREATE TABLE IF NOT EXISTS directory_entries (
    id INT NOT NULL,
    name varchar(50),
    type varchar(25),
    foreign_id varchar(25),
    audio varchar(50),
    dial varchar(50) default ''
);";

$check = $db->query($sql);
if (DB::IsError($check)) {
  out(_('failed'));
	out(_('Can not create `directory_entries` table: ') . $check->getMessage());
}
out(_('ok'));

$sql = "SELECT say_extension FROM directory_details";
$check = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($check)) {
  // add new field
  outn(_("adding say_extension field to directory_details.."));
  $sql = "ALTER TABLE directory_details ADD say_extension VARCHAR(5)";
  $result = $db->query($sql);
  if(DB::IsError($result)) { 
    out(_("fatal error"));
    die_freepbx($result->getDebugInfo()); 
  } else {
    out(_("ok"));
  }
}
?>
