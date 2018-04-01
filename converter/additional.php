<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 22.01.14
 * Time: 21:50
 */

// Insert default users
foreach($phpBBDefaultUsers as $phpBBUser)
{
    if($phpBBUser['user_type'] != USER_FOUNDER)
    {
        unset($phpBBUser['user_id']);
        insertData(USERS_TABLE, $phpBBUser);
        output('row');
    }
}

// Add and register custom auth method
copy(dirname(__FILE__).'files/phpbb30/auth_wbb_db.php', $phpBBPath.'includes/auth/auth_wbb_db.php');
replaceInFile($phpBBPath.'includes/auth/auth_wbb_db.php', "define('ENCRYPTION_ENABLE_SALTING', 1);", "define('ENCRYPTION_ENABLE_SALTING', ".$wbbConfig['encryption_enable_salting'].");");
replaceInFile($phpBBPath.'includes/auth/auth_wbb_db.php', "define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', 1);", "define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', ".$wbbConfig['encryption_encrypt_before_salting'].");");
replaceInFile($phpBBPath.'includes/auth/auth_wbb_db.php', "define('ENCRYPTION_METHOD', 'sha1');", "define('ENCRYPTION_METHOD', '".$wbbConfig['encryption_method']."');");
replaceInFile($phpBBPath.'includes/auth/auth_wbb_db.php', "define('ENCRYPTION_SALT_POSITION', 'before');", "define('ENCRYPTION_SALT_POSITION', '".$wbbConfig['encryption_salt_position']."');");

replaceInFile($phpBBPath.'includes/constants.php', "// Additional tables", "// Additional tables\n\ndefine('USERS_WBB_PASSWORDS_TABLE',	\$table_prefix . 'users_wbb_passwords');");

$phpBBConfigUpdate  = array(
    'config_value'  => 'wbb_db',
);
updateData(CONFIG_TABLE, $phpBBConfigUpdate, "config_name = 'auth_method'");

output('row');


// Set the anonymous user on phpbb constants
$phpBBAnonymous = $phpBBDb->query("SELECT user_id FROM {$phpBBMySQLConnection['prefix']}users WHERE group_id = 1;");
if(empty($phpBBAnonymous))
{
    throw new Exception('Can not find the anonymous user on phpbb tables!');
}

$phpBBAnonymousId = reset($phpBBAnonymous->fetch_row());
replaceInFile($phpBBPath.'includes/constants.php', "define('ANONYMOUS', ".ANONYMOUS.");", "define('ANONYMOUS', {$phpBBAnonymousId});");
output('row');

// Clear phpBB cache
array_map('unlink', glob($phpBBPath.'cache/*.php'));
output('row');

// BBCodes
// Add align bbcode
$bbCode = array(
	'bbcode_id' 			=> 13,
	'bbcode_tag' 			=> 'align=',
	'bbcode_helpline'		=> '',
	'display_on_posting' 	=> 0,
	'bbcode_match' 			=> '[align={IDENTIFIER}]{TEXT}[/align]',
	'bbcode_tpl' 			=> '<div style="text-align:{IDENTIFIER}">{TEXT}</div>',
	'first_pass_match' 		=> $phpBBDb->real_escape_string('!\\[align\\=([a-zA-Z0-9-_]+)\\](.*?)\\[/align\\]!ies'),
	'first_pass_replace' 	=> $phpBBDb->real_escape_string('\'[align=${1}:$uid]\'.str_replace(array("\r\n", \'\\"\', \'\\\'\', \'(\', \')\'), array("\n", \'"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${2}\')).\'[/align:$uid]\''),
	'second_pass_match' 	=> $phpBBDb->real_escape_string('!\\[align\\=([a-zA-Z0-9-_]+):$uid\\](.*?)\\[/align:$uid\\]!s'),
	'second_pass_replace' 	=> $phpBBDb->real_escape_string('<div style="text-align:${1}">${2}</div>')
);

insertData(BBCODES_TABLE, $bbCode);
output('row');

// Add font bbcode
$bbCode = array(
    'bbcode_id' 			=> 14,
    'bbcode_tag'            => 'font=',
    'bbcode_helpline'       => '',
    'display_on_posting'    => 0,
    'bbcode_match'          => '[font={SIMPLETEXT}]{TEXT}[/font]',
    'bbcode_tpl'            => '<span style="font-family:{SIMPLETEXT}">{TEXT}</span>',
    'first_pass_match'      => $phpBBDb->real_escape_string('!\\[font\\=([a-zA-Z0-9-+.,_ ]+)\\](.*?)\\[/font\\]!ies'),
    'first_pass_replace'    => $phpBBDb->real_escape_string('\'[font=${1}:$uid]\'.str_replace(array("\r\n", \'\\"\', \'\\\'\', \'(\', \')\'), array("\n", \'"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${2}\')).\'[/font:$uid]\''),
    'second_pass_match'     => $phpBBDb->real_escape_string('!\\[font\\=([a-zA-Z0-9-+.,_ ]+):$uid\\](.*?)\\[/font:$uid\\]!s'),
	'second_pass_replace'   => $phpBBDb->real_escape_string('<span style="font-family:${1}">${2}</span>')
);

insertData(BBCODES_TABLE, $bbCode);
output('end');