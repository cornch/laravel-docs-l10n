---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/79/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 44.75
---

# 輔助函式

- [簡介](#introduction)
- [可用方法](#available-methods)
- [其他公用程式](#other-utilities)
  - [效能評定 (Benchmark)](#benchmarking)
  - [Date - 日期](#dates)
  - [Lottery](#lottery)
  - [Pipeline](#pipeline)
  - [Sleep](#sleep)
  

<a name="introduction"></a>

## 簡介

Laravel 提供了多種全域 PHP「輔助函式」。這些函式中，大部分都是 Laravel 本身有在使用的。不過，若你覺得這些方法很方便的話，也可以在你自己的專案內使用。

<a name="available-methods"></a>

## 可用的方法

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
<a name="arrays-and-objects-method-list"></a>

### 陣列與物件

<div class="collection-method-list" markdown="1">
[Arr::accessible](#method-array-accessible)
[Arr::add](#method-array-add)
[Arr::collapse](#method-array-collapse)
[Arr::crossJoin](#method-array-crossjoin)
[Arr::divide](#method-array-divide)
[Arr::dot](#method-array-dot)
[Arr::except](#method-array-except)
[Arr::exists](#method-array-exists)
[Arr::first](#method-array-first)
[Arr::flatten](#method-array-flatten)
[Arr::forget](#method-array-forget)
[Arr::get](#method-array-get)
[Arr::has](#method-array-has)
[Arr::hasAny](#method-array-hasany)
[Arr::isAssoc](#method-array-isassoc)
[Arr::isList](#method-array-islist)
[Arr::join](#method-array-join)
[Arr::keyBy](#method-array-keyby)
[Arr::last](#method-array-last)
[Arr::map](#method-array-map)
[Arr::mapWithKeys](#method-array-map-with-keys)
[Arr::only](#method-array-only)
[Arr::pluck](#method-array-pluck)
[Arr::prepend](#method-array-prepend)
[Arr::prependKeysWith](#method-array-prependkeyswith)
[Arr::pull](#method-array-pull)
[Arr::query](#method-array-query)
[Arr::random](#method-array-random)
[Arr::set](#method-array-set)
[Arr::shuffle](#method-array-shuffle)
[Arr::sort](#method-array-sort)
[Arr::sortDesc](#method-array-sort-desc)
[Arr::sortRecursive](#method-array-sort-recursive)
[Arr::sortRecursiveDesc](#method-array-sort-recursive-desc)
[Arr::take](#method-array-take)
[Arr::toCssClasses](#method-array-to-css-classes)
[Arr::toCssStyles](#method-array-to-css-styles)
[Arr::undot](#method-array-undot)
[Arr::where](#method-array-where)
[Arr::whereNotNull](#method-array-where-not-null)
[Arr::wrap](#method-array-wrap)
[data_fill](#method-data-fill)
[data_get](#method-data-get)
[data_set](#method-data-set)
[data_forget](#method-data-forget)
[head](#method-head)
[last](#method-last)

</div>
<a name="numbers-method-list"></a>

### Numbers

<div class="collection-method-list" markdown="1">
[Number::abbreviate](#method-number-abbreviate)
[Number::clamp](#method-number-clamp)
[Number::currency](#method-number-currency)
[Number::fileSize](#method-number-file-size)
[Number::forHumans](#method-number-for-humans)
[Number::format](#method-number-format)
[Number::ordinal](#method-number-ordinal)
[Number::percentage](#method-number-percentage)
[Number::spell](#method-number-spell)
[Number::useLocale](#method-number-use-locale)
[Number::withLocale](#method-number-with-locale)

</div>
<a name="paths-method-list"></a>

### 路徑

<div class="collection-method-list" markdown="1">
[app_path](#method-app-path)
[base_path](#method-base-path)
[config_path](#method-config-path)
[database_path](#method-database-path)
[lang_path](#method-lang-path)
[mix](#method-mix)
[public_path](#method-public-path)
[resource_path](#method-resource-path)
[storage_path](#method-storage-path)

</div>
<a name="urls-method-list"></a>

### URL

<div class="collection-method-list" markdown="1">
[action](#method-action)
[asset](#method-asset)
[route](#method-route)
[secure_asset](#method-secure-asset)
[secure_url](#method-secure-url)
[to_route](#method-to-route)
[url](#method-url)

</div>
<a name="miscellaneous-method-list"></a>

### 其他

<div class="collection-method-list" markdown="1">
[abort](#method-abort)
[abort_if](#method-abort-if)
[abort_unless](#method-abort-unless)
[app](#method-app)
[auth](#method-auth)
[back](#method-back)
[bcrypt](#method-bcrypt)
[blank](#method-blank)
[broadcast](#method-broadcast)
[cache](#method-cache)
[class_uses_recursive](#method-class-uses-recursive)
[collect](#method-collect)
[config](#method-config)
[cookie](#method-cookie)
[csrf_field](#method-csrf-field)
[csrf_token](#method-csrf-token)
[decrypt](#method-decrypt)
[dd](#method-dd)
[dispatch](#method-dispatch)
[dispatch_sync](#method-dispatch-sync)
[dump](#method-dump)
[encrypt](#method-encrypt)
[env](#method-env)
[event](#method-event)
[fake](#method-fake)
[filled](#method-filled)
[info](#method-info)
[literal](#method-literal)
[logger](#method-logger)
[method_field](#method-method-field)
[now](#method-now)
[old](#method-old)
[once](#method-once)
[optional](#method-optional)
[policy](#method-policy)
[redirect](#method-redirect)
[report](#method-report)
[report_if](#method-report-if)
[report_unless](#method-report-unless)
[request](#method-request)
[rescue](#method-rescue)
[resolve](#method-resolve)
[response](#method-response)
[retry](#method-retry)
[session](#method-session)
[tap](#method-tap)
[throw_if](#method-throw-if)
[throw_unless](#method-throw-unless)
[today](#method-today)
[trait_uses_recursive](#method-trait-uses-recursive)
[transform](#method-transform)
[validator](#method-validator)
[value](#method-value)
[view](#method-view)
[with](#method-with)

</div>
<a name="arrays"></a>

## 陣列與物件

<a name="method-array-accessible"></a>

#### `Arr::accessible()` {.collection-method .first-collection-method}

`Arr::accessible` 方法判斷給定的值是否能以陣列方式存取：

    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    
    $isAccessible = Arr::accessible(['a' => 1, 'b' => 2]);
    
    // true
    
    $isAccessible = Arr::accessible(new Collection);
    
    // true
    
    $isAccessible = Arr::accessible('abc');
    
    // false
    
    $isAccessible = Arr::accessible(new stdClass);
    
    // false
<a name="method-array-add"></a>

#### `Arr::add()` {.collection-method}

`Arr::add` 方法會在給定的索引鍵 / 值配對不存在於給定陣列、或是該索引鍵的值 `null` 時將該配對新增到陣列上：

    use Illuminate\Support\Arr;
    
    $array = Arr::add(['name' => 'Desk'], 'price', 100);
    
    // ['name' => 'Desk', 'price' => 100]
    
    $array = Arr::add(['name' => 'Desk', 'price' => null], 'price', 100);
    
    // ['name' => 'Desk', 'price' => 100]
<a name="method-array-collapse"></a>

#### `Arr::collapse()` {.collection-method}

`Arr::collapse` 方法將一組陣列的陣列^[坍縮](Collapse)成單一陣列：

    use Illuminate\Support\Arr;
    
    $array = Arr::collapse([[1, 2, 3], [4, 5, 6], [7, 8, 9]]);
    
    // [1, 2, 3, 4, 5, 6, 7, 8, 9]
<a name="method-array-crossjoin"></a>

#### `Arr::crossJoin()` {.collection-method}

`Arr::crossJoin` 方法^[交叉合併](Cross Join)給定的陣列，產生一個包含所有可能^[排列](Permutation)的^[笛卡兒積](Cartesian Product)：

    use Illuminate\Support\Arr;
    
    $matrix = Arr::crossJoin([1, 2], ['a', 'b']);
    
    /*
        [
            [1, 'a'],
            [1, 'b'],
            [2, 'a'],
            [2, 'b'],
        ]
    */
    
    $matrix = Arr::crossJoin([1, 2], ['a', 'b'], ['I', 'II']);
    
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
<a name="method-array-divide"></a>

#### `Arr::divide()` {.collection-method}

`Arr::divide` 方法回傳兩個陣列：一個陣列包含給定陣列的索引鍵，而另一個陣列則包含給定陣列的值：

    use Illuminate\Support\Arr;
    
    [$keys, $values] = Arr::divide(['name' => 'Desk']);
    
    // $keys: ['name']
    
    // $values: ['Desk']
<a name="method-array-dot"></a>

#### `Arr::dot()` {.collection-method}

`Arr::dot` 方法將多為陣列^[扁平化](Flatten)為一個使用「點 (.)」標記法來表示深度的一維陣列：

    use Illuminate\Support\Arr;
    
    $array = ['products' => ['desk' => ['price' => 100]]];
    
    $flattened = Arr::dot($array);
    
    // ['products.desk.price' => 100]
<a name="method-array-except"></a>

#### `Arr::except()` {.collection-method}

`Arr::except` 方法從陣列中移除給定的索引鍵 / 值配對：

    use Illuminate\Support\Arr;
    
    $array = ['name' => 'Desk', 'price' => 100];
    
    $filtered = Arr::except($array, ['price']);
    
    // ['name' => 'Desk']
<a name="method-array-exists"></a>

#### `Arr::exists()` {.collection-method}

`Arr::exists` 方法會檢查給定的索引鍵是否存在於提供的陣列中：

    use Illuminate\Support\Arr;
    
    $array = ['name' => 'John Doe', 'age' => 17];
    
    $exists = Arr::exists($array, 'name');
    
    // true
    
    $exists = Arr::exists($array, 'salary');
    
    // false
<a name="method-array-first"></a>

#### `Arr::first()` {.collection-method}

`Arr::first` 方法會回傳該陣列中通過給定布林測試的第一個元素：

    use Illuminate\Support\Arr;
    
    $array = [100, 200, 300];
    
    $first = Arr::first($array, function (int $value, int $key) {
        return $value >= 150;
    });
    
    // 200
也可以在第三個引數上提供一個預設值給該方法。若沒有任何值通過條件測試，就會回傳這個預設值：

    use Illuminate\Support\Arr;
    
    $first = Arr::first($array, $callback, $default);
<a name="method-array-flatten"></a>

#### `Arr::flatten()` {.collection-method}

`Arr::flatten` 方法會將一個多維陣列^[扁平化](Flatten)為單一維度：

    use Illuminate\Support\Arr;
    
    $array = ['name' => 'Joe', 'languages' => ['PHP', 'Ruby']];
    
    $flattened = Arr::flatten($array);
    
    // ['Joe', 'PHP', 'Ruby']
<a name="method-array-forget"></a>

#### `Arr::forget()` {.collection-method}

`Arr::forget` 方法使用「點 (.)」標記法來在多層巢狀陣列中移除給定的索引鍵 / 值配對：

    use Illuminate\Support\Arr;
    
    $array = ['products' => ['desk' => ['price' => 100]]];
    
    Arr::forget($array, 'products.desk');
    
    // ['products' => []]
<a name="method-array-get"></a>

#### `Arr::get()` {.collection-method}

`Arr::get` 方法使用「點 (.)」標記法來在多層巢狀陣列中取值：

    use Illuminate\Support\Arr;
    
    $array = ['products' => ['desk' => ['price' => 100]]];
    
    $price = Arr::get($array, 'products.desk.price');
    
    // 100
`Arr::get` 還接受一個預設值。若指定的索引鍵不存在時會回傳該預設值：

    use Illuminate\Support\Arr;
    
    $discount = Arr::get($array, 'products.desk.discount', 0);
    
    // 0
<a name="method-array-has"></a>

#### `Arr::has()` {.collection-method}

`Arr::has` 方法使用「點 (.)」標記法來檢查給定的一個或多個項目是否存在：

    use Illuminate\Support\Arr;
    
    $array = ['product' => ['name' => 'Desk', 'price' => 100]];
    
    $contains = Arr::has($array, 'product.name');
    
    // true
    
    $contains = Arr::has($array, ['product.price', 'product.discount']);
    
    // false
<a name="method-array-hasany"></a>

#### `Arr::hasAny()` {.collection-method}

`Arr::hasAny` 方法使用「點 (.)」標記法來檢查給定的多個項目中是否只少有一個存在：

    use Illuminate\Support\Arr;
    
    $array = ['product' => ['name' => 'Desk', 'price' => 100]];
    
    $contains = Arr::hasAny($array, 'product.name');
    
    // true
    
    $contains = Arr::hasAny($array, ['product.name', 'product.discount']);
    
    // true
    
    $contains = Arr::hasAny($array, ['category', 'product.discount']);
    
    // false
<a name="method-array-isassoc"></a>

#### `Arr::isAssoc()` {.collection-method}

若給定的陣列為^[關聯式陣列](Associative Array)，`Arr::isAssoc` 方法會回傳 `true`。當某個陣列的索引鍵不是以 0 開始依序排列的數字時，就是「關聯式」的陣列：

    use Illuminate\Support\Arr;
    
    $isAssoc = Arr::isAssoc(['product' => ['name' => 'Desk', 'price' => 100]]);
    
    // true
    
    $isAssoc = Arr::isAssoc([1, 2, 3]);
    
    // false
<a name="method-array-islist"></a>

#### `Arr::isList()` {.collection-method}

若給定陣列的索引鍵是從 0 開始的有序整數的話，`Arr::isList` 方法會回傳 `true`：

    use Illuminate\Support\Arr;
    
    $isList = Arr::isList(['foo', 'bar', 'baz']);
    
    // true
    
    $isList = Arr::isList(['product' => ['name' => 'Desk', 'price' => 100]]);
    
    // false
<a name="method-array-join"></a>

#### `Arr::join()` {.collection-method}

`Arr::join` 方法可使用字串來將各個陣列元素串接在一起。也可以用該方法的第二個引數來指定用於串接陣列中最後一個元素的字串：

    use Illuminate\Support\Arr;
    
    $array = ['Tailwind', 'Alpine', 'Laravel', 'Livewire'];
    
    $joined = Arr::join($array, ', ');
    
    // Tailwind, Alpine, Laravel, Livewire
    
    $joined = Arr::join($array, ', ', ' and ');
    
    // Tailwind, Alpine, Laravel and Livewire
<a name="method-array-keyby"></a>

#### `Arr::keyBy()` {.collection-method}

`Arr::keyBy` 方法依照給定的索引鍵來為該陣列加上索引鍵。若多個項目有相同的索引鍵，則新的陣列中只會包含最後一個項目：

    use Illuminate\Support\Arr;
    
    $array = [
        ['product_id' => 'prod-100', 'name' => 'Desk'],
        ['product_id' => 'prod-200', 'name' => 'Chair'],
    ];
    
    $keyed = Arr::keyBy($array, 'product_id');
    
    /*
        [
            'prod-100' => ['product_id' => 'prod-100', 'name' => 'Desk'],
            'prod-200' => ['product_id' => 'prod-200', 'name' => 'Chair'],
        ]
    */
<a name="method-array-last"></a>

#### `Arr::last()` {.collection-method}

`Arr::last` 方法會回傳該陣列中通過給定布林測試的最後一個元素：

    use Illuminate\Support\Arr;
    
    $array = [100, 200, 300, 110];
    
    $last = Arr::last($array, function (int $value, int $key) {
        return $value >= 150;
    });
    
    // 300
可以在第三個引數上提供一個預設值給該方法。若沒有任何值通過條件測試，就會回傳這個預設值：

    use Illuminate\Support\Arr;
    
    $last = Arr::last($array, $callback, $default);
<a name="method-array-map"></a>

#### `Arr::map()` {.collection-method}

`Arr::map` 可用於迭代整個陣列，並將各個陣列值與索引鍵傳入給定的回呼中。陣列值會被回呼中回傳的值給取代：

    use Illuminate\Support\Arr;
    
    $array = ['first' => 'james', 'last' => 'kirk'];
    
    $mapped = Arr::map($array, function (string $value, string $key) {
        return ucfirst($value);
    });
    
    // ['first' => 'James', 'last' => 'Kirk']
<a name="method-array-map-with-keys"></a>

#### `Arr::mapWithKeys()` {.collection-method}

`Arr::mapWithKeys` 方法會迭代該陣列，並將各個值傳入給定的回呼。該回呼應回傳一個包含單一索引鍵／值配對的關聯性陣列：

    use Illuminate\Support\Arr;
    
    $array = [
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
    ];
    
    $mapped = Arr::mapWithKeys($array, function (array $item, int $key) {
        return [$item['email'] => $item['name']];
    });
    
    /*
        [
            'john@example.com' => 'John',
            'jane@example.com' => 'Jane',
        ]
    */
<a name="method-array-only"></a>

#### `Arr::only()` {.collection-method}

`Arr::only` 方法回傳給定陣列中特定的索引鍵 / 值配對：

    use Illuminate\Support\Arr;
    
    $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
    
    $slice = Arr::only($array, ['name', 'price']);
    
    // ['name' => 'Desk', 'price' => 100]
<a name="method-array-pluck"></a>

#### `Arr::pluck()` {.collection-method}

`Arr::pluck` 方法可從給定陣列中取得給定索引鍵內的所有值：

    use Illuminate\Support\Arr;
    
    $array = [
        ['developer' => ['id' => 1, 'name' => 'Taylor']],
        ['developer' => ['id' => 2, 'name' => 'Abigail']],
    ];
    
    $names = Arr::pluck($array, 'developer.name');
    
    // ['Taylor', 'Abigail']
也可以指定產生的清單要如何設定索引鍵：

    use Illuminate\Support\Arr;
    
    $names = Arr::pluck($array, 'developer.name', 'developer.id');
    
    // [1 => 'Taylor', 2 => 'Abigail']
<a name="method-array-prepend"></a>

#### `Arr::prepend()` {.collection-method}

`Arr::prepend` 方法會將某個項目放到該陣列的最前面：

    use Illuminate\Support\Arr;
    
    $array = ['one', 'two', 'three', 'four'];
    
    $array = Arr::prepend($array, 'zero');
    
    // ['zero', 'one', 'two', 'three', 'four']
若有需要，也可以指定該值要使用的索引鍵：

    use Illuminate\Support\Arr;
    
    $array = ['price' => 100];
    
    $array = Arr::prepend($array, 'Desk', 'name');
    
    // ['name' => 'Desk', 'price' => 100]
<a name="method-array-prependkeyswith"></a>

#### `Arr::prependKeysWith()` {.collection-method}

`Arr::prependKeysWith` 會將關聯式陣列中，將所有的索引鍵名稱加上給定的前置詞：

    use Illuminate\Support\Arr;
    
    $array = [
        'name' => 'Desk',
        'price' => 100,
    ];
    
    $keyed = Arr::prependKeysWith($array, 'product.');
    
    /*
        [
            'product.name' => 'Desk',
            'product.price' => 100,
        ]
    */
<a name="method-array-pull"></a>

#### `Arr::pull()` {.collection-method}

`Arr::pull` 方法從陣列中移除一組索引鍵 / 值配對：

    use Illuminate\Support\Arr;
    
    $array = ['name' => 'Desk', 'price' => 100];
    
    $name = Arr::pull($array, 'name');
    
    // $name: Desk
    
    // $array: ['price' => 100]
可以在第三個引數上提供一個預設值給該方法。若指定的索引鍵不存在，就會回傳這個預設值：

    use Illuminate\Support\Arr;
    
    $value = Arr::pull($array, $key, $default);
<a name="method-array-query"></a>

#### `Arr::query()` {.collection-method}

`Arr::query` 方法將該陣列轉換為^[查詢字串](Query String)：

    use Illuminate\Support\Arr;
    
    $array = [
        'name' => 'Taylor',
        'order' => [
            'column' => 'created_at',
            'direction' => 'desc'
        ]
    ];
    
    Arr::query($array);
    
    // name=Taylor&order[column]=created_at&order[direction]=desc
<a name="method-array-random"></a>

#### `Arr::random()` {.collection-method}

`Arr::random` 方法從陣列中隨機回傳一個值：

    use Illuminate\Support\Arr;
    
    $array = [1, 2, 3, 4, 5];
    
    $random = Arr::random($array);
    
    // 4 - (retrieved randomly)
也可以在第二個引數上指定要回傳項目的數量。請注意，若有提供第二個引數，就算只要求一個項目，還是會回傳一組陣列：

    use Illuminate\Support\Arr;
    
    $items = Arr::random($array, 2);
    
    // [2, 5] - (retrieved randomly)
<a name="method-array-set"></a>

#### `Arr::set()` {.collection-method}

`Arr::set` 方法可使用「點 (.)」標記法來在多層巢狀陣列中賦值：

    use Illuminate\Support\Arr;
    
    $array = ['products' => ['desk' => ['price' => 100]]];
    
    Arr::set($array, 'products.desk.price', 200);
    
    // ['products' => ['desk' => ['price' => 200]]]
<a name="method-array-shuffle"></a>

#### `Arr::shuffle()` {.collection-method}

`Arr::shuffle` 方法會隨機排序該陣列內的項目：

    use Illuminate\Support\Arr;
    
    $array = Arr::shuffle([1, 2, 3, 4, 5]);
    
    // [3, 2, 5, 1, 4] - (generated randomly)
<a name="method-array-sort"></a>

#### `Arr::sort()` {.collection-method}

`Arr::sort` 方法以陣列內的值來排列陣列：

    use Illuminate\Support\Arr;
    
    $array = ['Desk', 'Table', 'Chair'];
    
    $sorted = Arr::sort($array);
    
    // ['Chair', 'Desk', 'Table']
也可以使用給定閉包的執行結果來排序陣列：

    use Illuminate\Support\Arr;
    
    $array = [
        ['name' => 'Desk'],
        ['name' => 'Table'],
        ['name' => 'Chair'],
    ];
    
    $sorted = array_values(Arr::sort($array, function (array $value) {
        return $value['name'];
    }));
    
    /*
        [
            ['name' => 'Chair'],
            ['name' => 'Desk'],
            ['name' => 'Table'],
        ]
    */
<a name="method-array-sort-desc"></a>

#### `Arr::sortDesc()` {.collection-method}

`Arr::sortDesc` 方法將陣列以其值來降冪排序：

    use Illuminate\Support\Arr;
    
    $array = ['Desk', 'Table', 'Chair'];
    
    $sorted = Arr::sortDesc($array);
    
    // ['Table', 'Desk', 'Chair']
也可以使用給定閉包的執行結果來排序陣列：

    use Illuminate\Support\Arr;
    
    $array = [
        ['name' => 'Desk'],
        ['name' => 'Table'],
        ['name' => 'Chair'],
    ];
    
    $sorted = array_values(Arr::sortDesc($array, function (array $value) {
        return $value['name'];
    }));
    
    /*
        [
            ['name' => 'Table'],
            ['name' => 'Desk'],
            ['name' => 'Chair'],
        ]
    */
<a name="method-array-sort-recursive"></a>

#### `Arr::sortRecursive()` {.collection-method}

`Arr::sortRecursive` 方法會遞迴排序陣列。當遇到數字索引鍵的子陣列時，會使用 `sort` 函式；若子陣列為關聯式陣列，則使用 `ksort` 函式：

    use Illuminate\Support\Arr;
    
    $array = [
        ['Roman', 'Taylor', 'Li'],
        ['PHP', 'Ruby', 'JavaScript'],
        ['one' => 1, 'two' => 2, 'three' => 3],
    ];
    
    $sorted = Arr::sortRecursive($array);
    
    /*
        [
            ['JavaScript', 'PHP', 'Ruby'],
            ['one' => 1, 'three' => 3, 'two' => 2],
            ['Li', 'Roman', 'Taylor'],
        ]
    */
若想讓結果以降冪排序，可使用 `Arr::sortRecursiveDesc` 方法。

    $sorted = Arr::sortRecursiveDesc($array);
<a name="method-array-take"></a>

#### `Arr::take()` {.collection-method}

The `Arr::take` method returns a new array with the specified number of items:

    use Illuminate\Support\Arr;
    
    $array = [0, 1, 2, 3, 4, 5];
    
    $chunk = Arr::take($array, 3);
    
    // [0, 1, 2]
You may also pass a negative integer to take the specified number of items from the end of the array:

    $array = [0, 1, 2, 3, 4, 5];
    
    $chunk = Arr::take($array, -2);
    
    // [4, 5]
<a name="method-array-to-css-classes"></a>

#### `Arr::toCssClasses()` {.collection-method}

The `Arr::toCssClasses` method conditionally compiles a CSS class string. The method accepts an array of classes where the array key contains the class or classes you wish to add, while the value is a boolean expression. If the array element has a numeric key, it will always be included in the rendered class list:

    use Illuminate\Support\Arr;
    
    $isActive = false;
    $hasError = true;
    
    $array = ['p-4', 'font-bold' => $isActive, 'bg-red' => $hasError];
    
    $classes = Arr::toCssClasses($array);
    
    /*
        'p-4 bg-red'
    */
<a name="method-array-to-css-styles"></a>

#### `Arr::toCssStyles()` {.collection-method}

`Arr::toCssClasses` 可以有條件地編譯 CSS Class 字串。該方法接受一組包含 Class 的陣列，其中，陣列的索引鍵代表欲新增的 Class，陣列值則是一個布林運算式。若陣列的元素有數字索引鍵，則該元素一定會被加到轉譯後的 Class 列表上：

```php
use Illuminate\Support\Arr;

$hasColor = true;

$array = ['background-color: blue', 'color: blue' => $hasColor];

$classes = Arr::toCssStyles($array);

/*
    'background-color: blue; color: blue;'
*/
```
該方法用於提供了 Laravel 的「[將 Class 於 Blade 元件的 Attribute Bag 合併](/docs/{{version}}/blade#conditionally-merge-classes)」功能，以及 `@class` [Blade 指示詞](/docs/{{version}}/blade#conditional-classes)。

<a name="method-array-undot"></a>

#### `Arr::undot()` {.collection-method}

`Arr::undot` 方法將一組使用「點 (.)」標記法的一維陣列展開為多維陣列：

    use Illuminate\Support\Arr;
    
    $array = [
        'user.name' => 'Kevin Malone',
        'user.occupation' => 'Accountant',
    ];
    
    $array = Arr::undot($array);
    
    // ['user' => ['name' => 'Kevin Malone', 'occupation' => 'Accountant']]
<a name="method-array-where"></a>

#### `Arr::where()` {.collection-method}

`Arr::where` 方法使用給定的閉包來篩選陣列：

    use Illuminate\Support\Arr;
    
    $array = [100, '200', 300, '400', 500];
    
    $filtered = Arr::where($array, function (string|int $value, int $key) {
        return is_string($value);
    });
    
    // [1 => '200', 3 => '400']
<a name="method-array-where-not-null"></a>

#### `Arr::whereNotNull()` {.collection-method}

`Arr::whereNotNull` 方法從給定陣列中移除所有 `null` 的值：

    use Illuminate\Support\Arr;
    
    $array = [0, null];
    
    $filtered = Arr::whereNotNull($array);
    
    // [0 => 0]
<a name="method-array-wrap"></a>

#### `Arr::wrap()` {.collection-method}

`Arr::wrap` 將給定值^[包裝](Wrap)為陣列。若給定的值已為陣列，則該方法會直接回傳該陣列，不做其他修改：

    use Illuminate\Support\Arr;
    
    $string = 'Laravel';
    
    $array = Arr::wrap($string);
    
    // ['Laravel']
若給定值為 `null`，則會回傳空陣列：

    use Illuminate\Support\Arr;
    
    $array = Arr::wrap(null);
    
    // []
<a name="method-data-fill"></a>

#### `data_fill()` {.collection-method}

`data_fill` 方法使用「點 (.)」標記法來在巢狀陣列或物件中填上原本不存在的值：

    $data = ['products' => ['desk' => ['price' => 100]]];
    
    data_fill($data, 'products.desk.price', 200);
    
    // ['products' => ['desk' => ['price' => 100]]]
    
    data_fill($data, 'products.desk.discount', 10);
    
    // ['products' => ['desk' => ['price' => 100, 'discount' => 10]]]
該方法也支援使用星號作為萬用字元，會填上對應的目標：

    $data = [
        'products' => [
            ['name' => 'Desk 1', 'price' => 100],
            ['name' => 'Desk 2'],
        ],
    ];
    
    data_fill($data, 'products.*.price', 200);
    
    /*
        [
            'products' => [
                ['name' => 'Desk 1', 'price' => 100],
                ['name' => 'Desk 2', 'price' => 200],
            ],
        ]
    */
<a name="method-data-get"></a>

#### `data_get()` {.collection-method}

`data_get` 方法使用「點 (.)」標記法來從巢狀陣列或物件中取值：

    $data = ['products' => ['desk' => ['price' => 100]]];
    
    $price = data_get($data, 'products.desk.price');
    
    // 100
`data_get` 還接受一個預設值。若找不到指定的索引鍵時會回傳該預設值：

    $discount = data_get($data, 'products.desk.discount', 0);
    
    // 0
該方法也接受使用星號來作為萬用字元，可以套用到陣列或物件上的任何索引鍵：

    $data = [
        'product-one' => ['name' => 'Desk 1', 'price' => 100],
        'product-two' => ['name' => 'Desk 2', 'price' => 150],
    ];
    
    data_get($data, '*.name');
    
    // ['Desk 1', 'Desk 2'];
The `{first}` and `{last}` placeholders may be used to retrieve the first or last items in an array:

    $flight = [
        'segments' => [
            ['from' => 'LHR', 'departure' => '9:00', 'to' => 'IST', 'arrival' => '15:00'],
            ['from' => 'IST', 'departure' => '16:00', 'to' => 'PKX', 'arrival' => '20:00'],
        ],
    ];
    
    data_get($flight, 'segments.{first}.arrival');
    
    // 15:00
<a name="method-data-set"></a>

#### `data_set()` {.collection-method}

`data_set` 函式使用「點 (.)」標記法來在巢狀陣列或物件上賦值：

    $data = ['products' => ['desk' => ['price' => 100]]];
    
    data_set($data, 'products.desk.price', 200);
    
    // ['products' => ['desk' => ['price' => 200]]]
該函式也接受使用星號作為萬用字元，會為設定相應的目標賦值：

    $data = [
        'products' => [
            ['name' => 'Desk 1', 'price' => 100],
            ['name' => 'Desk 2', 'price' => 150],
        ],
    ];
    
    data_set($data, 'products.*.price', 200);
    
    /*
        [
            'products' => [
                ['name' => 'Desk 1', 'price' => 200],
                ['name' => 'Desk 2', 'price' => 200],
            ],
        ]
    */
預設情況下，會複寫現有的值。若只想為不存在的項目賦值，可傳入 `false` 作為第四個引數給該函式：

    $data = ['products' => ['desk' => ['price' => 100]]];
    
    data_set($data, 'products.desk.price', 200, overwrite: false);
    
    // ['products' => ['desk' => ['price' => 100]]]
<a name="method-data-forget"></a>

#### `data_forget()` {.collection-method}

`data_forget` 函式使用「點 (.)」標記法來在巢狀陣列或物件上移除一個值：

    $data = ['products' => ['desk' => ['price' => 100]]];
    
    data_forget($data, 'products.desk.price');
    
    // ['products' => ['desk' => []]]
該函式也接受使用星號作為萬用字元，會移除相應的目標值：

    $data = [
        'products' => [
            ['name' => 'Desk 1', 'price' => 100],
            ['name' => 'Desk 2', 'price' => 150],
        ],
    ];
    
    data_forget($data, 'products.*.price');
    
    /*
        [
            'products' => [
                ['name' => 'Desk 1'],
                ['name' => 'Desk 2'],
            ],
        ]
    */
<a name="method-head"></a>

#### `head()` {.collection-method}

`head` 方法回傳給定陣列中的第一個元素：

    $array = [100, 200, 300];
    
    $first = head($array);
    
    // 100
<a name="method-last"></a>

#### `last()` {.collection-method}

`last` 方法回傳給定陣列中的最後一個元素：

    $array = [100, 200, 300];
    
    $last = last($array);
    
    // 300
<a name="numbers"></a>

## Numbers

<a name="method-number-abbreviate"></a>

#### `Number::abbreviate()` {.collection-method}

The `Number::abbreviate` method returns the human-readable format of the provided numerical value, with an abbreviation for the units:

    use Illuminate\Support\Number;
    
    $number = Number::abbreviate(1000);
    
    // 1K
    
    $number = Number::abbreviate(489939);
    
    // 490K
    
    $number = Number::abbreviate(1230000, precision: 2);
    
    // 1.23M
<a name="method-number-clamp"></a>

#### `Number::clamp()` {.collection-method}

The `Number::clamp` method ensures a given number stays within a specified range. If the number is lower than the minimum, the minimum value is returned. If the number is higher than the maximum, the maximum value is returned:

    use Illuminate\Support\Number;
    
    $number = Number::clamp(105, min: 10, max: 100);
    
    // 100
    
    $number = Number::clamp(5, min: 10, max: 100);
    
    // 10
    
    $number = Number::clamp(10, min: 10, max: 100);
    
    // 10
    
    $number = Number::clamp(20, min: 10, max: 100);
    
    // 20
<a name="method-number-currency"></a>

#### `Number::currency()` {.collection-method}

The `Number::currency` method returns the currency representation of the given value as a string:

    use Illuminate\Support\Number;
    
    $currency = Number::currency(1000);
    
    // $1,000
    
    $currency = Number::currency(1000, in: 'EUR');
    
    // €1,000
    
    $currency = Number::currency(1000, in: 'EUR', locale: 'de');
    
    // 1.000 €
<a name="method-number-file-size"></a>

#### `Number::fileSize()` {.collection-method}

The `Number::fileSize` method returns the file size representation of the given byte value as a string:

    use Illuminate\Support\Number;
    
    $size = Number::fileSize(1024);
    
    // 1 KB
    
    $size = Number::fileSize(1024 * 1024);
    
    // 1 MB
    
    $size = Number::fileSize(1024, precision: 2);
    
    // 1.00 KB
<a name="method-number-for-humans"></a>

#### `Number::forHumans()` {.collection-method}

The `Number::forHumans` method returns the human-readable format of the provided numerical value:

    use Illuminate\Support\Number;
    
    $number = Number::forHumans(1000);
    
    // 1 thousand
    
    $number = Number::forHumans(489939);
    
    // 490 thousand
    
    $number = Number::forHumans(1230000, precision: 2);
    
    // 1.23 million
<a name="method-number-format"></a>

#### `Number::format()` {.collection-method}

The `Number::format` method formats the given number into a locale specific string:

    use Illuminate\Support\Number;
    
    $number = Number::format(100000);
    
    // 100,000
    
    $number = Number::format(100000, precision: 2);
    
    // 100,000.00
    
    $number = Number::format(100000.123, maxPrecision: 2);
    
    // 100,000.12
    
    $number = Number::format(100000, locale: 'de');
    
    // 100.000
<a name="method-number-ordinal"></a>

#### `Number::ordinal()` {.collection-method}

The `Number::ordinal` method returns a number's ordinal representation:

    use Illuminate\Support\Number;
    
    $number = Number::ordinal(1);
    
    // 1st
    
    $number = Number::ordinal(2);
    
    // 2nd
    
    $number = Number::ordinal(21);
    
    // 21st
<a name="method-number-percentage"></a>

#### `Number::percentage()` {.collection-method}

The `Number::percentage` method returns the percentage representation of the given value as a string:

    use Illuminate\Support\Number;
    
    $percentage = Number::percentage(10);
    
    // 10%
    
    $percentage = Number::percentage(10, precision: 2);
    
    // 10.00%
    
    $percentage = Number::percentage(10.123, maxPrecision: 2);
    
    // 10.12%
    
    $percentage = Number::percentage(10, precision: 2, locale: 'de');
    
    // 10,00%
<a name="method-number-spell"></a>

#### `Number::spell()` {.collection-method}

The `Number::spell` method transforms the given number into a string of words:

    use Illuminate\Support\Number;
    
    $number = Number::spell(102);
    
    // one hundred and two
    
    $number = Number::spell(88, locale: 'fr');
    
    // quatre-vingt-huit
The `after` argument allows you to specify a value after which all numbers should be spelled out:

    $number = Number::spell(10, after: 10);
    
    // 10
    
    $number = Number::spell(11, after: 10);
    
    // eleven
The `until` argument allows you to specify a value before which all numbers should be spelled out:

    $number = Number::spell(5, until: 10);
    
    // five
    
    $number = Number::spell(10, until: 10);
    
    // 10
<a name="method-number-use-locale"></a>

#### `Number::useLocale()` {.collection-method}

The `Number::useLocale` method sets the default number locale globally, which affects how numbers and currency are formatted by subsequent invocations to the `Number` class's methods:

    use Illuminate\Support\Number;
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Number::useLocale('de');
    }
<a name="method-number-with-locale"></a>

#### `Number::withLocale()` {.collection-method}

The `Number::withLocale` method executes the given closure using the specified locale and then restores the original locale after the callback has executed:

    use Illuminate\Support\Number;
    
    $number = Number::withLocale('de', function () {
        return Number::format(1500);
    });
<a name="paths"></a>

## 路徑

<a name="method-app-path"></a>

#### `app_path()` {.collection-method}

`app_path` 回傳專案 `app` 目錄的完整名稱路徑。也可以使用 `app_path` 函式來為 app 目錄下相對路徑的完整名稱路徑：

    $path = app_path();
    
    $path = app_path('Http/Controllers/Controller.php');
<a name="method-base-path"></a>

#### `base_path()` {.collection-method}

`base_path` 函式回傳專案根目錄的完整名稱路徑。也可以使用 `base_path` 來產生相對於根目錄下給定檔案的完整名稱路徑：

    $path = base_path();
    
    $path = base_path('vendor/bin');
<a name="method-config-path"></a>

#### `config_path()` {.collection-method}

`config_path` 函式回傳專案 `config` 目錄的完整名稱路徑。也可以使用 `config_path` 函式來產生專案 `config` 目錄內給定檔案的完整名稱路徑：

    $path = config_path();
    
    $path = config_path('app.php');
<a name="method-database-path"></a>

#### `database_path()` {.collection-method}

`database_path` 函式回傳專案 `database` 目錄的完整名稱路徑。也可以使用 `database_path` 函式來產生 `database` 目錄下給定檔案的完整名稱路徑：

    $path = database_path();
    
    $path = database_path('factories/UserFactory.php');
<a name="method-lang-path"></a>

#### `lang_path()` {.collection-method}

`lang_path` 函式回傳專案 `lang` 目錄的完整名稱路徑。也可以使用 `lang_path` 函式來產生 `lang` 目錄下給定檔案的完整名稱路徑：

    $path = lang_path();
    
    $path = lang_path('en/messages.php');
> [!NOTE]  
> 預設情況下，Laravel 專案的 Skeleton 中未包含 `lang` 目錄。若想自定 Laravel 的語系檔，可以使用 `lang:publish` Artisan 指令來安裝語系檔：

<a name="method-mix"></a>

#### `mix()` {.collection-method}

`mix` 函式回傳[版本化的 Mix 檔案](/docs/{{version}}/mix)路徑：

    $path = mix('css/app.css');
<a name="method-public-path"></a>

#### `public_path()` {.collection-method}

`public_path` 函式回傳專案 `public` 目錄的完整名稱路徑。也可以使用 `public_path` 函式來產生 `public` 目錄下給定檔案的完整名稱路徑：

    $path = public_path();
    
    $path = public_path('css/app.css');
<a name="method-resource-path"></a>

#### `resource_path()` {.collection-method}

`resource_path` 函式回傳專案 `resources` 目錄的完整名稱路徑。也可以使用 `resource_path` 函式來產生 `resources` 目錄下給定檔案的完整名稱路徑：

    $path = resource_path();
    
    $path = resource_path('sass/app.scss');
<a name="method-storage-path"></a>

#### `storage_path()` {.collection-method}

`storage_path` 函式回傳專案 `storage` 目錄的完整名稱路徑。也可以使用 `storage_path` 函式來產生 `storage` 目錄下給定檔案的完整名稱路徑：

    $path = storage_path();
    
    $path = storage_path('app/file.txt');
<a name="urls"></a>

## URL

<a name="method-action"></a>

#### `action()` {.collection-method}

`action` 方法可為給定的 Controller 動作產生 URL：

    use App\Http\Controllers\HomeController;
    
    $url = action([HomeController::class, 'index']);
若該方法接受 Route 參數，請將這些 Route 參數作為第二個引數傳給該方法：

    $url = action([UserController::class, 'profile'], ['id' => 1]);
<a name="method-asset"></a>

#### `asset()` {.collection-method}

`asset` 方法使用目前 Request 的 Scheme (HTTP 或 HTTPS) 來產生素材 URL：

    $url = asset('img/photo.jpg');
可以在 `.env` 檔案中設定 `ASSET_URL` 變數來設定素材 URL 的主機名稱。若你將素材放在如 Amazon S3 或其他 CDN 之類的外部服務上，就很適合這樣設定：

    // ASSET_URL=http://example.com/assets
    
    $url = asset('img/photo.jpg'); // http://example.com/assets/img/photo.jpg
<a name="method-route"></a>

#### `route()` {.collection-method}

`route` 函式產生給定[命名 Route](/docs/{{version}}/routing#named-routes) 的 URL：

    $url = route('route.name');
若該 Route 接受參數，請將這些參數作為第二個引數傳給該方法：

    $url = route('route.name', ['id' => 1]);
預設情況下，`route` 函式回傳絕對 URL。若想產生相對 URL，請傳入 `false` 作為第三個引數給該函式：

    $url = route('route.name', ['id' => 1], false);
<a name="method-secure-asset"></a>

#### `secure_asset()` {.collection-method}

`secure_asset` 函式使用 HTTPS 為素材產生 URL：

    $url = secure_asset('img/photo.jpg');
<a name="method-secure-url"></a>

#### `secure_url()` {.collection-method}

`secure_url` 函式產生給定路徑上的完整名稱 HTTPS URL。可以傳入額外的 URL 片段給該函式的第二個引數：

    $url = secure_url('user/profile');
    
    $url = secure_url('user/profile', [1]);
<a name="method-to-route"></a>

#### `to_route()` {.collection-method}

`to_route` 函式為給定的[命名 Route](/docs/{{version}}/routing#named-routes) 產生一個[重新導向的 HTTP Response](/docs/{{version}}/responses#redirects)：

    return to_route('users.show', ['user' => 1]);
若有需要，也可以傳入一個用於跳轉的 HTTP 狀態碼以及一些額外的回應標頭作為 `to_route` 方法的第三與第四個引數：

    return to_route('users.show', ['user' => 1], 302, ['X-Framework' => 'Laravel']);
<a name="method-url"></a>

#### `url()` {.collection-method}

`url` 函式可以產生給定路徑上的完整名稱 URL：

    $url = url('user/profile');
    
    $url = url('user/profile', [1]);
若未提供路徑，則會回傳 `Illuminate\Routing\UrlGenerator` 實體：

    $current = url()->current();
    
    $full = url()->full();
    
    $previous = url()->previous();
<a name="miscellaneous"></a>

## 其他

<a name="method-abort"></a>

#### `abort()` {.collection-method}

`abort` 函式會擲回 [HTTP Exception](/docs/{{version}}/errors#http-exceptions)。HTTP Exception 會被 [^[Exception Handler](%E4%BE%8B%E5%A4%96%E8%99%95%E7%90%86%E5%B8%B8%E5%BC%8F)](/docs/{{version}}/errors#the-exception-handler) 轉譯：

    abort(403);
也可以提供 Exception 訊息與要傳送給瀏覽器的自訂 HTTP Response 標頭：

    abort(403, 'Unauthorized.', $headers);
<a name="method-abort-if"></a>

#### `abort_if()` {.collection-method}

`abort_if` 函式會在給定布林運算式取值為 `true` 時擲回一個 HTTP Exception：

    abort_if(! Auth::user()->isAdmin(), 403);
與 `abort` 方法類似，我們也可以在第三個引數上提供 Exception 的 Response 文字，並在第四個引數上提供一組自訂 Response 標頭陣列。

<a name="method-abort-unless"></a>

#### `abort_unless()` {.collection-method}

`abort_unless` 函式會在給定布林運算式取值為 `false` 時擲回一個 HTTP Exception：

    abort_unless(Auth::user()->isAdmin(), 403);
與 `abort` 方法類似，我們也可以在第三個引數上提供 Exception 的 Response 文字，並在第四個引數上提供一組自訂 Response 標頭陣列。

<a name="method-app"></a>

#### `app()` {.collection-method}

`app` 函式回傳 [Service Container](/docs/{{version}}/container) 實體：

    $container = app();
也可以傳入一個類別或介面名稱來用 Container 解析：

    $api = app('HelpSpot\API');
<a name="method-auth"></a>

#### `auth()` {.collection-method}

`auth` 函式回傳 [Authenticator](/docs/{{version}}/authentication) 的實體。可以使用 `auth` 函式來作為 `Auth` Facade 的替代：

    $user = auth()->user();
若有需要，可以指定要存取的 Guard 實體：

    $user = auth('admin')->user();
<a name="method-back"></a>

#### `back()` {.collection-method}

`back` 函式產生一個指向使用者上一個瀏覽位置的[重新導向 HTTP Response]：

    return back($status = 302, $headers = [], $fallback = '/');
    
    return back();
<a name="method-bcrypt"></a>

#### `bcrypt()` {.collection-method}

`bcrypt` 方法使用 Bcrypt 來[雜湊](/docs/{{version}}/hashing)給定的值。也可以使用這個函式來作為 `Hash` Facade 的替代：

    $password = bcrypt('my-secret-password');
<a name="method-blank"></a>

#### `blank()` {.collection-method}

`blank` 函式判斷給定值是否為「^[空白](Blank)」：

    blank('');
    blank('   ');
    blank(null);
    blank(collect());
    
    // true
    
    blank(0);
    blank(true);
    blank(false);
    
    // false
請參考 [`filled`](#method-filled) 方法以瞭解與 `blank` 相反的方法。

<a name="method-broadcast"></a>

#### `broadcast()` {.collection-method}

`broadcast` 函式會[廣播](/docs/{{version}}/broadcasting)給定的 [Event](/docs/{{version}}/events) 給其 Listener：

    broadcast(new UserRegistered($user));
    
    broadcast(new UserRegistered($user))->toOthers();
<a name="method-cache"></a>

#### `cache()` {.collection-method}

`cache` 函式可用來從[快取](/docs/{{version}}/cache)中取值。若快取中沒有給定的索引鍵，則會回傳可選的預設值：

    $value = cache('key');
    
    $value = cache('key', 'default');
可以傳入一組索引鍵 / 值配對的陣列給該函式來將項目加入快取中。也請傳入單位為秒的快取值有效期間：

    cache(['key' => 'value'], 300);
    
    cache(['key' => 'value'], now()->addSeconds(10));
<a name="method-class-uses-recursive"></a>

#### `class_uses_recursive()` {.collection-method}

`class_uses_recursive` 函式回傳某個類別使用的所有 Trait，包含其所有^[上層](Parent)類別使用的 Trait：

    $traits = class_uses_recursive(App\Models\User::class);
<a name="method-collect"></a>

#### `collect()` {.collection-method}

`collect` 函式使用給定值來建立一個 [Collection](/docs/{{version}}/collections) 實體：

    $collection = collect(['taylor', 'abigail']);
<a name="method-config"></a>

#### `config()` {.collection-method}

`config` 函式可取得[設定](/docs/{{version}}/configuration)變數中的值。設定值可以通過「點 (.)」語法來存取，即包含設定檔名稱與欲存取的選項名。也可以指定設定選項不存在時要回傳的預設值：

    $value = config('app.timezone');
    
    $value = config('app.timezone', $default);
也可以在執行階段傳入一組索引鍵 / 值配對的陣列來設定設定值。不過，請注意，該函式只會影響目前 Request 的設定值，並不會實際更新設定：

    config(['app.debug' => true]);
<a name="method-cookie"></a>

#### `cookie()` {.collection-method}

`cookie` 函式建立一個新的 [Cookie](/docs/{{version}}/requests#cookies) 實體：

    $cookie = cookie('name', 'value', $minutes);
<a name="method-csrf-field"></a>

#### `csrf_field()` {.collection-method}

`csrf_field` 函式產生一個包含 CSRF Token 的 HTML `hidden` 輸入欄位。舉例來說，在 [Blade 語法](/docs/{{version}}/blade)中可這樣使用：

    {{ csrf_field() }}
<a name="method-csrf-token"></a>

#### `csrf_token()` {.collection-method}

`csrf_token` 函式可取得目前 CSRF Token 的值：

    $token = csrf_token();
<a name="method-decrypt"></a>

#### `decrypt()` {.collection-method}

`decrypt` 函式會[解密](/docs/{{version}}/encryption)給定的值。可使用這個方法作為 `Crypt` Facade 的替代。

    $password = decrypt($value);
<a name="method-dd"></a>

#### `dd()` {.collection-method}

`dd` 函式可傾印給定變數，並結束目前的指令碼執行：

    dd($value);
    
    dd($value1, $value2, $value3, ...);
若不想結束目前的指令碼執行，請改為使用 [`dump`](#method-dump) 方法。

<a name="method-dispatch"></a>

#### `dispatch()` {.collection-method}

`dispatch` 方法將給定 [Job](/docs/{{version}}/queues#creating-jobs) 推入 Laravel 的 [Job 佇列](/docs/{{version}}/queues) 中：

    dispatch(new App\Jobs\SendEmails);
<a name="method-dispatch-sync"></a>

#### `dispatch_sync()` {.collection-method}

`dispatch_sync` 方法將給定的 Job 推入到[同步的 (Sync)](/docs/{{version}}/queues#synchronous-dispatching) Queue 中以立即處理該 Job：

    dispatch_sync(new App\Jobs\SendEmails);
<a name="method-dump"></a>

#### `dump()` {.collection-method}

`dump` 函式傾印給定的變數：

    dump($value);
    
    dump($value1, $value2, $value3, ...);
若想在傾印變數後停止執行指令碼，請使用 [`dd`](#method-dd) 函式來代替。

<a name="method-encrypt"></a>

#### `encrypt()` {.collection-method}

`encrypt` 函式會[加密](/docs/{{version}}/encryption)給定的值。可使用這個方法作為 `Crypt` Facade 的替代：

    $secret = encrypt('my-secret-value');
<a name="method-env"></a>

#### `env()` {.collection-method}

`env` 函式可取得[環境變數](/docs/{{version}}/configuration#environment-configuration)的值，或是回傳預設值：

    $env = env('APP_ENV');
    
    $env = env('APP_ENV', 'production');
> [!WARNING]  
> 若在部署流程中執行了 `config:cache` 指令，應確保只有在設定檔中呼叫 `env` 函式。設定檔被快取後，就不會再載入 `.env` 檔了。所有 `env` 函式查詢 `.env` 變數的呼叫都會回傳 `null`。

<a name="method-event"></a>

#### `event()` {.collection-method}

`event` 函式將給定 [Event](/docs/{{version}}/events) ^[分派](Dispatch)給其 Listener：

    event(new UserRegistered($user));
<a name="method-fake"></a>

#### `fake()` {.collection-method}

`fake` 方法會從 Container 中解析 [Faker](https://github.com/FakerPHP/Faker) 單例 (Singleton)，很適合在 Model Factory、資料庫 Seeder、測試、打樣 View 等地方建立假資料：

```blade
@for($i = 0; $i < 10; $i++)
    <dl>
        <dt>Name</dt>
        <dd>{{ fake()->name() }}</dd>

        <dt>Email</dt>
        <dd>{{ fake()->unique()->safeEmail() }}</dd>
    </dl>
@endfor
```
By default, the `fake` function will utilize the `app.faker_locale` configuration option in your `config/app.php` configuration. Typically, this configuration option is set via the `APP_FAKER_LOCALE` environment variable. You may also specify the locale by passing it to the `fake` function. Each locale will resolve an individual singleton:

    fake('nl_NL')->name()
<a name="method-filled"></a>

#### `filled()` {.collection-method}

`filled` 函式判斷給定值是否不為「^[空白](Blank)」：

    filled(0);
    filled(true);
    filled(false);
    
    // true
    
    filled('');
    filled('   ');
    filled(null);
    filled(collect());
    
    // false
請參考 [blank](#method-blank) 方法以瞭解與 `filled` 相反的方法。

<a name="method-info"></a>

#### `info()` {.collection-method}

`info` 函式寫入 info 等級的資訊至程式的 [日誌](/docs/{{version}}/logging) 中：

    info('Some helpful information!');
也可以傳入一組包含上下文資料的陣列給該函式：

    info('User login attempt failed.', ['id' => $user->id]);
<a name="method-literal"></a>

#### `literal()` {.collection-method}

"The `literal` function creates a new [stdClass](https://www.php.net/manual/en/class.stdclass.php) instance with the given named arguments as properties:

    $obj = literal(
        name: 'Joe',
        languages: ['PHP', 'Ruby'],
    );
    
    $obj->name; // 'Joe'
    $obj->languages; // ['PHP', 'Ruby']
<a name="method-logger"></a>

#### `logger()` {.collection-method}

`logger` 函式可用來寫入 `debug` 等級的訊息至[日誌](/docs/{{version}}/logging)中：

    logger('Debug message');
也可以傳入一組包含上下文資料的陣列給該函式：

    logger('User has logged in.', ['id' => $user->id]);
若未傳入任何值給該方法，則會回傳 [Logger](/docs/{{version}}/errors#logging) 實體：

    logger()->error('You are not allowed here.');
<a name="method-method-field"></a>

#### `method_field()` {.collection-method}

`method_field` 函式產生一個用於模擬表單 HTTP 動詞的 HTML `hidden` 輸入欄位。舉例來說，在 [Blade 語法](/docs/{{version}}/blade)中可這樣使用：

    <form method="POST">
        {{ method_field('DELETE') }}
    </form>
<a name="method-now"></a>

#### `now()` {.collection-method}

`now` 函式建立一個目前時間的新 `Illuminate\Support\Carbon` 實體：

    $now = now();
<a name="method-old"></a>

#### `old()` {.collection-method}

`old` 函式[取得](/docs/{{version}}/requests#retrieving-input)快閃存入 Session 的[舊輸入](/docs/{{version}}/requests#old-input)值：

    $value = old('value');
    
    $value = old('value', 'default');
由於提供給 `old` 方法第二個引數的「預設值」常常都是 Eloquent Model 的屬性，因此，在 Laravel 中，我們可以直接將整個 Eloquent Model 作為第二個引數傳給 `old` 方法即可。當我們傳入 Eloquent Model 給 `old` 方法時，Laravel 會假設傳給 `old` 方法的第一個引數即為要當作「預設值」的 Eloquent 屬性名稱：

    {{ old('name', $user->name) }}
    
    // Is equivalent to...
    
    {{ old('name', $user) }}
<a name="method-once"></a>

#### `once()` {.collection-method}

The `once` function executes the given callback and caches the result in memory for the duration of the request. Any subsequent calls to the `once` function with the same callback will return the previously cached result:

    function random(): int
    {
        return once(function () {
            return random_int(1, 1000);
        });
    }
    
    random(); // 123
    random(); // 123 (cached result)
    random(); // 123 (cached result)
When the `once` function is executed from within an object instance, the cached result will be unique to that object instance:

```php
<?php

class NumberService
{
    public function all(): array
    {
        return once(fn () => [1, 2, 3]);
    }
}

$service = new NumberService;

$service->all();
$service->all(); // (cached result)

$secondService = new NumberService;

$secondService->all();
$secondService->all(); // (cached result)
```
<a name="method-optional"></a>

#### `optional()` {.collection-method}

`optional` 函式接受任何的引數，並可讓你存取該物件上的屬性或呼叫該物件上的方法。若給定的物件為 `null`，存取屬性和方法時會回傳 `null` 而不是發生錯誤：

    return optional($user->address)->street;
    
    {!! old('name', optional($user)->name) !!}
`optional` 函式也接受閉包作為其第二個引數。若第一個引數傳入的值不是 null 時會叫用該閉包：

    return optional(User::find($id), function (User $user) {
        return $user->name;
    });
<a name="method-policy"></a>

#### `policy()` {.collection-method}

`policy` 方法取得給定類別的 [Policy](/docs/{{version}}/authorization#creating-policies) 實體：

    $policy = policy(App\Models\User::class);
<a name="method-redirect"></a>

#### `redirect()` {.collection-method}

`redirect` 函式回傳一個[重新導向的 HTTP Response](/docs/{{version}}/responses#redirects)。若呼叫時未提供任何引數，則回傳 Redirector 實體：

    return redirect($to = null, $status = 302, $headers = [], $https = null);
    
    return redirect('/home');
    
    return redirect()->route('route.name');
<a name="method-report"></a>

#### `report()` {.collection-method}

`report` 函式會使用 [Exception Handler](/docs/{{version}}/errors#the-exception-handler) 來回報 Exception：

    report($e);
`report` 函式也接受一個字串作為其引數。若傳入字串給該函式時，`report` 會使用給定的字串作為訊息來建立 Exception：

    report('Something went wrong.');
<a name="method-report-if"></a>

#### `report_if()` {.collection-method}

`report_if` 函式會在給定條件為 `true` 時使用 [Exception Handler](/docs/{{version}}/errors#the-exception-handler) 來回報 Exception：

    report_if($shouldReport, $e);
    
    report_if($shouldReport, 'Something went wrong.');
<a name="method-report-unless"></a>

#### `report_unless()` {.collection-method}

`report_unless` 函式會在給定條件為 `false` 時使用 [Exception Handler](/docs/{{version}}/errors#the-exception-handler) 來回報 Exception：

    report_unless($reportingDisabled, $e);
    
    report_unless($reportingDisabled, 'Something went wrong.');
<a name="method-request"></a>

#### `request()` {.collection-method}

`request` 函式回傳目前的 [Request](/docs/{{version}}/requests) 實體，或是從目前 Request 中取得輸入欄位的值：

    $request = request();
    
    $value = request('key', $default);
<a name="method-rescue"></a>

#### `rescue()` {.collection-method}

`rescue` 函式會執行給定的閉包，並 Catch 在執行時發生的所有 Exception。被 Catch 到的 Exception 會被送到 [Exception Handler](/docs/{{version}}/errors#the-exception-handler)，不過，Request 會繼續執行：

    return rescue(function () {
        return $this->method();
    });
也可以傳入第二個引數給 `rescue` 函式。執行閉包時若有發生 Exception，就會使用這個引數來當作回傳的「預設」值：

    return rescue(function () {
        return $this->method();
    }, false);
    
    return rescue(function () {
        return $this->method();
    }, function () {
        return $this->failure();
    });
可提供一個 `report` 引數給 `rescue` 函式來判斷 Exception 是否應以 `report` 函式回報：

    return rescue(function () {
        return $this->method();
    }, report: function (Throwable $throwable) {
        return $throwable instanceof InvalidArgumentException;
    });
<a name="method-resolve"></a>

#### `resolve()` {.collection-method}

`resolve` 函式使用 [Service Container](/docs/{{version}}/container) 來將給定的類別或介面名稱解析為實體：

    $api = resolve('HelpSpot\API');
<a name="method-response"></a>

#### `response()` {.collection-method}

`response` 函式建立一個 [Response](/docs/{{version}}/responses) 實體，或是取得 Response Factory 的實體：

    return response('Hello World', 200, $headers);
    
    return response()->json(['foo' => 'bar'], 200, $headers);
<a name="method-retry"></a>

#### `retry()` {.collection-method}

`retry` 函式會嘗試執行給定的閉包，直到達到最大嘗試次數限制。若該回呼未^[擲回](Throw) Exception，則會回傳該回呼的回傳值。若回呼擲回 Exception，就會自動嘗試重新執行回呼。達到最大嘗試次數後，就會擲回 Exception：

    return retry(5, function () {
        // Attempt 5 times while resting 100ms between attempts...
    }, 100);
若想自動手動計算每次長時間要暫停的毫秒數，可傳入閉包作為第三個引數給 `retry` 函式：

    use Exception;
    
    return retry(5, function () {
        // ...
    }, function (int $attempt, Exception $exception) {
        return $attempt * 100;
    });
為了方便起見，也可以提供陣列作為 `retry` 函式的第一個引數。會使用這個真累來判斷每次嘗試間要暫停多久：

    return retry([100, 200], function () {
        // Sleep for 100ms on first retry, 200ms on second retry...
    });
若只想在特定條件下重試，可傳入一個閉包作為第四個引數給 `retry` 函式：

    use Exception;
    
    return retry(5, function () {
        // ...
    }, 100, function (Exception $exception) {
        return $exception instanceof RetryException;
    });
<a name="method-session"></a>

#### `session()` {.collection-method}

`session` 函式可用來取得或設定 [Session](/docs/{{version}}/session) 值：

    $value = session('key');
可以傳入一組索引鍵 / 值配對的陣列給該函式來賦值：

    session(['chairs' => 7, 'instruments' => 3]);
若未傳入任何值給該方法，則會回傳 Session Store 實體：

    $value = session()->get('key');
    
    session()->put('key', $value);
<a name="method-tap"></a>

#### `tap()` {.collection-method}

`tap` 方法接受兩個引數：任意值 `$value`、以及一個閉包。`$value` 會被傳入閉包中，然後 `tap` 方法會回傳 `$value` 的值。閉包的回傳值沒有任何影響：

    $user = tap(User::first(), function (User $user) {
        $user->name = 'taylor';
    
        $user->save();
    });
若未傳入閉包給 `tap` 函式，則可呼叫任何給定 `$value` 上的方法。無論呼叫的方法回傳什麼值，在此處都會回傳 `$value`。舉例來說，Eloquent `update` 方法一般會回傳整數。不過，若我們可以將 `update` 方法的呼叫串在 `tap` 函式後方，來強制把該方法的回傳值改為 Model 實體：

    $user = tap($user)->update([
        'name' => $name,
        'email' => $email,
    ]);
若要將 `tap` 方法加到類別上，可以將 `Illuminate\Support\Traits\Tappable` Trait 加到類別中。該 Trait 的 `tap` 方法接受一個閉包作為其唯一的引數。物件實體本身會被傳入該閉包中，並由 `tap` 方法回傳：

    return $user->tap(function (User $user) {
        // ...
    });
<a name="method-throw-if"></a>

#### `throw_if()` {.collection-method}

`throw_if` 函式會在給定布林運算式取值為 `true` 時擲回一個 Exception：

    throw_if(! Auth::user()->isAdmin(), AuthorizationException::class);
    
    throw_if(
        ! Auth::user()->isAdmin(),
        AuthorizationException::class,
        'You are not allowed to access this page.'
    );
<a name="method-throw-unless"></a>

#### `throw_unless()` {.collection-method}

`throw_unless` 函式會在給定布林運算式取值為 `false` 時擲回一個 Exception：

    throw_unless(Auth::user()->isAdmin(), AuthorizationException::class);
    
    throw_unless(
        Auth::user()->isAdmin(),
        AuthorizationException::class,
        'You are not allowed to access this page.'
    );
<a name="method-today"></a>

#### `today()` {.collection-method}

`today` 函式建立一個目前日期的新 `Illuminate\Support\Carbon` 實體：

    $today = today();
<a name="method-trait-uses-recursive"></a>

#### `trait_uses_recursive()` {.collection-method}

`trait_uses_recursive` 函式回傳該 Trait 使用的所有 Trait：

    $traits = trait_uses_recursive(\Illuminate\Notifications\Notifiable::class);
<a name="method-transform"></a>

#### `transform()` {.collection-method}

`transform` 函式會在給定值不為[^[空白](Blank)](#method-blank)時執行閉包，並回傳該閉包的回傳值：

    $callback = function (int $value) {
        return $value * 2;
    };
    
    $result = transform(5, $callback);
    
    // 10
可傳入預設值或閉包作為第三個引數給該函式。若給定值為空白時，會回傳這個值：

    $result = transform(null, $callback, 'The value is blank');
    
    // The value is blank
<a name="method-validator"></a>

#### `validator()` {.collection-method}

`validator` 函式使用給定的引數來建立一個新的 [Validator](/docs/{{version}}/validation) 實體。可以用來當作是 `Validator` Facade 的替代：

    $validator = validator($data, $rules, $messages);
<a name="method-value"></a>

#### `value()` {.collection-method}

`value` 函式會回傳給定的值。不過，若傳入閉包給該函式，會執行該閉包並回傳該閉包的值：

    $result = value(true);
    
    // true
    
    $result = value(function () {
        return false;
    });
    
    // false
也可以傳入更多引數給 `value` 函式。若第一個引數為閉包，則這些其他的引數會被作為引數來傳給該閉包。若不是閉包，則這些引數會被忽略：

    $result = value(function (string $name) {
        return $name;
    }, 'Taylor');
    
    // 'Taylor'
<a name="method-view"></a>

#### `view()` {.collection-method}

`view` 函式可取得一個 [View](/docs/{{version}}/views) 實體：

    return view('auth.login');
<a name="method-with"></a>

#### `with()` {.collection-method}

`with` 函式會回傳給定的值。若有傳入閉包作為第二個引數該函式，會執行該閉包並回傳該閉包的回傳值：

    $callback = function (mixed $value) {
        return is_numeric($value) ? $value * 2 : 0;
    };
    
    $result = with(5, $callback);
    
    // 10
    
    $result = with(null, $callback);
    
    // 0
    
    $result = with(5, null);
    
    // 5
<a name="other-utilities"></a>

## 其他公用程式

<a name="benchmarking"></a>

### 效能評定 (Benchmark)

有時候，我們會想快速地測試程式中某個部分的效能。這種時候，可以使用 `Benchmark` 輔助類別來測量完成給定回呼所需的毫秒數：

    <?php
    
    use App\Models\User;
    use Illuminate\Support\Benchmark;
    
    Benchmark::dd(fn () => User::find(1)); // 0.1 ms
    
    Benchmark::dd([
        'Scenario 1' => fn () => User::count(), // 0.5 ms
        'Scenario 2' => fn () => User::all()->count(), // 20.0 ms
    ]);
預設情況下，給定回呼擲回被執行一次 (即一次迭代)，而執行所花費的時間會被顯示在瀏覽器或主控台上。

若想讓該回呼被執行多次，可使用該方法的第二個引數來指定該回呼要被呼叫的迭代數。執行該回呼超過一次時，`Benchmark` 類別會回傳在各個迭代間執行該回呼所花費的平均毫秒數：

    Benchmark::dd(fn () => User::count(), iterations: 10); // 0.5 ms
有時候，我們可能會需要針對回呼進行基準測試 (Benchmark)，並取得該回呼的回傳值。 `value` 方法將回傳一個元組 (Tuple)，其中包含該回呼的回傳值，以及執行該回呼所花費的毫秒數：

    [$count, $duration] = Benchmark::value(fn () => User::count());
<a name="dates"></a>

### Date - 日期

Laravel 包含了 [Carbon](https://carbon.nesbot.com/docs/) 函式庫。Carbon 是一個強大的日期與時間操作函式庫。若要建立新的 `Carbon` 實體，可呼叫 `now` 函式。該函式在 Laravel 專案中是全域函式，隨處都可用：

```php
$now = now();
```
或者，也可以使用 `Illuminate\Support\Carbon` 類別來建立新的 `Carbon` 實體：

```php
use Illuminate\Support\Carbon;

$now = Carbon::now();
```
請參考 [Carbon 的官方說明文件](https://carbon.nesbot.com/docs/)以進一步瞭解 Carbon 與其功能。

<a name="lottery"></a>

### Lottery

Laravel 的 Lottery 類別可用來依據給定的機率執行回呼。這個類別特別適用於想只在特定比例的連入 Request 內執行程式時：

    use Illuminate\Support\Lottery;
    
    Lottery::odds(1, 20)
        ->winner(fn () => $user->won())
        ->loser(fn () => $user->lost())
        ->choose();
也可以將 Laravel 的 Lottery 類別與其他 Laravel 功能組合使用。舉例來說，當資料庫查詢速度慢時，我們可以只將其中一部分的查詢回報給 Exception Handler。此外，由於 Lottery  類別是 callable，因此我們可以將 Lottery 實體傳給任何接受 callable 的方法：

    use Carbon\CarbonInterval;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Lottery;
    
    DB::whenQueryingForLongerThan(
        CarbonInterval::seconds(2),
        Lottery::odds(1, 100)->winner(fn () => report('Querying > 2 seconds.')),
    );
<a name="testing-lotteries"></a>

#### 測試 Lottery

Laravel 提供了一些簡單的方法，能讓你輕鬆測試專案的 Lottery 呼叫：

    // Lottery will always win...
    Lottery::alwaysWin();
    
    // Lottery will always lose...
    Lottery::alwaysLose();
    
    // Lottery will win then lose, and finally return to normal behavior...
    Lottery::fix([true, false]);
    
    // Lottery will return to normal behavior...
    Lottery::determineResultsNormally();
<a name="pipeline"></a>

### Pipeline

Laravel 的 `Pipeline` Facade 是一個能將給定輸入「^[Pipe](%E8%BC%B8%E9%80%81)」進一系列 Invokable 類別、閉包、或 Callable 管道的便利功能，能讓管道中一系列類別都有機會檢查並修改輸入，然後再繼續呼叫管道中的下一個 Callable：

```php
use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Pipeline;

$user = Pipeline::send($user)
            ->through([
                function (User $user, Closure $next) {
                    // ...

                    return $next($user);
                },
                function (User $user, Closure $next) {
                    // ...

                    return $next($user);
                },
            ])
            ->then(fn (User $user) => $user);
```
就像這樣，管道中的各個 Invokable 類別或閉包都會收到輸入值以及一個 `$next` 閉包。呼叫 `$next` 閉包就會呼叫管道中的下一個 Callable。讀者可能已經注意到，這個寫法跟 [Middleware](/docs/{{version}}/middleware) 非常類似。

當管道中的最後一個 Callable 呼叫了 `$next` 閉包，就會呼叫傳送給 `then` 中的 Callable。一般來說，這個 Callable 只會回傳給定的輸入。

當然，就像剛才討論過的，除了 Closure 外，也可以傳入 Invokable Class 給 Pipeline。若提供了 Class 名稱，則該 Class 會被 Laravel 的 [Service Container](/docs/{{version}}/container) 初始化，以將相依類別注入到 Invokable Class 中：

```php
$user = Pipeline::send($user)
            ->through([
                GenerateProfilePhoto::class,
                ActivateSubscription::class,
                SendWelcomeEmail::class,
            ])
            ->then(fn (User $user) => $user);
```
<a name="sleep"></a>

### Sleep

Laravel 的 `Sleep` 類別是一個輕便的 ^[Wrapper](%E5%8C%85%E8%A3%9D)，將 PHP 的原生 `sleep` 與 `unsleep` 函式包裝起來，提供更強的可測試性，並同時提供對開發人員更友善的時間處理 API：

    use Illuminate\Support\Sleep;
    
    $waiting = true;
    
    while ($waiting) {
        Sleep::for(1)->second();
    
        $waiting = /* ... */;
    }
`Sleep` 類別提供了多個方法，能讓你處理不同單位的時間：

    // Pause execution for 90 seconds...
    Sleep::for(1.5)->minutes();
    
    // Pause execution for 2 seconds...
    Sleep::for(2)->seconds();
    
    // Pause execution for 500 milliseconds...
    Sleep::for(500)->milliseconds();
    
    // Pause execution for 5,000 microseconds...
    Sleep::for(5000)->microseconds();
    
    // Pause execution until a given time...
    Sleep::until(now()->addMinute());
    
    // Alias of PHP's native "sleep" function...
    Sleep::sleep(2);
    
    // Alias of PHP's native "usleep" function...
    Sleep::usleep(5000);
若要輕鬆組合搭配不同單位的時間，可使用 `and` 方法：

    Sleep::for(1)->second()->and(10)->milliseconds();
<a name="testing-sleep"></a>

#### 測試 Sleep

在測試有使用 `Sleep` 類別或 PHP 原生 sleep 函式的程式碼時，測試也會暫停執行。可想而知，這樣會使測試套件明顯變慢。舉例來說，假設我們要測試下列程式碼：

    $waiting = /* ... */;
    
    $seconds = 1;
    
    while ($waiting) {
        Sleep::for($seconds++)->seconds();
    
        $waiting = /* ... */;
    }
一般來說，測試此程式碼會花費 **至少** 一秒鐘。所幸，`Sleep` 類別能讓我們「模擬 (Fake)」暫停，好讓我們的測試套件能保持快速：

```php
it('waits until ready', function () {
    Sleep::fake();

    // ...
});
```
```php
public function test_it_waits_until_ready()
{
    Sleep::fake();

    // ...
}
```
在模擬 (Fake) `Sleep` Class 時，會跳過實際的執行暫停，因此會使測試變快。

一旦模擬了 `Sleep` 類別，我們就可以針對預期應產生的「Sleep」進行 Assertion 判斷。為了說明如何進行 Assertion，我們先假設我們要測試一個會暫停執行 3 次的程式碼，每次暫停都會增加 1 秒鐘。使用 `assertSequence` 方法，就可以測試我們的程式碼是否已「Sleep」適當的時間，同時又能保持讓我們的程式快速執行：

```php
it('checks if ready three times', function () {
    Sleep::fake();

    // ...

    Sleep::assertSequence([
        Sleep::for(1)->second(),
        Sleep::for(2)->seconds(),
        Sleep::for(3)->seconds(),
    ]);
}
```
```php
public function test_it_checks_if_ready_four_times()
{
    Sleep::fake();

    // ...

    Sleep::assertSequence([
        Sleep::for(1)->second(),
        Sleep::for(2)->seconds(),
        Sleep::for(3)->seconds(),
    ]);
}
```
當然，`Sleep` 類別還提供了各種其他的 Assertion 判斷能讓你在測試中使用：

    use Carbon\CarbonInterval as Duration;
    use Illuminate\Support\Sleep;
    
    // Assert that sleep was called 3 times...
    Sleep::assertSleptTimes(3);
    
    // Assert against the duration of sleep...
    Sleep::assertSlept(function (Duration $duration): bool {
        return /* ... */;
    }, times: 1);
    
    // Assert that the Sleep class was never invoked...
    Sleep::assertNeverSlept();
    
    // Assert that, even if Sleep was called, no execution paused occurred...
    Sleep::assertInsomniac();
有時，我們會需要在模擬 Sleep 發生時，執行一些動作。若要在發生模擬 Sleep 時執行動作，可提供規格回呼給 `whenFakingSleep` 方法。在下方的範例中，我們使用了 Laravel 的[時間操作輔助函式](/docs/{{version}}/mocking#interacting-with-time)來在每個 Sleep 發生的時候立即更改時間：

```php
use Carbon\CarbonInterval as Duration;

$this->freezeTime();

Sleep::fake();

Sleep::whenFakingSleep(function (Duration $duration) {
    // Progress time when faking sleep...
    $this->travel($duration->totalMilliseconds)->milliseconds();
});
```
在 Laravel 內部會在需要暫停執行時使用 `Sleep` Class。舉例來說，[`retry`](#method-retry) 輔助函式會在暫停時使用 `Sleep` Class，以在使用該輔助函式時提升可測試性。
