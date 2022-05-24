<?php

namespace Spotler;

use Exception;
use Spotler\Exceptions\SpotlerException;
use Spotler\Modules\Contact;
use Spotler\Modules\Campaign;
use Spotler\Modules\CampaignMailing;

class SpotlerClient
{
    private string          $consumerKey;
    private string          $consumerSecret;
    private Client          $client;
    private int             $responseCode;
    private                 $responseBody;
    private Contact         $contact;
    private Campaign        $campaign;
    private CampaignMailing $campaignMailing;



    public function __construct(string $key, string $secret)
    {
        $this->consumerKey     = $key;
        $this->consumerSecret  = $secret;
        $this->client          = new Client($this->consumerKey, $this->consumerSecret);
        $this->contact         = new Contact($this);
        $this->campaign        = new Campaign($this);
        $this->campaignMailing = new CampaignMailing($this);
    }



    /**
     * @throws SpotlerException
     * @throws Exception
     */
    public function execute(string $endpoint, string $method = 'GET', $data = null)
    {
        try {
            $response           = $this->client->execute($endpoint, $method, $data);
            $this->responseCode = $this->client->getLastResponseCode();
            $this->responseBody = $this->client->getLastResponseBody();

            // Status code 204 is Success without content
            if ($this->client->getLastResponseCode() == 404) {
                throw new SpotlerException(sprintf('Endpoint %s not found', $endpoint), 404);
            }
            // Status code 204 is Success without content
            if ($this->client->getLastResponseCode() == 204) {
                return true;
            }
            if ($this->client->getLastResponseCode() > 299) {
                $data = json_decode($response);
                if ($data === null) {
                    throw new SpotlerException('System error on spotler server', $this->client->getLastResponseCode());
                }

                $message = sprintf('Message: %s\nType: %s', $data->message, $data->errorType);
                throw new SpotlerException($message, $this->client->getLastResponseCode());
            }

            // decode json string to stdObject
            $data = json_decode($response);

            // when no valid json response we will return false
            if ($data === null) {
                return false;
            }

            return $data;
        } catch (Exception $ex) {
            throw new SpotlerException($ex);
        }
    }



    public function getLastResponseCode(): int
    {
        return $this->responseCode;
    }



    public function getLastResponseBody()
    {
        return $this->responseBody;
    }



    public function contact() : Contact
    {
        return $this->contact;
    }



    public function campaign(): Campaign
    {
        return $this->campaign;
    }



    public function campaignMailing(): CampaignMailing
    {
        return $this->campaignMailing;
    }
}
