# twitterdownload

Command-line script to fetch all like tweets from a user id and store them to DB.

Exported SQL structure is available in `twitter.sql`, just create a database and import.

Run `php index.php` from a command prompt. Once the database is filled you can comment the `$twitter->storeLikedTweets();` command.

Might take multiple runs because of the Twitter rate limits.
