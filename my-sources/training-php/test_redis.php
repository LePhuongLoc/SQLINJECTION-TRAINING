<?php
$redis = new Redis();
$redis->connect('redis-oss', 6379); // nếu dùng container redis tên là redis-oss
// Hoặc: $redis->connect('127.0.0.1', 6379); nếu truy cập qua localhost

$redis->set("demo", "Hello from PHP with Redis");
$value = $redis->get("demo");

echo "Giá trị trong Redis: " . $value;
