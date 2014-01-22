<?php
/**
 * Created by PhpStorm.
 * User: Fabio
 * Date: 15.01.14
 * Time: 23:50
 */

//TODO: bessere varibalen namen.

$PollIdSql          = $phpBBDb->query("SELECT topic_first_post_id FROM {$phpBBMySQLConnection['prefix']}topics WHERE poll_start > 0;");
$PollIds            = array();
while ($i = $PollIdSql->fetch_assoc())
    $PollIds[] = $i['topic_first_post_id'];

$PollIdSql->close();

if(!empty($PollIds))
{
    //TODO: result always empty.
    $wbbPollOptions     = $wbbDb->query("SELECT wcfpo.*, wbbp.threadID
    FROM wcf{$wbbMySQLConnection['wbbNum']}_poll_option wcfpo
    INNER JOIN wcf{$wbbMySQLConnection['wbbNum']}_poll wcfp ON wcfpo.pollID = wcfp.pollID
    INNER JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_post wbbp ON wcfp.messageID = wbbp.postID
    WHERE wbbp.threadID IN (".implode(',',$PollIDs).");");

    while ($wbbPollOption = $wbbPollOptions->fetch_assoc())
    {
        $phpBBPollOptions = array(
            'poll_option_id'    => $wbbPollOption['pollOptionID'],
            'topic_id'          => $wbbPollOption['threadID'],
            'poll_option_text'  => $phpBBDb->real_escape_string($wbbPollOption['pollOption']),
            'poll_option_total' => $wbbPollOption['votes']
        );

        insertData('poll_options', $phpBBPollOptions);

        output('row');
    }

    $wbbPollOptions->close();
}
output('end');