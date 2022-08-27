<?php

ob_start();
error_log('run');
$c = curl_init('https://www.iana.org/assignments/http-status-codes/http-status-codes-1.csv');
curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)");
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
$csv_data = curl_exec($c);

curl_close($c);

$headers = [];
$data = [];
foreach (explode("\n", $csv_data) as $i => $row) {
    $cols = explode(',', $row, 3);

    if ($i === 0) {
        $headers = $cols;
        continue;
    }

    if (!$cols[0]) break;
    $data[((int) $cols[0][0] . '00')][] = $cols;
}

$colors = [
    100 => '#35cf7a',
    200 => '#35cf7a',
    200 => '#35cf7a',
    300 => '#f39b00',
    400 => 'red',
    500 => 'black',
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/chota.min.css">
    <meta name="description" content="List of the http codes defined by the IANA; quickly search for the status code you need and explode some that you might not know">
    <title>HTTP status codes</title>
</head>

<body>
    <div class="container" style="padding: 10px;">
        <h1 class="is-marginless">List of all the HTTP Status codes</h1>
        <p style="font-size: 12px;"><a href="https://github.com/mijorus/http-status-codes">by mijorus on github</a></p>
        <div style="padding-top: 10px;">
            <input id="search" type="search" placeholder="Type to search ...">
            <p style="font-size: 12px;">Press <kbd>/</kbd> to focus</p>
        </div>
        <?php foreach ($data as $http_group) : ?>
            <div style="display: flex; flex-direction: row; align-items: center;">
                <div><?php echo $http_group[0][0] ?></div>
                <div class="remove-me" style="width: 90%; height: 2px; background: black; margin-left: 10px;"></div>
            </div>
            <div class="row" style="margin-top: 20px;">
                <?php foreach ($http_group as $http_code) : ?>
                    <div 
                    class="http-code-box col-12 col-6-md" 
                    style="margin-bottom: 10px;" 
                    data-code="<?php echo $http_code[0] ?>"
                    data-label="<?php echo $http_code[1] ?>"
                    >
                        <?php
                        $color = 'gray';
                        $is_group = str_contains($http_code[0], '-');
                        if (!str_contains($http_code[0], '-')) {
                            $color = $colors[((int) $http_code[0][0] . '00')];
                        }
                        ?>
                        <div class="col-12">
                            <div class="row" >
                                <div class="is-vertical-align" style="margin-right: 10px;">
                                    <div class="tag status-code" style="background-color: <?php echo $color; ?>">
                                        <?php echo $http_code[0] ?>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; ">
                                    <?php if ($is_group) : ?>
                                        <div style="color: black;" class="row">
                                            <?php echo $http_code[1] ?>
                                        </div>
                                    <?php else : ?>
                                        <a style="color: black; text-decoration: underline;" href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/<?php echo $http_code[0] ?>" class="row">
                                            <?php echo $http_code[1] ?>
                                        </a>
                                    <?php endif ?>
                                    <div class="row" style="font-size: 10px;">
                                        <?php echo str_replace('"', '', $http_code[2]) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endforeach ?>
        <footer>
            <hr>
            <div class="counterapi" style="min-height:44px" color="black" bg="white" label="vists"></div>
        </footer>
    </div>
</body>

<style>
    .status-code {
        border-radius: 8px;
        border: none;
        color: white;
        margin-right: 10px;
    }
</style>

<script src="https://counterapi.com/c.js?ns=httpstatus.mijorus.it" async ></script>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        const statusCodes = document.querySelectorAll('.status-code');
        const statusCodesRows = document.querySelectorAll('.http-code-box');
        const searchInput = document.getElementById('search');
        const removeMe = document.querySelectorAll('.remove-me');

        searchInput.focus();

        window.addEventListener('keydown', function(e) {
            if ((e.key === '/') &&
                (document.activeElement !== searchInput) &&
                (document.activeElement?.localName !== 'input') &&
                (document.activeElement?.localName !== 'textarea')
            ) {
                e.preventDefault();
                searchInput.focus();
            }
        });

        searchInput.addEventListener('keyup', function(e) {
            statusCodesRows.forEach(el => {
                console.log(el.dataset.code);
                el.classList.remove('is-hidden');
                // removeMe.forEach(el => el.classList.remove('is-hidden'))

                if ((searchInput.value.length && el.dataset.code.includes('-')) || 
                    (!el.dataset.code.includes(searchInput.value) && !el.dataset.label.toLowerCase().includes(searchInput.value.toLowerCase()))
                ) {
                    el.classList.add('is-hidden');
                }

                if (searchInput.value.trim().length) {
                    // removeMe.forEach(el => el.classList.add('is-hidden'))
                }
            })
        })
    })
</script>

</html>

<?php

file_put_contents(__DIR__ . '/index.html', ob_get_contents());
ob_clean();
?>