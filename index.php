<?php

/**
 *
 * WBB 3.1 to php 3.0.12 converter
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
 * TODO: forums
 * TODO: topic
 * TODO: posts
 * TODO: polls
 * TODO: bbcodes
 *
 */
define('IN_PHPBB', true);

//TODO: Add command line help


if(!class_exists('mysqli'))
{
    echo 'Extension mysqli is required. Exiting.';
    exit(1);
}

$wbbMySQLConnection = array(
    'host'        => 'localhost',
    'user'        => 'creativesandbox',
    'password'    => 'creativesandbox',
    'database'    => 'creativesandbox_phpbb',
    'wbbNum'      => '25292',
);

$wbbPath = '';

$phpBBMySQLConnection = array(
    'host'        => 'localhost',
    'user'        => 'creativesandbox',
    'password'    => 'creativesandbox',
    'database'    => 'creativesandbox_phpbb',
    'prefix'      => 'phpbb_',
);

$phpBBPath = '';

$wbbDb   = new mysqli($wbbMySQLConnection['host'], $wbbMySQLConnection['user'],
    $wbbMySQLConnection['password'], $wbbMySQLConnection['database']);

$phpBBDb = new mysqli($phpBBMySQLConnection['host'], $phpBBMySQLConnection['user'],
    $phpBBMySQLConnection['password'], $phpBBMySQLConnection['database']);


require 'functions.php';
require $phpBBPath.'includes/utf/utf_tools.php';
require $phpBBPath.'includes/functions.php';
require $phpBBPath.'includes/constants.php';

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