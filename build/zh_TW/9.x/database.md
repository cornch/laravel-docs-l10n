---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/43/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 51.56
---

# 資料庫：入門

- [簡介](#introduction)
  - [設定](#configuration)
  - [讀寫連線](#read-and-write-connections)
  
- [執行 SQL 查詢](#running-queries)
  - [使用多個資料庫連線](#using-multiple-database-connections)
  - [監聽查詢事件](#listening-for-query-events)
  - [監控積累的查詢時間](#monitoring-cumulative-query-time)
  
- [資料庫 Transaction](#database-transactions)
- [連線至資料庫 CLI](#connecting-to-the-database-cli)
- [檢視資料庫](#inspecting-your-databases)
- [監控資料庫](#monitoring-your-databases)

<a name="introduction"></a>

## 簡介

幾乎所有的現代網站都會與資料庫互動。比起直接使用原始 SQL，Laravel 通過[流暢的 Query Builder](/docs/{{version}}/queries)、[Eloquent ORM](/docs/{{version}}/eloquent) 等功能大大簡化了與多種支援資料庫互動的過程。目前，Laravel 對 5 種資料庫提供了第一方支援：

<div class="content-list" markdown="1">
- MariaDB 10.3+ ([版本政策](https://mariadb.org/about/#maintenance-policy))
- MySQL 5.7+ ([版本政策](https://en.wikipedia.org/wiki/MySQL#Release_history))
- PostgreSQL 10.0+ ([版本政策](https://www.postgresql.org/support/versioning/))
- SQLite 3.8.8+
- SQL Server 2017+ ([版本政策](https://docs.microsoft.com/en-us/lifecycle/products/?products=sql-server))

</div>
<a name="configuration"></a>

### 設定

用於 Laravel 資料庫服務的設定檔位於專案的 `config/database.php` 設定檔中。可以這個設定檔中定義所有的資料庫連線，並可指定預設要使用哪個連線。該檔案中的大多數的設定選項都使用專案的環境變數。該檔案內含有 Laravel 所支援的大多數資料庫系統的設定範例。

預設情況下，Laravel 的範例[環境設定](/docs/{{version}}/configuration#environment-configuration)已準備好與 [Laravel Sail](/docs/{{version}}/sail) 一起使用。Laravel Sail 是一個 Docker 設定，用於在本機上開發 Laravel 專案。不過，可以隨意依據本機資料庫的需求來更改資料庫設定。

<a name="sqlite-configuration"></a>

#### SQLite 設定

SQLite 資料庫包含在檔案系統上的單一檔案。可以在終端機內使用 `touch` 指令來建立一個新的 SQLite 資料庫：`touch database/database.sqlite`。建立好該資料庫後，只需要輕鬆地將使用絕對路徑來將 `DB_DATABASE` 環境變數指向該資料庫即可：

```ini
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```
若要在 SQLite 連線上啟用外部索引鍵條件約束 (Foreign Key Constraint)，則應將 `DB_FOREIGN_KEYS` 環境變數設為 `true`：

```ini
DB_FOREIGN_KEYS=true
```
<a name="mssql-configuration"></a>

#### Microsoft SQL Server 設定

若要使用 Microsoft SQL Server 資料庫，則應確保有安裝 `sqlsrv` 與 `pdo_sqlsrv` PHP 擴充套件，以及任何可能需要的相依性，如 Microsoft SQL ODBC 驅動器。

<a name="configuration-using-urls"></a>

#### 使用 URL 來進行設定

一般來說，資料庫的連線是通過多個設定值來設定的，如 `host`, `database`, `username`, `password`…等。這幾個設定值都有其對應的環境變數。這表示在正式環境伺服器上設定資料庫連線資訊時，需要處理多個環境變數。

像 AWS 或 Heroku 等受管理的資料庫提供商會提供單一的資料庫「URL」，該 URL 在單一字串內包含了所有用於該資料庫的連線資訊。一下為這種 URL 的範例：

```html
mysql://root:password@127.0.0.1/forge?charset=UTF-8
```
這些 URL 通常遵守一種標準的結構描述規範：

```html
driver://username:password@host:port/database?options
```
為了方便起見，Laravel 也支援這些 URL 作為設定多個設定選項的替代。若有提供 `url` 設定選項 (或相應的 `DATABASE_URL` 環境變數)，則會使用該值來拆出資料庫連線與金鑰資訊。

<a name="read-and-write-connections"></a>

### 讀、寫連線

有時候，我們可能會像在 SELECT 陳述式上使用某個資料庫連線，並在 INSERT, UPDATE, DELETE 陳述式上使用另一個資料庫連線。在 Laravel 中要達成這種目標非常容易，而且不管是使用原始查詢、Query Builder、或是 Eloquent ORM，都能判斷使用適合的連線。

要瞭解如何設定讀、寫連線，來看看這個範例：

    'mysql' => [
        'read' => [
            'host' => [
                '192.168.1.1',
                '196.168.1.2',
            ],
        ],
        'write' => [
            'host' => [
                '196.168.1.3',
            ],
        ],
        'sticky' => true,
        'driver' => 'mysql',
        'database' => 'database',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
請注意，在設定陣列中加入了三個新的鍵值：`read`, `write`, `stick`。`read` 與 `write` 索引鍵為一個陣列，包含單一索引鍵：`host`。其他用於 `read` 與 `write` 連線的資料庫選項會從主要的 `mysql` 設定陣列合併過來：

只需要將 `mysql` 陣列中所需要複寫的值放到 `read` 與 `write` 陣列中即可。因此，在這個例子中，「read」連線的主機會是 `192.168.1.1` ，而「write」連線則會使用 `192.168.1.3`。資料庫認證、前置詞、字元集、以及其他主要 `mysql` 陣列中的選項都將在這兩個連線間共用。當 `host` 設定陣列中有多個值時，每個請求都會隨機選擇一個資料庫主機。

<a name="the-sticky-option"></a>

#### `sticky` 選項

`sticky` 選項是一個 **可選** 的值。該選項可用於設定在同一個請求生命週期中，當資料寫入資料庫後馬上讀取該記錄。若有啟用 `sticky` 選項，且有在目前請求生命週期內進行「寫入」操作，接下來的「讀取」操作都會使用「write」連線。這樣一來可以確保在該請求生命週期內寫入的資料能壩上在該請求內從資料庫內讀取回來。開發人員可以自行決定這種行為是否適用與你的專案。

<a name="running-queries"></a>

## 執行 SQL 查詢

設定好資料庫連線後，就可以使用 `DB` Facade 來執行查詢。`DB` Facade 提供了用於各類查詢的方法：`select`, `update`, `insert`, `delete` 與 `statement`。

<a name="running-a-select-query"></a>

#### 執行 SELECT 查詢

若要執行標準的 SELECT 查詢，可以使用 `DB` Facade 上的 `select` 方法：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;
    
    class UserController extends Controller
    {
        /**
         * Show a list of all of the application's users.
         *
         * @return \Illuminate\Http\Response
         */
        public function index()
        {
            $users = DB::select('select * from users where active = ?', [1]);
    
            return view('user.index', ['users' => $users]);
        }
    }
傳入 `select` 的第一個引數是 SQL 查詢，而第二個引數則是需要繫結到該查詢上的參數繫結。通常來說，這些繫結值就是 `where` 子句限制式的值。使用參數繫結即可避免 SQL 注入攻擊。

`select` 方法只會回傳 `array` 作為其結果。在陣列中的各個結果都會是 PHP 的 `stdClass`，代表資料庫內的記錄：

    use Illuminate\Support\Facades\DB;
    
    $users = DB::select('select * from users');
    
    foreach ($users as $user) {
        echo $user->name;
    }
<a name="selecting-scalar-values"></a>

#### Select ^[純量](Scalar)值

有時候，有些資料庫查詢只會回傳一個單一、^[純量](Scalar)的值。在 Laravel 中，我們不一定要從資料物件中取出該查詢的純量值，而可以使用 `scalar` 方法來直接取得這個值：

    $burgers = DB::scalar(
        "select count(case when food = 'burger' then 1 end) as burgers from menu"
    );
<a name="using-named-bindings"></a>

#### 使用命名繫結

比起使用 `?` 來表示參數繫結，也可以使用命名繫結來執行查詢：

    $results = DB::select('select * from users where id = :id', ['id' => 1]);
<a name="running-an-insert-statement"></a>

#### 執行 INSERT 陳述式

若要執行 `insert` 陳述式，可以使用 `DB` Facade 上的 `insert` 方法。與 `select` 方法類似，這個方法接受 SQL 查詢作為其第一個引數，而繫結則為其第二個引數：

    use Illuminate\Support\Facades\DB;
    
    DB::insert('insert into users (id, name) values (?, ?)', [1, 'Marc']);
<a name="running-an-update-statement"></a>

#### 執行 UPDATE 陳述式

`update` 陳述式應用來更新資料庫中的現有資料。該方法將回傳受該陳述式所影響的行數：

    use Illuminate\Support\Facades\DB;
    
    $affected = DB::update(
        'update users set votes = 100 where name = ?',
        ['Anita']
    );
<a name="running-a-delete-statement"></a>

#### 執行 DELETE 陳述式

`delete` 方法應用於從資料庫內刪除陣列。與 `update` 類似，該方法會回傳受影響的行數：

    use Illuminate\Support\Facades\DB;
    
    $deleted = DB::delete('delete from users');
<a name="running-a-general-statement"></a>

#### 執行一般陳述式

有的資料庫陳述式並不會回傳任何值。對於這類操作，可以使用 `DB` Facade 上的 `statement` 方法：

    DB::statement('drop table users');
<a name="running-an-unprepared-statement"></a>

#### 執行非預先準備的陳述式

有的時候，我們可能會想在不繫結任何值的情況下執行 SQL 陳述式。可以使用 `DB` Facade 的 `unprepared` 方法來達成：

    DB::unprepared('update users set votes = 100 where name = "Dries"');
> [!WARNING]  
> 由於未預先準備的陳述式並不繫結參數，因此這些查詢可能容易遭受 SQL 注入攻擊。在未預先準備的陳述式中，不應包含使用者可控制的值。

<a name="implicit-commits-in-transactions"></a>

#### 隱式 Commit

在 Transaction 內使用 `DB` Facade 的 `statement` 與 `unprepared` 方法時，應特別小心，以避免會導致[隱式 Commit](https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html) 的陳述式。這些陳述式會導致資料庫引擎間接地 Commit 整個 Transaction，並讓 Laravel 無從得知資料庫的 Transaction 等級。建立資料庫表就是這種陳述式的一個例子：

    DB::unprepared('create table a (col varchar(1) null)');
有關會觸發隱式 Commit 的[這類陳述式的清單](https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html)，請參考 MySQL 操作手冊。

<a name="using-multiple-database-connections"></a>

### 使用多個資料庫連線

若 `config/database.php` 設定檔中有有定義多個連線，則可以通過 `DB` Facade 的 `connection` 方法來存取各個連線。傳入 `connection` 方法內的連線名稱應對應到 `config/database.php` 設定檔內所列出的其中一個連線名稱，或是在執行階段使用 `config` 輔助函式所設定的連線：

    use Illuminate\Support\Facades\DB;
    
    $users = DB::connection('sqlite')->select(/* ... */);
也可以通過連線實體上的 `getPdo` 方法來存取原始、底層的 PDO 實體：

    $pdo = DB::connection()->getPdo();
<a name="listening-for-query-events"></a>

### 監聽查詢事件

若想讓網站在每次執行 SQL 查詢時叫用某個閉包，可以使用 `DB` Facade 的 `listen` 方法。該方法適用於記錄查詢或偵錯。可以在 [Service Provider](/docs/{{version}}/providers) 內的 `boot` 方法中註冊查詢的監聽程式閉包：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }
    
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            DB::listen(function ($query) {
                // $query->sql;
                // $query->bindings;
                // $query->time;
            });
        }
    }
<a name="monitoring-cumulative-query-time"></a>

### 監控積累的查詢時間

在現代網頁 App 中常見的效能瓶頸就是在查詢資料庫所花費的時間上。幸好，Laravel 可以在程式在單一 Request 中查詢資料庫花費太多時間時，叫用指定的閉包或回呼。若要開始監控積累的查詢時間，請向 `whenQueryingForLongerThan` 方法提供一個查詢時間的閥值 (單位為毫秒)，以及一個閉包。可以在某個 [Service Provider](/docs/{{version}}/providers) 中叫用此方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Database\Connection;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Database\Events\QueryExecuted;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         *
         * @return void
         */
        public function register()
        {
            //
        }
    
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            DB::whenQueryingForLongerThan(500, function (Connection $connection, QueryExecuted $event) {
                // Notify development team...
            });
        }
    }
<a name="database-transactions"></a>

## 資料庫 Transaction

可以使用 `DB` Facade 所提供的 `transaction` 方法來在資料庫 Transaction 內執行一系列的操作。若在該 Transaction 閉包內擲回了例外，則該 Transaction 會被自動回滾，並將該例外重新擲回。若閉包成功執行，則該 Transaction 會被自動 Commit。在使用 `transaction` 方法時不需要顧慮如何手動回滾或 Commit：

    use Illuminate\Support\Facades\DB;
    
    DB::transaction(function () {
        DB::update('update users set votes = 1');
    
        DB::delete('delete from posts');
    });
<a name="handling-deadlocks"></a>

#### 處理死結 (Deadlock)

`transaction` 方法接受一個可選的第二引數，用來定義當發生死結 (Deadlock) 時該 Transaction 要重試的最大次數。當達到該限制後，會擲回例外：

    use Illuminate\Support\Facades\DB;
    
    DB::transaction(function () {
        DB::update('update users set votes = 1');
    
        DB::delete('delete from posts');
    }, 5);
<a name="manually-using-transactions"></a>

#### 手動使用 Transaction

若想手動開啟 Transaction，並完整控制回滾與 Commit，則可使用 `DB` Facade 提供的 `beginTransaction` 方法：

    use Illuminate\Support\Facades\DB;
    
    DB::beginTransaction();
可以通過 `rollBack` 方法來回滾該 Transaction：

    DB::rollBack();
最後，可以使用 `commit` 方法來 Commit Transaction：

    DB::commit();
> [!NOTE]  
> `DB` Facade 的 Transaction 方法會同時控制到 [Query Builder](/docs/{{version}}/queries) 與 [Eloquent ORM](/docs/{{version}}/eloquent)。

<a name="connecting-to-the-database-cli"></a>

## 連線到資料庫 CLI

若想連線到資料庫的 CLI，可以使用 `db` Artisan 指令：

```shell
php artisan db
```
若有需要，可以指定資料庫連線名稱來連先到非預設連線的資料庫連線：

```shell
php artisan db mysql
```
<a name="inspecting-your-databases"></a>

## 檢視資料庫

使用 `db:show` 與 `db:table` Artisan 指令，即可檢視有關資料庫與其關聯的資料表的各種實用資料。若要檢視資料庫的概覽，如資料庫大小、型別、開啟中的連線數、資料表概覽等，可使用 `db:show` 指令：

```shell
php artisan db:show
```
也可以提供 `--database` 選項來提供要檢視的資料庫連線名稱：

```shell
php artisan db:show --database=pgsql
```
若要在該指令的輸出中包含資料表的行數統計與資料庫 View 的詳情，可提供 `--counts` 與 `--views`，這兩個指令分別對應了此二功能。在大型資料庫中，取得行數與 View 的詳情可能較慢：

```shell
php artisan db:show --counts --views
```
<a name="table-overview"></a>

#### 資料表概覽

若想取得資料庫中個別資料表的概覽，可執行 `db:table` Artisan 指令。該指令會為某個資料庫資料表提供一般性的概覽，包含其欄位、型別、屬性、索引鍵、與索引等：

```shell
php artisan db:table users
```
<a name="monitoring-your-databases"></a>

## 監控資料庫

使用 `db:monitor` Artisan 指令，當資料庫中處理了超過特定數量的連線時，Laravel 就會分派一個 `Illuminate\Database\Events\DatabaseBusy` 事件。

若要開始監控資料庫，可設定排程，[每分鐘](/docs/{{version}}/scheduling)都執行一次 `db:monitor` 指令。可傳入要監控的資料庫連線名稱給該指令，或是分派 Event 前可允許的最大開放連線數：

```shell
php artisan db:monitor --databases=mysql,pgsql --max=100
```
若只排程執行該指令，檔開放連線數過高時仍然不會觸發通知來提醒你。當該指令偵測到資料庫的開放連線數超過指定的閥值時，會分派一個 `DatabaseBusy` 事件。我們需要在專案的 `EventServiceProvider` 內監聽該事件，才能將通知傳送給你，或是你的開發團隊：

```php
use App\Notifications\DatabaseApproachingMaxConnections;
use Illuminate\Database\Events\DatabaseBusy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

/**
 * Register any other events for your application.
 *
 * @return void
 */
public function boot()
{
    Event::listen(function (DatabaseBusy $event) {
        Notification::route('mail', 'dev@example.com')
                ->notify(new DatabaseApproachingMaxConnections(
                    $event->connectionName,
                    $event->connections
                ));
    });
}
```