<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 22.01.14
 * Time: 21:50
 */

// Insert default users
foreach($defaultUsers as $phpBBUser)
{
    if($phpBBUser['user_type'] != USER_FOUNDER)
    {
        unset($phpBBUser['user_id']);
        insertData('users', $phpBBUser);
        output('row');
    }
}


// Add and register custom auth method
copy('files/auth_wbb_db.php', $phpBBPath.'includes/auth/');

$phpBBConfigUpdate  = array(
    'config_value'  => 'wbb_db',
);
updateData('config', $phpBBConfigUpdate, "config_name = 'auth_method'");

output('row');


// Set the anonymous user on phpbb constants
$phpBBAnonymous = $phpBBDb->query("SELECT user_id FROM {$phpBBMySQLConnection['prefix']}users WHERE group_id = 1;");
if(empty($phpBBAnonymous))
{
    throw new Exception('Can not find the anonymous user on phpbb tables!');
}

$phpBBAnonymousId = reset($phpBBAnonymous->fetch_row());
replaceInFile('includes/constants.php', "define('ANONYMOUS', ".ANONYMOUS.");", "define('ANONYMOUS', {$phpBBAnonymousId});");

output('end');