<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 15.01.14
 * Time: 23:50
 */

$phpBBPollIdSql          = $phpBBDb->query("SELECT topic_first_post_id FROM ".TOPICS_TABLE." WHERE poll_start > 0;");
$phpBBPollIds            = array();
while ($phpBBPollId = $phpBBPollIdSql->fetch_assoc())
{
    $phpBBPollIds[] = $phpBBPollId['topic_first_post_id'];
}

if(!empty($phpBBPollIds))
{

    $wbbPollVotes     = $wbbDb->query("SELECT wcfpov.*, wbbp.threadID
    FROM ".PREFIX_WCF."_poll_option_vote wcfpov
    INNER JOIN ".PREFIX_WCF."_poll wcfp ON wcfpov.pollID = wcfp.pollID
    INNER JOIN ".PREFIX_WBB."_post wbbp ON wcfp.messageID = wbbp.postID
    WHERE wbbp.postID IN (".implode(',',$phpBBPollIds).");");

    while ($wbbPollOption = $wbbPollVotes ->fetch_assoc())
    {
        $phpBBPollOptions = array(
            'topic_id'          => $wbbPollOption['threadID'],
            'poll_option_id'    => $wbbPollOption['pollOptionID'],
            'vote_user_id'      => $wbbPollOption['userID'],
            'vote_user_ip'      => $wbbPollOption['ipAddress']
        );

        insertData(POLL_VOTES_TABLE, $phpBBPollOptions);

        output('row');
    }

    $wbbPollVotes ->close();
}
output('end');