<?php

if (!defined('NineteenEleven')) {
    die('Direct access not premitted');
}
require_once ABSDIR . "includes/SourceBansClass.php";

class promotions extends sb {

    public function getActivePromos($activeOnly = true) {
        if ($activeOnly) {
            $where = '`active` = 1';
        } else {
            $where = '1';
        }

        $stmt = $this->ddb->query("SELECT * FROM `promotions` WHERE $where");
        $this->activePromos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->activePromos;
    }

    public function checkPromo($code) {
        $code = filter_var($code, FILTER_SANITIZE_STRING);
        $stmt = $this->ddb->prepare("SELECT * FROM `promotions` WHERE `code`=?");
        $stmt->bindParam(1, $code);
        $stmt->execute();
        if ($stmt->rowCount() == 1) {
            $this->promoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->promoInfo;
        } else {
            return false;
        }
    }

}
