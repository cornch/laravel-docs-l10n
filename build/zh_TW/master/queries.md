---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/123/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:15:00Z'
---

# 資料庫：Query Builder

- [簡介](#introduction)
- [執行資料庫查詢](#running-database-queries)
   - [將結果分段](#chunking-results)
   - [延遲的查詢結果資料流](#streaming-results-lazily)
   - [彙總](#aggregates)
- [Select 陳述式](#select-statements)
- [原始陳述式](#raw-expressions)
- [Join](#joins)
- [Union](#unions)
- [基礎的 Where 子句](#basic-where-clauses)
   - [Where 子句](#where-clauses)
   - [Or Where 子句](#or-where-clauses)
   - [Where Not 子句](#where-not-clauses)
   - [JSON Where 子句](#json-where-clauses)
   - [其他 Where 子句](#additional-where-clauses)
   - [邏輯群組](#logical-grouping)
- [進階 Where 子句](#advanced-where-clauses)
   - [Where Exists 子句](#where-exists-clauses)
   - [Subquery Where 子句](#subquery-where-clauses)
   - [全文 Where 子句](#full-text-where-clauses)
- [Order、Group、Limit、Offset](#ordering-grouping-limit-and-offset)
   - [Order](#ordering)
   - [Group](#grouping)
   - [Limit 與 Offset](#limit-and-offset)
- [條件式子句](#conditional-clauses)
- [Insert 陳述式](#insert-statements)
   - [Upsert](#upserts)
- [Update 陳述式](#update-statements)
   - [更新 JSON 欄位](#updating-json-columns)
   - [遞增與遞減](#increment-and-decrement)
- [Delete 陳述式](#delete-statements)
- [悲觀鎖定](#pessimistic-locking)
- [偵錯](#debugging)

<a name="introduction"></a>

## 簡介

Laravel 的資料庫 Query Builder 提供了方便流暢的介面，可用於建立與執行資料庫查詢。Laravel 的 Query Builder 可以在專案中進行各類資料庫動作，且可以在所有 Laravel 支援的資料庫系統上使用。

Laravel 的 Query Builder 使用 PDO 參數繫結來保護網站免於 SQL 注入攻擊。在將字串作為查詢繫結傳入 Query Builder 時，不需要清理或消毒字串。

> **Warning** PDO 不支援繫結欄位名稱。因此，絕對不要在查詢中以使用者輸入的值來參照欄位名稱。「order by」欄位亦同。

<a name="running-database-queries"></a>

## 執行資料庫查詢

<a name="retrieving-all-rows-from-a-table"></a>

#### 從資料表中取得所有欄位

我們可以使用 `DB` Facade 的 `table` 方法來進行查詢。`table` 方法會回傳用於給定資料表的 Fluent Query Builder 實體。使用該實體，我們就能在查詢上串接更多的查詢條件，並在最後使用 `get` 方法來取得查詢的結果：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use Illuminate\Support\Facades\DB;
    use Illuminate\View\View;
    
    class UserController extends Controller
    {
        /**
         * Show a list of all of the application's users.
         */
        public function index(): View
        {
            $users = DB::table('users')->get();
    
            return view('user.index', ['users' => $users]);
        }
    }

`Illuminate\Support\Collection` 實體的 `get` 方法會以 PHP `stdClass` 物件來回傳查詢的結果。要存取各個欄位的值時，我們可以把該物件上的屬性當作欄位來存取：

    use Illuminate\Support\Facades\DB;
    
    $users = DB::table('users')->get();
    
    foreach ($users as $user) {
        echo $user->name;
    }

> **Note**Laravel 的 Collection 提供了許多用於 Map 與 Reduce 資料的超強大功能。有關 Laravel Collection 的更多資訊，請參考 [Collection 說明文件](/docs/{{version}}/collections)。

<a name="retrieving-a-single-row-column-from-a-table"></a>

#### 從資料表中取得單行或單一欄位

若只需要從資料庫資料表中取得單一行的話，可使用 `DB` Facade 的 `first` 方法。該方法只會回傳一個 `stdClass` 物件：

    $user = DB::table('users')->where('name', 'John')->first();
    
    return $user->email;

若不需要整行的資料庫，可以使用 `value` 方法來從一筆記錄中取得單一值。該方法會直接回傳欄位的值：

    $email = DB::table('users')->where('name', 'John')->value('email');

若要使用 `id` 欄位的值來取得某一行，可使用 `find` 方法：

    $user = DB::table('users')->find(3);

<a name="retrieving-a-list-of-column-values"></a>

#### 取得一組欄位值的清單

若想將某個欄位的所有值放在 `Illuminate\Support\Collection` 實體中取得，可使用 `pluck` 方法。在這個範例中，我們會取得一個包含使用者抬頭的 Collection：

    use Illuminate\Support\Facades\DB;
    
    $titles = DB::table('users')->pluck('title');
    
    foreach ($titles as $title) {
        echo $title;
    }

我們也可以提供第二個引數給 `pluck` 來指定要在產生的 Collection 中使用哪個欄位來當作索引鍵：

    $titles = DB::table('users')->pluck('title', 'name');
    
    foreach ($titles as $name => $title) {
        echo $title;
    }

<a name="chunking-results"></a>

### 將查詢結果分段

若要處理上千筆資料，請考慮使用 `DB` Facade 的 `chunk` 方法。該方法一次只會取得一小段資料，並將各個分段的內容傳入閉包中供我們處理。舉例來說，我們來以一次只取 100 筆記錄的方式分段取得整個 `users` 資料表：

    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\DB;
    
    DB::table('users')->orderBy('id')->chunk(100, function (Collection $users) {
        foreach ($users as $user) {
            // ...
        }
    });

我們也可以在閉包中回傳 `false` 來停止處理接下來的分段：

    DB::table('users')->orderBy('id')->chunk(100, function (Collection $users) {
        // 處理資料...
    
        return false;
    });

若要在為結果分段的同時更新資料庫中的資料，則分段的結果可能會不如預期。若要在為查詢結果分段的同時更新所取得的資料，最好該用 `chunkById` 方法。該方法會自動使用資料的主索引鍵來將結果分頁：

    DB::table('users')->where('active', false)
        ->chunkById(100, function (Collection $users) {
            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['active' => true]);
            }
        });

> **Warning** 在分段閉包中更新或刪除資料時，所有對主索引鍵或外部索引鍵所做出的更改都有可能影響分段的資料庫查詢。更新或刪除資料也有可能讓某些資料不被包含在分段的結果中。

<a name="streaming-results-lazily"></a>

### 延遲的查詢結果資料流

`lazy` 方法與 [`chunk` 方法](#chunking-results) 的原理類似，都是以分段的方式執行查詢。不過，`lazy()` 方法不是直接把每個分段傳入回呼中，而是回傳一個 [`LazyCollection`](/docs/{{version}}/collections#lazy-collections)。使用這個 LazyCollection，就可以以單一資料流的方式使用查詢結果：

```php
use Illuminate\Support\Facades\DB;

DB::table('users')->orderBy('id')->lazy()->each(function (object $user) {
    // ...
});
```

一樣，若要在迭代查詢結果的同時更新這些資料的話，最好該用 `lazyById` 或 `lazyByIdDesc` 方法。這些方法會自動使用這些資料的主索引鍵來為查詢結果分頁：

```php
DB::table('users')->where('active', false)
    ->lazyById()->each(function (object $user) {
        DB::table('users')
            ->where('id', $user->id)
            ->update(['active' => true]);
    });
```

> **Warning** 在迭代時更新或刪除資料時，所有對主索引鍵或外部索引鍵所做出的更改都有可能影響分段的資料庫查詢。更新或刪除資料也有可能讓某些資料不被包含查詢結果中。

<a name="aggregates"></a>

### 彙總

Query Builder 還提供了許多用來取得彙總值的方法，如 `count`、`max`、`min`、`avg`、`sum` 等。我們可以在建立查詢時使用這些方法：

    use Illuminate\Support\Facades\DB;
    
    $users = DB::table('users')->count();
    
    $price = DB::table('orders')->max('price');

當然，我們也可以使用其他閉包來組合使用這些方法，以微調這些彙總值的計算方法：

    $price = DB::table('orders')
                    ->where('finalized', 1)
                    ->avg('price');

<a name="determining-if-records-exist"></a>

#### 判斷資料是否存在

我們不需要使用 `count` 來判斷是否有某個符合查詢條件的資料存在，而可以使用 `exists` 與 `doesntExist` 方法：

    if (DB::table('orders')->where('finalized', 1)->exists()) {
        // ...
    }
    
    if (DB::table('orders')->where('finalized', 1)->doesntExist()) {
        // ...
    }

<a name="select-statements"></a>

## Select 陳述式

<a name="specifying-a-select-clause"></a>

#### 指定 Select 子句

我們不是每次都想把資料表上所有的欄位都抓下來。使用 `select` 方法，就可以指定查詢的「select」子句：

    use Illuminate\Support\Facades\DB;
    
    $users = DB::table('users')
                ->select('name', 'email as user_email')
                ->get();

可使用 `distinct` 方法來強制查詢只回傳不重複的結果：

    $users = DB::table('users')->distinct()->get();

若已經有現有的 Query Builder 實體，而想在現有的 select 子句內新增欄位的話，可使用 `addSelect` 方法：

    $query = DB::table('users')->select('name');
    
    $users = $query->addSelect('age')->get();

<a name="raw-expressions"></a>

## Raw 運算式

有時候，我們會需要在查詢中插入任意字串。若要建立 ^[Raw](原始的) 字串運算式，可使用 `DB` Facade 提供的 `raw` 方法：

    $users = DB::table('users')
                 ->select(DB::raw('count(*) as user_count, status'))
                 ->where('status', '<>', 1)
                 ->groupBy('status')
                 ->get();

> **Warning** Raw 陳述式會直接以字串形式插入到查詢中，因此在使用上必須格外小心，以避免 SQL Injection 弱點。

<a name="raw-methods"></a>

### Raw 方法

除了使用 `DB::raw` 方法外，也可以使用下列方法將 Raw 陳述式插入到查詢中的各個部分。**請記得，Laravel 無法保使用 Raw 運算式的查詢有受到避免 SQL Injection 弱點的保護。**

<a name="selectraw"></a>

#### `selectRaw`

可使用 `selectRaw` 來代替使用 `addSelect(DB::raw(/* ... */))`。該方法接受一個可選的第二引數，為一繫結陣列：

    $orders = DB::table('orders')
                    ->selectRaw('price * ? as price_with_tax', [1.0825])
                    ->get();

<a name="whereraw-orwhereraw"></a>

#### `whereRaw / orWhereRaw`

`whereRaw` 與 `orWhereRaw` 方法可用來在查詢中插入 Raw 的「where」子句。這兩個方法的第三個引數為一可選的繫結陣列：

    $orders = DB::table('orders')
                    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
                    ->get();

<a name="havingraw-orhavingraw"></a>

#### `havingRaw / orHavingRaw`

`havingRaw` 與 `orHavingRaw` 方法可用來向「having」子句提供 Raw 字串作為該子句的值。這兩個方法的第三個引數為一可選的繫結陣列：

    $orders = DB::table('orders')
                    ->select('department', DB::raw('SUM(price) as total_sales'))
                    ->groupBy('department')
                    ->havingRaw('SUM(price) > ?', [2500])
                    ->get();

<a name="orderbyraw"></a>

#### `orderByRaw`

`orderByRaw` 方法可用來提供「order by」子句原始字串作為該子句的值：

    $orders = DB::table('orders')
                    ->orderByRaw('updated_at - created_at DESC')
                    ->get();

<a name="groupbyraw"></a>

### `groupByRaw`

`groupByRaw` 方法可用來提供「group by」子句原始字串作為該子句的值：

    $orders = DB::table('orders')
                    ->select('city', 'state')
                    ->groupByRaw('city, state')
                    ->get();

<a name="joins"></a>

## Join

<a name="inner-join-clause"></a>

#### Inner Join 子句

Query Builder 也可用來在查詢內加入 Join 子句。若要使用基本的「Inner Join」，可使用 Query Builder 實體上的 `join` 方法。傳給 `join` 方法的第一個引數是要 Join 的表名，而剩下的引數則為 Join 的欄位條件限制。在單一查詢上可以 Join 多張表：

    use Illuminate\Support\Facades\DB;
    
    $users = DB::table('users')
                ->join('contacts', 'users.id', '=', 'contacts.user_id')
                ->join('orders', 'users.id', '=', 'orders.user_id')
                ->select('users.*', 'contacts.phone', 'orders.price')
                ->get();

<a name="left-join-right-join-clause"></a>

#### Left Join 與 Right Join 子句

若不想新增「Innert Join」，而是想新增「Left Join」或「Right Join」，可使用 `leftJoin` 或 `rightJoin`。這些方法的^[簽章](Signature)與 `join` 方法相同：

    $users = DB::table('users')
                ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
                ->get();
    
    $users = DB::table('users')
                ->rightJoin('posts', 'users.id', '=', 'posts.user_id')
                ->get();

<a name="cross-join-clause"></a>

#### Cross Join 子句

可使用 `crossJoin` 方法來新增「Cross Join」。Cross Join 會產生第一個表與被 Join 表的^[笛卡爾乘積](Cartesian Product)：

    $sizes = DB::table('sizes')
                ->crossJoin('colors')
                ->get();

<a name="advanced-join-clauses"></a>

#### 進階的 Join 子句

我們也可以指定更進階的 Join 子句。若要指定更進階的 Join 子句，`join` 方法的第二個引數請傳入一閉包。該閉包會接收 `Illuminate\Database\Query\JoinClause` 實體，我們可以使用該實體來指定「Join」子句的條件限制：

    DB::table('users')
            ->join('contacts', function (JoinClause $join) {
                $join->on('users.id', '=', 'contacts.user_id')->orOn(/* ... */);
            })
            ->get();

若想在 Join 上使用「Where」子句，可使用 `JoinClause` 實體提供的 `where` 與 `orWhere` 方法。除了直接比較兩個欄位外，也可以使用這些方法將欄位與值相比較：

    DB::table('users')
            ->join('contacts', function (JoinClause $join) {
                $join->on('users.id', '=', 'contacts.user_id')
                     ->where('contacts.user_id', '>', 5);
            })
            ->get();

<a name="subquery-joins"></a>

#### 子查詢的 Join

我們可以使用 `joinSub`、`leftJoinSub`、與 `rightJoinSub` 方法來在查詢中 Join 一個子查詢。這幾個方法都接收三個引數：子查詢、資料表別名、定義關聯欄位的閉包。在這個範例中，我們會取得一組使用者 Collection，其中各個使用者記錄中還包含該使用者最近發佈的部落格貼文之 `updated_at` 時戳：

    $latestPosts = DB::table('posts')
                       ->select('user_id', DB::raw('MAX(created_at) as last_post_created_at'))
                       ->where('is_published', true)
                       ->groupBy('user_id');
    
    $users = DB::table('users')
            ->joinSub($latestPosts, 'latest_posts', function (JoinClause $join) {
                $join->on('users.id', '=', 'latest_posts.user_id');
            })->get();

<a name="unions"></a>

## Union

Laravel 的 Query Builder 還提供了一個可用來「Union」兩個或多個查詢的方便方法。舉例來說，我們可以先建立一個查詢，然後再使用 Union 方法來將該查詢與更多的查詢^[聯集](Union)起來：

    use Illuminate\Support\Facades\DB;
    
    $first = DB::table('users')
                ->whereNull('first_name');
    
    $users = DB::table('users')
                ->whereNull('last_name')
                ->union($first)
                ->get();

除了 `union` 方法外，Laravel 的 Query Builder 還提供了一個 `unionAll` 方法。在使用 `unionAll` 方法結合的查詢中，若有重複記錄，將保留這些重複的記錄。`unionAll` 方法的^[簽章](Signature)與 `union` 方法相同。

<a name="basic-where-clauses"></a>

## 基本的 Where 子句

<a name="where-clauses"></a>

### Where 子句

我們可以使用 Query Builder 的 `where` 方法來將「where」子句加到查詢中。一個 `where` 方法的基本呼叫需要三個引數。第一個引數為欄位名稱，第二個引數為運算子，該運算子可為任何資料庫支援的運算子，第三個印數則為要與欄位值相比較的值。

舉例來說，下列查詢會取得所有 `votes` 欄位等於 `100`、且 `age` 欄位大於 `35` 的使用者：

    $users = DB::table('users')
                    ->where('votes', '=', 100)
                    ->where('age', '>', 35)
                    ->get();

為了方便起見，如果要驗證欄位是否 `=` 給定的值，我們可以直接將該值傳給 `where` 方法的第二個引數。這時，Laravel 會假設要使用的是 `=` 運算子：

    $users = DB::table('users')->where('votes', 100)->get();

與剛才提到的一樣，只要是做使用的資料庫系統所支援的運算子，我們都可以使用：

    $users = DB::table('users')
                    ->where('votes', '>=', 100)
                    ->get();
    
    $users = DB::table('users')
                    ->where('votes', '<>', 100)
                    ->get();
    
    $users = DB::table('users')
                    ->where('name', 'like', 'T%')
                    ->get();

也可以傳入一組條件陣列給 `where` 函式。陣列中的各個元素都應為一個包含三個引數的陣列，這三個引數就是平常傳給 `where` 方法的值：

    $users = DB::table('users')->where([
        ['status', '=', '1'],
        ['subscribed', '<>', '1'],
    ])->get();

> **Warning** PDO 不支援繫結欄位名稱。因此，絕對不要在查詢中以使用者輸入的值來參照欄位名稱。「order by」欄位亦同。

<a name="or-where-clauses"></a>

### Or Where 子句

在串聯呼叫 Query Builder 的 `where` 方法時，「where」子句通常會使用 `and` 運算子串在一起。不過，我們也可以使用 `orWhere` 方法，以使用 `or` 運算子來串聯子句。`orWhere` 方法接受與 `where` 方法相同的引數：

    $users = DB::table('users')
                        ->where('votes', '>', 100)
                        ->orWhere('name', 'John')
                        ->get();

若將「or」條件放入括號中分組，則傳入一個閉包作為 `orWhere` 的第一個引數：

    $users = DB::table('users')
                ->where('votes', '>', 100)
                ->orWhere(function(Builder $query) {
                    $query->where('name', 'Abigail')
                          ->where('votes', '>', 50);
                })
                ->get();

上述範例會產生下列 SQL：

```sql
select * from users where votes > 100 or (name = 'Abigail' and votes > 50)
```

> **Warning** 請總是將 `orWhere` 分組起來，以避免在有套用全域 Scope 時產生未預期的行為。

<a name="where-not-clauses"></a>

### Where Not 子句

`whereNot` 與 `orWhereNot` 方法可用來否定給定的查詢條件群組。舉例來說，下列查詢會排除所有目前為 Clearance (清倉)，且價格 (Price) 小於 10 的商品 (Product)：

    $products = DB::table('products')
                    ->whereNot(function (Builder $query) {
                        $query->where('clearance', true)
                              ->orWhere('price', '<', 10);
                    })
                    ->get();

<a name="json-where-clauses"></a>

### JSON 的 Where 子句

對於有支援 JSON 欄位型別的資料庫，Laravel 也支援查詢 JSON 欄位。目前，支援 JSON 欄位型別的資料庫包含 MySQL 5.7+、PostgreSQL、SQL Server 2016、SQLite 3.39.0 (搭配 [JSON1 擴充程式](https://www.sqlite.org/json1.html)) 等。若要查詢 JSON 欄位，請使用 `->` 運算子：

    $users = DB::table('users')
                    ->where('preferences->dining->meal', 'salad')
                    ->get();

也可以使用 `whereJsonContains` 來查詢 JSON 陣列。3.38.0 版以前的 SQLite 不支援此功能：

    $users = DB::table('users')
                    ->whereJsonContains('options->languages', 'en')
                    ->get();

若專案使用 MySQL 或 PostgreSQL 資料庫，則可遺傳一組陣列值給 `whereJsonContains` 方法：

    $users = DB::table('users')
                    ->whereJsonContains('options->languages', ['en', 'de'])
                    ->get();

也可使用 `whereJsonLength` 方法來以長度查詢 JSON 陣列：

    $users = DB::table('users')
                    ->whereJsonLength('options->languages', 0)
                    ->get();
    
    $users = DB::table('users')
                    ->whereJsonLength('options->languages', '>', 1)
                    ->get();

<a name="additional-where-clauses"></a>

### 額外的 Where 子句

**whereBetween / orWhereBetween**

`whereBetween` 方法檢查某個欄位的值是否介於兩個值之間：

    $users = DB::table('users')
               ->whereBetween('votes', [1, 100])
               ->get();

**whereNotBetween / orWhereNotBetween**

`whereNotBetween` 方法檢查某個欄位的值是否不介於兩個值之間：

    $users = DB::table('users')
                        ->whereNotBetween('votes', [1, 100])
                        ->get();

**whereBetweenColumns / whereNotBetweenColumns / orWhereBetweenColumns / orWhereNotBetweenColumns**

`whereBetweenColumns` 方法會驗證欄位值是否介於資料表中同一行的兩個欄位值之間：

    $patients = DB::table('patients')
                           ->whereBetweenColumns('weight', ['minimum_allowed_weight', 'maximum_allowed_weight'])
                           ->get();

`whereNotBetweenColumns` 方法會驗證欄位值是否不在資料表中同一行的兩個欄位值之間：

    $patients = DB::table('patients')
                           ->whereNotBetweenColumns('weight', ['minimum_allowed_weight', 'maximum_allowed_weight'])
                           ->get();

**whereIn / whereNotIn / orWhereIn / orWhereNotIn**

`whereIn` 方法可檢查給定欄位的值是否包含在給定陣列中：

    $users = DB::table('users')
                        ->whereIn('id', [1, 2, 3])
                        ->get();

`whereNotIn` 方法可檢查給定欄位的值是否不包含在給定陣列中：

    $users = DB::table('users')
                        ->whereNotIn('id', [1, 2, 3])
                        ->get();

也可以提供查詢物件作為 `whereIn` 方法的第二個引數：

    $activeUsers = DB::table('users')->select('id')->where('is_active', 1);
    
    $users = DB::table('comments')
                        ->whereIn('user_id', $activeUsers)
                        ->get();

上述範例會產生下列 SQL：

```sql
select * from comments where user_id in (
    select id
    from users
    where is_active = 1
)
```

> **Warning** 若要在查詢中加上大量的整數陣列，可使用 `whereIntegerInRaw` 與 `whereIntegerNotInRaw` 等方法來有效降低記憶體使用量。

**whereNull / whereNotNull / orWhereNull / orWhereNotNull**

`whereNull` 方法檢查給定欄位的值是否為 `NULL`：

    $users = DB::table('users')
                    ->whereNull('updated_at')
                    ->get();

`whereNotNull` 方法檢查給定欄位的值是否不為 `NULL`：

    $users = DB::table('users')
                    ->whereNotNull('updated_at')
                    ->get();

**whereDate / whereMonth / whereDay / whereYear / whereTime**

`whereDate` 方法可用來將欄位值與特定日期比較：

    $users = DB::table('users')
                    ->whereDate('created_at', '2016-12-31')
                    ->get();

`whereMonth` 方法可用來將欄位值與特定月份比較：

    $users = DB::table('users')
                    ->whereMonth('created_at', '12')
                    ->get();

`whereDay` 方法可用來將欄位值與特定日比較：

    $users = DB::table('users')
                    ->whereDay('created_at', '31')
                    ->get();

`whereYear` 方法可用來將欄位值與特定年份比較：

    $users = DB::table('users')
                    ->whereYear('created_at', '2016')
                    ->get();

`whereTime` 方法可用來將欄位值與特定時間比較：

    $users = DB::table('users')
                    ->whereTime('created_at', '=', '11:20:45')
                    ->get();

**whereColumn / orWhereColumn**

`whereColumn` 方法可用來檢查兩個欄位是否相等：

    $users = DB::table('users')
                    ->whereColumn('first_name', 'last_name')
                    ->get();

也可以穿入比較運算子給 `whereColumn` 方法：

    $users = DB::table('users')
                    ->whereColumn('updated_at', '>', 'created_at')
                    ->get();

也可以穿入一組欄位比較陣列給 `whereColumn` 方法。傳入的條件會使用 `and` 運算子組合起來：

    $users = DB::table('users')
                    ->whereColumn([
                        ['first_name', '=', 'last_name'],
                        ['updated_at', '>', 'created_at'],
                    ])->get();

<a name="logical-grouping"></a>

### 邏輯分組

有時候，我們會需要將多個「where」子句以括號分組起來，好讓我們能以特定的邏輯分組來查詢。其實，一般來說，在使用 `orWhere` 時，大部分情況都應該放在括號中，以避免產生未預期的行為。若要進行邏輯分組，可傳入一個閉包給 `where` 方法：

    $users = DB::table('users')
               ->where('name', '=', 'John')
               ->where(function (Builder $query) {
                   $query->where('votes', '>', 100)
                         ->orWhere('title', '=', 'Admin');
               })
               ->get();

就像這樣，傳入閉包給 `where` 就代表要讓 Query Builder 開啟一個條件限制的分組。該閉包會收到一個 Query Builder 實體，我們可以使用這個實體來在括號分組內設定其中要包含的條件限制。上述範例會產生這樣的 SQL：

```sql
select * from users where name = 'John' and (votes > 100 or title = 'Admin')
```

> **Warning** 請總是將 `orWhere` 分組起來，以避免在有套用全域 Scope 時產生未預期的行為。

<a name="advanced-where-clauses"></a>

### 進階的 Where 子句

<a name="where-exists-clauses"></a>

### Where Exists 子句

使用 `whereExists` 方法，我們就能撰寫「where exists」SQL 子句。`whereExists` 方法接受一個閉包，該閉包會收到一個 Query Builder 實體，我們可以使用該實體來定義要放在「exists」子句內的查詢：

    $users = DB::table('users')
               ->whereExists(function (Builder $query) {
                   $query->select(DB::raw(1))
                         ->from('orders')
                         ->whereColumn('orders.user_id', 'users.id');
               })
               ->get();

或者，除了閉包外，也可以提供一個 Query 物件給 `whereExists` 方法：

    $orders = DB::table('orders')
                    ->select(DB::raw(1))
                    ->whereColumn('orders.user_id', 'users.id');
    
    $users = DB::table('users')
                        ->whereExists($orders)
                        ->get();

上面的兩個範例都會產生下列 SQL：

```sql
select * from users
where exists (
    select 1
    from orders
    where orders.user_id = users.id
)
```

<a name="subquery-where-clauses"></a>

### 子查詢的 Where 子句

有時候，我們會需要製作一種「Where」子句，這種「Where」子句需要將某個子查詢的結果與給定值相比較。這種情況，我們只要穿入一個閉包以及一個值給 `where` 方法即可。舉例來說，下列查詢會取得所有最近的「membership」為給定類型的使用者：

    use App\Models\User;
    use Illuminate\Database\Query\Builder;
    
    $users = User::where(function (Builder $query) {
        $query->select('type')
            ->from('membership')
            ->whereColumn('membership.user_id', 'users.id')
            ->orderByDesc('membership.start_date')
            ->limit(1);
    }, 'Pro')->get();

或者，有時候我們還需要建立將某個欄位與子查詢結果比較的「where」子句。若要將欄位與子查詢的結果比較，請傳入一個欄位名稱、運算子、以及一個閉包給 `where` 方法。舉例來說，下列查詢會取得所有的收入 (Income) 記錄，其中，這些收入記錄的金額 (Amount) 必須小於平均值：

    use App\Models\Income;
    use Illuminate\Database\Query\Builder;
    
    $incomes = Income::where('amount', '<', function (Builder $query) {
        $query->selectRaw('avg(i.amount)')->from('incomes as i');
    })->get();

<a name="full-text-where-clauses"></a>

### 全文 Where 子句

> **Warning** 目前只有 MySQL 與 PostgreSQL 支援全文 Where 子句。

使用 `whereFullText` 與 `orWhereFullText` 方法，就可在查詢中為有[全文索引](/docs/{{version}}/migrations#available-index-types)的欄位加上全文「where」子句。Laravel 會依據底層的資料庫系統將這些方法轉換為適當的 SQL。舉例來說，使用 MySQL 的專案會產生 `MATCH AGAINST` 子句：

    $users = DB::table('users')
               ->whereFullText('bio', 'web developer')
               ->get();

<a name="ordering-grouping-limit-and-offset"></a>

## Order、Group、Limit、Offset

<a name="ordering"></a>

### 排序

<a name="orderby"></a>

#### `orderBy` 方法

使用 `orderBy` 方法，我們就能將查詢的結果以給定欄位來排序。`orderBy` 方法的第一個引數為要排序的欄位，而第二個引數則用來判斷排序的方向，可為 `asc` (升冪) 或 `desc` (降冪)：

    $users = DB::table('users')
                    ->orderBy('name', 'desc')
                    ->get();

若要使用多個欄位來排序，只需要叫用所需次數的 `orderBy` 方法即可：

    $users = DB::table('users')
                    ->orderBy('name', 'desc')
                    ->orderBy('email', 'asc')
                    ->get();

<a name="latest-oldest"></a>

#### `latest` 與 `oldest` 方法

使用 `latest` 與 `oldest` 方法，我們就能輕鬆地以日期來進行排序。預設情況，會使用資料表中的 `created_at` 欄位來排序查詢結果。或者，也可以傳入要用來排序的欄位名稱：

    $user = DB::table('users')
                    ->latest()
                    ->first();

<a name="random-ordering"></a>

#### 隨機排序

使用 `inRandomOrder` 方法，我們就可以使用隨機順序來排序查詢的結果。舉例來說，我們可以使用這個方法來取得某個隨機的使用者：

    $randomUser = DB::table('users')
                    ->inRandomOrder()
                    ->first();

<a name="removing-existing-orderings"></a>

#### 移除現有的排序

`reorder` 方法會移除所有之前已套用到查詢上的「order by」子句：

    $query = DB::table('users')->orderBy('name');
    
    $unorderedUsers = $query->reorder()->get();

在呼叫 `reorder` 方法時也可以傳入欄位名稱與方向。若有傳入欄位名稱與方向，即可移除所有已套用的「order by」子句，並在查詢上套用全新的排序設定：

    $query = DB::table('users')->orderBy('name');
    
    $usersOrderedByEmail = $query->reorder('email', 'desc')->get();

<a name="grouping"></a>

### 分組

<a name="groupby-having"></a>

#### `groupBy` 與 `having` 方法

與方法名稱看起來一樣，`groupBy` 與 `having` 方法可用來為查詢結果分組。`having` 方法的簽章與 `where` 方法的類似：

    $users = DB::table('users')
                    ->groupBy('account_id')
                    ->having('account_id', '>', 100)
                    ->get();

我們也可以使用 `havingBetween` 方法來使用給定的範圍篩選查詢結果：

    $report = DB::table('orders')
                    ->selectRaw('count(id) as number_of_orders, customer_id')
                    ->groupBy('customer_id')
                    ->havingBetween('number_of_orders', [5, 15])
                    ->get();

也可以傳入多個引數給 `groupBy` 方法來分組多個欄位：

    $users = DB::table('users')
                    ->groupBy('first_name', 'status')
                    ->having('account_id', '>', 100)
                    ->get();

若要建立更複雜的 `having` 陳述式，請參考 [`havingRaw`](#raw-methods) 方法。

<a name="limit-and-offset"></a>

### Limit 與 Offset

<a name="skip-take"></a>

#### `skip` 與 `take` 方法

我們可以使用 `skip` 與 `take` 方法來限制查詢所回傳的結果數 (take)，或是在查詢中跳過特定數量的結果 (skip)：

    $users = DB::table('users')->skip(10)->take(5)->get();

或者，我們也可以使用 `limit` 與 `offset` 方法。這兩個方法的功能與 `take` 跟 `skip` 方法相同：

    $users = DB::table('users')
                    ->offset(10)
                    ->limit(5)
                    ->get();

<a name="conditional-clauses"></a>

## 條件式子句

有時候，我們會想依據一些條件來決定是否套用某個查詢子句。舉例來說，我們可能會想只在連入 HTTP Request 中包含給定的輸入值時才套用 `where` 子句。這種情況下，只要使用 `when` 即可：

    $role = $request->string('role');
    
    $users = DB::table('users')
                    ->when($role, function (Builder $query, string $role) {
                        $query->where('role_id', $role);
                    })
                    ->get();

`when` 方法只會在第一個引數為 `true` 時才執行給定的閉包。若第一個引數為 `false`，則將不會執行該閉包。因此，在上述的範例中，只有在 `role` 欄位有出現在連入 Request 中，且取值為 `true` 值，才會叫用傳給 `when` 方法的閉包。

我們也可以傳入另一個閉包給 `when` 方法，作為其第三個引數。只有在第一個引數取值為 `false` 時才會被執行。為了說明使用這個功能的情況，在這裡我們用這個功能來為查詢設定預設的排序：

    $sortByVotes = $request->boolean('sort_by_votes');
    
    $users = DB::table('users')
                    ->when($sortByVotes, function (Builder $query, bool $sortByVotes) {
                        $query->orderBy('votes');
                    }, function (Builder $query) {
                        $query->orderBy('name');
                    })
                    ->get();

<a name="insert-statements"></a>

## Insert 陳述式

Laravel 的 Query Builder 還提供了一個 `insert` 方法，可用來將資料插入到資料表中。`insert` 方法接受一組欄位名稱與值的陣列：

    DB::table('users')->insert([
        'email' => 'kayla@example.com',
        'votes' => 0
    ]);

我們也可以傳入一組陣列的陣列來一次插入多筆記錄。其中，每個陣列都代表了要插入到資料表的一筆資料：

    DB::table('users')->insert([
        ['email' => 'picard@example.com', 'votes' => 0],
        ['email' => 'janeway@example.com', 'votes' => 0],
    ]);

`insertOrIgnore` 方法在將指令插入到資料庫時會忽略錯誤。使用此方法時，請記得，當因資料庫重複而發生錯誤時，該錯誤會被忽略，而依據資料庫引擎的不同，也有可能會忽略其他類型的錯誤。舉例來說，`insertOrIgnore` 會[忽略 MySQL 的 嚴格模式 (Strict Mode)](https://dev.mysql.com/doc/refman/en/sql-mode.html#ignore-effect-on-execution)：

    DB::table('users')->insertOrIgnore([
        ['id' => 1, 'email' => 'sisko@example.com'],
        ['id' => 2, 'email' => 'archer@example.com'],
    ]);

`insertUsing` 方法會使用子查詢來判斷是否應插入該資料，然後在將新資料插入到資料表中：

    DB::table('pruned_users')->insertUsing([
        'id', 'name', 'email', 'email_verified_at'
    ], DB::table('users')->select(
        'id', 'name', 'email', 'email_verified_at'
    )->where('updated_at', '<=', now()->subMonth()));

<a name="auto-incrementing-ids"></a>

#### ^[Auto-Increment](自動遞增) 的 ID

若資料表有 Auto-Increment 的 ID，則可使用 `insertGetId` 方法來插入一筆資料，並取得該 ID：

    $id = DB::table('users')->insertGetId(
        ['email' => 'john@example.com', 'votes' => 0]
    );

> **Warning** 使用 PostgreSQL 時，`insertGetId` 方法預設 Auto-Increment 的欄位名稱為 `id`。若想從不同的「^[Sequence](序列)」中取得 ID，則請傳入欄位名稱給 `insertGetId` 方法的第二個因數。

<a name="upserts"></a>

### Upsert

當指定的記錄不存在時，`upsert` 方法會插入該筆記錄；若記錄已存在時，則會以指定的值來更新現有記錄。該方法的第一個引數為要插入或更新的值，而第二個引數則是一組用來判斷給定記錄在資料表中是否為^[不重複](Unique)記錄的欄位名稱。該方法的第三個與最後一個引數為一組欄位名稱的陣列，當在資料庫中找到符合的記錄時，會更新資料庫記錄：

    DB::table('flights')->upsert(
        [
            ['departure' => 'Oakland', 'destination' => 'San Diego', 'price' => 99],
            ['departure' => 'Chicago', 'destination' => 'New York', 'price' => 150]
        ],
        ['departure', 'destination'],
        ['price']
    );

在上述範例中，Laravel 會嘗試插入量比記錄。若資料庫中已有相同的 `depature` 與 `destination` 欄位值，則 Laravel 會更新該筆資料的 `price` 欄位。

> **Warning** 除了 SQL Server 以外，所有的資料庫都要求 `upsert` 方法第二個引數中的欄位必須有「Primary」或「Unique」索引。此外，MySQL 資料庫 Driver 會忽略 `upsert` 方法的第二個引數，該 Driver 只會使用該資料表的「Primary」與「Unique」索引來判斷現有的記錄。

<a name="update-statements"></a>

## Update 陳述式

除了將資料插入資料庫外，在 Laravel 的 Query Builder 中，也可以使用 `update` 方法來更新現有的資料。`update` 方法與 `insert` 方法類似，接受一組欄位／值配對的陣列，用來代表要更新的欄位。`update` 方法會回傳受影響的行數。我們可以使用 `where` 子句來對 `update` 查詢做條件限制：

    $affected = DB::table('users')
                  ->where('id', 1)
                  ->update(['votes' => 1]);

<a name="update-or-insert"></a>

#### Update Or Insert

有時候，我們會想更新資料庫內現有的資料，但如果資料庫中還沒有這筆資料的話，就建立一筆新的資料。這時，可以使用 `updateOrInsert` 方法。`updateOrInsert` 方法接受兩個因數：一組用來尋找資料的陣列，以及一組用來表示欄位更新的欄位／值配對陣列。

`updateOrInsert` 方法會試著使用第一個引數的欄位／值配對來找到符合的資料。若有找到資料，則 Query Builder 會使用第二個引數內的值來更新該資料；若找不到資料，則會將這兩個引數合併，並插入到資料庫中：

    DB::table('users')
        ->updateOrInsert(
            ['email' => 'john@example.com', 'name' => 'John'],
            ['votes' => '2']
        );

<a name="updating-json-columns"></a>

### 更新 JSON 欄位

在更新 JSON 欄位時，應使用 `->` 格式來更新 JSON 物件中對應的索引鍵。更新 JSON 物件索引鍵支援 MySQL 5.7 版以上與 PostgreSQL 9.5 版以上：

    $affected = DB::table('users')
                  ->where('id', 1)
                  ->update(['options->enabled' => true]);

<a name="increment-and-decrement"></a>

### 遞增與遞減

Laravel 的 Query Builder 還提供了用來遞增與遞減給定欄位值的方便方法。這幾個方法都接受至少一個引數：要修改的欄位名稱。也可以提供第二個引數，來指定該欄位要遞增或遞減多少：

    DB::table('users')->increment('votes');
    
    DB::table('users')->increment('votes', 5);
    
    DB::table('users')->decrement('votes');
    
    DB::table('users')->decrement('votes', 5);

若有需要，可以在進行遞增或遞減時指定額外的欄位：

    DB::table('users')->increment('votes', 1, ['name' => 'John']);

此外，也可以使用 `incrementEach` 與 `decrementEach` 方法來同時遞增或遞減多個欄位：

    DB::table('users')->incrementEach([
        'votes' => 5,
        'balance' => 100,
    ]);

<a name="delete-statements"></a>

## Delete 陳述式

在 Laravel 的 Query Builder 中，可使用 `delete` 方法來將資料從資料表中刪除。`delete` 方法會回傳受影響的行數。我們可以在呼叫 `delete` 方法前新增「where」子句來對 `delete` 陳述式做條件限制：

    $deleted = DB::table('users')->delete();
    
    $deleted = DB::table('users')->where('votes', '>', 100)->delete();

若想 ^[Truncate](截斷) 整張資料表，也就是從資料表中移除所有資料，並將 Auto-Increment 的 ID 重設為 0，則可使用 `truncate` 方法：

    DB::table('users')->truncate();

<a name="table-truncation-and-postgresql"></a>

#### Truncate 資料表與 PostgreSQL

在 PostgreSQL 資料庫中 Truncate 資料表時，會套用 `CASCADE` 行為。這表示，這張資料表中若有與其他資料表使用外部索引鍵關聯，則其他資料表上的資料也會被刪除。

<a name="pessimistic-locking"></a>

## 悲觀鎖定

Laravel 的 Query Builder 中，還包含了一些能讓我們在執行 `select` 陳述式時進行「^[悲觀鎖定](Pessimistic Locking)」的功能。若要以「Shared Lock」執行陳述式，可以呼叫 `sharedLock` 方法。使用 Shared Lock 可防止 Select 陳述式所取得的資料在 Transaction 被 Commit 前都不被修改：

    DB::table('users')
            ->where('votes', '>', 100)
            ->sharedLock()
            ->get();

或者，我們也可以使用 `lockForUpdate` 方法。「For Update」Lock 可防止 Select 陳述式所取得的資料被修改，並且讓其他 Shared Lock 無法 Select 該資料：

    DB::table('users')
            ->where('votes', '>', 100)
            ->lockForUpdate()
            ->get();

<a name="debugging"></a>

## 偵錯

在建立查詢時，可以使用 `dd` 與 `dump` 方法來將目前的查詢繫結於 SQL 傾印出來。`dd` 方法會顯示偵錯資訊，然後停止執行該 Request。`dump` 方法會顯示出偵錯資訊，並讓 Request 繼續執行：

    DB::table('users')->where('votes', '>', 100)->dd();
    
    DB::table('users')->where('votes', '>', 100)->dump();
