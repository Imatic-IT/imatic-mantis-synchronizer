<?php

namespace Imatic\Mantis\Synchronizer;
/**
 *
 */
class ImaticWebhook
{

    /**
     * @var string
     */
    private $file;
    /**
     * @var string
     */
    private $dir;

    /**
     * @var ImaticMantisWebhookModel
     */
    private $webhook_model;


    /**
     *
     */
    public function __construct()
    {
        $this->webhook_model = new ImaticMantisWebhookModel();
    }


    /**
     * @param $webhook
     * @return void
     */
    public function createWebhook($webhook)
    {
        $this->data = $webhook;
        $new_webhook = ['name' => $this->data['name'], 'url' => $this->data['url'], 'status' => $this->data['status'] ?: '', 'projects' => json_encode($this->data['projects']) ?: '', 'events' => json_encode($this->data['events']) ?: '',];
        $this->webhook_model->saveWebhook($new_webhook);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }


    /**
     * @param $webhook
     * @return void
     */
    public function updateWebhook($webhook)
    {
        $this->webhook_model->imaticUpdateWebhook($webhook);
        header('Location: ' . $_SERVER['HTTP_REFERER']);

    }

    /**
     * @param $id
     * @return void
     */
    public function deleteWebhook($id)
    {
        $this->webhook_model->imaticDeleteWebhook($id);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }


    /**
     * @return array
     */
    public function getWebhooks()
    {
        $webhooks = $this->webhook_model->imaticGetWebhooks();
        return $webhooks;
    }


    /**
     * @param $id
     * @return mixed
     */
    public function getWebhook($id)
    {
        $webhook = $this->webhook_model->imaticGetWebhook($id);
        return $webhook;
    }

    public function getEnabledWebhooks($event)
    {
        $webhooks = $this->webhook_model->imaticGetEnadbledWebhooks();
        $enabledWebhooks = [];

        foreach ($webhooks as $webhook) {
            if (isset($webhook['events']) && !empty($webhook['events'])) {
                $events = isset($webhook['events']) ? $webhook['events'] : [];
                if ($this->isEnabledEvent($events, $event)) {
                    $enabledWebhooks[] = $webhook;
                }
            }
        }

        return $enabledWebhooks;

    }

    public function isEnabledEvent($webhookEvents, $event)
    {
        $webhookEvents = json_decode($webhookEvents, true);

        if (is_array($webhookEvents)) {
            return in_array($event, $webhookEvents);
        }
        return false;
    }

    /**
     * @param $sended_data
     * @param $url
     * @return array
     */
    public function sendWebhook($sended_data, $url)
    {

        // need implement into webhooks
        $apiKey = "your-api-key";
        $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $apiKey,);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sended_data));
        $response = curl_exec($ch);
        if ($response == false) {

            return ['status' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'error' => "CURL Error: " . curl_error($ch),];
        }
        return ['status' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'body' => json_decode($response, true),];
    }


    /**
     * @return array
     */
    public function getWebhooksProjects()
    {
        $webhooks = $this->getWebhooks();
        $projects = [];
        if (!$webhooks) {
            return $projects;
        };
        foreach ($webhooks as $webhook) {
            $projects = array_unique(array_merge($webhook['projects'], $projects));
        }
        return $projects;
    }
}
