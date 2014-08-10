<?php
if (isset($_POST['steamid_user'])) {
    $steamid_user = filter_input(INPUT_POST, 'steamid_user', FILTER_SANITIZE_STRING);
    $tier = filter_input(INPUT_POST, 'tier', FILTER_SANITIZE_NUMBER_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);
} elseif (isset($_GET['steamid_user'])) {
    $steamid_user = filter_input(INPUT_GET, 'steamid_user', FILTER_SANITIZE_STRING);
    $tier = filter_input(INPUT_GET, 'tier', FILTER_SANITIZE_NUMBER_INT);
    $amount = filter_input(INPUT_GET, 'amount', FILTER_SANITIZE_NUMBER_INT);
} else {
    die();
}
//define('TIERED_DONOR', true);
define('NineteenEleven', TRUE);
require_once'includes/config.php';
require_once ABSDIR . 'includes/SourceBansClass.php';
require_once ABSDIR . 'includes/LanguageClass.php';
require_once ABSDIR . 'includes/SteamClass.php';
$language = new language;
try {
    $sb = new sb;
} catch (Exception $ex) {
    print "Oops something went wrong, please try again later.";
}
if (isset($_POST['langSelect'])) {
    $lang = $language->getLang($_POST['langSelect']);
} else {
    $lang = $language->getLang(DEFAULT_LANGUAGE);
}

$stmt = $sb->ddb->prepare("SELECT * FROM `cache` WHERE steamid=:steamid_user;");
$stmt->bindParam(1, $steamid_user, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() > 0) {

    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $userInfo['personaname'];
    $avatarfull = $userInfo['avatarfull'];
    $steamID64 = $userInfo['steamid64'];
    $steam_link = $userInfo['steam_link'];
    $steam_id = $userInfo['steamid'];
} else {
    //user not in cache
    try {
        $steam = new SteamIDConvert($steamid_user);
        $steam->SteamIDCheck()->fillCache();

        $username = $steam->playerSummaries->response->players[0]->personaname;
        $avatarfull = $steam->playerSummaries->response->players[0]->avatarfull;
        $steamID64 = $steam->steamId64;
        $steam_link = $steam->steam_link;
        $steam_id = $steam->steam_id;
    } catch (Exception $ex) {
        @$socket = fsockopen('steamcommunity.com', 80, $errno, $errstr, 30);
        if (!$socket) {
            die("<h3>" . $lang->steamdown[0]->msg1 .
                    "<br />" . $lang->steamdown[0]->msg2 .
                    "<br />" . $lang->steamdown[0]->msg3 .
                    "<br /><a href='javascript:history.go(-1);'>" . $lang->misc[0]->msg1 . "</a></h3>");
        } else {
            @fclose($socket);
            die("<h3>" . $lang->steamdown[0]->msg4 .
                    "<br />" . $lang->steamdown[0]->msg5 .
                    "<br />" . $lang->steamdown[0]->msg6 .
                    "<br /><a href='javascript:history.go(-1);'>" . $lang->misc[0]->msg1 . "</a></h3>");
        }
    }
}


///////////////////////
if (strpos($amount, "$") === 0) {
    $amount = substr($amount, 1);
}
$group = $sb->getGroupInfo($tier);
if ($amount < $group['minimum'] && $group['minimum'] != 0) {
    $amountSmall = true;
} else {
    $amountSmall = false;
}

$amount = round($amount);

$now = date('U');


$days_purchased = round(($amount * $group['multiplier']));

unset($stmt);
$stmt = $sb->ddb->prepare("SELECT * FROM donors WHERE steam_id =:steamid_user;");
$stmt->bindParam(1, $steamid_user, PDO::PARAM_STR);
$stmt->execute();

$n = "+" . $days_purchased . " days";

