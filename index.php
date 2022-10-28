<?php

require "vendor/autoload.php";
use App\Lib\TweetController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV["DB_HOST"];
$username = $_ENV["DB_USER"];
$password = $_ENV["DB_PASS"];
$dbName = $_ENV["DB_NAME"];

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


/**
 * Loop through all liked Tweets and store id, text to db
 * @return void
 * @throws JsonException
 * @throws \GuzzleHttp\Exception\GuzzleException
 */
function storeLikedTweets()
{
    global $conn, $settings;

    $client = new TweetController($settings);
    $client->setEndpoint('users/222041939/liked_tweets');
    $result = $client->performRequest();

    $nexttoken = $result->meta->next_token;

    while ($nexttoken != null) {
        $client->setNextToken($nexttoken);
        $result = $client->performRequest();
        $nexttoken = $result->meta->next_token;

        foreach ($result->data as $row) {

            $statement = $conn->prepare('INSERT INTO liked VALUES (:id, :content)');

            $statement->execute([
                'id' => $row->id,
                'content' => $row->text,
            ]);
        }
    }
}

/**
 * Fetch attachment, author, etc
 * @param int $id
 * @return void
 */
function storeTweet(int $id)
{
    global $conn, $settings;
    $client = new \Noweh\TwitterApi\Client($settings);
    $result = $client->tweet()->performRequest('GET', array( 'id' => $id));

    //$statement = $conn->prepare('INSERT INTO tweet VALUES (:id, :content) ON DUPLICATE KEY UPDATE content = :content');
    //
    //$encoding = mb_detect_encoding($result->data->text);
    //$content = $result->data->text;
    //$statement->execute([
    //    'id' => $result->data->id,
    //    'content' => $content,
    //]);



}


