<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 15.01.14
 * Time: 19:49
 */

$rootUser        = $phpBBDb->query("SELECT * FROM ".USERS_TABLE." WHERE user_type = 3;")->fetch_assoc();
// save all users
$phpBBDefaultUsers          = array();
$phpBBDefaultUsersDbResult  = $phpBBDb->query("SELECT * FROM ".USERS_TABLE." WHERE user_type = 2;");
while($user = $phpBBDefaultUsersDbResult->fetch_assoc())
{
    $phpBBDefaultUsers[]    = $user;
}
$phpBBDefaultUsersDbResult->close();
output('row');

// get default rights
$phpBBDefaultBoardACLs          = array();
$phpBBDefaultBoardACLsDbResult  = $phpBBDb->query("SELECT * FROM ".ACL_GROUPS_TABLE." WHERE forum_id IN (1,2);");
while($acl = $phpBBDefaultBoardACLsDbResult->fetch_assoc())
{
    switch($acl['forum_id'])
    {
        case 1:
            $phpBBDefaultBoardACLs[FORUM_CAT][]    = $acl;
            break;
        case 2:
            $phpBBDefaultBoardACLs[FORUM_POST][]    = $acl;
            break;
    }
}
$phpBBDefaultBoardACLsDbResult->close();
output('row');

$phpBBDefaultBoardACLs[FORUM_LINK] = $phpBBDefaultBoardACLs[FORUM_POST];
output('row');

// delete the admin and demo posts.
$phpBBDb->query("TRUNCATE ".ACL_USERS_TABLE.";");
output('row');
$phpBBDb->query("TRUNCATE ".TOPICS_POSTED_TABLE.";");
output('row');
$phpBBDb->query("TRUNCATE ".TOPICS_TABLE.";");
output('row');
$phpBBDb->query("TRUNCATE ".FORUMS_TABLE.";");
output('row');
$phpBBDb->query("TRUNCATE ".POSTS_TABLE.";");
output('row');
$phpBBDb->query("TRUNCATE ".USERS_TABLE.";");
output('row');

$phpBBDb->query("DELETE FROM ".ACL_GROUPS_TABLE." WHERE forum_id != 0;");
output('row');
output('end');