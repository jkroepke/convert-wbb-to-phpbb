<?php

/**
 *
 * WBB 3.1 to php 3.0.12 converter
 * convert features:
 * user
 * user group
 * user rank
 * user avatar
 * TODO: user friends
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

$wbbDb   = new mysqli($wbbMySQLConnection['host'], $wbbMySQLConnection['user'], $wbbMySQLConnection['password'], $wbbMySQLConnection['database']);
$phpBBDb = new mysqli($phpBBMySQLConnection['host'], $phpBBMySQLConnection['user'], $phpBBMySQLConnection['password'], $phpBBMySQLConnection['database']);

require 'functions.php';
require $phpBBPath.'includes/utf/utf_tools.php';
require $phpBBPath.'includes/functions.php';
require $phpBBPath.'includes/constants.php';

// Step 1 - Prepare phpbb Tables
echo "\n\nPrepare phpbb Tables...\n";

$rootUser        = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}_users WHERE user_type = 3;")->fetch_assoc();
// save all users
$defaultUsers    = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}_users WHERE user_type = 2;")->fetch_all();

// delete the admin and demo posts.
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}acl_users;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}topics_posted;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}topics;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}forums;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}posts;");

$phpBBDb->query("DELETE FROM {$phpBBMySQLConnection['prefix']}users WHERE user_id = 2;");
$phpBBDb->query("DELETE FROM {$phpBBMySQLConnection['prefix']}acl_groups WHERE forum_id != 0;");


// get the phpbb config.
$phpBBConfigResult = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}config;");
$phpBBConfig       = array();
while($configRow = $phpBBConfigResult->fetch_assoc())
{
    $phpBBConfig[$configRow['config_name']] = $configRow['config_value'];
}

$phpBBConfigResult->close();

// Step 2 - Import User
echo "Step 2 - Import User\n";
$wbbUserIpAddress = array();

// If the wbb has non dafault optionIDs, we can ask them here.
$wbbUserOptions = $wbbDb->query("SELECT optionID,optionName FROM wcf{$wbbMySQLConnection['wbbNum']}_user_option
    WHERE optionName IN
    ('birthday','aboutMe','enableDaylightSavingTime','timezone','location','homepage','icq','aim','jabber','msn','yim');");

$wbbUserOptionNames = array();
while($option = $wbbUserOptions->fetch_assoc())
{
    $wbbUserOptionNames[$option['optionName']] = 'userOption'.$option['optionID'];
}

$wbbUserOptions->close();

$wbbUsers = $wbbDb->query("SELECT wcfu.*, wbbu.boardLastMarkAllAsReadTime, ".implode(', ', $wbbUserOptionNames)."
    FROM wcf{$wbbMySQLConnection['wbbNum']}_users wcfu
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_users wbbu USING (userID)
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_user_option_value wbbu USING (userID);");

while($wbbUser = $wbbUsers->fetch_assoc())
{
    $userSignature                        = convertBBCode($wbbUser['signature']);
    $wbbUserIpAddress[$wbbUser['userID']] = $wbbUser['registrationIpAddress'];

    $birthday = '';

    // don't convert dates without year
    if(!empty($userSignature[$wbbUserOptionNames['birthday']]) && substr($userSignature[$wbbUserOptionNames['birthday']], 0, 4) !== '0000')
    {
        $birthday    = sprintf('%2d-%2d-%4d',
            substr($userSignature[$wbbUserOptionNames['birthday']], 8, 2), // day
            substr($userSignature[$wbbUserOptionNames['birthday']], 5, 2), // month
            substr($userSignature[$wbbUserOptionNames['birthday']], 0, 4)  // year
        );
    }
    
    $phpBBUser = array(
        'user_id'                  => $wbbUser['userID'],
        'user_type'                => USER_NORMAL,
        'group_id'                 => 2,
        'user_permissions'         => '',
        'user_perm_from'           => 0,
        'user_ip'                  => $wbbUser['registrationIpAddress'],
        'user_regdate'             => $wbbUser['registrationDate'],
        'username'                 => $phpbbDb->real_escape_string($wbbUser['username']),
        'username_clean'           => $phpbbDb->real_escape_string(utf8_clean_string($wbbUser['username'])),
        'user_password'            => phpbb_hash($wbbUser['password']),
        'user_passchg'             => time(),
        'user_pass_convert'        => 1,
        'user_email'               => $phpbbDb->real_escape_string($wbbUser['email']),
        'user_email_hash'          => phpbb_email_hash($wbbUser['email']),
        'user_birthday'            => $birthday,
        'user_lastvisit'           => $wbbUser['boardLastActivityTime'],
        'user_lastmark'            => $wbbUser['boardLastMarkAllAsReadTime'],
        'user_lastpost_time'       => 0,
        'user_lastpage'            => '',
        'user_last_confirm_key'    => '',
        'user_last_search'         => 0,
        'user_warnings'            => 0,
        'user_last_warning'        => 0,
        'user_login_attempts'      => 0,
        'user_inactive_reason'     => 0,
        'user_inactive_time'       => 0,
        'user_posts'               => 0,
        'user_lang'                => $phpBBConfig['default_lang'],
        'user_timezone'            => $phpBBConfig['board_timezone'],
        'user_dst'                 => $phpBBConfig['board_dst'],
        'user_dateformat'          => $phpBBConfig['default_dateformat'],
        'user_style'               => $phpBBConfig['default_style'],
        'user_rank'                => 0,
        'user_colour'              => '',
        'user_new_privmsg'         => 0,
        'user_unread_privmsg'      => 0,
        'user_last_privmsg'        => 0,
        'user_message_rules'       => 0,
        'user_full_folder'         => PRIVMSGS_NO_BOX,
        'user_emailtime'           => 0,
        'user_notify'              => 0,
        'user_notify_pm'           => 1,
        'user_notify_type'         => NOTIFY_EMAIL,
        'user_allow_pm'            => 1,
        'user_allow_viewonline'    => 1,
        'user_allow_viewemail'     => 1,
        'user_allow_massemail'     => 1,
        'user_options'             => 230271,
        'user_sig'                 => $phpbbDb->real_escape_string($userSignature['text']),
        'user_sig_bbcode_uid'      => $userSignature['uid'],
        'user_sig_bbcode_bitfield' => $userSignature['bitfield'],
        'user_from'                => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['location']]),
        'user_icq'                 => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['icq']]),
        'user_aim'                 => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['aim']]),
        'user_yim'                 => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['yim']]),
        'user_msnm'                => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['msn']]),
        'user_jabber'              => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['jabber']]),
        'user_website'             => $phpbbDb->real_escape_string($userSignature[$wbbUserOptionNames['homepage']]),
        'user_occ'                 => '',
        'user_interests'           => '',
        'user_form_salt'           => unique_id(),
        'user_new'                 => 0
    );

    if($wbbUser['email'] == $rootUser['user_email'])
    {
        // we found the old admin user. yey

        $phpBBUser['user_type']         = USER_FOUNDER;
        $phpBBUser['group_id']          = 3;
        $phpBBUser['user_rank']         = $rootUser['user_rank'];
        $phpBBUser['user_colour']       = $rootUser['user_colour'];
        $phpBBUser['user_pass_convert'] = 0;

        // let them login directly without request a new password.
        $phpBBUser['user_password']     = $rootUser['user_password'];

        // register the admin as an admin
        $phpBBAclUser = array(
            'user_id'        => $phpBBUser['user_id'],
            'forum_id'       => 0,
            'auth_option_id' => 0,
            'auth_role_id'   => 5,
            'auth_setting'   => 0
        );

        insertData("acl_users", $phpBBAclUser);

        // add the founder to global mod ...
        $phpBBUserToGroup = array(
            'group_id'     => 4,
            'user_id'      => $wbbUser['userID'],
            'group_leader' => 0,
            'user_pending' => 0
        );

        insertData("user_group", $phpBBUserToGroup);

        // ... and admin group.
        $phpBBUserToGroup = array(
            'group_id'     => 5,
            'user_id'      => $wbbUser['userID'],
            'group_leader' => 1,
            'user_pending' => 0
        );

        insertData("user_group", $phpBBUserToGroup);
    }

    insertData("user", $phpBBUser);

    // add user to user group
    $phpBBUserToGroup = array(
        'group_id'     => 5,
        'user_id'      => $wbbUser['userID'],
        'group_leader' => 0,
        'user_pending' => 0
    );

    insertData("user_group", $phpBBUserToGroup);
    echo '.';
}

$wbbUsers->close();

$phpBBDb->query("UPDATE {$phpBBMySQLConnection['prefix']}config SET config_value = '{$wbbUser['userID']}' WHERE config_name = 'newest_user_id';");
$phpBBDb->query("UPDATE {$phpBBMySQLConnection['prefix']}config SET config_value = '{$phpbbDb->real_escape_string($wbbUser['username'])}' WHERE config_name = 'newest_username';");
$phpBBDb->query("UPDATE {$phpBBMySQLConnection['prefix']}config SET config_value = (SELECT COUNT(*) FROM {$phpBBMySQLConnection['prefix']}user WHERE user_type IN (".USER_FOUNDER.",".USER_NORMAL.")) WHERE config_name = 'num_users';");
echo "\n\n";

// Step 3 - Import User Groups
echo "Import Groups\n";
// the first six user groups are wbb builtin groups. Just ignore them.

$phpBBLastGroupId = reset($wbbDb->query("SELECT MAX(group_id) FROM {$phpBBMySQLConnection['prefix']}groups;")->fetch_row());
$wbbUserGroups    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_group WHERE groupID > 6;");

while($wbbUserGroup = $wbbUserGroups->fetch_assoc())
{
    // on phpbb ranks and groups are splitted, just create a rank and group on phpbb

    $phpBBUserRank = array(
        'rank_title'   => $wbbUserGroup['groupName'],
        'rank_special' => 1,
    );

    insertData("ranks", $phpBBUserRank);

    $rankId    = $phpBBDb->insert_id;

    $groupText = convertBBCode($wbbUserGroup['groupDescription']);

    // wbb knows only group types like GROUP_CLOSED
    $phpBBUserGroup = array(
        'group_id'             => $wbbUserGroup['groupID'] + $phpBBLastGroupId,
        'group_type'           => GROUP_CLOSED,
        'group_founder_manage' => 0,
        'group_skip_auth'      => 0,
        'group_name'           => $wbbUserGroup['groupName'],
        'group_desc'           => $groupText['text'],
        'group_desc_bitfield'  => $groupText['bitfield'],
        'group_desc_options'   => 7,
        'group_desc_uid'       => $groupText['uid'],
        'group_display'        => $wbbUserGroup['showOnTeamPage'],
        'group_rank'           => $rankId,
    );

    insertData("user_group", $phpBBUserGroup);
}
$wbbUserGroups->close();

// Step 4 - Import User rank
$wbbUserToGroups    = $wbbDb->query("SELECT ug.*,leaderUserID FROM wcf{$wbbMySQLConnection['wbbNum']}_user_to_groups ug
    LEFT JOIN wcf{$wbbMySQLConnection['wbbNum']}_group_leader gl
        ON userID = leaderUserID AND ug.groupID = gl.groupID
    WHERE ug.groupID > 6;");

while($wbbUserToGroup = $wbbUserToGroups->fetch_assoc())
{
    $phpBBUserToGroup = array(
        'group_id'     => $wbbUserToGroup['groupID'],
        'user_id'      => $wbbUserToGroup['userID'],
        'group_leader' => $wbbUserToGroup['userID'] == $wbbUserToGroup['leaderUserID'],
        'user_pending' => 0
    );

    insertData("user_group", $phpBBUserToGroup);
}

$wbbUserToGroups->close();

// Step 5 - Import User rank
$wbbUserRanks = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_rank WHERE `rankTitle` NOT LIKE 'wcf.%';");

while($wbbUserRank = $wbbUserRanks->fetch_assoc())
{
    $phpBBUserRank = array(
        'rank_title'   => $wbbUserGroup['rankTitle'],
        'rank_special' => 0,
        'rank_min'     => $wbbUserGroup['neededPoints'] / 5
    );

    insertData("ranks", $phpBBUserRank);
}

$wbbUserRanks->close();

// Step 6 Import Pms
echo "Import PMs\n";

$wbbPmUser  = array();
$wbbPmUsers = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_pm_to_user;");

while($pmUser = $wbbPmUsers->fetch_assoc())
{
    $wbbPmUser[$pmUser['pmID']][]    = $pmUser;
}

$wbbPmUsers->close();


$wbbPms        = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_pm;");

while($wbbPm = $wbbPms->fetch_assoc())
{
    if($wbbPm['isDraft'])
    {
        // In phpbb, drafts have a own table.
        $phpDraft    = array(
            'user_id'       => $wbbPm['userID'],
            'topic_id'      => 0,
            'forum_id'      => 0,
            'save_time'     => $wbbPm['time'],
            'draft_subject' => $phpBBDb->real_escape_string($wbbPm['subject']),
            'draft_message' => $phpBBDb->real_escape_string($wbbPm['text'])
        );

        insertData("drafts", $phpDraft);
    }
    else
    {
        $toUser   = array();
        $bccUser  = array();

        $pmToUser = array(
            'isForwarded' => 0,
            'isReplied'   => 0
        );

        foreach($wbbPmUser[$wbbPm['pmID']] as $pmUser)
        {
            if($pmUser['isBlindCopy'] == 1)
            {
                $bccUser[] = 'u_'.$pmUser['recipientID'];
            }
            else
            {
                $toUser[]  = 'u_'.$pmUser['recipientID'];
            }

            $pmToUser    = array(
                'msg_id'       => $wbbPm['pmID'],
                'user_id'      => $pmUser['recipientID'],
                'author_id'    => $wbbPm['userID'],
                'pm_deleted'   => (int) $pmUser['isDeleted'] > 0,
                'pm_new'       => (int) $pmUser['userWasNotified'] != 0,
                'pm_unread'    => (int) $pmUser['isViewed'] != 0,
                'pm_replied'   => $pmUser['isReplied'],
                'pm_marked'    => 0,
                'pm_forwarded' => $pmUser['isForwarded'],
                'folder_id'    => $pmUser['folderID'] != 0 ? $pmUser['folderID'] : PRIVMSGS_INBOX
            );

            insertData("privmsgs_to", $pmToUser);
        }

        // phpBB add the sender to the "to" table, wbb not.

        $pmToUser    = array(
            'msg_id'       => $wbbPm['pmID'],
            'user_id'      => $wbbPm['userID'],
            'author_id'    => $wbbPm['userID'],
            'pm_deleted'   => (int) $wbbPm['saveInOutbox'] == 0,
            'pm_new'       => 0,
            'pm_unread'    => 0,
            'pm_replied'   => $pmUser['isReplied'],
            'pm_marked'    => 0,
            'pm_forwarded' => $pmUser['isForwarded'],
            'folder_id'    => PRIVMSGS_OUTBOX
        );

        insertData("privmsgs_to", $pmToUser);

        $pmText        = convertBBCode($wbbPm['text']);
        $phpBBPM    = array(
            'msg_id'              => $wbbPm['pmID'],
            'root_level'          => $wbbPm['parentPmID'],
            'author_id'           => $wbbPm['userID'],
            'icon_id'             => 0,
            'author_ip'           => isset($wbbUserIpAddress[$wbbPm['userID']]) ? $wbbUserIpAddress[$wbbPm['userID']] : '127.0.0.1',
            'message_time'        => $wbbPm['time'],
            'enable_bbcode'       => $wbbPm['enableBBCodes'],
            'enable_smilies'      => $wbbPm['enableSmilies'],
            'enable_magic_url'    => 1,
            'enable_sig'          => $wbbPm['showSignature'],
            'message_subject'     => $phpBBDb->real_escape_string($wbbPm['subject']),
            'message_text'        => $phpBBDb->real_escape_string($pmText['text']),
            'message_edit_reason' => '',
            'message_edit_user'   => 0,
            'message_attachment'  => 0,
            'bbcode_bitfield'     => $pmText['bitfield'],
            'bbcode_uid'          => $pmText['uid'],
            'message_edit_time'   => 0,
            'message_edit_count'  => 0,
            'to_address'          => implode(':', $toUser),
            'bcc_address'         => implode(':', $bccUser),
            'message_reported'    => 0,
        );

        insertData("privmsgs", $phpBBPM);
    }

    echo '.';
}

$wbbPms->close();

// Step 7 - Import Pm Folders

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

// Step 8 - Import Avatars

//TODO: Tabelle abändern
$wbbAvatars    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_avatar;");

while($wbbAvatar = $wbbAvatars->fetch_assoc())
{
    $phpBBAvatar = array(
        'user_id'   => $wbbAvatar['userID'],
        'height'    => $wbbAvatar['height'],
        'width'     => $wbbAvatar['width'],
        'filename'  => $phpBBConfig['avatar_salt']."_".$wbbAvatar['userID'].".".$wbbAvatar['avatarExtension']
    );

    $wbbAvatarPath = $wbbPath.'wcf/images/avatars/avatar-'.$wbbAvatar['avatarID'].'.'.$wbbAvatar['avatarExtension'];
    $phpBBAvatarPath = $phpBBPath.$phpBBConfig['avatar_path'].'/'.$phpBBAvatar['filename'];


    //TODO: Am anfang des Convert schecken, ob die Pfade less und beschreibar sind.
    //TODO: phpBB Pfade vielleicht leeren.
    if(!is_readable($wbbAvatarPath)) continue;
    if(!is_writeable(dirname($phpBBAvatarPath))) continue;

    if (copy($wbbAvatarPath, $phpBBAvatarPath))
    {
        //TODO: inserData Funktion nutzten
        $phpBBDb->query("UPDATE {$phpBBMySQLConnection['prefix']}users SET
            user_avatar_height = {$phpBBAvatar['height']},
            user_avatar_width = {$phpBBAvatar['width']},
            user_avatar = '".$phpBBAvatar['filename']."',
            user_avatar_type = 1
        WHERE
            user_id = {$phpBBAvatar['user_id']};");
    }
}

$wbbAvatars->close();


// Step 9 - Import Attachments

$wbbAttachments    = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_avatar;");

while($wbbAttachment = $wbbAttachments->fetch_assoc())
{
    $phpBBAttachment = array(
        'attachmentID'      => $wbbAttachment['attachmentID'],
        'post_msg_id'       => $wbbAttachment['containerID'],
        'in_message'        => ($wbbAttachment['containerType'] == 'pm' ? 1 : 0),
        'poster_id'         => $wbbAttachment['userID'],
        'physical_filename' => $wbbAttachment['userID']."_".md5(unique_id()),
        'real_filename'     => $wbbAttachment['attachmentName'],
        'download_count'    => $wbbAttachment['downloads'],
        'extension'         => substr(strrchr($wbbAttachment['attachmentName'],'.'),1),
        'mimetype'          => $wbbAttachment['fileType'],
        'filesize'          => $wbbAttachment['attachmentsSize'],
        'filetime'          => $wbbAttachment['uploadTime']
);
    //TODO: Am anfang des Convert schecken, ob die Pfade less und beschreibar sind.
    //TODO: phpBB Pfade vielleicht leeren.

    $wbbAttachmentPath = $wbbPath.'wcf/attachments/attachment-'.$wbbAttachment['attachmentID'];
    $phpBBAttachmentPath = $phpBBPath.$phpBBConfig['upload_path'].'/'.$phpBBAttachment['physical_filename'];

    if (copy($wbbAttachmentPath, $phpBBAttachmentPath))
    {
        //TODO: inserData Funktion nutzten
        $phpBBDb->query("INSERT INTO {$phpBBMySQLConnection['prefix']}attachments
            (attach_id, post_msg_id, in_message, poster_id, is_orphan, physical_filename, real_filename, download_count, extension, mimetype, filesize, filetime)
        VALUES
            ({$phpBBAttachment['attachmentID']}, {$phpBBAttachment['post_msg_id']}, {$phpBBAttachment['in_message']} {$phpBBAttachment['poster_id']}, 0, '{$phpBBAttachment['physical_filename']}', '{$phpBBAttachment['real_filename']}', {$phpBBAttachment['download_count']}, '{$phpBBAttachment['extension']}', '{$phpBBAttachment['mimetype']}', {$phpBBAttachment['filesize']}, {$phpBBAttachment['filetime']})
        ;");
    }

}

$wbbAttachments->close();