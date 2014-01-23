<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 22.01.14
 * Time: 21:50
 */

// Insert default users
foreach($phpBBDefaultUsers as $phpBBUser)
{
    if($phpBBUser['user_type'] != USER_FOUNDER)
    {
        unset($phpBBUser['user_id']);
        insertData('users', $phpBBUser);
        output('row');
    }
}


// Add and register custom auth method
copy('files/auth_wbb_db.php', $phpBBPath.'includes/auth/auth_wbb_db.php');
replaceInFile('includes/auth/auth_wbb_db.php', "define('ENCRYPTION_ENABLE_SALTING', 1);", "define('ENCRYPTION_ENABLE_SALTING', ".$wbbConfig['encryption_enable_salting'].");");
replaceInFile('includes/auth/auth_wbb_db.php', "define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', 1);", "define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', ".$wbbConfig['encryption_encrypt_before_salting'].");");
replaceInFile('includes/auth/auth_wbb_db.php', "define('ENCRYPTION_METHOD', 'sha1');", "define('ENCRYPTION_METHOD', '".$wbbConfig['encryption_method']."');");
replaceInFile('includes/auth/auth_wbb_db.php', "define('ENCRYPTION_SALT_POSITION', 'before');", "define('ENCRYPTION_SALT_POSITION', '".$wbbConfig['encryption_salt_position']."');");

replaceInFile('includes/constants.php', "// Additional tables", "// Additional tables\n\ndefine('USERS_WBB_PASSWORDS_TABLE',	\$table_prefix . 'users_wbb_passwords');");

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

// Clear phpBB cache
array_map('unlink', glob($phpBBPath.'cache/*.php'));

output('end');