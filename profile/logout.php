<?php
session_start();
session_destroy();

header("Location: /rholance_pms/index.php");
exit;