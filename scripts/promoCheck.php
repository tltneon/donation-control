<?php

define('NineteenEleven', TRUE);
require_once'../includes/config.php';
require_once ABSDIR . 'includes/SourceBansClass.php';
require_once ABSDIR . 'includes/LanguageClass.php';
require_once ABSDIR . 'includes/SteamClass.php';
require_once ABSDIR . 'includes/PromotionsClass.php';
try {
    $promos = new promotions;
} catch (Exception $ex) {
    print "Oops something went wrong, please try again later.";
}
$code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
$amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT);
$expire = filter_input(INPUT_POST, 'expire', FILTER_SANITIZE_NUMBER_INT);

//$code = 'test-promo';
//$amout = 5;
//$expire = date('U');

$promo = $promos->checkPromo($code);
if ($promo) {
//array(8) { ["id"]=> string(1) "2" ["type"]=> string(1) "1" ["amount"]=> string(1) "9" ["days"]=> string(1) "2"
//["number"]=> string(1) "2" ["code"]=> string(10) "promo-code" ["descript"]=> string(24) "This is a test promotion"
//["active"]=> string(1) "1" }
    printf("<div class='foundPromo'><span class='glyphicon glyphicon-ok'></span> found promo '%s'<br />", $promo['descript']);
    if ($promo['type'] == '1') {
        //precent off
        $amountOff = $amount / $promo['amount'];
        $amount = round($amount - $amountOff, 2);
        echo "<script>$('#ppAmount').val('$amount');</script>";
        echo "Your new payment will be $amount";
    } elseif ($promo['type'] == '2') {
        $extraDays = $promo['amount'] * 86400;
        $expire = $expire + $extraDays;
        printf("Your perks will expire on %s</div>", date($date_format['front_end'], $expire));
    }
    echo "<script>var ppCustom = $('#ppCustom').val();"
    . "$('#ppCustom').val(ppCustom + '|$code');"
    . "$('#promoCode').attr('readonly', true);</script>";
} else {
    echo "<div class='noPromo'><span class='glyphicon glyphicon-remove'></span>Not a vaild code.</div>";
}

