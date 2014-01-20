<?php

$wbbUserRanks = $wbbDb->query("SELECT * FROM wcf{$wbbMySQLConnection['wbbNum']}_user_rank WHERE `rankTitle` NOT LIKE 'wcf.%';");
if($wbbUserRanks->num_rows > 0)
{
    while($wbbUserRank = $wbbUserRanks->fetch_assoc())
    {
        $phpBBUserRank = array(
            'rank_title'   => $phpBBDb->real_escape_string($wbbUserRank['rankTitle']),
            'rank_special' => 0,
            'rank_min'     => $wbbUserRank['neededPoints'] / 5
        );

        insertData("ranks", $phpBBUserRank);
        echo '.';
    }
}
else
{
    echo '.';
}

$wbbUserRanks->close();