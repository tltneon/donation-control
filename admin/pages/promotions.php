<?php
if (!defined('adminPage')) {
    exit("Direct access not premitted.");
}

if (isset($_POST['promo_form'])) {

    $args = array(
        'type' => FILTER_SANITIZE_NUMBER_INT,
        'amount' => FILTER_SANITIZE_NUMBER_INT,
        'days' => FILTER_SANITIZE_NUMBER_INT,
        'number' => FILTER_SANITIZE_NUMBER_INT,
        'code' => FILTER_SANITIZE_STRING,
        'descript' => FILTER_SANITIZE_STRING
    );
    $data = filter_input_array(INPUT_POST, $args, true);
    $required = array('type', 'amount', 'code', 'descript');
    foreach ($data as $key => $val) {

        if (is_null($key) && !array_key_exists($key, $data)) {
            unset($data[$key]);
        } elseif (array_key_exists($key, $data)) {
            continue;
        } else {
            die("<div class='alert alert-danger' role='alert'>Please fill out the $key field</div>");
        }
    }
    try {
        $stmt = $sb->ddb->prepare("INSERT INTO `promotions` (`" . implode("`, `", array_keys($data)) . "`) VALUES (:" . implode(", :", array_keys($data)) . ")");
        foreach ($data as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
    } catch (Exception $ex) {
        $log->logError($ex->getMessage(), $ex->getFile(), $ex->getLine());
        echo "<div class='alert alert-danger' role='alert'>" . $ex->getMessage() . "</div>";
    }
}
?>
<div class='panel panel-default half-width'>
    <h3 class='panel-title'>Promotions</h3>
    <div class='panel-body'>

        <form action='show_donations.php?loc=promotions' method='POST' id='promo_form'>
            <div class='panel panel-default panel-small inline'>
                <h3 class='panel-title'>Type of promotion</h3>
                <div class='panel-body'>
                    <div class='input-group'>
                        <span class='input-group-addon'>

                            <input type='radio' name='type' value='1' required />
                        </span>
                        <input type='text' readonly value='% off' class='form-control' style='cursor:context-menu;'>
                    </div>

                    <div class='input-group'>
                        <span class='input-group-addon'>
                            <input type='radio' name='type' value='2' required />
                        </span>
                        <input type='text' readonly value='Extra Days' class='form-control' style='cursor:context-menu;'>
                    </div>
                </div>
            </div>

            <div class="input-group">
                <span class="input-group-addon">Amount or %</span>
                <input type="number" class="form-control" name='amount' required min="0" max="99999" placeholder='Amount of extra days or % off'>
            </div>

            <div class="input-group">
                <span class="input-group-addon">Number of days</span>
                <input type="number" class="form-control" name='days' min="0" max="99999" placeholder="Lenth in days to run promotion. Blank to run forever">

            </div>

            <div class="input-group">
                <span class="input-group-addon">Number of promotions</span>
                <input type="number" class="form-control" name='number' min="0" max="99999" placeholder='How many promotions to give before stopping. Blank to run forever'>
            </div>

            <div class="input-group">
                <span class="input-group-addon">Promo code</span>
                <input type="text" class="form-control" name='code' required placeholder='Code to be entered at checkout'>
            </div>
            <div class="input-group">
                <span class="input-group-addon">Description</span>
                <input type="text" class="form-control" name='descript' required maxlength="128" placeholder='Promotion description'>
            </div>
            <input type='hidden' name='promo_form' value='1'>
        </form>
        <input type='submit' class='btn btn-default' value='Create Promotion' form='promo_form' />
    </div>
</div>
