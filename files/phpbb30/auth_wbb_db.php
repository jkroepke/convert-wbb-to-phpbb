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
*/
define('ENCRYPTION_ENABLE_SALTING', 1);
define('ENCRYPTION_ENCRYPT_BEFORE_SALTING', 1);
define('ENCRYPTION_METHOD', 'sha1');
define('ENCRYPTION_SALT_POSITION', 'before');

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

    // sha1($row['salt'].sha1($row['salt'].sha1($password)))
    if(!empty($row) && getDoubleSaltedHash($password, $row['salt']) === $row['password'])
    {
        $sql = 'UPDATE ' . USERS_TABLE . "
        SET user_password = '".phpbb_hash($password)."'
        WHERE user_id = " . (int) $row['user_id'] . ";";
        $db->sql_query($sql);

        $sql = 'DELETE FROM ' . USERS_WBB_PASSWORDS_TABLE . "
        WHERE user_id = " . (int) $row['user_id'] . ";";
        $db->sql_query($sql);
    }

    return login_db($username, $password, $ip, $browser, $forwarded_for);
}



/**
 * Returns a salted hash of the given value.
 *
 * @param 	string 		$value
 * @param	string		$salt
 * @return 	string 		$hash
 */
function getSaltedHash($value, $salt) {
    if (!defined('ENCRYPTION_ENABLE_SALTING') || ENCRYPTION_ENABLE_SALTING) {
        $hash = '';
        // salt
        if (!defined('ENCRYPTION_SALT_POSITION') || ENCRYPTION_SALT_POSITION == 'before') {
            $hash .= $salt;
        }

        // value
        if (!defined('ENCRYPTION_ENCRYPT_BEFORE_SALTING') || ENCRYPTION_ENCRYPT_BEFORE_SALTING) {
            $hash .= encrypt($value);
        }
        else {
            $hash .= $value;
        }

        // salt
        if (defined('ENCRYPTION_SALT_POSITION') && ENCRYPTION_SALT_POSITION == 'after') {
            $hash .= $salt;
        }

        return encrypt($hash);
    }
    else {
        return encrypt($value);
    }
}

/**
 * Returns a double salted hash of the given value.
 *
 * @param 	string 		$value
 * @param	string		$salt
 * @return 	string 		$hash
 */
function getDoubleSaltedHash($value, $salt) {
    return encrypt($salt . getSaltedHash($value, $salt));
}

/**
 * encrypts the given value.
 *
 * @param 	string 		$value
 * @return 	string 		$hash
 */
function encrypt($value) {
    if (defined('ENCRYPTION_METHOD')) {
        switch (ENCRYPTION_METHOD) {
            case 'sha1': return sha1($value);
            case 'md5': return md5($value);
            case 'crc32': return crc32($value);
            case 'crypt': return crypt($value);
        }
    }
    return sha1($value);
}