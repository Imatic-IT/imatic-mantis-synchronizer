<?php


namespace Imatic\Mantis\Synchronizer;

class ImaticWebhook
{

    /**
     * @var
     */
    private $sended_data;
    /**
     * @var
     */
    private $webhooks_json;
    /**
     * @var string
     */
    private $file;

    private $webhooks_decoded;

    /**
     *
     */
    public function __construct()
    {
        $this->file = dirname(__DIR__, 1) . '/webhooks/webhooks.json';
    }

    /**
     * @param $sended_data
     * @return void
     */
    public function createWebhook($sended_data)
    {
        $this->data = $sended_data;

        if (!file_exists($this->file)) {
            fopen($this->file, "w");
        }

        $webhooks_decoded = $this->getWebhooks();

        $new_webhook = [
            'name' => $this->data['name'],
            'url' => $this->data['url'],
            'status' => $this->data['status'],
            'projects' => $this->data['projects'],
            'created' => date_create()
        ];

        $webhooks_decoded[] = $new_webhook;

        $this->saveWebhook($webhooks_decoded);

        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }


    /**
     * @param $webhook_data
     * @return void
     */
    public function updateWebhook($webhook_data)
    {

        $id = $webhook_data['webhook_id'];

        $webhooks_decoded = $this->getWebhooks();

        $webhooks_decoded[$id]['name'] = $webhook_data['name'];
        $webhooks_decoded[$id]['url'] = $webhook_data['url'];
        $webhooks_decoded[$id]['status'] = $webhook_data['status'];
        $webhooks_decoded[$id]['projects'] = $webhook_data['projects'];

        $this->saveWebhook($webhooks_decoded);

        header('Location: ' . $_SERVER['HTTP_REFERER']);

    }

    public function deleteWebhook($id)
    {

        $webhooks_decoded = $this->getWebhooks();


        unset($webhooks_decoded[$id]);
        sort($webhooks_decoded);


        $this->saveWebhook($webhooks_decoded);

        header('Location: ' . $_SERVER['HTTP_REFERER']);

    }


    /**
     * @param $webhooks_decoded
     * @return string
     */
    private function saveWebhook($webhooks_decoded)
    {

        $webhooks_encoded = json_encode((object)$webhooks_decoded, JSON_PRETTY_PRINT);

        file_put_contents($this->file, $webhooks_encoded);

        return $this->file;
    }


    /**
     * @return mixed|void
     */
    public function getWebhooks()
    {

        if (!file_exists($this->file)) {
            return;
        }

        $this->webhooks_json = json_decode(file_get_contents($this->file), true);

        return $this->webhooks_json;
    }


    /**
     * @param $id
     * @return mixed
     */
    public function getWebhook($id)
    {

        $this->webhooks_json = json_decode(file_get_contents($this->file), true);

        return $this->webhooks_json[$id];
    }

    public function sendWebhook($sended_data, $user = null)
    {
        $this->webhooks_decoded = $this->getWebhooks();

        foreach ($this->webhooks_decoded as $webhook) {

            if (in_array($sended_data->issue->project_id, $webhook['projects'])) {

                // need implement into webhooks
                $apiKey = "your-api-key";

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                );

                $ch = curl_init($webhook['url']);

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sended_data));

                $response = curl_exec($ch);

                if ($response === false) {
                    return [
                        'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                        'error' => "CURL Error: " . curl_error($ch),
                    ];
                }

                return [
                    'status' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                    'body' => json_decode($response, true),
                ];
            }
        }
    }

    public function getWebhooksProjects()
    {

        $this->webhooks_decoded = $this->getWebhooks();

        $projects = [];
        foreach ($this->webhooks_decoded as $webhook) {
            $projects = array_unique(array_merge($webhook['projects'], $projects));
        }

        return $projects;
    }
}
