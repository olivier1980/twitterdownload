<?php

require "vendor/autoload.php";
use App\Lib\TweetController;
use App\Lib\Twitter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV["DB_HOST"];
$username = $_ENV["DB_USER"];
$password = $_ENV["DB_PASS"];
$dbName = $_ENV["DB_NAME"];
$conn = false;

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbName;charset=utf8mb4", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");

    echo "Connected successfully";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$settings = [
    'account_id' => $_ENV['ACCOUNT_ID'],
    'consumer_key' => $_ENV['CONSUMER_KEY'],
    'consumer_secret' => $_ENV['CONSUMER_SECRET'],
    'bearer_token' => $_ENV['BEARER_TOKEN'],
    'access_token' => $_ENV['ACCESS_TOKEN'],
    'access_token_secret' => $_ENV['ACCESS_TOKEN_SECRET']
];

$client = new TweetController($settings);
$twitter = new Twitter($client, $conn);

$twitter->storeLikedTweets();
$twitter->enrichTweets();


