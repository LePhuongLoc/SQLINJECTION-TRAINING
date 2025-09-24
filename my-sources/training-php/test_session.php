<?php
session_start();
$_SESSION['test'] = 'Hello Redis';
echo "Session saved in Redis!";