if ($stmt->rowCount() == 1) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row['activated'] == "2") {
        //expired donor
        $expire = strtotime($n, $now);
        $return_donor = false;
    } else {
        if ($tier != $row['tier']) {
            //current donor changing group
            $grpChange = true;

            $oldGroup = $sb->getGroupInfo($row['tier']);

            $cDaysLeft = round(($row['expiration_date'] - $now) / 86400, 2, PHP_ROUND_HALF_UP); //amount of days left until perks expire

            $change = round($cDaysLeft / $oldGroup['multiplier'], 2, PHP_ROUND_HALF_UP); //prorated dollar amount

            $extraDays = round($change * $group['multiplier'], 2, PHP_ROUND_HALF_UP); // how many days to be added to new level
            //echo "$cDaysLeft $change $extraDays ";

            $n = "+" . round($days_purchased + $extraDays, 0, PHP_ROUND_HALF_UP) . " days";
            $expire = round(($days_purchased + $extraDays) * 86400, 0, PHP_ROUND_HALF_UP) + $now;
        } else {
            //current donor extending perks

            $expire = strtotime($n, $row['expiration_date']);
        }

        $expiration_date = $row['expiration_date']; //when repeat donors perks expire
        $return_donor = true;
    }
} else {
    //new donor
    $expire = strtotime($n, $now);
    $return_donor = false;
}
unset($n);
?>
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <script type="text/javascript" src="js/jscolor/jscolor.js"></script>
        <script>
            function change() {
                document.getElementById("langSelect").submit();
            }
        </script>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <style type="text/css">
            textarea{
                cursor: pointer;
            }
            .main-content{
                width: 100%;

            }
            p, textarea{
                margin: 2.1em .8em;
            }
            body{
                background-color: black;
            }
            input[type=checkbox]{
                margin: 0 1em;
            }
            .btn{
                margin: 1em .8em;
            }
            @media (min-width:920px) {
                textarea{
                    width:95%;
                    height: 200px;
                }
                .main-content{
                    max-width: 920px;
                    margin-left: auto;
                    margin-right: auto;
                    margin-top:30px;
                }
            }
            @media (max-width:919px) { /* mobile */
                textarea{
                    width:100%;
                    height: 100px;
                    cursor: pointer;
                }
            }
        </style>



        <title><?php echo $lang->donate[0]->msg1; ?></title>
    </head>
    <body>
        <?php
        if (PP_SANDBOX) {
            echo '<div class="alert alert-warning" role="alert">Paypal sandbox is turned on, this is a test donation.</div>';
        }
        ?>
        <div class = "content">
            <div class = "panel panel-default main-content">
                <?php
                if ($return_donor) {
                    printf("<div class='panel-heading'>" . $lang->donate[0]->msg2 . " " . date('l F j Y', $expiration_date) . "</div>", $username);
                }
                ?>
                <div class="panel-body">
                    <?php
                    if ($amountSmall) {
                        exit(printf("<div class='alert alert-danger' role='alert'>" . $lang->donate[0]->msg3 . "</div>", $group['minimum']));
                    }

                    if (isset($grpChange)) {
                        printf("<div class='alert alert-warning' role='alert'><span class='glyphicon glyphicon-flash'></span> " . $lang->donate[0]->changePerks . " <span class='glyphicon glyphicon-flash'></span></div>", $oldGroup['name'], $group['name']);
                    }


                    if (PP_SANDBOX) {
                        print('<form id="donate" name="_xclick" action="https://sandbox.paypal.com/cgi-bin/webscr" method="post" >');
                        print('<input type="hidden" name="business" value="' . PP_SANDBOX_EMAIL . '">');
                    } else {
                        print('<form id="donate" name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post" >');
                        print('<input type="hidden" name="business" value="' . PP_EMAIL . '">');
                    }


                    echo "<center>";
                    echo "<h1>" . $lang->donate[0]->msg4 . "</h1><br/ >";



                    echo "<img src='$avatarfull' /><br /> <h1>  <a href='$steam_link' target='_blank'>$username</a></h1><hr />";
                    echo "</center>";

                    printf("<p>" . $lang->donate[0]->msg5, $amount, $days_purchased);

                    printf(" " . $lang->donate[0]->msg6 . " ", $group['name']);
                    if ($return_donor) {
                        printf($lang->donate[0]->msg7a . ".</p>", date('l F j Y', $expire));
                    } else {
                        printf($lang->donate[0]->msg7 . ".</p>", date('l F j Y', $now), date('l F j Y', $expire));
                    }
                    if ($group['ccc_enabled'] && CCC) {
                        echo "<p>" . $lang->donate[0]->msg8 . "</p>";
                        echo "<input type=\"hidden\" name=\"os0\" value=\"nameColor\"><input type=\"hidden\" name=\"os1\" value=\"chatColor\">";
                        echo "<p><input class='color' name='on0' value='#33CC99' id='colorInput'>" . $lang->misc[0]->msg3 . " <input class='color' name='on1' value='#990000' id='colorInput'>" . $lang->misc[0]->msg2 . "</p>";
                    }
                    echo "<p>" . $lang->donate[0]->msg9 . "</p>";
                    echo "";
                    print('<input type="hidden" name="cmd" value="_xclick">');
                    print('<input type="hidden" name="no_note" value="1">');
                    print('<input type="hidden" name="amount" value="' . $amount . '">');
                    print('<input type="hidden" name="item_name" value="' . PP_DESC . '">');
                    print('<input type="hidden" name="no_shipping" value="1">');
                    print('<input type="hidden" name="rm" value="2">');
                    print('<input type="hidden" name="return" value="' . PP_SUCCESS . '">');
                    print('<input type="hidden" name="notify_url" value="' . PP_IPN . '">');
                    print('<input type="hidden" name="cancel_return" value="' . PP_FAIL . '">');
                    print('<input type="hidden" name="currency_code" value="' . PP_CURRENCY . '">');
                    print("<input type='hidden' name='custom' value='$steam_id|$tier'>");
                    print('<br />');
                    print('<br />');
                    if (is_file('includes/tos.txt')) {
                        $file = file("includes/tos.txt");
                        print('<textarea readonly class="tos">');
                        foreach ($file as $line) {
                            print($line);
                        }
                        print('</textarea>');
                        printf('<p><input type="checkbox" required />%s</p>', $lang->donate[0]->acceptTos);
                    }
                    if (isset($grpChange)) {
                        printf("<p><input type='checkbox' required>" . $lang->donate[0]->msg10 . "</p>", $oldGroup['name'], $group['name']);
                    }
                    print('<input type="submit" value="DONATE!" class="btn btn-lg btn-success" form="donate">');
                    print('</form>');
                    ?>
                    <div class='panel-footer'>
                        <form id="langSelect" method="post">Change Language:
                            <select name = "langSelect" onchange="change()">
                                <?php
                                $langList = $language->listLang();
                                foreach ($langList as $list) {
                                    if ($list == $lang->language) {
                                        printf('<option value="%s" selected>%s</option>', $list, $availableLanguages[$list]);
                                    } else {
                                        printf('<option value="%s">%s</option>', $list, $availableLanguages[$list]);
                                    }
                                }

                                unset($i);
//send the varibles to the new page with the new language
                                printf("<input type='hidden' name='steamid_user' value='%s'><input type='hidden' name='amount' value='%s'>", $userInfo['steamid'], $amount);
                                printf("<input type='hidden' name='tier' value='%s'>", $tier);
                                ?>
                            </select>
                        </form>
                    </div> <!-- panel footer -->
                </div> <!-- panel -->
            </div> <!-- panel-body -->
        </div> <!-- content -->

        <script src="js/jquery-ui.min.js" type="text/javascript"></script>
        <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    </body>
</html>