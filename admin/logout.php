<?php
session_start();
// End of session and clear session variables
session_destroy();
header("Location: /");
exit();
?>
