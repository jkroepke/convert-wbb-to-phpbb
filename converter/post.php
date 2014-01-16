<?php

$wbbPosts = $wbbDb->query("SELECT wbbp.*, wbbt.boardID FROM wcf{$wbbMySQLConnection['wbbNum']}_post wbbp
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_thread wbbt USING (threadID);");

while($wbbPost = $wbbPosts->fetch_assoc())
{
    $postText = convertBBCode($wbbPost['message']);

    $phpBBPost = array(
        'post_id'          => $wbbPost['postID'],
        'topic_id'         => $wbbPost['threadID'],
        'forum_id'         => $wbbPost['boardID'],
        'poster_id'        => $wbbPost['userID'],
        'icon_id'          => 0,
        'poster_ip'        => $wbbPost['ipAddress'],
        'post_time'        => $wbbPost['time'],
        'post_approved'    => (int) $wbbPost['isDisabled'] != 1,
        'post_reported'    => 0,
        'enable_bbcode'    => $wbbPost['enableBBCodes'],
        'enable_smilies'   => $wbbPost['enableSmilies'],
        'enable_magic_url' => 1,
        'enable_sig'       => $wbbPost['showSignature'],
        'post_username'    => $wbbPost['userID'] == 0 ? $phpbbDb->real_escape_string($wbbPost['username']) : '',
        'post_subject'     => $phpbbDb->real_escape_string($wbbPost['subject']),
        'post_text'        => $phpbbDb->real_escape_string($postText['text']),
        'post_checksum'    => $postText['checksum'],
        'post_attachment'  => (int) $wbbPost['attachments'] > 1,
        'bbcode_bitfield'  => $postText['bitfield'],
        'bbcode_uid'       => $postText['uid'],
        'post_postcount'   => 1,
        'post_edit_time'   => $wbbPost['lastEditTime'],
        'post_edit_reason' => $phpbbDb->real_escape_string($wbbPost['editReason']),
        'post_edit_user'   => $wbbPost['editorID'],
        'post_edit_count'  => $wbbPost['editCount'],
        'post_edit_locked' => $wbbPost['isClosed'],
    );

    insertData("posts", $phpBBPost);

    if($wbbPost['userID'] != 0)
    {
        $phpBBTopicPosted = array(
            'user_id'       => $wbbPost['userID'],
            'topic_id'      => $wbbPost['threadID'],

            // doesn't matter, what it does ...
            'topic_posted'  => 1,
        );

        insertData("topics_posted", $phpBBTopicPosted);
    }
}

$wbbPosts->close();