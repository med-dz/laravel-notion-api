<?php

use FiveamCode\LaravelNotionApi\Entities\Page;
use FiveamCode\LaravelNotionApi\Entities\User;
use FiveamCode\LaravelNotionApi\Exceptions\NotionException;
use FiveamCode\LaravelNotionApi\Notion;
use FiveamCode\LaravelNotionApi\Query\Filters\Filter;
use FiveamCode\LaravelNotionApi\Query\Filters\Operators;
use FiveamCode\LaravelNotionApi\Query\StartCursor;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

it('sends authenticated paginated user requests through Guzzle', function () {
    $handler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/stubs/endpoints/users/response_all_200.json')),
    ]);
    $notion = new Notion('test-token');
    $notion->getConnection()->setHandler($handler);

    $users = $notion->users()->limit(2)->offset(new StartCursor('next-page'))->all();
    $request = $handler->getLastRequest();
    parse_str($request->getUri()->getQuery(), $query);

    $this->assertSame('GET', $request->getMethod());
    $this->assertSame('api.notion.com', $request->getUri()->getHost());
    $this->assertSame('/v1/users', $request->getUri()->getPath());
    $this->assertSame(['page_size' => '2', 'start_cursor' => 'next-page'], $query);
    $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
    $this->assertSame('2021-05-13', $request->getHeaderLine('Notion-Version'));
    $this->assertCount(2, $users->asCollection());
    $this->assertContainsOnlyInstancesOf(User::class, $users->asCollection());
    $this->assertSame('Avocado Lovelace', $users->asCollection()->first()->getName());
});

it('serializes database query JSON through Guzzle', function () {
    $handler = new MockHandler([
        new Response(200, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/stubs/endpoints/databases/response_query_limit1_200.json')),
    ]);
    $notion = new Notion('test-token');
    $notion->getConnection()->setHandler($handler);

    $pages = $notion->database('8284f3ff77e24d4a939d19459e4d6bdc')
        ->filterBy(Filter::textFilter('Name', Operators::CONTAINS, 'Betty'))
        ->limit(1)
        ->query();
    $request = $handler->getLastRequest();

    $this->assertSame('POST', $request->getMethod());
    $this->assertSame('/v1/databases/8284f3ff77e24d4a939d19459e4d6bdc/query', $request->getUri()->getPath());
    $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
    $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
    $this->assertSame('2021-05-13', $request->getHeaderLine('Notion-Version'));
    $this->assertSame([
        'filter' => ['or' => [['property' => 'Name', 'text' => ['contains' => 'Betty']]]],
        'page_size' => 1,
    ], json_decode((string) $request->getBody(), true));
    $this->assertCount(1, $pages->asCollection());
    $this->assertInstanceOf(Page::class, $pages->asCollection()->first());
    $this->assertSame('Betty Holberton', $pages->asCollection()->first()->getTitle());
});

it('converts Guzzle HTTP error responses into Notion exceptions', function () {
    $handler = new MockHandler([
        new Response(404, ['Content-Type' => 'application/json'], file_get_contents(__DIR__.'/stubs/endpoints/users/response_specific_404.json')),
    ]);
    $notion = new Notion('test-token');
    $notion->getConnection()->setHandler($handler);

    $this->expectException(NotionException::class);
    $this->expectExceptionCode(404);
    $this->expectExceptionMessage('Not Found: (object_not_found) (Could not find user with ID: b55c9c91-384d-452b-81db-d1ef79372b79.)');

    $notion->users()->find('b55c9c91-384d-452b-81db-d1ef79372b79');
});
