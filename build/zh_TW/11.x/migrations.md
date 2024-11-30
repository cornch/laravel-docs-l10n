---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/103/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 52.99
---

# 資料庫：Migration

- [簡介](#introduction)
- [產生 Migration](#generating-migrations)
  - [壓縮 Migration](#squashing-migrations)
  
- [Migration 的架構](#migration-structure)
- [執行 Migration](#running-migrations)
  - [復原 Migration](#rolling-back-migrations)
  
- [資料表](#tables)
  - [建立資料表](#creating-tables)
  - [更新資料表](#updating-tables)
  - [重新命名或刪除資料表](#renaming-and-dropping-tables)
  
- [欄位](#columns)
  - [建立欄位](#creating-columns)
  - [可用的欄位型別](#available-column-types)
  - [欄位修飾詞](#column-modifiers)
  - [修改欄位](#modifying-columns)
  - [重新命名欄位](#renaming-columns)
  - [刪除欄位](#dropping-columns)
  
- [索引](#indexes)
  - [建立索引](#creating-indexes)
  - [重新命名索引](#renaming-indexes)
  - [刪除索引](#dropping-indexes)
  - [Foreign Key Constraint](#foreign-key-constraints)
  
- [Event](#events)

<a name="introduction"></a>

## 簡介

「^[Migration](%E7%A7%BB%E8%BD%89)」就像是資料表的版本控制一樣，我們能通過 Migration 來定義並與開發團隊共享專案的資料庫結構定義。讀者是否曾經在從版控拉去更新後，還需要告訴同事要手動新增欄位？資料庫 Migration 就是要解決這樣的問題。

Laravel 的 `Schema` [Facade](/docs/{{version}}/facades) 提供了一種可建立或修改資料表的功能，該功能不區分資料，可用在所有 Laravel 支援的資料庫系統上。一般來說，Migration 會使用該 Facade 來建立或修改資料庫資料表與欄位。

<a name="generating-migrations"></a>

## 產生 Migration

我們可以使用 `make:migration` [Artisan 指令](/docs/{{version}}/artisan) 來產生資料庫 Migration。新建立的 Migration 會放在 `database/migrations` 目錄下。各個 Migration 的檔名都包含了一個時戳，用來讓 Laravel 判斷各個 Migration 的執行順序：

```shell
php artisan make:migration create_flights_table
```
Laravel 會使用 Migration 的名稱來嘗試推測資料表的名稱，並嘗試推測該 Migration 是否要建立新資料表。若 Laravel 可判斷檔案名稱，則 Laravel 會預先在產生的 Migration 檔中填入特定的資料表。若無法判斷時，我們只需要在 Migration 檔中手動指定資料表即可。

若想為產生的 Migration 檔指定自訂的路徑，則可在執行 `make:migration` 指令時使用 `--path` 選項。給定的路徑應為相對於專案根目錄的相對路徑。

> [!NOTE]  
> 可以[安裝 Stub](/docs/{{version}}/artisan#stub-customization) 來自訂 Migration 的 Stub。

<a name="squashing-migrations"></a>

### 壓縮 Migration

在我們持續撰寫專案的同時，我們可能會逐漸累積出越來越多的資料庫 Migration 檔。這樣可能會導致 `database/migrations` 目錄中包含了數百個 Migration 檔。若有需要的話，我們可以將 Migration 檔「壓縮」進單一 SQL 檔內。要開始壓縮，請執行 `schema:dump` 指令：

```shell
php artisan schema:dump

# Dump the current database schema and prune all existing migrations...
php artisan schema:dump --prune
```
執行該指令時，Laravel 會將一個「^[Schema](%E7%B5%90%E6%A7%8B%E6%8F%8F%E8%BF%B0)」檔案寫入 `database/schema` 目錄內。Schema 檔案的名稱對影到資料庫連線的名稱。當要移轉資料庫時，若尚未執行過任何 Migration，Laravel 會先執行目前正在使用的資料庫連線所對應 Schema 檔中的 SQL。執行完 Schema 檔內的陳述式後，Laravel 才會接著執行不在該 Schema 傾印中剩下的 Migration。

若專案的測試使用的資料庫連線與本機開發環境所使用的連線不同時，請確認是否有使用該資料庫連線傾印 Schema 檔案，這樣測試中才能正常的建立資料庫。通常這個步驟應放在將開發環境所使用的資料庫連線傾印出來之後：

```shell
php artisan schema:dump
php artisan schema:dump --database=testing --prune
```
請將資料庫 Schema 檔 ^[Commit](%E7%B0%BD%E5%85%A5) 進版本控制中，好讓團隊中其他的新開發人員可快速建立專案的初始資料庫結構。

> [!WARNING]  
> Migration squashing is only available for the MariaDB, MySQL, PostgreSQL, and SQLite databases and utilizes the database's command-line client.

<a name="migration-structure"></a>

## Migration 的架構

Migration 類別中包含了兩個方法：`up` 與 `down`。`up` 方法可用來在資料庫中新增新資料表、欄位、索引等；而 `down` 方法則用來做與 `up` 方法相反的事。

在這兩個方法中，我們可以使用 Laravel 的 Schema Builder 來以描述性的方法建立與修改資料表。若要瞭解 `Schema` Builder 中所有可用的方法，[請參考 Schema Builder 的說明文件](#creating-tables)。舉例來說，下列 Migration 會建立一個 `flights` 資料表：

    <?php
    
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('flights', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('airline');
                $table->timestamps();
            });
        }
    
        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::drop('flights');
        }
    };
<a name="setting-the-migration-connection"></a>

#### Setting the Migration Connection

若 Migration 會使用與專案預設資料庫連線不同的資料庫連線，則請在 Migration 中設定 `$connection` 屬性：

    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql';
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ...
    }
<a name="running-migrations"></a>

## 執行 Migration

若要執行所有尚未執行過的 Migration，請執行 `migrate` Artisan 指令：

```shell
php artisan migrate
```
若想檢視目前為止已執行了哪些 Migration，可使用 `migrate:status` Artisan 指令：

```shell
php artisan migrate:status
```
若想檢視 Migration 中會執行的 SQL 陳述式而不實際執行這些 SQL，則可提供 `--pretend` 旗標給 `migrate` 指令：

```shell
php artisan migrate --pretend
```
#### 獨立執行 Migration

若專案會被部署到多個伺服器上，而 Migration 是部署流程的一部分的話，我們通常不會希望有兩台 Server 同時嘗試執行資料庫移轉。若要避免有兩台 Server 同時執行，可以在呼叫 `migrate` 指令時加上 `isolated` 選項。

提供了 `isolated` 選項後，Laravel 會嘗試使用專案的快取 Driver 來取得一個 ^[Atomic Lock](%E4%B8%8D%E5%8F%AF%E9%83%A8%E5%88%86%E5%AE%8C%E6%88%90%E9%8E%96%E5%AE%9A)，然後再執行 Migration。當 Lock 被鎖住時，嘗試執行 `migrate` 指令就不會執行。不過，這些不被執行的 `migrate` 指令仍然會回傳成功的終止狀態代碼：

```shell
php artisan migrate --isolated
```
> [!WARNING]  
> 若要使用此功能，則應用程式必須要使用 `memcached`, `redis`, `dynamodb`, `database`, `file` 或 `array` 作為應用程式的預設快取 Driver。另外，所有的伺服器也都必須要連線至相同的中央快取伺服器。

<a name="forcing-migrations-to-run-in-production"></a>

#### Forcing Migrations to Run in Production

有些 Migration 中的動作是破壞性的，也就是一些會導致資料消失的動作。為了避免在正式環境資料庫中執行這些破壞性的動作，因此在執行指令時，會出現提示要求確認。若要強制該指令不跳出提示直接執行，請使用 `--force` 旗標：

```shell
php artisan migrate --force
```
<a name="rolling-back-migrations"></a>

### 復原 Migration

若要復原最後執行的 Migration 動作，可使用 `rollback` Artisan 指令。該指令會復原最後「一批」執行的 Migration，其中可能包含多個 Migration 檔：

```shell
php artisan migrate:rollback
```
我們也可以提供各一個 `step` 選項給 `rollback` 指令，以限制要復原的 Migration 數量。舉例來說，下列指令只會復原最後 5 個 Migration：

```shell
php artisan migrate:rollback --step=5
```
只要提供 `batch` 選項給 `rollback` 指令，就可以復原特定「批次」的 Migration。這裡的 `batch` 選項，對應到專案資料庫中 `migrations` 資料表的 batch 值。舉例來說，下列指令會復原所有第 3 批次的 Migration：

 ```shell
 php artisan migrate:rollback --batch=3
 ```
若想檢視 Migration 中會執行的 SQL 陳述式而不實際執行這些 SQL，則可提供 `--pretend` 旗標給 `migrate:rollback` 指令：

```shell
php artisan migrate:rollback --pretend
```
`migrate:reset` 指令會復原專案中所有的 Migration：

```shell
php artisan migrate:reset
```
<a name="roll-back-migrate-using-a-single-command"></a>

#### Roll Back and Migrate Using a Single Command

`migrate:refresh` 指令會將所有的 Migration 都復原回去，並接著執行 `migrate` 指令。使用該指令，就可以有效率的重建整個資料庫：

```shell
php artisan migrate:refresh

# Refresh the database and run all database seeds...
php artisan migrate:refresh --seed
```
我們也可以提供各一個 `step` 選項給 `refresh` 指令，以限制要復原並重新 Migrate 的 Migration 數量。舉例來說，下列指令只會復原並重新 Migrate 最後 5 個 Migration：

```shell
php artisan migrate:refresh --step=5
```
<a name="drop-all-tables-migrate"></a>

#### Drop All Tables and Migrate

`migrate:fresh` 指令會刪除資料庫中所有資料表，並接著執行 `migrate` 指令：

```shell
php artisan migrate:fresh

php artisan migrate:fresh --seed
```
By default, the `migrate:fresh` command only drops tables from the default database connection. However, you may use the `--database` option to specify the database connection that should be migrated. The database connection name should correspond to a connection defined in your application's `database` [configuration file](/docs/{{version}}/configuration):

```shell
php artisan migrate:fresh --database=admin
```
> [!WARNING]  
> 不論資料表是否有前置詞 (Prefix)，`migrate:fresh` 指令會刪除所有的資料庫資料表。在使用與其他專案共享的資料庫時，若要與本指令搭配使用請務必注意。

<a name="tables"></a>

## 資料表

<a name="creating-tables"></a>

### 建立資料表

若要建立新的資料庫資料表，請使用 `Schema` Facade 上的 `create` 方法。`create` 方法接受兩個引數：資料表名稱、以及一個接收 `Blueprint` 物件的閉包。`Blueprint` 物件可用來定義新資料表：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->timestamps();
    });
建立資料表時，我們可以使用任意 Schema Builder 的[欄位方法](#creating-columns)來定義資料表欄位。

<a name="determining-table-column-existence"></a>

#### Determining Table / Column Existence

You may determine the existence of a table, column, or index using the `hasTable`, `hasColumn`, and `hasIndex` methods:

    if (Schema::hasTable('users')) {
        // The "users" table exists...
    }
    
    if (Schema::hasColumn('users', 'email')) {
        // The "users" table exists and has an "email" column...
    }
    
    if (Schema::hasIndex('users', ['email'], 'unique')) {
        // The "users" table exists and has a unique index on the "email" column...
    }
<a name="database-connection-table-options"></a>

#### Database Connection and Table Options

若要在非專案預設連線的資料庫連線上做 Schema 動作，請使用 `connection` 方法：

    Schema::connection('sqlite')->create('users', function (Blueprint $table) {
        $table->id();
    });
In addition, a few other properties and methods may be used to define other aspects of the table's creation. The `engine` property may be used to specify the table's storage engine when using MariaDB or MySQL:

    Schema::create('users', function (Blueprint $table) {
        $table->engine('InnoDB');
    
        // ...
    });
The `charset` and `collation` properties may be used to specify the character set and collation for the created table when using MariaDB or MySQL:

    Schema::create('users', function (Blueprint $table) {
        $table->charset('utf8mb4');
        $table->collation('utf8mb4_unicode_ci');
    
        // ...
    });
`temporary` 方法可用來表示該資料表是「臨時」資料表。臨時資料表只可在目前連線的資料庫工作階段中使用，且會在連線關閉後自動刪除：

    Schema::create('calculations', function (Blueprint $table) {
        $table->temporary();
    
        // ...
    });
If you would like to add a "comment" to a database table, you may invoke the `comment` method on the table instance. Table comments are currently only supported by MariaDB, MySQL, and PostgreSQL:

    Schema::create('calculations', function (Blueprint $table) {
        $table->comment('Business calculations');
    
        // ...
    });
<a name="updating-tables"></a>

### 更新資料表

`Schema` Facade 上的 `table` 方法可用來更新現有的資料表。與 `create` 方法類似，`table` 方法接受兩個因數：資料表名稱，以及一個接收 `Blueprint` 實體的閉包。使用 `Blueprint` 實體，即可用來在資料表上新增欄位或索引：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('users', function (Blueprint $table) {
        $table->integer('votes');
    });
<a name="renaming-and-dropping-tables"></a>

### 重新命名或刪除資料表

若要重新命名現有的資料表，可使用 `rename` 方法：

    use Illuminate\Support\Facades\Schema;
    
    Schema::rename($from, $to);
若要移除現有的資料表，可使用 `drop` 或 `dropIfExists` 方法：

    Schema::drop('users');
    
    Schema::dropIfExists('users');
<a name="renaming-tables-with-foreign-keys"></a>

#### 與外部索引鍵一起重新命名資料表

在重新命名資料表時，請務必確認該資料表上的^[外部索引鍵條件](Foreign Key Constraint)是否有直接設定名稱，而不是使用 Laravel 所指定的基於慣例的名稱。若未直接設定名稱，則這些外部索引鍵條件的名稱可能會參照到舊的資料表名稱：

<a name="columns"></a>

## 欄位

<a name="creating-columns"></a>

### 建立欄位

`Schema` Facade 上的 `table` 方法可用來更新現有的資料表。與 `create` 方法類似，`table` 方法接受兩個因數：資料表名稱，以及一個接收 `Illuminate\Database\Schema\Blueprint` 實體的閉包。使用這個 `Blueprint` 實體，即可用來在資料表上新增欄位或索引：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('users', function (Blueprint $table) {
        $table->integer('votes');
    });
<a name="available-column-types"></a>

### 可用的欄位型別

Schema Builder Blueprint 提供了多種方法，這些方法對應到可新增至資料庫資料表中各種不同的欄位型別。可用的各個方法列在下表中：

<style>
    .collection-method-list > p {
        columns: 10.8em 3; -moz-columns: 10.8em 3; -webkit-columns: 10.8em 3;
    }

    .collection-method-list a {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .collection-method code {
        font-size: 14px;
    }

    .collection-method:not(.first-collection-method) {
        margin-top: 50px;
    }
</style>
<div class="collection-method-list" markdown="1">
[bigIncrements](#column-method-bigIncrements)
[bigInteger](#column-method-bigInteger)
[binary](#column-method-binary)
[boolean](#column-method-boolean)
[char](#column-method-char)
[dateTimeTz](#column-method-dateTimeTz)
[dateTime](#column-method-dateTime)
[date](#column-method-date)
[decimal](#column-method-decimal)
[double](#column-method-double)
[enum](#column-method-enum)
[float](#column-method-float)
[foreignId](#column-method-foreignId)
[foreignIdFor](#column-method-foreignIdFor)
[foreignUlid](#column-method-foreignUlid)
[foreignUuid](#column-method-foreignUuid)
[geography](#column-method-geography)
[geometry](#column-method-geometry)
[id](#column-method-id)
[increments](#column-method-increments)
[integer](#column-method-integer)
[ipAddress](#column-method-ipAddress)
[json](#column-method-json)
[jsonb](#column-method-jsonb)
[longText](#column-method-longText)
[macAddress](#column-method-macAddress)
[mediumIncrements](#column-method-mediumIncrements)
[mediumInteger](#column-method-mediumInteger)
[mediumText](#column-method-mediumText)
[morphs](#column-method-morphs)
[nullableMorphs](#column-method-nullableMorphs)
[nullableTimestamps](#column-method-nullableTimestamps)
[nullableUlidMorphs](#column-method-nullableUlidMorphs)
[nullableUuidMorphs](#column-method-nullableUuidMorphs)
[rememberToken](#column-method-rememberToken)
[set](#column-method-set)
[smallIncrements](#column-method-smallIncrements)
[smallInteger](#column-method-smallInteger)
[softDeletesTz](#column-method-softDeletesTz)
[softDeletes](#column-method-softDeletes)
[string](#column-method-string)
[text](#column-method-text)
[timeTz](#column-method-timeTz)
[time](#column-method-time)
[timestampTz](#column-method-timestampTz)
[timestamp](#column-method-timestamp)
[timestampsTz](#column-method-timestampsTz)
[timestamps](#column-method-timestamps)
[tinyIncrements](#column-method-tinyIncrements)
[tinyInteger](#column-method-tinyInteger)
[tinyText](#column-method-tinyText)
[unsignedBigInteger](#column-method-unsignedBigInteger)
[unsignedInteger](#column-method-unsignedInteger)
[unsignedMediumInteger](#column-method-unsignedMediumInteger)
[unsignedSmallInteger](#column-method-unsignedSmallInteger)
[unsignedTinyInteger](#column-method-unsignedTinyInteger)
[ulidMorphs](#column-method-ulidMorphs)
[uuidMorphs](#column-method-uuidMorphs)
[ulid](#column-method-ulid)
[uuid](#column-method-uuid)
[vector](#column-method-vector)
[year](#column-method-year)

</div>
<a name="column-method-bigIncrements"></a>

#### `bigIncrements()` {.collection-method .first-collection-method}

`bigIncrements` 方法建立一個 ^[Auto-Increment](%E8%87%AA%E5%8B%95%E9%81%9E%E5%A2%9E) 的 `UNSIGNED BIGINT` (^[主索引鍵](Primary Key)) 或相等欄位：

    $table->bigIncrements('id');
<a name="column-method-bigInteger"></a>

#### `bigInteger()` {.collection-method}

`bigInteger` 方法建立一個 `BIGINT` 或相等的欄位：

    $table->bigInteger('votes');
<a name="column-method-binary"></a>

#### `binary()` {.collection-method}

`binary` 方法建立一個 `BLOB` 或相等欄位：

    $table->binary('photo');
When utilizing MySQL, MariaDB, or SQL Server, you may pass `length` and `fixed` arguments to create `VARBINARY` or `BINARY` equivalent column:

    $table->binary('data', length: 16); // VARBINARY(16)
    
    $table->binary('data', length: 16, fixed: true); // BINARY(16)
<a name="column-method-boolean"></a>

#### `boolean()` {.collection-method}

`boolean` 方法建立一個 `BOOLEAN` 或相等欄位：

    $table->boolean('confirmed');
<a name="column-method-char"></a>

#### `char()` {.collection-method}

`char` 方法以給定的長度來建立一個 `CHAR` 或相等欄位：

    $table->char('name', length: 100);
<a name="column-method-dateTimeTz"></a>

#### `dateTimeTz()` {.collection-method}

The `dateTimeTz` method creates a `DATETIME` (with timezone) equivalent column with an optional fractional seconds precision:

    $table->dateTimeTz('created_at', precision: 0);
<a name="column-method-dateTime"></a>

#### `dateTime()` {.collection-method}

The `dateTime` method creates a `DATETIME` equivalent column with an optional fractional seconds precision:

    $table->dateTime('created_at', precision: 0);
<a name="column-method-date"></a>

#### `date()` {.collection-method}

`date` 方法會建立一個 `DATE` 或相等欄位：

    $table->date('created_at');
<a name="column-method-decimal"></a>

#### `decimal()` {.collection-method}

`decimal` 方法會以給定的^[精度](Precision) (總位數) 與^[小數位數](Scale) (小數位數) 來建立一個 `DECIMAL` 或相等欄位：

    $table->decimal('amount', total: 8, places: 2);
<a name="column-method-double"></a>

#### `double()` {.collection-method}

The `double` method creates a `DOUBLE` equivalent column:

    $table->double('amount');
<a name="column-method-enum"></a>

#### `enum()` {.collection-method}

`enum` 方法以給定的有效值來建立一個 `ENUM` 或相等欄位：

    $table->enum('difficulty', ['easy', 'hard']);
<a name="column-method-float"></a>

#### `float()` {.collection-method}

The `float` method creates a `FLOAT` equivalent column with the given precision:

    $table->float('amount', precision: 53);
<a name="column-method-foreignId"></a>

#### `foreignId()` {.collection-method}

`foreignId` 方法會建立一個 `UNSIGNED BIGINT` 或相等的欄位：

    $table->foreignId('user_id');
<a name="column-method-foreignIdFor"></a>

#### `foreignIdFor()` {.collection-method}

`foreignIdFor` 方法會新增一個用於給定 Model 類別的 `{column}_id` 同等欄位。依照該 Model 的索引鍵型別，該欄位可能為 `UNSIGNED BIGINT`、`CHAR(36)` 或 `CHAR(26)`。

    $table->foreignIdFor(User::class);
<a name="column-method-foreignUlid"></a>

#### `foreignUlid()` {.collection-method}

`foreignUlid` 方法會建立一個 `ULID` 或相等欄位：

    $table->foreignUlid('user_id');
<a name="column-method-foreignUuid"></a>

#### `foreignUuid()` {.collection-method}

`foreignUuid` 方法會建立一個 `UUID` 或相等欄位：

    $table->foreignUuid('user_id');
<a name="column-method-geography"></a>

#### `geography()` {.collection-method}

The `geography` method creates a `GEOGRAPHY` equivalent column with the given spatial type and SRID (Spatial Reference System Identifier):

    $table->geography('coordinates', subtype: 'point', srid: 4326);
> [!NOTE]  
> Support for spatial types depends on your database driver. Please refer to your database's documentation. If your application is utilizing a PostgreSQL database, you must install the [PostGIS](https://postgis.net) extension before the `geography` method may be used.

<a name="column-method-geometry"></a>

#### `geometry()` {.collection-method}

The `geometry` method creates a `GEOMETRY` equivalent column with the given spatial type and SRID (Spatial Reference System Identifier):

    $table->geometry('positions', subtype: 'point', srid: 0);
> [!NOTE]  
> Support for spatial types depends on your database driver. Please refer to your database's documentation. If your application is utilizing a PostgreSQL database, you must install the [PostGIS](https://postgis.net) extension before the `geometry` method may be used.

<a name="column-method-id"></a>

#### `id()` {.collection-method}

`id` 欄位為 `bigIncrements` 方法的別名。預設情況下，該方法會建立一個 `id` 欄位。不過，若想為該欄位指定不同的名稱，也可以傳入欄位名稱：

    $table->id();
<a name="column-method-increments"></a>

#### `increments()` {.collection-method}

`increments` 方法會建立一個 ^[Auto-Increment](%E8%87%AA%E5%8B%95%E9%81%9E%E5%A2%9E) 的 `UNSIGNED INTEGER` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->increments('id');
<a name="column-method-integer"></a>

#### `integer()` {.collection-method}

`integer` 方法建立一個 `INTEGER` 或相等的欄位：

    $table->integer('votes');
<a name="column-method-ipAddress"></a>

#### `ipAddress()` {.collection-method}

`ipAddress` 方法會建立一個 `VARCHAR` 或相等欄位：

    $table->ipAddress('visitor');
When using PostgreSQL, an `INET` column will be created.

<a name="column-method-json"></a>

#### `json()` {.collection-method}

`json` 方法會建立一個 `JSON` 或相等欄位：

    $table->json('options');
<a name="column-method-jsonb"></a>

#### `jsonb()` {.collection-method}

`jsonb` 方法會建立一個 `JSONB` 或相等欄位：

    $table->jsonb('options');
<a name="column-method-longText"></a>

#### `longText()` {.collection-method}

`longText` 方法建立一個 `LONGTEXT` 或相等欄位：

    $table->longText('description');
When utilizing MySQL or MariaDB, you may apply a `binary` character set to the column in order to create a `LONGBLOB` equivalent column:

    $table->longText('data')->charset('binary'); // LONGBLOB
<a name="column-method-macAddress"></a>

#### `macAddress()` {.collection-method}

`macAddress` 方法會建立一個用來保存 MAC 位址的欄位。在某些資料庫系統，如 PostgreSQL 中，有專門的欄位可用來保存這種類型的資料。在其他資料庫系統，則會使用相等的字串欄位：

    $table->macAddress('device');
<a name="column-method-mediumIncrements"></a>

#### `mediumIncrements()` {.collection-method}

`mediumIncrements` 方法會建立一個 ^[Auto-Increment](%E8%87%AA%E5%8B%95%E9%81%9E%E5%A2%9E) 的 `UNSIGNED MEDIUMINT` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->mediumIncrements('id');
<a name="column-method-mediumInteger"></a>

#### `mediumInteger()` {.collection-method}

`mediumInteger` 方法建立一個 `MEDIUMINT` 或相等的欄位：

    $table->mediumInteger('votes');
<a name="column-method-mediumText"></a>

#### `mediumText()` {.collection-method}

`mediumText` 方法建立一個 `MEDIUMTEXT` 或相等的欄位：

    $table->mediumText('description');
When utilizing MySQL or MariaDB, you may apply a `binary` character set to the column in order to create a `MEDIUMBLOB` equivalent column:

    $table->mediumText('data')->charset('binary'); // MEDIUMBLOB
<a name="column-method-morphs"></a>

#### `morphs()` {.collection-method}

`morphs` 方法會新增一個用於給定 Model 類別的 `{column}_id` 同等欄位與 `{column}_type` `VARCHAR` 同等欄位。依照該 Model 的索引鍵型別，`{column}_id` 欄位可能為 `UNSIGNED BIGINT`、`CHAR(36)` 或 `CHAR(26)` 型別。

該方法主要是要給多型 [Eloquent 關聯](/docs/{{version}}/eloquent-relationships)定義欄位用的。在下列範例中，會建立 `taggable_id` 與 `taggable_type` 欄位：

    $table->morphs('taggable');
<a name="column-method-nullableTimestamps"></a>

#### `nullableTimestamps()` {.collection-method}

`nullabaleTimestamps` 方法是 [timestamps](#column-method-timestamps) 方法的別名：

    $table->nullableTimestamps(precision: 0);
<a name="column-method-nullableMorphs"></a>

#### `nullableMorphs()` {.collection-method}

該方法與 [morphs](#column-method-morphs) 方法類似。不過，使用 `nullableMorphs` 方法建立的欄位會是「nullable」的：

    $table->nullableMorphs('taggable');
<a name="column-method-nullableUlidMorphs"></a>

#### `nullableUlidMorphs()` {.collection-method}

該方法與 [ulidMorphs](#column-method-ulidMorphs) 方法類似。不過，使用 `ulidMorphs` 方法建立的欄位會是「nullable」的：

    $table->nullableUlidMorphs('taggable');
<a name="column-method-nullableUuidMorphs"></a>

#### `nullableUuidMorphs()` {.collection-method}

該方法與 [uuidMorphs](#column-method-uuidMorphs) 方法類似。不過，使用 `nullableMorphs` 方法建立的欄位會是「nullable」的：

    $table->nullableUuidMorphs('taggable');
<a name="column-method-rememberToken"></a>

#### `rememberToken()` {.collection-method}

`rememberToken` 方法會建立一個 Nullable 的 `VARCHAR(100)` 或相等的欄位，用於存放目前的「記住我」[身份驗證權杖](/docs/{{version}}/authentication#remembering-users)：

    $table->rememberToken();
<a name="column-method-set"></a>

#### `set()` {.collection-method}

`set` 方法會以給定的有效值來建立一個 `SET` 或相等欄位：

    $table->set('flavors', ['strawberry', 'vanilla']);
<a name="column-method-smallIncrements"></a>

#### `smallIncrements()` {.collection-method}

`smallIncrements` 方法會建立一個 ^[Auto-Increment](%E8%87%AA%E5%8B%95%E9%81%9E%E5%A2%9E) 的 `UNSIGNED SMALLINT` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->smallIncrements('id');
<a name="column-method-smallInteger"></a>

#### `smallInteger()` {.collection-method}

`smallInteger` 方法建立一個 `SMALLINT` 或相等的欄位：

    $table->smallInteger('votes');
<a name="column-method-softDeletesTz"></a>

#### `softDeletesTz()` {.collection-method}

The `softDeletesTz` method adds a nullable `deleted_at` `TIMESTAMP` (with timezone) equivalent column with an optional fractional seconds precision. This column is intended to store the `deleted_at` timestamp needed for Eloquent's "soft delete" functionality:

    $table->softDeletesTz('deleted_at', precision: 0);
<a name="column-method-softDeletes"></a>

#### `softDeletes()` {.collection-method}

The `softDeletes` method adds a nullable `deleted_at` `TIMESTAMP` equivalent column with an optional fractional seconds precision. This column is intended to store the `deleted_at` timestamp needed for Eloquent's "soft delete" functionality:

    $table->softDeletes('deleted_at', precision: 0);
<a name="column-method-string"></a>

#### `string()` {.collection-method}

`string` 方法以給定的長度來建立一個 `VARCHAR` 或相等欄位：

    $table->string('name', length: 100);
<a name="column-method-text"></a>

#### `text()` {.collection-method}

`text` 方法會建立一個 `TEXT` 或相等欄位：

    $table->text('description');
When utilizing MySQL or MariaDB, you may apply a `binary` character set to the column in order to create a `BLOB` equivalent column:

    $table->text('data')->charset('binary'); // BLOB
<a name="column-method-timeTz"></a>

#### `timeTz()` {.collection-method}

The `timeTz` method creates a `TIME` (with timezone) equivalent column with an optional fractional seconds precision:

    $table->timeTz('sunrise', precision: 0);
<a name="column-method-time"></a>

#### `time()` {.collection-method}

The `time` method creates a `TIME` equivalent column with an optional fractional seconds precision:

    $table->time('sunrise', precision: 0);
<a name="column-method-timestampTz"></a>

#### `timestampTz()` {.collection-method}

The `timestampTz` method creates a `TIMESTAMP` (with timezone) equivalent column with an optional fractional seconds precision:

    $table->timestampTz('added_at', precision: 0);
<a name="column-method-timestamp"></a>

#### `timestamp()` {.collection-method}

The `timestamp` method creates a `TIMESTAMP` equivalent column with an optional fractional seconds precision:

    $table->timestamp('added_at', precision: 0);
<a name="column-method-timestampsTz"></a>

#### `timestampsTz()` {.collection-method}

The `timestampsTz` method creates `created_at` and `updated_at` `TIMESTAMP` (with timezone) equivalent columns with an optional fractional seconds precision:

    $table->timestampsTz(precision: 0);
<a name="column-method-timestamps"></a>

#### `timestamps()` {.collection-method}

The `timestamps` method creates `created_at` and `updated_at` `TIMESTAMP` equivalent columns with an optional fractional seconds precision:

    $table->timestamps(precision: 0);
<a name="column-method-tinyIncrements"></a>

#### `tinyIncrements()` {.collection-method}

`tinyIncrements` 方法會建立一個 ^[Auto-Increment](%E8%87%AA%E5%8B%95%E9%81%9E%E5%A2%9E) 的 `UNSIGNED TINYINT` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->tinyIncrements('id');
<a name="column-method-tinyInteger"></a>

#### `tinyInteger()` {.collection-method}

`tinyInteger` 方法建立一個 `TINYINT` 或相等的欄位：

    $table->tinyInteger('votes');
<a name="column-method-tinyText"></a>

#### `tinyText()` {.collection-method}

`tinyText` 方法建立一個 `TINYTEXT` 或相等欄位：

    $table->tinyText('notes');
When utilizing MySQL or MariaDB, you may apply a `binary` character set to the column in order to create a `TINYBLOB` equivalent column:

    $table->tinyText('data')->charset('binary'); // TINYBLOB
<a name="column-method-unsignedBigInteger"></a>

#### `unsignedBigInteger()` {.collection-method}

`unsignedBigInteger` 方法會建立一個 `UNSIGNED BIGINT` 或相等的欄位：

    $table->unsignedBigInteger('votes');
<a name="column-method-unsignedInteger"></a>

#### `unsignedInteger()` {.collection-method}

`unsignedInteger` 方法會建立一個 `UNSIGNED INTEGER` 或相等的欄位：

    $table->unsignedInteger('votes');
<a name="column-method-unsignedMediumInteger"></a>

#### `unsignedMediumInteger()` {.collection-method}

`unsignedMediumInteger` 方法會建立一個 `UNSIGNED MEDIUMINT` 或相等的欄位：

    $table->unsignedMediumInteger('votes');
<a name="column-method-unsignedSmallInteger"></a>

#### `unsignedSmallInteger()` {.collection-method}

`unsignedSmallInteger` 方法會建立一個 `UNSIGNED SMALLINT` 或相等的欄位：

    $table->unsignedSmallInteger('votes');
<a name="column-method-unsignedTinyInteger"></a>

#### `unsignedTinyInteger()` {.collection-method}

`unsignedTinyInteger` 方法會建立一個 `UNSIGNED TINYINT` 或相等的欄位：

    $table->unsignedTinyInteger('votes');
<a name="column-method-ulidMorphs"></a>

#### `ulidMorphs()` {.collection-method}

`ulidMorphs` 是一個方便方法，會新增一個 `{欄位}_id` `CHAR(26)` 或相等欄位，以及一個 `{欄位}_type` `VARCHAR` 或相等欄位。

該方法主要是要給使用 ULID 作為識別字元的多型 [Eloquent 關聯](/docs/{{version}}/eloquent-relationships)定義欄位用的。在下列範例中，會建立 `taggable_id` 與 `taggable_type` 欄位：

    $table->ulidMorphs('taggable');
<a name="column-method-uuidMorphs"></a>

#### `uuidMorphs()` {.collection-method}

`uuidMorphs` 是一個方便方法，會新增一個 `{欄位}_id` `CHAR(36)` 或相等欄位，以及一個 `{欄位}_type` `VARCHAR` 或想等欄位。

該方法主要是要給使用 UUID 作為識別字元的多型 [Eloquent 關聯](/docs/{{version}}/eloquent-relationships)定義欄位用的。在下列範例中，會建立 `taggable_id` 與 `taggable_type` 欄位：

    $table->uuidMorphs('taggable');
<a name="column-method-ulid"></a>

#### `ulid()` {.collection-method}

`ulid` 方法會建立一個 `ULID` 或相等欄位：

    $table->ulid('id');
<a name="column-method-uuid"></a>

#### `uuid()` {.collection-method}

`uuid` 方法會建立一個 `UUID` 或相等欄位：

    $table->uuid('id');
<a name="column-method-vector"></a>

#### `vector()` {.collection-method}

The `vector` method creates a `vector` equivalent column:

    $table->vector('embedding', dimensions: 100);
<a name="column-method-year"></a>

#### `year()` {.collection-method}

`year` 方法會建立一個 `YEAR` 或相等欄位：

    $table->year('birth_year');
<a name="column-modifiers"></a>

### 欄位修飾詞

除了上列欄位型別外，還有多種可在將欄位新增至資料表時使用的欄位「修飾詞」。舉例來說，若要將欄位設為「Nullable」，可使用 `nullable` 方法：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('users', function (Blueprint $table) {
        $table->string('email')->nullable();
    });
下表中包含了所有可用的修飾詞。該列表中未包含[索引修飾詞](#creating-indexes)：

<div class="overflow-auto">
| 修飾詞 | 說明 |
| --- | --- |
| `->after('column')` | Place the column "after" another column (MariaDB / MySQL). |
| `->autoIncrement()` | Set `INTEGER` columns as auto-incrementing (primary key). |
| `->charset('utf8mb4')` | Specify a character set for the column (MariaDB / MySQL). |
| `->collation('utf8mb4_unicode_ci')` | Specify a collation for the column. |
| `->comment('my comment')` | Add a comment to a column (MariaDB / MySQL / PostgreSQL). |
| `->default($value)` | 為欄位指定「^[預設](Default)」值。 |
| `->first()` | Place the column "first" in the table (MariaDB / MySQL). |
| `->from($integer)` | Set the starting value of an auto-incrementing field (MariaDB / MySQL / PostgreSQL). |
| `->invisible()` | Make the column "invisible" to `SELECT *` queries (MariaDB / MySQL). |
| `->nullable($value = true)` | Allow `NULL` values to be inserted into the column. |
| `->storedAs($expression)` | Create a stored generated column (MariaDB / MySQL / PostgreSQL / SQLite). |
| `->unsigned()` | Set `INTEGER` columns as `UNSIGNED` (MariaDB / MySQL). |
| `->useCurrent()` | Set `TIMESTAMP` columns to use `CURRENT_TIMESTAMP` as default value. |
| `->useCurrentOnUpdate()` | Set `TIMESTAMP` columns to use `CURRENT_TIMESTAMP` when a record is updated (MariaDB / MySQL). |
| `->virtualAs($expression)` | Create a virtual generated column (MariaDB / MySQL / SQLite). |
| `->generatedAs($expression)` | 以指定的 ^[Sequence](%E9%A0%86%E5%BA%8F) 選項來建立 Identity 欄位 (PostgreSQL)。 |
| `->always()` | 定義一個優先使用 Sequence 值而不使用輸入值的 Identity 欄位 (PostgreSQL)。 |

</div>
<a name="default-expressions"></a>

#### 預設運算式

`default` 修飾詞可接受 `Illuminate\Database\Query\Expression` 實體。使用 `Expression` 實體時，Laravel 就不會將輸入值包裝在引號內，能讓我們使用資料庫所提供的函式。有一些狀況特別適合使用這個方法，如要給 JSON 欄位指定預設值時：

    <?php
    
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Query\Expression;
    use Illuminate\Database\Migrations\Migration;
    
    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('flights', function (Blueprint $table) {
                $table->id();
                $table->json('movies')->default(new Expression('(JSON_ARRAY())'));
                $table->timestamps();
            });
        }
    };
> [!WARNING]  
> 對於預設運算式的支援程度會因資料庫 Driver、資料庫版本、欄位型別等而有所不同。請參考資料庫的說明文件。

<a name="column-order"></a>

#### 欄位順序

When using the MariaDB or MySQL database, the `after` method may be used to add columns after an existing column in the schema:

    $table->after('password', function (Blueprint $table) {
        $table->string('address_line1');
        $table->string('address_line2');
        $table->string('city');
    });
<a name="modifying-columns"></a>

### 修改欄位

使用 `change` 欄位，即可修改現有欄位的型別或屬性。舉例來說，我們可以用來增加 `string` 欄位的大小。來看看使用 `change` 方法的例子，我們來把 `name` 欄位的大小從 25 增加到 50。若要增加 `name` 欄位的大小，我們只需要定義該欄位的新狀態，然後呼叫 `change` 方法即可：

    Schema::table('users', function (Blueprint $table) {
        $table->string('name', 50)->change();
    });
When modifying a column, you must explicitly include all the modifiers you want to keep on the column definition - any missing attribute will be dropped. For example, to retain the `unsigned`, `default`, and `comment` attributes, you must call each modifier explicitly when changing the column:

    Schema::table('users', function (Blueprint $table) {
        $table->integer('votes')->unsigned()->default(1)->comment('my comment')->change();
    });
The `change` method does not change the indexes of the column. Therefore, you may use index modifiers to explicitly add or drop an index when modifying the column:

```php
// Add an index...
$table->bigIncrements('id')->primary()->change();

// Drop an index...
$table->char('postal_code', 10)->unique(false)->change();
```
<a name="renaming-columns"></a>

### 重新命名欄位

若要重新命名欄位，可使用 Schema Builder 所提供的 `renameColumn` 方法：

    Schema::table('users', function (Blueprint $table) {
        $table->renameColumn('from', 'to');
    });
<a name="dropping-columns"></a>

### 刪除欄位

若要移除欄位，可使用 Schema Builder 所提供的 `dropColumn` 方法：

    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('votes');
    });
可以傳入一組欄位名稱的陣列給 `dropColumn` 方法來一次移除多個欄位：

    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['votes', 'avatar', 'location']);
    });
<a name="available-command-aliases"></a>

#### 可用的指令別名

Laravel 提供了多種可用來刪除常見欄位型別的方便方法。下表中說明了這些方法：

<div class="overflow-auto">
| 指令 | 說明 |
| --- | --- |
| `$table->dropMorphs('morphable');` | 刪除 `morphable_id` 與 `morphable_type` 欄位。 |
| `$table->dropRememberToken();` | 刪除 `remember_token` 欄位。 |
| `$table->dropSoftDeletes();` | 刪除 `deleted_at` 欄位。 |
| `$table->dropSoftDeletesTz();` | `dropSoftDeletes()` 方法的別名。 |
| `$table->dropTimestamps();` | 刪除 `created_at` 與 `updated_at` 欄位。 |
| `$table->dropTimestampsTz();` | `dropTimestamps()` 方法的別名。 |

</div>
<a name="indexes"></a>

## 索引

<a name="creating-indexes"></a>

### 建立索引

Laravel 的 Schema Builder 支援多種類型的索引。下列為一個建立新 `email` 欄位並指定該欄位值^[不可重複](Unique)的範例。若要建立索引，我們可以將 `unique` 方法串聯到欄位定義之後呼叫：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('users', function (Blueprint $table) {
        $table->string('email')->unique();
    });
或者，我們也可以在定義完欄位後再建立索引。若要在定義欄位完後才建立索引，我們需要在 Schema Builder Blueprint 上呼叫 `unique` 方法。該方法的引數為要設為 Unique 索引的欄位名稱：

    $table->unique('email');
我們也可以傳入一組欄位的陣列給索引方法，以建立一個^[複合式](Compound) / ^[組合式](Composite)的索引

    $table->index(['account_id', 'created_at']);
建立索引時，Laravel 會自動依據資料表名稱、欄位名稱、索引類型等來產生索引名稱。不過，我們也可以傳入第二個因數給該方法來自行指定索引名稱：

    $table->unique('email', 'unique_email');
<a name="available-index-types"></a>

#### 可用的索引類型

Laravel 的 Schema Builder Blueprint 為 Laravel 所支援的各種索引類型都提供了方法。每個索引方法都接受可選的第二引數，可用來指定索引的名稱。若未提供索引名稱，則會自動使用索引的資料表名稱、欄位名稱、以及索引型別等來產生索引名稱。下表為可用的索引方法：

<div class="overflow-auto">
| 指令 | 說明 |
| --- | --- |
| `$table->primary('id');` | 新增^[主索引鍵](Primary Key)。 |
| `$table->primary(['id', 'parent_id']);` | 新增^[複合式索引鍵](Composite Keys)。 |
| `$table->unique('email');` | 新增 ^[Unique](%E4%B8%8D%E9%87%8D%E8%A4%87) 索引 |
| `$table->index('state');` | 新增索引。 |
| `$table->fullText('body');` | Adds a full text index (MariaDB / MySQL / PostgreSQL). |
| `$table->fullText('body')->language('english');` | 以指定的語言來新增全文索引 (PostgreSQL)。 |
| `$table->spatialIndex('location');` | 新增 ^[Spatial](%E7%A9%BA%E9%96%93) 索引 (除了 SQLite)。 |

</div>
<a name="renaming-indexes"></a>

### 重新命名索引

若要重新命名索引，可使用 Schema Builder Blueprint 提供的 `renameIndex` 方法。該方法的第一個引數為目前的索引名稱，而第二個引數則為要修改的名稱：

    $table->renameIndex('from', 'to')
<a name="dropping-indexes"></a>

### 刪除索引

若要刪除索引，則需要指定索引的名稱。預設情況下，Laravel 會自動依照資料表名稱、索引欄位名稱、以及索引類型來指派索引名稱。範例如下：

<div class="overflow-auto">
| 指令 | 說明 |
| --- | --- |
| `$table->dropPrimary('users_id_primary');` | 在「users」資料表內刪除^[主索引鍵](Primary Key)。 |
| `$table->dropUnique('users_email_unique');` | 從「users」資料表中刪除 Unique 索引。 |
| `$table->dropIndex('geo_state_index');` | 從「geo」資料表中刪除一般索引。 |
| `$table->dropFullText('posts_body_fulltext');` | 從「users」資料表中刪除全文索引。 |
| `$table->dropSpatialIndex('geo_location_spatialindex');` | Drop a spatial index from the "geo" table  (except SQLite). |

</div>
在刪除索引上時，若傳入一組欄位陣列給該方法，則會自動依照資料表名稱、欄位名稱、索引型別等產生慣例式的索引名稱：

    Schema::table('geo', function (Blueprint $table) {
        $table->dropIndex(['state']); // Drops index 'geo_state_index'
    });
<a name="foreign-key-constraints"></a>

### Foreign Key Constraint

在 Laravel 中，也可以建立 ^[Foreign Key Constraint](%E5%A4%96%E9%83%A8%E7%B4%A2%E5%BC%95%E9%8D%B5%E7%9A%84%E6%A2%9D%E4%BB%B6%E7%B4%84%E6%9D%9F)。使用 Foreigh Key Constraint，就可在資料庫層級上強制確保參照的完整性。舉例來說，我們來在 `posts` 資料表上定義一個參照到 `users` 資料表 `id` 欄位的 `user_id` 欄位：

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    Schema::table('posts', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id');
    
        $table->foreign('user_id')->references('id')->on('users');
    });
由於這個語法有點複雜，因此 Laravel 提供了一個額外的、簡潔的方法。這種方法使用慣例，來提供更好的^[開發者經驗](DX)。在使用 `foreignID` 方法建立欄位時，上方的範例可以被這樣改寫：

    Schema::table('posts', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained();
    });
`foreignId` 方法會建立一個 `UNSIGNED BIGINT` 或相等欄位，而 `constrained` 方法會使用慣例來判斷要參照的資料表與欄位。若表名不符合 Laravel 的慣例，則可以手動將資料庫表名稱提供給 `constrained` 方法。此外，也可以指定產生的索引名稱：

    Schema::table('posts', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained(
            table: 'users', indexName: 'posts_user_id'
        );
    });
也可以指定 Constraint「on delete」與「on update」屬性的動作：

    $table->foreignId('user_id')
          ->constrained()
          ->onUpdate('cascade')
          ->onDelete('cascade');
或者，也有比較描述性的語法可以設定這些動作：

<div class="overflow-auto">
| 方法 | 說明 |
| --- | --- |
| `$table->cascadeOnUpdate();` | 更新時應串聯更新 (Cascade)。 |
| `$table->restrictOnUpdate();` | 更新時應限制更新 (Restricted)。 |
| `$table->nullOnUpdate();` | Updates should set the foreign key value to null. |
| `$table->noActionOnUpdate();` | No action on updates. |
| `$table->cascadeOnDelete();` | 刪除時應串聯刪除 (Cascade)。 |
| `$table->restrictOnDelete();` | 刪除時應限制刪除 (Restricted)。 |
| `$table->nullOnDelete();` | 刪除時應將外部索引鍵設為 null。 |
| `$table->noActionOnDelete();` | Prevents deletes if child records exist. |

</div>
若有額外的[欄位修飾詞](#column-modifiers)，應將其放在 `constrained` 方法前呼叫：

    $table->foreignId('user_id')
          ->nullable()
          ->constrained();
<a name="dropping-foreign-keys"></a>

#### 刪除外部索引鍵

若要刪除 ^[Foreign Key](%E5%A4%96%E9%83%A8%E7%B4%A2%E5%BC%95%E9%8D%B5)，可使用 `dropForeign` 方法，傳入要刪除的 ^[Foreign Key Constraint](%E5%A4%96%E9%83%A8%E7%B4%A2%E5%BC%95%E9%8D%B5%E6%A2%9D%E4%BB%B6%E7%B4%84%E6%9D%9F) 名稱即可。Foreign Key Constraint 使用與索引相同的命名規範。換句話說，Foreign Key Constraint 的名稱會使用要約束的資料表名稱與欄位名稱組成，並在後方加上「_foreign」後置詞：

    $table->dropForeign('posts_user_id_foreign');
或者，也可以傳入一組包含欄位名稱的陣列給 `dropForeign` 方法。這組陣列中應包含 Foreign Key 的名稱。傳入該陣列後，Laravel 會使用約束的命名管理來將該陣列轉換為 Foreign Key Constraint 的名稱：

    $table->dropForeign(['user_id']);
<a name="toggling-foreign-key-constraints"></a>

#### 啟用／禁用 Foreign Key Constraint

可以使用下列方法來在 Migration 中啟用或禁用 Foreign Key Constraint：

    Schema::enableForeignKeyConstraints();
    
    Schema::disableForeignKeyConstraints();
    
    Schema::withoutForeignKeyConstraints(function () {
        // Constraints disabled within this closure...
    });
> [!WARNING]  
> SQLite disables foreign key constraints by default. When using SQLite, make sure to [enable foreign key support](/docs/{{version}}/database#configuration) in your database configuration before attempting to create them in your migrations.

<a name="events"></a>

## Event

為了讓開發更方便，Migration 中的各個動作都會分派 ^[[Event](/docs/{{version}}/events)](事件)。下列的所有 Event 都繼承了 `Illuminate\Database\Events\MigrationEvent` 類別：

<div class="overflow-auto">
| 類別 | 說明 |
| --- | --- |
| `Illuminate\Database\Events\MigrationsStarted` | 即將執行一批 Migration。 |
| `Illuminate\Database\Events\MigrationsEnded` | 已完成執行一批 Migration。 |
| `Illuminate\Database\Events\MigrationStarted` | 即將執行單一 Migration。 |
| `Illuminate\Database\Events\MigrationEnded` | 已完成執行單一 Migration。 |
| `Illuminate\Database\Events\NoPendingMigrations` | A migration command found no pending migrations. |
| `Illuminate\Database\Events\SchemaDumped` | 已傾印完成資料庫結構。 |
| `Illuminate\Database\Events\SchemaLoaded` | 已載入現有的資料庫結構傾印。 |

</div>