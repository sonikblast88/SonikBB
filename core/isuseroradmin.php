<?php

function isUserOrAdmin() {
    return isset($_SESSION['type'], $_SESSION['is_loged']) && 
           ($_SESSION['type'] === 1 || $_SESSION['type'] === 2) && 
           $_SESSION['is_loged'] === true;
}