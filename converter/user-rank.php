<?php

$wbbUserRanks = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_rank WHERE `rankTitle` NOT LIKE 'wcf.%';");

while($wbbUserRank = $wbbUserRanks->fetch_assoc())
{
    $phpBBUserRank = array(
        'rank_title'   => $wbbUserGroup['rankTitle'],
        'rank_special' => 0,
        'rank_min'     => $wbbUserGroup['neededPoints'] / 5
    );

    insertData("ranks", $phpBBUserRank);
}

$wbbUserRanks->close();