<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HTTPClient {
    private $client;

    const URL_GS = 'http://personendatenbank.germania-sacra.de/api/v1.0/person';
    const URL_GS_API = 'http://germania-sacra-datenbank.uni-goettingen.de/api/v1.0/person';

    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }

    public function findCanonByDiocese($diocese, $limit, $offset) {

        $qparam = [
            'query[0][field]' => 'amt.klosterid',
            'query[0][value]' => $diocese,
            'limit' => $limit,
            'offset' => $offset,
            'format' => 'json'
        ];

        $response = $this->client->request('GET', self::URL_GS_API, ['query' => $qparam]);

        $json = $response->getContent();

        $canons = json_decode($json);
        return $canons->records;
    }

}
