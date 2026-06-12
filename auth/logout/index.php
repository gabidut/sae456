<?php
include("../../includes/global.php");
$authentificator->logout();
$session->unsetAdminUser();
header("Location: /");
exit();