<?php

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
    exit;
}

/*
The auth plugin just works, if the wbb has default ENCRYPTION options.

define('ENCRYPTION_ENABLE_SALTING', 1);
define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', 1);
define('ENCRYPTION_METHOD', 'sha1');
define('ENCRYPTION_SALT_POSITION', 'before');
*/

require 'auth_db.php';

function login_wbb_db($username, $password, $ip = '', $browser = '', $forwarded_for = '')
{
    global $db;
    $username_clean = utf8_clean_string($username);
    $sql = 'SELECT wbbpw.* FROM ' . USERS_TABLE . "
        INNER JOIN " . USERS_WBB_PASSWORDS_TABLE . " wbbpw USING (user_id)
		WHERE username_clean = '" . $db->sql_escape($username_clean) . "'";


    $result = $db->sql_query($sql);
    $row = $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if(!empty($row) && sha1($row['salt'].sha1($row['salt'].sha1($password))) === $row['password'])
    {
        $sql = 'UPDATE ' . USERS_TABLE . "
        SET user_password = '".phpbb_hash($row['password'])."'
        WHERE user_id = " . (int) $row['user_id'] . ";";
        $db->sql_query($sql);

        $sql = 'DELETE FROM ' . USERS_WBB_PASSWORDS_TABLE . "
        WHERE user_id = " . (int) $row['user_id'] . ";";
        $db->sql_query($sql);
    }

    return login_db($username, $password, $ip, $browser, $forwarded_for);
}