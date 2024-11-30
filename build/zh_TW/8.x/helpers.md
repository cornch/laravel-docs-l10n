---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/79/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 44.75
---

# 輔助函式

- [簡介](#introduction)
- [可用方法](#available-methods)

<a name="introduction"></a>

## 簡介

Laravel 提供了多種全域 PHP「輔助函式」。這些函式中，大部分都是 Laravel 本身有在使用的。不過，若你覺得這些方法很方便的話，也可以在你自己的專案內使用。

<a name="available-methods"></a>

## 可用的方法

<style>
    .collection-method-list > p {
        column-count: 3; -moz-column-count: 3; -webkit-column-count: 3;
        column-gap: 2em; -moz-column-gap: 2em; -webkit-column-gap: 2em;
    }

    .collection-method-list a {
        display: block;
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
[Arr::last](#method-array-last)
[Arr::only](#method-array-only)
[Arr::pluck](#method-array-pluck)
[Arr::prepend](#method-array-prepend)
[Arr::pull](#method-array-pull)
[Arr::query](#method-array-query)
[Arr::random](#method-array-random)
[Arr::set](#method-array-set)
[Arr::shuffle](#method-array-shuffle)
[Arr::sort](#method-array-sort)
[Arr::sortRecursive](#method-array-sort-recursive)
[Arr::toCssClasses](#method-array-to-css-classes)
[Arr::undot](#method-array-undot)
[Arr::where](#method-array-where)
[Arr::whereNotNull](#method-array-where-not-null)
[Arr::wrap](#method-array-wrap)
[data_fill](#method-data-fill)
[data_get](#method-data-get)
[data_set](#method-data-set)
[head](#method-head)
[last](#method-last)

</div>
<a name="paths-method-list"></a>

### 路徑

<div class="collection-method-list" markdown="1">
[app_path](#method-app-path)
[base_path](#method-base-path)
[config_path](#method-config-path)
[database_path](#method-database-path)
[mix](#method-mix)
[public_path](#method-public-path)
[resource_path](#method-resource-path)
[storage_path](#method-storage-path)

</div>
<a name="strings-method-list"></a>

### 字串

<div class="collection-method-list" markdown="1">
[__](#method-__)
[class_basename](#method-class-basename)
[e](#method-e)
[preg_replace_array](#method-preg-replace-array)
[Str::after](#method-str-after)
[Str::afterLast](#method-str-after-last)
[Str::ascii](#method-str-ascii)
[Str::before](#method-str-before)
[Str::beforeLast](#method-str-before-last)
[Str::between](#method-str-between)
[Str::camel](#method-camel-case)
[Str::contains](#method-str-contains)
[Str::containsAll](#method-str-contains-all)
[Str::endsWith](#method-ends-with)
[Str::finish](#method-str-finish)
[Str::headline](#method-str-headline)
[Str::is](#method-str-is)
[Str::isAscii](#method-str-is-ascii)
[Str::isUuid](#method-str-is-uuid)
[Str::kebab](#method-kebab-case)
[Str::length](#method-str-length)
[Str::limit](#method-str-limit)
[Str::lower](#method-str-lower)
[Str::markdown](#method-str-markdown)
[Str::mask](#method-str-mask)
[Str::orderedUuid](#method-str-ordered-uuid)
[Str::padBoth](#method-str-padboth)
[Str::padLeft](#method-str-padleft)
[Str::padRight](#method-str-padright)
[Str::plural](#method-str-plural)
[Str::pluralStudly](#method-str-plural-studly)
[Str::random](#method-str-random)
[Str::remove](#method-str-remove)
[Str::replace](#method-str-replace)
[Str::replaceArray](#method-str-replace-array)
[Str::replaceFirst](#method-str-replace-first)
[Str::replaceLast](#method-str-replace-last)
[Str::reverse](#method-str-reverse)
[Str::singular](#method-str-singular)
[Str::slug](#method-str-slug)
[Str::snake](#method-snake-case)
[Str::start](#method-str-start)
[Str::startsWith](#method-starts-with)
[Str::studly](#method-studly-case)
[Str::substr](#method-str-substr)
[Str::substrCount](#method-str-substrcount)
[Str::substrReplace](#method-str-substrreplace)
[Str::title](#method-title-case)
[Str::toHtmlString](#method-str-to-html-string)
[Str::ucfirst](#method-str-ucfirst)
[Str::upper](#method-str-upper)
[Str::uuid](#method-str-uuid)
[Str::wordCount](#method-str-word-count)
[Str::words](#method-str-words)
[trans](#method-trans)
[trans_choice](#method-trans-choice)

</div>
<a name="fluent-strings-method-list"></a>

### Fluent 字串

<div class="collection-method-list" markdown="1">
[after](#method-fluent-str-after)
[afterLast](#method-fluent-str-after-last)
[append](#method-fluent-str-append)
[ascii](#method-fluent-str-ascii)
[basename](#method-fluent-str-basename)
[before](#method-fluent-str-before)
[beforeLast](#method-fluent-str-before-last)
[between](#method-fluent-str-between)
[camel](#method-fluent-str-camel)
[contains](#method-fluent-str-contains)
[containsAll](#method-fluent-str-contains-all)
[dirname](#method-fluent-str-dirname)
[endsWith](#method-fluent-str-ends-with)
[exactly](#method-fluent-str-exactly)
[explode](#method-fluent-str-explode)
[finish](#method-fluent-str-finish)
[is](#method-fluent-str-is)
[isAscii](#method-fluent-str-is-ascii)
[isEmpty](#method-fluent-str-is-empty)
[isNotEmpty](#method-fluent-str-is-not-empty)
[isUuid](#method-fluent-str-is-uuid)
[kebab](#method-fluent-str-kebab)
[length](#method-fluent-str-length)
[limit](#method-fluent-str-limit)
[lower](#method-fluent-str-lower)
[ltrim](#method-fluent-str-ltrim)
[markdown](#method-fluent-str-markdown)
[mask](#method-fluent-str-mask)
[match](#method-fluent-str-match)
[matchAll](#method-fluent-str-match-all)
[padBoth](#method-fluent-str-padboth)
[padLeft](#method-fluent-str-padleft)
[padRight](#method-fluent-str-padright)
[pipe](#method-fluent-str-pipe)
[plural](#method-fluent-str-plural)
[prepend](#method-fluent-str-prepend)
[remove](#method-fluent-str-remove)
[replace](#method-fluent-str-replace)
[replaceArray](#method-fluent-str-replace-array)
[replaceFirst](#method-fluent-str-replace-first)
[replaceLast](#method-fluent-str-replace-last)
[replaceMatches](#method-fluent-str-replace-matches)
[rtrim](#method-fluent-str-rtrim)
[scan](#method-fluent-str-scan)
[singular](#method-fluent-str-singular)
[slug](#method-fluent-str-slug)
[snake](#method-fluent-str-snake)
[split](#method-fluent-str-split)
[start](#method-fluent-str-start)
[startsWith](#method-fluent-str-starts-with)
[studly](#method-fluent-str-studly)
[substr](#method-fluent-str-substr)
[substrReplace](#method-fluent-str-substrreplace)
[tap](#method-fluent-str-tap)
[test](#method-fluent-str-test)
[title](#method-fluent-str-title)
[trim](#method-fluent-str-trim)
[ucfirst](#method-fluent-str-ucfirst)
[upper](#method-fluent-str-upper)
[when](#method-fluent-str-when)
[whenContains](#method-fluent-str-when-contains)
[whenContainsAll](#method-fluent-str-when-contains-all)
[whenEmpty](#method-fluent-str-when-empty)
[whenNotEmpty](#method-fluent-str-when-not-empty)
[whenStartsWith](#method-fluent-str-when-starts-with)
[whenEndsWith](#method-fluent-str-when-ends-with)
[whenExactly](#method-fluent-str-when-exactly)
[whenIs](#method-fluent-str-when-is)
[whenIsAscii](#method-fluent-str-when-is-ascii)
[whenIsUuid](#method-fluent-str-when-is-uuid)
[whenTest](#method-fluent-str-when-test)
[wordCount](#method-fluent-str-word-count)
[words](#method-fluent-str-words)

</div>
<a name="urls-method-list"></a>

### URL

<div class="collection-method-list" markdown="1">
[action](#method-action)
[asset](#method-asset)
[route](#method-route)
[secure_asset](#method-secure-asset)
[secure_url](#method-secure-url)
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
[dd](#method-dd)
[dispatch](#method-dispatch)
[dump](#method-dump)
[env](#method-env)
[event](#method-event)
[filled](#method-filled)
[info](#method-info)
[logger](#method-logger)
[method_field](#method-method-field)
[now](#method-now)
[old](#method-old)
[optional](#method-optional)
[policy](#method-policy)
[redirect](#method-redirect)
[report](#method-report)
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
<a name="method-listing"></a>

## 方法清單

<style>
    .collection-method code {
        font-size: 14px;
    }

    .collection-method:not(.first-collection-method) {
        margin-top: 50px;
    }
</style>
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
    
    $first = Arr::first($array, function ($value, $key) {
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

若給定的陣列為^[關聯式陣列](Associative Array)，`Arr::isAssoc` 回傳 `true`。當某個陣列的索引鍵不是以 0 開始依序排列的數字時，就是「關聯式」的陣列：

    use Illuminate\Support\Arr;
    
    $isAssoc = Arr::isAssoc(['product' => ['name' => 'Desk', 'price' => 100]]);
    
    // true
    
    $isAssoc = Arr::isAssoc([1, 2, 3]);
    
    // false
<a name="method-array-last"></a>

#### `Arr::last()` {.collection-method}

`Arr::last` 方法會回傳該陣列中通過給定布林測試的最後一個元素：

    use Illuminate\Support\Arr;
    
    $array = [100, 200, 300, 110];
    
    $last = Arr::last($array, function ($value, $key) {
        return $value >= 150;
    });
    
    // 300
可以在第三個引數上提供一個預設值給該方法。若沒有任何值通過條件測試，就會回傳這個預設值：

    use Illuminate\Support\Arr;
    
    $last = Arr::last($array, $callback, $default);
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
    
    $sorted = array_values(Arr::sort($array, function ($value) {
        return $value['name'];
    }));
    
    /*
        [
            ['name' => 'Chair'],
            ['name' => 'Desk'],
            ['name' => 'Table'],
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
<a name="method-array-to-css-classes"></a>

#### `Arr::toCssClasses()` {.collection-method}

`Arr::toCssClasses` 可以有條件地編譯 CSS class 字串。該方法接受一組包含 class 的陣列，其中，陣列的索引鍵代表欲新增的 class，陣列值則是一個布林運算式。若陣列的元素有數字索引鍵，則該元素一定會被加到轉譯後的 Class 列表上：

    use Illuminate\Support\Arr;
    
    $isActive = false;
    $hasError = true;
    
    $array = ['p-4', 'font-bold' => $isActive, 'bg-red' => $hasError];
    
    $classes = Arr::toCssClasses($array);
    
    /*
        'p-4 bg-red'
    */
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
    
    $filtered = Arr::where($array, function ($value, $key) {
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
    
    data_set($data, 'products.desk.price', 200, $overwrite = false);
    
    // ['products' => ['desk' => ['price' => 100]]]
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
<a name="strings"></a>

## 字串

<a name="method-__"></a>

#### `__()` {.collection-method}

`__` 函式使用[語系檔](/docs/{{version}}/localization)來翻譯給定的翻譯字串或翻譯索引鍵：

    echo __('Welcome to our application');
    
    echo __('messages.welcome');
若指定的翻譯字串或翻譯索引鍵不存在時，`__` 函式會回傳給定的值。因此，在上述範例中，若 `messages.welcome` 索引鍵不存在，`__` 函式會回傳 `messages.welcome`。

<a name="method-class-basename"></a>

#### `class_basename()` {.collection-method}

`class_basename` 函式回傳給定類別在移除類別 Namespace 後的類別名稱：

    $class = class_basename('Foo\Bar\Baz');
    
    // Baz
<a name="method-e"></a>

#### `e()` {.collection-method}

`e` 函式執行 PHP 的 `htmlspecialchars` 函式，其中 `double_encode` 選項預設為 `true`：

    echo e('<html>foo</html>');
    
    // &lt;html&gt;foo&lt;/html&gt;
<a name="method-preg-replace-array"></a>

#### `preg_replace_array()` {.collection-method}

`preg_replace_array` 函式使用陣列來依序在陣列中取代給定的格式：

    $string = 'The event will take place between :start and :end';
    
    $replaced = preg_replace_array('/:[a-z_]+/', ['8:30', '9:00'], $string);
    
    // The event will take place between 8:30 and 9:00
<a name="method-str-after"></a>

#### `Str::after()` {.collection-method}

`Str::after` 方法回傳字串中給定值以後的所有內容。若該字串中找不到給定值，會回傳整個字串：

    use Illuminate\Support\Str;
    
    $slice = Str::after('This is my name', 'This is');
    
    // ' my name'
<a name="method-str-after-last"></a>

#### `Str::afterLast()` {.collection-method}

`Str::afterLast` 方法回傳給定字串後最後一個出現給定值之後的所有內容。若找不到該值，會回傳整個字串：

    use Illuminate\Support\Str;
    
    $slice = Str::afterLast('App\Http\Controllers\Controller', '\\');
    
    // 'Controller'
<a name="method-str-ascii"></a>

#### `Str::ascii()` {.collection-method}

`Str::ascii` 方法會嘗試將給定字串翻譯為 ASCII 值：

    use Illuminate\Support\Str;
    
    $slice = Str::ascii('û');
    
    // 'u'
<a name="method-str-before"></a>

#### `Str::before()` {.collection-method}

`Str::before` 回傳字串在遇到給定值前的所有內容：

    use Illuminate\Support\Str;
    
    $slice = Str::before('This is my name', 'my name');
    
    // 'This is '
<a name="method-str-before-last"></a>

#### `Str::beforeLast()` {.collection-method}

`Str::beforeLast` 方法回傳字串中最後一次出現給定值以前的所有內容：

    use Illuminate\Support\Str;
    
    $slice = Str::beforeLast('This is my name', 'is');
    
    // 'This '
<a name="method-str-between"></a>

#### `Str::between()` {.collection-method}

`Str::between` 方法回傳介於兩個值之間的字串：

    use Illuminate\Support\Str;
    
    $slice = Str::between('This is my name', 'This', 'name');
    
    // ' is my '
<a name="method-camel-case"></a>

#### `Str::camel()` {.collection-method}

`Str::camel` 方法將給定字串轉為 `camelCase` —— 駝峰命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::camel('foo_bar');
    
    // fooBar
<a name="method-str-contains"></a>

#### `Str::contains()` {.collection-method}

`Str::contains` 方法判斷給定字串是否包含給定值。該方法區分大小寫：

    use Illuminate\Support\Str;
    
    $contains = Str::contains('This is my name', 'my');
    
    // true
也可以傳入一組要判斷的陣列值，來判斷給定字串中是否有包含該陣列中任何一個值：

    use Illuminate\Support\Str;
    
    $contains = Str::contains('This is my name', ['my', 'foo']);
    
    // true
<a name="method-str-contains-all"></a>

#### `Str::containsAll()` {.collection-method}

`Str::containsAll` 判斷給定字串是否有包含給定陣列中的所有值：

    use Illuminate\Support\Str;
    
    $containsAll = Str::containsAll('This is my name', ['my', 'name']);
    
    // true
<a name="method-ends-with"></a>

#### `Str::endsWith()` {.collection-method}

Str::endsWith` 方法可判斷給定字串是否以給定值結尾：

    use Illuminate\Support\Str;
    
    $result = Str::endsWith('This is my name', 'name');
    
    // true
也可以傳入一組陣列值來判斷給定字串的結尾是否符合該陣列內的其中一項：

    use Illuminate\Support\Str;
    
    $result = Str::endsWith('This is my name', ['name', 'foo']);
    
    // true
    
    $result = Str::endsWith('This is my name', ['this', 'foo']);
    
    // false
<a name="method-str-finish"></a>

#### `Str::finish()` {.collection-method}

`Str::finish` 方法會在給定字串不是以給定值結尾時，在該字串後方加上這個值：

    use Illuminate\Support\Str;
    
    $adjusted = Str::finish('this/string', '/');
    
    // this/string/
    
    $adjusted = Str::finish('this/string/', '/');
    
    // this/string/
<a name="method-str-headline"></a>

#### `Str::headline()` {.collection-method}

`Str::headline` 方法將以大小寫、減號、底線等方式區隔的字串轉換為以空格區隔的字串，並將其中每個單詞的首字母都轉為大寫：

    use Illuminate\Support\Str;
    
    $headline = Str::headline('steve_jobs');
    
    // Steve Jobs
    
    $headline = Str::headline('EmailNotificationSent');
    
    // Email Notification Sent
<a name="method-str-is"></a>

#### `Str::is()` {.collection-method}

`Str::is` 判斷給定字串是否符合給定的格式。可使用星號作為萬用字元：

    use Illuminate\Support\Str;
    
    $matches = Str::is('foo*', 'foobar');
    
    // true
    
    $matches = Str::is('baz*', 'foobar');
    
    // false
<a name="method-str-is-ascii"></a>

#### `Str::isAscii()` {.collection-method}

`Str::isAscii` 方法判斷給定字串是否為 7 位元 ASCII：

    use Illuminate\Support\Str;
    
    $isAscii = Str::isAscii('Taylor');
    
    // true
    
    $isAscii = Str::isAscii('ü');
    
    // false
<a name="method-str-is-uuid"></a>

#### `Str::isUuid()` {.collection-method}

`Str::isUuid` 方法判斷給定字串是否為有效的 UUID：

    use Illuminate\Support\Str;
    
    $isUuid = Str::isUuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de');
    
    // true
    
    $isUuid = Str::isUuid('laravel');
    
    // false
<a name="method-kebab-case"></a>

#### `Str::kebab()` {.collection-method}

`Str::kebab` 方法將給定字串轉換為 `kebab-case`：

    use Illuminate\Support\Str;
    
    $converted = Str::kebab('fooBar');
    
    // foo-bar
<a name="method-str-length"></a>

#### `Str::length()` {.collection-method}

`Str::length` 方法回傳給定字串的長度：

    use Illuminate\Support\Str;
    
    $length = Str::length('Laravel');
    
    // 7
<a name="method-str-limit"></a>

#### `Str::limit()` {.collection-method}

`Str::limit` 方法將給定字串截斷成指定長度：

    use Illuminate\Support\Str;
    
    $truncated = Str::limit('The quick brown fox jumps over the lazy dog', 20);
    
    // The quick brown fox...
也可以傳入第三個引數給該方法，以更改當字串被截斷時要加在最後方的內容：

    use Illuminate\Support\Str;
    
    $truncated = Str::limit('The quick brown fox jumps over the lazy dog', 20, ' (...)');
    
    // The quick brown fox (...)
<a name="method-str-lower"></a>

#### `Str::lower()` {.collection-method}

`Str::lower` 方法將給定字串轉為小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::lower('LARAVEL');
    
    // laravel
<a name="method-str-markdown"></a>

#### `Str::markdown()` {.collection-method}

`Str::markdown` 方法可將 GitHub Flavored Markdown 轉位為 HTML：

    use Illuminate\Support\Str;
    
    $html = Str::markdown('# Laravel');
    
    // <h1>Laravel</h1>
    
    $html = Str::markdown('# Taylor <b>Otwell</b>', [
        'html_input' => 'strip',
    ]);
    
    // <h1>Taylor Otwell</h1>
<a name="method-str-mask"></a>

#### `Str::mask()` {.collection-method}

`Str::mask` 方法將字串中的一部分轉為重複字元，可用來為 E-Mail 位址或電話號碼⋯⋯等字串^[打碼](Obfuscate)：

    use Illuminate\Support\Str;
    
    $string = Str::mask('taylor@example.com', '*', 3);
    
    // tay***************
若有需要，`mask` 方法的第三個引數可提供負數，這樣 `mask` 就會從字串結尾起給定的長度開始打碼：

    $string = Str::mask('taylor@example.com', '*', -15, 3);
    
    // tay***@example.com
<a name="method-str-ordered-uuid"></a>

#### `Str::orderedUuid()` {.collection-method}

`Str::orderedUuid` 方法會產生一個「^[時戳優先](Timestamp First)」的 UUID，可用來儲存在有所引的資料庫欄位中。使用本方法產生的 UUID 在排序時會被排到之前使用本方法產生的 UUID 之後：

    use Illuminate\Support\Str;
    
    return (string) Str::orderedUuid();
<a name="method-str-padboth"></a>

#### `Str::padBoth()` {.collection-method}

`Str::padBoth` 方法包裝了 PHP 的 `str_path` 方法，會填充字串的兩端，直到字串符合預期的長度：

    use Illuminate\Support\Str;
    
    $padded = Str::padBoth('James', 10, '_');
    
    // '__James___'
    
    $padded = Str::padBoth('James', 10);
    
    // '  James   '
<a name="method-str-padleft"></a>

#### `Str::padLeft()` {.collection-method}

`Str::padLeft` 包裝了 PHP 的 `str_pad` 方法，會使用另一個字串填充給定字串的左邊，直到符合預期的長度：

    use Illuminate\Support\Str;
    
    $padded = Str::padLeft('James', 10, '-=');
    
    // '-=-=-James'
    
    $padded = Str::padLeft('James', 10);
    
    // '     James'
<a name="method-str-padright"></a>

#### `Str::padRight()` {.collection-method}

`Str::padRight` 包裝了 PHP 的 `str_pad` 方法，會使用另一個字串填充給定字串的右邊，直到符合預期的長度：

    use Illuminate\Support\Str;
    
    $padded = Str::padRight('James', 10, '-');
    
    // 'James-----'
    
    $padded = Str::padRight('James', 10);
    
    // 'James     '
<a name="method-str-plural"></a>

#### `Str::plural()` {.collection-method}

`Str::plural` 方法將單數單詞轉換為其複數型。該函式目前只支援英文：

    use Illuminate\Support\Str;
    
    $plural = Str::plural('car');
    
    // cars
    
    $plural = Str::plural('child');
    
    // children
也可以提供一個整數作為該方法的第二個引數，用來判斷要取得該字串的單數或複數型：

    use Illuminate\Support\Str;
    
    $plural = Str::plural('child', 2);
    
    // children
    
    $singular = Str::plural('child', 1);
    
    // child
<a name="method-str-plural-studly"></a>

#### `Str::pluralStudly()` {.collection-method}

`Str::pluralStudly` 方法將單數單詞轉換為 Studly 命名法的複數型。該方法目前只支援英文：

    use Illuminate\Support\Str;
    
    $plural = Str::pluralStudly('VerifiedHuman');
    
    // VerifiedHumans
    
    $plural = Str::pluralStudly('UserFeedback');
    
    // UserFeedback
也可以提供一個整數作為該方法的第二個引數，用來判斷要取得該字串的單數或複數型：

    use Illuminate\Support\Str;
    
    $plural = Str::pluralStudly('VerifiedHuman', 2);
    
    // VerifiedHumans
    
    $singular = Str::pluralStudly('VerifiedHuman', 1);
    
    // VerifiedHuman
<a name="method-str-random"></a>

#### `Str::random()` {.collection-method}

`Str::random` 方法產生指定長度的隨機字串。該函式使用 PHP 的 `random_bytes` 函式：

    use Illuminate\Support\Str;
    
    $random = Str::random(40);
<a name="method-str-remove"></a>

#### `Str::remove()` {.collection-method}

`Str::remove` 方法從字串中移除給定的一個或多個值：

    use Illuminate\Support\Str;
    
    $string = 'Peter Piper picked a peck of pickled peppers.';
    
    $removed = Str::remove('e', $string);
    
    // Ptr Pipr pickd a pck of pickld ppprs.
也可以傳入 `false` 作為第三個引數給 `remove` 方法來在移除字串時忽略大小寫差異：

<a name="method-str-replace"></a>

#### `Str::replace()` {.collection-method}

`Str::replace` 方法在字串中取代給定字串：

    use Illuminate\Support\Str;
    
    $string = 'Laravel 8.x';
    
    $replaced = Str::replace('8.x', '9.x', $string);
    
    // Laravel 9.x
<a name="method-str-replace-array"></a>

#### `Str::replaceArray()` {.collection-method}

`Str::replaceArray` 函式使用陣列來依序在陣列中取代給定的值：

    use Illuminate\Support\Str;
    
    $string = 'The event will take place between ? and ?';
    
    $replaced = Str::replaceArray('?', ['8:30', '9:00'], $string);
    
    // The event will take place between 8:30 and 9:00
<a name="method-str-replace-first"></a>

#### `Str::replaceFirst()` {.collection-method}

`Str::replaceFirst` 方法取代字串中第一次出現的給定值：

    use Illuminate\Support\Str;
    
    $replaced = Str::replaceFirst('the', 'a', 'the quick brown fox jumps over the lazy dog');
    
    // a quick brown fox jumps over the lazy dog
<a name="method-str-replace-last"></a>

#### `Str::replaceLast()` {.collection-method}

`Str::replaceLast` 方法取代字串中最後一次出現的給定值：

    use Illuminate\Support\Str;
    
    $replaced = Str::replaceLast('the', 'a', 'the quick brown fox jumps over the lazy dog');
    
    // the quick brown fox jumps over a lazy dog
<a name="method-str-reverse"></a>

#### `Str::reverse()` {.collection-method}

`Str::reverse` 方法反轉給定的字串：

    use Illuminate\Support\Str;
    
    $reversed = Str::reverse('Hello World');
    
    // dlroW olleH
<a name="method-str-singular"></a>

#### `Str::singular()` {.collection-method}

`Str::singular` 方法將複數單詞轉換為其單數型。該函式目前只支援英文：

    use Illuminate\Support\Str;
    
    $singular = Str::singular('cars');
    
    // car
    
    $singular = Str::singular('children');
    
    // child
<a name="method-str-slug"></a>

#### `Str::slug()` {.collection-method}

`Str::slug` 方法以給定字串產生適合在 URL 中使用的「Slug」格式：

    use Illuminate\Support\Str;
    
    $slug = Str::slug('Laravel 5 Framework', '-');
    
    // laravel-5-framework
<a name="method-snake-case"></a>

#### `Str::snake()` {.collection-method}

`Str::snake` 方法將給定字串轉為 `snake_case` —— 蛇型命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::snake('fooBar');
    
    // foo_bar
    
    $converted = Str::snake('fooBar', '-');
    
    // foo-bar
<a name="method-str-start"></a>

#### `Str::start()` {.collection-method}

`Str::start` 方法會在給定字串不是以給定值起始時，在該字串前方加上這個值：

    use Illuminate\Support\Str;
    
    $adjusted = Str::start('this/string', '/');
    
    // /this/string
    
    $adjusted = Str::start('/this/string', '/');
    
    // /this/string
<a name="method-starts-with"></a>

#### `Str::startsWith()` {.collection-method}

Str::startsWith` 方法可判斷給定字串是否以給定值起始：

    use Illuminate\Support\Str;
    
    $result = Str::startsWith('This is my name', 'This');
    
    // true
若傳入一組陣列，當字串以給定值中任何一個值開頭時，`startsWith` 方法會回傳 `true`：

    $result = Str::startsWith('This is my name', ['This', 'That', 'There']);
    
    // true
<a name="method-studly-case"></a>

#### `Str::studly()` {.collection-method}

`Str::studly` 方法將給定字串轉為 `StudlyCase` —— Studly 命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::studly('foo_bar');
    
    // FooBar
<a name="method-str-substr"></a>

#### `Str::substr()` {.collection-method}

`Str::substr` 方法回傳字串中指定的起始位置開始指定長度的字串：

    use Illuminate\Support\Str;
    
    $converted = Str::substr('The Laravel Framework', 4, 7);
    
    // Laravel
<a name="method-str-substrcount"></a>

#### `Str::substrCount()` {.collection-method}

`Str::substrCount` 方法回傳給定值中給定值出現的次數：

    use Illuminate\Support\Str;
    
    $count = Str::substrCount('If you like ice cream, you will like snow cones.', 'like');
    
    // 2
<a name="method-str-substrreplace"></a>

#### `Str::substrReplace()` {.collection-method}

`Str::substrreplace` 方法在字串中取代其中一段文字，第三個引數指定起始位置，並以第四個引數來指定要取代的字元數。若第四個引數傳入 `0`，則會在指定位置插入字串，而不取代字串中現有的字元：

    use Illuminate\Support\Str;
    
    $result = Str::substrReplace('1300', ':', 2); 
    // 13:
    
    $result = Str::substrReplace('1300', ':', 2, 0); 
    // 13:00
<a name="method-title-case"></a>

#### `Str::title()` {.collection-method}

`Str::title` 方法將給定字串轉為 `Title Case` —— 標題用的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::title('a nice title uses the correct case');
    
    // A Nice Title Uses The Correct Case
<a name="method-str-to-html-string"></a>

#### `Str::toHtmlString()` {.collection-method}

`Str::toHtmlString` 方法將字串實體轉換為 `Illuminate\Support\HtmlString` 的實體。`HtmlString` 實體可以在 Blade 樣板中顯示：

    use Illuminate\Support\Str;
    
    $htmlString = Str::of('Nuno Maduro')->toHtmlString();
<a name="method-str-ucfirst"></a>

#### `Str::ucfirst()` {.collection-method}

`Str::ucfirst` 方法回傳給定字串第一個字元轉為大寫後的字串：

    use Illuminate\Support\Str;
    
    $string = Str::ucfirst('foo bar');
    
    // Foo bar
<a name="method-str-upper"></a>

#### `Str::upper()` {.collection-method}

`Str::upper` 方法將給定字串轉換為大寫：

    use Illuminate\Support\Str;
    
    $string = Str::upper('laravel');
    
    // LARAVEL
<a name="method-str-uuid"></a>

#### `Str::uuid()` {.collection-method}

`Str::uuid` 方法產生 UUID (第 4 版)：

    use Illuminate\Support\Str;
    
    return (string) Str::uuid();
<a name="method-str-word-count"></a>

#### `Str::wordCount()` {.collection-method}

`Str::wordCount` 方法回傳該字串中所包含的單詞數：

```php
use Illuminate\Support\Str;

Str::wordCount('Hello, world!'); // 2
```
<a name="method-str-words"></a>

#### `Str::words()` {.collection-method}

`Str::words` 方法將字串中的單詞數限制在指定數量內。也可以第三引數來傳入一個額外的字串，用來指定當字串被截斷時要加在最後方的內容：

    use Illuminate\Support\Str;
    
    return Str::words('Perfectly balanced, as all things should be.', 3, ' >>>');
    
    // Perfectly balanced, as >>>
<a name="method-trans"></a>

#### `trans()` {.collection-method}

`trans` 函式使用[語系檔](/docs/{{version}}/localization)來翻譯給定的翻譯字串或翻譯索引鍵：

    echo trans('messages.welcome');
若指定的翻譯字串或翻譯索引鍵不存在時，`trans` 函式會回傳給定的值。因此，在上述範例中，若 `messages.welcome` 索引鍵不存在，`trans` 函式會回傳 `messages.welcome`。

<a name="method-trans-choice"></a>

#### `trans_choice()` {.collection-method}

`trans_choice` 函式會翻譯有詞形變化的翻譯索引鍵：

    echo trans_choice('messages.notifications', $unreadCount);
若指定的翻譯字串或翻譯索引鍵不存在時，`trans_choice` 函式會回傳給定的值。因此，在上述範例中，若 `messages.notifications` 索引鍵不存在，`trans_choice` 函式會回傳 `messages.welcome`。

<a name="fluent-strings"></a>

## Fluent 字串

Fluent 字串提供處理字串值一個更流暢、物件導向的介面。我們可以串接多個字串操作，得到比起傳統字串操作來說更好閱讀的語法：

<a name="method-fluent-str-after"></a>

#### `after` {.collection-method}

`after` 方法回傳字串中給定值以後的所有內容。若該字串中找不到給定值，會回傳整個字串：

    use Illuminate\Support\Str;
    
    $slice = Str::of('This is my name')->after('This is');
    
    // ' my name'
<a name="method-fluent-str-after-last"></a>

#### `afterLast` {.collection-method}

`afterLast` 方法回傳給定字串後最後一個出現給定值之後的所有內容。若找不到該值，會回傳整個字串：

    use Illuminate\Support\Str;
    
    $slice = Str::of('App\Http\Controllers\Controller')->afterLast('\\');
    
    // 'Controller'
<a name="method-fluent-str-append"></a>

#### `append` {.collection-method}

`append` 方法將給定的值加到字串最後面：

    use Illuminate\Support\Str;
    
    $string = Str::of('Taylor')->append(' Otwell');
    
    // 'Taylor Otwell'
<a name="method-fluent-str-ascii"></a>

#### `ascii` {.collection-method}

`ascii` 方法會嘗試將給定字串翻譯為 ASCII 值：

    use Illuminate\Support\Str;
    
    $string = Str::of('ü')->ascii();
    
    // 'u'
<a name="method-fluent-str-basename"></a>

#### `basename` {.collection-method}

`basename` 方法回傳給定字串中最後一個名稱部分：

    use Illuminate\Support\Str;
    
    $string = Str::of('/foo/bar/baz')->basename();
    
    // 'baz'
若有需要，也可以提供要從最後一個元件中移除的「副檔名」：

    use Illuminate\Support\Str;
    
    $string = Str::of('/foo/bar/baz.jpg')->basename('.jpg');
    
    // 'baz'
<a name="method-fluent-str-before"></a>

#### `before` {.collection-method}

`before` 回傳字串在遇到給定值前的所有內容：

    use Illuminate\Support\Str;
    
    $slice = Str::of('This is my name')->before('my name');
    
    // 'This is '
<a name="method-fluent-str-before-last"></a>

#### `beforeLast` {.collection-method}

`beforeLast` 方法回傳字串中最後一次出現給定值以前的所有內容：

    use Illuminate\Support\Str;
    
    $slice = Str::of('This is my name')->beforeLast('is');
    
    // 'This '
<a name="method-fluent-str-between"></a>

#### `between` {.collection-method}

`between` 方法回傳介於兩個值之間的字串：

    use Illuminate\Support\Str;
    
    $converted = Str::of('This is my name')->between('This', 'name');
    
    // ' is my '
<a name="method-fluent-str-camel"></a>

#### `camel` {.collection-method}

`camel` 方法將給定字串轉為 `camelCase` —— 駝峰命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::of('foo_bar')->camel();
    
    // fooBar
<a name="method-fluent-str-contains"></a>

#### `contains` {.collection-method}

`contains` 方法判斷給定字串是否包含給定值。該方法區分大小寫：

    use Illuminate\Support\Str;
    
    $contains = Str::of('This is my name')->contains('my');
    
    // true
也可以傳入一組要判斷的陣列值，來判斷給定字串中是否有包含該陣列中任何一個值：

    use Illuminate\Support\Str;
    
    $contains = Str::of('This is my name')->contains(['my', 'foo']);
    
    // true
<a name="method-fluent-str-contains-all"></a>

#### `containsAll` {.collection-method}

`containsAll` 判斷給定字串是否有包含給定陣列中的所有值：

    use Illuminate\Support\Str;
    
    $containsAll = Str::of('This is my name')->containsAll(['my', 'name']);
    
    // true
<a name="method-fluent-str-dirname"></a>

#### `dirname` {.collection-method}

`dirname` 方法回傳給定字串中上層目錄的部分：

    use Illuminate\Support\Str;
    
    $string = Str::of('/foo/bar/baz')->dirname();
    
    // '/foo/bar'
若有需要，也可以指定要去的多少層以上的目錄：

    use Illuminate\Support\Str;
    
    $string = Str::of('/foo/bar/baz')->dirname(2);
    
    // '/foo'
<a name="method-fluent-str-ends-with"></a>

#### `endsWith` {.collection-method}

`endsWith` 方法可判斷給定字串是否以給定值結尾：

    use Illuminate\Support\Str;
    
    $result = Str::of('This is my name')->endsWith('name');
    
    // true
也可以傳入一組陣列值來判斷給定字串的結尾是否符合該陣列內的其中一項：

    use Illuminate\Support\Str;
    
    $result = Str::of('This is my name')->endsWith(['name', 'foo']);
    
    // true
    
    $result = Str::of('This is my name')->endsWith(['this', 'foo']);
    
    // false
<a name="method-fluent-str-exactly"></a>

#### `exactly` {.collection-method}

`exactly` 方法判斷給定字串是否完全符合另一個字串：

    use Illuminate\Support\Str;
    
    $result = Str::of('Laravel')->exactly('Laravel');
    
    // true
<a name="method-fluent-str-explode"></a>

#### `explode` {.collection-method}

`explode` 方法以給定的分隔符號來拆分字串，並回傳一個包含分割後所有段落的 Collection：

    use Illuminate\Support\Str;
    
    $collection = Str::of('foo bar baz')->explode(' ');
    
    // collect(['foo', 'bar', 'baz'])
<a name="method-fluent-str-finish"></a>

#### `finish` {.collection-method}

`finish` 方法會在給定字串不是以給定值結尾時，在該字串後方加上這個值：

    use Illuminate\Support\Str;
    
    $adjusted = Str::of('this/string')->finish('/');
    
    // this/string/
    
    $adjusted = Str::of('this/string/')->finish('/');
    
    // this/string/
<a name="method-fluent-str-is"></a>

#### `is` {.collection-method}

`is` 判斷給定字串是否符合給定的格式。可使用星號作為萬用字元：

    use Illuminate\Support\Str;
    
    $matches = Str::of('foobar')->is('foo*');
    
    // true
    
    $matches = Str::of('foobar')->is('baz*');
    
    // false
<a name="method-fluent-str-is-ascii"></a>

#### `isAscii` {.collection-method}

`isAscii` 方法判斷給定字串是否為 ASCII 字串：

    use Illuminate\Support\Str;
    
    $result = Str::of('Taylor')->isAscii();
    
    // true
    
    $result = Str::of('ü')->isAscii();
    
    // false
<a name="method-fluent-str-is-empty"></a>

#### `isEmpty` {.collection-method}

`isEmpty` 方法判斷給定字串是否為空：

    use Illuminate\Support\Str;
    
    $result = Str::of('  ')->trim()->isEmpty();
    
    // true
    
    $result = Str::of('Laravel')->trim()->isEmpty();
    
    // false
<a name="method-fluent-str-is-not-empty"></a>

#### `isNotEmpty` {.collection-method}

`isNotEmpty` 方法判斷給定字串是否不為空：

    use Illuminate\Support\Str;
    
    $result = Str::of('  ')->trim()->isNotEmpty();
    
    // false
    
    $result = Str::of('Laravel')->trim()->isNotEmpty();
    
    // true
<a name="method-fluent-str-is-uuid"></a>

#### `isUuid` {.collection-method}

`isUuid` 方法判斷給定字串是否為有效的 UUID：

    use Illuminate\Support\Str;
    
    $result = Str::of('5ace9ab9-e9cf-4ec6-a19d-5881212a452c')->isUuid();
    
    // true
    
    $result = Str::of('Taylor')->isUuid();
    
    // false
<a name="method-fluent-str-kebab"></a>

#### `kebab` {.collection-method}

`kebab` 方法將給定字串轉換為 `kebab-case` —— Kebab 命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::of('fooBar')->kebab();
    
    // foo-bar
<a name="method-fluent-str-length"></a>

#### `length` {.collection-method}

`length` 方法回傳給定字串的長度：

    use Illuminate\Support\Str;
    
    $length = Str::of('Laravel')->length();
    
    // 7
<a name="method-fluent-str-limit"></a>

#### `limit` {.collection-method}

`limit` 方法將給定字串截斷成指定長度：

    use Illuminate\Support\Str;
    
    $truncated = Str::of('The quick brown fox jumps over the lazy dog')->limit(20);
    
    // The quick brown fox...
也可以傳入第二個引數，以更改當字串被截斷時要加在最後方的內容：

    use Illuminate\Support\Str;
    
    $truncated = Str::of('The quick brown fox jumps over the lazy dog')->limit(20, ' (...)');
    
    // The quick brown fox (...)
<a name="method-fluent-str-lower"></a>

#### `lower` {.collection-method}

`lower` 方法將給定字串轉為小寫：

    use Illuminate\Support\Str;
    
    $result = Str::of('LARAVEL')->lower();
    
    // 'laravel'
<a name="method-fluent-str-ltrim"></a>

#### `ltrim` {.collection-method}

`ltrim` 方法修剪字串左邊的值：

    use Illuminate\Support\Str;
    
    $string = Str::of('  Laravel  ')->ltrim();
    
    // 'Laravel  '
    
    $string = Str::of('/Laravel/')->ltrim('/');
    
    // 'Laravel/'
<a name="method-fluent-str-markdown"></a>

#### `markdown` {.collection-method}

`markdown` 方法可將 GitHub Flavored Markdown 轉位為 HTML：

    use Illuminate\Support\Str;
    
    $html = Str::of('# Laravel')->markdown();
    
    // <h1>Laravel</h1>
    
    $html = Str::of('# Taylor <b>Otwell</b>')->markdown([
        'html_input' => 'strip',
    ]);
    
    // <h1>Taylor Otwell</h1>
<a name="method-fluent-str-mask"></a>

#### `mask` {.collection-method}

`mask` 方法將字串中的一部分轉為重複字元，可用來為 E-Mail 位址或電話號碼⋯⋯等字串^[打碼](Obfuscate)：

    use Illuminate\Support\Str;
    
    $string = Str::of('taylor@example.com')->mask('*', 3);
    
    // tay***************
若有需要，`mask` 方法的第三個引數可提供負數，這樣 `mask` 就會從字串結尾起給定的長度開始打碼：

    $string = Str::of('taylor@example.com')->mask('*', -15, 3);
    
    // tay***@example.com
<a name="method-fluent-str-match"></a>

#### `match` {.collection-method}

`match` 方法回傳字串中符合給定正規表示式格式的部分：

    use Illuminate\Support\Str;
    
    $result = Str::of('foo bar')->match('/bar/');
    
    // 'bar'
    
    $result = Str::of('foo bar')->match('/foo (.*)/');
    
    // 'bar'
<a name="method-fluent-str-match-all"></a>

#### `matchAll` {.collection-method}

`matchAll` 方法回傳一組 Collection，其中包含字串中所有符合給定正規表示式格式的部分：

    use Illuminate\Support\Str;
    
    $result = Str::of('bar foo bar')->matchAll('/bar/');
    
    // collect(['bar', 'bar'])
也可以在正規式中指定^[分組](Matching Group)，Laravel 會回傳一個包含這些分組的 Collection：

    use Illuminate\Support\Str;
    
    $result = Str::of('bar fun bar fly')->matchAll('/f(\w*)/');
    
    // collect(['un', 'ly']);
若未找到相符合的內容，會回傳空 Collection。

<a name="method-fluent-str-padboth"></a>

#### `padBoth` {.collection-method}

`padBoth` 方法包裝了 PHP 的 `str_path` 方法，會填充字串的兩端，直到字串符合預期的長度：

    use Illuminate\Support\Str;
    
    $padded = Str::of('James')->padBoth(10, '_');
    
    // '__James___'
    
    $padded = Str::of('James')->padBoth(10);
    
    // '  James   '
<a name="method-fluent-str-padleft"></a>

#### `padLeft` {.collection-method}

`padLeft` 包裝了 PHP 的 `str_pad` 方法，會使用另一個字串填充給定字串的左邊，直到符合預期的長度：

    use Illuminate\Support\Str;
    
    $padded = Str::of('James')->padLeft(10, '-=');
    
    // '-=-=-James'
    
    $padded = Str::of('James')->padLeft(10);
    
    // '     James'
<a name="method-fluent-str-padright"></a>

#### `padRight` {.collection-method}

`padRight` 包裝了 PHP 的 `str_pad` 方法，會使用另一個字串填充給定字串的右邊，直到符合預期的長度：

    use Illuminate\Support\Str;
    
    $padded = Str::of('James')->padRight(10, '-');
    
    // 'James-----'
    
    $padded = Str::of('James')->padRight(10);
    
    // 'James     '
<a name="method-fluent-str-pipe"></a>

#### `pipe` {.collection-method}

`pipe` 方法會講目前字串傳入給定的閉包內，來讓我們變換字串：

    use Illuminate\Support\Str;
    
    $hash = Str::of('Laravel')->pipe('md5')->prepend('Checksum: ');
    
    // 'Checksum: a5c95b86291ea299fcbe64458ed12702'
    
    $closure = Str::of('foo')->pipe(function ($str) {
        return 'bar';
    });
    
    // 'bar'
<a name="method-fluent-str-plural"></a>

#### `plural` {.collection-method}

`plural` 方法將單數單詞轉換為其複數型。該函式目前只支援英文：

    use Illuminate\Support\Str;
    
    $plural = Str::of('car')->plural();
    
    // cars
    
    $plural = Str::of('child')->plural();
    
    // children
也可以提供一個整數作為該方法的第二個引數，用來判斷要取得該字串的單數或複數型：

    use Illuminate\Support\Str;
    
    $plural = Str::of('child')->plural(2);
    
    // children
    
    $plural = Str::of('child')->plural(1);
    
    // child
<a name="method-fluent-str-prepend"></a>

#### `prepend` {.collection-method}

`prepend` 方法將給定的值加到字串最後面：

    use Illuminate\Support\Str;
    
    $string = Str::of('Framework')->prepend('Laravel ');
    
    // Laravel Framework
<a name="method-fluent-str-remove"></a>

#### `remove` {.collection-method}

`remove` 方法從字串中移除給定的一個或多個值：

    use Illuminate\Support\Str;
    
    $string = Str::of('Arkansas is quite beautiful!')->remove('quite');
    
    // Arkansas is beautiful!
也可以傳入 `false` 作為第二個引數，來在移除字串時忽略大小寫差異：

<a name="method-fluent-str-replace"></a>

#### `replace` {.collection-method}

`replace` 方法在字串中取代給定字串：

    use Illuminate\Support\Str;
    
    $replaced = Str::of('Laravel 6.x')->replace('6.x', '7.x');
    
    // Laravel 7.x
<a name="method-fluent-str-replace-array"></a>

#### `replaceArray` {.collection-method}

`replaceArray` 函式使用陣列來依序在陣列中取代給定的值：

    use Illuminate\Support\Str;
    
    $string = 'The event will take place between ? and ?';
    
    $replaced = Str::of($string)->replaceArray('?', ['8:30', '9:00']);
    
    // The event will take place between 8:30 and 9:00
<a name="method-fluent-str-replace-first"></a>

#### `replaceFirst` {.collection-method}

`replaceFirst` 方法取代字串中第一次出現的給定值：

    use Illuminate\Support\Str;
    
    $replaced = Str::of('the quick brown fox jumps over the lazy dog')->replaceFirst('the', 'a');
    
    // a quick brown fox jumps over the lazy dog
<a name="method-fluent-str-replace-last"></a>

#### `replaceLast` {.collection-method}

`replaceLast` 方法取代字串中最後一次出現的給定值：

    use Illuminate\Support\Str;
    
    $replaced = Str::of('the quick brown fox jumps over the lazy dog')->replaceLast('the', 'a');
    
    // the quick brown fox jumps over a lazy dog
<a name="method-fluent-str-replace-matches"></a>

#### `replaceMatches` {.collection-method}

`replaceMatches` 方法使用給定取代字串來取代字串中所有符合格式的部分：

    use Illuminate\Support\Str;
    
    $replaced = Str::of('(+1) 501-555-1000')->replaceMatches('/[^A-Za-z0-9]++/', '')
    
    // '15015551000'
`replaceMatches` 也接受一個閉包，每當有符合格式的部分時，就會將符合的部分傳給該閉包，讓我們能在閉包內處理取代邏輯，並在閉包內回傳要取代的值：

    use Illuminate\Support\Str;
    
    $replaced = Str::of('123')->replaceMatches('/\d/', function ($match) {
        return '['.$match[0].']';
    });
    
    // '[1][2][3]'
<a name="method-fluent-str-rtrim"></a>

#### `rtrim` {.collection-method}

`rtrim` 方法修剪字串右邊的值：

    use Illuminate\Support\Str;
    
    $string = Str::of('  Laravel  ')->rtrim();
    
    // '  Laravel'
    
    $string = Str::of('/Laravel/')->rtrim('/');
    
    // '/Laravel'
<a name="method-fluent-str-scan"></a>

#### `scan` {.collection-method}

`scan` 方法依照給定的格式來講輸入字串解析為 Collection。給定的格式為 [`sscanf` PHP 函式](https://www.php.net/manual/en/function.sscanf.php)所支援的：

    use Illuminate\Support\Str;
    
    $collection = Str::of('filename.jpg')->scan('%[^.].%s');
    
    // collect(['filename', 'jpg'])
<a name="method-fluent-str-singular"></a>

#### `singular` {.collection-method}

`singular` 方法將複數單詞轉換為其單數型。該函式目前只支援英文：

    use Illuminate\Support\Str;
    
    $singular = Str::of('cars')->singular();
    
    // car
    
    $singular = Str::of('children')->singular();
    
    // child
<a name="method-fluent-str-slug"></a>

#### `slug` {.collection-method}

`slug` 方法以給定字串產生適合在 URL 中使用的「Slug」格式：

    use Illuminate\Support\Str;
    
    $slug = Str::of('Laravel Framework')->slug('-');
    
    // laravel-framework
<a name="method-fluent-str-snake"></a>

#### `snake` {.collection-method}

`snake` 方法將給定字串轉為 `snake_case` —— 蛇型命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::of('fooBar')->snake();
    
    // foo_bar
<a name="method-fluent-str-split"></a>

#### `split` {.collection-method}

`split` 方法使用正規表示式來將字串拆分為 Collection：

    use Illuminate\Support\Str;
    
    $segments = Str::of('one, two, three')->split('/[\s,]+/');
    
    // collect(["one", "two", "three"])
<a name="method-fluent-str-start"></a>

#### `start` {.collection-method}

`start` 方法會在給定字串不是以給定值起始時，在該字串前方加上這個值：

    use Illuminate\Support\Str;
    
    $adjusted = Str::of('this/string')->start('/');
    
    // /this/string
    
    $adjusted = Str::of('/this/string')->start('/');
    
    // /this/string
<a name="method-fluent-str-starts-with"></a>

#### `startsWith` {.collection-method}

startsWith` 方法可判斷給定字串是否以給定值起始：

    use Illuminate\Support\Str;
    
    $result = Str::of('This is my name')->startsWith('This');
    
    // true
<a name="method-fluent-str-studly"></a>

#### `studly` {.collection-method}

`studly` 方法將給定字串轉為 `StudlyCase` —— Studly 命名法的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::of('foo_bar')->studly();
    
    // FooBar
<a name="method-fluent-str-substr"></a>

#### `substr` {.collection-method}

`substr` 方法回傳字串中指定的起始位置開始指定長度的字串：

    use Illuminate\Support\Str;
    
    $string = Str::of('Laravel Framework')->substr(8);
    
    // Framework
    
    $string = Str::of('Laravel Framework')->substr(8, 5);
    
    // Frame
<a name="method-fluent-str-substrreplace"></a>

#### `substrReplace` {.collection-method}

`substrReplace` 方法在字串中取代其中一段文字，第二個引數指定起始位置，並以第三個引數來指定要取代的字元數。若第三個引數傳入 `0`，則會在指定位置插入字串，而不取代字串中現有的字元：

    use Illuminate\Support\Str;
    
    $string = Str::of('1300')->substrReplace(':', 2);
    
    // 13:
    
    $string = Str::of('The Framework')->substrReplace(' Laravel', 3, 0);
    
    // The Laravel Framework
<a name="method-fluent-str-tap"></a>

#### `tap` {.collection-method}

`tap` 方法將目前字串傳入給定的閉包內，讓我們可以在不影響目前字串的情況下檢視與處理該字串。無論該閉包回傳什麼，`tap` 都會回傳原始字串：

    use Illuminate\Support\Str;
    
    $string = Str::of('Laravel')
        ->append(' Framework')
        ->tap(function ($string) {
            dump('String after append: ' . $string);
        })
        ->upper();
    
    // LARAVEL FRAMEWORK
<a name="method-fluent-str-test"></a>

#### `test` {.collection-method}

`test` 方法判斷目前字串是否符合給定的正規表示式：

    use Illuminate\Support\Str;
    
    $result = Str::of('Laravel Framework')->test('/Laravel/');
    
    // true
<a name="method-fluent-str-title"></a>

#### `title` {.collection-method}

`title` 方法將給定字串轉為 `Title Case` —— 標題用的大小寫：

    use Illuminate\Support\Str;
    
    $converted = Str::of('a nice title uses the correct case')->title();
    
    // A Nice Title Uses The Correct Case
<a name="method-fluent-str-trim"></a>

#### `trim` {.collection-method}

`trim` 方法修剪字串值：

    use Illuminate\Support\Str;
    
    $string = Str::of('  Laravel  ')->trim();
    
    // 'Laravel'
    
    $string = Str::of('/Laravel/')->trim('/');
    
    // 'Laravel'
<a name="method-fluent-str-ucfirst"></a>

#### `ucfirst` {.collection-method}

`ucfirst` 方法回傳給定字串第一個字元轉為大寫後的字串：

    use Illuminate\Support\Str;
    
    $string = Str::of('foo bar')->ucfirst();
    
    // Foo bar
<a name="method-fluent-str-upper"></a>

#### `upper` {.collection-method}

`upper` 方法將給定字串轉換為大寫：

    use Illuminate\Support\Str;
    
    $adjusted = Str::of('laravel')->upper();
    
    // LARAVEL
<a name="method-fluent-str-when"></a>

#### `when` {.collection-method}

`when` 方法會在給定條件為 `true` 時叫用給定閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('Taylor')
                    ->when(true, function ($string) {
                        return $string->append(' Otwell');
                    });
    
    // 'Taylor Otwell'
若有需要，也可以傳入另一個閉包作為第三個引數給 `when` 方法。第三個引數上的閉包會在條件參數為 `false` 時被執行。

<a name="method-fluent-str-when-contains"></a>

#### `whenContains` {.collection-method}

`whenContains` 方法會在字串包含給定值時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('tony stark')
                ->whenContains('tony', function ($string) {
                    return $string->title();
                });
    
    // 'Tony Stark'
若有需要，也可以傳入另一個閉包作為第三個引數給 `whenContains` 方法。當字串內未包含給定值時會執行第三個引數上的閉包。

也可以傳入一組要判斷的陣列值，來判斷給定字串中是否有包含該陣列中任何一個值：

    use Illuminate\Support\Str;
    
    $string = Str::of('tony stark')
                ->whenContains(['tony', 'hulk'], function ($string) {
                    return $string->title();
                });
    
    // Tony Stark
<a name="method-fluent-str-when-contains-all"></a>

#### `whenContainsAll` {.collection-method}

`whenContainsAll` 方法會在字串包含所有給定的子字串時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('tony stark')
                    ->whenContainsAll(['tony', 'stark'], function ($string) {
                        return $string->title();
                    });
    
    // 'Tony Stark'
若有需要，也可以傳入另一個閉包作為第三個引數給 `when` 方法。第三個引數上的閉包會在條件參數為 `false` 時被執行。

<a name="method-fluent-str-when-empty"></a>

#### `whenEmpty` {.collection-method}

`whenEmpty` 方法會在目前字串為空時叫用給定的閉包。若該閉包有回傳值，則 `whenEmpty` 方法會回傳這個值。若該閉包無回傳值，則會回傳 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('  ')->whenEmpty(function ($string) {
        return $string->trim()->prepend('Laravel');
    });
    
    // 'Laravel'
<a name="method-fluent-str-when-not-empty"></a>

#### `whenNotEmpty` {.collection-method}

`whenNotEmpty` 方法會在目前字串不為空時叫用給定的閉包。若該閉包有回傳值，則 `whenNotEmpty` 方法會回傳這個值。若該閉包無回傳值，則會回傳 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('Framework')->whenNotEmpty(function ($string) {
        return $string->prepend('Laravel ');
    });
    
    // 'Laravel Framework'
<a name="method-fluent-str-when-starts-with"></a>

#### `whenStartsWith` {.collection-method}

`whenStartsWith` 方法會在字串以給定子字串開頭時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('disney world')->whenStartsWith('disney', function ($string) {
        return $string->title();
    });
    
    // 'Disney World'
<a name="method-fluent-str-when-ends-with"></a>

#### `whenEndsWith` {.collection-method}

`whenEndsWith` 方法會在字串以給定子字串結尾時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('disney world')->whenEndsWith('world', function ($string) {
        return $string->title();
    });
    
    // 'Disney World'
<a name="method-fluent-str-when-exactly"></a>

#### `whenExactly` {.collection-method}

`whenExactly` 方法會在目前字串符合給定字串時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('laravel')->whenExactly('laravel', function ($string) {
        return $string->title();
    });
    
    // 'Laravel'
<a name="method-fluent-str-when-is"></a>

#### `whenIs` {.collection-method}

`whenIs` 方法會在目前字串符合給定格式時叫用給定的閉包。可使用星號作為萬用字元。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('foo/bar')->whenIs('foo/*', function ($string) {
        return $string->append('/baz');
    });
    
    // 'foo/bar/baz'
<a name="method-fluent-str-when-is-ascii"></a>

#### `whenIsAscii` {.collection-method}

`whenIsAscii` 方法會在目前字串為 7 位元 ASCII 時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('foo/bar')->whenIsAscii('laravel', function ($string) {
        return $string->title();
    });
    
    // 'Laravel'
<a name="method-fluent-str-when-is-uuid"></a>

#### `whenIsUuid` {.collection-method}

`whenIsUuis` 方法會在目前字串為有效 UUID 時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('foo/bar')->whenIsUuid('a0a2a2d2-0b87-4a18-83f2-2529882be2de', function ($string) {
        return $string->substr(0, 8);
    });
    
    // 'a0a2a2d2'
<a name="method-fluent-str-when-test"></a>

#### `whenTest` {.collection-method}

`whenTest` 方法會在字串符合給定的正規表示式時叫用給定的閉包。該閉包會收到 Fluent 字串實體：

    use Illuminate\Support\Str;
    
    $string = Str::of('laravel framework')->whenTest('/laravel/', function ($string) {
        return $string->title();
    });
    
    // 'Laravel Framework'
<a name="method-fluent-str-word-count"></a>

#### `wordCount` {.collection-method}

`wordCount` 方法回傳該字串中所包含的單詞數：

```php
use Illuminate\Support\Str;

Str::of('Hello, world!')->wordCount(); // 2
```
<a name="method-fluent-str-words"></a>

#### `words` {.collection-method}

`words` 方法可限制字串中的單詞數。若有需要，可以指定一個額外的字串來附加到截斷的字串上：

    use Illuminate\Support\Str;
    
    $string = Str::of('Perfectly balanced, as all things should be.')->words(3, ' >>>');
    
    // Perfectly balanced, as >>>
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
<a name="method-dump"></a>

#### `dump()` {.collection-method}

`dump` 函式傾印給定的變數：

    dump($value);
    
    dump($value1, $value2, $value3, ...);
若想在傾印變數後停止執行指令碼，請使用 [`dd`](#method-dd) 函式來代替。

<a name="method-env"></a>

#### `env()` {.collection-method}

`env` 函式可取得[環境變數](/docs/{{version}}/configuration#environment-configuration)的值，或是回傳預設值：

    $env = env('APP_ENV');
    
    $env = env('APP_ENV', 'production');
> [!NOTE]  
> 若在部署流程中執行了 `config:cache` 指令，應確保只有在設定檔中呼叫 `env` 函式。設定檔被快取後，就不會再載入 `.env` 檔了。所有 `env` 函式查詢 `.env` 變數的呼叫都會回傳 `null`。

<a name="method-event"></a>

#### `event()` {.collection-method}

`event` 函式將給定 [Event](/docs/{{version}}/events) ^[分派](Dispatch)給其 Listener：

    event(new UserRegistered($user));
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
<a name="method-optional"></a>

#### `optional()` {.collection-method}

`optional` 函式接受任何的引數，並可讓你存取該物件上的屬性或呼叫該物件上的方法。若給定的物件為 `null`，存取屬性和方法時會回傳 `null` 而不是發生錯誤：

    return optional($user->address)->street;
    
    {!! old('name', optional($user)->name) !!}
`optional` 函式也接受閉包作為其第二個引數。若第一個引數傳入的值不是 null 時會叫用該閉包：

    return optional(User::find($id), function ($user) {
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
        // Attempt 5 times while resting 100ms in between attempts...
    }, 100);
若想自動手動計算每次長時間要暫停的毫秒數，可傳入閉包作為第三個引數給 `retry` 函式：

    return retry(5, function () {
        // ...
    }, function ($attempt) {
        return $attempt * 100;
    });
若只想在特定條件下重試，可傳入一個閉包作為第四個引數給 `retry` 函式：

    return retry(5, function () {
        // ...
    }, 100, function ($exception) {
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

    $user = tap(User::first(), function ($user) {
        $user->name = 'taylor';
    
        $user->save();
    });
若未傳入閉包給 `tap` 函式，則可呼叫任何給定 `$value` 上的方法。無論呼叫的方法回傳什麼值，在此處都會回傳 `$value`。舉例來說，Eloquent `update` 方法一般會回傳整數。不過，若我們可以將 `update` 方法的呼叫串在 `tap` 函式後方，來強制把該方法的回傳值改為 Model 實體：

    $user = tap($user)->update([
        'name' => $name,
        'email' => $email,
    ]);
若要將 `tap` 方法加到類別上，可以將 `Illuminate\Support\Traits\Tappable` Trait 加到類別中。該 Trait 的 `tap` 方法接受一個閉包作為其唯一的引數。物件實體本身會被傳入該閉包中，並由 `tap` 方法回傳：

    return $user->tap(function ($user) {
        //
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

    $callback = function ($value) {
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
<a name="method-view"></a>

#### `view()` {.collection-method}

`view` 函式可取得一個 [View](/docs/{{version}}/views) 實體：

    return view('auth.login');
<a name="method-with"></a>

#### `with()` {.collection-method}

`with` 函式會回傳給定的值。若有傳入閉包作為第二個引數該函式，會執行該閉包並回傳該閉包的回傳值：

    $callback = function ($value) {
        return is_numeric($value) ? $value * 2 : 0;
    };
    
    $result = with(5, $callback);
    
    // 10
    
    $result = with(null, $callback);
    
    // 0
    
    $result = with(5, null);
    
    // 5