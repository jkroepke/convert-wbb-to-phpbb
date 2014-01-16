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
    $PollIDs[] = $i['topic_first_post_id'];

$wbbPollVotes     = $wbbDb->query("SELECT wcfpov.*,wbbp.threadID
    FROM wcf{$wbbMySQLConnection['wbbNum']}_poll_option_vote wcfpov
    INNER JOIN wcf{$wbbMySQLConnection['wbbNum']}_poll wcfp USING pollID
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_post wbbp ON wcfp.messageID = wbbp.postID
    WHERE wbbp.threadID IN (".implode(',',$PollIDs).");");

while ($wbbPollOption = $wbbPollOptions->fetch_assoc())
{
    $phpBBPollOptions = array(
        'topic_id'          => $wbbPollOption['pollID'],
        'poll_option_id'    => $wbbPollOption['pollOptionID'],
        'vote_user_id'      => $wbbPollOption['userID'],
        'vote_user_ip'      => $wbbPollOption['ipAddress']
    );
    insertData('poll_votes', $phpBBPollOptions);
}