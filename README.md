# Twitter Download

Command-line script to fetch all liked Tweets from a user id and store them in a database.

I used this to store the ~5000 tweets I liked over the years, with all attached images.

Exported SQL structure is available in `twitter.sql`, just create a MySQL/MariaDB database and import.

Run `php download.php` from a command prompt. This will do 2 actions:
- $twitter->storeLikedTweets();
- $twitter->enrichTweets();

First command stores all liked tweets. Second command loops rows in the database and fetches user info and download attached images, video etc to the `images` folder.

Once the database is filled you can comment the `$twitter->storeLikedTweets();` command, to prevent double work on multiple 'download asset' runs.

Might take multiple runs because of the Twitter rate limits.
