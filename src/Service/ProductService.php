<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductService
{
    private $client;
    private $username = '658A3699-750C-427E-9B14-EB7CDCA9BEDB';
    private $password = '93F02F5C-7371-4D8A-A123-0610F15A3777';
    private $apiUrl = 'https://ext.btp.link/Gateway/ClientApi/ProductCatalogueGet';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getProducts(): array
    {
        $response = $this->client->request('GET', $this->apiUrl, [
            'auth_basic' => [$this->username, $this->password],
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Error fetching products');
        }

        $data = $response->toArray();
        
        return $data['lines'] ?? [];
        # return $data['lines'][0] ?? null; //first product
    }
}
