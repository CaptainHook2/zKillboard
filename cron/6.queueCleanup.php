<?php

use cvweiss\redistools\RedisQueue;

include_once '../init.php';

if ($redis->llen("queueProcess") > 100) exit();
$queueCleanup = new RedisQueue('queueCleanup');

$minute = date('Hi');
while ($minute == date('Hi')) {
    $killID = $queueCleanup->pop();
    if ($killID === null) {
        exit();
    }

    $killmail = $mdb->findDoc('rawmails', ['killID' => $killID]);

    if (!isset($killmail['killID_str'])) {
        continue;
    }

    $killmail = cleanup($killmail);
    $mdb->save('rawmails', $killmail);
}

function cleanup($array)
{
    $removable = ['icon', 'href', 'name'];

    foreach ($array as $key => $value) {
        if (substr($key, -4) == '_str') {
            //Util::out("Unsetting _str $key");
            unset($array[$key]);
        } elseif (in_array($key, $removable, true)) {
            //Util::out("Unsetting removable $key");
            unset($array[$key]);
        } elseif (is_array($value)) {
            $array[$key] = cleanup($value);
        }
    }

    return $array;
}
