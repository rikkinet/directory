<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
global $db, $amp_conf;

$table = \FreePBX::Database()->migrate("directory_details");
$cols = array (
  'id' =>
  array (
    'type' => 'integer',
    'primaryKey' => true,
    'autoincrement' => true,
  ),
  'dirname' =>
  array (
    'type' => 'string',
    'length' => '50',
    'notnull' => false,
  ),
  'description' =>
  array (
    'type' => 'string',
    'length' => '150',
    'notnull' => false,
  ),
  'announcement' =>
  array (
    'type' => 'integer',
    'notnull' => false,
  ),
  'callid_prefix' =>
  array (
    'type' => 'string',
    'length' => '10',
    'notnull' => false,
  ),
  'alert_info' =>
  array (
    'type' => 'string',
    'length' => '50',
    'notnull' => false,
  ),
	'rvolume' =>
	array (
		'type' => 'string',
		'length' => '2',
		'notnull' => true,
		'default' => ''
	),
  'repeat_loops' =>
  array (
    'type' => 'string',
    'length' => '3',
    'notnull' => false,
  ),
  'repeat_recording' =>
  array (
    'type' => 'integer',
    'notnull' => false,
  ),
  'invalid_recording' =>
  array (
    'type' => 'integer',
    'notnull' => false,
  ),
  'invalid_destination' =>
  array (
    'type' => 'string',
    'length' => '50',
    'notnull' => false,
  ),
  'retivr' =>
  array (
    'type' => 'string',
    'length' => '5',
    'notnull' => false,
  ),
  'say_extension' =>
  array (
    'type' => 'string',
    'length' => '5',
    'notnull' => false,
  ),
);


$indexes = array (
);
$table->modify($cols, $indexes);
unset($table);

$table = \FreePBX::Database()->migrate("directory_entries");
$cols = array (
  'id' =>
  array (
    'type' => 'integer',
  ),
  'e_id' =>
  array (
    'type' => 'integer',
    'notnull' => false,
  ),
  'name' =>
  array (
    'type' => 'string',
    'length' => '50',
    'notnull' => false,
  ),
  'type' =>
  array (
    'type' => 'string',
    'length' => '25',
    'notnull' => false,
  ),
  'foreign_id' =>
  array (
    'type' => 'string',
    'length' => '25',
    'notnull' => false,
  ),
  'audio' =>
  array (
    'type' => 'string',
    'length' => '50',
    'notnull' => false,
  ),
  'dial' =>
  array (
    'type' => 'string',
    'length' => '50',
    'notnull' => false,
    'default' => '',
  ),
);


$indexes = array (
);
$table->modify($cols, $indexes);
unset($table);

//TODO: needed? remove broken links
$files = array("cdir-please-enter-first-three.wav","cdir-transferring-further-assistance.wav","cdir-matching-entries-continue.wav","cdir-there-are.wav","cdir-welcome.wav","cdir-sorry-no-entries.wav","cdir-matching-entries-or-pound.wav");
foreach($files as $file) {
	$path = $amp_conf['ASTVARLIBDIR']."/sounds";
	if(is_link($path."/fr/".$file) && !file_exists($path."/fr/".$file)) {
		unlink($path."/fr/".$file);
	}
}
