<!DOCTYPE html>
<html>
<head>
    <title>Calculadora PHP</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }

        .calculator {
            width: 300px;
            padding: 20px;
            border-radius: 20px;
            background-color: #e6e6e6;
            box-shadow: 8px 8px 20px rgba(0, 0, 0, 0.2),
                        -8px -8px 20px rgba(255, 255, 255, 0.5);
        }

        .calculator input[type="text"] {
            width: 95%;
            border-radius: 15px;
            margin-bottom: 20px;
            padding: 10px;
            font-size: 20px;
            border: none;
            background-color: #e6e6e6;
            box-shadow: inset 4px 4px 10px rgba(0, 0, 0, 0.1),
                        inset -4px -4px 10px rgba(255, 255, 255, 0.5);
        }

        .calculator .button-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }

        .calculator .button {
            margin: 5px;
            width: 100%;
            height: 50px;
            padding: 0;
            font-size: 20px;
            border-radius: 10px;
            border: none;
            background-color: #e6e6e6;
            box-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2),
                        -4px -4px 10px rgba(255, 255, 255, 0.5);
            cursor: pointer;
        }

        .calculator .button:hover {
            background-color: #d9d9d9;
        }

        .calculator .button:active {
            box-shadow: inset 4px 4px 10px rgba(0, 0, 0, 0.2),
                        inset -4px -4px 10px rgba(255, 255, 255, 0.5);
        }

        .calculator .button:focus {
            outline: none;
        }

        .calculator .result {
            margin-top: 20px;
            font-size: 24px;
            text-align: center;
        }

        .calculator .history button {
            margin: 0 auto;
            display: block;
            width: fit-content;
        }


        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .modal-close {
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            color: #888;
        }

        .modal-close:hover {
            color: #000;
        }

        .modal-body {
            overflow: auto;
        }

        .calculation {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calculation .expression {
            flex-grow: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .calculation .delete {
            cursor: pointer;
            font-weight: bold;
            color: #f44336;
        }

        .calculation .delete:hover {
            color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="calculator">
        <form action="" method="post">
            <?php
            session_start();

            $result = '';

            if (isset($_POST['calculate'])) {
                $expression = $_POST['expression'];

                if (preg_match('/^[-+()\/.*\s\d]+$/', $expression)) {
                    $result = evaluateExpression($expression);
                } else {
                    $result = "Expresión matemática inválida";
                }

                saveCalculationToHistory($expression, $result);
            }
            ?>
            <input type="text" name="expression" id="expression-input" value="<?php echo $result; ?>" placeholder="Expresión matemática" required pattern="[0-9()+\-*/.\s]*">
            <div class="button-grid">
                <input type="button" class="button" value="/" onclick="appendSymbol('/')">
                <input type="button" class="button" value="CE" onclick="clearEntry()">
                <input type="button" class="button" value="C" onclick="clearExpression()">
                <input type="button" class="button" value="DEL" onclick="deleteLastSymbol()">
                <input type="button" class="button" value="7" onclick="appendSymbol('7')">
                <input type="button" class="button" value="8" onclick="appendSymbol('8')">
                <input type="button" class="button" value="9" onclick="appendSymbol('9')">
                <input type="button" class="button" value="X" onclick="appendSymbol('*')">
                <input type="button" class="button" value="4" onclick="appendSymbol('4')">
                <input type="button" class="button" value="5" onclick="appendSymbol('5')">
                <input type="button" class="button" value="6" onclick="appendSymbol('6')">
                <input type="button" class="button" value="-" onclick="appendSymbol('-')">
                <input type="button" class="button" value="1" onclick="appendSymbol('1')">
                <input type="button" class="button" value="2" onclick="appendSymbol('2')">
                <input type="button" class="button" value="3" onclick="appendSymbol('3')">
                <input type="button" class="button" value="." onclick="appendSymbol('.')">
                <input type="button" class="button" value="+" onclick="appendSymbol('+')">
                <input type="button" class="button" value="0" onclick="appendSymbol('0')">
                <input type="button" class="button" value="(" onclick="appendSymbol('(')">
                <input type="button" class="button" value=")" onclick="appendSymbol(')')">
            </div>
            <input type="submit" class="button" name="calculate" value="=" />
        </form>

        <?php
            if (isset($_GET['index'])) {
                $index = $_GET['index'];
                deleteCalculationFromHistory($index);
            }

            if (isset($_SESSION['history']) && !empty($_SESSION['history'])) {
                echo "<div class='history'>";
                echo "<button onclick=\"openModal()\">Historial de cálculos</button>";
                echo "</div>";
            }
        ?>

        <div id="modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Historial de cálculos</h2>
                    <span class="modal-close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <?php
                    if (isset($_SESSION['history']) && !empty($_SESSION['history'])) {
                        foreach ($_SESSION['history'] as $index => $calculation) {
                            echo "<div class='calculation'>";
                            echo "<span class='expression'>" . htmlspecialchars($calculation['expression']) . "</span>";
                            echo "<span class='delete' onclick='deleteCalculation($index)'>&times;</span>";
                            echo "</div>";
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

        <script>
            // JavaScript aquí

            function appendSymbol(symbol) {
                var expressionInput = document.getElementById("expression-input");
                expressionInput.value += symbol;
            }

            function deleteLastSymbol() {
                var expressionInput = document.getElementById("expression-input");
                expressionInput.value = expressionInput.value.slice(0, -1);
            }

            function clearEntry() {
                var expressionInput = document.getElementById("expression-input");
                var lastCharIndex = expressionInput.value.length - 1;
                var lastChar = expressionInput.value[lastCharIndex];

                if (lastChar === ' ') {
                    lastCharIndex -= 1;
                    lastChar = expressionInput.value[lastCharIndex];
                }

                while (lastChar !== ' ' && lastCharIndex >= 0) {
                    lastCharIndex -= 1;
                    lastChar = expressionInput.value[lastCharIndex];
                }

                expressionInput.value = expressionInput.value.slice(0, lastCharIndex + 1);
            }

            function openModal() {
                document.getElementById("modal").style.display = "block";
            }

            function closeModal() {
                document.getElementById("modal").style.display = "none";
            }

            function deleteCalculation(index) {
                window.location.href = "?index=" + index;
            }
        </script>
    </div>
</body>
</html>

<?php
function evaluateExpression($expression) {
    $expression = str_replace(' ', '', $expression);

    try {
        // Utilizar bibliotecas o funciones específicas para evaluar expresiones matemáticas
        $result = eval("return $expression;");
        return $result;
    } catch (ParseError $error) {
        return "Error de sintaxis";
    } catch (Throwable $error) {
        return "Error en la expresión matemática";
    }
}

function saveCalculationToHistory($expression, $result) {
    if (!isset($_SESSION['history'])) {
        $_SESSION['history'] = array();
    }

    array_unshift($_SESSION['history'], array(
        'expression' => $expression,
        'result' => $result
    ));
}

function deleteCalculationFromHistory($index) {
    if (isset($_SESSION['history']) && array_key_exists($index, $_SESSION['history'])) {
        unset($_SESSION['history'][$index]);
        $_SESSION['history'] = array_values($_SESSION['history']);
    }
}
?>
