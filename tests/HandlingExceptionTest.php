<?php

namespace FiveamCode\LaravelNotionApi\Tests;

use FiveamCode\LaravelNotionApi\Exceptions\HandlingException;
use PHPUnit\Framework\TestCase;

/**
 * Class HandlingExceptionTest.
 */
class HandlingExceptionTest extends TestCase
{
    public function test_returns_a_handling_exception_instance_with_payload()
    {
        $wrapperException = HandlingException::instance('An error occured.', ['foo' => 'bar']);

        $this->assertInstanceOf(
            HandlingException::class,
            $wrapperException
        );

        $this->assertNotEmpty($wrapperException->getPayload());
    }

    public function test_returns_a_handling_exception_instance_without_payload()
    {
        $wrapperException = HandlingException::instance('An error occured.');

        $this->assertInstanceOf(
            HandlingException::class,
            $wrapperException
        );

        $this->assertEmpty($wrapperException->getPayload());
    }
}
