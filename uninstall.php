<?php
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
outn(_('dropping directory_details, directory_entries..'));
sql('DROP TABLE IF EXISTS directory_details, directory_entries');
out(_('ok'));

outn(_('deleting default_directory and migration tracking keys..'));
sql("DELETE FROM `admin` WHERE `variable` = 'default_directory'");
sql("DELETE FROM `admin` WHERE `variable` = 'directory28_migrated'");
out(_('ok'));
?>
