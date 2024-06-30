---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/103/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
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

「^[Migration](移轉)」就像是資料表的版本控制一樣，我們能通過 Migration 來定義並與開發團隊共享專案的資料庫結構定義。讀者是否曾經在從版控拉去更新後，還需要告訴同事要手動新增欄位？資料庫 Migration 就是要解決這樣的問題。

Laravel 的 `Schema` [Facade](/docs/{{version}}/facades) 提供了一種可建立或修改資料表的功能，該功能不區分資料，可用在所有 Laravel 支援的資料庫系統上。一般來說，Migration 會使用該 Facade 來建立或修改資料庫資料表與欄位。

<a name="generating-migrations"></a>

## 產生 Migration

我們可以使用 `make:migration` [Artisan 指令](/docs/{{version}}/artisan) 來產生資料庫 Migration。新建立的 Migration 會放在 `database/migrations` 目錄下。各個 Migration 的檔名都包含了一個時戳，用來讓 Laravel 判斷各個 Migration 的執行順序：

```shell
php artisan make:migration create_flights_table
```

Laravel 會使用 Migration 的名稱來嘗試推測資料表的名稱，並嘗試推測該 Migration 是否要建立新資料表。若 Laravel 可判斷檔案名稱，則 Laravel 會預先在產生的 Migration 檔中填入特定的資料表。若無法判斷時，我們只需要在 Migration 檔中手動指定資料表即可。

若想為產生的 Migration 檔指定自訂的路徑，則可在執行 `make:migration` 指令時使用 `--path` 選項。給定的路徑應為相對於專案根目錄的相對路徑。

