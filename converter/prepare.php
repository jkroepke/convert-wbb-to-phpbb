<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 15.01.14
 * Time: 19:49
 */

$rootUser        = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}users WHERE user_type = 3;")->fetch_assoc();
// save all users
$phpBBDefaultUsers          = array();
$phpBBDefaultUsersDbResult  = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}users WHERE user_type = 2;");
while($user = $phpBBDefaultUsersDbResult->fetch_assoc())
{
    $phpBBDefaultUsers[]    = $user;
}
$phpBBDefaultUsersDbResult->close();

output('row');

// delete the admin and demo posts.
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}acl_users;");
output('row');
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}topics_posted;");
output('row');
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}topics;");
output('row');
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}forums;");
output('row');
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}posts;");
output('row');
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}users;");
output('row');

$phpBBDb->query("DELETE FROM {$phpBBMySQLConnection['prefix']}acl_groups WHERE forum_id != 0;");
output('row');
output('end');