---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/25/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:17:00Z'
---

# Collection

- [簡介](#introduction)
   - [建立 Collection](#creating-collections)
   - [擴充 Collection](#extending-collections)
- [可用方法](#available-methods)
- [高階訊息 (Higher Order Message)](#higher-order-messages)
- [Lazy Collection](#lazy-collections)
   - [簡介](#lazy-collection-introduction)
   - [建立 Lazy Collection](#creating-lazy-collections)
   - [Enumerable Contract](#the-enumerable-contract)
   - [Lazy Collection 方法](#lazy-collection-methods)

<a name="introduction"></a>

## 簡介

`Illuminate\Support\Collection` 類別為處理陣列資料提供了一個流暢且便利的包裝。舉例來說，看看下列程式碼。我們會使用 `collect` 輔助函式來自陣列建立一個新的 Collection 實體，然後在每個元素上執行 `strtoupper` 函式，並移除所有空元素：

    $collection = collect(['taylor', 'abigail', null])->map(function ($name) {
        return strtoupper($name);
    })->reject(function ($name) {
        return empty($name);
    });

如你所見，`Collection` 類別能讓你將其方法串在一起呼叫，以流暢地在底層的陣列上進行 Map 與 Reduce 處理。通常來說，Collection 是不可變的（Immutable），這代表每個 `Collection` 方法都會回傳一個全新的 `Collection` 實體。

<a name="creating-collections"></a>

### 建立 Collection

就像上面提到的一樣，`collect` 輔助函式會為給定的陣列回傳一個新的 `Illuminate\Support\Collection` 實體。因此，建立 Collection 就這麼簡單：

    $collection = collect([1, 2, 3]);

> **Note** [Eloquent](/docs/{{version}}/eloquent) 查詢的結果總會回傳為 `Collection` 實體。

<a name="extending-collections"></a>

### 擴充 Collection

Collection 是「Macroable (可巨集)」的，這代表我們可以在執行階段往 `Collection` 增加額外的方法。`Illuminate\Support\Collection` 類別的 `macro` 方法接收一個閉包，該閉包會在 Macro 被呼叫時執行。Macro 閉包也能像真正的 Collection 類別方法一樣，通過 `$this` 來存取該 Collection 的其他方法。舉例來說，下列程式碼會往 `Collection` 類別內新增一個 `toUpper` 方法：

    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    
    Collection::macro('toUpper', function () {
        return $this->map(function ($value) {
            return Str::upper($value);
        });
    });
    
    $collection = collect(['first', 'second']);
    
    $upper = $collection->toUpper();
    
    // ['FIRST', 'SECOND']

一般來說，Collection Macro 的宣告應放置於某個 [Service Provider](/docs/{{version}}/providers) 的 `boot` 方法內。

<a name="macro-arguments"></a>

#### Macro 引數

若有需要，也可以定義接受額外引數的 Macro：

    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Lang;
    
    Collection::macro('toLocale', function ($locale) {
        return $this->map(function ($value) use ($locale) {
            return Lang::get($value, [], $locale);
        });
    });
    
    $collection = collect(['first', 'second']);
    
    $translated = $collection->toLocale('es');

<a name="available-methods"></a>

## 可用方法

在 Collection 說明文件剩下的一大部分，我們會討論 `Collection` 類別內可用的各個方法。請記住，這裡所有的方法都可以互相串接使用，以流利地操作底層的陣列。此外，幾乎所有的函式都會回傳一個新的 `Collection` 實體，讓你可以在有需要的時候保留原始的 Collection 拷貝：

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
</style>

<div class="collection-method-list" markdown="1">

[all](#method-all) [average](#method-average) [avg](#method-avg) [chunk](#method-chunk) [chunkWhile](#method-chunkwhile) [collapse](#method-collapse) [collect](#method-collect) [combine](#method-combine) [concat](#method-concat) [contains](#method-contains) [containsOneItem](#method-containsoneitem) [containsStrict](#method-containsstrict) [count](#method-count) [countBy](#method-countBy) [crossJoin](#method-crossjoin) [dd](#method-dd) [diff](#method-diff) [diffAssoc](#method-diffassoc) [diffKeys](#method-diffkeys) [doesntContain](#method-doesntcontain) [dump](#method-dump) [duplicates](#method-duplicates) [duplicatesStrict](#method-duplicatesstrict) [each](#method-each) [eachSpread](#method-eachspread) [every](#method-every) [except](#method-except) [filter](#method-filter) [first](#method-first) [firstOrFail](#method-first-or-fail) [firstWhere](#method-first-where) [flatMap](#method-flatmap) [flatten](#method-flatten) [flip](#method-flip) [forget](#method-forget) [forPage](#method-forpage) [get](#method-get) [groupBy](#method-groupby) [has](#method-has) [hasAny](#method-hasany) [implode](#method-implode) [intersect](#method-intersect) [intersectByKeys](#method-intersectbykeys) [isEmpty](#method-isempty) [isNotEmpty](#method-isnotempty) [join](#method-join) [keyBy](#method-keyby) [keys](#method-keys) [last](#method-last) [lazy](#method-lazy) [macro](#method-macro) [make](#method-make) [map](#method-map) [mapInto](#method-mapinto) [mapSpread](#method-mapspread) [mapToGroups](#method-maptogroups) [mapWithKeys](#method-mapwithkeys) [max](#method-max) [median](#method-median) [merge](#method-merge) [mergeRecursive](#method-mergerecursive) [min](#method-min) [mode](#method-mode) [nth](#method-nth) [only](#method-only) [pad](#method-pad) [partition](#method-partition) [pipe](#method-pipe) [pipeInto](#method-pipeinto) [pipeThrough](#method-pipethrough) [pluck](#method-pluck) [pop](#method-pop) [prepend](#method-prepend) [pull](#method-pull) [push](#method-push) [put](#method-put) [random](#method-random) [range](#method-range) [reduce](#method-reduce) [reduceSpread](#method-reduce-spread) [reject](#method-reject) [replace](#method-replace) [replaceRecursive](#method-replacerecursive) [reverse](#method-reverse) [search](#method-search) [shift](#method-shift) [shuffle](#method-shuffle) [skip](#method-skip) [skipUntil](#method-skipuntil) [skipWhile](#method-skipwhile) [slice](#method-slice) [sliding](#method-sliding) [sole](#method-sole) [some](#method-some) [sort](#method-sort) [sortBy](#method-sortby) [sortByDesc](#method-sortbydesc) [sortDesc](#method-sortdesc) [sortKeys](#method-sortkeys) [sortKeysDesc](#method-sortkeysdesc) [sortKeysUsing](#method-sortkeysusing) [splice](#method-splice) [split](#method-split) [splitIn](#method-splitin) [sum](#method-sum) [take](#method-take) [takeUntil](#method-takeuntil) [takeWhile](#method-takewhile) [tap](#method-tap) [times](#method-times) [toArray](#method-toarray) [toJson](#method-tojson) [transform](#method-transform) [undot](#method-undot) [union](#method-union) [unique](#method-unique) [uniqueStrict](#method-uniquestrict) [unless](#method-unless) [unlessEmpty](#method-unlessempty) [unlessNotEmpty](#method-unlessnotempty) [unwrap](#method-unwrap) [value](#method-value) [values](#method-values) [when](#method-when) [whenEmpty](#method-whenempty) [whenNotEmpty](#method-whennotempty) [where](#method-where) [whereStrict](#method-wherestrict) [whereBetween](#method-wherebetween) [whereIn](#method-wherein) [whereInStrict](#method-whereinstrict) [whereInstanceOf](#method-whereinstanceof) [whereNotBetween](#method-wherenotbetween) [whereNotIn](#method-wherenotin) [whereNotInStrict](#method-wherenotinstrict) [whereNotNull](#method-wherenotnull) [whereNull](#method-wherenull) [wrap](#method-wrap) [zip](#method-zip)

</div>

<a name="method-listing"></a>

## 方法列表

<style>
    .collection-method code {
        font-size: 14px;
    }

    .collection-method:not(.first-collection-method) {
        margin-top: 50px;
    }
</style>

<a name="method-all"></a>

#### `all()` {.collection-method .first-collection-method}

`all` 方法會回傳該 Collection 所代表的底層陣列：

    collect([1, 2, 3])->all();
    
    // [1, 2, 3]

<a name="method-average"></a>

#### `average()` {.collection-method}

[`avg`](#method-avg) 方法的別名。

<a name="method-avg"></a>

#### `avg()` {.collection-method}

`avg` 方法會回傳給定索引鍵的[平均值](https://zh.wikipedia.org/zh-tw/%E5%B9%B3%E5%9D%87%E6%95%B0)：

    $average = collect([
        ['foo' => 10],
        ['foo' => 10],
        ['foo' => 20],
        ['foo' => 40]
    ])->avg('foo');
    
    // 20
    
    $average = collect([1, 1, 2, 4])->avg();
    
    // 2

<a name="method-chunk"></a>

#### `chunk()` {.collection-method}

`chunk` 方法會將該 Collection 以給定的大小拆分為多個較小的 Collection：

    $collection = collect([1, 2, 3, 4, 5, 6, 7]);
    
    $chunks = $collection->chunk(4);
    
    $chunks->all();
    
    // [[1, 2, 3, 4], [5, 6, 7]]

此方法特別適合用在 [View](/docs/{{version}}/views) 內，配合如 [Bootstrap](https://getbootstrap.com/docs/4.1/layout/grid/) 等網格系統一起使用。舉例來說，假設我們有一個 [Eloquent](/docs/{{version}}/eloquent) 模型的 Collection 想要在網格上顯示：

```blade
@foreach ($products->chunk(3) as $chunk)
    <div class="row">
        @foreach ($chunk as $product)
            <div class="col-xs-4">{{ $product->name }}</div>
        @endforeach
    </div>
@endforeach
```

<a name="method-chunkwhile"></a>

#### `chunkWhile()` {.collection-method}

`chunkWhile` 方法會依照給定回呼的取值結果來將 Collection 拆分為多個更小的 Collection。傳入閉包的 `$chunk` 變數可用來檢視上一個元素：

    $collection = collect(str_split('AABBCCCD'));
    
    $chunks = $collection->chunkWhile(function ($value, $key, $chunk) {
        return $value === $chunk->last();
    });
    
    $chunks->all();
    
    // [['A', 'A'], ['B', 'B'], ['C', 'C', 'C'], ['D']]

<a name="method-collapse"></a>

#### `collapse()` {.collection-method}

`collapse` 方法可以將多個陣列合併為單一的扁平 (Flat) Collection：

    $collection = collect([
        [1, 2, 3],
        [4, 5, 6],
        [7, 8, 9],
    ]);
    
    $collapsed = $collection->collapse();
    
    $collapsed->all();
    
    // [1, 2, 3, 4, 5, 6, 7, 8, 9]

<a name="method-collect"></a>

#### `collect()` {.collection-method}

`collect` 方法會回傳一個包含目前 Collection 內項目的新 `Collection` 實體：

    $collectionA = collect([1, 2, 3]);
    
    $collectionB = $collectionA->collect();
    
    $collectionB->all();
    
    // [1, 2, 3]

`collect` 方法特別適合用來將 [Lazy Collection](#lazy-collections) 轉換為標準 `Collection` 實體：

    $lazyCollection = LazyCollection::make(function () {
        yield 1;
        yield 2;
        yield 3;
    });
    
    $collection = $lazyCollection->collect();
    
    get_class($collection);
    
    // 'Illuminate\Support\Collection'
    
    $collection->all();
    
    // [1, 2, 3]

> **Note** `collect` 方法特別適合用於如有 `Enumerable` 實體且需要一個非 Lazy Collection 的實體。由於 `collect()` 是 `Enumerable` Contract 的一部分，因此我們可以安全地使用該方法來取得 `Collection` 實體。

<a name="method-combine"></a>

#### `combine()` {.collection-method}

`combine` 可用來將 Collection 的值作為索引鍵來與作為值的另一個陣列或 Collection 進行合併：

    $collection = collect(['name', 'age']);
    
    $combined = $collection->combine(['George', 29]);
    
    $combined->all();
    
    // ['name' => 'George', 'age' => 29]

<a name="method-concat"></a>

#### `concat()` {.collection-method}

`concat` 方法會將給定的 `array` 或 Collection 的值附加到另一個 Collection 的末端：

    $collection = collect(['John Doe']);
    
    $concatenated = $collection->concat(['Jane Doe'])->concat(['name' => 'Johnny Doe']);
    
    $concatenated->all();
    
    // ['John Doe', 'Jane Doe', 'Johnny Doe']

`concat` 方法將各個項目串接到原始 Collection 陣列中，而串接的各個項目會依照數字順序重新設定索引鍵。若要保留關聯式 Collection 的索引鍵，請參照 [merge](#method-merge) 方法。

<a name="method-contains"></a>

#### `contains()` {.collection-method}

`contains` 方法可用來判斷該 Collection 是否包含給定的項目。可以傳入一個閉包給 `contains` 方法來根據給定的真值條件測試判斷某個元素是否在該 Collection 內：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->contains(function ($value, $key) {
        return $value > 5;
    });
    
    // false

或者，也可以將字串傳入 `contains` 方法來判斷該 Collection 是否包含給定的項目值：

    $collection = collect(['name' => 'Desk', 'price' => 100]);
    
    $collection->contains('Desk');
    
    // true
    
    $collection->contains('New York');
    
    // false

也可以傳入一組索引鍵／值配對給 `contains` 方法，用來判斷給定的索引鍵／值配對是否存在於該 Collection 內：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 100],
    ]);
    
    $collection->contains('product', 'Bookcase');
    
    // false

`contains` 方法在比對項目值時使用了「鬆散 (Loose)」的比對方法。這標示，具有整數值的字串與一個有相同值的整數會被視為相同。請使用 [`containsStrict`](#method-containsstrict) 方法來使用「嚴格 (Strict)」比對進行過濾。

請參考 [doesntContain](#method-doesntcontain) 方法以瞭解與 `contains` 相反的方法。

<a name="method-containsoneitem"></a>

#### `containsOneItem()` {.collection-method}

`containsOneItem` 用於判斷該 Collection 是否只包含一個項目：

    collect([])->containsOneItem();
    
    // false
    
    collect(['1'])->containsOneItem();
    
    // true
    
    collect(['1', '2'])->containsOneItem();
    
    // false

<a name="method-containsstrict"></a>

#### `containsStrict()` {.collection-method}

該方法與 [`contains`](#method-contains) 方法的簽章一致。不過，所有的數值比對都是使用「嚴格」比對模式。

> **Note** 在使用 [Eloquent Collection](/docs/{{version}}/eloquent-collections#method-contains) 時，該方法的行為有經過修改。

<a name="method-count"></a>

#### `count()` {.collection-method}

`count` 方法回傳該 Collection 內的項目總數：

    $collection = collect([1, 2, 3, 4]);
    
    $collection->count();
    
    // 4

<a name="method-countBy"></a>

#### `countBy()` {.collection-method}

`countBy` 方法會計算在該 Collection 內各個值的出現次數。預設情況下，該方法會計算所有元素的出現次數，讓你可以計算該 Collection 中特定「類型」的元素：

    $collection = collect([1, 2, 2, 2, 3]);
    
    $counted = $collection->countBy();
    
    $counted->all();
    
    // [1 => 1, 2 => 3, 3 => 1]

可以將一個閉包傳給 `countBy` 方法來依照自訂值計算所有項目：

    $collection = collect(['alice@gmail.com', 'bob@yahoo.com', 'carlos@gmail.com']);
    
    $counted = $collection->countBy(function ($email) {
        return substr(strrchr($email, "@"), 1);
    });
    
    $counted->all();
    
    // ['gmail.com' => 2, 'yahoo.com' => 1]

<a name="method-crossjoin"></a>

#### `crossJoin()` {.collection-method}

The `crossJoin` method cross joins the collection's values among the given arrays or collections, returning a Cartesian product with all possible permutations:

    $collection = collect([1, 2]);
    
    $matrix = $collection->crossJoin(['a', 'b']);
    
    $matrix->all();
    
    /*
        [
            [1, 'a'],
            [1, 'b'],
            [2, 'a'],
            [2, 'b'],
        ]
    */
    
    $collection = collect([1, 2]);
    
    $matrix = $collection->crossJoin(['a', 'b'], ['I', 'II']);
    
    $matrix->all();
    
    /*
        [
            [1, 'a', 'I'],
            [1, 'a', 'II'],
            [1, 'b', 'I'],
            [1, 'b', 'II'],
            [2, 'a', 'I'],
            [2, 'a', 'II'],
            [2, 'b', 'I'],
            [2, 'b', 'II'],
        ]
    */

<a name="method-dd"></a>

#### `dd()` {.collection-method}

`dd` 方法會傾印該 Collection 的項目，並結束目前的指令碼執行：

    $collection = collect(['John Doe', 'Jane Doe']);
    
    $collection->dd();
    
    /*
        Collection {
            #items: array:2 [
                0 => "John Doe"
                1 => "Jane Doe"
            ]
        }
    */

若不想結束目前的指令碼執行，請改為使用 [`dump`](#method-dump) 方法。

<a name="method-diff"></a>

#### `diff()` {.collection-method}

`diff` 方法會將該 Collection 的值與另一個 Collection 或純 PHP `array` 陣列進行比對。該方法會回傳在原始 Collection 中有出現，但給定的 Collection 中未出現的值：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $diff = $collection->diff([2, 4, 6, 8]);
    
    $diff->all();
    
    // [1, 3, 5]

> **Note** 在使用 [Eloquent Collection](/docs/{{version}}/eloquent-collections#method-diff) 時，該方法的行為有經過修改。

<a name="method-diffassoc"></a>

#### `diffAssoc()` {.collection-method}

`diffAssoc` 方法會將該 Collection 的索引鍵／值與另一個 Collection 或純 PHP `array` 陣列進行比對。該方法會回傳在原始 Collection 中有出現，但給定的 Collection 中未出現的索引鍵／值配對：

    $collection = collect([
        'color' => 'orange',
        'type' => 'fruit',
        'remain' => 6,
    ]);
    
    $diff = $collection->diffAssoc([
        'color' => 'yellow',
        'type' => 'fruit',
        'remain' => 3,
        'used' => 6,
    ]);
    
    $diff->all();
    
    // ['color' => 'orange', 'remain' => 6]

<a name="method-diffkeys"></a>

#### `diffKeys()` {.collection-method}

`diffKey` 方法會將該 Collection 的索引鍵與另一個 Collection 或純 PHP `array` 陣列進行比對。該方法會回傳在原始 Collection 中有出現，但給定的 Collection 中未出現的索引對：

    $collection = collect([
        'one' => 10,
        'two' => 20,
        'three' => 30,
        'four' => 40,
        'five' => 50,
    ]);
    
    $diff = $collection->diffKeys([
        'two' => 2,
        'four' => 4,
        'six' => 6,
        'eight' => 8,
    ]);
    
    $diff->all();
    
    // ['one' => 10, 'three' => 30, 'five' => 50]

<a name="method-doesntcontain"></a>

#### `doesntContain()` {.collection-method}

`doesntContain` 方法可用來判斷該 Collection 是否不包含給定的項目。可以傳入一個閉包給 `doesntContain` 方法來根據給定的真值條件測試判斷某個元素是否不在該 Collection 內：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->doesntContain(function ($value, $key) {
        return $value < 5;
    });
    
    // false

或者，也可以將字串傳入 `doesntContain` 方法來判斷該 Collection 是否不包含給定的項目值：

    $collection = collect(['name' => 'Desk', 'price' => 100]);
    
    $collection->doesntContain('Table');
    
    // true
    
    $collection->doesntContain('Desk');
    
    // false

也可以傳入一組索引鍵／值配對給 `doesntContains` 方法，用來判斷給定的索引鍵／值配對是否不存在於該 Collection 內：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 100],
    ]);
    
    $collection->doesntContain('product', 'Bookcase');
    
    // true

`doesntContain` 方法在比對項目值時使用了「鬆散 (Loose)」的比對方法。這表示，具有整數值的字串與一個有相同值的整數會被視為相同。

<a name="method-dump"></a>

#### `dump()` {.collection-method}

`dump` 方法會傾印出該 Collection 中的項目：

    $collection = collect(['John Doe', 'Jane Doe']);
    
    $collection->dump();
    
    /*
        Collection {
            #items: array:2 [
                0 => "John Doe"
                1 => "Jane Doe"
            ]
        }
    */

若想在傾印該 Collection 後停止執行指令碼，請使用 [`dd`](#method-dd) 方法來代替。

<a name="method-duplicates"></a>

#### `duplicates()` {.collection-method}

`duplicates` 方法會取得並回傳該 Collection 中重複的值：

    $collection = collect(['a', 'b', 'a', 'c', 'b']);
    
    $collection->duplicates();
    
    // [2 => 'a', 4 => 'b']

若該 Collection 內包含陣列或物件，則可以傳入想用來檢查重複值的屬性索引鍵：

    $employees = collect([
        ['email' => 'abigail@example.com', 'position' => 'Developer'],
        ['email' => 'james@example.com', 'position' => 'Designer'],
        ['email' => 'victoria@example.com', 'position' => 'Developer'],
    ]);
    
    $employees->duplicates('position');
    
    // [2 => 'Developer']

<a name="method-duplicatesstrict"></a>

#### `duplicatesStrict()` {.collection-method}

該方法與 [`duplicates`](#method-duplicates) 方法的簽章一致。不過，所有的數值比對都是使用「嚴格」比對模式。

<a name="method-each"></a>

#### `each()` {.collection-method}

`each` 方法會迭代 Collection 中的項目，並將各個項目傳給閉包：

    $collection->each(function ($item, $key) {
        //
    });

若想停止迭代項目，可以在閉包內回傳 `false`：

    $collection->each(function ($item, $key) {
        if (/* condition */) {
            return false;
        }
    });

<a name="method-eachspread"></a>

#### `eachSpread()` {.collection-method}

`eachSpread` 方法會迭代該 Collection 的項目，並將每個巢狀項目傳入給定的回呼：

    $collection = collect([['John Doe', 35], ['Jane Doe', 33]]);
    
    $collection->eachSpread(function ($name, $age) {
        //
    });

可以通過在回呼內回傳 `false` 來停止迭代項目：

    $collection->eachSpread(function ($name, $age) {
        return false;
    });

<a name="method-every"></a>

#### `every()` {.collection-method}

`every` 方法可以用來認證某個 Collection 中的所有元素是否都通過了給定的布林測試：

    collect([1, 2, 3, 4])->every(function ($value, $key) {
        return $value > 2;
    });
    
    // false

若 Collection 為空，則 `every` 方法總是回傳 true：

    $collection = collect([]);
    
    $collection->every(function ($value, $key) {
        return $value > 2;
    });
    
    // true

<a name="method-except"></a>

#### `except()` {.collection-method}

`except` 方法會回傳該 Collection 中，除了具有特定索引鍵外的所有項目：

    $collection = collect(['product_id' => 1, 'price' => 100, 'discount' => false]);
    
    $filtered = $collection->except(['price', 'discount']);
    
    $filtered->all();
    
    // ['product_id' => 1]

請參考 [only](#method-only) 方法以瞭解與 `except` 相反的方法。

> **Note** 在使用 [Eloquent Collection](/docs/{{version}}/eloquent-collections#method-except) 時，該方法的行為有經過修改。

<a name="method-filter"></a>

#### `filter()` {.collection-method}

`filter` 方法會使用給定的回呼來篩選該 Collection，並只保留通過給定布林測試的項目：

    $collection = collect([1, 2, 3, 4]);
    
    $filtered = $collection->filter(function ($value, $key) {
        return $value > 2;
    });
    
    $filtered->all();
    
    // [3, 4]

若未提供回呼，則該 Collection 中所有等價於 `false` 的項目都會被移除：

    $collection = collect([1, 2, 3, null, false, '', 0, []]);
    
    $collection->filter()->all();
    
    // [1, 2, 3]

請參考 [reject](#method-reject) 方法以瞭解與 `filter` 相反的方法。

<a name="method-first"></a>

#### `first()` {.collection-method}

`first` 方法會回傳該 Collection 中通過給定布林測試的第一個元素：

    collect([1, 2, 3, 4])->first(function ($value, $key) {
        return $value > 2;
    });
    
    // 3

呼叫 `first` 方法時也可以不給任何引數，以取得該 Collection 中的第一個元素。若該 Collection 為空，則會回傳 `null`：

    collect([1, 2, 3, 4])->first();
    
    // 1

<a name="method-first-or-fail"></a>

#### `firstOrFail()` {.collection-method}

`firstOrFail` 方法與 `first` 方法完全相同。不過，若無結果，則會擲回 `Illuminate\Support\ItemNotFoundException` Exception：

    collect([1, 2, 3, 4])->firstOrFail(function ($value, $key) {
        return $value > 5;
    });
    
    // 擲回 ItemNotFoundException...

我們也可以不帶任何參數地呼叫 `firstOrFail` 方法，以取得該 Collection 中的第一個元素。若該 Collection 為空，則會擲回 `Illuminate\Support\ItemNotFoundException` Exception：

    collect([])->firstOrFail();
    
    // 擲回 ItemNotFoundException...

<a name="method-first-where"></a>

#### `firstWhere()` {.collection-method}

`firstWhere` 方法會回傳該 Collection 中具有給定索引鍵／值配對的第一個元素：

    $collection = collect([
        ['name' => 'Regena', 'age' => null],
        ['name' => 'Linda', 'age' => 14],
        ['name' => 'Diego', 'age' => 23],
        ['name' => 'Linda', 'age' => 84],
    ]);
    
    $collection->firstWhere('name', 'Linda');
    
    // ['name' => 'Linda', 'age' => 14]

也可以使用比較運算子來呼叫 `firstWhere` 方法：

    $collection->firstWhere('age', '>=', 18);
    
    // ['name' => 'Diego', 'age' => 23]

與 [where](#method-where) 方法類似，可以傳入一個引數給 `firstWhere` 方法。在這種情境下，`firstWhere` 方法會回傳給定項目的索引鍵值可被視為「True」的第一個項目：

    $collection->firstWhere('age');
    
    // ['name' => 'Linda', 'age' => 14]

<a name="method-flatmap"></a>

#### `flatMap()` {.collection-method}

`flatMap` 方法會迭代該 Collection，並將每個值傳入給定的閉包。該閉包可自由修改項目並進行回傳，藉此依據修改的項目來建立一個新的 Collection。接著，陣列會被扁平化一個階層：

    $collection = collect([
        ['name' => 'Sally'],
        ['school' => 'Arkansas'],
        ['age' => 28]
    ]);
    
    $flattened = $collection->flatMap(function ($values) {
        return array_map('strtoupper', $values);
    });
    
    $flattened->all();
    
    // ['name' => 'SALLY', 'school' => 'ARKANSAS', 'age' => '28'];

<a name="method-flatten"></a>

#### `flatten()` {.collection-method}

`flatten` 方法會將一個多維 Collection 扁平化為單一維度：

    $collection = collect([
        'name' => 'taylor',
        'languages' => [
            'php', 'javascript'
        ]
    ]);
    
    $flattened = $collection->flatten();
    
    $flattened->all();
    
    // ['taylor', 'php', 'javascript'];

若有需要，可以傳入一個可選的「depth 深度」引數：

    $collection = collect([
        'Apple' => [
            [
                'name' => 'iPhone 6S',
                'brand' => 'Apple'
            ],
        ],
        'Samsung' => [
            [
                'name' => 'Galaxy S7',
                'brand' => 'Samsung'
            ],
        ],
    ]);
    
    $products = $collection->flatten(1);
    
    $products->values()->all();
    
    /*
        [
            ['name' => 'iPhone 6S', 'brand' => 'Apple'],
            ['name' => 'Galaxy S7', 'brand' => 'Samsung'],
        ]
    */

在此範例中，在不提供深度的情況下呼叫 `flatten` 會連巢狀陣列也一併被扁平化，產生 `['iPhone 6S', 'Apple', 'Galaxy S7', 'Samsung']`。若提供了深度，則可指定哪些等級的巢狀陣列要被扁平化。

<a name="method-flip"></a>

#### `flip()` {.collection-method}

`flip` 方法會將該 Collection 的索引鍵與其對應的值進行呼喚：

    $collection = collect(['name' => 'taylor', 'framework' => 'laravel']);
    
    $flipped = $collection->flip();
    
    $flipped->all();
    
    // ['taylor' => 'name', 'laravel' => 'framework']

<a name="method-forget"></a>

#### `forget()` {.collection-method}

`forget` 方法根據項目的索引鍵來在該 Collection 中移除項目：

    $collection = collect(['name' => 'taylor', 'framework' => 'laravel']);
    
    $collection->forget('name');
    
    $collection->all();
    
    // ['framework' => 'laravel']

> **Warning** 與其他大多數 Collection 方法不同，`forget` 不會回傳經過修改的新 Collection。該方法會修改被呼叫的那個 Collection。

<a name="method-forpage"></a>

#### `forPage()` {.collection-method}

`forPage` 會回傳一個新的 Collection，包含指定頁碼上的項目。該方法接受第一個引數為頁碼，而第二個引數則為每頁要顯示幾個項目：

    $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9]);
    
    $chunk = $collection->forPage(2, 3);
    
    $chunk->all();
    
    // [4, 5, 6]

<a name="method-get"></a>

#### `get()` {.collection-method}

`get` 方法會回傳給定索引鍵上的項目。若該索引鍵不存在，則會回傳 `null`：

    $collection = collect(['name' => 'taylor', 'framework' => 'laravel']);
    
    $value = $collection->get('name');
    
    // taylor

也可以傳入可選的第二個引數提供預設值：

    $collection = collect(['name' => 'taylor', 'framework' => 'laravel']);
    
    $value = $collection->get('age', 34);
    
    // 34

也可以傳入一個回呼來作為該方法的預設值。若特定的索引鍵不存在，就會回傳該回呼的結果：

    $collection->get('email', function () {
        return 'taylor@example.com';
    });
    
    // taylor@example.com

<a name="method-groupby"></a>

#### `groupBy()` {.collection-method}

`groupBy` 方法會依照給定的索引鍵來將該 Collection 的項目分組：

    $collection = collect([
        ['account_id' => 'account-x10', 'product' => 'Chair'],
        ['account_id' => 'account-x10', 'product' => 'Bookcase'],
        ['account_id' => 'account-x11', 'product' => 'Desk'],
    ]);
    
    $grouped = $collection->groupBy('account_id');
    
    $grouped->all();
    
    /*
        [
            'account-x10' => [
                ['account_id' => 'account-x10', 'product' => 'Chair'],
                ['account_id' => 'account-x10', 'product' => 'Bookcase'],
            ],
            'account-x11' => [
                ['account_id' => 'account-x11', 'product' => 'Desk'],
            ],
        ]
    */

除了傳入字串 `key` 以外，也可以傳入一個回呼。該回呼應回傳用於分組的索引鍵值：

    $grouped = $collection->groupBy(function ($item, $key) {
        return substr($item['account_id'], -3);
    });
    
    $grouped->all();
    
    /*
        [
            'x10' => [
                ['account_id' => 'account-x10', 'product' => 'Chair'],
                ['account_id' => 'account-x10', 'product' => 'Bookcase'],
            ],
            'x11' => [
                ['account_id' => 'account-x11', 'product' => 'Desk'],
            ],
        ]
    */

若有多個分組的方法，則可傳入陣列。陣列中的各個元素會被在對應的多維陣列上的層級：

    $data = new Collection([
        10 => ['user' => 1, 'skill' => 1, 'roles' => ['Role_1', 'Role_3']],
        20 => ['user' => 2, 'skill' => 1, 'roles' => ['Role_1', 'Role_2']],
        30 => ['user' => 3, 'skill' => 2, 'roles' => ['Role_1']],
        40 => ['user' => 4, 'skill' => 2, 'roles' => ['Role_2']],
    ]);
    
    $result = $data->groupBy(['skill', function ($item) {
        return $item['roles'];
    }], preserveKeys: true);
    
    /*
    [
        1 => [
            'Role_1' => [
                10 => ['user' => 1, 'skill' => 1, 'roles' => ['Role_1', 'Role_3']],
                20 => ['user' => 2, 'skill' => 1, 'roles' => ['Role_1', 'Role_2']],
            ],
            'Role_2' => [
                20 => ['user' => 2, 'skill' => 1, 'roles' => ['Role_1', 'Role_2']],
            ],
            'Role_3' => [
                10 => ['user' => 1, 'skill' => 1, 'roles' => ['Role_1', 'Role_3']],
            ],
        ],
        2 => [
            'Role_1' => [
                30 => ['user' => 3, 'skill' => 2, 'roles' => ['Role_1']],
            ],
            'Role_2' => [
                40 => ['user' => 4, 'skill' => 2, 'roles' => ['Role_2']],
            ],
        ],
    ];
    */

<a name="method-has"></a>

#### `has()` {.collection-method}

`has` 方法用於判斷給定的索引鍵是否存在於該 Collection 上：

    $collection = collect(['account_id' => 1, 'product' => 'Desk', 'amount' => 5]);
    
    $collection->has('product');
    
    // true
    
    $collection->has(['product', 'amount']);
    
    // true
    
    $collection->has(['amount', 'price']);
    
    // false

<a name="method-hasany"></a>

#### `hasAny()` {.collection-method}

`hasAny` 方法用於判斷給定的多個索引鍵中，是否有任何索引鍵存在於該 Collection 上：

    $collection = collect(['account_id' => 1, 'product' => 'Desk', 'amount' => 5]);
    
    $collection->hasAny(['product', 'price']);
    
    // true
    
    $collection->hasAny(['name', 'price']);
    
    // false

<a name="method-implode"></a>

#### `implode()` {.collection-method}

`implode` 方法可將 Collection 內多個項目串聯。該方法的引數會依照該 Collection 中項目的類型而有所不同。若該 Collection 中包含陣列或物件，則應傳入欲串聯的屬性之索引鍵，以及要用來串聯各個值的「Glue (黏著)」字串：

    $collection = collect([
        ['account_id' => 1, 'product' => 'Desk'],
        ['account_id' => 2, 'product' => 'Chair'],
    ]);
    
    $collection->implode('product', ', ');
    
    // Desk, Chair

若該 Collection 中只包含了單純的字串或數字值，則只需要傳入「Glue (黏著)」字串作為該方法唯一的引數即可：

    collect([1, 2, 3, 4, 5])->implode('-');
    
    // '1-2-3-4-5'

若想為 implode 後的值自定格式，可以傳入一個閉包給 `implode` 方法：

    $collection->implode(function ($item, $key) {
        return strtoupper($item['product']);
    }, ', ');
    
    // DESK, CHAIR

<a name="method-intersect"></a>

#### `intersect()` {.collection-method}

`intersect` 方法會從原始 Collection 中移除給定 `array` 或 Collection 中不存在的值。產生的 Collection 會保有原始 Collection 的索引鍵：

    $collection = collect(['Desk', 'Sofa', 'Chair']);
    
    $intersect = $collection->intersect(['Desk', 'Chair', 'Bookcase']);
    
    $intersect->all();
    
    // [0 => 'Desk', 2 => 'Chair']

> **Note** 在使用 [Eloquent Collection](/docs/{{version}}/eloquent-collections#method-intersect) 時，該方法的行為有經過修改。

<a name="method-intersectbykeys"></a>

#### `intersectByKeys()` {.collection-method}

`intersectByKeys` 方法會自原始 Collection 中移除其索引鍵不存在於給定 `array` 或 Collection 中的索引鍵與值：

    $collection = collect([
        'serial' => 'UX301', 'type' => 'screen', 'year' => 2009,
    ]);
    
    $intersect = $collection->intersectByKeys([
        'reference' => 'UX404', 'type' => 'tab', 'year' => 2011,
    ]);
    
    $intersect->all();
    
    // ['type' => 'screen', 'year' => 2009]

<a name="method-isempty"></a>

#### `isEmpty()` {.collection-method}

`isEmpty` 會在該 Collection 為空時回傳 `true`。否則會回傳 `false`：

    collect([])->isEmpty();
    
    // true

<a name="method-isnotempty"></a>

#### `isNotEmpty()` {.collection-method}

`isNotEmpty` 會在該 Collection 不為空時回傳 `true`。否則會回傳 `false`：

    collect([])->isNotEmpty();
    
    // false

<a name="method-join"></a>

#### `join()` {.collection-method}

`join` 方法會將該 Collection 中的值合併為一個字串。使用該方法的第二個引數可用來指定最後一個元素要如何被加進字串裡：

    collect(['a', 'b', 'c'])->join(', '); // 'a, b, c'
    collect(['a', 'b', 'c'])->join(', ', ', and '); // 'a, b, and c'
    collect(['a', 'b'])->join(', ', ' and '); // 'a and b'
    collect(['a'])->join(', ', ' and '); // 'a'
    collect([])->join(', ', ' and '); // ''

<a name="method-keyby"></a>

#### `keyBy()` {.collection-method}

`keyBy` 方法依照給定的索引鍵來將該 Collection 加上索引鍵。若多個項目有相同的索引鍵，則新的 Collection 中只會包含最後一個項目：

    $collection = collect([
        ['product_id' => 'prod-100', 'name' => 'Desk'],
        ['product_id' => 'prod-200', 'name' => 'Chair'],
    ]);
    
    $keyed = $collection->keyBy('product_id');
    
    $keyed->all();
    
    /*
        [
            'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
            'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]
    */

也可以傳入回呼給該方法。該回呼應回傳用於為該 Collection 加上索引鍵的值：

    $keyed = $collection->keyBy(function ($item, $key) {
        return strtoupper($item['product_id']);
    });
    
    $keyed->all();
    
    /*
        [
            'PROD-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
            'PROD-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]
    */

<a name="method-keys"></a>

#### `keys()` {.collection-method}

`keys` 方法回傳該 Collection 中的所有索引鍵：

    $collection = collect([
        'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
        'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
    ]);
    
    $keys = $collection->keys();
    
    $keys->all();
    
    // ['prod-100', 'prod-200']

<a name="method-last"></a>

#### `last()` {.collection-method}

`last` 方法會回傳該 Collection 中通過給定布林測試的最後一個元素：

    collect([1, 2, 3, 4])->last(function ($value, $key) {
        return $value < 3;
    });
    
    // 2

呼叫 `last` 方法時也可以不給任何引數，以取得該 Collection 中的最後一個元素。若該 Collection 為空，則會回傳 `null`：

    collect([1, 2, 3, 4])->last();
    
    // 4

<a name="method-lazy"></a>

#### `lazy()` {.collection-method}

`lazy` 方法使用底層項目陣列來回傳一個新的 [`LazyCollection`](#lazy-collections) 實體：

    $lazyCollection = collect([1, 2, 3, 4])->lazy();
    
    get_class($lazyCollection);
    
    // Illuminate\Support\LazyCollection
    
    $lazyCollection->all();
    
    // [1, 2, 3, 4]

若想轉換一個有許多項目的大型 `Collection`，就特別適合使用該方法：

    $count = $hugeCollection
        ->lazy()
        ->where('country', 'FR')
        ->where('balance', '>', '100')
        ->count();

將 Collection 轉為 `LazyCollection` 後，就可避免使用到大量額外的記憶體。雖然，**原始陣列的值** 還是會保存在記憶體中，但之後所進行的篩選結果將不會被保存在記憶體中。因此，在篩選 Collection 結果時，將不會使用到額外的記憶體。

<a name="method-macro"></a>

#### `macro()` {.collection-method}

靜態 `macro` 方法可用來在執行階段將方法加入 `Collection` 類別內。更多資訊請參考有關[擴充 Collection](#extending-collections) 的說明文件。

<a name="method-make"></a>

#### `make()` {.collection-method}

The static `make` method creates a new collection instance. See the [Creating Collections](#creating-collections) section.

<a name="method-map"></a>

#### `map()` {.collection-method}

`map` 方法會迭代該 Collection，並將每個值傳入給定的回呼。該回呼可自由修改項目並進行回傳，藉此依據修改的項目來建立一個新的 Collection：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $multiplied = $collection->map(function ($item, $key) {
        return $item * 2;
    });
    
    $multiplied->all();
    
    // [2, 4, 6, 8, 10]

> **Warning** 與其他 Collection 方法類似，`map` 會回傳一個新的 Collection 實體。該方法不會修改正在呼叫的 Collection。若想改變原始 Collection，請使用 [`transform`](#method-transform) 方法代替。

<a name="method-mapinto"></a>

#### `mapInto()` {.collection-method}

The `mapInto()` method iterates over the collection, creating a new instance of the given class by passing the value into the constructor:

    class Currency
    {
        /**
         * Create a new currency instance.
         *
         * @param  string  $code
         * @return void
         */
        function __construct(string $code)
        {
            $this->code = $code;
        }
    }
    
    $collection = collect(['USD', 'EUR', 'GBP']);
    
    $currencies = $collection->mapInto(Currency::class);
    
    $currencies->all();
    
    // [Currency('USD'), Currency('EUR'), Currency('GBP')]

<a name="method-mapspread"></a>

#### `mapSpread()` {.collection-method}

`mapSpread` 方法會迭代該 Collection 的項目，並將各個巢狀項目傳入給定的閉包內。該閉包可修改這些項目並回傳，藉此以修改過的項目來建立新的 Collection：

    $collection = collect([0, 1, 2, 3, 4, 5, 6, 7, 8, 9]);
    
    $chunks = $collection->chunk(2);
    
    $sequence = $chunks->mapSpread(function ($even, $odd) {
        return $even + $odd;
    });
    
    $sequence->all();
    
    // [1, 5, 9, 13, 17]

<a name="method-maptogroups"></a>

#### `mapToGroups()` {.collection-method}

`mapToGroups` 方法使用給定的閉包來分組該 Collection 的項目。該閉包應回傳一個包含單一索引鍵／值配對的關聯性陣列，藉此以經過分組的值來建立新的 Collection：

    $collection = collect([
        [
            'name' => 'John Doe',
            'department' => 'Sales',
        ],
        [
            'name' => 'Jane Doe',
            'department' => 'Sales',
        ],
        [
            'name' => 'Johnny Doe',
            'department' => 'Marketing',
        ]
    ]);
    
    $grouped = $collection->mapToGroups(function ($item, $key) {
        return [$item['department'] => $item['name']];
    });
    
    $grouped->all();
    
    /*
        [
            'Sales' => ['John Doe', 'Jane Doe'],
            'Marketing' => ['Johnny Doe'],
        ]
    */
    
    $grouped->get('Sales')->all();
    
    // ['John Doe', 'Jane Doe']

<a name="method-mapwithkeys"></a>

#### `mapWithKeys()` {.collection-method}

`mapWithKeys` 方法會迭代該 Collection，並將各個值傳入給定的回呼。該回呼應回傳一個包含單一索引鍵／值配對的關聯性陣列：

    $collection = collect([
        [
            'name' => 'John',
            'department' => 'Sales',
            'email' => 'john@example.com',
        ],
        [
            'name' => 'Jane',
            'department' => 'Marketing',
            'email' => 'jane@example.com',
        ]
    ]);
    
    $keyed = $collection->mapWithKeys(function ($item, $key) {
        return [$item['email'] => $item['name']];
    });
    
    $keyed->all();
    
    /*
        [
            'john@example.com' => 'John',
            'jane@example.com' => 'Jane',
        ]
    */

<a name="method-max"></a>

#### `max()` {.collection-method}

`max` 方法回傳給定索引鍵的最大值：

    $max = collect([
        ['foo' => 10],
        ['foo' => 20]
    ])->max('foo');
    
    // 20
    
    $max = collect([1, 2, 3, 4, 5])->max();
    
    // 5

<a name="method-median"></a>

#### `median()` {.collection-method}

`medium` 方法會回傳給定索引鍵的[中位數](https://zh.wikipedia.org/zh-tw/%E4%B8%AD%E4%BD%8D%E6%95%B8)：

    $median = collect([
        ['foo' => 10],
        ['foo' => 10],
        ['foo' => 20],
        ['foo' => 40]
    ])->median('foo');
    
    // 15
    
    $median = collect([1, 1, 2, 4])->median();
    
    // 1.5

<a name="method-merge"></a>

#### `merge()` {.collection-method}

`merge` 方法會將給定的陣列或 Collection 與原始 Collection 合併。若在給定項目中有字串索引鍵與原始 Collection 中的字串索引鍵相同，則給定項目的值會覆蓋原始 Collection 中的值：

    $collection = collect(['product_id' => 1, 'price' => 100]);
    
    $merged = $collection->merge(['price' => 200, 'discount' => false]);
    
    $merged->all();
    
    // ['product_id' => 1, 'price' => 200, 'discount' => false]

若給定項目的索引鍵是數字值，則其值會被加到該 Collection 的最後面：

    $collection = collect(['Desk', 'Chair']);
    
    $merged = $collection->merge(['Bookcase', 'Door']);
    
    $merged->all();
    
    // ['Desk', 'Chair', 'Bookcase', 'Door']

<a name="method-mergerecursive"></a>

#### `mergeRecursive()` {.collection-method}

`mergeRecursive` 方法會將給定的陣列或 Collection 與原始 Collection 遞迴合併。若在給定項目中有字串索引鍵與原始 Collection 中的字串索引鍵相同，則這些索引鍵的值將被一起合併為一個陣列，且此一過程將遞迴進行：

    $collection = collect(['product_id' => 1, 'price' => 100]);
    
    $merged = $collection->mergeRecursive([
        'product_id' => 2,
        'price' => 200,
        'discount' => false
    ]);
    
    $merged->all();
    
    // ['product_id' => [1, 2], 'price' => [100, 200], 'discount' => false]

<a name="method-min"></a>

#### `min()` {.collection-method}

`min` 方法回傳給定索引鍵的最小值：

    $min = collect([['foo' => 10], ['foo' => 20]])->min('foo');
    
    // 10
    
    $min = collect([1, 2, 3, 4, 5])->min();
    
    // 1

<a name="method-mode"></a>

#### `mode()` {.collection-method}

`mode` 方法會回傳給定索引鍵的[眾數](https://zh.wikipedia.org/zh-tw/%E4%BC%97%E6%95%B0_%28%E6%95%B0%E5%AD%A6%29)：

    $mode = collect([
        ['foo' => 10],
        ['foo' => 10],
        ['foo' => 20],
        ['foo' => 40]
    ])->mode('foo');
    
    // [10]
    
    $mode = collect([1, 1, 2, 4])->mode();
    
    // [1]
    
    $mode = collect([1, 1, 2, 2])->mode();
    
    // [1, 2]

<a name="method-nth"></a>

#### `nth()` {.collection-method}

`nth` 方法會使用每 n 個元素來建立新的 Collection：

    $collection = collect(['a', 'b', 'c', 'd', 'e', 'f']);
    
    $collection->nth(4);
    
    // ['a', 'e']

也可以傳入可選的第二個引數來設定起始偏移值：

    $collection->nth(4, 1);
    
    // ['b', 'f']

<a name="method-only"></a>

#### `only()` {.collection-method}

`only` 方法會回傳該 Collection 中具有特定索引鍵的所有項目：

    $collection = collect([
        'product_id' => 1,
        'name' => 'Desk',
        'price' => 100,
        'discount' => false
    ]);
    
    $filtered = $collection->only(['product_id', 'name']);
    
    $filtered->all();
    
    // ['product_id' => 1, 'name' => 'Desk']

請參考 [except](#method-except) 方法以瞭解與 `only` 相反的方法。

> **Note** 在使用 [Eloquent Collection](/docs/{{version}}/eloquent-collections#method-only) 時，該方法的行為有經過修改。

<a name="method-pad"></a>

#### `pad()` {.collection-method}

`pad` 方法會以給定的值來填充陣列，直到該陣列達到特定大小。該方法的行為與 [array_pad](https://secure.php.net/manual/en/function.array-pad.php) PHP 方法類似。

若要向左填充，則可指定負數大小。若給定大小的絕對值小於或等於該陣列的長度，則不會進行填充：

    $collection = collect(['A', 'B', 'C']);
    
    $filtered = $collection->pad(5, 0);
    
    $filtered->all();
    
    // ['A', 'B', 'C', 0, 0]
    
    $filtered = $collection->pad(-5, 0);
    
    $filtered->all();
    
    // [0, 0, 'A', 'B', 'C']

<a name="method-partition"></a>

#### `partition()` {.collection-method}

`partition` 方法可用來與 PHP 的陣列結構函式一起使用，以區分出符合與不符合給定布林測試的元素：

    $collection = collect([1, 2, 3, 4, 5, 6]);
    
    [$underThree, $equalOrAboveThree] = $collection->partition(function ($i) {
        return $i < 3;
    });
    
    $underThree->all();
    
    // [1, 2]
    
    $equalOrAboveThree->all();
    
    // [3, 4, 5, 6]

<a name="method-pipe"></a>

#### `pipe()` {.collection-method}

`pipe` 方法會將該 Collection 傳入給定閉包，並回傳執行該閉包的結果：

    $collection = collect([1, 2, 3]);
    
    $piped = $collection->pipe(function ($collection) {
        return $collection->sum();
    });
    
    // 6

<a name="method-pipeinto"></a>

#### `pipeInto()` {.collection-method}

`pipeInto` 方法會以給定的類別建立新實體，並將該 Collection 傳入其建構函式內：

    class ResourceCollection
    {
        /**
         * The Collection instance.
         */
        public $collection;
    
        /**
         * Create a new ResourceCollection instance.
         *
         * @param  Collection  $collection
         * @return void
         */
        public function __construct(Collection $collection)
        {
            $this->collection = $collection;
        }
    }
    
    $collection = collect([1, 2, 3]);
    
    $resource = $collection->pipeInto(ResourceCollection::class);
    
    $resource->collection->all();
    
    // [1, 2, 3]

<a name="method-pipethrough"></a>

#### `pipeThrough()` {.collection-method}

`pipeThrough` 方法會將該 Collection 傳入給定之包含閉包的陣列，並回傳這些閉包的執行結果：

    $collection = collect([1, 2, 3]);
    
    $result = $collection->pipeThrough([
        function ($collection) {
            return $collection->merge([4, 5]);
        },
        function ($collection) {
            return $collection->sum();
        },
    ]);
    
    // 15

<a name="method-pluck"></a>

#### `pluck()` {.collection-method}

`pluck` 方法可取得給定索引鍵內的所有值：

    $collection = collect([
        ['product_id' => 'prod-100', 'name' => 'Desk'],
        ['product_id' => 'prod-200', 'name' => 'Chair'],
    ]);
    
    $plucked = $collection->pluck('name');
    
    $plucked->all();
    
    // ['Desk', 'Chair']

也可以指定產生的 Collection 要如何設定索引鍵：

    $plucked = $collection->pluck('name', 'product_id');
    
    $plucked->all();
    
    // ['prod-100' => 'Desk', 'prod-200' => 'Chair']

`pluck` 方法也支援使用「點 (.)」標記法來取得巢狀數值：

    $collection = collect([
        [
            'name' => 'Laracon',
            'speakers' => [
                'first_day' => ['Rosa', 'Judith'],
            ],
        ],
        [
            'name' => 'VueConf',
            'speakers' => [
                'first_day' => ['Abigail', 'Joey'],
            ],
        ],
    ]);
    
    $plucked = $collection->pluck('speakers.first_day');
    
    $plucked->all();
    
    // [['Rosa', 'Judith'], ['Abigail', 'Joey']]

若存在重複的索引鍵，則最後一個相符合的元素會被插入 pluck 後的 Collection：

    $collection = collect([
        ['brand' => 'Tesla',  'color' => 'red'],
        ['brand' => 'Pagani', 'color' => 'white'],
        ['brand' => 'Tesla',  'color' => 'black'],
        ['brand' => 'Pagani', 'color' => 'orange'],
    ]);
    
    $plucked = $collection->pluck('color', 'brand');
    
    $plucked->all();
    
    // ['Tesla' => 'black', 'Pagani' => 'orange']

<a name="method-pop"></a>

#### `pop()` {.collection-method}

`pop` 方法會從該 Collection 中移除最後一個項目並將其回傳：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->pop();
    
    // 5
    
    $collection->all();
    
    // [1, 2, 3, 4]

可以將整數傳入 `pop` 方法以從 Collection 的結尾移除並回傳多個項目：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->pop(3);
    
    // collect([5, 4, 3])
    
    $collection->all();
    
    // [1, 2]

<a name="method-prepend"></a>

#### `prepend()` {.collection-method}

`prepend` 方法會將項目加至該 Collection 的開頭：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->prepend(0);
    
    $collection->all();
    
    // [0, 1, 2, 3, 4, 5]

也可以傳入第二個引數來指定要被加到前面之項目的索引鍵：

    $collection = collect(['one' => 1, 'two' => 2]);
    
    $collection->prepend(0, 'zero');
    
    $collection->all();
    
    // ['zero' => 0, 'one' => 1, 'two' => 2]

<a name="method-pull"></a>

#### `pull()` {.collection-method}

`pull` 方法根據項目的索引鍵來在該 Collection 中移除項目並將其回傳：

    $collection = collect(['product_id' => 'prod-100', 'name' => 'Desk']);
    
    $collection->pull('name');
    
    // 'Desk'
    
    $collection->all();
    
    // ['product_id' => 'prod-100']

<a name="method-push"></a>

#### `push()` {.collection-method}

`push` 方法會將項目加至該 Collection 的結尾：

    $collection = collect([1, 2, 3, 4]);
    
    $collection->push(5);
    
    $collection->all();
    
    // [1, 2, 3, 4, 5]

<a name="method-put"></a>

#### `put()` {.collection-method}

`put` 方法將給定的索引鍵與值設定至該 Collection 內：

    $collection = collect(['product_id' => 1, 'name' => 'Desk']);
    
    $collection->put('price', 100);
    
    $collection->all();
    
    // ['product_id' => 1, 'name' => 'Desk', 'price' => 100]

<a name="method-random"></a>

#### `random()` {.collection-method}

`random` 方法會從該 Collection 內回傳一個隨機的項目：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->random();
    
    // 4 - (隨機取得)

也可以傳入一個整數給 `random` 來指定要隨機取得幾個項目。當有明顯傳入要取得記得項目的時候，將總是回傳一個包含項目的 Collection：

    $random = $collection->random(3);
    
    $random->all();
    
    // [2, 4, 5] - (retrieved randomly)

若該 Collection 實體內的項目比所要求的還要少，則 `random` 方法會擲回一個 `InvalidArgumentException`。

`random` 函式也接受傳入一個閉包，該閉包會收到目前 Collection 的實體：

    $random = $collection->random(fn ($items) => min(10, count($items)));
    
    $random->all();
    
    // [1, 2, 3, 4, 5] - (隨機取得)

<a name="method-range"></a>

#### `range()` {.collection-method}

`range` 方法回傳一個包含了介於指定範圍內整數的 Collection：

    $collection = collect()->range(3, 6);
    
    $collection->all();
    
    // [3, 4, 5, 6]

<a name="method-reduce"></a>

#### `reduce()` {.collection-method}

`reduce` 方法會將該 Collection 歸約 (Reduce) 為單一值，將各個迭代的結果傳送至接下來的迭代中：

    $collection = collect([1, 2, 3]);
    
    $total = $collection->reduce(function ($carry, $item) {
        return $carry + $item;
    });
    
    // 6

第一次迭代時，`$carry` 的值為 `null`。不過，也可以通過將第二個引數傳給 `reduce` 來指定初始值：

    $collection->reduce(function ($carry, $item) {
        return $carry + $item;
    }, 4);
    
    // 10

`reduce` 方法也會將關聯式 Collection 的索引鍵傳入給定的回呼中：

    $collection = collect([
        'usd' => 1400,
        'gbp' => 1200,
        'eur' => 1000,
    ]);
    
    $ratio = [
        'usd' => 1,
        'gbp' => 1.37,
        'eur' => 1.22,
    ];
    
    $collection->reduce(function ($carry, $value, $key) use ($ratio) {
        return $carry + ($value * $ratio[$key]);
    });
    
    // 4264

<a name="method-reduce-spread"></a>

#### `reduceSpread()` {.collection-method}

`reduceSpread` 方法會將該 Collection 歸約 (Reduce) 為一組包含值的陣列，並將每次迭代的結果傳遞給下一個迭代。這個方法與 `reduce` 方法類似，不過 `reduceSpread` 接受多個初始值：

    [$creditsRemaining, $batch] = Image::where('status', 'unprocessed')
        ->get()
        ->reduceSpread(function ($creditsRemaining, $batch, $image) {
            if ($creditsRemaining >= $image->creditsRequired()) {
                $batch->push($image);
    
                $creditsRemaining -= $image->creditsRequired();
            }
    
            return [$creditsRemaining, $batch];
        }, $creditsAvailable, collect());

<a name="method-reject"></a>

#### `reject()` {.collection-method}

`reject` 方法會使用給定的閉包來過濾該 Collection。若某項目應自產生的 Collection 內移除，則該閉包應回傳 `true`：

    $collection = collect([1, 2, 3, 4]);
    
    $filtered = $collection->reject(function ($value, $key) {
        return $value > 2;
    });
    
    $filtered->all();
    
    // [1, 2]

有關與 `reject` 相反的方法。請參考 [`filter`]（#method-filter) 方法。

<a name="method-replace"></a>

#### `replace()` {.collection-method}

`replace` 方法與 `merge` 方法類似。不過，除了複寫有字串索引鍵的相符項目外，`replace` 還會複寫該 Collection 中符合數字索引鍵的項目：

    $collection = collect(['Taylor', 'Abigail', 'James']);
    
    $replaced = $collection->replace([1 => 'Victoria', 3 => 'Finn']);
    
    $replaced->all();
    
    // ['Taylor', 'Victoria', 'James', 'Finn']

<a name="method-replacerecursive"></a>

#### `replaceRecursive()` {.collection-method}

該方法與 `replace` 類似，但這個方法會遞迴僅各個陣列，並將相同的取代過程套用至內部的數值：

    $collection = collect([
        'Taylor',
        'Abigail',
        [
            'James',
            'Victoria',
            'Finn'
        ]
    ]);
    
    $replaced = $collection->replaceRecursive([
        'Charlie',
        2 => [1 => 'King']
    ]);
    
    $replaced->all();
    
    // ['Charlie', 'Abigail', ['James', 'King', 'Finn']]

<a name="method-reverse"></a>

#### `reverse()` {.collection-method}

`reverse` 方法會將該 Collection 項目的順序顛倒過來，但保留原來的索引鍵：

    $collection = collect(['a', 'b', 'c', 'd', 'e']);
    
    $reversed = $collection->reverse();
    
    $reversed->all();
    
    /*
        [
            4 => 'e',
            3 => 'd',
            2 => 'c',
            1 => 'b',
            0 => 'a',
        ]
    */

<a name="method-search"></a>

#### `search()` {.collection-method}

`search` 方法在該 Collection 中搜尋給定的值，並在找到後回傳其索引鍵。若找不到該項目，則會回傳 `false`：

    $collection = collect([2, 4, 6, 8]);
    
    $collection->search(4);
    
    // 1

這裡的搜尋是使用「鬆散 (Loose)」比對的，這表示，一個整數值與一個有相同值的字串會被視為相等。若要使用「嚴格 (Strict)」比對，可傳入 `true` 作為該方法的第二個引數：

    collect([2, 4, 6, 8])->search('4', $strict = true);
    
    // false

或者，也可以提供你自己的閉包來搜尋符合給定布林測試的第一個項目：

    collect([2, 4, 6, 8])->search(function ($item, $key) {
        return $item > 5;
    });
    
    // 2

<a name="method-shift"></a>

#### `shift()` {.collection-method}

`shift` 方法會從該 Collection 中移除第一個項目並將其回傳：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->shift();
    
    // 1
    
    $collection->all();
    
    // [2, 3, 4, 5]

可以將整數傳入 `shift` 方法以從 Collection 的開頭移除並回傳多個項目：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->shift(3);
    
    // collect([1, 2, 3])
    
    $collection->all();
    
    // [4, 5]

<a name="method-shuffle"></a>

#### `shuffle()` {.collection-method}

`shuffle` 方法會隨機排序該 Collection 內的項目：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $shuffled = $collection->shuffle();
    
    $shuffled->all();
    
    // [3, 2, 5, 1, 4] - (隨機產生)

<a name="method-skip"></a>

#### `skip()` {.collection-method}

`skip` 方法會從該 Collection 的開頭移除給定數量的元素，作為一個新的陣列回傳：

    $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    
    $collection = $collection->skip(4);
    
    $collection->all();
    
    // [5, 6, 7, 8, 9, 10]

<a name="method-skipuntil"></a>

#### `skipUntil()` {.collection-method}

`skipUtil` 方法會一直跳過，直到給定的回呼回傳 `true`。接著，會回傳該 Collection 中剩下的項目作為一個新的 Collection 實體：

    $collection = collect([1, 2, 3, 4]);
    
    $subset = $collection->skipUntil(function ($item) {
        return $item >= 3;
    });
    
    $subset->all();
    
    // [3, 4]

也可以傳入一個簡單的值給 `skipUntil` 方法，來跳過直到找到指定項目之前的所有項目：

    $collection = collect([1, 2, 3, 4]);
    
    $subset = $collection->skipUntil(3);
    
    $subset->all();
    
    // [3, 4]

> **Warning** 若找不到給定的值，或是回呼從未回傳 `true`，則 `skipUntil` 方法會回傳一個空 Collection。

<a name="method-skipwhile"></a>

#### `skipWhile()` {.collection-method}

`skipWhile` 方法會在給定的回呼回傳 `true` 的時候從該 Collection 中跳過項目，並回傳該 Collection 中剩餘的項目作為一個新的 Collection：

    $collection = collect([1, 2, 3, 4]);
    
    $subset = $collection->skipWhile(function ($item) {
        return $item <= 3;
    });
    
    $subset->all();
    
    // [4]

> **Warning** 若該回呼從未回傳 `true`，則 `skipWhile` 方法會回傳一個空 Collection。

<a name="method-slice"></a>

#### `slice()` {.collection-method}

`slice` 方法會回傳該 Collection 從給定索引開始的部分：

    $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    
    $slice = $collection->slice(4);
    
    $slice->all();
    
    // [5, 6, 7, 8, 9, 10]

若想限制回傳部分的大小，可傳入需要的大小作為該方法的第二個引數：

    $slice = $collection->slice(4, 2);
    
    $slice->all();
    
    // [5, 6]

回傳的部分預設會保留索引鍵。若不想保留原始的索引鍵，可以使用 [`values`](#method-values) 方法來重新索引這些項目。

<a name="method-sliding"></a>

#### `sliding()` {.collection-method}

`sliding` 方法會以代表「Sliding Window」的方式將該 Collection 中的項目拆分為數個片段並回傳一個新的 Collection：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $chunks = $collection->sliding(2);
    
    $chunks->toArray();
    
    // [[1, 2], [2, 3], [3, 4], [4, 5]]

這個方法特別適合與 [`eachSpread`](#method-eachspread) 方法一起使用：

    $transactions->sliding(2)->eachSpread(function ($previous, $current) {
        $current->total = $previous->total + $current->amount;
    });

也可以傳入第二個可選的「區間 (step)」值，用來判斷每個片段中第一個項目的距離：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $chunks = $collection->sliding(3, step: 2);
    
    $chunks->toArray();
    
    // [[1, 2, 3], [3, 4, 5]]

<a name="method-sole"></a>

#### `sole()` {.collection-method}

`sole` 方法會回傳該 Collection 中第一個且唯一一個通過給定真值條件測試的元素：

    collect([1, 2, 3, 4])->sole(function ($value, $key) {
        return $value === 2;
    });
    
    // 2

也可以傳入一組索引鍵／值配對給 `sole` 方法，`sole` 方法會回傳該 Collection 中符合給定索引鍵／值配對的第一個且唯一一個項目：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 100],
    ]);
    
    $collection->sole('product', 'Chair');
    
    // ['product' => 'Chair', 'price' => 100]

或者，也可以在不給定引數的情況下呼叫 `sole`，以在該 Collection 中只有一個元素時取得其第一個元素：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
    ]);
    
    $collection->sole();
    
    // ['product' => 'Desk', 'price' => 200]

若該 Collection 中沒有能被 `sole` 方法回傳的元素，則會擲回一個 `\Illuminate\Collections\ItemNotFoundException` 例外。若有多於一個能被回傳的元素，則會擲回 `\Illuminate\Collections\MultipleItemsFoundException` 例外。

<a name="method-some"></a>

#### `some()` {.collection-method}

[`contains`](#method-contains) 方法的別名。

<a name="method-sort"></a>

#### `sort()` {.collection-method}

`sort` 方法可排列該 Collection。經過排列的 Collection 會保留原始的陣列索引鍵，因此，在下列範例中我們使用了 [`values`](#method-values) 方法來將索引鍵重設成連續的數字索引：

    $collection = collect([5, 3, 1, 2, 4]);
    
    $sorted = $collection->sort();
    
    $sorted->values()->all();
    
    // [1, 2, 3, 4, 5]

若有進階的排序需求，可以傳入包含自訂演算法的回呼給 `sort`。請參考 PHP 說明文件中的 [`uasort`](https://secure.php.net/manual/en/function.uasort.php#refsect1-function.uasort-parameters)，該函式為 Collection 的 `sort` 方法內部所使用。

> **Note** 若有需要排序包含巢狀陣列或物件的 Collection，請參考 [`sortBy`](#method-sortby) 與 [`sortByDesc`](#method-sortbydesc) 方法。

<a name="method-sortby"></a>

#### `sortBy()` {.collection-method}

`sortBy` 方法可依照給定的索引鍵來排列該 Collection。經過排列的 Collection 會保留原始的陣列索引鍵，因此，在下列範例中我們使用了 [`values`](#method-values) 方法來將索引鍵重設成連續的數字索引：

    $collection = collect([
        ['name' => 'Desk', 'price' => 200],
        ['name' => 'Chair', 'price' => 100],
        ['name' => 'Bookcase', 'price' => 150],
    ]);
    
    $sorted = $collection->sortBy('price');
    
    $sorted->values()->all();
    
    /*
        [
            ['name' => 'Chair', 'price' => 100],
            ['name' => 'Bookcase', 'price' => 150],
            ['name' => 'Desk', 'price' => 200],
        ]
    */

`sortBy` 方法也接受[排序旗標](https://www.php.net/manual/en/function.sort.php)作為其第二引數：

    $collection = collect([
        ['title' => 'Item 1'],
        ['title' => 'Item 12'],
        ['title' => 'Item 3'],
    ]);
    
    $sorted = $collection->sortBy('title', SORT_NATURAL);
    
    $sorted->values()->all();
    
    /*
        [
            ['title' => 'Item 1'],
            ['title' => 'Item 3'],
            ['title' => 'Item 12'],
        ]
    */

此外，也可以傳入你自己的閉包來判斷該如何排序該 Collection 的值：

    $collection = collect([
        ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
        ['name' => 'Chair', 'colors' => ['Black']],
        ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
    ]);
    
    $sorted = $collection->sortBy(function ($product, $key) {
        return count($product['colors']);
    });
    
    $sorted->values()->all();
    
    /*
        [
            ['name' => 'Chair', 'colors' => ['Black']],
            ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
            ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
        ]
    */

若想以多個屬性來排序 Collection，可以傳入包含排序操作的陣列給 `sortBy` 方法。各個排序操作應為一個陣列，該陣列由欲排序屬性以及排序順序所組成：

    $collection = collect([
        ['name' => 'Taylor Otwell', 'age' => 34],
        ['name' => 'Abigail Otwell', 'age' => 30],
        ['name' => 'Taylor Otwell', 'age' => 36],
        ['name' => 'Abigail Otwell', 'age' => 32],
    ]);
    
    $sorted = $collection->sortBy([
        ['name', 'asc'],
        ['age', 'desc'],
    ]);
    
    $sorted->values()->all();
    
    /*
        [
            ['name' => 'Abigail Otwell', 'age' => 32],
            ['name' => 'Abigail Otwell', 'age' => 30],
            ['name' => 'Taylor Otwell', 'age' => 36],
            ['name' => 'Taylor Otwell', 'age' => 34],
        ]
    */

當以多個屬性排序 Collection 時，也可以提供閉包來定義各個排序操作：

    $collection = collect([
        ['name' => 'Taylor Otwell', 'age' => 34],
        ['name' => 'Abigail Otwell', 'age' => 30],
        ['name' => 'Taylor Otwell', 'age' => 36],
        ['name' => 'Abigail Otwell', 'age' => 32],
    ]);
    
    $sorted = $collection->sortBy([
        fn ($a, $b) => $a['name'] <=> $b['name'],
        fn ($a, $b) => $b['age'] <=> $a['age'],
    ]);
    
    $sorted->values()->all();
    
    /*
        [
            ['name' => 'Abigail Otwell', 'age' => 32],
            ['name' => 'Abigail Otwell', 'age' => 30],
            ['name' => 'Taylor Otwell', 'age' => 36],
            ['name' => 'Taylor Otwell', 'age' => 34],
        ]
    */

<a name="method-sortbydesc"></a>

#### `sortByDesc()` {.collection-method}

該方法的簽章與 [`sortBy`](#method-sortby) 方法相同，但會以相反的順序來排序該 Collection。

<a name="method-sortdesc"></a>

#### `sortDesc()` {.collection-method}

該方法會以與 [`sort`](#method-sort) 方法相反的順序來排序該 Collection：

    $collection = collect([5, 3, 1, 2, 4]);
    
    $sorted = $collection->sortDesc();
    
    $sorted->values()->all();
    
    // [5, 4, 3, 2, 1]

與 `sort` 不同，`sortDesc` 不接受傳入閉包。可以使用 [`sort`](#method-sort) 方法並使用相反的比較來代替。

<a name="method-sortkeys"></a>

#### `sortKeys()` {.collection-method}

`sortKeys` 方法以底層關聯式陣列的索引鍵來排序該 Collection：

    $collection = collect([
        'id' => 22345,
        'first' => 'John',
        'last' => 'Doe',
    ]);
    
    $sorted = $collection->sortKeys();
    
    $sorted->all();
    
    /*
        [
            'first' => 'John',
            'id' => 22345,
            'last' => 'Doe',
        ]
    */

<a name="method-sortkeysdesc"></a>

#### `sortKeysDesc()` {.collection-method}

該方法的簽章與 [`sortKeys`](#method-sortkeys) 方法相同，但會以相反的順序來排序該 Collection。

<a name="method-sortkeysusing"></a>

#### `sortKeysUsing()` {.collection-method}

`sortKeysUsing` 方法使用給定的回呼來以底層關聯式陣列的索引鍵排序該 Collection：

    $collection = collect([
        'ID' => 22345,
        'first' => 'John',
        'last' => 'Doe',
    ]);
    
    $sorted = $collection->sortKeysUsing('strnatcasecmp');
    
    $sorted->all();
    
    /*
        [
            'first' => 'John',
            'ID' => 22345,
            'last' => 'Doe',
        ]
    */

該回呼必須為回傳一個小於、等於、或大於 0 之整數的比較函式。更多資訊請參考 PHP 說明文件中有關 [`uksort`](https://www.php.net/manual/en/function.uksort.php#refsect1-function.uksort-parameters) 的部分。`uksort` 時 `sortKeysUsing` 方法內部所使用的 PHP 函式。

<a name="method-splice"></a>

#### `splice()` {.collection-method}

`splice` 方法會自指定的索引開始移除一部分的項目並將其回傳：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $chunk = $collection->splice(2);
    
    $chunk->all();
    
    // [3, 4, 5]
    
    $collection->all();
    
    // [1, 2]

可以傳入第二個引數來限制產生 Collection 的大小：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $chunk = $collection->splice(2, 1);
    
    $chunk->all();
    
    // [3]
    
    $collection->all();
    
    // [1, 2, 4, 5]

此外，也可以傳入一個包含用來取代自 Collection 內移除項目的新項目作為第三個引數：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $chunk = $collection->splice(2, 1, [10, 11]);
    
    $chunk->all();
    
    // [3]
    
    $collection->all();
    
    // [1, 2, 10, 11, 4, 5]

<a name="method-split"></a>

#### `split()` {.collection-method}

`split` 方法會將 Collection 拆解成給定數量的群組：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $groups = $collection->split(3);
    
    $groups->all();
    
    // [[1, 2], [3, 4], [5]]

<a name="method-splitin"></a>

#### `splitIn()` {.collection-method}

`splitIn` 方法會將 Collection 拆解成給定數量的群組，並會在將剩餘項目填至最後一個群組前先填滿非結尾的群組：

    $collection = collect([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    
    $groups = $collection->splitIn(3);
    
    $groups->all();
    
    // [[1, 2, 3, 4], [5, 6, 7, 8], [9, 10]]

<a name="method-sum"></a>

#### `sum()` {.collection-method}

`sum` 方法會回傳該 Collection 中所有項目的綜合：

    collect([1, 2, 3, 4, 5])->sum();
    
    // 15

若該 Collection 包含了巢狀陣列或物件，應傳入一個用來判斷要加總哪個值的索引鍵：

    $collection = collect([
        ['name' => 'JavaScript: The Good Parts', 'pages' => 176],
        ['name' => 'JavaScript: The Definitive Guide', 'pages' => 1096],
    ]);
    
    $collection->sum('pages');
    
    // 1272

此外，也可以傳入你自己的閉包來判斷該 Collection 中的哪個值要被加總：

    $collection = collect([
        ['name' => 'Chair', 'colors' => ['Black']],
        ['name' => 'Desk', 'colors' => ['Black', 'Mahogany']],
        ['name' => 'Bookcase', 'colors' => ['Red', 'Beige', 'Brown']],
    ]);
    
    $collection->sum(function ($product) {
        return count($product['colors']);
    });
    
    // 6

<a name="method-take"></a>

#### `take()` {.collection-method}

`take` 方法會回傳包含特定數量項目的新 Collection：

    $collection = collect([0, 1, 2, 3, 4, 5]);
    
    $chunk = $collection->take(3);
    
    $chunk->all();
    
    // [0, 1, 2]

也可以傳入負數整數來從該 Collection 的結尾開始取特定數量的項目：

    $collection = collect([0, 1, 2, 3, 4, 5]);
    
    $chunk = $collection->take(-2);
    
    $chunk->all();
    
    // [4, 5]

<a name="method-takeuntil"></a>

#### `takeUntil()` {.collection-method}

`takeUntil` 方法會回傳該 Collection 中的項目，直到給定的回呼回傳 `true`：

    $collection = collect([1, 2, 3, 4]);
    
    $subset = $collection->takeUntil(function ($item) {
        return $item >= 3;
    });
    
    $subset->all();
    
    // [1, 2]

也可以傳入一個簡單的值給 `takeUntil` 方法，來取直到找到指定項目之前的所有項目：

    $collection = collect([1, 2, 3, 4]);
    
    $subset = $collection->takeUntil(3);
    
    $subset->all();
    
    // [1, 2]

> **Warning** 若找不到給定的值，或是回呼從未回傳 `true`，則 `skipUntil` 方法會回傳該 Collection 中的所有項目。

<a name="method-takewhile"></a>

#### `takeWhile()` {.collection-method}

`takeWhile` 方法會回傳該 Collection 中的項目，直到給定的回呼回傳 `false`：

    $collection = collect([1, 2, 3, 4]);
    
    $subset = $collection->takeWhile(function ($item) {
        return $item < 3;
    });
    
    $subset->all();
    
    // [1, 2]

> **Warning** 若回呼從未回傳 `false`，則 `takeWhile` 方法會回傳該 Collection 中的所有項目。

<a name="method-tap"></a>

#### `tap()` {.collection-method}

`tap` 方法會將該 Collection 傳給給定的回呼，讓你能在特定的時間點上「竊聽 (Tap)」該 Collection，並在不影響該 Collection 本身的情況下對其中的項目做點事情。該 Collection 會接著被 `tap` 方法回傳：

    collect([2, 4, 3, 1, 5])
        ->sort()
        ->tap(function ($collection) {
            Log::debug('Values after sorting', $collection->values()->all());
        })
        ->shift();
    
    // 1

<a name="method-times"></a>

#### `times()` {.collection-method}

`times` 靜態方法會通過叫用給定的閉包特定次數來建立一個新的 Collection：

    $collection = Collection::times(10, function ($number) {
        return $number * 9;
    });
    
    $collection->all();
    
    // [9, 18, 27, 36, 45, 54, 63, 72, 81, 90]

<a name="method-toarray"></a>

#### `toArray()` {.collection-method}

`toArray` 方法將該 Collection 轉為純 PHP `array`。若該 Collection 的值為 [Eloquent](/docs/{{version}}/eloquent) Model，則該 Model 也會被轉為陣列：

    $collection = collect(['name' => 'Desk', 'price' => 200]);
    
    $collection->toArray();
    
    /*
        [
            ['name' => 'Desk', 'price' => 200],
        ]
    */

> **Warning** `toArray` 也會將該 Collection 中所有 `Arrayable` 實作的巢狀物件轉換為陣列。若只是想取得該 Collection 底層的原始陣列，請使用 [`all`](#method-all) 方法代替。

<a name="method-tojson"></a>

#### `toJson()` {.collection-method}

`toJson` 方法將該 Collection 轉為經過 JSON 序列化的字串：

    $collection = collect(['name' => 'Desk', 'price' => 200]);
    
    $collection->toJson();
    
    // '{"name":"Desk", "price":200}'

<a name="method-transform"></a>

#### `transform()` {.collection-method}

`transform` 方法迭代該 Collection，並以該 Collection 中的各個項目呼叫給定的回呼。該 Collection 中的項目將被這個回呼所回傳的值取代：

    $collection = collect([1, 2, 3, 4, 5]);
    
    $collection->transform(function ($item, $key) {
        return $item * 2;
    });
    
    $collection->all();
    
    // [2, 4, 6, 8, 10]

> **Warning** 與其他 Collection 方法不同，`transform` 會修改該 Collection 本身。若想建立新的 Collection，請使用 [`map`](#method-map) 方法代替。

<a name="method-undot"></a>

#### `undot()` {.collection-method}

`undot` 方法將一組使用「點 (.)」標記法的一維 Collection 展開為多維 Collection：

    $person = collect([
        'name.first_name' => 'Marie',
        'name.last_name' => 'Valentine',
        'address.line_1' => '2992 Eagle Drive',
        'address.line_2' => '',
        'address.suburb' => 'Detroit',
        'address.state' => 'MI',
        'address.postcode' => '48219'
    ]);
    
    $person = $person->undot();
    
    $person->toArray();
    
    /*
        [
            "name" => [
                "first_name" => "Marie",
                "last_name" => "Valentine",
            ],
            "address" => [
                "line_1" => "2992 Eagle Drive",
                "line_2" => "",
                "suburb" => "Detroit",
                "state" => "MI",
                "postcode" => "48219",
            ],
        ]
    */

<a name="method-union"></a>

#### `union()` {.collection-method}

`union` 方法將給定的陣列加至該 Collection 中。若給定的陣列包含了已存在於原始 Collection 中的索引鍵，則會優先使用原始 Collection 中的值：

    $collection = collect([1 => ['a'], 2 => ['b']]);
    
    $union = $collection->union([3 => ['c'], 1 => ['d']]);
    
    $union->all();
    
    // [1 => ['a'], 2 => ['b'], 3 => ['c']]

<a name="method-unique"></a>

#### `unique()` {.collection-method}

`unique` 方法會回傳該 Collection 中所有不重複的項目。回傳的 Collection 會保留原始的陣列索引鍵，因此，在下列範例中我們使用了 [`values`](#method-values) 方法來將索引鍵重設成連續的數字索引：

    $collection = collect([1, 1, 2, 2, 3, 4, 2]);
    
    $unique = $collection->unique();
    
    $unique->values()->all();
    
    // [1, 2, 3, 4]

在處理巢狀陣列或物件時，可以指定用來判斷是否重複的索引鍵：

    $collection = collect([
        ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
        ['name' => 'iPhone 5', 'brand' => 'Apple', 'type' => 'phone'],
        ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
        ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
        ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
    ]);
    
    $unique = $collection->unique('brand');
    
    $unique->values()->all();
    
    /*
        [
            ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
            ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
        ]
    */

此外，還可以傳入你自己的閉包給 `unique` 方法，來指定要用哪個值判斷項目是否重複：

    $unique = $collection->unique(function ($item) {
        return $item['brand'].$item['type'];
    });
    
    $unique->values()->all();
    
    /*
        [
            ['name' => 'iPhone 6', 'brand' => 'Apple', 'type' => 'phone'],
            ['name' => 'Apple Watch', 'brand' => 'Apple', 'type' => 'watch'],
            ['name' => 'Galaxy S6', 'brand' => 'Samsung', 'type' => 'phone'],
            ['name' => 'Galaxy Gear', 'brand' => 'Samsung', 'type' => 'watch'],
        ]
    */

`unique` 方法在比對項目值時使用了「鬆散 (Loose)」的比對方法。這標示，具有整數值的字串與一個有相同值的整數會被視為相同。請使用 [`uniqueStrict`](#method-uniquestrict) 方法來使用「嚴格 (Strict)」比對進行過濾。

> **Note** 在使用 [Eloquent Collection](/docs/{{version}}/eloquent-collections#method-unique) 時，該方法的行為有經過修改。

<a name="method-uniquestrict"></a>

#### `uniqueStrict()` {.collection-method}

該方法與 [`unique`](#method-unique) 方法的簽章一致。不過，所有的數值比對都是使用「嚴格」比對模式。

<a name="method-unless"></a>

#### `unless()` {.collection-method}

`unless` 方法只會在傳入該方法的第一個引數取值不為 `true` 時執行給定的回呼：

    $collection = collect([1, 2, 3]);
    
    $collection->unless(true, function ($collection) {
        return $collection->push(4);
    });
    
    $collection->unless(false, function ($collection) {
        return $collection->push(5);
    });
    
    $collection->all();
    
    // [1, 2, 3, 5]

可以傳入第二個回呼給 `unless` 方法。當第一個傳給 `unless` 方法的引數為 `true` 時，會呼叫第二個回呼：

    $collection = collect([1, 2, 3]);
    
    $collection->unless(true, function ($collection) {
        return $collection->push(4);
    }, function ($collection) {
        return $collection->push(5);
    });
    
    $collection->all();
    
    // [1, 2, 3, 5]

請參考 [when](#method-when) 方法以瞭解與 `unless` 相反的方法。

<a name="method-unlessempty"></a>

#### `unlessEmpty()` {.collection-method}

[`whenNotEmpty`](#method-whennotempty) 方法的別名。

<a name="method-unlessnotempty"></a>

#### `unlessNotEmpty()` {.collection-method}

[`whenEmpty`](#method-whenempty) 方法的別名。

<a name="method-unwrap"></a>

#### `unwrap()` {.collection-method}

靜態 `unwrap` 方法會可能的情況下從給定值中取得該 Collection 的底層項目：

    Collection::unwrap(collect('John Doe'));
    
    // ['John Doe']
    
    Collection::unwrap(['John Doe']);
    
    // ['John Doe']
    
    Collection::unwrap('John Doe');
    
    // 'John Doe'

<a name="method-value"></a>

#### `value()` {.collection-method}

`value` 方法會取得該 Collection 中的第一個元素內取得給定的值：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Speaker', 'price' => 400],
    ]);
    
    $value = $collection->value('price');
    
    // 200

<a name="method-values"></a>

#### `values()` {.collection-method}

`values` 方法回傳將索引鍵重設為連續整數的新 Collection：

    $collection = collect([
        10 => ['product' => 'Desk', 'price' => 200],
        11 => ['product' => 'Desk', 'price' => 200],
    ]);
    
    $values = $collection->values();
    
    $values->all();
    
    /*
        [
            0 => ['product' => 'Desk', 'price' => 200],
            1 => ['product' => 'Desk', 'price' => 200],
        ]
    */

<a name="method-when"></a>

#### `when()` {.collection-method}

`when` 方法會在傳給該方法的第一個引數取值為 `true` 時執行給定的回呼。Collection 實體與傳給 `when` 方法的第一個引數會被提供給該閉包：

    $collection = collect([1, 2, 3]);
    
    $collection->when(true, function ($collection, $value) {
        return $collection->push(4);
    });
    
    $collection->when(false, function ($collection, $value) {
        return $collection->push(5);
    });
    
    $collection->all();
    
    // [1, 2, 3, 4]

可以傳入第二個回呼給 `when` 方法。當第一個傳給 `when` 方法的引數為 `false` 時，會呼叫第二個回呼：

    $collection = collect([1, 2, 3]);
    
    $collection->when(false, function ($collection, $value) {
        return $collection->push(4);
    }, function ($collection) {
        return $collection->push(5);
    });
    
    $collection->all();
    
    // [1, 2, 3, 5]

請參考 [`unless`](#method-unless) 方法以瞭解與 `when` 相反的方法。

<a name="method-whenempty"></a>

#### `whenEmpty()` {.collection-method}

`whenEmpty` 方法會在該 Collection 為空時執行給定的回呼：

    $collection = collect(['Michael', 'Tom']);
    
    $collection->whenEmpty(function ($collection) {
        return $collection->push('Adam');
    });
    
    $collection->all();
    
    // ['Michael', 'Tom']
    
    
    $collection = collect();
    
    $collection->whenEmpty(function ($collection) {
        return $collection->push('Adam');
    });
    
    $collection->all();
    
    // ['Adam']

可以傳入第二個閉包給 `whenEmpty` 方法，該閉包會在該 Collection 不為空時被執行：

    $collection = collect(['Michael', 'Tom']);
    
    $collection->whenEmpty(function ($collection) {
        return $collection->push('Adam');
    }, function ($collection) {
        return $collection->push('Taylor');
    });
    
    $collection->all();
    
    // ['Michael', 'Tom', 'Taylor']

請參考 [`whenNotEmpty`](#method-whennotempty) 方法以瞭解與 `whenEmpty` 相反的方法。

<a name="method-whennotempty"></a>

#### `whenNotEmpty()` {.collection-method}

`whenNotEmpty` 方法會在該 Collection 不為空時執行給定的回呼：

    $collection = collect(['michael', 'tom']);
    
    $collection->whenNotEmpty(function ($collection) {
        return $collection->push('adam');
    });
    
    $collection->all();
    
    // ['michael', 'tom', 'adam']
    
    
    $collection = collect();
    
    $collection->whenNotEmpty(function ($collection) {
        return $collection->push('adam');
    });
    
    $collection->all();
    
    // []

可以傳入第二個閉包給 `whenNotEmpty` 方法，該閉包會在該 Collection 為空時被執行：

    $collection = collect();
    
    $collection->whenNotEmpty(function ($collection) {
        return $collection->push('adam');
    }, function ($collection) {
        return $collection->push('taylor');
    });
    
    $collection->all();
    
    // ['taylor']

請參考 [`whenEmpty`](#method-whenempty) 方法以瞭解與 `whenNotEmpty` 相反的方法。

<a name="method-where"></a>

#### `where()` {.collection-method}

`where` 方法使用給定的索引鍵／值配對來篩選該 Collection：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 100],
        ['product' => 'Bookcase', 'price' => 150],
        ['product' => 'Door', 'price' => 100],
    ]);
    
    $filtered = $collection->where('price', 100);
    
    $filtered->all();
    
    /*
        [
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Door', 'price' => 100],
        ]
    */

`where` 方法在比對項目值時使用了「鬆散 (Loose)」的比對方法。這標示，具有整數值的字串與一個有相同值的整數會被視為相同。請使用 [`whereStrict`](#method-wherestrict) 方法來使用「嚴格 (Strict)」比對進行過濾。

也可以選擇性地傳入一個比較運算子來作為該方法的第二個參數。支援的運算子有 '===', '!==', '!=', '==', '=', '<>', '>', '<', '>=', 與 '<='：

    $collection = collect([
        ['name' => 'Jim', 'deleted_at' => '2019-01-01 00:00:00'],
        ['name' => 'Sally', 'deleted_at' => '2019-01-02 00:00:00'],
        ['name' => 'Sue', 'deleted_at' => null],
    ]);
    
    $filtered = $collection->where('deleted_at', '!=', null);
    
    $filtered->all();
    
    /*
        [
            ['name' => 'Jim', 'deleted_at' => '2019-01-01 00:00:00'],
            ['name' => 'Sally', 'deleted_at' => '2019-01-02 00:00:00'],
        ]
    */

<a name="method-wherestrict"></a>

#### `whereStrict()` {.collection-method}

該方法與 [`where`](#method-where) 方法的簽章一致。不過，所有的數值比對都是使用「嚴格」比對模式。

<a name="method-wherebetween"></a>

#### `whereBetween()` {.collection-method}

`whereBetween` 方法會判斷特定項目值是否介於給定範圍來篩選該 Collection：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 80],
        ['product' => 'Bookcase', 'price' => 150],
        ['product' => 'Pencil', 'price' => 30],
        ['product' => 'Door', 'price' => 100],
    ]);
    
    $filtered = $collection->whereBetween('price', [100, 200]);
    
    $filtered->all();
    
    /*
        [
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Bookcase', 'price' => 150],
            ['product' => 'Door', 'price' => 100],
        ]
    */

<a name="method-wherein"></a>

#### `whereIn()` {.collection-method}

`whereIn` 方法會從該 Collection 中移除項目值不包含在給定陣列中的項目：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 100],
        ['product' => 'Bookcase', 'price' => 150],
        ['product' => 'Door', 'price' => 100],
    ]);
    
    $filtered = $collection->whereIn('price', [150, 200]);
    
    $filtered->all();
    
    /*
        [
            ['product' => 'Desk', 'price' => 200],
            ['product' => 'Bookcase', 'price' => 150],
        ]
    */

`whereIn` 方法在比對項目值時使用了「鬆散 (Loose)」的比對方法。這標示，具有整數值的字串與一個有相同值的整數會被視為相同。請使用 [`whereInStrict`](#method-whereinstrict) 方法來使用「嚴格 (Strict)」比對進行過濾。

<a name="method-whereinstrict"></a>

#### `whereInStrict()` {.collection-method}

該方法與 [`whereIn`](#method-wherein) 方法的簽章一致。不過，所有的數值比對都是使用「嚴格」比對模式。

<a name="method-whereinstanceof"></a>

#### `whereInstanceOf()` {.collection-method}

`whereInstanceOf` 方法使用給定的類別型別來篩選該 Collection：

    use App\Models\User;
    use App\Models\Post;
    
    $collection = collect([
        new User,
        new User,
        new Post,
    ]);
    
    $filtered = $collection->whereInstanceOf(User::class);
    
    $filtered->all();
    
    // [App\Models\User, App\Models\User]

<a name="method-wherenotbetween"></a>

#### `whereNotBetween()` {.collection-method}

`whereNotBetween` 方法會判斷特定項目值是否不在給定範圍來篩選該 Collection：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 80],
        ['product' => 'Bookcase', 'price' => 150],
        ['product' => 'Pencil', 'price' => 30],
        ['product' => 'Door', 'price' => 100],
    ]);
    
    $filtered = $collection->whereNotBetween('price', [100, 200]);
    
    $filtered->all();
    
    /*
        [
            ['product' => 'Chair', 'price' => 80],
            ['product' => 'Pencil', 'price' => 30],
        ]
    */

<a name="method-wherenotin"></a>

#### `whereNotIn()` {.collection-method}

`whereNotIn` 方法會從該 Collection 中移除項目值不在給定陣列中的項目：

    $collection = collect([
        ['product' => 'Desk', 'price' => 200],
        ['product' => 'Chair', 'price' => 100],
        ['product' => 'Bookcase', 'price' => 150],
        ['product' => 'Door', 'price' => 100],
    ]);
    
    $filtered = $collection->whereNotIn('price', [150, 200]);
    
    $filtered->all();
    
    /*
        [
            ['product' => 'Chair', 'price' => 100],
            ['product' => 'Door', 'price' => 100],
        ]
    */

`whereNotIn` 方法在比對項目值時使用了「鬆散 (Loose)」的比對方法。這標示，具有整數值的字串與一個有相同值的整數會被視為相同。請使用 [`whereNotInStrict`](#method-wherenotinstrict) 方法來使用「嚴格 (Strict)」比對進行過濾。

<a name="method-wherenotinstrict"></a>

#### `whereNotInStrict()` {.collection-method}

該方法與 [`whereNotIn`](#method-wherenotin) 方法的簽章一致。不過，所有的數值比對都是使用「嚴格」比對模式。

<a name="method-wherenotnull"></a>

#### `whereNotNull()` {.collection-method}

`whereNotNull` 方法回傳該 Collection 中給定索引鍵不為 `null` 的項目：

    $collection = collect([
        ['name' => 'Desk'],
        ['name' => null],
        ['name' => 'Bookcase'],
    ]);
    
    $filtered = $collection->whereNotNull('name');
    
    $filtered->all();
    
    /*
        [
            ['name' => 'Desk'],
            ['name' => 'Bookcase'],
        ]
    */

<a name="method-wherenull"></a>

#### `whereNull()` {.collection-method}

`whereNull` 方法回傳該 Collection 中給定索引鍵為 `null` 的項目：

    $collection = collect([
        ['name' => 'Desk'],
        ['name' => null],
        ['name' => 'Bookcase'],
    ]);
    
    $filtered = $collection->whereNull('name');
    
    $filtered->all();
    
    /*
        [
            ['name' => null],
        ]
    */

<a name="method-wrap"></a>

#### `wrap()` {.collection-method}

靜態 `wrap` 方法會在可能的情況下將給定值包裝成 Collection：

    use Illuminate\Support\Collection;
    
    $collection = Collection::wrap('John Doe');
    
    $collection->all();
    
    // ['John Doe']
    
    $collection = Collection::wrap(['John Doe']);
    
    $collection->all();
    
    // ['John Doe']
    
    $collection = Collection::wrap(collect('John Doe'));
    
    $collection->all();
    
    // ['John Doe']

<a name="method-zip"></a>

#### `zip()` {.collection-method}

`zip` 方法將給定陣列與原始 Collection 在其對應索引上合併在一起：

    $collection = collect(['Chair', 'Desk']);
    
    $zipped = $collection->zip([100, 200]);
    
    $zipped->all();
    
    // [['Chair', 100], ['Desk', 200]]

<a name="higher-order-messages"></a>

## 高階訊息

Collection 也提供了「高階訊息 (Higher Order Message)」的支援，在 Collection 上進行常見操作的時候可用來當作捷徑。有提供高階訊息的 Collection 方法為：[`average`](#method-average), [`avg`](#method-avg), [`contains`](#method-contains), [`each`](#method-each), [`every`](#method-every), [`filter`](#method-filter), [`first`](#method-first), [`flatMap`](#method-flatmap), [`groupBy`](#method-groupby), [`keyBy`](#method-keyby), [`map`](#method-map), [`max`](#method-max), [`min`](#method-min), [`partition`](#method-partition), [`reject`](#method-reject), [`skipUntil`](#method-skipuntil), [`skipWhile`](#method-skipwhile), [`some`](#method-some), [`sortBy`](#method-sortby), [`sortByDesc`](#method-sortbydesc), [`sum`](#method-sum), [`takeUntil`](#method-takeuntil), [`takeWhile`](#method-takewhile) 與 [`unique`](#method-unique)。

各個高階訊息都可作為 Collection 實體上的一個動態屬性來存取。舉例來說，我們來使用 `each` 高階訊息，在 Collection 中的各個物件上呼叫一個方法：

    use App\Models\User;
    
    $users = User::where('votes', '>', 500)->get();
    
    $users->each->markAsVip();

類似地，我們也可以使用 `sum` 高階訊息來取得使用者 Collection 中的所有「votes」加總：

    $users = User::where('group', 'Development')->get();
    
    return $users->sum->votes;

<a name="lazy-collections"></a>

## Lazy Collection

<a name="lazy-collection-introduction"></a>

### 簡介

> **Warning** 在開始學習有關 Laravel 的 Lazy Collection 之前，建議先花點時間熟悉 [PHP Generator](https://www.php.net/manual/en/language.generators.overview.php)。

為了補強已經很強大的 `Collection` 類別，`LazyCollection` 類別使用了 PHP 的 [Generator](https://www.php.net/manual/en/language.generators.overview.php) 來讓你能在不使用太多記憶體的情況下處理非常大量的資料。

舉例來說，假設你的專案需要處理一個好幾 GB 的日誌檔，但又想利用 Laravel 的 Collection 方法來解析日誌。比起一次將整個檔案讀入記憶體，使用 Lazy Collection 的話，記憶體內一次就只會保持一小部分的檔案。

    use App\Models\LogEntry;
    use Illuminate\Support\LazyCollection;
    
    LazyCollection::make(function () {
        $handle = fopen('log.txt', 'r');
    
        while (($line = fgets($handle)) !== false) {
            yield $line;
        }
    })->chunk(4)->map(function ($lines) {
        return LogEntry::fromLines($lines);
    })->each(function (LogEntry $logEntry) {
        // Process the log entry...
    });

或者，想像一下需要迭代 10,000 個 Eloquent Model。在使用傳統 Laravel Collection 時，必須要一次將 10,000 個 Eloquent Model 讀入記憶體：

    use App\Models\User;
    
    $users = User::all()->filter(function ($user) {
        return $user->id > 500;
    });

不過，Query Builder 的 `cursor` 方法回傳了一個 `LazyCollection` 實體。這樣一來就能讓你在只執行單一資料庫查詢的情況下，一次只將一個 Eloquent Model 讀入記憶體。在這個例子中，`filter` 回呼只會在實際迭代到個別使用者的時候才會被執行，讓我們能有效降低記憶體使用：

    use App\Models\User;
    
    $users = User::cursor()->filter(function ($user) {
        return $user->id > 500;
    });
    
    foreach ($users as $user) {
        echo $user->id;
    }

<a name="creating-lazy-collections"></a>

### 建立 Lazy Collection

若要建立 Lazy Collection 實體，應傳入 PHP Generator 方法至該 Collection 的 `make` 方法：

    use Illuminate\Support\LazyCollection;
    
    LazyCollection::make(function () {
        $handle = fopen('log.txt', 'r');
    
        while (($line = fgets($handle)) !== false) {
            yield $line;
        }
    });

<a name="the-enumerable-contract"></a>

### Enumerable Contract

幾乎所有在 `Collection` 類別中可用的方法都可在 `LazyCollection` 類別上使用。這兩個類別都實作了 `Illuminate\Support\Enumerable` Contract，該介面定義了下列方法：

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
</style>

<div class="collection-method-list" markdown="1">

[all](#method-all) [average](#method-average) [avg](#method-avg) [chunk](#method-chunk) [chunkWhile](#method-chunkwhile) [collapse](#method-collapse) [collect](#method-collect) [combine](#method-combine) [concat](#method-concat) [contains](#method-contains) [containsStrict](#method-containsstrict) [count](#method-count) [countBy](#method-countBy) [crossJoin](#method-crossjoin) [dd](#method-dd) [diff](#method-diff) [diffAssoc](#method-diffassoc) [diffKeys](#method-diffkeys) [dump](#method-dump) [duplicates](#method-duplicates) [duplicatesStrict](#method-duplicatesstrict) [each](#method-each) [eachSpread](#method-eachspread) [every](#method-every) [except](#method-except) [filter](#method-filter) [first](#method-first) [firstOrFail](#method-first-or-fail) [firstWhere](#method-first-where) [flatMap](#method-flatmap) [flatten](#method-flatten) [flip](#method-flip) [forPage](#method-forpage) [get](#method-get) [groupBy](#method-groupby) [has](#method-has) [implode](#method-implode) [intersect](#method-intersect) [intersectByKeys](#method-intersectbykeys) [isEmpty](#method-isempty) [isNotEmpty](#method-isnotempty) [join](#method-join) [keyBy](#method-keyby) [keys](#method-keys) [last](#method-last) [macro](#method-macro) [make](#method-make) [map](#method-map) [mapInto](#method-mapinto) [mapSpread](#method-mapspread) [mapToGroups](#method-maptogroups) [mapWithKeys](#method-mapwithkeys) [max](#method-max) [median](#method-median) [merge](#method-merge) [mergeRecursive](#method-mergerecursive) [min](#method-min) [mode](#method-mode) [nth](#method-nth) [only](#method-only) [pad](#method-pad) [partition](#method-partition) [pipe](#method-pipe) [pluck](#method-pluck) [random](#method-random) [reduce](#method-reduce) [reject](#method-reject) [replace](#method-replace) [replaceRecursive](#method-replacerecursive) [reverse](#method-reverse) [search](#method-search) [shuffle](#method-shuffle) [skip](#method-skip) [slice](#method-slice) [sole](#method-sole) [some](#method-some) [sort](#method-sort) [sortBy](#method-sortby) [sortByDesc](#method-sortbydesc) [sortKeys](#method-sortkeys) [sortKeysDesc](#method-sortkeysdesc) [split](#method-split) [sum](#method-sum) [take](#method-take) [tap](#method-tap) [times](#method-times) [toArray](#method-toarray) [toJson](#method-tojson) [union](#method-union) [unique](#method-unique) [uniqueStrict](#method-uniquestrict) [unless](#method-unless) [unlessEmpty](#method-unlessempty) [unlessNotEmpty](#method-unlessnotempty) [unwrap](#method-unwrap) [values](#method-values) [when](#method-when) [whenEmpty](#method-whenempty) [whenNotEmpty](#method-whennotempty) [where](#method-where) [whereStrict](#method-wherestrict) [whereBetween](#method-wherebetween) [whereIn](#method-wherein) [whereInStrict](#method-whereinstrict) [whereInstanceOf](#method-whereinstanceof) [whereNotBetween](#method-wherenotbetween) [whereNotIn](#method-wherenotin) [whereNotInStrict](#method-wherenotinstrict) [wrap](#method-wrap) [zip](#method-zip)

</div>

> **Warning** 會修改 (Mutate) Collection 的方法 (如 `shift`、`pop`、`prepend` 等) 在 `LazyCollection` 類別上都 **不可用**。

<a name="lazy-collection-methods"></a>

### Lazy Collection 方法

除了在 `Enumerable` Contract 上定義的方法外，`LazyCollection` 類別還包含了下列方法：

<a name="method-takeUntilTimeout"></a>

#### `takeUntilTimeout()` {.collection-method}

`takeUntilTimeout` 方法會回傳一個在特定時間前枚舉出值的新 Lazy Collection。在這個時間之後，該 Collection 便會停止枚舉：

    $lazyCollection = LazyCollection::times(INF)
        ->takeUntilTimeout(now()->addMinute());
    
    $lazyCollection->each(function ($number) {
        dump($number);
    
        sleep(1);
    });
    
    // 1
    // 2
    // ...
    // 58
    // 59

為了說明如何使用這個方法，請想像一下有個會使用 Cursor 將發票存入資料庫的專案。我們可以定義一個每 15 分鐘會執行的[排程任務](/docs/{{version}}/scheduling)，並在該任務中只處理 14 分鐘的發票：

    use App\Models\Invoice;
    use Illuminate\Support\Carbon;
    
    Invoice::pending()->cursor()
        ->takeUntilTimeout(
            Carbon::createFromTimestamp(LARAVEL_START)->add(14, 'minutes')
        )
        ->each(fn ($invoice) => $invoice->submit());

<a name="method-tapEach"></a>

#### `tapEach()` {.collection-method}

`each` 方法會直接為 Collection 中各個項目呼叫給定的回呼，而 `tapEach` 方法則只會對被從清單中取出的項目一個一個呼叫給定的回呼：

    // 還未傾印任何結果...
    $lazyCollection = LazyCollection::times(INF)->tapEach(function ($value) {
        dump($value);
    });
    
    // 傾印三個項目...
    $array = $lazyCollection->take(3)->all();
    
    // 1
    // 2
    // 3

<a name="method-remember"></a>

#### `remember()` {.collection-method}

`remember` 方法會回傳一個新的 Lazy Collection，該 Lazy Collection 會記住任何已經被枚舉過的值，並在接下來的 Collection 枚舉時不會再取出這些值：

    // 還未執行任何查詢...
    $users = User::cursor()->remember();
    
    // 已執行查詢...
    // 已從資料庫中填入了前 5 位使用者...
    $users->take(5)->all();
    
    // 從 Collection 的 Cache 中取得前 5 位使用者...
    // 剩下的則從資料庫中取得並填過來...
    $users->take(20)->all();
