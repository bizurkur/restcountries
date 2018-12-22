<?php

namespace Bizurkur\Tests;

use Bizurkur\CountryLookup;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class CountryLookupTest extends TestCase
{
    /**
     * @var CountryLookup
     */
    protected $fixture = null;

    /**
     * @var Client|MockObject
     */
    protected $client = null;

    /**
     * @var string[]
     */
    protected $fields = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->fields = [uniqid('a'), uniqid('b'), uniqid('c')];

        $this->fixture = new CountryLookup($this->client, $this->fields);
    }

    public function testLookupCallsClientForCodeAndName(): void
    {
        $term = substr(uniqid(), 0, rand(2, 3));

        $response = $this->createResponse();
        $this->client->expects(self::exactly(2))
            ->method('__call')
            ->withConsecutive(
                ['get', ['alpha/'.$term.'?fields='.implode(';', $this->fields)]],
                ['get', ['name/'.$term.'?fields='.implode(';', $this->fields)]]
            )
            ->willReturn($response);

        $this->fixture->lookup($term);
    }

    /**
     * @dataProvider sampleNameOnlyTerms
     */
    public function testLookupCallsClientForNameOnly(string $term): void
    {
        $response = $this->createResponse();
        $this->client->expects(self::once())
            ->method('__call')
            ->with('get', ['name/'.$term.'?fields='.implode(';', $this->fields)])
            ->willReturn($response);

        $this->fixture->lookup($term);
    }

    public function sampleNameOnlyTerms(): array
    {
        return [
            'too long' => [uniqid()],
            'too short' => [substr(uniqid(), 0, 1)],
        ];
    }

    /**
     * @param mixed $codeResults
     * @param mixed $nameResults
     * @param int|null $limit
     * @param array[] $expected
     *
     * @dataProvider sampleSortData
     */
    public function testLookup(
        $codeResults,
        $nameResults,
        ?int $limit,
        array $expected
    ): void {
        $this->client->method('__call')
            ->willReturnOnConsecutiveCalls(
                $this->createResponse($codeResults),
                $this->createResponse($nameResults)
            );

        $actual = $this->fixture->lookup(substr(uniqid(), 0, 3), $limit);

        self::assertEquals($expected, $actual);
    }

    public function sampleSortData(): array
    {
        $dataA = ['name' => uniqid('a')];
        $dataB = ['name' => uniqid('b')];
        $dataC = ['name' => uniqid('c')];
        $dataD = ['name' => uniqid('d')];

        return [
            'non-array code result' => [
                'codeResults' => uniqid(),
                'nameResults' => [],
                'limit' => null,
                'expected' => [],
            ],
            'non-array name result' => [
                'codeResults' => [],
                'nameResults' => uniqid(),
                'limit' => null,
                'expected' => [],
            ],
            'single code result' => [
                'codeResults' => $dataA,
                'nameResults' => [],
                'limit' => null,
                'expected' => [$dataA],
            ],
            'single name result' => [
                'codeResults' => [],
                'nameResults' => $dataA,
                'limit' => null,
                'expected' => [$dataA],
            ],
            'dual single results' => [
                'codeResults' => $dataB,
                'nameResults' => $dataA,
                'limit' => null,
                'expected' => [$dataA, $dataB],
            ],
            'multiple code results' => [
                'codeResults' => [$dataD, $dataB, $dataC],
                'nameResults' => $dataA,
                'limit' => null,
                'expected' => [$dataA, $dataB, $dataC, $dataD],
            ],
            'multiple name results' => [
                'codeResults' => $dataA,
                'nameResults' => [$dataD, $dataB, $dataC],
                'limit' => null,
                'expected' => [$dataA, $dataB, $dataC, $dataD],
            ],
            'mixed results with limit' => [
                'codeResults' => [$dataD, $dataB, $dataC],
                'nameResults' => $dataA,
                'limit' => 3,
                'expected' => [$dataA, $dataB, $dataC],
            ],
        ];
    }

    public function testLookupException(): void
    {
        $exception = $this->createMock(ClientException::class);
        $this->client->method('__call')->willThrowException($exception);

        $actual = $this->fixture->lookup(uniqid());

        self::assertEquals([], $actual);
    }

    /**
     * @param mixed $body
     *
     * @return ResponseInterface
     */
    protected function createResponse($body = ''): ResponseInterface
    {
        return $this->createConfiguredMock(
            ResponseInterface::class,
            ['getBody' => json_encode($body)]
        );
    }
}
