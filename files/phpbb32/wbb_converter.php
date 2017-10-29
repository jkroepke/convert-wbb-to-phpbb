<?php

namespace phpbb\auth\provider;

class wbb_converter extends \phpbb\auth\provider\db
{
	/**
	 * {@inheritdoc}
	 */
	public function login($username, $password)
	{
        $username_clean = utf8_clean_string($username);

        $sql = 'SELECT wbbpw.* FROM ' . USERS_TABLE . "
        INNER JOIN " . USERS_WBB_PASSWORDS_TABLE . " wbbpw USING (user_id)
		WHERE username_clean = '" . $this->db->sql_escape($username_clean) . "'";

        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        // sha1($row['salt'].sha1($row['salt'].sha1($password)))
        if(!empty($row) && $this->getDoubleSaltedHash($password, $row['salt']) === $row['password'])
        {
            // Hash passwords into legacy md5, because phpbb convert legacy passwords itself.

            $sql = 'UPDATE ' . USERS_TABLE . "
            SET user_password = '".md5($password)."'
            WHERE user_id = " . (int) $row['user_id'] . ";";
            $this->db->sql_query($sql);

            $sql = 'DELETE FROM ' . USERS_WBB_PASSWORDS_TABLE . "
            WHERE user_id = " . (int) $row['user_id'] . ";";
            $this->db->sql_query($sql);
        }


	    return parent::login($username, $password);
	}


    /**
     * Returns a salted hash of the given value.
     *
     * @param 	string 		$value
     * @param	string		$salt
     * @return 	string 		$hash
     */
    private function getSaltedHash($value, $salt) {
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
    private function getDoubleSaltedHash($value, $salt) {
        return encrypt($salt . getSaltedHash($value, $salt));
    }

    /**
     * encrypts the given value.
     *
     * @param 	string 		$value
     * @return 	string 		$hash
     */
    private function encrypt($value) {
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
}