> **Note** 可以[安裝 Stub](/docs/{{version}}/artisan#stub-customization) 來自訂 Migration 的 Stub。

<a name="squashing-migrations"></a>

### 壓縮 Migration

在我們持續撰寫專案的同時，我們可能會逐漸累積出越來越多的資料庫 Migration 檔。這樣可能會導致 `database/migrations` 目錄中包含了數百個 Migration 檔。若有需要的話，我們可以將 Migration 檔「壓縮」進單一 SQL 檔內。要開始壓縮，請執行 `schema:dump` 指令：

```shell
php artisan schema:dump

# 傾印目前的資料庫結構，並刪除所有現存的 Migration...
php artisan schema:dump --prune
```

執行該指令時，Laravel 會將一個「^[Schema](結構描述)」檔案寫入 `database/schema` 目錄內。Schema 檔案的名稱對影到資料庫連線的名稱。當要移轉資料庫時，若尚未執行過任何 Migration，Laravel 會先執行目前正在使用的資料庫連線所對應 Schema 檔中的 SQL。執行完 Schema 檔內的陳述式後，Laravel 才會接著執行不在該 Schema 傾印中剩下的 Migration。

若專案的測試使用的資料庫連線與本機開發環境所使用的連線不同時，請確認是否有使用該資料庫連線傾印 Schema 檔案，這樣測試中才能正常的建立資料庫。通常這個步驟應放在將開發環境所使用的資料庫連線傾印出來之後：

```shell
php artisan schema:dump
php artisan schema:dump --database=testing --prune
```

請將資料庫 Schema 檔 ^[Commit](簽入) 進版本控制中，好讓團隊中其他的新開發人員可快速建立專案的初始資料庫結構。

> **Warning** Migration 壓縮只支援 MySQL、PostgreSQL、SQLite 等資料庫，且會使用資料庫的主控台用戶端。Schema 傾印無法用來復原 In-Memory 的 SQLite 資料庫。

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

#### 設定 Migration 的連線

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

提供了 `isolated` 選項後，Laravel 會嘗試使用專案的快取 Driver 來取得一個 ^[Atomic Lock](不可部分完成鎖定)，然後再執行 Migration。當 Lock 被鎖住時，嘗試執行 `migrate` 指令就不會執行。不過，這些不被執行的 `migrate` 指令仍然會回傳成功的終止狀態代碼：

```shell
php artisan migrate --isolated
```

> **Warning** 若要使用此功能，則應用程式必須要使用 `memcached`, `redis`, `dynamodb`, `database`, `file` 或 `array` 作為應用程式的預設快取 Driver。另外，所有的伺服器也都必須要連線至相同的中央快取伺服器。

<a name="forcing-migrations-to-run-in-production"></a>

#### 在正式環境中強制執行 Migration

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

`migrate:reset` 指令會復原專案中所有的 Migration：

```shell
php artisan migrate:reset
```

<a name="roll-back-migrate-using-a-single-command"></a>

#### 以單一指令來復原並 Migrate

`migrate:refresh` 指令會將所有的 Migration 都復原回去，並接著執行 `migrate` 指令。使用該指令，就可以有效率的重建整個資料庫：

```shell
php artisan migrate:refresh

# 重新整理資料庫，並執行所有的資料庫 Seed...
php artisan migrate:refresh --seed
```

我們也可以提供各一個 `step` 選項給 `refresh` 指令，以限制要復原並重新 Migrate 的 Migration 數量。舉例來說，下列指令只會復原並重新 Migrate 最後 5 個 Migration：

```shell
php artisan migrate:refresh --step=5
```

<a name="drop-all-tables-migrate"></a>

#### 刪除所有資料表並 Migrate

`migrate:fresh` 指令會刪除資料庫中所有資料表，並接著執行 `migrate` 指令：

```shell
php artisan migrate:fresh

php artisan migrate:fresh --seed
```

> **Warning** 不論資料表是否有前置詞 (Prefix)，`migrate:fresh` 指令會刪除所有的資料庫資料表。在使用與其他專案共享的資料庫時，若要與本指令搭配使用請務必注意。

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

<a name="checking-for-table-column-existence"></a>

#### 檢查資料表與欄位是否存在

我們可以使用 `hasTable` 與 `hasColumn` 方法來檢查資料表或欄位是否存在：

    if (Schema::hasTable('users')) {
        // 「users」資料表存在...
    }
    
    if (Schema::hasColumn('users', 'email')) {
        // 「users」資料表存在，且包含一個「email」欄位...
    }

<a name="database-connection-table-options"></a>

#### 資料庫連線與資料表選項

若要在非專案預設連線的資料庫連線上做 Schema 動作，請使用 `connection` 方法：

    Schema::connection('sqlite')->create('users', function (Blueprint $table) {
        $table->id();
    });

此外，還有一些其他的屬性或方法，可用來調整資料表建立中的其他細節。使用 MySQL 時，可使用 `engine` 屬性來指定資料表的 Storage Engine：

    Schema::create('users', function (Blueprint $table) {
        $table->engine = 'InnoDB';
    
        // ...
    });

使用 MySQL 時，`charset` 與 `collation` 屬性可用來指定建立資料表的 Character Set 與 Collection：

    Schema::create('users', function (Blueprint $table) {
        $table->charset = 'utf8mb4';
        $table->collation = 'utf8mb4_unicode_ci';
    
        // ...
    });

`temporary` 方法可用來表示該資料表是「臨時」資料表。臨時資料表只可在目前連線的資料庫工作階段中使用，且會在連線關閉後自動刪除：

    Schema::create('calculations', function (Blueprint $table) {
        $table->temporary();
    
        // ...
    });

若想在資料表上加入「註解」，可在 Table 實體上叫用 `comment` 方法。資料表註解目前只支援 MySQL 與 Postgres：

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

[bigIncrements](#column-method-bigIncrements) [bigInteger](#column-method-bigInteger) [binary](#column-method-binary) [boolean](#column-method-boolean) [char](#column-method-char) [dateTimeTz](#column-method-dateTimeTz) [dateTime](#column-method-dateTime) [date](#column-method-date) [decimal](#column-method-decimal) [double](#column-method-double) [enum](#column-method-enum) [float](#column-method-float) [foreignId](#column-method-foreignId) [foreignIdFor](#column-method-foreignIdFor) [foreignUlid](#column-method-foreignUlid) [foreignUuid](#column-method-foreignUuid) [geometryCollection](#column-method-geometryCollection) [geometry](#column-method-geometry) [id](#column-method-id) [increments](#column-method-increments) [integer](#column-method-integer) [ipAddress](#column-method-ipAddress) [json](#column-method-json) [jsonb](#column-method-jsonb) [lineString](#column-method-lineString) [longText](#column-method-longText) [macAddress](#column-method-macAddress) [mediumIncrements](#column-method-mediumIncrements) [mediumInteger](#column-method-mediumInteger) [mediumText](#column-method-mediumText) [morphs](#column-method-morphs) [multiLineString](#column-method-multiLineString) [multiPoint](#column-method-multiPoint) [multiPolygon](#column-method-multiPolygon) [nullableMorphs](#column-method-nullableMorphs) [nullableTimestamps](#column-method-nullableTimestamps) [nullableUlidMorphs](#column-method-nullableUlidMorphs) [nullableUuidMorphs](#column-method-nullableUuidMorphs) [point](#column-method-point) [polygon](#column-method-polygon) [rememberToken](#column-method-rememberToken) [set](#column-method-set) [smallIncrements](#column-method-smallIncrements) [smallInteger](#column-method-smallInteger) [softDeletesTz](#column-method-softDeletesTz) [softDeletes](#column-method-softDeletes) [string](#column-method-string) [text](#column-method-text) [timeTz](#column-method-timeTz) [time](#column-method-time) [timestampTz](#column-method-timestampTz) [timestamp](#column-method-timestamp) [timestampsTz](#column-method-timestampsTz) [timestamps](#column-method-timestamps) [tinyIncrements](#column-method-tinyIncrements) [tinyInteger](#column-method-tinyInteger) [tinyText](#column-method-tinyText) [unsignedBigInteger](#column-method-unsignedBigInteger) [unsignedDecimal](#column-method-unsignedDecimal) [unsignedInteger](#column-method-unsignedInteger) [unsignedMediumInteger](#column-method-unsignedMediumInteger) [unsignedSmallInteger](#column-method-unsignedSmallInteger) [unsignedTinyInteger](#column-method-unsignedTinyInteger) [ulidMorphs](#column-method-ulidMorphs) [uuidMorphs](#column-method-uuidMorphs) [ulid](#column-method-ulid) [uuid](#column-method-uuid) [year](#column-method-year)

</div>

<a name="column-method-bigIncrements"></a>

#### `bigIncrements()` {.collection-method .first-collection-method}

`bigIncrements` 方法建立一個 ^[Auto-Increment](自動遞增) 的 `UNSIGNED BIGINT` (^[主索引鍵](Primary Key)) 或相等欄位：

    $table->bigIncrements('id');

<a name="column-method-bigInteger"></a>

#### `bigInteger()` {.collection-method}

`bigInteger` 方法建立一個 `BIGINT` 或相等的欄位：

    $table->bigInteger('votes');

<a name="column-method-binary"></a>

#### `binary()` {.collection-method}

`binary` 方法建立一個 `BLOB` 或相等欄位：

    $table->binary('photo');

<a name="column-method-boolean"></a>

#### `boolean()` {.collection-method}

`boolean` 方法建立一個 `BOOLEAN` 或相等欄位：

    $table->boolean('confirmed');

<a name="column-method-char"></a>

#### `char()` {.collection-method}

`char` 方法以給定的長度來建立一個 `CHAR` 或相等欄位：

    $table->char('name', 100);

<a name="column-method-dateTimeTz"></a>

#### `dateTimeTz()` {.collection-method}

`dateTimeTz` 方法以給定的精度 (總位數) 建立一個 `DATETIME` (含時區) 或相等欄位：

    $table->dateTimeTz('created_at', $precision = 0);

<a name="column-method-dateTime"></a>

#### `dateTime()` {.collection-method}

`dateTime` 方法會使用給定的可選精度 (總位數) 來建立一個 `DATETIME` 或相等欄位：

    $table->dateTime('created_at', $precision = 0);

<a name="column-method-date"></a>

#### `date()` {.collection-method}

`date` 方法會建立一個 `DATE` 或相等欄位：

    $table->date('created_at');

<a name="column-method-decimal"></a>

#### `decimal()` {.collection-method}

`decimal` 方法會以給定的^[精度](Precision) (總位數) 與^[小數位數](Scale) (小數位數) 來建立一個 `DECIMAL` 或相等欄位：

    $table->decimal('amount', $precision = 8, $scale = 2);

<a name="column-method-double"></a>

#### `double()` {.collection-method}

`double` 方法會以給定的^[精度](Precision) (總位數) 與^[小數位數](Scale) (小數位數) 來建立一個 `DOUBLE` 或相等欄位：

    $table->double('amount', 8, 2);

<a name="column-method-enum"></a>

#### `enum()` {.collection-method}

`enum` 方法以給定的有效值來建立一個 `ENUM` 或相等欄位：

    $table->enum('difficulty', ['easy', 'hard']);

<a name="column-method-float"></a>

#### `float()` {.collection-method}

`float` 方法會以給定的^[精度](Precision) (總位數) 與^[小數位數](Scale) (小數位數) 來建立一個 `FLOAT` 或相等欄位：

    $table->float('amount', 8, 2);

<a name="column-method-foreignId"></a>

#### `foreignId()` {.collection-method}

`foreignId` 方法會建立一個 `UNSIGNED BIGINT` 或相等的欄位：

    $table->foreignId('user_id');

<a name="column-method-foreignIdFor"></a>

#### `foreignIdFor()` {.collection-method}

`foreighIdFor` 方法會以給定的 Model 類別來建立一個 `{欄位}_id UNSIGNED BIGINT` 或相等欄位：

    $table->foreignIdFor(User::class);

<a name="column-method-foreignUlid"></a>

#### `foreignUlid()` {.collection-method}

`foreignUlid` 方法會建立一個 `ULID` 或相等欄位：

    $table->foreignUlid('user_id');

<a name="column-method-foreignUuid"></a>

#### `foreignUuid()` {.collection-method}

`foreignUuid` 方法會建立一個 `UUID` 或相等欄位：

    $table->foreignUuid('user_id');

<a name="column-method-geometryCollection"></a>

#### `geometryCollection()` {.collection-method}

`geometryCollection` 方法會建立一個 `GEOMETRYCOLLECTION` 或相等欄位：

    $table->geometryCollection('positions');

<a name="column-method-geometry"></a>

#### `geometry()` {.collection-method}

`geometry` 方法建立一個 `GEOMETRY` 或相等欄位：

    $table->geometry('positions');

<a name="column-method-id"></a>

#### `id()` {.collection-method}

`id` 欄位為 `bigIncrements` 方法的別名。預設情況下，該方法會建立一個 `id` 欄位。不過，若想為該欄位指定不同的名稱，也可以傳入欄位名稱：

    $table->id();

<a name="column-method-increments"></a>

#### `increments()` {.collection-method}

`increments` 方法會建立一個 ^[Auto-Increment](自動遞增) 的 `UNSIGNED INTEGER` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->increments('id');

<a name="column-method-integer"></a>

#### `integer()` {.collection-method}

`integer` 方法建立一個 `INTEGER` 或相等的欄位：

    $table->integer('votes');

<a name="column-method-ipAddress"></a>

#### `ipAddress()` {.collection-method}

`ipAddress` 方法會建立一個 `VARCHAR` 或相等欄位：

    $table->ipAddress('visitor');

<a name="column-method-json"></a>

#### `json()` {.collection-method}

`json` 方法會建立一個 `JSON` 或相等欄位：

    $table->json('options');

<a name="column-method-jsonb"></a>

#### `jsonb()` {.collection-method}

`jsonb` 方法會建立一個 `JSONB` 或相等欄位：

    $table->jsonb('options');

<a name="column-method-lineString"></a>

#### `lineString()` {.collection-method}

`lineString` 方法建立一個 `LINESTRING` 或相等的欄位：

    $table->lineString('positions');

<a name="column-method-longText"></a>

#### `longText()` {.collection-method}

`longText` 方法建立一個 `LONGTEXT` 或相等欄位：

    $table->longText('description');

<a name="column-method-macAddress"></a>

#### `macAddress()` {.collection-method}

`macAddress` 方法會建立一個用來保存 MAC 位址的欄位。在某些資料庫系統，如 PostgreSQL 中，有專門的欄位可用來保存這種類型的資料。在其他資料庫系統，則會使用相等的字串欄位：

    $table->macAddress('device');

<a name="column-method-mediumIncrements"></a>

#### `mediumIncrements()` {.collection-method}

`mediumIncrements` 方法會建立一個 ^[Auto-Increment](自動遞增) 的 `UNSIGNED MEDIUMINT` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->mediumIncrements('id');

<a name="column-method-mediumInteger"></a>

#### `mediumInteger()` {.collection-method}

`mediumInteger` 方法建立一個 `MEDIUMINT` 或相等的欄位：

    $table->mediumInteger('votes');

<a name="column-method-mediumText"></a>

#### `mediumText()` {.collection-method}

`mediumText` 方法建立一個 `MEDIUMTEXT` 或相等的欄位：

    $table->mediumText('description');

<a name="column-method-morphs"></a>

#### `morphs()` {.collection-method}

`morphs` 是一個方便方法，會新增一個 `{欄位}_id` `UNSIGNED BIGINT` 或相等欄位，以及一個 `{欄位}_type` `VARCHAR` 或想等欄位。

該方法主要是要給多型 [Eloquent 關聯](/docs/{{version}}/eloquent-relationships)定義欄位用的。在下列範例中，會建立 `taggable_id` 與 `taggable_type` 欄位：

    $table->morphs('taggable');

<a name="column-method-multiLineString"></a>

#### `multiLineString()` {.collection-method}

`multiLineString` 方法建立一個 `MULTILINESTRING` 或相等的欄位：

    $table->multiLineString('positions');

<a name="column-method-multiPoint"></a>

#### `multiPoint()` {.collection-method}

`multiPoint` 方法建立一個 `MULTIPOINT` 或相等的欄位：

    $table->multiPoint('positions');

<a name="column-method-multiPolygon"></a>

#### `multiPolygon()` {.collection-method}

`multiPolygon` 方法建立一個 `MULTIPOLYGON` 或相等的欄位：

    $table->multiPolygon('positions');

<a name="column-method-nullableTimestamps"></a>

#### `nullableTimestamps()` {.collection-method}

`nullabaleTimestamps` 方法是 [timestamps](#column-method-timestamps) 方法的別名：

    $table->nullableTimestamps(0);

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

<a name="column-method-point"></a>

#### `point()` {.collection-method}

`point` 方法會建立一個 `POINT` 或相等欄位：

    $table->point('position');

<a name="column-method-polygon"></a>

#### `polygon()` {.collection-method}

`polygon` 方法建立一個 `POLYGON` 或相等的欄位：

    $table->polygon('position');

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

`smallIncrements` 方法會建立一個 ^[Auto-Increment](自動遞增) 的 `UNSIGNED SMALLINT` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->smallIncrements('id');

<a name="column-method-smallInteger"></a>

#### `smallInteger()` {.collection-method}

`smallInteger` 方法建立一個 `SMALLINT` 或相等的欄位：

    $table->smallInteger('votes');

<a name="column-method-softDeletesTz"></a>

#### `softDeletesTz()` {.collection-method}

`softDeletesTz` 方法會以給定的可選^[精度](Precision) (總位數) 新增一個 Nullable 的 `deleted_at` `TIMESTAMP` (含時區) 或相等欄位。該欄位主要是給 Eloquent「軟刪除」功能使用的，用來保存 `deleted_at` 時戳：

    $table->softDeletesTz($column = 'deleted_at', $precision = 0);

<a name="column-method-softDeletes"></a>

#### `softDeletes()` {.collection-method}

`softDeletes` 方法會以給定的可選^[精度](Precision) (總位數) 新增一個 Nullable 的 `deleted_at` `TIMESTAMP` 或相等欄位。該欄位主要是給 Eloquent「軟刪除」功能使用的，用來保存 `deleted_at` 時戳：

    $table->softDeletes($column = 'deleted_at', $precision = 0);

<a name="column-method-string"></a>

#### `string()` {.collection-method}

`string` 方法以給定的長度來建立一個 `VARCHAR` 或相等欄位：

    $table->string('name', 100);

<a name="column-method-text"></a>

#### `text()` {.collection-method}

`text` 方法會建立一個 `TEXT` 或相等欄位：

    $table->text('description');

<a name="column-method-timeTz"></a>

#### `timeTz()` {.collection-method}

`timeTz` 方法以給定的精度 (總位數) 建立一個 `TIME` (含時區) 或相等欄位：

    $table->timeTz('sunrise', $precision = 0);

<a name="column-method-time"></a>

#### `time()` {.collection-method}

`time` 方法會使用給定的可選精度 (總位數) 來建立一個 `TIME` 或相等欄位：

    $table->time('sunrise', $precision = 0);

<a name="column-method-timestampTz"></a>

#### `timestampTz()` {.collection-method}

`timestampTz` 方法以給定的精度 (總位數) 建立一個 `TIMESTAMP` (含時區) 或相等欄位：

    $table->timestampTz('added_at', $precision = 0);

<a name="column-method-timestamp"></a>

#### `timestamp()` {.collection-method}

`timestamp` 方法會使用給定的可選精度 (總位數) 來建立一個 `TIMESTAMP` 或相等欄位：

    $table->timestamp('added_at', $precision = 0);

<a name="column-method-timestampsTz"></a>

#### `timestampsTz()` {.collection-method}

`timestampsTz` 方法以給定可選^[精度](Precision) (總位數) 建立 `TIMESTAMP` (含時區) 或相等的 `created_at` 與 `updated_at` 欄位：

    $table->timestampsTz($precision = 0);

<a name="column-method-timestamps"></a>

#### `timestamps()` {.collection-method}

`timestamps` 方法以給定可選^[精度](Precision) (總位數) 建立 `TIMESTAMP` 或相等的 `created_at` 與 `updated_at` 欄位：

    $table->timestamps($precision = 0);

<a name="column-method-tinyIncrements"></a>

#### `tinyIncrements()` {.collection-method}

`tinyIncrements` 方法會建立一個 ^[Auto-Increment](自動遞增) 的 `UNSIGNED TINYINT` 或同等欄位作為^[主索引鍵](Primary Key)：

    $table->tinyIncrements('id');

<a name="column-method-tinyInteger"></a>

#### `tinyInteger()` {.collection-method}

`tinyInteger` 方法建立一個 `TINYINT` 或相等的欄位：

    $table->tinyInteger('votes');

<a name="column-method-tinyText"></a>

#### `tinyText()` {.collection-method}

`tinyText` 方法建立一個 `TINYTEXT` 或相等欄位：

    $table->tinyText('notes');

<a name="column-method-unsignedBigInteger"></a>

#### `unsignedBigInteger()` {.collection-method}

`unsignedBigInteger` 方法會建立一個 `UNSIGNED BIGINT` 或相等的欄位：

    $table->unsignedBigInteger('votes');

<a name="column-method-unsignedDecimal"></a>

#### `unsignedDecimal()` {.collection-method}

`unsignedDecimal` 方法會以給定的可選^[精度](Precision) (總位數) 與^[小數位數](Scale) (小數位數) 來建立一個 `UNSIGNED DECIMAL` 或相等欄位：

    $table->unsignedDecimal('amount', $precision = 8, $scale = 2);

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

| 修飾詞 | 說明 |
| --- | --- |
| `->after('column')` | 將欄位放在另一個欄位「^[之後](After)」(MySQL)。 |
| `->autoIncrement()` | 將 INTEGER 欄位設為 ^[Auto-Increment](自動遞增) (^[主索引鍵](Primary Key))。 |
| `->charset('utf8mb4')` | 指定用於該欄位的 Character Set (MySQL)。 |
| `->collation('utf8mb4_unicode_ci')` | 指定用於該欄位的 Collation (MySQL/PostgreSQL/SQL Server)。 |
| `->comment('my comment')` | 為該欄位新增註解 (MySQL/PostgreSQL)。 |
| `->default($value)` | 為欄位指定「^[預設](Default)」值。 |
| `->first()` | 將欄位放在資料表中的「^[第一個](First)」欄位 (MySQL)。 |
| `->from($integer)` | 設定 ^[Auto-Increment](自動遞增) 欄位的起始值 (MySQL / PostgreSQL)。 |
| `->invisible()` | 讓該欄位在 `SELECT *` 查詢中「^[不可見](Invisible)」(MySQL)。 |
| `->nullable($value = true)` | 允許將 NULL 值插入該欄位中。 |
| `->storedAs($expression)` | 建立一個 Stored Generated 的欄位 (MySQL / PostgreSQL)。 |
| `->unsigned()` | 將 INTEGER 欄位設為 UNSIGNED (MySQL)。 |
| `->useCurrent()` | 設定 TIMESTAMP 欄位使用 CURRENT_TIMESTAMP 作為預設值。 |
| `->useCurrentOnUpdate()` | 在資料更新時，將 TIMESTAMP 欄位設為 CURRENT_TIMESTAMP。 |
| `->virtualAs($expression)` | 建立 Virtual Generated 欄位 (MySQL)。 |
| `->generatedAs($expression)` | 以指定的 ^[Sequence](順序) 選項來建立 Identity 欄位 (PostgreSQL)。 |
| `->always()` | 定義一個優先使用 Sequence 值而不使用輸入值的 Identity 欄位 (PostgreSQL)。 |
| `->isGeometry()` | 將 Spatial 欄位的型別設為 `geometry` —— 即 `geography` 的預設型別 (PostgreSQL)。 |

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

> **Warning** 對於預設運算式的支援程度會因資料庫 Driver、資料庫版本、欄位型別等而有所不同。請參考資料庫的說明文件。

<a name="column-order"></a>

#### 欄位順序

在使用 MySQL 資料庫時，可使用 `after` 方法來將欄位插入到資料表結構中的某個現有欄位之後：

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

修改欄位時，必須明顯包含所有要保留在該欄位上的修飾子 (Modifier)。若有未提供的修飾子，則這些屬性會被移除。舉例來說，若要保留 `unsigned`、`default`、與 `comment` 屬性，則在修改欄位時必須明顯呼叫這幾個修飾子：

    Schema::table('users', function (Blueprint $table) {
        $table->integer('votes')->unsigned()->default(1)->comment('my comment')->change();
    });

<a name="modifying-columns-on-sqlite"></a>

#### 在 SQLite 上修改欄位

若專案使用 SQLite 資料庫，則必須使用 Composer 套件管理員來安裝 `doctrine/dbal` 套件，才可以修改欄位。Doctrine DBAL 函式庫要用來判斷目前欄位的狀態，並用以建立要修改欄位所需要的 SQL 查詢：

    composer require doctrine/dbal

若有需要修改使用 `timestamp` 方法建立的欄位，則必須在 `config/database.php` 設定檔中加上下列設定：

```php
use Illuminate\Database\DBAL\TimestampType;

'dbal' => [
    'types' => [
        'timestamp' => TimestampType::class,
    ],
],
```

> **Warning** 使用 `doctrine/dbal` 套件時，可修改的欄位型別有：bigInteger`, `binary`, `boolean`, `char`, `date`, `dateTime`, `dateTimeTz`, `decimal`, `double`, `integer`, `json`, `longText`, `mediumText`, `smallInteger`, `string`, `text`, `time`, `tinyText`, `unsignedBigInteger`, `unsignedInteger`, `unsignedSmallInteger`, 與 `uuid`。

<a name="renaming-columns"></a>

### 重新命名欄位

若要重新命名欄位，可使用 Schema Builder 所提供的 `renameColumn` 方法：

    Schema::table('users', function (Blueprint $table) {
        $table->renameColumn('from', 'to');
    });

<a name="renaming-columns-on-legacy-databases"></a>

#### 在舊版資料庫中重新命名欄位

若你使用的資料庫版本比下列版本還要老舊，則請確定有在重新命名欄位前使用 Composer 套件管理員安裝 `doctrine/dbal` 函式庫：

<div class="content-list" markdown="1">

- MySQL < `8.0.3`
- MariaDB < `10.5.2`
- SQLite < `3.25.0`

</div>

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

<a name="dropping-columns-on-legacy-databases"></a>

#### 在舊版資料庫中移除欄位

若使用 `3.35.0` 版以前的 SQLite，則必須先使用 Composer 套件管理員安裝 `doctrine/dbal` 套件，才能使用 `dropColumn`。使用這個套件時，不支援在單一 Migration 內移除或修改多個欄位。

<a name="available-command-aliases"></a>

#### 可用的指令別名

Laravel 提供了多種可用來刪除常見欄位型別的方便方法。下表中說明了這些方法：

| 指令 | 說明 |
| --- | --- |
| `$table->dropMorphs('morphable');` | 刪除 `morphable_id` 與 `morphable_type` 欄位。 |
| `$table->dropRememberToken();` | 刪除 `remember_token` 欄位。 |
| `$table->dropSoftDeletes();` | 刪除 `deleted_at` 欄位。 |
| `$table->dropSoftDeletesTz();` | `dropSoftDeletes()` 方法的別名。 |
| `$table->dropTimestamps();` | 刪除 `created_at` 與 `updated_at` 欄位。 |
| `$table->dropTimestampsTz();` | `dropTimestamps()` 方法的別名。 |

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

| 指令 | 說明 |
| --- | --- |
| `$table->primary('id');` | 新增^[主索引鍵](Primary Key)。 |
| `$table->primary(['id', 'parent_id']);` | 新增^[複合式索引鍵](Composite Keys)。 |
| `$table->unique('email');` | 新增 ^[Unique](不重複) 索引 |
| `$table->index('state');` | 新增索引。 |
| `$table->fullText('body');` | 新增全文索引 (MySQL/PostgreSQL)。 |
| `$table->fullText('body')->language('english');` | 以指定的語言來新增全文索引 (PostgreSQL)。 |
| `$table->spatialIndex('location');` | 新增 ^[Spatial](空間) 索引 (除了 SQLite)。 |

<a name="renaming-indexes"></a>

### 重新命名索引

若要重新命名索引，可使用 Schema Builder Blueprint 提供的 `renameIndex` 方法。該方法的第一個引數為目前的索引名稱，而第二個引數則為要修改的名稱：

    $table->renameIndex('from', 'to')

> **Warning** 若專案使用 SQLite 資料庫，則在使用 `renameIndex` 前必須先使用 Composer 套件管理員安裝 `doctrine/dbal` 套件：

<a name="dropping-indexes"></a>

### 刪除索引

若要刪除索引，則需要指定索引的名稱。預設情況下，Laravel 會自動依照資料表名稱、索引欄位名稱、以及索引類型來指派索引名稱。範例如下：

| 指令 | 說明 |
| --- | --- |
| `$table->dropPrimary('users_id_primary');` | 在「users」資料表內刪除^[主索引鍵](Primary Key)。 |
| `$table->dropUnique('users_email_unique');` | 從「users」資料表中刪除 Unique 索引。 |
| `$table->dropIndex('geo_state_index');` | 從「geo」資料表中刪除一般索引。 |
| `$table->dropFullText('posts_body_fulltext');` | 從「users」資料表中刪除全文索引。 |
| `$table->dropSpatialIndex('geo_location_spatialindex');` | 從「geo」資料表中刪除 Spatial 索引 (除了 SQLite)。 |

在刪除索引上時，若傳入一組欄位陣列給該方法，則會自動依照資料表名稱、欄位名稱、索引型別等產生慣例式的索引名稱：

    Schema::table('geo', function (Blueprint $table) {
        $table->dropIndex(['state']); // 刪除 'geo_state_index' 索引
    });

<a name="foreign-key-constraints"></a>

### Foreign Key Constraint

在 Laravel 中，也可以建立 ^[Foreign Key Constraint](外部索引鍵的條件約束)。使用 Foreigh Key Constraint，就可在資料庫層級上強制確保參照的完整性。舉例來說，我們來在 `posts` 資料表上定義一個參照到 `users` 資料表 `id` 欄位的 `user_id` 欄位：

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

`foreignId` 方法會建立一個 `UNSIGNED BIGINT` 或相等欄位，而 `constrained` 方法會使用慣例來判斷要參照的資料表與欄位。若表名不符合 Laravel 的慣例，可在 `constrained` 方法的第二個引數上指定資料表名稱：

    Schema::table('posts', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained('users');
    });

也可以指定 Constraint「on delete」與「on update」屬性的動作：

    $table->foreignId('user_id')
          ->constrained()
          ->onUpdate('cascade')
          ->onDelete('cascade');

或者，也有比較描述性的語法可以設定這些動作：

| 方法 | 說明 |
| --- | --- |
| `$table->cascadeOnUpdate();` | 更新時應串聯更新 (Cascade)。 |
| `$table->restrictOnUpdate();` | 更新時應限制更新 (Restricted)。 |
| `$table->cascadeOnDelete();` | 刪除時應串聯刪除 (Cascade)。 |
| `$table->restrictOnDelete();` | 刪除時應限制刪除 (Restricted)。 |
| `$table->nullOnDelete();` | 刪除時應將外部索引鍵設為 null。 |

若有額外的[欄位修飾詞](#column-modifiers)，應將其放在 `constrained` 方法前呼叫：

    $table->foreignId('user_id')
          ->nullable()
          ->constrained();

<a name="dropping-foreign-keys"></a>

#### 刪除外部索引鍵

若要刪除 ^[Foreign Key](外部索引鍵)，可使用 `dropForeign` 方法，傳入要刪除的 ^[Foreign Key Constraint](外部索引鍵條件約束) 名稱即可。Foreign Key Constraint 使用與索引相同的命名規範。換句話說，Foreign Key Constraint 的名稱會使用要約束的資料表名稱與欄位名稱組成，並在後方加上「_foreign」後置詞：

    $table->dropForeign('posts_user_id_foreign');

或者，也可以傳入一組包含欄位名稱的陣列給 `dropForeign` 方法。這組陣列中應包含 Foreign Key 的名稱。傳入該陣列後，Laravel 會使用約束的命名管理來將該陣列轉換為 Foreign Key Constraint 的名稱：

    $table->dropForeign(['user_id']);

<a name="toggling-foreign-key-constraints"></a>

#### 啟用／禁用 Foreign Key Constraint

可以使用下列方法來在 Migration 中啟用或禁用 Foreign Key Constraint：

    Schema::enableForeignKeyConstraints();
    
    Schema::disableForeignKeyConstraints();
    
    Schema::withoutForeignKeyConstraints(function () {
        // 此閉包內會禁用外部索引鍵條件限制 (Foreign Key Constraint)...
    });

> **Warning** SQLite 預設會禁用 Foreign Key Constraint。使用 SQLite 時，在 Migration 中建立 Foreign Key Constraint 前，請先檢查是否有在資料庫設定中[啟用 Foreign Key 支援](/docs/{{version}}/database#configuration)。此外，SQLite 只支援在建立資料表時設定 Foreign Key，而[無法在修改資料表時新增](https://www.sqlite.org/omitted.html)。

<a name="events"></a>

## Event

為了讓開發更方便，Migration 中的各個動作都會分派 ^[[Event](/docs/{{version}}/events)](事件)。下列的所有 Event 都繼承了 `Illuminate\Database\Events\MigrationEvent` 類別：

| 類別 | 說明 |
| --- | --- |
| `Illuminate\Database\Events\MigrationsStarted` | 即將執行一批 Migration。 |
| `Illuminate\Database\Events\MigrationsEnded` | 已完成執行一批 Migration。 |
| `Illuminate\Database\Events\MigrationStarted` | 即將執行單一 Migration。 |
| `Illuminate\Database\Events\MigrationEnded` | 已完成執行單一 Migration。 |
| `Illuminate\Database\Events\SchemaDumped` | 已傾印完成資料庫結構。 |
| `Illuminate\Database\Events\SchemaLoaded` | 已載入現有的資料庫結構傾印。 |
