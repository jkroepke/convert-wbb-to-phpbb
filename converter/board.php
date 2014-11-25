<?php

$wbbBoards = $wbbDb->query("SELECT wbbb.*, wbbs.position,
wbbp.postID as lastPostID, wbbp.subject as lastPostSubject, wbbp.time as lastPostTime,
wbbu.userID as lastPosterID, wbbu.username as lastPosterName, wbbu.email as lastPosterMail
FROM wbb{$wbbMySQLConnection['wbbNum']}_1_board wbbb
INNER JOIN `wbb{$wbbMySQLConnection['wbbNum']}_1_board_structure` wbbs USING(boardID)
LEFT JOIN `wbb{$wbbMySQLConnection['wbbNum']}_1_board_last_post` wbblp USING(boardID)
LEFT JOIN `wbb{$wbbMySQLConnection['wbbNum']}_1_post` wbbp USING(threadID)
LEFT JOIN `wcf{$wbbMySQLConnection['wbbNum']}_user` wbbu USING(userID)
GROUP BY wbbb.boardID
ORDER BY wbblp.languageID ASC, wbbp.postID DESC;");

while($wbbBoard = $wbbBoards->fetch_assoc())
{
    #boardType

    $wbbBoardText   = convertBBCode($wbbBoard['description']);

    switch($wbbBoard['boardType'])
    {
        case 2:
            $boardType = FORUM_LINK;
            break;
        case 1:
            $boardType = FORUM_CAT;
            break;
        default:
            $boardType = FORUM_POST;
            break;
    }

    $phpBBBoard = array(
        'forum_id'                 => $wbbBoard['boardID'],
        'parent_id'                => $wbbBoard['parentID'],

        // left and right is to complex, so let they fix it using the stk
        'left_id'                  => $wbbBoard['position'],
        'right_id'                 => 0,

        'forum_parents'            => '',
        'forum_name'               => $phpBBDb->real_escape_string($wbbBoard['title']),
        'forum_desc'               => $phpBBDb->real_escape_string($wbbBoardText['text']),
        'forum_desc_bitfield'      => $wbbBoardText['bitfield'],
        'forum_desc_options'       => 7,
        'forum_desc_uid'           => $wbbBoardText['uid'],
        'forum_link'               => $phpBBDb->real_escape_string($wbbBoard['externalURL']),
        'forum_password'           => '',
        'forum_style'              => 0,
        'forum_image'              => '',
        'forum_rules'              => '',
        'forum_rules_link'         => '',
        'forum_rules_bitfield'     => '',
        'forum_rules_options'      => 7,
        'forum_rules_uid'          => '',
        'forum_topics_per_page'    => $wbbBoard['threadsPerPage'],
        'forum_type'               => $boardType,
        'forum_status'             => $wbbBoard['isClosed'] ? ITEM_LOCKED : ITEM_UNLOCKED,
        'forum_posts'              => $wbbBoard['posts'],
        'forum_topics'             => $wbbBoard['threads'],
        'forum_topics_real'        => $wbbBoard['threads'],
        'forum_last_post_id'       => $wbbBoard['lastPostID'],
        'forum_last_poster_id'     => $wbbBoard['lastPosterID'],
        'forum_last_post_subject'  => $phpBBDb->real_escape_string($wbbBoard['lastPostSubject']),
        'forum_last_post_time'     => $wbbBoard['lastPostTime'],
        'forum_last_poster_name'   => $phpBBDb->real_escape_string($wbbBoard['lastPosterName']),
        'forum_last_poster_colour' => $wbbBoard['lastPosterMail'] == $rootUser['user_email'] ? $rootUser['user_colour'] : '',
        'forum_flags'              => FORUM_FLAG_ACTIVE_TOPICS + FORUM_FLAG_QUICK_REPLY,
        'forum_options'            => 0,
        'display_subforum_list'    => $wbbBoard['showSubBoards'],
        'display_on_index'         => (int) $wbbBoard['isInvisible'] == 0,
        'enable_indexing'          => 1,
        'enable_icons'             => 1,
        'enable_prune'             => 0,
        'prune_next'               => 0,
        'prune_days'               => 0,
        'prune_viewed'             => 0,
        'prune_freq'               => 0,
    );

    insertData(FORUMS_TABLE, $phpBBBoard);

    foreach($phpBBDefaultBoardACLs[$boardType] as $acl)
    {
        $acl['forum_id'] = $wbbBoard['boardID'];
        insertData(ACL_GROUPS_TABLE, $acl);
    }

    output('row');
}

$wbbBoards->close();
output('end');