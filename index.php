<?php

define('IN_PHPBB', true);

$wbbMySQLConnection = array(
	'host'		=> 'localhost',
	'user'		=> 'creativesandbox',
	'password'	=> 'creativesandbox',
	'database'	=> 'creativesandbox_phpbb',
	'wbbNum'	=> '25292',
);

$wbbPath = '/www/oldwbb/';

$phpbbMySQLConnection = array(
	'host'		=> 'localhost',
	'user'		=> 'creativesandbox',
	'password'	=> 'creativesandbox',
	'database'	=> 'creativesandbox_phpbb',
	'prefix'	=> 'phpbb_',
);

function convertBBCode($text)
{
	return $text;
}

$phpBBPath = '/www/phpbb/';

$wbbDb		= new mysqli($wbbMySQLConnection['host'], $wbbMySQLConnection['user'], $wbbMySQLConnection['password'], $wbbMySQLConnection['database']);
$phpbbDb	= new mysqli($phpbbMySQLConnection['host'], $phpbbMySQLConnection['user'], $phpbbMySQLConnection['password'], $phpbbMySQLConnection['database']);

include $phpBBPath.'includes/utf/utf_tools.php';
include $phpBBPath.'includes/functions.php';
include $phpBBPath.'includes/constants.php';

// Step 1 - Prepare phpbb Tables

$rootUser	= $phpbbDb->query("SELECT * FROM {$phpbbMySQLConnection['prefix']}_users WHERE user_id = 2;")->fetch_assoc();
$lastUserId	= reset($phpbbDb->query("SELECT MAX(user_id) FROM {$phpbbMySQLConnection['prefix']}_users;")->fetch_num());

$phpbbDb->query("TRUNCATE {$phpbbMySQLConnection['prefix']}acl_users;");
$phpbbDb->query("TRUNCATE {$phpbbMySQLConnection['prefix']}topics_posted;");
$phpbbDb->query("TRUNCATE {$phpbbMySQLConnection['prefix']}topics;");
$phpbbDb->query("TRUNCATE {$phpbbMySQLConnection['prefix']}forums;");
$phpbbDb->query("TRUNCATE {$phpbbMySQLConnection['prefix']}posts;");

$phpbbDb->query("DELETE FROM {$phpbbMySQLConnection['prefix']}users WHERE user_id = 2;");
$phpbbDb->query("DELETE FROM {$phpbbMySQLConnection['prefix']}acl_groups WHERE forum_id != 0;");

$phpbbConfigResult	= $phpbbDb->query("SELECT * FROM {$phpbbMySQLConnection['prefix']}config;");
$phpbbConfig		= array();
while($configRow = $phpbbConfigResult->fetch_assoc())
{
	$phpbbConfig[$configRow['config_name']]	= $configRow['config_value'];
}

$phpbbConfigResult->close();

// Step 2 - Import User

$wbbUsers	= $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_users
	INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_users USING userID;");

while($wbbUser = $wbbUsers->fetch_assoc())
{
	$phpBBUser	= array(
		'user_id'					=> $wbbUser['userID'] + $lastUserId,
		'user_type'					=> USER_NORMAL,
		'group_id'					=> 2,
		'user_permissions'			=> '',
		'user_perm_from'			=> 0,
		'user_ip'					=> $wbbUser['registrationIpAddress'],
		'user_regdate'				=> $wbbUser['registrationDate'],
		'username'					=> $wbbUser['username'],
		'username_clean'			=> utf8_clean_string($wbbUser['username']),
		'user_password'				=> phpbb_hash($wbbUser['password']),
		'user_passchg'				=> time(),
		'user_pass_convert'			=> 1,
		'user_email'				=> $wbbUser['email'],
		'user_email_hash'			=> phpbb_email_hash($wbbUser['email']),
		'user_birthday'				=> sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']),
		'user_lastvisit'			=> $wbbUser['boardLastActivityTime'],
		'user_lastmark'				=> $wbbUser['boardLastMarkAllAsReadTime'],
		'user_lastpost_time'		=> 0,
		'user_lastpage'				=> '',
		'user_last_confirm_key'		=> '',
		'user_last_search'			=> 0,
		'user_warnings'				=> 0,
		'user_last_warning'			=> 0,
		'user_login_attempts'		=> 0,
		'user_inactive_reason'		=> 0,
		'user_inactive_time'		=> 0,
		'user_posts'				=> 0,
		'user_lang'					=> $phpbbConfig['default_lang'],
		'user_timezone'				=> ,
		'user_dst'					=> $phpbbConfig['board_dst'],
		'user_dateformat'			=> $phpbbConfig['default_dateformat'],
		'user_style'				=> $phpbbConfig['default_style'],
		'user_rank'					=> ,
		'user_colour'				=> ,
		'user_new_privmsg'			=> 0,
		'user_unread_privmsg'		=> 0,
		'user_last_privmsg'			=> 0,
		'user_message_rules'		=> 0,
		'user_full_folder'			=> PRIVMSGS_NO_BOX,
		'user_emailtime'			=> 0,
		'user_notify'				=> 0,
		'user_notify_pm'			=> 1,
		'user_notify_type'			=> NOTIFY_EMAIL,
		'user_allow_pm'				=> 1,
		'user_allow_viewonline'		=> 1,
		'user_allow_viewemail'		=> 1,
		'user_allow_massemail'		=> 1,
		'user_options'				=> 230271,
		'user_avatar'				=> ,
		'user_avatar_type'			=> ,
		'user_avatar_width'			=> ,
		'user_avatar_height'		=> ,
		'user_sig'					=> convertBBCode($wbbUser['signature']),
		'user_sig_bbcode_uid'		=> ,
		'user_sig_bbcode_bitfield'	=> ,
		'user_from'					=> ,
		'user_icq'					=> ,
		'user_aim'					=> ,
		'user_yim'					=> ,
		'user_msnm'					=> ,
		'user_jabber'				=> ,
		'user_website'				=> ,
		'user_occ'					=> '',
		'user_interests'			=> ,
		'user_actkey'				=> '',
		'user_form_salt'			=> unique_id(),
		'user_new'					=> 0
	);

	if($wbbUser['email'] == $rootUser['user_email'])
	{
		$phpBBUser['user_type']	= USER_FOUNDER;
		$phpBBUser['group_id']	= 3;

		$phpbbDb->query("INSERT INTO {$phpbbMySQLConnection['prefix']}acl_users SET
		user_id			= {$phpBBUser['user_id']},
		forum_id		= 0
		auth_option_id	= 0
		auth_role_id	= 5
		auth_setting	= 0;");
		$phpBBUser['user_password']		= $rootUser['user_password'];
		$phpBBUser['user_rank']			= $rootUser['user_rank'];
		$phpBBUser['user_colour']		= $rootUser['user_colour'];
		$phpBBUser['user_pass_convert']	= 0;
	}

	$phpbbDb->query("INSERT INTO {$phpbbMySQLConnection['prefix']}user_group (user_id, group_id, user_pending)
		VALUES ({$phpBBUser['user_id']}, 2, 0)");
}

$wbbUsers->close();