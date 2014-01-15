<?php

$phpBBFoldersCount = array();

$mysqlFoldersCount = $phpBBDb->query("SELECT folder_id, COUNT(msg_id) as pm_count FROM {$phpBBMySQLConnection['prefix']}privmsgs_to GROUP BY folder_id WHERE folder_id > 0;");
while($folder = $mysqlFoldersCount->fetch_assoc())
{
    $phpBBFoldersCount[$folder['folder_id']]    = $folder['pm_count'];
}

$mysqlFoldersCount->close();

$wbbPmFolders      = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_pm_folder;");

while($wbbPmFolder = $wbbPmFolders->fetch_assoc())
{
    $phpBBFolder = array(
        'folder_id'   => $wbbPmFolder['folderID'],
        'user_id'     => $phpBBDb->real_escape_string($wbbPmFolder['userID']),
        'folder_name' => $wbbPmFolder['folderName'],
        'pm_count'    => isset($phpBBFoldersCount[$wbbPmFolder['folderID']]) ? $phpBBFoldersCount[$wbbPmFolder['folderID']] : 0,
    );

    insertData("privmsgs_folder", $phpBBFolder);
    echo '.';
}

$wbbPmFolders->close();