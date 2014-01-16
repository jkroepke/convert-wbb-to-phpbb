<?php

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
 * TODO: topics subscription
 * TODO: posts
 * polls
 * TODO: bbcodes
 *
 * Polls: Note that only polls placed in the first post of every topic will be converted.
 *
 */


define('IN_PHPBB', true);

//TODO: Add command line help


require 'config.php';
require 'functions.php';
require $phpBBPath.'includes/utf/utf_tools.php';
require $phpBBPath.'includes/functions.php';
require $phpBBPath.'includes/constants.php';


if(!class_exists('mysqli'))
{
    throw new Exception('Extension mysqli is required. Exiting.');
}

$wbbDb   = new mysqli($wbbMySQLConnection['host'], $wbbMySQLConnection['user'],
    $wbbMySQLConnection['password'], $wbbMySQLConnection['database']);

$phpBBDb = new mysqli($phpBBMySQLConnection['host'], $phpBBMySQLConnection['user'],
    $phpBBMySQLConnection['password'], $phpBBMySQLConnection['database']);

// get the phpbb config.
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
    'topic',
    'post',
    'topic-subscriptions',
    'poll',
    'poll-options',
    'poll-votes',
    'attachments'
);

foreach($convertProcess as $converterName)
{
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