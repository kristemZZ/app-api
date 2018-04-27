<?php
use ext\pdo;
use ext\redis_session;
use ext\crypt;
pdo::$host    = '127.0.0.1';
pdo::$port    = 3306;
pdo::$user    = 'root';
pdo::$pwd     = 'root';
pdo::$db_name = 'jiaapp';
pdo::$charset = 'utf8mb4';

redis_session::$lifetime = 7*24*60*60;

crypt::$ssl_conf = 'D:/wamp/PHP7.1.4.7/extras/ssl/openssl.cnf';

