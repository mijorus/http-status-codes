<?php

ob_start();

$c = curl_init('https://www.iana.org/assignments/http-status-codes/http-status-codes-1.csv');
curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
$csv_data = curl_exec($c);

curl_close($c);

$headers = [];
$data = [];
foreach (explode("\n", $csv_data) as $i => $row) {
    $cols = explode(',', $row);

    if ($i === 0) {
        $headers = $cols;
        continue;
    }

    $data[] = $cols;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/chota@latest">
    <title>Document</title>
</head>

<body>
    <?php foreach ($data as $http_code) : ?>
        <div>
            <?php echo $http_code[0] ?>
        </div>
    <?php endforeach ?>
</body>

</html>

<?php

file_put_contents(__DIR__ . '/index.html', ob_get_contents());
ob_clean();
?>