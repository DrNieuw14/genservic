<?php
function logAction($conn, $user_id, $action, $module){
    mysqli_query($conn, "
        INSERT INTO audit_logs (user_id, action, module)
        VALUES ('$user_id', '$action', '$module')
    ");
}
?>