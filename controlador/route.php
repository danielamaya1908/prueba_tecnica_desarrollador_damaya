<?php

$post = $_POST;
error_reporting(E_ALL);
ini_set('display_errors','On');

session_start();
require_once("requeridosJS.php");

include "../vista/" . $post['vista'] . ".php";
