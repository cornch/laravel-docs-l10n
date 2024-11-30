---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/41/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 53.6
---

# 資料庫測試

- [簡介](#introduction)
  - [Resetting the Database After Each Test](#resetting-the-database-after-each-test)
  
- [Model Factory](#model-factories)
- [執行 Seeder](#running-seeders)
- [可用的 Assertion](#available-assertions)

<a name="introduction"></a>

## 簡介

Laravel 提供了數種實用工具與 Assertion (判斷提示) 讓你能更輕鬆地測試由資料庫驅動的網站。此外，通過 Laravel 的 Model Factory 與 Seeder，也能輕鬆地使用專案的 Eloquent Model 與 Eloquent 關聯來測試資料庫。我們會在接下來的說明文件內討論這些強大的工具。

<a name="resetting-the-database-after-each-test"></a>

### Resetting the Database After Each Test

在進一步繼續之前，我們先來討論如何在每個測試前重設資料庫，這樣一來前一個測試的資料就不會影響到接下來的測試。Laravel 內含了 `Illuminate\Foundation\Testing\RefreshDatabase` Trait，會處理這樣的重設。只需要在測試類別內 use 這個 Trait 即可：

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('basic example', function () {
    $response = $this->get('/');

    // ...
});
```
```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic functional test example.
     */
    public function test_basic_example(): void
    {
        $response = $this->get('/');

        // ...
    }
}
```
當資料庫架構 (Schema) 已是最新的時候， `Illuminate\Foundation\Testing\RefreshDatabase` Trait 將不會執行資料庫遷移 (Migration)，只會在資料庫 Transaction 中執行測試。因此，在未使用該 Trait 中的測試例中，若有新增紀錄，將會保留在資料庫中。

若想完整重設資料庫，請改用 `Illuminate\Foundation\Testing\DatabaseMigrations` 或 `Illuminate\Foundation\Testing\DatabaseTruncation` Trait。不過，這兩種方式都會比 `RefreshDatabase` Trait 慢很多。

<a name="model-factories"></a>

## Model Factory

在測試時，我們可能會需要在執行測試前先插入一些資料到資料庫內。比起在建立這個測試資料時手動指定各個欄位的值，在 Laravel 中，我們可以使用 [Model Factory]((/docs/{{version}}/eloquent-factories) 來為各個 [Eloquent Model](/docs/{{version}}/eloquent) 定義一系列的預設屬性。

若要瞭解更多有關建立 Model Factory，或是使用 Model Factory 來建立 Model 的資訊，請參考完整的 [Model Factory 說明文件](/docs/{{version}}/eloquent-factories)。定義好 Model Factory 後，就可以在測試中使用 Factory 來建立 Model：

```php
use App\Models\User;

test('models can be instantiated', function () {
    $user = User::factory()->create();

    // ...
});
```
```php
use App\Models\User;

public function test_models_can_be_instantiated(): void
{
    $user = User::factory()->create();

    // ...
}
```
<a name="running-seeders"></a>

## 執行 Seeder

若想使用[資料庫 Seeder](/docs/{{version}}/seeding) 來在功能測試時修改資料庫，則可以叫用 `seed` 方法。預設情況下，`seed` 方法會執行 `DatabaseSeeder`，該 Seeder 應用來執行所有其他的 Seeder。或者，也可以傳入指定的 Seeder 類別名稱給 `seed` 方法：

```php
<?php

use Database\Seeders\OrderStatusSeeder;
use Database\Seeders\TransactionStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('orders can be created', function () {
    // Run the DatabaseSeeder...
    $this->seed();

    // Run a specific seeder...
    $this->seed(OrderStatusSeeder::class);

    // ...

    // Run an array of specific seeders...
    $this->seed([
        OrderStatusSeeder::class,
        TransactionStatusSeeder::class,
        // ...
    ]);
});
```
```php
<?php

namespace Tests\Feature;

use Database\Seeders\OrderStatusSeeder;
use Database\Seeders\TransactionStatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating a new order.
     */
    public function test_orders_can_be_created(): void
    {
        // Run the DatabaseSeeder...
        $this->seed();

        // Run a specific seeder...
        $this->seed(OrderStatusSeeder::class);

        // ...

        // Run an array of specific seeders...
        $this->seed([
            OrderStatusSeeder::class,
            TransactionStatusSeeder::class,
            // ...
        ]);
    }
}
```
或者，也可以使用 `RefreshDatabase` Trait 來讓 Laravel 在每次測試前都自動執行資料庫 Seed。可以通過在基礎測試類別上定義 `$seed` 屬性來完成：

    <?php
    
    namespace Tests;
    
    use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
    
    abstract class TestCase extends BaseTestCase
    {
        /**
         * Indicates whether the default seeder should run before each test.
         *
         * @var bool
         */
        protected $seed = true;
    }
當 `$seed` 屬性為 `true` 時，各個使用了 `RefreshDatabase` Trait 的測試都會在開始前先執行 `Database\Seeders\DatabaseSeeder` 類別。不過，也可以通過在測試類別內定義 `$seeder` 屬性來指定要執行的 Seeder。

    use Database\Seeders\OrderStatusSeeder;
    
    /**
     * Run a specific seeder before each test.
     *
     * @var string
     */
    protected $seeder = OrderStatusSeeder::class;
<a name="available-assertions"></a>

## 可用的 Assertion

Laravel provides several database assertions for your [Pest](https://pestphp.com) or [PHPUnit](https://phpunit.de) feature tests. We'll discuss each of these assertions below.

<a name="assert-database-count"></a>

#### assertDatabaseCount

判斷資料庫中的某個資料表是否包含給定數量的記錄：

    $this->assertDatabaseCount('users', 5);
<a name="assert-database-empty"></a>

#### assertDatabaseEmpty

Assert that a table in the database contains no records:

    $this->assertDatabaseEmpty('users');
<a name="assert-database-has"></a>

#### assertDatabaseHas

判斷資料庫中的某個資料表包含符合給定索引鍵／值查詢條件的記錄：

    $this->assertDatabaseHas('users', [
        'email' => 'sally@example.com',
    ]);
<a name="assert-database-missing"></a>

#### assertDatabaseMissing

判斷資料庫中的某個資料表是否不包含符合給定索引鍵／值查詢條件的記錄：

    $this->assertDatabaseMissing('users', [
        'email' => 'sally@example.com',
    ]);
<a name="assert-deleted"></a>

#### assertSoftDeleted

`assertSoftDeleted` 方法可用來判斷給定 Eloquent Model 是否已「軟刪除 (Soft Delete)」：

    $this->assertSoftDeleted($user);
<a name="assert-not-deleted"></a>

#### assertNotSoftDeleted

`assertNotSoftDeleted` 方法可用來判斷給定 Eloquent Model 是否未被「軟刪除 (Soft Delete)」：

    $this->assertNotSoftDeleted($user);
<a name="assert-model-exists"></a>

#### assertModelExists

判斷給定 Model 存在資料庫中：

    use App\Models\User;
    
    $user = User::factory()->create();
    
    $this->assertModelExists($user);
<a name="assert-model-missing"></a>

#### assertModelMissing

判斷給定 Model 不存在資料庫中：

    use App\Models\User;
    
    $user = User::factory()->create();
    
    $user->delete();
    
    $this->assertModelMissing($user);
<a name="expects-database-query-count"></a>

#### expectsDatabaseQueryCount

可以在測試的最開始呼叫 `expectsDatabaseQueryCount` 方法來指定在執行此測試的期間預期執行多少筆資料庫查詢。若實際執行的查詢數不符合該預期，測試就會失敗：

    $this->expectsDatabaseQueryCount(5);
    
    // Test...