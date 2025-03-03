<?php

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['type'], $_SESSION['is_loged']) && $_SESSION['type'] === 2 && $_SESSION['is_loged'] === true;
    }
}
?>
