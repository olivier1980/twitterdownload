<?php
require "vendor/autoload.php";
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

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

<html>
<head>
<!--    <script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>-->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
        }
        #flex-container {
            display: flex;
            width: 100%;
            flex-direction: row;
            flex-wrap: wrap;
        }
        #flex-container > .flex-item {
            flex: auto;
            max-width: 400px;
            border:1px solid #067acc;
            padding: 5px;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div id="flex-container" class="grid">
<?php
$stmt = $conn->prepare("SELECT liked.*, tu.name, tu.username FROM liked LEFT JOIN twitter_users tu ON tu.id = user_id WHERE `updated` IS NOT NULL ");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);



foreach ($rows as $row) {
    $pattern = "images/".$row['id'] ."*";

    echo "<div class='flex-item grid-item'>";

    echo '<strong><a href="https://twitter.com/'.$row["username"].'/status/'.$row['id'].'" target="_blank">'.$row['name'].'</a></strong><br/>';
    echo $row['content'].'<br/>';

    $files = glob($pattern);
    foreach ($files as $file) {
        if (str_ends_with($file,'jpg') || str_ends_with($file,'png') || str_ends_with($file,'gif')) {
            print '<img width=200 src="'.$file.'">';
        }
        if (str_ends_with($file,'mp4')) {
            print <<<DOC
            <video width="320" height="240" controls>
            <source src="$file" type="video/mp4">
            </video>
DOC;
        }
    }
    
    echo "</div>";
}

?>

</div>
<script>

    // var grid = document.querySelector('.grid');
    // var msnry = new Masonry( grid, {
    //     // options...
    //     itemSelector: '.grid-item',
    //     columnWidth: 200
    // });
</script>
</body>


</html>
