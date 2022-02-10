<?php

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Task</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav>
    <ul>
        <li><a href="/">Тестовое задание</a></li>
    </ul>
</nav>

<div class="container">
    <form action="upload.php" method="post" enctype="multipart/form-data">
        Отправить файл: <input name="uploadedFile" type="file" accept=".csv" />
        <input type="submit" name="import" value="Отправить файл">
    </form>
</div>
</body>
</html>
