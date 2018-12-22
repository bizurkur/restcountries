<?php

namespace Bizurkur;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

class CountryLookup
{
    /**
     * @var Client
     */
    protected $client = null;

    /**
     * @var string[]
     */
    protected $fields = null;

    /**
     * @param Client $client
     */
    public function __construct(Client $client, array $fields = [])
    {
        $this->client = $client;
        $this->fields = $fields;
    }

    /**
     * Looks up a search term for matching country codes or names.
     *
     * This sorts the data by country name and limits the results to a set number.
     *
     * @param string $query
     * @param int|null $limit
     *
     * @return array[]
     */
    public function lookup(string $query, ?int $limit = 50): array
    {
        $results = array_merge(
            $this->lookupByCode($query),
            $this->lookupByName($query)
        );

        ksort($results);

        return array_slice(array_values($results), 0, $limit);
    }

    /**
     * Looks up a country by code.
     *
     * Ignores any string that isn't a two or three character string.
     *
     * @param string $code
     *
     * @return array[]
     */
    protected function lookupByCode(string $code): array
    {
        $len = strlen($code);
        if ($len > 3 || $len < 2) {
            return [];
        }

        return $this->getResource('alpha/', $code);
    }

    /**
     * Looks up a country by name.
     *
     * Can be partial or fill name.
     *
     * @param string $name
     *
     * @return array[]
     */
    protected function lookupByName(string $name): array
    {
        return $this->getResource('name/', $name);
    }

    /**
     * Gets a resource from the REST Countries API.
     *
     * Automatically applies the fields to filter on.
     *
     * @param string $resource
     * @param string $term
     *
     * @return array[]
     */
    protected function getResource(string $resource, string $term): array
    {
        try {
            $response = $this->client->get($resource.$term.'?fields='.implode(';', $this->fields));
        } catch (ClientException $exception) {
            return [];
        }

        return $this->parseResponse($response);
    }

    /**
     * Parses a response to make it uniform.
     *
     * @param ResponseInterface $response
     *
     * @return array[]
     */
    protected function parseResponse(ResponseInterface $response): array
    {
        $data = json_decode($response->getBody(), true);
        if (!is_array($data)) {
            return [];
        }

        if (isset($data['name'])) {
            // Single response doesn't get returned as list?
            return [
                $data['name'] => $data,
            ];
        }

        $names = array_column($data, 'name');
        $results = array_combine($names, $data);

        return $results ?: [];
    }
}
