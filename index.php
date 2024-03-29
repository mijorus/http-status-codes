<?php

ob_start();
error_log('Building...');
$csv_data = file_get_contents('http-status-codes-1.csv');

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
    100 => '#52b6e7',
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
    <link rel="shortcut icon" href="/icon.png">
    <meta name="description" content="List of the http codes defined by the IANA; quickly search for the status code you need and explode some that you might not know">
    <title>HTTP status codes</title>
</head>

<body>
    <style>
        <?php echo file_get_contents(__DIR__ . '/chota.min.css'); ?>
    </style>

    <style>
        .status-code {
            border-radius: 8px;
            border: none;
            color: white;
            margin-right: 10px;
        }
        
        .unassigned-code {
            background-color: gray !important;
        }

        .wikilink {
            color: white; 
            text-decoration: underline;
            transition: transform 0.1s linear;
        }
    </style>

    <div class="container" style="padding: 10px;">
        <h1 class="is-marginless">List of all the HTTP Status codes</h1>
        <p style="font-size: 12px;"><a href="https://github.com/mijorus/http-status-codes">by mijorus on github</a></p>
        <p>This is a searchable list of all the official HTTP status codes defined by the IANA</p>
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
                    <div class="http-code-box col-12 col-6-md" style="margin-bottom: 10px;" data-code="<?php echo $http_code[0] ?>" data-label="<?php echo $http_code[1] ?>">
                        <?php
                        $color = 'gray';
                        $is_group = strpos($http_code[0], '-') !== false;
                        $unassigned = in_array($http_code[1], ['Unassigned', '(Unused)']);

                        if (!$is_group) {
                            $color = $colors[((int) $http_code[0][0] . '00')];
                        }
                        ?>
                        <div class="col-12">
                            <div class="row">
                                <div class="is-vertical-align" style="margin-right: 10px;">
                                    <div class="tag status-code <?= $unassigned ? 'unassigned-code' : '' ?>" style="background-color: <?php echo $color; ?>">
                                    <?php if ($unassigned) : ?>
                                        <?= $http_code[0] ?>
                                        <?php else : ?>
                                            <a class="wikilink"
                                                href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#<?= $http_code[0] ?>">
                                                <?php echo $http_code[0] ?>
                                            </a>
                                        <?php endif ?>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; ">
                                    <div style="color: black; <?php echo ($unassigned ? 'font-style: italic;' : '')  ?>" class="row">
                                        <?php if ($http_code[0] === '418') : ?>
                                            I'm a teapot
                                        <?php else : ?>
                                            <?php echo $http_code[1] ?>
                                        <?php endif ?>
                                    </div>
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
            <div class="counterapi"></div>
        </footer>
    </div>
</body>

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
                try {
                    el.classList.remove('is-hidden');
    
                    let inRange = false;
                    if (el.dataset.code.includes('-') && /^\d+$/g.test(searchInput.value)) {
                        const searchNum = parseInt(searchInput.value);
                        const range = el.dataset.code.split('-').map(s => parseInt(s));
                        console.log(searchNum);
    
                        inRange =(searchNum > 100) && (searchNum >= range[0]) && (searchNum <= range[1]);
                    }
    
                    if ((searchInput.value.length && el.dataset.code.includes('-') && searchInput.value[0].match(/\d/)) ||
                        (!el.dataset.code.includes(searchInput.value) && !el.dataset.label.toLowerCase().includes(searchInput.value.toLowerCase()))
                    ) {
                        if ((searchInput.value.length && !inRange)) {
                            el.classList.add('is-hidden');
                        }
                    }
                } catch (e) {
                    console.error(e);
                }
            })
        });

        let xhr = new XMLHttpRequest();
        xhr.open("GET", "https://api.countapi.xyz/hit/httpstatus.mijorus.it/home");
        xhr.responseType = "json";
        xhr.onload = function () {
            const result = this.response.value;
            document.querySelector('.counterapi').textContent = 'Visits: ' + result.toLocaleString();
        }
            
        xhr.send();

        
    })
</script>

</html>

<?php
file_put_contents(__DIR__ . '/public/index.html', ob_get_contents());

error_log('Done');
ob_clean();
?>