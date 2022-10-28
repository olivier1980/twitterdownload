<?php
namespace App\Lib;

use JsonException;
use Noweh\TwitterApi\Client;
use PDO;

class Twitter
{

    public function __construct(
        private TweetController $client,
        private PDO $conn
    )
    {

    }


    function storeToDb($result)
    {
        foreach ($result->data as $row) {

            $statement = $this->conn->prepare('INSERT INTO liked SET id = :id, content = :content)');

            $statement->execute([
                'id' => $row->id,
                'content' => $row->text,
            ]);
        }
    }

    /**
     * Loop through all liked Tweets and store id, text to db
     * @return void
     * @throws JsonException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeLikedTweets()
    {
        $this->client->setEndpoint('users/222041939/liked_tweets');
        $result = $this->client->performRequest();
        $this->storeToDb($result);
        $nexttoken = $result->meta->next_token;

        while ($nexttoken != null) {
            $this->client->setNextToken($nexttoken);
            $result = $this->client->performRequest();
            $nexttoken = $result->meta->next_token;
            $this->storeToDb($result);
        }
    }

    public function enrichTweets()
    {
        $stmt = $this->conn->prepare("SELECT id FROM liked WHERE `updated` IS NULL");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print('Found '. $stmt->rowCount() . ' rows.'.PHP_EOL);
        foreach ($rows as $row) {
            $this->storeTweet($row['id']);
        }
    }


    /**
     * Fetch attachment, author, etc
     * @param int $id
     * @return void
     */
    public function storeTweet(int $id)
    {
        //$id = 494871728191856642;
        $this->client->setEndpoint('tweets/'.$id, true);

        $result = $this->client->performRequest();

        if (isset($result->includes->users)) {
            $user = $result->includes->users[0];
            $user_id = null;
            $rows = $this->conn->prepare("SELECT id FROM twitter_users WHERE twitter_id = :id");
            $rows->execute([
                'id' => $user->id
            ]);
            if ($rows->rowCount() == 0) {
                $statement = $this->conn->prepare('INSERT INTO twitter_users (`twitter_id`, `name`, `username`) VALUES (:id, :name, :username)');
                try {
                    $insert_result = $statement->execute([
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                    ]);
                    $user_id = $this->conn->lastInsertId();
                } catch (\Exception $e) {
                    $test = 1;
                }
            } else {
                $user_id = $rows->fetchColumn();
            }

            $statement = $this->conn->prepare('UPDATE liked SET user_id = :id, updated= NOW() WHERE id = :tweet_id');
            $statement->execute([
                'tweet_id' => $id,
                'id' => $user_id,
            ]);

        }

        if (isset($result->includes->media)) {
            $i = 1;
            foreach ($result->includes->media as $img) {
//                $type = match ($img->type) {
//                    'photo' => '.png',
//                    'animated_gif' => '.gif',
//                    'video' => '.mp4'
//                };

                if ($img->type == 'animated_gif') {
                    $url = $img->variants[0]->url;
                    $ext = 'mp4';
                } elseif ($img->type == 'video') {
                    $t=1;

                } else {
                    $url = $img->url;
                    $ext = pathinfo($img->url, PATHINFO_EXTENSION);
                }




                $filename = __DIR__ . '/../../images/'.$id.'_'.$i.'.'.$ext;

                file_put_contents($filename, file_get_contents($url));
                $i++;
            }
        }
    }


}