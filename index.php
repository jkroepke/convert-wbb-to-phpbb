<?php

@set_time_limit(0);
chdir(dirname(__FILE__));

$startTime = microtime(true);

/**
 *
 * WBB 3.1 to phpBB 3.0.12 converter
 * convert features:
 * user
 * user group
 * user rank
 * user avatar
 * user friends
 * user ignore list
 * TODO: user passwords ?
 * private messages
 * private message folders
 * private message attachments
 * forums
 * topic
 * topics subscription
 * posts
 * polls
 * TODO: bbcodes
 *
 * Polls: Note that only polls placed in the first post of every topic will be converted.
 *
 */

define('IN_PHPBB', true);

//TODO: Add command line help

require 'config.php';

$phpBBPath = realpath($phpBBPath).'/';
$wbbPath   = realpath($wbbPath).'/';

if(!file_exists($phpBBPath.'includes/utf/utf_tools.php'))
{
    throw new Exception("Invalid phpBB path '{$phpBBPath}'!");
}

if(!file_exists($wbbPath.'wcf/config.inc.php'))
{
    throw new Exception("Invalid wbb path '{$wbbPath}'!");
}

if(!in_array(PHPBB_VERSION, array('3.0.12')))
{
    throw new Exception('phpBB Version must be 3.0.12!');
}

require 'functions.php';

require $phpBBPath.'includes/utf/utf_tools.php';
require $phpBBPath.'includes/functions.php';
require $phpBBPath.'includes/functions_convert.php';
require $phpBBPath.'includes/constants.php';

if(!class_exists('mysqli'))
{
    throw new Exception('Extension mysqli is required. Exiting.');
}

$wbbDb   = new mysqli($wbbMySQLConnection['host'], $wbbMySQLConnection['user'],
    $wbbMySQLConnection['password'], $wbbMySQLConnection['database']);

$phpBBDb = new mysqli($phpBBMySQLConnection['host'], $phpBBMySQLConnection['user'],
    $phpBBMySQLConnection['password'], $phpBBMySQLConnection['database']);


// get the wbb config.
$wbbConfigResult = $phpBBDb->query("SELECT optionName, optionValue FROM wcf{$wbbMySQLConnection['wbbNum']}_option;");
$wbbConfig       = array();
while($configRow = $wbbConfigResult->fetch_assoc())
{
    $wbbConfig[$configRow['optionName']] = $configRow['optionValue'];
}

$wbbConfigResult->close();

// get the phpbb config.
$phpBBConfigResult = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}config;");
$phpBBConfig       = array();
while($configRow = $phpBBConfigResult->fetch_assoc())
{
    $phpBBConfig[$configRow['config_name']] = $configRow['config_value'];
}

$phpBBConfigResult->close();


// check if avatar and attachment directories are readable and writable
if (!is_readable($wbbPath.'wcf/attachments') || !@chmod($wbbPath.'wcf/attachments', 0777))
{
    throw new Exception("No read access to directory '{$wbbPath}wcf/attachments'!");
}
if (!is_writeable($phpBBPath.$phpBBConfig['upload_path']) || !@chmod($phpBBPath.$phpBBConfig['upload_path'], 0777))
{
    throw new Exception("No write access to directory '{$phpBBPath}{$phpBBConfig['upload_path']}'!");
}

if (!is_readable($wbbPath.'wcf/images/avatars') || !@chmod($wbbPath.'wcf/images/avatars', 0777))
{
    throw new Exception("No read access to directory '{$wbbPath}wcf/images/avatars'!");
}
if (!is_writeable($phpBBPath.$phpBBConfig['avatar_path']) || !@chmod($phpBBPath.$phpBBConfig['avatar_path'], 0777))
{
    throw new Exception("No write access to directory '{$phpBBPath}{$phpBBConfig['avatar_path']}'!");
}

$convertProcess = array(
    'prepare',
    'user',
    'user-group',
    'user-to-group',
    'user-rank',
    'user-avatar',
    'user-friends',
    'user-ignore-list',
    'private-messages',
    'private-messages-folder',
    'board',
    'board-subscriptions',
    'topic',
    'post',
    'topic-subscriptions',
    'poll',
    'poll-options',
    'poll-votes',
    'attachments'
);

$numberOfProcesses  = count($convertProcess);

foreach($convertProcess as $stepNum => $converterName)
{
    echo "\n\n[{".(1 + $stepNum)."}/{$numberOfProcesses}] Starting {$converterName} step... \n";

    $converterFile = "converter/{$converterName}.php";

    if(!file_exists($converterFile))
    {
        throw new Exception("Can not load converter {$converterName}!");
    }
    else
    {
        require $converterFile;
    }
}

$endTime = microtime(true) - $startTime;
echo "[DONE] {$endTime} seconds execution time.";