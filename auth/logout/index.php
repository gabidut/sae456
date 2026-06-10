<?php
include("../../includes/global.php");
$authentificator->logout();
header("Location: /");
exit();