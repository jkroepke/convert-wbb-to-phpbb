<?php
/**
 * Created by PhpStorm.
 * User: Jan
 * Date: 14.01.14
 * Time: 02:33
 */


function convertBBCode($text)
{
    global $phpBBConfig;
    //TODO: Convert between WBB's and phpBB's bbcode syntax (i.e. [attach]<id>[/attach] -> [attachment=<id>][/attachment]

    $message_parser = new parse_message();
    $message_parser->message = str_replace('"', '&quot;', html_entity_decode($text));
    //function parse($allow_bbcode, $allow_magic_url, $allow_smilies, $allow_img_bbcode = true, $allow_flash_bbcode = true, $allow_quote_bbcode = true, $allow_url_bbcode = true, $update_this_message = true, $mode = 'post')
    $message_parser->parse($phpBBConfig['allow_bbcode'] ? true : false, $phpBBConfig['allow_post_links'] ? true : false, true, $phpBBConfig['allow_bbcode'], $phpBBConfig['allow_bbcode'], true, $phpBBConfig['allow_post_links']);

    return array(
        'text'         	=> $message_parser->message,
        'checksum'     	=> md5($message_parser->message),
        'bitfield'   	=> $message_parser->bbcode_bitfield,
        'uid'        	=> $message_parser->bbcode_uid,
    );
}

function insertData($table, $data)
{
	global $phpBBDb, $phpBBMySQLConnection;

	$sql = "INSERT INTO {$phpBBMySQLConnection['prefix']}{$table} SET ";

	foreach($data as $key => $value)
	{
		$sql	.= "´".$key."´ = '".$value."',";
	}

	$sql	= substr($sql, 0, -1).';';
	$phpBBDb->query($sql);
}

function updateData($table, $data, $where = '1=1')
{
	global $phpBBDb, $phpBBMySQLConnection;

	$sql = "UPDATE {$phpBBMySQLConnection['prefix']}{$table} SET ";

	foreach($data as $key => $value)
	{
		$sql	.= "´".$key."´ = '".$value."',";
	}

	$sql	= substr($sql, 0, -1).' ';
	$sql	.= "WHERE {$where};";

	$phpBBDb->query($sql);
}