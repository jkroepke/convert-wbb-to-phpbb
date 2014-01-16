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

$wbbPollOptions     = $wbbDb->query("SELECT wcfpo.*,wbbp.threadID
    FROM wcf{$wbbMySQLConnection['wbbNum']}_poll_option wcfpo
    INNER JOIN wcf{$wbbMySQLConnection['wbbNum']}_poll wcfp USING pollID
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_post wbbp ON wcfp.messageID = wbbp.postID
    WHERE wbbp.threadID IN (".implode(',',$PollIDs).");");

while ($wbbPollOption = $wbbPollOptions->fetch_assoc())
{
    $phpBBPollOptions = array(
        'poll_option_id'    => $wbbPollOption['pollOptionID'],
        'topic_id'          => $wbbPollOption['pollID'],
        'poll_option_text'  => $wbbPollOption['pollOption'],
        'poll_option_total' => $wbbPollOption['votes']
    );
    insertData('poll_options', $phpBBPollOptions);
}