<?php
use Webgk\Helper\AjaxHelper;
?>

<script data-skip-moving="true">
    window.endpoints = {
        registerUser: '<?= AjaxHelper::getControllerActionUrl(new \Webgk\Controller\User, 'registerUser') ?>',
    };
</script>