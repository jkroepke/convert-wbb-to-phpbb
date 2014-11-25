<?php

$phpBBFoldersCount = array();

$mysqlFoldersCount = $phpBBDb->query("SELECT folder_id, COUNT(msg_id) as pm_count FROM {$phpBBMySQLConnection['prefix']}privmsgs_to WHERE folder_id > 0 GROUP BY folder_id;");

while($folder = $mysqlFoldersCount->fetch_assoc())
{
    $phpBBFoldersCount[$folder['folder_id']]    = $folder['pm_count'];
}

$mysqlFoldersCount->close();

$wbbPmFolders      = $wbbDb->query("SELECT * FROM ".PREFIX_WCF."_pm_folder;");

while($wbbPmFolder = $wbbPmFolders->fetch_assoc())
{
    $phpBBFolder = array(
        'folder_id'   => $wbbPmFolder['folderID'],
        'user_id'     => $wbbPmFolder['userID'],
        'folder_name' => $phpBBDb->real_escape_string($wbbPmFolder['folderName']),
        'pm_count'    => isset($phpBBFoldersCount[$wbbPmFolder['folderID']]) ? $phpBBFoldersCount[$wbbPmFolder['folderID']] : 0,
    );

    insertData(PRIVMSGS_FOLDER_TABLE, $phpBBFolder);

    output('row');
}

$wbbPmFolders->close();
output('end');