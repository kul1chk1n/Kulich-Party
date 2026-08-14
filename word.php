<?php

require __DIR__ . '/config.php';

session_start();

/* Проверяем авторизацию */
if (empty($_SESSION['admin'])) {
    http_response_code(403);
    exit('Доступ запрещён');
}


/* Файл с ответами */
$file = __DIR__ . '/data/responses.json';

if (file_exists($file)) {

    $json = file_get_contents($file);
    $rows = json_decode($json, true);

    if (!is_array($rows)) {
        $rows = array();
    }

} else {

    $rows = array();

}


/* Сортировка гостей по алфавиту */
usort($rows, function ($a, $b) {

    $nameA = isset($a['name']) ? $a['name'] : '';
    $nameB = isset($b['name']) ? $b['name'] : '';

    $nameA = mb_strtolower($nameA, 'UTF-8');
    $nameB = mb_strtolower($nameB, 'UTF-8');

    return strcmp($nameA, $nameB);

});


/* Название файла */
$fn = 'Гости_Кулич_20_' . date('d-m-Y') . '.doc';


/* Заголовки для Word */
header('Content-Type: application/msword; charset=utf-8');

header(
    'Content-Disposition: attachment; filename="' . $fn . '"'
);


/* Начинаем документ */
echo '<!DOCTYPE html>';

echo '<html>';

echo '<head>';

echo '<meta charset="utf-8">';

echo '<style>

body {
    font-family: Arial, sans-serif;
    font-size: 12pt;
}

h1 {
    font-size: 20pt;
}

.info {
    margin-bottom: 20px;
}

table {
    border-collapse: collapse;
    width: 100%;
}

th {
    background: #eeeeee;
    font-weight: bold;
}

th,
td {
    border: 1px solid #000000;
    padding: 6px;
    vertical-align: top;
}

</style>';

echo '</head>';

echo '<body>';


/* Заголовок */
echo '<h1>Гости — День рождения Кулича</h1>';

echo '<div class="info">';

echo '<p><strong>Дата:</strong> 25 августа 2026</p>';

echo '<p><strong>Город:</strong> Якутск</p>';

echo '<p><strong>Начало:</strong> 16:00</p>';

echo '<p><strong>Количество анкет:</strong> ' . count($rows) . '</p>';

echo '</div>';


/* Таблица */
echo '<table>';

echo '<tr>';

echo '<th>№</th>';
echo '<th>ФИО</th>';
echo '<th>Участие</th>';
echo '<th>+1</th>';
echo '<th>Ночёвка</th>';
echo '<th>Напитки</th>';
echo '<th>Комментарий</th>';

echo '</tr>';


/* Гости */
foreach ($rows as $i => $r) {

    $name = isset($r['name'])
        ? $r['name']
        : '';

    $attendance = isset($r['attendance'])
        ? $r['attendance']
        : '';

    $plusOne = isset($r['plus_one'])
        ? $r['plus_one']
        : 'Нет';

    $plusOneName = isset($r['plus_one_name'])
        ? $r['plus_one_name']
        : '';

    $sleep = isset($r['sleep'])
        ? $r['sleep']
        : '';

    $comment = isset($r['comment'])
        ? $r['comment']
        : '';


    /* +1 */
    $plusOneText = $plusOne;

    if ($plusOne === 'Да' && $plusOneName !== '') {

        $plusOneText .= ' — ' . $plusOneName;

    }


    /* Напитки */
    $drinks = '';

    if (
        isset($r['drinks']) &&
        is_array($r['drinks'])
    ) {

        $drinks = implode(', ', $r['drinks']);

    }


    echo '<tr>';


    echo '<td>';
    echo ($i + 1);
    echo '</td>';


    echo '<td>';
    echo htmlspecialchars(
        $name,
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</td>';


    echo '<td>';
    echo htmlspecialchars(
        $attendance,
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</td>';


    echo '<td>';
    echo htmlspecialchars(
        $plusOneText,
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</td>';


    echo '<td>';
    echo htmlspecialchars(
        $sleep,
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</td>';


    echo '<td>';
    echo htmlspecialchars(
        $drinks,
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</td>';


    echo '<td>';
    echo nl2br(
        htmlspecialchars(
            $comment,
            ENT_QUOTES,
            'UTF-8'
        )
    );
    echo '</td>';


    echo '</tr>';

}


echo '</table>';


echo '</body>';

echo '</html>';

exit;
?>