<?php

use Imatic\Mantis\Synchronizer\ImaticWebhook;

if (isset($_POST) && !empty($_POST)) {

    $webhook = new ImaticWebhook($_POST);

    $webhook->deleteWebhook($_POST['webhook_id']);
}
