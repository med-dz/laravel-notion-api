<?php

use FiveamCode\LaravelNotionApi\Entities\Blocks\BulletedListItem;
use FiveamCode\LaravelNotionApi\Entities\Blocks\HeadingOne;
use FiveamCode\LaravelNotionApi\Entities\Blocks\HeadingThree;
use FiveamCode\LaravelNotionApi\Entities\Blocks\HeadingTwo;
use FiveamCode\LaravelNotionApi\Entities\Blocks\NumberedListItem;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Paragraph;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Quote;
use FiveamCode\LaravelNotionApi\Entities\Blocks\ToDo;
use FiveamCode\LaravelNotionApi\Entities\Blocks\Toggle;
use FiveamCode\LaravelNotionApi\Exceptions\HandlingException;

it('throws a handling exception for wrong type', function ($entityClass) {
    $this->expectException(HandlingException::class);
    $entityClass::create(new \stdClass());
})->with([
    [BulletedListItem::class],
    [HeadingOne::class],
    [HeadingTwo::class],
    [HeadingThree::class],
    [NumberedListItem::class],
    [Paragraph::class],
    [ToDo::class],
    [Toggle::class],
    [Quote::class],
]);
