<?php

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
            'draft_message' => $phpBBDb->real_escape_string($wbbPm['message'])
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

        $pmText = convertBBCode($wbbPm['message']);
        $phpBBPM = array(
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

    output('row');
}

$wbbPms->close();
output('end');