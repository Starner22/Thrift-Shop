<?php
session_start();
session_unset();
session_destroy();
header("Location: /Thrift-Shop-FrontEnd/login.html");
exit();
?>
