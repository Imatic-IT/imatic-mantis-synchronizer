<?php

use Imatic\Mantis\Synchronizer\ImaticWebhook;

auth_ensure_user_authenticated();

$id = $_POST['webhook_id'];

$imatic_webhook = new ImaticWebhook();
$webhook =  $imatic_webhook->getWebhook($id);

echo json_encode($webhook);
