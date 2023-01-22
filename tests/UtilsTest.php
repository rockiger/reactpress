<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Hamcrest\Util;
use ReactPress\Admin\Utils;

use function Brain\Monkey\Functions\stubs;

class UtilsPostMock {
    public int $ID = 100;
    public string $post_title = 'Title';
}

define('REPR_APPS_PATH', '/htdocs/wp-content/reactpress/apps');
$_SERVER['DOCUMENT_ROOT'] = '/htdocs';
$GLOBALS['get_post'] = fn (int $id) => new UtilsPostMock();
$GLOBALS['get_permalink'] = fn (int $id) => '/test';

final class UtilsTest extends TestCase {

    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testAppPath() {
        $this->assertEquals('/htdocs/wp-content/reactpress/apps/test', Utils::app_path('test'));
        $this->assertEquals('/wp-content/reactpress/apps/test', Utils::app_path('test', true));
    }

    public function testArrayAdd() {
        $this->assertEquals(['test'], Utils::array_add([], 'test'));
        $this->assertEquals([1, 2], Utils::array_add([1], 2));
    }

    public function testGetAppOptions() {
        $this->assertEquals(null, Utils::__get_app_options('test', []));
        $this->assertEquals(
            [
                "allowsRouting" => false,
                "appname" => "test",
                "pageIds" => [
                    100,
                ],
                "type" => "development",
            ],
            Utils::__get_app_options('test', [
                [
                    "allowsRouting" => false,
                    "appname" => "test",
                    "pageIds" => [
                        100,
                    ],
                    "type" => "development",
                ],
            ])
        );
    }

    public function testDeletePage() {
        Monkey\Functions\stubs([
            'get_post' => new UtilsPostMock(),
            'get_permalink' => '/title'
        ]);
        $this->assertEquals([], Utils::__delete_page([], 'test', 100));
        $this->assertEquals(Utils::__get_apps([
            [
                "allowsRouting" => false,
                "appname" => "test1",
                "pageIds" => [],
            ],
        ], []), Utils::__delete_page(Utils::__get_apps([
            [
                "allowsRouting" => false,
                "appname" => "test1",
                "pageIds" => [
                    100,
                ],
            ],
        ], []), 'test1', 100));
    }

    /** Test when directories of React apps are present */
    public function testGetApps_fromDir() {
        Monkey\Functions\stubs([
            'get_post' => null,
            'get_permalink' => null
        ]);
        $this->assertEquals([], Utils::__get_apps([], []));
        $this->assertEquals([[
            "allowsRouting" => false,
            "appname" => "test",
            "pageIds" => [],
            "pages" => [],
            "type" => 'orphan',
        ]], Utils::__get_apps([], ['test']));
    }

    /** Test the when repr_apps are present */
    public function testGetApps_fromOptions() {
        Monkey\Functions\stubs([
            'get_post' => new UtilsPostMock(),
            'get_permalink' => 'https://domain.test/title'
        ]);
        $this->assertEquals([[
            "allowsRouting" => false,
            "appname" => "test",
            "pageIds" => [100],
            "pages" => [[
                'ID' => 100,
                'permalink' => 'https://domain.test/title',
                'title' => 'Title'
            ]],
            "type" => 'orphan',
        ]], Utils::__get_apps([['allowsRouting' => false, "appname" => 'test', 'pageIds' => [100]]], []));
    }
}
