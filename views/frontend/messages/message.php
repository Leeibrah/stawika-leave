<?php
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_code = $_SESSION['message_code'] ?? 'info';
    $type = ($message_code === 'error') ? 'danger' : $message_code;
    ?>
    <script>
        if (<?= json_encode($message) ?> === "Unauthenticated! Please log in!") {
            window.location.href = "/login";
        } else if (typeof showToast === 'function') {
            showToast(<?= json_encode($message) ?>, <?= json_encode($type) ?>);
        }
    </script>
    <?php
    unset($_SESSION['message']);
    unset($_SESSION['message_code']);
}
?>
