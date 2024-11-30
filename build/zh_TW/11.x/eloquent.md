---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/61/en-zhtw'
updatedAt: '2024-06-30T08:26:00Z'
contributors: {  }
progress: 50.66
---

# Eloquent：入門

- [簡介](#introduction)
- [產生 Model 類別](#generating-model-classes)
- [Eloquent Model 慣例](#eloquent-model-conventions)
  - [資料表名稱](#table-names)
  - [主索引鍵 (Primary Key)](#primary-keys)
  - [UUID and ULID Keys](#uuid-and-ulid-keys)
  - [時戳](#timestamps)
  - [資料庫連線](#database-connections)
  - [預設的屬性值](#default-attribute-values)
  - [設定 Eloquent 的嚴格性](#configuring-eloquent-strictness)
  
- [取得 Model](#retrieving-models)
  - [Collection](#collections)
  - [將結果分段](#chunking-results)
  - [使用 Lazy Collection 來將查詢結果分段](#chunking-using-lazy-collections)
  - [指標](#cursors)
  - [進階子查詢](#advanced-subqueries)
  
- [取得單一 Model 或彙總的結果](#retrieving-single-models)
  - [Retrieving or Creating Models](#retrieving-or-creating-models)
  - [取得彙總值](#retrieving-aggregates)
  
- [Inserting and Updating Models](#inserting-and-updating-models)
  - [插入 - Insert](#inserts)
  - [更新 - Update](#updates)
  - [大量賦值 (Mass Assignment)](#mass-assignment)
  - [更新插入 - Upsert](#upserts)
  
- [刪除 Model](#deleting-models)
  - [軟刪除](#soft-deleting)
  - [查詢軟刪除的 Model](#querying-soft-deleted-models)
  
- [剪除 (Prune) Model](#pruning-models)
- [複製 (Replicate) Model](#replicating-models)
- [Query Scope](#query-scopes)
  - [全域的 Scope](#global-scopes)
  - [區域 Scopes](#local-scopes)
  
- [比較 Model](#comparing-models)
- [事件](#events)
  - [使用閉包](#events-using-closures)
  - [Observer](#observers)
  - [靜音事件](#muting-events)
  

<a name="introduction"></a>

## 簡介

Laravel 中包含了 Eloquent。Eloquent 是一個物件關聯對映 (ORM, Object-Relational Mapper)，能讓開發人員以更愉快的方式與資料庫互動。在使用 Eloquent 時，每個資料表都會有一個對應的「Model」，我們可以通過 Model 來使用資料表。除了從資料表中取得資料外，通過 Eloquent Model，我們還能進行插入、更新、與刪除的動作。

> [!NOTE]  
> 在開始之前，請先確定是否有在 `config/database.php` 設定檔中設定好資料庫連線。更多有關設定資料庫則資訊，請參考[資料庫設定說明文件](/docs/{{version}}/database#configuration)。

#### Laravel Bootcamp

如果你第一次接觸 Laravel，歡迎參考 [Laravel Bootcamp (英語)](https://bootcamp.laravel.com)。Laravel Bootcamp 會帶領你使用 Eloquent 來建立你的第一個 Laravel 專案。Laravel Bootcamp 是學習各種有關 Laravel 與 Eloquent 相關技術的好地方。

<a name="generating-model-classes"></a>

## 產生 Model 類別

我們先來建立 Eloquent Model。一般來說，Model 都放在 `app\Models` 目錄內，並繼承 `Illuminate\Database\Eloquent\Model` 類別。我們可以使用 `make:model` [Artisan 指令](/docs/{{version}}/artisan)來產生新 Model：

```shell
php artisan make:model Flight
```
若想在產生 Model 時一併產生[資料庫 Migration](/docs/{{version}}/migrations)，可使用 `--migration` 或 `-m` 選項：

```shell
php artisan make:model Flight --migration
```
在產生 Model 時，也能產生許多其他類型的類別，如 Factory、Seeder、Policy、Controller、Form Request⋯等。此外，我們也能組合多個選項來一次產生多個類別：

```shell
# Generate a model and a FlightFactory class...
php artisan make:model Flight --factory
php artisan make:model Flight -f

# Generate a model and a FlightSeeder class...
php artisan make:model Flight --seed
php artisan make:model Flight -s

# Generate a model and a FlightController class...
php artisan make:model Flight --controller
php artisan make:model Flight -c

# Generate a model, FlightController resource class, and form request classes...
php artisan make:model Flight --controller --resource --requests
php artisan make:model Flight -crR

# Generate a model and a FlightPolicy class...
php artisan make:model Flight --policy

# Generate a model and a migration, factory, seeder, and controller...
php artisan make:model Flight -mfsc

# Shortcut to generate a model, migration, factory, seeder, policy, controller, and form requests...
php artisan make:model Flight --all
php artisan make:model Flight -a

# Generate a pivot model...
php artisan make:model Member --pivot
php artisan make:model Member -p
```
<a name="inspecting-models"></a>

#### 檢查 Model

有時候，只看 Model 的程式碼很難瞭解到 Model 上所有可用的屬性。除了直接看程式碼外，也可以試試用 `model:show` Artisan 指令。使用該指令就可以方便地看到 Model 上所有的屬性與關聯：

```shell
php artisan model:show Flight
```
<a name="eloquent-model-conventions"></a>

## Eloquent Model 慣例

使用 `make:model` 指令產生的 Model 會被放在 `app/Models` 目錄中。我們來看看一個基礎的 Model 類別，並討論一些 Eloquent 的重要慣例：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        // ...
    }
<a name="table-names"></a>

### 資料表名稱

看一眼上方的範例後，讀者應該已經發現，我們並沒有告訴 Eloquent，我們的 `Flight` Model 要對應到哪個資料表。依照慣例，除非有特別指定，否則 Eloquent 會將類別名稱的複數形式改為「蛇行命名法 (snake_case)」來當作表名。因此，在這個例子中，Eloquent 會假設 `Flight` Model 將資料儲存在 `flights` 資料表中，而 `AirTrafficController` Model 則會儲存在 `air_traffic_controllers` 資料表中。

若你的 Model 對應的資料表不符合這個慣例，可以手動在 Model 上定義 `table` 屬性來指定 Model 的表名：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * The table associated with the model.
         *
         * @var string
         */
        protected $table = 'my_flights';
    }
<a name="primary-keys"></a>

### 主索引鍵 - Primary Key

Eloquent 會假設每個 Model 對應的資料表都有一個名為 `id` 的主索引鍵欄位。若有需要的話，可以在 Model 上定義一個 protected `$primaryKey` 屬性來指定不同的欄位作為 Model 的主索引鍵：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * The primary key associated with the table.
         *
         * @var string
         */
        protected $primaryKey = 'flight_id';
    }
此外，Eloquent 還會假設主索引鍵是一個遞增 (Incrementing) 的整數值。這表示，Eloquent 會自動將主索引鍵的型別轉換為整數 (Integer)。若想使用非遞增或非數字的主索引鍵，則應在 Model 上定義一個 public `$incrementing` 屬性，並將其值設為 `false`：

    <?php
    
    class Flight extends Model
    {
        /**
         * Indicates if the model's ID is auto-incrementing.
         *
         * @var bool
         */
        public $incrementing = false;
    }
若 Model 的主索引件不是正數，則應在 Model 上定義一個 protected `$keyType` 屬性。該屬性的值應為 `string`：

    <?php
    
    class Flight extends Model
    {
        /**
         * The data type of the primary key ID.
         *
         * @var string
         */
        protected $keyType = 'string';
    }
<a name="composite-primary-keys"></a>

#### 「組合式 (Composite)」主索引鍵

在 Eloquent 中，每個 Model 都必須要有至少一個不重複的識別用「ID」作為其主索引鍵。Eloquent Model 不支援「組合式」的主索引鍵。不過，除了不重複的識別用主索引鍵以外，你可以自由在資料表中新增額外的多欄位、不重複索引。

<a name="uuid-and-ulid-keys"></a>

### UUID and ULID Keys

除了使用 Auto-Increment 的整數作為 Eloquent Model 的主索引鍵外，也可以選擇使用 UUID。UUID 是 36 字元長、普遍 (Universally) 不重複的英數識別字 (Identifier)。

若要在 Model 上使用 UUID 而不使用 Auto-Increment 的整數索引鍵的話，可在 Model 中加上 `Illuminate\Database\Eloquent\Concerns\HasUuids` Trait。檔案，也請確保該 Model 中有[相等於 UUID 的主索引鍵欄位](/docs/{{version}}/migrations#column-method-uuid)：

    use Illuminate\Database\Eloquent\Concerns\HasUuids;
    use Illuminate\Database\Eloquent\Model;
    
    class Article extends Model
    {
        use HasUuids;
    
        // ...
    }
    
    $article = Article::create(['title' => 'Traveling to Europe']);
    
    $article->id; // "8f8e8478-9035-4d23-b9a7-62f4d2612ce5"
By default, The `HasUuids` trait will generate ["ordered" UUIDs](/docs/{{version}}/strings#method-str-ordered-uuid) for your models. These UUIDs are more efficient for indexed database storage because they can be sorted lexicographically.

只要在 Model 中定義一個 `newUniqueId` 方法，就可以複寫特定 Model 產生 UUID 的程式。此外，也可以在 Model 上定義一個 `uniqueIds` 方法來指定哪些欄位要接受 UUID：

    use Ramsey\Uuid\Uuid;
    
    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) Uuid::uuid4();
    }
    
    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id', 'discount_code'];
    }
若有需要的話，可以使用「ULID」來代替 UUID。ULID 與 UUID 類似。不過，ULID 只有 26 個字元長。與有序 UUID 類似，ULID 也可使用字典順序排列，適合用在資料庫索引上。若要使用 ULID，可以在 Model 中使用 `Illuminate\Database\Eloquent\Concerns\HasUlids` 屬性。也請確保該 Model 有 [ULID 相等的主索引鍵欄位](/docs/{{version}}/migrations#column-method-ulid)：

    use Illuminate\Database\Eloquent\Concerns\HasUlids;
    use Illuminate\Database\Eloquent\Model;
    
    class Article extends Model
    {
        use HasUlids;
    
        // ...
    }
    
    $article = Article::create(['title' => 'Traveling to Asia']);
    
    $article->id; // "01gd4d3tgrrfqeda94gdbtdk5c"
<a name="timestamps"></a>

### 時戳 - Timestamps

By default, Eloquent expects `created_at` and `updated_at` columns to exist on your model's corresponding database table.  Eloquent will automatically set these column's values when models are created or updated. If you do not want these columns to be automatically managed by Eloquent, you should define a `$timestamps` property on your model with a value of `false`:

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * Indicates if the model should be timestamped.
         *
         * @var bool
         */
        public $timestamps = false;
    }
若需要自訂 Model 時戳的格式，可在 Model 上設定 `$dateFormat` 屬性。這個屬性會用來決定儲存在資料庫中的日期格式，以及 Model 被序列化為陣列或 JSON 時使用的各式：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * The storage format of the model's date columns.
         *
         * @var string
         */
        protected $dateFormat = 'U';
    }
若有需要自訂用來儲存時戳的欄位名稱，可在 Model 上定義 `CREATED_AT` 與 `UPDATED_AT` 常數：

    <?php
    
    class Flight extends Model
    {
        const CREATED_AT = 'creation_date';
        const UPDATED_AT = 'updated_date';
    }
若想在不修改 Model 上 `updated_at` 時戳的情況下執行 Model 操作，可在傳入 `withoutTimestamps` 方法中的閉包內對該 Model 進行修改：

    Model::withoutTimestamps(fn () => $post->increment('reads'));
<a name="database-connections"></a>

### 資料庫連線

預設情況下，Eloquent Model 會使用專案設定的預設資料庫連線。若想為特定 Model 指定不同的資料庫連線，可在 Model 上定義一個 `$connection` 屬性：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * The database connection that should be used by the model.
         *
         * @var string
         */
        protected $connection = 'mysql';
    }
<a name="default-attribute-values"></a>

### 預設的屬性值

預設情況下，剛初始化的新 Model 實體不會包含任何屬性值。若想為 Model 中的某些屬性定義預設值，請在 Model 中定義一個 `$attributes` 屬性。放在 `$attributes` 陣列中的屬性值應為其原始的、「可保存的」格式，即這些值被從資料庫中讀出來時的格式：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * The model's default values for attributes.
         *
         * @var array
         */
        protected $attributes = [
            'options' => '[]',
            'delayed' => false,
        ];
    }
<a name="configuring-eloquent-strictness"></a>

### 設定 Eloquent 的嚴格性

Laravel 提供了能為各種狀況設定 Eloquent 行為與「嚴格性」的各種方法：

首先，`preventLazyLoading` 方法接受一個可選的布林引數，用來判斷是否要阻止 Lazy Loading。舉例來說，我們可以只在非正式環境下禁用 Lazy Loading。這樣一來，即使正式環境中不小心包含了會造成 Lazy Loading 的程式碼，正式環境也可以正常運作。一般來說，這個方法應在專案的 `AppServiceProvider` 中 `boot` 方法內執行：

```php
use Illuminate\Database\Eloquent\Model;

/**
 * Bootstrap any application services.
 */
public function boot(): void
{
    Model::preventLazyLoading(! $this->app->isProduction());
}
```
此外，也可以呼叫 `preventSilentlyDiscardingAttributes ` 來讓 Laravel 在嘗試填入不可填入的屬性時擲回 Exception。這樣一來就可防止在本機開發環境下若嘗試設定某個未在 Model `fillable` 陣列中的屬性產生未逾期的錯誤：

```php
Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
```
<a name="retrieving-models"></a>

## 取得 Model

Once you have created a model and [its associated database table](/docs/{{version}}/migrations#generating-migrations), you are ready to start retrieving data from your database. You can think of each Eloquent model as a powerful [query builder](/docs/{{version}}/queries) allowing you to fluently query the database table associated with the model. The model's `all` method will retrieve all of the records from the model's associated database table:

    use App\Models\Flight;
    
    foreach (Flight::all() as $flight) {
        echo $flight->name;
    }
<a name="building-queries"></a>

#### 建立查詢

Eloquent 的 `all` 方法會回傳 Model 資料表中的所有紀錄。不過，由於每個 Eloquent Model 也都有 [Query Builder](/docs/{{version}}/queries) 的功能，因此你可以隨意加上額外的查詢條件，然後再叫用 `get` 方法來取得結果：

    $flights = Flight::where('active', 1)
                   ->orderBy('name')
                   ->take(10)
                   ->get();
> [!NOTE]  
> 由於 Eloquent Model 是 Query Builder，因此讀者應先看看 Laravel 的 [Query Builder](/docs/{{version}}/queries) 中所提供的方法。在撰寫 Eloquent 查詢時，可以使用這些方法中所有的方法。

<a name="refreshing-models"></a>

#### 重新整理 Model

若已經從資料庫中取得 Eloquent Model 的實體，則可以使用 `fresh` 與 `refresh` 方法來「重新整理」Model。`fresh` 方法會從資料庫中重新取得 Model。現有的 Model 實體將不受影響：

    $flight = Flight::where('number', 'FR 900')->first();
    
    $freshFlight = $flight->fresh();
`refresh` 方法會使用資料庫中最新的資料庫來重新填寫現有的 Model。此外，該 Model 中所有已載入的關聯也會被重新載入：

    $flight = Flight::where('number', 'FR 900')->first();
    
    $flight->number = 'FR 456';
    
    $flight->refresh();
    
    $flight->number; // "FR 900"
<a name="collections"></a>

### Collection

如同我們看到的，`all` 或 `get` 等 Eloquent 方法會從資料庫中取得多筆紀錄。不過，這些方法並不是回傳一般的 PHP 陣列，而是回傳一個 `Illuminate\Database\Eloquent\Collection` 的實體。

Eloquent 的 `Collection` 類別繼承自 Laravel 的基礎 `Illuminate\Support\Collection` 類別。對於要與一組資料互動，這個類別提供了[許多實用的方法](/docs/{{version}}/collections#available-methods)。舉例來說，使用 `reject` 方法，可以按照閉包的叫用結果來將一些 Model 從 Collection 中移除：

```php
$flights = Flight::where('destination', 'Paris')->get();

$flights = $flights->reject(function (Flight $flight) {
    return $flight->cancelled;
});
```
除了 Laravel 的基礎 Collection 類別中提供的方法外，為了與包含 Eloquent Model 的 Collection
互動，Eloquent Collection 也特別提供了[一些額外的方法](/docs/{{version}}/eloquent-collections#available-methods)。

由於所有的 Laravel Collection 都實作了 PHP 的 Iterable 介面，因此我們可以把 Collection 當作陣列一樣迭代：

```php
foreach ($flights as $flight) {
    echo $flight->name;
}
```
<a name="chunking-results"></a>

### 將查詢結果分段

如果嘗試使用 `all` 或 `get` 方法來取得幾萬筆 Eloquent 紀錄，那麼你的程式可能會記憶體不足。除了使用這些方法外，可以使用 `chunk` 方法來更有效率地處理大量的 Model：

`chunk` 方法會取得一部分的 Eloquent Model，然後將這些 Model 傳入用於處理的閉包中。由於一次只會取得目前這個部分的 Eloquent Model，因此在處理大量 Model 時，`chunk` 方法會顯著地降低記憶體的使用：

```php
use App\Models\Flight;
use Illuminate\Database\Eloquent\Collection;

Flight::chunk(200, function (Collection $flights) {
    foreach ($flights as $flight) {
        // ...
    }
});
```
傳給 `chunk` 方法的第一個引數代表每個「Chunk (分段)」要取得的紀錄。每次從資料庫中取得一組片段後，就會叫用作為第二個引數傳入的閉包。每取得一組傳入閉包的片段，就會執行一次資料庫查詢。

若使用 `chunk` 方法時有過濾資料，在迭代結果的時候會更新這個欄位，則應使用 `chunkById` 方法。若在這種情況下使用 `chunk` 可能會取得未預期的結果或是不一致的結果。在 `chunkById` 方法內部，會取得 `id` 欄位值大於前一個分段中最後一個 Model 的 Model：

```php
Flight::where('departed', true)
    ->chunkById(200, function (Collection $flights) {
        $flights->each->update(['departed' => false]);
    }, column: 'id');
```
Since the `chunkById` and `lazyById` methods add their own "where" conditions to the query being executed, you should typically [logically group](/docs/{{version}}/queries#logical-grouping) your own conditions within a closure:

```php
Flight::where(function ($query) {
    $query->where('delayed', true)->orWhere('cancelled', true);
})->chunkById(200, function (Collection $flights) {
    $flights->each->update([
        'departed' => false,
        'cancelled' => true
    ]);
}, column: 'id');
```
<a name="chunking-using-lazy-collections"></a>

### 使用 Lazy Collection 來將查詢結果分段

`lazy` 方法與 [`chunk` 方法](#chunking-results) 的原理類似，都是以分段的方式執行查詢。不過，`lazy` 方法不是直接把每個分段傳入回呼中，而是回傳一個包含 Eloquent Model 的扁平 (Flattened) [`LazyCollection`](/docs/{{version}}/collections#lazy-collections)，使用這個 LazyCollection，就可以以單一資料流的方式與查詢結果互動：

```php
use App\Models\Flight;

foreach (Flight::lazy() as $flight) {
    // ...
}
```
若使用 `lazy` 方法時有過濾資料，在迭代結果的時候會更新這個欄位，則應使用 `lazyById` 方法。在 `lazyById` 方法內部，會取得 `id` 欄位值大於前一個分段中最後一個 Model 的 Model：

```php
Flight::where('departed', true)
    ->lazyById(200, column: 'id')
    ->each->update(['departed' => false]);
```
可以使用 `lazyByIdDesc` 以依據 `id` 的降冪排序來過濾查詢結果。

<a name="cursors"></a>

### 指標 - Cursor

與 `lazy` 方法類似，`cursor` 方法也可用來在疊檯數千筆 Eloquent Model 時顯著降低程式的記憶體使用量。

`cursor` 方法只會執行一筆資料庫查詢。不過，直到個別 Eloquent Model 被迭代到以前，這些 Model 都不會被解凍 (Hydrated)。因此，Cursor 的每次迭代時，記憶體內一次都只會有一個 Eloquent Model。

> [!WARNING]  
> 由於 `cursor` 方法一次只會將一個 Eloquent Model 放在記憶體內，因此我們沒有辦法對關聯做積極式載入。若想積極式載入關聯，請考慮使用 [`lazy` 方法](#chunking-using-lazy-collections) 代替。

`cursor` 方法在其內部使用了 PHP 的 [Generator](https://www.php.net/manual/en/language.generators.overview.php) 來實作此功能：

```php
use App\Models\Flight;

foreach (Flight::where('destination', 'Zurich')->cursor() as $flight) {
    // ...
}
```
`cursor` 會回傳一個 `Illuminate\Support\LazyCollection` 實體。使用 [Lazy collection](/docs/{{version}}/collections#lazy-collections)，能讓我們使用許多一般 Laravel Collection 中的方法，但一次只需要將一筆 Model 載入記憶體即可：

```php
use App\Models\User;

$users = User::cursor()->filter(function (User $user) {
    return $user->id > 500;
});

foreach ($users as $user) {
    echo $user->id;
}
```
雖然 `cursor` 方法比起一般查詢使用較少記憶體 (因為記憶體內一次只會有一筆 Eloquent Model)，但最終還是由可能會記憶體不足。這是[因為 PHP 的 PDO Driver 會自動在內部將所有的查詢結果都快取在其緩衝區 (Buffer) 上](https://www.php.net/manual/en/mysqlinfo.concepts.buffering.php)。所以，若要處理非常大量的 Eloquent 紀錄，請考慮使用 [`lazy` 方法]#chunking-using-lazy-collections)替代。

<a name="advanced-subqueries"></a>

### 進階子查詢

<a name="subquery-selects"></a>

#### 子查詢 Select

Eloquent 也提供了進階子查詢的支援，能讓你在單一查詢內從其他相關的資料表中取得資料。舉例來說，假設我們有張班機目的地的 `destinations` 資料表，以及一張由前往該目的地班機的 `flights` 資料表。`flights` 資料表中包含了顯示班機抵達目的地時間的 `arrived_at` 欄位。

使用 Query Builder 的 `select` 與 `addSelect` 方法中的子查詢功能，我們就能使用單一查詢來選擇所有目的地 `destinations` 以及最近抵達該目的地的航班名稱：

    use App\Models\Destination;
    use App\Models\Flight;
    
    return Destination::addSelect(['last_flight' => Flight::select('name')
        ->whereColumn('destination_id', 'destinations.id')
        ->orderByDesc('arrived_at')
        ->limit(1)
    ])->get();
<a name="subquery-ordering"></a>

#### 子查詢排序

此外，Query Builder 的 `orderBy` 功能也支援子查詢。繼續使用剛才的航班範例，我們可以使用這個功能來按照最後班機抵達目的地的時間來為所有目的地進行排序。同樣，我們只需要單一資料庫查詢就可以完成：

    return Destination::orderByDesc(
        Flight::select('arrived_at')
            ->whereColumn('destination_id', 'destinations.id')
            ->orderByDesc('arrived_at')
            ->limit(1)
    )->get();
<a name="retrieving-single-models"></a>

## 取得單一 Model 或彙總

除了取得所有符合給定查詢的紀錄外，我們也可以使用 `find`, `first`, 或 `firstWhere` 方法來取得單一紀錄。這些方法不會回傳一組包含 Model 的 Collection，而只會回傳單一 Model：

    use App\Models\Flight;
    
    // Retrieve a model by its primary key...
    $flight = Flight::find(1);
    
    // Retrieve the first model matching the query constraints...
    $flight = Flight::where('active', 1)->first();
    
    // Alternative to retrieving the first model matching the query constraints...
    $flight = Flight::firstWhere('active', 1);
有時候，我們可能會想在查詢無結果時執行其他動作。`findOr` 與 `firstOr` 方法會回傳單一 Model 實體，並在找不到結果時執行給定的閉包。該閉包回傳的值會被當作該方法的結果回傳：

    $flight = Flight::findOr(1, function () {
        // ...
    });
    
    $flight = Flight::where('legs', '>', 3)->firstOr(function () {
        // ...
    });
<a name="not-found-exceptions"></a>

#### 找不到的例外

有時候，我們可能會想在找不到 Model 時擲回一個例外。這種行為特別適用於路由或 Controller 中。`findOrFail` 或 `firstOrFail` 方法會取得查詢的第一筆結果。不過，若找不到結果，會擲回 `Illuminate\Database\Eloquent\ModelNotFoundException`：

    $flight = Flight::findOrFail(1);
    
    $flight = Flight::where('legs', '>', 3)->firstOrFail();
若未攔截 (Catch) `ModelNotFoundException`，則會自動回傳 404 HTTP 回應給用戶端：

    use App\Models\Flight;
    
    Route::get('/api/flights/{id}', function (string $id) {
        return Flight::findOrFail($id);
    });
<a name="retrieving-or-creating-models"></a>

### Retrieving or Creating Models

The `firstOrCreate` method will attempt to locate a database record using the given column / value pairs. If the model cannot be found in the database, a record will be inserted with the attributes resulting from merging the first array argument with the optional second array argument:

`firstOrNew` 方法與 `firstOrCreate` 方法類似，會嘗試在資料庫中尋找符合給定屬性的紀錄。不過，若找不到 Model，則會回傳新的 Model 實體。請注意，`firstOrNew` 回傳的 Model 還未被儲存在資料庫中，應手動呼叫 `save` 方法來保存：

    use App\Models\Flight;
    
    // Retrieve flight by name or create it if it doesn't exist...
    $flight = Flight::firstOrCreate([
        'name' => 'London to Paris'
    ]);
    
    // Retrieve flight by name or create it with the name, delayed, and arrival_time attributes...
    $flight = Flight::firstOrCreate(
        ['name' => 'London to Paris'],
        ['delayed' => 1, 'arrival_time' => '11:30']
    );
    
    // Retrieve flight by name or instantiate a new Flight instance...
    $flight = Flight::firstOrNew([
        'name' => 'London to Paris'
    ]);
    
    // Retrieve flight by name or instantiate with the name, delayed, and arrival_time attributes...
    $flight = Flight::firstOrNew(
        ['name' => 'Tokyo to Sydney'],
        ['delayed' => 1, 'arrival_time' => '11:30']
    );
<a name="retrieving-aggregates"></a>

### 取得彙總值 (Aggregate)

在與 Eloquent Model 互動時，我們也可以使用 `count`、`sum`、`max`⋯等其他由 Laravel [Query Builder](/docs/{{version}}/queries) 提供的[彙總方法](/docs/{{version}}/queries#aggregates)。如同讀者預期的一樣，這些方法會回傳純量值 (Scalar Value)，而非 Eloquent Model 實體：

    $count = Flight::where('active', 1)->count();
    
    $max = Flight::where('active', 1)->max('price');
<a name="inserting-and-updating-models"></a>

## Inserting and Updating Models

<a name="inserts"></a>

### 插入 - Insert

當然，在使用 Eloquent 時，我們的需求不只有從資料庫中取得資料，我們也需要能插入新紀錄。所幸，在 Eloquent 中要插入資料非常簡單。要將新紀錄插入資料庫，請初始化一個新的 Model 實體，並在 Model 上設定屬性。然後，在 Model 實體上呼叫 `save` 方法：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\Flight;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    
    class FlightController extends Controller
    {
        /**
         * Store a new flight in the database.
         */
        public function store(Request $request): RedirectResponse
        {
            // Validate the request...
    
            $flight = new Flight;
    
            $flight->name = $request->name;
    
            $flight->save();
    
            return redirect('/flights');
        }
    }
在這個範例中，我們將來自連入 HTTP 請求的 `name` 欄位賦值給 `App\Models\Flight` Model 實體的 `name` 屬性。呼叫 `save` 方法後，就會將一筆紀錄插入到資料庫中。在呼叫 `save` 方法時，會自動設定 `created_at` 與 `updated_at` 時戳，因此我們不需要手動設定這些欄位。

或者，我們也可以使用 `create` 來通過單一 PHP 陳述式「保存」新 Model。`create` 方法會回傳插入的 Model 實體：

    use App\Models\Flight;
    
    $flight = Flight::create([
        'name' => 'London to Paris',
    ]);
不過，在使用 `create` 方法時，應在 Model 類別上設定好 `fillable` 或 `guarded` 屬性。由於所有的 Eloquent Model 預設都有針對大量賦值弱點 (Mass Assignment Vulnerability) 進行保護，因此必須設定這些屬性。要瞭解更多有關大量賦值的資訊，請參考[大量賦值說明文件](#mass-assignment)。

<a name="updates"></a>

### 更新 - Update

`save` 方法也可用來更新已存在於資料庫的 Model。要更新 Model，應先取得這個 Model，然後設定要更新的屬性。接著，呼叫 Model 的 `save` 方法。同樣，`updated_at` 時戳會自動更新，無須手動設定：

    use App\Models\Flight;
    
    $flight = Flight::find(1);
    
    $flight->name = 'Paris to London';
    
    $flight->save();
有時候，我們可能需要更新現有的 Model，或是當沒有符合的 Model 存在時建立一個新的 Model。與 `firstOrCreate` 方法類似，`updateOrCreate` 方法可將 Model 保存在資料庫中，因此我們不需手動呼叫 `save` 方法。

在下方的範例中，若有 `depature` 位置為 `Oakland` 且 `destination` 位置為 `San Diego` 的航班，會更新其 `price` 與 `discounted` 欄位。若沒有找到符合的航班，則會將第一個引數的陣列與第二個引數的陣列合併，並用來建立一個新的航班：

    $flight = Flight::updateOrCreate(
        ['departure' => 'Oakland', 'destination' => 'San Diego'],
        ['price' => 99, 'discounted' => 1]
    );
<a name="mass-updates"></a>

#### 批次更新

也可以更新符合給定查詢的 Model。在這個範例中，設為 `active` 且 `destination` 為 `San Diego` 的航班會被標記為誤點 (Delayed)：

    Flight::where('active', 1)
          ->where('destination', 'San Diego')
          ->update(['delayed' => 1]);
`update` 方法預期一組包含欄位與值的陣列，用來代表要更新的欄位。`update` 方法會回傳受影響的行數。

> [!WARNING]  
> 在使用 Eloquent 進行批次更新時，將不會引發 `saving`、`saved`、`updating`、`updated` 等 Model 事件。這是因為，在批次更新時並不會實際取得這些 Model。

<a name="examining-attribute-changes"></a>

#### 檢驗屬性的更改

Eloquent 提供了 `isDirty`、`isClean`、`wasChanged` 等方法，用以檢驗 Model 的內部狀態，並判斷自取得 Model 以來其屬性的更改。

`isDirty` 方法判斷自取得 Model 以來，Model 中是否有任何的屬性經過修改。可以傳入一個屬性名稱或是一組屬性陣列給 `isDirty` 方法來判斷這些屬性是否有被更改 (Dirty)。`isClean` 方法則用來判斷某個屬性是否從取得 Model 以來都沒有被更改過。這個方法同樣也接受一個可選的屬性引數：

    use App\Models\User;
    
    $user = User::create([
        'first_name' => 'Taylor',
        'last_name' => 'Otwell',
        'title' => 'Developer',
    ]);
    
    $user->title = 'Painter';
    
    $user->isDirty(); // true
    $user->isDirty('title'); // true
    $user->isDirty('first_name'); // false
    $user->isDirty(['first_name', 'title']); // true
    
    $user->isClean(); // false
    $user->isClean('title'); // false
    $user->isClean('first_name'); // true
    $user->isClean(['first_name', 'title']); // false
    
    $user->save();
    
    $user->isDirty(); // false
    $user->isClean(); // true
`wasChanged` 方法用來判斷在目前的請求週期內，自動上次保存 Model 後，是否有任何屬性經過修改。若有需要的話，也可以傳入一個屬性名稱來判斷某個特定的屬性是否經過修改：

    $user = User::create([
        'first_name' => 'Taylor',
        'last_name' => 'Otwell',
        'title' => 'Developer',
    ]);
    
    $user->title = 'Painter';
    
    $user->save();
    
    $user->wasChanged(); // true
    $user->wasChanged('title'); // true
    $user->wasChanged(['title', 'slug']); // true
    $user->wasChanged('first_name'); // false
    $user->wasChanged(['first_name', 'title']); // true
`getOriginal` 方法則回傳一個包含 Model 原始屬性的陣列，無論取得 Model 後是否有進行任何修改。若有需要，我們也可以傳入一個屬性名稱來取得某個特定屬性的原始值：

    $user = User::find(1);
    
    $user->name; // John
    $user->email; // john@example.com
    
    $user->name = "Jack";
    $user->name; // Jack
    
    $user->getOriginal('name'); // John
    $user->getOriginal(); // Array of original attributes...
<a name="mass-assignment"></a>

### 大量賦值

我們也可以使用 `create` 來通過單一 PHP 陳述式「保存」新 Model。`create` 方法會回傳插入的 Model 實體：

    use App\Models\Flight;
    
    $flight = Flight::create([
        'name' => 'London to Paris',
    ]);
不過，在使用 `create` 方法時，應在 Model 類別上設定好 `fillable` 或 `guarded` 屬性。由於所有的 Eloquent Model 預設都有針對大量賦值弱點 (Mass Assignment Vulnerability) 進行保護，因此必須設定這些屬性。

當使用者傳入一個未預期的 HTTP 請求欄位，且該欄位會更改開發人員未預期的資料庫欄位時，就會導致大量賦值弱點。舉例來說，惡意使用者可能會通過 HTTP 請求傳入一個 `is_admin` 屬性，而該屬性可能會傳入 Model 的 `create` 方法，進一步導致使用者能自行將自己的權限提升為管理員。

因此，要開始使用大量賦值，應先定義哪些 Model 屬性能被大量複製。可以使用 Model 上的 `$fillable` 屬性來達成。舉例來說，我們來設定讓 `Flight` Model 的 `name` 屬性可被大量複製：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Flight extends Model
    {
        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = ['name'];
    }
指定好哪些屬性能被大量賦值後，就可以使用 `create` 方法來插入新紀錄到資料庫中。`create` 方法會回傳新建立的 Model 實體：

    $flight = Flight::create(['name' => 'London to Paris']);
若已有 Model 實體，則可以使用 `fill` 方法來以一組包含屬性的陣列來修改這個 Model 實體：

    $flight->fill(['name' => 'Amsterdam to Frankfurt']);
<a name="mass-assignment-json-columns"></a>

#### Mass Assignment and JSON Columns

在為 JSON 欄位賦值時，應在 Model 的 `$fillable` 陣列中指定所有可大量複製的欄位。基於安全性考量，Laravel 並不支援在使用 `guarded` 屬性時更新巢狀 JSON 屬性：

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'options->enabled',
    ];
<a name="allowing-mass-assignment"></a>

#### 允許大量賦值

若想讓所有的屬性都可被大量賦值，則可將 `$guarded` 屬性設為空真理。若要取消保護 Model，則應特別注意，且應只將手動設定的陣列傳給 Eloquent 的 `fill`、`create`、`update` 等方法：

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];
<a name="mass-assignment-exceptions"></a>

#### 大量賦值的 Exception

預設情況下，未包含在 `$fillable` 陣列中的值若出現在大量賦值操作時，會自動被忽略。在正式環境中，此行為是符合預期的。不過，在本機開發環境中，這個行為可能會造成開發人員的困擾，開發人員可能無法輕易找出 Model 更改未生效的原因。

If you wish, you may instruct Laravel to throw an exception when attempting to fill an unfillable attribute by invoking the `preventSilentlyDiscardingAttributes` method. Typically, this method should be invoked in the `boot` method of your application's `AppServiceProvider` class:

    use Illuminate\Database\Eloquent\Model;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
    }
<a name="upserts"></a>

### 更新插入 - Upsert

Eloquent 的 `upsert` 方法可用來以 ^[Atomic](%E4%B8%8D%E5%8F%AF%E9%83%A8%E5%88%86%E5%AE%8C%E6%88%90) 的單一查詢來更新或建立紀錄。該方法的第一個引數為用來插入或更新的值，而第二個引數則列出用來在相關資料表上識別出紀錄唯一性的欄位。該方法的第三個與最後一個引數是一組包含欄位的陣列，這些欄位是在資料庫中有相符紀錄時會更新的欄位。若 Model 上有啟用時戳，則 `upsert` 方法會自動設定 `created_at` 與 `updated_at` 時戳：

    Flight::upsert([
        ['departure' => 'Oakland', 'destination' => 'San Diego', 'price' => 99],
        ['departure' => 'Chicago', 'destination' => 'New York', 'price' => 150]
    ], uniqueBy: ['departure', 'destination'], update: ['price']);
> [!WARNING]  
> All databases except SQL Server require the columns in the second argument of the `upsert` method to have a "primary" or "unique" index. In addition, the MariaDB and MySQL database drivers ignore the second argument of the `upsert` method and always use the "primary" and "unique" indexes of the table to detect existing records.

<a name="deleting-models"></a>

## 刪除 Model

若要刪除 Model，則可以在 Model 實體上呼叫 `delete` 方法：

    use App\Models\Flight;
    
    $flight = Flight::find(1);
    
    $flight->delete();
可以呼叫 `truncate` 方法來刪除與 Model 相關的所有資料庫紀錄。`truncate` 行動也會重設該 Model 資料表上所有的自動遞增 (Auto-Incrementing) ID 欄位：

    Flight::truncate();
<a name="deleting-an-existing-model-by-its-primary-key"></a>

#### Deleting an Existing Model by its Primary Key

In the example above, we are retrieving the model from the database before calling the `delete` method. However, if you know the primary key of the model, you may delete the model without explicitly retrieving it by calling the `destroy` method.  In addition to accepting the single primary key, the `destroy` method will accept multiple primary keys, an array of primary keys, or a [collection](/docs/{{version}}/collections) of primary keys:

    Flight::destroy(1);
    
    Flight::destroy(1, 2, 3);
    
    Flight::destroy([1, 2, 3]);
    
    Flight::destroy(collect([1, 2, 3]));
If you are utilizing [soft deleting models](#soft-deleting), you may permanently delete models via the `forceDestroy` method:

    Flight::forceDestroy(1);
> [!WARNING]  
> `destroy` 方法會先載入個別 Model，然後再呼叫其 `delete` 方法。因此，每個 Model 的 `deleting` 與 `deleted` 事件都會被正確分派。

<a name="deleting-models-using-queries"></a>

#### 使用查詢來刪除 Model

當然，我們也可以建立一個 Eloquent 查詢來刪除所有符合查詢條件的 Model。在這個範例中，我們會刪除所有被標記為 Inactive 的航班。與批次更新類似，批次刪除也不會為要刪除的 Model 分派 Model 事件：

    $deleted = Flight::where('active', 0)->delete();
> [!WARNING]  
> 使用 Eloquent 執行批次刪除時，將不會為被刪除的 Model 指派 `deleting` 與 `deleted` Model 事件。這是因為在執行刪除陳述式時，我們並不會真的取得這些 Model。

<a name="soft-deleting"></a>

### 軟刪除

除了從資料庫中真正將資料刪除外，Eloquent 也可以「軟刪除」Model。當 Model 被軟刪除後，這些資料並不會真的被從資料庫內移除，而是會在 Model 上設定一個 `deleted_at` 屬性，代表 Model 被「刪除」的日期與時間。若要為 Model 啟用軟刪除，請將 `Illuminate\Database\Eloquent\SoftDeletes` Trait 加到 Model 上：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    
    class Flight extends Model
    {
        use SoftDeletes;
    }
> [!NOTE]  
> `SoftDeletes` Trait 會自動幫你將 `deleted_at` 屬性型別轉換為 `DateTime` 或 `Carbon`。

也應將 `deleted_at` 欄位加到資料表上。Laravel 的 [Schema Builder](/docs/{{version}}/migrations) 中有一個用來建立該欄位的輔助方法：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('flights', function (Blueprint $table) {
        $table->softDeletes();
    });
    
    Schema::table('flights', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
現在，在 Model 上呼叫 `delete` 方法時，會自動將 `deleted_at` 欄位的值設為目前的日期與時間，而 Model 的資料庫紀錄將保留在資料表內。查詢有軟刪除的 Model 時，查詢結果中將自動排除所有被軟刪除的 Model。

若要判斷某個 Model 實體是否已被軟刪除，可以使用 `trashed` 方法：

    if ($flight->trashed()) {
        // ...
    }
<a name="restoring-soft-deleted-models"></a>

#### 恢復軟刪除的 Model

有時候，我們會想「取消刪除」某個軟刪除的 Model。若要恢復軟刪除的 Model，可以在 Model 實體上呼叫 `restore` 方法。`restore` 方法會將 `deleted_at` 欄位設為 `null`：

    $flight->restore();
可以在查詢中使用 `restore` 方法來恢復多個 Model。跟其他「批次」行動一樣，這個方法並不會為恢復的 Model 分派任何 Model 事件：

    Flight::withTrashed()
            ->where('airline_id', 1)
            ->restore();
在建立[關聯](/docs/{{version}}/eloquent-relationships)查詢時，也可以使用 `restore` 方法：

    $flight->history()->restore();
<a name="permanently-deleting-models"></a>

#### 永久刪除 Model

有時候，我們會想將某個 Model 真正地從資料庫中刪除。可以使用 `forceDelete` 方法來將某個軟刪除的 Model 從資料表中永久移除：

    $flight->forceDelete();
建立 Eloquent 關聯查詢時也可以使用 `forceDelete` 方法：

    $flight->history()->forceDelete();
<a name="querying-soft-deleted-models"></a>

### 查詢軟刪除的 Model

<a name="including-soft-deleted-models"></a>

#### 包含軟刪除的 Model

前面也提到過，查詢結果中會自動排除已軟刪除的 Model。不過，我們通過在查詢上呼叫 `withTrashed` 來強制將已軟刪除的 Model 包含在查詢結果中：

    use App\Models\Flight;
    
    $flights = Flight::withTrashed()
                    ->where('account_id', 1)
                    ->get();
在建立[關聯](/docs/{{version}}/eloquent-relationships)查詢時，也可以呼叫 `withTrashed` 方法：

    $flight->history()->withTrashed()->get();
<a name="retrieving-only-soft-deleted-models"></a>

#### 只取得被軟刪除的 Model

`onlyTrashed` 方法 **只會** 取得被軟刪除的 Model：

    $flights = Flight::onlyTrashed()
                    ->where('airline_id', 1)
                    ->get();
<a name="pruning-models"></a>

## 修剪 (Prune) Model

有時候，我們可能會想定期刪除未使用的 Model。為此，我們可以使將 `Illuminate\Database\Eloquent\Prunable` 或 `Illuminate\Database\Eloquent\MassPrunable` Trait 加到要定期修剪的 Model 上。將其中一個 Trait 加到 Model
上後，請實作一個會回傳 Eloquent Query Builder 的 `prunable` 方法。這個 Query Builder 應解析出不再需要的 Model：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Prunable;
    
    class Flight extends Model
    {
        use Prunable;
    
        /**
         * Get the prunable model query.
         */
        public function prunable(): Builder
        {
            return static::where('created_at', '<=', now()->subMonth());
        }
    }
將 Model 標記為 `Prunable` 後，也可以在 Model 上定義一個 `pruning` 方法。這個方法會在 Model 被刪除後呼叫。該方法適用於想在 Model 被從資料庫內永久刪除前先刪除與這個 Model 相關的資源（如已保存的檔案等）時：

    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
        // ...
    }
After configuring your prunable model, you should schedule the `model:prune` Artisan command in your application's `routes/console.php` file. You are free to choose the appropriate interval at which this command should be run:

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('model:prune')->daily();
在 `model:prune` 中，會自動在專案 `app/Models` 目錄內的「Prunable」Model。若 Model 放在不同的位置，可使用 `--model` 選項來指定 Model 的類別名稱：

    Schedule::command('model:prune', [
        '--model' => [Address::class, Flight::class],
    ])->daily();
若想排除一些 Model 不被修剪，只修剪其他 Model 的話，可以使用 `--except` 選項：

    Schedule::command('model:prune', [
        '--except' => [Address::class, Flight::class],
    ])->daily();
可以通過以 `--pretend` 選項執行 `model:prune` 指令來測試 `prunable` 查詢。在模擬修剪時，`model:prune` 指令只會回報如果真的執行的時候，有多少筆紀錄會被刪除：

```shell
php artisan model:prune --pretend
```
> [!WARNING]  
> 若軟刪除的 Model 符合修剪查詢地條件，則會被永久性地刪除 (`forceDelete`)。

<a name="mass-pruning"></a>

#### 大量修剪

如果 Model 被 `Illuminate\Database\Eloquent\MassPrunable` Trait 標記，則這些 Model 會使用批次刪除查詢來從資料庫裡刪除。因此，將不會叫用 `pruning` 方法，也不會分派 `deleting` 與 `deleted` Model 事件。這是因為，在刪除前我們不會真的把 Model 抓回來，也因此整個修剪的過程會更有效率一點：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\MassPrunable;
    
    class Flight extends Model
    {
        use MassPrunable;
    
        /**
         * Get the prunable model query.
         */
        public function prunable(): Builder
        {
            return static::where('created_at', '<=', now()->subMonth());
        }
    }
<a name="replicating-models"></a>

## 複製 Model

我們可以使用 `replicate` 方法來為現有的 Model 實體建立一個未保存的拷貝。這個方法特別適用於當有多個 Model 實體共享了許多相同屬性的時候：

    use App\Models\Address;
    
    $shipping = Address::create([
        'type' => 'shipping',
        'line_1' => '123 Example Street',
        'city' => 'Victorville',
        'state' => 'CA',
        'postcode' => '90001',
    ]);
    
    $billing = $shipping->replicate()->fill([
        'type' => 'billing'
    ]);
    
    $billing->save();
若要將一個或多個屬性從複製出來的新 Model 中移除，可以傳入一個陣列給 `replicate` 方法：

    $flight = Flight::create([
        'destination' => 'LAX',
        'origin' => 'LHR',
        'last_flown' => '2020-03-04 11:00:00',
        'last_pilot_id' => 747,
    ]);
    
    $flight = $flight->replicate([
        'last_flown',
        'last_pilot_id'
    ]);
<a name="query-scopes"></a>

## 查詢 Scope

<a name="global-scopes"></a>

### 全域 Scope

使用全域 Scope，就可以將某個查詢條件套用到給定 Model 的所有查詢上。Laravel 本身的[軟刪除](#soft-deleting)功能就使用了全域 Scope 來只從資料庫中取得「非刪除」的 Model。撰寫你自己的全域 Scope，就可以方便、簡單地確保給定 Model 中的所有查詢都有相同的查詢條件。

<a name="generating-scopes"></a>

#### 產生 Scope

若要產生新的全域 Scope，可以呼叫 `make:scope` Artisan 指令。該指令會在專案的 `app/Models/Scopes` 目錄下產生 Scope：

```shell
php artisan make:scope AncientScope
```
<a name="writing-global-scopes"></a>

#### 撰寫全域 Scope

撰寫全域 Scope 很簡單。首先，使用 `make:scope` 指令來產生一個實作了 `Illuminate\Database\Eloquent\Scope` 介面的類別。`Scope` 介面要求要實作一個方法：`apply`。`apply` 方法可用來在 Query 上依照需求加入 `where` 條件限制或其他類型的子句：

    <?php
    
    namespace App\Models\Scopes;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Scope;
    
    class AncientScope implements Scope
    {
        /**
         * Apply the scope to a given Eloquent query builder.
         */
        public function apply(Builder $builder, Model $model): void
        {
            $builder->where('created_at', '<', now()->subYears(2000));
        }
    }
> [!NOTE]  
> 若你的全域 Scope 由將欄位加到查詢的 Select 子句中，請使用 `addSelect` 而不是 `select`。這樣可以避免我們不小心把查詢上原本就有的 Select 子句覆蓋掉。.

<a name="applying-global-scopes"></a>

#### 套用全域 Scope

To assign a global scope to a model, you may simply place the `ScopedBy` attribute on the model:

    <?php
    
    namespace App\Models;
    
    use App\Models\Scopes\AncientScope;
    use Illuminate\Database\Eloquent\Attributes\ScopedBy;
    
    #[ScopedBy([AncientScope::class])]
    class User extends Model
    {
        //
    }
Or, you may manually register the global scope by overriding the model's `booted` method and invoke the model's `addGlobalScope` method. The `addGlobalScope` method accepts an instance of your scope as its only argument:

    <?php
    
    namespace App\Models;
    
    use App\Models\Scopes\AncientScope;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The "booted" method of the model.
         */
        protected static function booted(): void
        {
            static::addGlobalScope(new AncientScope);
        }
    }
將上述範例中的 Scope 加到 `App\Models\User` Model 後，呼叫 `User::all()` 方法會執行下列 SQL 查詢：

```sql
select * from `users` where `created_at` < 0021-02-18 00:00:00
```
<a name="anonymous-global-scopes"></a>

#### 匿名全域 Scope

在 Eloquent 中，我們也可以使用閉包來定義全域 Scope。使用閉包來定義特別適用於一些簡單而不需要獨立拆分成 Class 的 Scope。使用閉包定義全域 Scope 時，應先設定一個自訂的 Scope 名稱作為第一個引數傳給 `addGlobalScope` 方法：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The "booted" method of the model.
         */
        protected static function booted(): void
        {
            static::addGlobalScope('ancient', function (Builder $builder) {
                $builder->where('created_at', '<', now()->subYears(2000));
            });
        }
    }
<a name="removing-global-scopes"></a>

#### 移除全域 Scope

若想在給定查詢內移除全域 Scope，可以使用 `withoutGlobalScope` 方法。這個方法接受全域 Scope 的類別名稱作為其唯一的引數：

    User::withoutGlobalScope(AncientScope::class)->get();
或者，若使用閉包定義全域 Scope，則可傳入自訂的字串名稱：

    User::withoutGlobalScope('ancient')->get();
若想移除多個或全部的查詢全域 Scope，可以使用 `withoutGlobalScopes` 方法：

    // Remove all of the global scopes...
    User::withoutGlobalScopes()->get();
    
    // Remove some of the global scopes...
    User::withoutGlobalScopes([
        FirstScope::class, SecondScope::class
    ])->get();
<a name="local-scopes"></a>

### 區域 Scope

使用區域 Scope，我們就可以定義一組通用的查詢條件，並可在專案內簡單地重複使用。舉例來說，我們可能會需要頻繁的找出所有「熱門」的使用者。若要定義 Scope，只需要定義一個有 `scope` 前置詞的 Eloquent Model 方法即可。

Scope 應回傳相同的 Query Builder 實體或 `void`：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * Scope a query to only include popular users.
         */
        public function scopePopular(Builder $query): void
        {
            $query->where('votes', '>', 100);
        }
    
        /**
         * Scope a query to only include active users.
         */
        public function scopeActive(Builder $query): void
        {
            $query->where('active', 1);
        }
    }
<a name="utilizing-a-local-scope"></a>

#### Utilizing a Local Scope

定義好 Scope 後，就可以在查詢 Model 時呼叫這個 Scope 方法。不過，在呼叫時不應包含 `scope` 前置詞。我們甚至還能串連呼叫多個 Scope：

    use App\Models\User;
    
    $users = User::popular()->active()->orderBy('created_at')->get();
在使用 `or` 查詢運算子來組合多個 Eloquent Model Scope 時，可能會需要使用閉包來取得正確的[邏輯性分組](/docs/{{version}}/queries#logical-grouping)：

    $users = User::popular()->orWhere(function (Builder $query) {
        $query->active();
    })->get();
不過，因為這麼做可能會很麻煩，因此 Laravel 提供了一個「高階的 (Higher Order)」`orWhere` 方法來讓我們能流暢地將各個 Scope 串連在一起，毋需使用閉包：

    $users = User::popular()->orWhere->active()->get();
<a name="dynamic-scopes"></a>

#### 動態 Scope

有時候，我們可能會想定義一些接受參數的 Scope。要定義接受參數的 Scope，只需要在 Scope 方法的簽章上加上額外的參數即可。所有 Scope 地參數的應定義在 `$query` 引數後：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * Scope a query to only include users of a given type.
         */
        public function scopeOfType(Builder $query, string $type): void
        {
            $query->where('type', $type);
        }
    }
將預期的引數加到 Scope 方法的簽章後，就可以在呼叫該 Scope 時傳入這些引數：

    $users = User::ofType('admin')->get();
<a name="comparing-models"></a>

## 比較 Model

有時候，我們可能需要判斷某兩個 Model 是否是「相同」的。`is` 與 `isNot` 方法可用來快速檢驗兩個 Model 是否有相同的主索引鍵、相同的資料表、以及相同的資料庫連線：

    if ($post->is($anotherPost)) {
        // ...
    }
    
    if ($post->isNot($anotherPost)) {
        // ...
    }
使用 `belongsTo`、`hasOne`、`morphTo`、`morphOne` 等[關聯](/docs/{{version}}/eloquent-relationships)時，也可使用 `is` 與 `isNot` 方法。這個方法特別適用於想在不實際執行查詢來取得 Model 的情況下比較關聯的 Model 時：

    if ($post->author()->is($user)) {
        // ...
    }
<a name="events"></a>

## 事件

> [!NOTE]  
> 想要直接將 Eloquent 事件廣播給前端嗎？請參考一下 Laravel 的 [Model 事件廣播](/docs/{{version}}/broadcasting#model-broadcasting)。

Eloquent Model 會分派數種事件，能讓我們在 Model 生命週期中的下列時刻進行攔截 (Hook)：`retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted`, `trashed`, `forceDeleting`, `forceDeleted`, `restoring`, `restored`, 與 `replicating`。

`retrieved` 事件會在現有 Model 從資料庫內取得後被分派。當新 Model 首次保存後，會分派 `creating` 與 `created` 事件。`updating` 與 `updated` 事件會在現有 Model 被修改並呼叫了 `save` 方法時被分派。`saving` 與 `saved` 事件會在 Model 被建立或更新後分派 —— 即使未對 Model 的屬性做任何更改。以 `-ing` 結尾的事件會在保存任何 Model 改動前被分派，而以 `-id` 結尾的事件則會在 Model 保存後被分派。

若要開始監聽 Model 事件，請在 Eloquent Model 中定義一個 `$dispatchesEvents` 屬性。該屬性會將 Eloquent Model 生命週期中的數個時間點影射到你自己的[事件類別](/docs/{{version}}/events)上。每個 Model 事件類別應預期會在其建構函式 (Constructor) 中收到一個受影響 Model 的實體：

    <?php
    
    namespace App\Models;
    
    use App\Events\UserDeleted;
    use App\Events\UserSaved;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable
    {
        use Notifiable;
    
        /**
         * The event map for the model.
         *
         * @var array<string, string>
         */
        protected $dispatchesEvents = [
            'saved' => UserSaved::class,
            'deleted' => UserDeleted::class,
        ];
    }
定義好 Eloquent 事件並設定好影射後，就可以使用[事件監聽程式](/docs/{{version}}/events#defining-listeners)來監聽事件。

> [!WARNING]  
> 在使用 Eloquent 進行批次更新或批次刪除查詢時，將不會引發 `saving`、`saved`、`updating`、`updated` 等 Model 事件。這是因為，在批次更新或批次刪除時並不會實際取得這些 Model。

<a name="events-using-closures"></a>

### 使用閉包

比起使用自訂的事件類別，我們也可以註冊一些會在 Model 事件分派時被執行的閉包。一般來說，應在 Model 的 `booted` 方法內定義這些閉包：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class User extends Model
    {
        /**
         * The "booted" method of the model.
         */
        protected static function booted(): void
        {
            static::created(function (User $user) {
                // ...
            });
        }
    }
若有需要，也可以在註冊 Model 事件時使用[可佇列的匿名事件監聽程式](/docs/{{version}}/events#queuable-anonymous-event-listeners)。這樣可以讓 Laravel 使用專案的[佇列](/docs/{{version}}/queues)來在背景執行 Model 事件監聽程式：

    use function Illuminate\Events\queueable;
    
    static::created(queueable(function (User $user) {
        // ...
    }));
<a name="observers"></a>

### 觀察程式 - Observer

<a name="defining-observers"></a>

#### 定義 Observer

若想在某個 Model 上監聽許多的事件，則可以使用 Observer 來把所有的監聽程式都放到單一類別內。Observer 類別內的方法名稱應對應欲監聽的 Eloquent 事件。這些方法都會收到受影響的 Model 作為其唯一引數。要建立新 Observer 最簡單的方法就是使用 `make:observer` Artisan 指令：

```shell
php artisan make:observer UserObserver --model=User
```
這個指令會將新的 Observer 放在 `app/Observers` 目錄中。若這個目錄不存在，則 Artisan 會自動建立。剛建立好的 Observer 會長這樣：

    <?php
    
    namespace App\Observers;
    
    use App\Models\User;
    
    class UserObserver
    {
        /**
         * Handle the User "created" event.
         */
        public function created(User $user): void
        {
            // ...
        }
    
        /**
         * Handle the User "updated" event.
         */
        public function updated(User $user): void
        {
            // ...
        }
    
        /**
         * Handle the User "deleted" event.
         */
        public function deleted(User $user): void
        {
            // ...
        }
    
        /**
         * Handle the User "restored" event.
         */
        public function restored(User $user): void
        {
            // ...
        }
    
        /**
         * Handle the User "forceDeleted" event.
         */
        public function forceDeleted(User $user): void
        {
            // ...
        }
    }
To register an observer, you may place the `ObservedBy` attribute on the corresponding model:

    use App\Observers\UserObserver;
    use Illuminate\Database\Eloquent\Attributes\ObservedBy;
    
    #[ObservedBy([UserObserver::class])]
    class User extends Authenticatable
    {
        //
    }
Or, you may manually register an observer by invoking the `observe` method on the model you wish to observe. You may register observers in the `boot` method of your application's `AppServiceProvider` class:

    use App\Models\User;
    use App\Observers\UserObserver;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }
> [!NOTE]  
> Observer 還能監聽一些額外的事件，如 `saving` 與 `retrieved`。這些事件都在[事件](#events)一節內討論過。.

<a name="observers-and-database-transactions"></a>

#### Observers and Database Transactions

若 Model 是在資料庫 Transaction 內建立的，則我們可能會想讓 Observer 只在資料庫 Transaction 被 Commit 後才執行其事件處理常式。為此，可以在 Observer 上實作 `ShouldHandleEventsAfterCommit` 介面。若沒有在執行資料庫 Transaction，則該 Event Handler 會立即執行：

    <?php
    
    namespace App\Observers;
    
    use App\Models\User;
    use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
    
    class UserObserver implements ShouldHandleEventsAfterCommit
    {
        /**
         * Handle the User "created" event.
         */
        public function created(User $user): void
        {
            // ...
        }
    }
<a name="muting-events"></a>

### 靜音事件

有時候，我們可能會想暫時「靜音」某個 Model 所觸發的所有事件。我們可以使用 `withoutEvents` 方法來達成。`withoutEvents` 方法接受一個閉包作為其單一引數。在這個閉包內執行的任何程式碼都不會派發出 Model 事件，而任何由該閉包回傳的值都會被 `withoutEvents` 方法回傳：

    use App\Models\User;
    
    $user = User::withoutEvents(function () {
        User::findOrFail(1)->delete();
    
        return User::find(2);
    });
<a name="saving-a-single-model-without-events"></a>

#### Saving a Single Model Without Events

有時候，我們可能會想在不分派任何事件的情況下「保存」某個 Model。我們可以使用 `saveQuietly` 方法來達成：

    $user = User::findOrFail(1);
    
    $user->name = 'Victoria Faith';
    
    $user->saveQuietly();
也可以在不分派任何事件的情況下「更新 (Update)」、「刪除 (Delete)」、「軟刪除 (Soft Delete)」、「恢復 (Restore)」、「複製 (Replicate)」給定的 Model：

    $user->deleteQuietly();
    $user->forceDeleteQuietly();
    $user->restoreQuietly();