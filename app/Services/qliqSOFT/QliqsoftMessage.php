<?php namespace App\Services\qliqSOFT;

use App\CLH\Contracts\Repositories\QliqsoftMessageRepositoryInterface;
use App\CLH\Repositories\QliqsoftMessageRepository;
use App\QliqsoftMessageLog;
use App\Services\qliqSOFT\ApiCredentials;
use App\CLH\Contracts\Messaging\SecureMessage;
use GuzzleHttp\Client;

class QliqsoftMessage implements SecureMessage{

    /**
     * Store all your ApiCredentials (key, secret, endpoint url) in this class.
     *
     * @var ApiCredentials
     */
    protected $apiCredentials;

    /**
     * QliqsoftMessageRepository
     *
     * @var QliqsoftMessageRepository
     */
    protected $repo;

    public function __construct(ApiCredentials $credentials, QliqsoftMessageRepositoryInterface $repo)
    {
        $this->apiCredentials = $credentials;
        $this->repo = $repo;
    }

    /**
     * @param $to
     * @param $from
     * @param $message
     * @param array $args
     * @return array
     */
    public function send($to, $from, $message, $args = [])
    {
        $requestData = $this->prepareRequestData( get_defined_vars() );

        $client = new Client();

        $response = $client->post($this->apiCredentials->apiUrl, [
            'json' => $requestData
        ]);

        if ( $response->getStatusCode() == 202 )
        {
            $body = json_decode( $response->getBody(), true );

            $this->repo->saveResponseToDb( array_merge($requestData, $body) );

            return $body;
        }

        return 'error';
    }

    /**
     * This will pick up additional arguments (if provided) and prepare the request.
     * Use camel case for the additional parameters.
     * eg. conversationId
     *
     * @param array $args
     * @return array|null
     */
    public function prepareRequestData($args = [])
    {
        $requestData = [
            'api_key' => $this->apiCredentials->apiKey,
            'to' => $args['to'],
            'from' => $args['from'],
            'text' => $args['message'],
        ];

        if ( array_key_exists('subject', $args['args']) && !empty($args['args']['subject']) ) $requestData['subject'] = $args['args']['subject'];

        if ( array_key_exists('conversationId', $args['args']) && !empty($args['args']['conversationId']) ) {
            $requestData['conversation_id'] = $args['args']['conversationId'];
        } elseif ( $this->repo->getConversationId($args['to']) ) {
//            NOTE: Here I am assuming that we let qliq set the conversation id.
//            We could set it manually by providing it our selves, or qliq gives us one whenever we send a message
            $requestData['conversation_id'] = $this->repo->getConversationId($args['to']);
        }

        return empty($requestData) ? null : $requestData;
    }

}