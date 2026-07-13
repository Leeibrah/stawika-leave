<?php

namespace app\messages;
class AlertMessage {
    public static function display() {
        if (isset($_SESSION['message']) && isset($_SESSION['message_code'])) {
            $message = $_SESSION['message'];
            $message_code = $_SESSION['message_code'];
            $type = ($message_code === 'error') ? 'danger' : $message_code;
            ?>
            <script>
                if (typeof showToast === 'function') {
                    showToast(<?= json_encode($message) ?>, <?= json_encode($type) ?>);
                }
            </script>
            <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_code']);
        }
    }
}
?>
