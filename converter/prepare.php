<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 15.01.14
 * Time: 19:49
 */



$rootUser        = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}_users WHERE user_type = 3;")->fetch_assoc();
// save all users
$defaultUsers    = $phpBBDb->query("SELECT * FROM {$phpBBMySQLConnection['prefix']}_users WHERE user_type = 2;")->fetch_all();

// delete the admin and demo posts.
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}acl_users;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}topics_posted;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}topics;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}forums;");
$phpBBDb->query("TRUNCATE {$phpBBMySQLConnection['prefix']}posts;");

$phpBBDb->query("DELETE FROM {$phpBBMySQLConnection['prefix']}users WHERE user_id = 2;");
$phpBBDb->query("DELETE FROM {$phpBBMySQLConnection['prefix']}acl_groups WHERE forum_id != 0;");