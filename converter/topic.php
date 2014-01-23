<?php

$wbbTopics = $wbbDb->query("SELECT DISTINCT wbbt.*, MAX(wbbtv.lastVisitTime) as lastVisitTime, LEAST(COUNT(wbbta.boardID), 1) as isGlobal
FROM wbb{$wbbMySQLConnection['wbbNum']}_1_thread wbbt
LEFT JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_thread_announcement wbbta USING(threadID)
LEFT JOIN wbb{$wbbMySQLConnection['wbbNum']}_1_thread_visit wbbtv USING(threadID)
GROUP BY wbbt.threadID;");

while($wbbTopic = $wbbTopics->fetch_assoc())
{
    $topicType    = POST_NORMAL;
    if($wbbTopic['isSticky'])
    {
        $topicType    = POST_STICKY;
    }
    elseif($wbbTopic['isAnnouncement'])
    {
        $topicType    = POST_ANNOUNCE;
    }
    elseif($wbbTopic['isGlobal'])
    {
        $topicType    = POST_GLOBAL;
    }

    if($wbbTopic['isClosed'])
    {
        $topicStatus    = ITEM_LOCKED;
    }
    elseif($wbbTopic['movedThreadID'])
    {
        $topicStatus    = ITEM_MOVED;
    }
    else
    {
        $topicStatus    = ITEM_UNLOCKED;
    }

    $phpBBTopic = array(
        'topic_id'                  => $wbbTopic['threadID'],
        'forum_id'                  => $wbbTopic['boardID'],
        'icon_id'                   => 0,
        'topic_attachment'          => (int) $wbbTopic['attachments'] > 0,
        'topic_approved'            => $wbbTopic['everEnabled'],
        'topic_reported'            => 0,
        'topic_title'               => $phpBBDb->real_escape_string($wbbTopic['topic']),
        'topic_poster'              => $wbbTopic['userID'],
        'topic_time'                => $wbbTopic['time'],
        'topic_time_limit'          => 0,
        'topic_views'               => $wbbTopic['views'],
        'topic_replies'             => $wbbTopic['replies'],
        'topic_replies_real'        => $wbbTopic['replies'],
        'topic_status'              => $topicStatus,
        'topic_type'                => $topicType,
        'topic_first_post_id'       => $wbbTopic['firstPostID'],
        'topic_first_poster_name'   => '',
        'topic_first_poster_colour' => '',
        'topic_last_post_id'        => 0,
        'topic_last_poster_id'      => $wbbTopic['lastPosterID'],
        'topic_last_poster_name'    => $phpBBDb->real_escape_string($wbbTopic['lastPoster']),
        'topic_last_poster_colour'  => 0,
        'topic_last_post_subject'   => 0,
        'topic_last_post_time'      => $wbbTopic['lastPostTime'],
        'topic_last_view_time'      => !empty($wbbTopic['lastVisitTime']) ? $wbbTopic['lastVisitTime'] : time(),
        'topic_moved_id'            => $wbbTopic['movedThreadID'],
    );

    insertData("topics", $phpBBTopic);

    output('row');
}

$wbbTopics->close();
output('end');