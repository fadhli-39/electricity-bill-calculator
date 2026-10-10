
<?php
// Initialise variables
$error = "";
$submitted = false;

$blocks = [
    "block1" => "",
    "block2" => "",
    "block3" => "",
    "block4" => "",
    "block5" => ""
];

$cost1 = 0;
$cost2 = 0;
$cost3 = 0;
$cost4 = 0;
$cost5 = 0;
$totalKwh = 0;
$totalBill = 0;
$sst = 0;
$finalBill = 0;

// Process form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $submitted = true;

    foreach ($blocks as $key => $value) {
        $blocks[$key] = trim($_POST[$key] ?? "");
    }

    // Validate all inputs
    foreach ($blocks as $key => $value) {

        if ($value === "" || !is_numeric($value)) {
            $error = "Warning: All fields must contain valid numbers.";
            break;
        }

        if ((float)$value < 0 ||
            floor((float)$value) != (float)$value) {
            $error = "Warning: Enter non-negative whole numbers only.";
            break;
        }
    }

    // Validate block limits
    if ($error === "") {

        if ((float)$blocks["block1"] > 200 ||
            (float)$blocks["block2"] > 100 ||
            (float)$blocks["block3"] > 300 ||
            (float)$blocks["block4"] > 300) {

            $error = "Warning: Consumption exceeds the limit for its block.";
        }
    }

    // Calculate only if input is valid
    if ($error === "") {

        $b1 = (int)$blocks["block1"];
        $b2 = (int)$blocks["block2"];
        $b3 = (int)$blocks["block3"];
        $b4 = (int)$blocks["block4"];
        $b5 = (int)$blocks["block5"];

        // Calculate tariff for each block
        $cost1 = $b1 * 0.218;
        $cost2 = $b2 * 0.344;
        $cost3 = $b3 * 0.516;
        $cost4 = $b4 * 0.546;
        $cost5 = $b5 * 0.571;

        // Total electricity consumption
        $totalKwh = $b1 + $b2 + $b3 + $b4 + $b5;

        // Total tariff charges
        $totalBill = $cost1 + $cost2 + $cost3
                   + $cost4 + $cost5;

        // Minimum monthly charge
        if ($totalBill < 300) {
            $totalBill = 300;
        }

        // SST: use >600 kWh as stated in the first paragraph
        if ($totalKwh > 600) {
            $sst = round($totalBill * 0.06, 2);
        } else {
            $sst = 0;
        }

        // Final bill payable
        $finalBill = $totalBill + $sst;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>House Electricity Bill Calculator</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 30px 15px;
            color: #222;
        }

        .container {
            max-width: 750px;
            margin: auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }

        h1 {
            font-size: 30px;
        }

        label {
            display: inline-block;
            width: 220px;
            margin-bottom: 18px;
        }

        input {
            padding: 10px;
            width: 150px;
            border: 1px solid #aaa;
            border-radius: 4px;
        }

        button {
            background: #222;
            color: white;
            padding: 13px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #444;
        }

        .result {
            margin-top: 30px;
            padding: 20px;
            background: #eee;
            border-radius: 5px;
        }

        .error {
            margin-top: 20px;
            padding: 12px;
            color: #b00020;
            background: #ffe8e8;
            border-radius: 4px;
        }

        .final {
            font-size: 22px;
            font-weight: bold;
        }

        hr {
            border: 0;
            border-top: 1px solid #aaa;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>House Electricity Bill Calculator</h1>

    <p>Enter your electricity consumption below.</p>

    <form method="post" action="">

        <label>First 200 kWh:</label>
        <input type="number" name="block1"
            min="0" max="200" step="1" required
            value="<?php echo htmlspecialchars($blocks["block1"]); ?>">
        <br>

        <label>Next 100 kWh:</label>
        <input type="number" name="block2"
            min="0" max="100" step="1" required
            value="<?php echo htmlspecialchars($blocks["block2"]); ?>">
        <br>

        <label>Next 300 kWh:</label>
        <input type="number" name="block3"
            min="0" max="300" step="1" required
            value="<?php echo htmlspecialchars($blocks["block3"]); ?>">
        <br>

        <label>Next 300 kWh:</label>
        <input type="number" name="block4"
            min="0" max="300" step="1" required
            value="<?php echo htmlspecialchars($blocks["block4"]); ?>">
        <br>

        <label>901 kWh onwards:</label>
        <input type="number" name="block5"
            min="0" step="1" required
            value="<?php echo htmlspecialchars($blocks["block5"]); ?>">
        <br><br>

        <button type="submit" name="calculate">
            Calculate Bill
        </button>

    </form>

    <?php if ($error !== "") { ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <?php if ($submitted && $error === "") { ?>

        <div class="result">

            <h2>Electricity Bill Details</h2>

            <p>First block: RM
                <?php echo number_format($cost1, 2); ?>
            </p>

            <p>Second block: RM
                <?php echo number_format($cost2, 2); ?>
            </p>

            <p>Third block: RM
                <?php echo number_format($cost3, 2); ?>
            </p>

            <p>Fourth block: RM
                <?php echo number_format($cost4, 2); ?>
            </p>

            <p>Fifth block: RM
                <?php echo number_format($cost5, 2); ?>
            </p>

            <hr>

            <p>Total Consumption:
                <?php echo $totalKwh; ?> kWh
            </p>

            <p>Total Bill before SST: RM
                <?php echo number_format($totalBill, 2); ?>
            </p>

            <p>SST (6%): RM
                <?php echo number_format($sst, 2); ?>
            </p>

            <p class="final">
                Final Bill: RM
                <?php echo number_format($finalBill, 2); ?>
            </p>

        </div>

    <?php } ?>

</div>

</body>
</html>
