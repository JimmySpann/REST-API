<?php

function isAuthenicated($db, $token) {
    return $db->query('SELECT token FROM users WHERE token=:token', array(':token'=>sha1($token)));
}