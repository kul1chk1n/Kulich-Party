<?php

require __DIR__ . '/config.php';

session_start();

/* Авторизация */
if (isset($_POST['password'])) {

    if (hash_equals(ADMIN_PASSWORD, $_POST['password'])) {
        $_SESSION['admin'] = 1;
    } else {
        $err = 'Неверный пароль';
    }
}

/* Выход */
if (isset($_GET['logout'])) {
    session_destroy();

    header('Location: admin.php');
    exit;
}

/* Страница входа */
if (empty($_SESSION['admin'])) {
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Гости — Кулич 20</title>

    <style>
        body {
            font: 18px Arial, sans-serif;
            max-width: 420px;
            margin: 15vh auto;
            padding: 20px;
            background: #fafafa;
            color: #111;
        }

        input,
        button {
            box-sizing: border-box;
            padding: 14px;
            width: 100%;
            margin: 8px 0;
            font-size: 16px;
        }

        button {
            background: #111;
            color: #fff;
            border: 0;
            cursor: pointer;
        }

        h1 {
            font-size: 30px;
        }

        .error {
            color: #b00020;
        }
    </style>
</head>

<body>

<h1>Список гостей</h1>

<?php if (!empty($err)): ?>
    <p class="error">
        <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?>
    </p>
<?php endif; ?>

<form method="post">

    <input
        type="password"
        name="password"
        placeholder="Пароль"
        required
    >

    <button type="submit">
        Войти
    </button>

</form>

</body>
</html>

<?php
exit;
}


/* Загружаем ответы */
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


/* Сортировка по алфавиту */
usort($rows, function ($a, $b) {

    $nameA = isset($a['name']) ? $a['name'] : '';
    $nameB = isset($b['name']) ? $b['name'] : '';

    $nameA = mb_strtolower($nameA, 'UTF-8');
    $nameB = mb_strtolower($nameB, 'UTF-8');

    return strcmp($nameA, $nameB);
});


/* Считаем гостей */
$totalResponses = count($rows);

$coming = 0;
$plusOnes = 0;
$sleeping = 0;

foreach ($rows as $r) {

    if (
        isset($r['attendance']) &&
        mb_stripos($r['attendance'], 'приду', 0, 'UTF-8') !== false
    ) {
        $coming++;
    }

    if (
        isset($r['plus_one']) &&
        $r['plus_one'] === 'Да'
    ) {
        $plusOnes++;
    }

    if (
        isset($r['sleep']) &&
        mb_stripos($r['sleep'], 'да', 0, 'UTF-8') !== false
    ) {
        $sleeping++;
    }
}

?>
<!doctype html>

<html lang="ru">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Гости — Кулич 20</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px;
            background: #fafafa;
            color: #111;
        }

        .container {
            max-width: 1400px;
            margin: auto;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 25px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat {
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
        }

        .stat-title {
            margin-top: 5px;
            color: #666;
        }

        .top {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .button {
            display: inline-block;
            padding: 13px 18px;
            background: #111;
            color: #fff;
            text-decoration: none;
            border: 0;
            cursor: pointer;
        }

        .table-wrapper {
            overflow-x: auto;
            background: #fff;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 900px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #111;
            color: #fff;
            white-space: nowrap;
        }

        tr:nth-child(even) {
            background: #f7f7f7;
        }

        .empty {
            padding: 30px;
            background: #fff;
            border: 1px solid #ddd;
        }

        @media (max-width: 700px) {

            body {
                padding: 15px;
            }

            h1 {
                font-size: 28px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h1>Гости — Кулич, 20</h1>

    <div class="top">

        <a class="button" href="word.php">
            Скачать Word
        </a>

        <a class="button" href="admin.php?logout=1">
            Выйти
        </a>

    </div>


    <div class="stats">

        <div class="stat">

            <div class="stat-number">
                <?php echo $totalResponses; ?>
            </div>

            <div class="stat-title">
                Всего ответов
            </div>

        </div>


        <div class="stat">

            <div class="stat-number">
                <?php echo $coming; ?>
            </div>

            <div class="stat-title">
                Подтвердили участие
            </div>

        </div>


        <div class="stat">

            <div class="stat-number">
                <?php echo $plusOnes; ?>
            </div>

            <div class="stat-title">
                Дополнительных гостей
            </div>

        </div>

    </div>


    <?php if (empty($rows)): ?>

        <div class="empty">
            Пока никто не заполнил анкету.
        </div>

    <?php else: ?>

        <div class="table-wrapper">

            <table>

                <tr>

                    <th>№</th>
                    <th>ФИО</th>
                    <th>Участие</th>
                    <th>+1</th>
                    <th>Ночёвка</th>
                    <th>Напитки</th>
                    <th>Комментарий</th>

                </tr>


                <?php foreach ($rows as $index => $r): ?>

                    <tr>

                        <td>
                            <?php echo $index + 1; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                isset($r['name']) ? $r['name'] : '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                isset($r['attendance']) ? $r['attendance'] : '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </td>


                        <td>

                            <?php

                            $plusOne = isset($r['plus_one'])
                                ? $r['plus_one']
                                : 'Нет';

                            echo htmlspecialchars(
                                $plusOne,
                                ENT_QUOTES,
                                'UTF-8'
                            );


                            if (
                                $plusOne === 'Да' &&
                                !empty($r['plus_one_name'])
                            ) {

                                echo '<br>';

                                echo htmlspecialchars(
                                    $r['plus_one_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                            }

                            ?>

                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                isset($r['sleep']) ? $r['sleep'] : '',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </td>


                        <td>

                            <?php

                            if (
                                isset($r['drinks']) &&
                                is_array($r['drinks'])
                            ) {

                                echo htmlspecialchars(
                                    implode(', ', $r['drinks']),
                                    ENT_QUOTES,
                                    'UTF-8'
                                );

                            } else {

                                echo '';

                            }

                            ?>

                        </td>


                        <td>

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    isset($r['comment'])
                                        ? $r['comment']
                                        : '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                )
                            );

                            ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        </div>

    <?php endif; ?>

</div>

</body>

</html>