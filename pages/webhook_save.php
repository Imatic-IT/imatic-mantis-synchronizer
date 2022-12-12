<?php

use Imatic\Mantis\Synchronizer\ImaticWebhook;

if (isset($_POST) && !empty($_POST)) {

    $webhook = new ImaticWebhook($_POST);

    if ($_POST['webhook_method'] == 'create') {
        $webhook->createWebhook($_POST);
    }

    if ($_POST['webhook_method'] == 'update') {
        $webhook->updateWebhook($_POST);
    }

}