<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 15.01.14
 * Time: 23:50
 */

$PollIdSql          = $phpBBDb->query("SELECT topic_first_post_id FROM {$phpBBMySQLConnection['prefix']}topics WHERE poll_start > 0;");
$PollIds            = array();
while ($i = $PollIdSql->fetch_assoc())
    $PollIds[] = $i['topic_first_post_id'];

if(!empty($PollIds))
{

    $wbbPollVotes     = $wbbDb->query("SELECT wcfpov.*, wbbp.threadID
    FROM wcf{$wbbMySQLConnection['wbbNum']}_poll_option_vote wcfpov
    INNER JOIN wcf{$wbbMySQLConnection['wbbNum']}_poll wcfp ON wcfpov.pollID = wcfp.pollID
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_post wbbp ON wcfp.messageID = wbbp.postID
    WHERE wbbp.threadID IN (".implode(',',$PollIDs).");");

    while ($wbbPollOption = $wbbPollVotes ->fetch_assoc())
    {
        $phpBBPollOptions = array(
            'topic_id'          => $wbbPollOption['threadID'],
            'poll_option_id'    => $wbbPollOption['pollOptionID'],
            'vote_user_id'      => $wbbPollOption['userID'],
            'vote_user_ip'      => $wbbPollOption['ipAddress']
        );

        insertData('poll_votes', $phpBBPollOptions);

        output('row');
    }

    $wbbPollVotes ->close();
}
output('end');