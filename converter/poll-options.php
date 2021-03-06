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

$phpBBPollIdSql->close();

if(!empty($phpBBPollIds))
{
    $wbbPollOptions     = $wbbDb->query("SELECT wcfpo.*, wbbp.threadID
    FROM ".PREFIX_WCF."_poll_option wcfpo
    INNER JOIN ".PREFIX_WCF."_poll wcfp ON wcfpo.pollID = wcfp.pollID
    INNER JOIN ".PREFIX_WBB."_post wbbp ON wcfp.messageID = wbbp.postID
    WHERE wbbp.postID IN (".implode(',',$phpBBPollIds).");");

    while ($wbbPollOption = $wbbPollOptions->fetch_assoc())
    {
        $phpBBPollOptions = array(
            'poll_option_id'    => $wbbPollOption['pollOptionID'],
            'topic_id'          => $wbbPollOption['threadID'],
            'poll_option_text'  => $phpBBDb->real_escape_string($wbbPollOption['pollOption']),
            'poll_option_total' => $wbbPollOption['votes']
        );

        insertData(POLL_OPTIONS_TABLE, $phpBBPollOptions);

        output('row');
    }

    $wbbPollOptions->close();
}
output('end');