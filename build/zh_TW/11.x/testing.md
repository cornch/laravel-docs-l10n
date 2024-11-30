---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/163/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 50.98
---

# 測試：入門

- [簡介](#introduction)
- [環境](#environment)
- [建立測試](#creating-tests)
- [執行測試](#running-tests)
  - [Running Tests in Parallel](#running-tests-in-parallel)
  - [回報測試覆蓋率](#reporting-test-coverage)
  - [測試分析](#profiling-tests)
  

<a name="introduction"></a>

## 簡介

Laravel is built with testing in mind. In fact, support for testing with [Pest](https://pestphp.com) and [PHPUnit](https://phpunit.de) is included out of the box and a `phpunit.xml` file is already set up for your application. The framework also ships with convenient helper methods that allow you to expressively test your applications.

預設情況下，專案的 `tests` 目錄內包含了兩個目錄：`Feature` 與 `Unit`。^[Unit Test](%E5%96%AE%E5%85%83%E6%B8%AC%E8%A9%A6) 是專注於測試一些小部分、與其他部分獨立的程式碼。其實，單元測試可能會只專注於測試單一方法。在「Unit」測試目錄下的測試不會啟用 Laravel 專案，因此無法存取專案的資料庫或其他 Laravel 的服務。

^[Feature Test](%E5%8A%9F%E8%83%BD%E6%B8%AC%E8%A9%A6)可用來測試較大部分的程式碼 —— 測試各個物件要如何互相使用、測試 JSON Endpoint 的完整 HTTP Request 等。**一般來說，大多數的測試應該都是 Feature Test。使用 Feature Test 有助於確保整體系統如期運作。**

An `ExampleTest.php` file is provided in both the `Feature` and `Unit` test directories. After installing a new Laravel application, execute the `vendor/bin/pest`, `vendor/bin/phpunit`, or `php artisan test` commands to run your tests.

<a name="environment"></a>

## 環境

When running tests, Laravel will automatically set the [configuration environment](/docs/{{version}}/configuration#environment-configuration) to `testing` because of the environment variables defined in the `phpunit.xml` file. Laravel also automatically configures the session and cache to the `array` driver so that no session or cache data will be persisted while testing.

若有需要，也可以自行定義其他的測試環境設定值。`testing` 環境變數可以在專案的 `phpunit.xml` 檔案中修改。不過，在執行測試前，請記得使用 `config:clear` Artisan 指令來清除設定快取！

<a name="the-env-testing-environment-file"></a>

#### `.env.testing` 環境檔

In addition, you may create a `.env.testing` file in the root of your project. This file will be used instead of the `.env` file when running Pest and PHPUnit tests or executing Artisan commands with the `--env=testing` option.

<a name="creating-tests"></a>

## 建立測試

若要建立新測試例，請使用 `make:test` Artisan 指令。預設情況下，測試會被放在 `tests/Feature` 目錄下：

```shell
php artisan make:test UserTest
```
若要在 `tests/Unit` 目錄下建立測試，可在執行 `make:test` 指令時使用 `--unit` 選項：

```shell
php artisan make:test UserTest --unit
```
> [!NOTE]  
> 可以[安裝 Stub](/docs/{{version}}/artisan#stub-customization) 來自訂測試的 Stub。

Once the test has been generated, you may define test as you normally would using Pest or PHPUnit. To run your tests, execute the `vendor/bin/pest`, `vendor/bin/phpunit`, or `php artisan test` command from your terminal:

```php
<?php

test('basic', function () {
    expect(true)->toBeTrue();
});
```
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_basic_test(): void
    {
        $this->assertTrue(true);
    }
}
```
> [!WARNING]  
> If you define your own `setUp` / `tearDown` methods within a test class, be sure to call the respective `parent::setUp()` / `parent::tearDown()` methods on the parent class. Typically, you should invoke `parent::setUp()` at the start of your own `setUp` method, and `parent::tearDown()` at the end of your `tearDown` method.

<a name="running-tests"></a>

## 執行測試

As mentioned previously, once you've written tests, you may run them using `pest` or `phpunit`:

```shell
./vendor/bin/pest
```
```shell
./vendor/bin/phpunit
```
In addition to the `pest` or `phpunit` commands, you may use the `test` Artisan command to run your tests. The Artisan test runner provides verbose test reports in order to ease development and debugging:

```shell
php artisan test
```
Any arguments that can be passed to the `pest` or `phpunit` commands may also be passed to the Artisan `test` command:

```shell
php artisan test --testsuite=Feature --stop-on-failure
```
<a name="running-tests-in-parallel"></a>

### Running Tests in Parallel

By default, Laravel and Pest / PHPUnit execute your tests sequentially within a single process. However, you may greatly reduce the amount of time it takes to run your tests by running tests simultaneously across multiple processes. To get started, you should install the `brianium/paratest` Composer package as a "dev" dependency. Then, include the `--parallel` option when executing the `test` Artisan command:

```shell
composer require brianium/paratest --dev

php artisan test --parallel
```
預設情況下，Laravel 會以機器上可用的 CPU 核心數來建立處理程序。不過，我們也可以使用 `--processes` 選項來調整處理程序的數量：

```shell
php artisan test --parallel --processes=4
```
> [!WARNING]  
> When running tests in parallel, some Pest / PHPUnit options (such as `--do-not-cache-result`) may not be available.

<a name="parallel-testing-and-databases"></a>

#### Parallel Testing and Databases

只要你有設定主要的資料庫連線，Laravel 就會自動為每個執行測試的平行處理程序建立並 Migrate 測試資料庫。Laravel 會使用每個處理程序都不同的處理程序 Token 來作為資料庫的前置詞。舉例來說，若有兩個平行的測試處理程序，則 Laravel 會建立並使用 `your_db_test_1` 與 `your_db_test_2` 測試資料庫。

預設情況下，在不同的 `test` Artisan 指令間，會共用相同的測試資料庫，以在連續呼叫 `test` 指令時使用這些資料庫。不過，我們也可以使用 `--create-databases` 選項來重新建立測試資料庫：

```shell
php artisan test --parallel --recreate-databases
```
<a name="parallel-testing-hooks"></a>

#### 平行測試的 Hook

有時候，我們會需要為一些專案所使用的特定資源做準備，好讓我們能在多個測試處理程序中使用這些資源。

只要使用 `ParallelTesting` Facade，就可指定要在處理程序或測試例的 `setUp` 或 `tearDown` 內要執行的特定程式碼。給定的閉包會收到 `$token` 與 `$testCase` 變數，著兩個變數中分別包含了處理程序的 Token，以及目前的測試例：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\ParallelTesting;
    use Illuminate\Support\ServiceProvider;
    use PHPUnit\Framework\TestCase;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            ParallelTesting::setUpProcess(function (int $token) {
                // ...
            });
    
            ParallelTesting::setUpTestCase(function (int $token, TestCase $testCase) {
                // ...
            });
    
            // Executed when a test database is created...
            ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
                Artisan::call('db:seed');
            });
    
            ParallelTesting::tearDownTestCase(function (int $token, TestCase $testCase) {
                // ...
            });
    
            ParallelTesting::tearDownProcess(function (int $token) {
                // ...
            });
        }
    }
<a name="accessing-the-parallel-testing-token"></a>

#### Accessing the Parallel Testing Token

若想從測試程式碼中的任何地方存取目前平行處理程序的「Token」，我們可以使用 `token` 方法。對於各個測試處理程序來說，平行處理程序的「Token」是一個不重複的字串，可用來在多個平行測試處理程序上為資源分段。舉例來說，Laravel 會自動將該 Token 放在各個由平行測試處理程序所建立的測試資料庫名稱後方：

    $token = ParallelTesting::token();
<a name="reporting-test-coverage"></a>

### 回報測試覆蓋率

> [!WARNING]  
> 要使用該功能，需安裝 [Xdebug](https://xdebug.org) 或 [PCOV](https://pecl.php.net/package/pcov)。

在執行專案測試時，我們可能會想判斷測試例是否有實際涵蓋到專案的程式碼、或是想知道在執行測試時到底使用到專案中多少的程式碼。若要瞭解測試覆蓋率，可在叫用 `test` 指令時提供 `--coverage` 選項：

```shell
php artisan test --coverage
```
<a name="enforcing-a-minimum-coverage-threshold"></a>

#### Enforcing a Minimum Coverage Threshold

可使用 `--min` 選項來為專案定義最低測試覆蓋率門檻。若未符合該門檻，測試套件會執行失敗：

```shell
php artisan test --coverage --min=80.3
```
<a name="profiling-tests"></a>

### 測試分析

Artisan 的測試執行程式中也包含了一個方便的機制，能讓我們列出專案中最慢的測試。執行 `test` 指令時提供 `--profile` 選項，就可以看到一組列表中列出了 10 個最慢的測試，讓我們能輕鬆調查可以改善哪些測試來讓測試套件執行更快：

```shell
php artisan test --profile
```