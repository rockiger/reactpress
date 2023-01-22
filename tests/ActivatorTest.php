<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ReactPress\Includes\Activator;

class ActivatorPostMock {
    public int $ID = 100;
}

final class ActivatorTest extends TestCase {
    public function testActivateHelper() {
        $post = new ActivatorPostMock();
        $this->assertEquals(Activator::activate_helper([], '2.0.0', fn ($var) => $post), []);
        $this->assertEquals(
            [
                [
                    "allowsRouting" => false,
                    "appname" => "test1",
                    "pageslugs" => [
                        "test1",
                    ],
                    "type" => "development",
                ],
            ],
            Activator::activate_helper(
                [[
                    "allowsRouting" => false,
                    "appname" => "test1",
                    "pageslugs" => [
                        "test1",
                    ],
                    "type" => "development",
                ]],
                '3.0.0',
                fn ($var) => $post
            ),
        );
        $this->assertEquals(
            [
                [
                    "allowsRouting" => false,
                    "appname" => "test1",
                    "pageIds" => [
                        100,
                    ],
                    "type" => "development",
                ],
            ],
            Activator::activate_helper(
                [[
                    "allowsRouting" => false,
                    "appname" => "test1",
                    "pageslug" => "test1",
                    "type" => "development",
                ]],
                '2.0.0',
                fn ($var) => $post
            ),
        );
    }
}
