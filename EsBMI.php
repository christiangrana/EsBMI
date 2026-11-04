<?php
$resultData = null;
$errorMsg = '';
$errorNome = '';
$errorPeso = '';
$errorAltezza = '';

$valNome = '';
$valPeso = '';
$valAltezza = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip = $_SERVER['REMOTE_ADDR'];
    $data = date('d/m/Y');
    $ora = date('H:i:s');

    $nome = trim($_POST['nome'] ?? '');
    $peso_str = trim($_POST['peso'] ?? '');
    $altezza_str = trim($_POST['altezza'] ?? '');

    $valNome = $_POST['nome'] ?? '';
    $valPeso = $_POST['peso'] ?? '';
    $valAltezza = $_POST['altezza'] ?? '';

    $valido = true;

    if ($nome === '') {
        $errorNome = "Valore non valido.";
        $valido = false;
    }

    if (!is_numeric($peso_str)) {
        $errorPeso = "Valore non valido.";
        $valido = false;
    }

    if (!is_numeric($altezza_str)) {
        $errorAltezza = "Valore non valido.";
        $valido = false;
    }

    if ($valido) {
        $peso = (float)$peso_str;
        $altezzaCm = (float)$altezza_str;

        if ($peso > 0 && $altezzaCm > 0) {
            $altezzaM = $altezzaCm / 100;
            $bmi = round($peso / ($altezzaM * $altezzaM), 1);

            // Determinazione categoria BMI
            if ($bmi < 18.5) {
                $categoria = 'Sottopeso';
                $classe = 'status-underweight';
            } elseif ($bmi >= 18.5 && $bmi < 25) {
                $categoria = 'Normopeso';
                $classe = 'status-normal';
            } elseif ($bmi >= 25 && $bmi < 30) {
                $categoria = 'Sovrappeso';
                $classe = 'status-overweight';
            } else {
                $categoria = 'Obesità';
                $classe = 'status-obese';
            }

            // Salvataggio nel file log.csv
            $rigaLog = "$data,$ora,$ip,\"$nome\",$altezzaCm,$peso,$bmi,$categoria\n";
            file_put_contents('log.csv', $rigaLog, FILE_APPEND);

            $resultData = [
                'nome' => $nome,
                'bmi' => $bmi,
                'categoria' => $categoria,
                'classe' => $classe
            ];
        } else {
            if ($peso <= 0) { $errorPeso = "Valore non valido."; }
            if ($altezzaCm <= 0) { $errorAltezza = "Valore non valido."; }

            $rigaLog = "$data,$ora,$ip,NOT_VALID,NOT_VALID,NOT_VALID,NOT_VALID,NOT_VALID\n";
            file_put_contents('log.csv', $rigaLog, FILE_APPEND);
            $errorMsg = "Dati non validi.";
        }
    } else {
        $rigaLog = "$data,$ora,$ip,NOT_VALID,NOT_VALID,NOT_VALID,NOT_VALID,NOT_VALID\n";
        file_put_contents('log.csv', $rigaLog, FILE_APPEND);
        $errorMsg = "Dati non validi.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcolatore BMI</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f1f3f5;
            color: #1f2328;
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 0 60px;
        }

        .card-container {
            width: 100%;
            max-width: 360px;
            background-color: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2328;
            margin-bottom: 24px;
            text-align: center;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1f2328;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            font-size: 15px;
            border: 1px solid #d0d7de;
            border-radius: 6px;
            background-color: #f6f8fa;
            color: #1f2328;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #1f2328;
            box-shadow: 0 0 0 3px rgba(31, 35, 40, 0.15);
            background-color: #ffffff;
        }

        .field-error {
            color: #cf222e;
            font-size: 12px;
            font-weight: 600;
            margin-top: 4px;
        }

        .btn-submit {
            width: 100%;
            background-color: #1f2328;
            color: #f1f3f5;
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
            margin-top: 6px;
        }

        .btn-submit:hover {
            background-color: #32383f;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .result-container {
            margin-top: 20px;
            padding: 16px;
            border-radius: 8px;
            text-align: center;
        }

        .result-title {
            font-size: 14px;
            color: #656d76;
            margin-bottom: 4px;
        }

        .result-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .result-status {
            font-size: 15px;
            font-weight: 600;
        }

        .error-message {
            margin-top: 15px;
            padding: 10px;
            background-color: #ffebe9;
            color: #cf222e;
            font-size: 14px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
        }

        .status-underweight { background-color: #ddf4ff; color: #0969da; }
        .status-normal { background-color: #dafbe1; color: #1a7f37; }
        .status-overweight { background-color: #fff8c5; color: #9a6700; }
        .status-obese { background-color: #ffebe9; color: #cf222e; }
    </style>
</head>
<body>

    <main class="card-container">
        <h1 class="title">Calcolatore BMI</h1>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" placeholder="Es. Mario Rossi" value="<?php echo htmlspecialchars($valNome); ?>">
                <?php if (!empty($errorNome)): ?>
                    <div class="field-error"><?php echo $errorNome; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="peso">Peso (kg)</label>
                <input type="number" id="peso" name="peso" step="0.1" min="1" max="300" placeholder="Es. 70" value="<?php echo htmlspecialchars($valPeso); ?>">
                <?php if (!empty($errorPeso)): ?>
                    <div class="field-error"><?php echo $errorPeso; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="altezza">Altezza (cm)</label>
                <input type="number" id="altezza" name="altezza" step="1" min="30" max="250" placeholder="Es. 175" value="<?php echo htmlspecialchars($valAltezza); ?>">
                <?php if (!empty($errorAltezza)): ?>
                    <div class="field-error"><?php echo $errorAltezza; ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Calcola BMI</button>
        </form>

        <?php if ($resultData): ?>
            <div class="result-container <?php echo $resultData['classe']; ?>">
                <div class="result-title">Risultato per <?php echo htmlspecialchars($resultData['nome']); ?>:</div>
                <div class="result-value"><?php echo $resultData['bmi']; ?> BMI</div>
                <div class="result-status"><?php echo $resultData['categoria']; ?></div>
            </div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="error-message">
                <?php echo $errorMsg; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>