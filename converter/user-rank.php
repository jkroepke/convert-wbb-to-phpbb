<?php

$wbbUserRanks = $wbbDb->query("SELECT * FROM ".PREFIX_WCF."_user_rank WHERE `rankTitle` NOT LIKE 'wcf.%';");

while($wbbUserRank = $wbbUserRanks->fetch_assoc())
{
    $phpBBUserRank = array(
        'rank_title'   => $phpBBDb->real_escape_string($wbbUserRank['rankTitle']),
        'rank_special' => (int) $wbbUserRank['groupID'] > 6 || $wbbUserRank['neededPoints'] == 0,
        'rank_min'     => round($wbbUserRank['neededPoints'] / 5)
    );

    insertData(RANKS_TABLE, $phpBBUserRank);

    output('row');
}

$wbbUserRanks->close();
output('end');