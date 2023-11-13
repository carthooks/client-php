<?php

namespace CartHooks;

use GuzzleHttp\Client;

class CartHooksClient
{
    private $client;
    private $apiKey;
    private $apiSecret;
    private $headers;

    public function __construct($apiKey, $apiSecret)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.carthooks.com/',
            'timeout'  => 2.0,
        ]);

        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->headers = [
            'X-Api-Key' => $this->apiKey,
            'X-Api-Secret' => $this->apiSecret,
        ];
    }

    public function setBaseUrl($baseUrl)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'timeout'  => 2.0,
        ]);
        return $this;
    }

    public function getItems($appId, $collectionId, array $options = [])
    {
        $query = [];

        // Field selection
        if (isset($options['fields'])) {
            foreach ($options['fields'] as $index => $field) {
                $query["fields[{$index}]"] = $field;
            }
        }

        // Filters
        if (isset($options['filters'])) {
            foreach ($options['filters'] as $field => $filter) {
                foreach ($filter as $operator => $value) {
                    $query["filters[{$field}][{$operator}]"] = $value;
                }
            }
        }

        // Sorting
        if (isset($options['sort'])) {
            foreach ($options['sort'] as $index => $sortField) {
                $query["sort[{$index}]"] = $sortField;
            }
        }

        // Pagination
        if (isset($options['pagination'])) {
            foreach ($options['pagination'] as $key => $value) {
                $query["pagination[{$key}]"] = $value;
            }
        }

        $response = $this->client->request('GET', "v1/apps/{$appId}/collections/{$collectionId}/items", [
            'headers' => $this->headers,
            'query' => $query,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getItemById($appId, $collectionId, $itemId, array $fields = [])
    {
        $response = $this->client->request('GET', "apps/{$appId}/collections/{$collectionId}/items/{$itemId}", [
            'headers' => $this->headers,
            'query' => [
                'fields' => $fields,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createItem($appId, $collectionId, array $data)
    {
        $response = $this->client->request('POST', "apps/{$appId}/collections/{$collectionId}/items", [
            'headers' => $this->headers,
            'json' => ['data' => $data],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function updateItem($appId, $collectionId, $itemId, array $data)
    {
        $response = $this->client->request('PUT', "apps/{$appId}/collections/{$collectionId}/items/{$itemId}", [
            'headers' => $this->headers,
            'json' => ['data' => $data],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function deleteItem($appId, $collectionId, $itemId)
    {
        $response = $this->client->request('DELETE', "apps/{$appId}/collections/{$collectionId}/items/{$itemId}", [
            'headers' => $this->headers,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getUploadToken(){
        $response = $this->client->request('GET', "/uploads/token", [
            'headers' => $this->headers,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}