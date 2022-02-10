<?php


function setup_temp_file($fileName) {
    copy($fileName, "/var/www/html/uploads/" . md5_file($_FILES["uploadedFile"]["tmp_name"]) . ".csv");
    $headers = array("КОД", "НАЗВАНИЕ", "Error");
    $temp = fopen("/var/www/html/uploads/".md5_file($_FILES["uploadedFile"]["tmp_name"]) . ".csv", 'w');
    fputcsv($temp, $headers);
    return $temp;
}

function find_restricted_symbol($error) {
    $symbols = array(":", ";", "<", "=", ">", "?", "@", "[", "\\", "]", "^", "_", "`", "{", "|", "}", "~", ",", "!", '"',"#","$","%","&","'", "(", ")", "*","+", "/");
    foreach (str_split($error) as $sign) {
        $restricted_symbol = array_search($sign, $symbols);
        if ($restricted_symbol) {

            return "Недопустимый символ ". $symbols[$restricted_symbol]. " в поле Название";
        }
    }
    return "";
}

function validate_data($column) {
    for ($c=1; $c < 2; $c++) {
        $validated = preg_match("/^[а-яА-ЯЁёa-zA-Z0-9\s\-\.]+$/u", $column[$c]);
        if ($validated) {
            return "";
        }
        $msg = find_restricted_symbol($column[$c]);
    }
    return $msg;
}

function upload_file()
{
    $upload_dir = '/var/www/html/uploads/';
    $upload_file = $upload_dir . basename($_FILES['uploadedFile']['name']);
    move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $upload_file);
}

function upload_csv($conn)
{
    if (isset($_POST["import"])) {
        $file_name = $_FILES["uploadedFile"]["tmp_name"];

        if ($_FILES["uploadedFile"]["size"] > 0) {
            $file = fopen($file_name, "r");
            $temp = setup_temp_file($file_name);
            $first_line = true;
            while (($column = fgetcsv($file, 10000, ",")) !== FALSE) {
                if($first_line) {
                    $first_line = false;
                    continue;
                }
                $msg = validate_data($column);
                $column[] = $msg;
                fputcsv($temp, $column);
                if (strlen($msg) === 0) {
                    $sql_insert = "INSERT INTO catalogues (code, title) VALUES ('" . $column[0] . "', '" . $column[1] . "') ON DUPLICATE KEY UPDATE code='" . $column[0] . "', title='" . $column[1] ."'";
                    $result = mysqli_query($conn, $sql_insert);

                    if (!empty($result)) {
                        $error= "Файл загружен";
                    } else {
                        $error = "Что-то пошло не так";
                    }
                }
            }
            fclose($file);
            echo ' 
 <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Зазрузить файл</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">Тестовое задание</a></li>
        </ul>
    </nav>
<h1>'. $error . '</h1>
</body>
</html>
';
        }
        upload_file();
    }
    if(!isset($_GET['view'])) {
        $upload_dir = '/var/www/html/uploads/';
        $upload_file = $upload_dir . basename($_FILES['uploadedFile']['name']);
        echo '<META HTTP-EQUIV="Refresh" Content="0; URL='."upload.php?file=$upload_file".'">';
        exit;
    }
}



$host = "db";

$user = "fnc";

$pass = "fnc";

$db_name = "task";

$sql = "CREATE TABLE IF NOT EXISTS catalogues (
    code VARCHAR(255) UNIQUE,
    title VARCHAR(255) NOT NULL
)";

if(!isset($_GET["file"])) {
    $conn = new mysqli($host, $user, $pass, $db_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        if (!mysqli_query($conn, $sql)) {
            echo "Error creating table: " . mysqli_error($conn);
        }
        else {
            if (isset($_POST["import"])) {
                upload_csv($conn);
            }
        }
    }
}
else {
    $file_name = "/var/www/html/uploads/" .md5_file($_GET["file"]) . ".csv";
    if(file_exists($file_name)) {
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header(
            "Content-Disposition: attachment; filename=\""
            . $file_name . "\"");
        readfile ($file_name);
    }
}

?>

