---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/17/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:17:00Z'
---

# Blade 樣板

- [簡介](#introduction)
- [顯示資料](#displaying-data)
   - [HTML 實體編碼](#html-entity-encoding)
   - [Blade 與 JavaScript 框架](#blade-and-javascript-frameworks)
- [Blade 指示詞](#blade-directives)
   - [If 陳述式](#if-statements)
   - [Switch 陳述式](#switch-statements)
   - [迴圈](#loops)
   - [迴圈變數](#the-loop-variable)
   - [條件式 Class](#conditional-classes)
   - [Include 子 View](#including-subviews)
   - [`@once` 指示詞](#the-once-directive)
   - [原始 PHP](#raw-php)
   - [註解](#comments)
- [元件](#components)
   - [轉譯元件](#rendering-components)
   - [將資料傳給元件](#passing-data-to-components)
   - [元件屬性](#component-attributes)
   - [保留字](#reserved-keywords)
   - [Slot](#slots)
   - [內嵌元件 View](#inline-component-views)
   - [匿名元件](#anonymous-components)
   - [動態元件](#dynamic-components)
   - [手動註冊元件](#manually-registering-components)
- [製作 Layout](#building-layouts)
   - [使用元件的 Layout](#layouts-using-components)
   - [使用樣板繼承的 Layout](#layouts-using-template-inheritance)
- [表單](#forms)
   - [CSRF 欄位](#csrf-field)
   - [方法欄位](#method-field)
   - [表單驗證錯誤](#validation-errors)
- [Stack](#stacks)
- [插入 Service](#service-injection)
- [擴充 Blade](#extending-blade)
   - [自訂的 Echo 處理常式](#custom-echo-handlers)
   - [自訂 If 陳述式](#custom-if-statements)

<a name="introduction"></a>

## 簡介

Blade 是 Laravel 內建的一個簡單但強大的樣板引擎。與其他 PHP 樣板引擎不同，Blade 不會在樣板中限制你不能使用純 PHP 程式碼。事實上，Blade 樣板會被編譯為純 PHP 程式碼，且在被修改前都會被快取起來。這代表，使用 Blade 並不會給你的網站帶來任何額外的開銷。Blade 樣板檔使用 `.blade.php` 副檔名，且通常放在 `resources/views` 目錄內。

Blade 樣板可以在路由或 Controller 內通過 `view` 全域輔助函式來回傳。當然，就像在 [View](/docs/{{version}}/views) 說明文件內講的一樣，可以使用 `view` 輔助函式的第二個引數來將資料傳給 Blade View：

    Route::get('/', function () {
        return view('greeting', ['name' => 'Finn']);
    });

> {tip} 想要將 Blade 樣板的功能提升到新的境界並輕鬆製作動態使用者界面嗎？請參考看看 [Laravel Livewire](https://laravel-livewire.com)。

<a name="displaying-data"></a>

## 顯示資料

可以通過將變數以大括號包裝起來來顯示傳給 Blade View 的資料。舉例來說，假設有下列路由：

    Route::get('/', function () {
        return view('welcome', ['name' => 'Samantha']);
    });

可以像這樣顯示 `name` 變數的內容：

    Hello, {{ $name }}.

> {tip} Blade 的 `{{ }}` echo 陳述式會自動通過 PHP 的 `htmlspecialchars` 函式來防止 XSS 攻擊。

在 Blade 中不只可以顯示傳進來的變數，還可以 echo 任何 PHP 函式的回傳值。事實上，可以在 Blade 的 echo 陳述式中放入任何的 PHP 程式碼：

    現在的 Unix 時戳是 {{ time() }}。

<a name="html-entity-encoding"></a>

### HTML 實體編碼

預設情況下，Blade (以及 Laravel 的 `e` 輔助函式) 會重複轉譯 HTML 實體 (Double Encode)。若不像被重複轉譯，請在 `AppServiceProvider` 內的 `boot` 方法中呼叫 `Blade::withoutDoubleEncoding` 方法：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Blade;
    use Illuminate\Support\ServiceProvider;
    
    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Bootstrap any application services.
         *
         * @return void
         */
        public function boot()
        {
            Blade::withoutDoubleEncoding();
        }
    }

<a name="displaying-unescaped-data"></a>

#### 顯示未逸出的資料

預設情況下，Blade 的 `{{ }}` 陳述式會自動通過 PHP 的 `htmlspecialchars` 函式來防止 XSS 攻擊。若不想要逸出資料，可以使用下列語法：

    Hello, {!! $name !!}.

> {note} 在輸出使用者提供的資料時，請格外小心。平常在顯示使用者提供的資料時應該要使用經過逸出的雙大括號語法來防止 XSS 攻擊。

<a name="blade-and-javascript-frameworks"></a>

### Blade 與 JavaScript 框架

由於許多 JavaScript 框架都使用「大括號」來判斷給定的運算式應顯示在瀏覽器上，因此可以使用 `@` 符號來告訴 Blade 轉譯引擎不應修改該運算式。如：

    <h1>Laravel</h1>
    
    Hello, @{{ name }}.

在這個例子中，Blade 會將 `@` 符號逸出。而 `{{ name }}` 運算式則不會被 Blade 引擎處理，這樣一來便可讓 JavaScript 框架進行轉譯。

`@` 符號也可用來逸出 Blade 指示詞：

    {{-- Blade 樣板 --}}
    @@if()
    
    <!-- HTML 輸出 -->
    @if()

<a name="rendering-json"></a>

#### 轉譯 JSON

有時候將陣列傳進 View 是為了將其轉譯為 JSON 來初始化 JavaScript 變數。如：

    <script>
        var app = <?php echo json_encode($array); ?>;
    </script>

不過，比起手動呼叫 `json_encode`，我們應使用 `Illuminate\Support\Js::from` 方法指示詞。`from` 方法接受的引數與 PHP 的 `json_encode` 函式相同；不過，`from` 方法會確保即使時在 HTML 引號內，也能正確逸出 JSON。`from` 方法會回傳一個 `JSON.parse` JavaScript 陳述式字串，該陳述式會將給定的物件或陣列轉換為有效的 JavaScript 物件：

    <script>
        var app = {{ Illuminate\Support\Js::from($array) }};
    </script>

最新版本的 Laravel 專案 Skeleton 包含了一個 `Js` Facade。使用這個 Facade 就能方便地在 Blade 樣板中存取這個功能：

    <script>
        var app = {{ Js::from($array) }};
    </script>

> {note} 請只在轉譯現有變數為 JSON 時使用 `Js::from` 方法。Blade 樣板引擎是是基於正規標示式實作的，若將複雜的陳述式傳給指示詞可能會導致未預期的錯誤。

<a name="the-at-verbatim-directive"></a>

#### `@verbatim` 指示詞

若在樣板中顯示了很多的 JavaScript 變數，則可以將 HTML 包裝在 `@verbatim` 指示詞內。這樣一來就不需要在每個 Blade 的 echo 陳述式前面加上前置 `@` 符號：

    @verbatim
        <div class="container">
            Hello, {{ name }}.
        </div>
    @endverbatim

<a name="blade-directives"></a>

## Blade 指示詞

除了繼承樣板與顯示資料外，Blade 也提供了常見 PHP 流程控制結構的方便捷徑，如條件陳述式以及迴圈。這些捷徑提供了一種非常乾淨簡介的方式來處理 PHP 流程控制，同時也保持了與 PHP 的相似性。

<a name="if-statements"></a>

### If 陳述式

可以通過 `@if`, `@elseif`, `@else` 與 `@endif` 指示詞來架構 `if` 陳述式。這些指示詞的功能與其 PHP 對應的部分相同：

    @if (count($records) === 1)
        這裡有 1 筆記錄！
    @elseif (count($records) > 1)
        這裡有多筆記錄！
    @else
        這裡沒有任何記錄！
    @endif

為了方便起見，Blade 也提供了一個 `@unless` 指示詞：

    @unless (Auth::check())
        你還沒有登入。
    @endunless

除了已經討論過的條件指示詞外，也可以通過 `@isset` 與 `@empty` 指示詞來作為其對應 PHP 函式的方便捷徑：

    @isset($records)
        // $records 已定義且不為 null...
    @endisset
    
    @empty($records)
        // $records 為「空」...
    @endempty

<a name="authentication-directives"></a>

#### 認證指示詞

`@auth` 與 `@guest` 指示詞可以用來快速判斷目前的使用者是否[已登入](/docs/{{version}}/authentication)，或是該使用者是否為訪客：

    @auth
        // 使用者已登入...
    @endauth
    
    @guest
        // 使用者未登入...
    @endguest

若有需要，可以在使用 `@auth` 與 `@guest` 指示詞時指定要使用哪個認證 Guard 來做檢查：

    @auth('admin')
        // 使用者已登入...
    @endauth
    
    @guest('admin')
        // 使用者未登入...
    @endguest

<a name="environment-directives"></a>

#### 環境指示詞

可以通過 `@production` 指示詞來判斷網站目前是否在正式環境上執行：

    @production
        // 只在正式環境上顯示的內容...
    @endproduction

或者，可以通過 `@env` 指示詞來判斷網站是否在特定的環境上執行：

    @env('staging')
        // 專案在「staging」環境上執行...
    @endenv
    
    @env(['staging', 'production'])
        // 專案在「staging」或「production」上執行...
    @endenv

<a name="section-directives"></a>

#### 段落指示詞

可以通過 `@hasSection` 指示詞來判斷某個樣板繼承段落是否有內容：

```html
@hasSection('navigation')
    <div class="pull-right">
        @yield('navigation')
    </div>

    <div class="clearfix"></div>
@endif
```

可以通過 `sectionMissing` 指示詞來判斷某個段落是否沒有內容：

```html
@sectionMissing('navigation')
    <div class="pull-right">
        @include('default-navigation')
    </div>
@endif
```

<a name="switch-statements"></a>

### Switch 陳述式

Switch 陳述式可以通過 `@switch`, `@case`, `@break`, `@default` 與 `@endswitch` 指示詞來架構：

    @switch($i)
        @case(1)
            第一個 case...
            @break
    
        @case(2)
            第二個 case...
            @break
    
        @default
            Default case...
    @endswitch

<a name="loops"></a>

### 迴圈

除了條件陳述式外，Blade 也提供了能配合 PHP 的迴圈架構一起使用的一些簡單指示詞。同樣地，這些指示詞的功能都與其對應 PHP 的部分相同：

    @for ($i = 0; $i < 10; $i++)
        目前值為 {{ $i }}
    @endfor
    
    @foreach ($users as $user)
        <p>使用者 {{ $user->id }}</p>
    @endforeach
    
    @forelse ($users as $user)
        <li>{{ $user->name }}</li>
    @empty
        <p>無使用者</p>
    @endforelse
    
    @while (true)
        <p>我會無限循環。</p>
    @endwhile

> {tip} 在使用 `foreach` 迴圈迭代時，可以使用[迴圈變數](#the-loop-variable)來取得有關迴圈的有用資訊，如目前是否在迴圈的第一次或最後一次迭代。

在使用迴圈時，也可以通過 `@continue` 與 `@break` 指示詞來結束迴圈或跳過目前迭代：

    @foreach ($users as $user)
        @if ($user->type == 1)
            @continue
        @endif
    
        <li>{{ $user->name }}</li>
    
        @if ($user->number == 5)
            @break
        @endif
    @endforeach

也可以在指示詞定義中包含 continue 或 break 的條件：

    @foreach ($users as $user)
        @continue($user->type == 1)
    
        <li>{{ $user->name }}</li>
    
        @break($user->number == 5)
    @endforeach

<a name="the-loop-variable"></a>

### 迴圈變數

在迭代 `foreach` 迴圈時，迴圈內提供了 `$loop` 變數可用。這個變數提供了許多實用的資訊，如目前的迴圈索引，以及本次迭代是否為迴圈的第一次或最後一次迭代：

    @foreach ($users as $user)
        @if ($loop->first)
            第一次迭代。
        @endif
    
        @if ($loop->last)
            最後一次迭代。
        @endif
    
        <p>使用者 {{ $user->id }}</p>
    @endforeach

若在巢狀迴圈中，可以通過 `parent` 屬性來存取上層迴圈的 `$loop` 變數：

    @foreach ($users as $user)
        @foreach ($user->posts as $post)
            @if ($loop->parent->first)
                這是上層迴圈的第一次迭代。
            @endif
        @endforeach
    @endforeach

`$loop` 變數也包含了其他各種實用的屬性：

| 屬性 | 說明 |
| --- | --- |
| `$loop->index` | 目前迴圈迭代的索引 (從 0 開始)。 |
| `$loop->iteration` | 目前的迴圈迭代 (從 1 開始)。 |
| `$loop->remaining` | 迴圈中剩餘的迭代數。 |
| `$loop->count` | 迭代中陣列內的總項目數。 |
| `$loop->first` | 目前是否為迴圈的第一次迭代。 |
| `$loop->last` | 目前是否為迴圈的最後一次迭代。 |
| `$loop->even` | 目前是否為迴圈的偶數次迭代。 |
| `$loop->odd` | 目前是否為迴圈的奇數次迭代。 |
| `$loop->depth` | 目前迴圈的巢狀深度等級。 |
| `$loop->parent` | 若在巢狀迴圈內，即代表上層的迴圈變數。 |

<a name="conditional-classes"></a>

### 有條件的 Class

`@class` 指示詞可以有條件地編譯 CSS class 字串。`@class` 指示詞接受一組包含 class 的陣列，其中，陣列的索引鍵代表欲新增的 class，陣列值則是一個布林運算式。若陣列的元素有數字索引鍵，則該元素一定會被加到轉譯後的 Class 列表上：

    @php
        $isActive = false;
        $hasError = true;
    @endphp
    
    <span @class([
        'p-4',
        'font-bold' => $isActive,
        'text-gray-500' => ! $isActive,
        'bg-red' => $hasError,
    ])></span>
    
    <span class="p-4 text-gray-500 bg-red"></span>

<a name="including-subviews"></a>

### Include 子 View

> {tip} 雖然可以使用 `@include` 指示詞，但 Blade 的[元件](#components)提供了類似的功能，但比起 `@include` 指示詞來說有更多的優勢，如資料與屬性綁定。

Blade 的 `@include` 指示詞可用來在 Blade View 中包含另一個 View。所有上層 View 中可用的變數在 Include 的 View 當中都保持可用：

```html
<div>
    @include('shared.errors')

    <form>
        <!-- 表單內容 -->
    </form>
</div>
```

雖然 Include 的 View 會繼承其上層 View 中所有的資料，但也可以將要在包含的 View 中使用的資料作為陣列傳入：

    @include('view.name', ['status' => 'complete'])

若嘗試 `@include` 一個不存在的 View，Laravel 會擲回錯誤。若想 Include 一個可能不存在的 View，應使用 `@includeIf` 指示詞：

    @includeIf('view.name', ['status' => 'complete'])

若想在某個布林表達式取值為 `true` 或 `false` 的時候 `@include` 一個 View，則可以使用 `@includeWhen` 與 `@includeUnless` 指示詞：

    @includeWhen($boolean, 'view.name', ['status' => 'complete'])
    
    @includeUnless($boolean, 'view.name', ['status' => 'complete'])

若要在某個包含了一些 View 的陣列中 Include 第一個存在的 View，可以使用 `includeFirst` 指示詞：

    @includeFirst(['custom.admin', 'admin'], ['status' => 'complete'])

> {note} 應避免在 Blade View 中使用 `__DIR__` 與 `__FILE__` 常數，因為這些常數會參照到經過快取與編譯過的 View。

<a name="rendering-views-for-collections"></a>

#### 為 Collection 轉譯 View

可以通過 Blade 的 `@each` 指示詞來將迴圈與 Include 組合成一行：

    @each('view.name', $jobs, 'job')

`@each` 指示詞的第一個引數是用來轉譯陣列或 Collection 中各個元素的 View。第二個引數則為要迭代的陣列或 Collection，而第三個引數則為要在 View 中被指派目前迭代的變數名稱。因此，舉例來說，若要迭代一個 `jobs` 陣列，通常我們會想在 View 中通過 `job` 變數來存取各個 Job。目前迭代的陣列索引鍵可在 View 中通過 `key` 存取。

也可以傳入第四個引數給 `@each` 指示詞。這個引數用來判斷當給定陣列為空時要被轉譯的 View。

    @each('view.name', $jobs, 'job', 'view.empty')

> {note} 通過 `@each` 所轉譯的 View 不會繼承其上層 View 的變數。若子 View 有需要這些變數，應使用 `@foreach` 與 `@include` 指示詞來代替。

<a name="the-once-directive"></a>

### `@once` 指示詞

`@once` 指示詞可以用來定義讓某部分的樣板在每個轉譯週期內只被轉譯一次。通常適用於想讓某部分的 JavaScript 的通過[堆疊](#stacks) Push 的頁面頭部時。舉例來說，若想在迴圈中轉譯某個給定的[元素](#components)，可能會只想在第一次轉譯的時候將 JavaScript Push 到頭部：

    @once
        @push('scripts')
            <script>
                // 自訂 JavaScript...
            </script>
        @endpush
    @endonce

<a name="raw-php"></a>

### 原始 PHP

在某些情況下，可能需要將 PHP 程式碼嵌入到 View 中。可以使用 Blade 的 `@php` 指示詞來在樣板中執行某一區塊的純 PHP：

    @php
        $counter = 1;
    @endphp

<a name="comments"></a>

### 註解

在 Blade 中，我們也可以在 View 中定義註解。不過，與 HTML 註解不同，Blade 的註解不會包含在網站所回傳的 HTML 中：

    {{-- 這條註解將不會出現在轉譯完的 HTML 中 --}}

<a name="components"></a>

## 元件

元件與 Slot 提供了與 Section, Layout 與 Include 類似的功能。不過，有些人可能會覺得元件跟 Slot 比較好懂。撰寫元件有兩種方法：一種是基於類別的元件，另一種則是匿名元件。

若要建立基於類別的元件，可以使用 `make:component` Artisan 指令。為了解釋如何使用元件，我們將會建立一個簡單的 `Alert` 元件。`make:component` 指令會將元件放在 `app/View/Components` 目錄中：

    php artisan make:component Alert

`make:component` 指令也會為元件建立一個 View 樣板。這個樣板會被放在 `resources/views/components` 目錄內。當在為專案撰寫元件時，元件會在 `app/View/Components` 與 `resources/views/components` 目錄中被 Auto Discover (自動偵測)，因此不需要進一步註冊元件。

也可以在子資料夾內建立元件：

    php artisan make:component Forms/Input

上述指令會在 `app/View/Components/Forms` 目錄內建立一個 `Input` 元件，而 View 會被放在 `resources/views/components/forms` 目錄內。

<a name="manually-registering-package-components"></a>

#### 手動註冊套件元件

在為專案撰寫元件時，元件會在 `app/View/Components` 與 `resources/views/components` 目錄下被 Auto Discover (自動偵測)。

不過，若想製作使用 Blade 元件的套件，則需要手動註冊元件類別與其 HTML 標籤別名。通常，應在套件的 Service Provider 內的 `boot` 方法中註冊你的元件：

    use Illuminate\Support\Facades\Blade;
    
    /**
     * Bootstrap your package's services.
     */
    public function boot()
    {
        Blade::component('package-alert', Alert::class);
    }

註冊好元件後，便可使用其標籤別名來轉譯：

    <x-package-alert/>

或者，也可以使用 `componentNamespace` 方法來依照慣例自動載入元件類別。舉例來說，`Nightshade` 套件可能包含了放在 `Package\Views\Components` Namespace 下的 `Calendar` 與 `ColorPicker` 元件：

    use Illuminate\Support\Facades\Blade;
    
    /**
     * Bootstrap your package's services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
    }

這樣依賴可以讓套件元件通過其 Vendor Namespace 來使用 `package-name::` 語法：

    <x-nightshade::calendar />
    <x-nightshade::color-picker />

Blade 會通過將元件名稱轉為 Pascal 命名法 (pascal-case) 來自動偵測與這個元件關連的類別。也可以使用「點」語法來支援子目錄。

<a name="rendering-components"></a>

### 轉譯元件

若要顯示元件，可以在 Blade 樣板中使用 Blade 元件的標籤。Blade 元件標籤以 `x-` 開頭，並接上以 Kebab Case 命名法命名的元素類別：

    <x-alert/>
    
    <x-user-profile/>

若元件類別在 `app/View/Components` 目錄中嵌套多層，可以使用 `.` 字元來標示巢狀目錄。舉例來說，假設有個位於 `app/View/Components/Inputs/Button.php` 的元件，我們可以像這樣轉譯該元件：

    <x-inputs.button/>

<a name="passing-data-to-components"></a>

### 將資料傳給元件

可以使用 HTML 屬性來將資料傳給 Blade 元素。硬式編碼或原生值可以使用簡單的 HTML 屬性字串來傳給元素。PHP 表達式與變數應使用以 `:` 字元作為前綴的屬性來傳遞：

    <x-alert type="error" :message="$message"/>

可以在元件的類別建構函式中定義元件所需的資料。元件中所有 Public 的屬性都會自動在元件的 View 中可用。不需要在元件的 `render` 方法中將這些資料傳給 View：

    <?php
    
    namespace App\View\Components;
    
    use Illuminate\View\Component;
    
    class Alert extends Component
    {
        /**
         * The alert type.
         *
         * @var string
         */
        public $type;
    
        /**
         * The alert message.
         *
         * @var string
         */
        public $message;
    
        /**
         * Create the component instance.
         *
         * @param  string  $type
         * @param  string  $message
         * @return void
         */
        public function __construct($type, $message)
        {
            $this->type = $type;
            $this->message = $message;
        }
    
        /**
         * Get the view / contents that represent the component.
         *
         * @return \Illuminate\View\View|\Closure|string
         */
        public function render()
        {
            return view('components.alert');
        }
    }

元件在進行轉譯時，可以通過 Echo 變數名稱來顯示元件的公用變數：

```html
<div class="alert alert-{{ $type }}">
    {{ $message }}
</div>
```

<a name="casing"></a>

#### 大小寫

元件建構函式中的引數應以 `camelCase` 來指定，而在 HTML 屬性中參照其引數名稱時應使用 `kebab-case`。舉例來說，假設有下列元件建構函式：

    /**
     * Create the component instance.
     *
     * @param  string  $alertType
     * @return void
     */
    public function __construct($alertType)
    {
        $this->alertType = $alertType;
    }

可以像這樣將 `$alertType` 引數提供給元件：

    <x-alert alert-type="danger" />

<a name="escaping-attribute-rendering"></a>

#### 逸出屬性轉譯

由於有些像 Alpine.js 的 JavaScript 框架也使用分號前綴的屬性，因此可以在 Blade 中使用雙分號（`::`）來提示 Blade 其屬性並非 PHP 運算式。舉例來說，假設有下列元件：

    <x-button ::class="{ danger: isDeleting }">
        送出
    </x-button>

Blade 會轉譯為下列 HTML：

    <button :class="{ danger: isDeleting }">
        送出
    </button>

<a name="component-methods"></a>

#### 元件方法

除了公用變數可以在元件樣板中使用以外，元件內的任何公用方法也可以被叫用。舉例來說，假設某個有 `isSelected` 方法的元件：

    /**
     * Determine if the given option is the currently selected option.
     *
     * @param  string  $option
     * @return bool
     */
    public function isSelected($option)
    {
        return $option === $this->selected;
    }

可以在元件樣板中通過叫用與方法名稱相同的變數來執行此方法：

    <option {{ $isSelected($value) ? 'selected="selected"' : '' }} value="{{ $value }}">
        {{ $label }}
    </option>

<a name="using-attributes-slots-within-component-class"></a>

#### 在元件類別中存取屬性與 Slot

Blade 元件也允許在類別的 `render` 方法中存取元素名稱、屬性、以及 Slot。不過，若要存取這些資料，就必須在元件 `render` 方法中回傳一個閉包。這個閉包會收到 `$data` 陣列作為其唯一的引數。該陣列將包含多個提供有關該元件資訊的元素：

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return function (array $data) {
            // $data['componentName'];
            // $data['attributes'];
            // $data['slot'];
    
            return '<div>Components content</div>';
        };
    }

`componentName` 與 HTML Tag 的 `x-` 前綴之後所使用的名稱相同。因此 `<x-alert />` 的 `componentName` 會是 `alert`。`attributes` 元素會包含出現在 HTML 標籤上的所有屬性。`slot` 元素是一個 `Illuminate\Support\HtmlString` 實體，其中包含了該元件的 Slot 內容。

這個閉包應回傳字串。若該閉包回傳的字串為一個現有的 View，則會轉譯該 View。否則，回傳的字串將被轉換為內嵌的 Blade View。

<a name="additional-dependencies"></a>

#### 額外的相依項

若元件需要從 Laravel 的 [Service Container](/docs/{{version}}/container) 內取得其他相依性，則可以將這些相依性列在所有元素的資料屬性前，Container 會自動插入這些相依性項目：

    use App\Services\AlertCreator
    
    /**
     * Create the component instance.
     *
     * @param  \App\Services\AlertCreator  $creator
     * @param  string  $type
     * @param  string  $message
     * @return void
     */
    public function __construct(AlertCreator $creator, $type, $message)
    {
        $this->creator = $creator;
        $this->type = $type;
        $this->message = $message;
    }

<a name="hiding-attributes-and-methods"></a>

#### 隱藏屬性與方法

若想防止一些公用方法或屬性被作為變數暴露到元件的樣板中，可以將這些項目加到元件的 `$except` 陣列屬性上：

    <?php
    
    namespace App\View\Components;
    
    use Illuminate\View\Component;
    
    class Alert extends Component
    {
        /**
         * The alert type.
         *
         * @var string
         */
        public $type;
    
        /**
         * The properties / methods that should not be exposed to the component template.
         *
         * @var array
         */
        protected $except = ['type'];
    }

<a name="component-attributes"></a>

### 元件屬性

我們已經看到了如何將資料屬性傳遞到元件內。然而，有時候可能會像指定不是元件運作所需的一些額外 HTML 屬性，如 `class`。通常來說，我們會像將這些額外的屬性向下傳遞到元件樣板中的根元素。舉例來說，假設我們想要像這樣轉譯一個 `alert` 元件：

    <x-alert type="error" :message="$message" class="mt-4"/>

所有不在元件建構函式內的屬性都會被加到元件的「屬性包 (Attribute Bag)」內。這個屬性包會自動通過一個 `$attributes` 變數在元件內可用。可以通過 echo 這個變數來讓所有的屬性在元件內被轉譯：

    <div {{ $attributes }}>
        <!-- 元件內容 -->
    </div>

> {note} 目前不支援在元件標籤內使用如 `@env` 的指示詞。舉例來說，`<x-alert :live="@env('production')"/>` 將不會被編譯。

<a name="default-merged-attributes"></a>

#### 預設與合併屬性

有時候我們可能需要為屬性指定預設值，或是將額外的值合併到某些元件的屬性內。為此，可以使用屬性包的 `merge` 方法。這個方法特別適合用在如定義一系列永遠會被套用到元件上的預設 CSS：

    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>

若我們假設這個元件會被這樣使用：

    <x-alert type="error" :message="$message" class="mb-4"/>

則屬性最後經過轉譯的 HTML 會長這樣：

```html
<div class="alert alert-error mb-4">
    <!-- $message 變數的內容 -->
</div>
```

<a name="conditionally-merge-classes"></a>

#### 有條件地合併 Class

有時候我們可能會依照給定條件式的結果是否為 `true` 來決定要不要合併 Class。可以通過 `class` 方法來達成，該方法接受一段含有 Class 的陣列。陣列的索引鍵包含了要新增的 Class，而陣列的值則為布林運算式。若陣列的元素有數字索引鍵，則該元素一定會被加到轉譯後的 Class 列表上：

    <div {{ $attributes->class(['p-4', 'bg-red' => $hasError]) }}>
        {{ $message }}
    </div>

若像將其他屬性合併到元件上，可以將 `merge` 方法串接到 `class` 方法後：

    <button {{ $attributes->class(['p-4'])->merge(['type' => 'button']) }}>
        {{ $slot }}
    </button>

> {tip} 若不想讓要套用條件式編譯 Class 的 HTML 收到經過合併的屬性，請使用 [`@class` 指示詞](#conditional-classes)。

<a name="non-class-attribute-merging"></a>

#### 非 Class 的屬性合併

在合併 `class` 以外的屬性值時，提供給 `merge` 方法的值會被當作是屬性的「預設」值。不過，與 `class` 屬性不同，這些屬性不會跟插入的屬性值合併，而是會被複寫。舉例來說，一個 `button` 元件的實作可能會長這樣：

    <button {{ $attributes->merge(['type' => 'button']) }}>
        {{ $slot }}
    </button>

若要以自訂 `type` 來轉譯按鈕元素，可以在使用元素時指定。若未指定 Type，則會使用 `button` Type：

    <x-button type="submit">
        送出
    </x-button>

在這個範例中，`button` 元件轉譯後的 HTML 會是：

    <button type="submit">
        送出
    </button>

若像要讓 `class` 之外的屬性也能將其預設值與插入的值被串在一起，則可以使用 `prepends` 方法。在此例子中，`data-controller` 屬性將總是以 `profile-controller` 開頭，而任何額外插入的 `data-controller` 值都將被放在這個預設值之後：

    <div {{ $attributes->merge(['data-controller' => $attributes->prepends('profile-controller')]) }}>
        {{ $slot }}
    </div>

<a name="filtering-attributes"></a>

#### 取得與過濾屬性

可以使用 `filter` 方法來過濾屬性。該方法接受一個閉包。若希望在屬性包內保留該屬性，則應在該閉包內回傳 `true`：

    {{ $attributes->filter(fn ($value, $key) => $key == 'foo') }}

為了方便起見，可以使用 `whereStartsWith` 方法來取得所有索引鍵以給定字串開頭的屬性：

    {{ $attributes->whereStartsWith('wire:model') }}

相對的，可以使用 `whereDoesntStartWith` 方法來排除所有索引鍵不以給定字串開頭的屬性：

    {{ $attributes->whereDoesntStartWith('wire:model') }}

使用 `first` 方法，就可以轉譯給定屬性包中的第一個屬性：

    {{ $attributes->whereStartsWith('wire:model')->first() }}

若像檢查某個屬性是否有出現在元件內，可以使用 `has` 方法。這個方法接受一個屬性名稱作為其唯一的一個引數，並且會回傳一個布林值，來代表該屬性是否有出現：

    @if ($attributes->has('class'))
        <div>有該 class 屬性</div>
    @endif

可以通過 `get` 方法來取得某個特定的屬性值：

    {{ $attributes->get('class') }}

<a name="reserved-keywords"></a>

### 保留字

預設情況下，Blade 中保留了一些關鍵字來作為內部使用，以用於轉譯元件。下列關鍵字將無法在元件內被定義為公用屬性或屬性名稱：

<div class="content-list" markdown="1">

- `data`
- `render`
- `resolveView`
- `shouldRender`
- `view`
- `withAttributes`
- `withName`

</div>

<a name="slots"></a>

### Slot

我們常常會通過「Slot」來將額外的內容傳到元件內。元件的 Slot 可以通過 Echo `$slot` 變數來進行轉譯。為了進一步探討這個概念，我們來想想有個長得像這樣的 `alert` 元件：

```html
<!-- /resources/views/components/alert.blade.php -->

<div class="alert alert-danger">
    {{ $slot }}
</div>
```

我們可以通過將內容插入到元件內來把內容傳給 `slot`：

```html
<x-alert>
    <strong>Whoops!</strong> Something went wrong!
</x-alert>
```

有時候，元件可能需要在元件中不同位置來轉譯多個不同的 Slot。我們來修改一下 alert 元件，讓這個元件能允許插入「title」Slot：

```html
<!-- /resources/views/components/alert.blade.php -->

<span class="alert-title">{{ $title }}</span>

<div class="alert alert-danger">
    {{ $slot }}
</div>
```

可以通過 `x-slot` 標籤來定義帶名稱 Slot 的內容。任何沒有明顯放在 `x-slot` 標籤內的內容都會被傳到元素的 `$slot` 變數內：

```html
<x-alert>
    <x-slot name="title">
        Server Error
    </x-slot>

    <strong>Whoops!</strong> Something went wrong!
</x-alert>
```

<a name="scoped-slots"></a>

#### 限定範圍的 Slot

若讀者使用過如 Vue 之類的 JavaScript 框架，可能有看過「限定區域的 Slot (Scoped Slot)」。這種 Slot 可以讓我們能在 Slot 中從元件內存取資料或方法。在 Laravel 中，可以在元素內定義公用方法或屬性，然後在 Slot 內通過 `$component` 變數來存取元件，就可以達到類似的行為。在這個例子裡，我們會假設 `x-alert` 元件中有一個在元件類別內定義的公用 `formatAlert` 方法：

```html
<x-alert>
    <x-slot name="title">
        {{ $component->formatAlert('Server Error') }}
    </x-slot>

    <strong>Whoops!</strong> Something went wrong!
</x-alert>
```

<a name="slot-attributes"></a>

#### Slot 屬性

與 Blade 元件類似，我們可以將一些如 CSS Class 名稱等額外的[屬性](#component-attributes)指派給 Slot：

```html
<x-card class="shadow-sm">
    <x-slot name="heading" class="font-bold">
        Heading
    </x-slot>

    Content

    <x-slot name="footer" class="text-sm">
        Footer
    </x-slot>
</x-card>
```

若要與 Slot 屬性互動，可以存取 Slot 變數的 `attributes` 屬性。更多有關與屬性互動的資訊，請參考關於[元件屬性](#component-attributes)的說明文件：

```php
@props([
    'heading',
    'footer',
])

<div {{ $attributes->class(['border']) }}>
    <h1 {{ $heading->attributes->class(['text-lg']) }}>
        {{ $heading }}
    </h1>

    {{ $slot }}

    <footer {{ $footer->attributes->class(['text-gray-700']) }}>
        {{ $footer }}
    </footer>
</div>
```

<a name="inline-component-views"></a>

### 內嵌元件 View

對於非常小的元件，要同時處理元件類別與元件樣板感覺非常麻煩。為此，可以直接在 `render` 方法內回傳元件的標記：

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return <<<'blade'
            <div class="alert alert-danger">
                {{ $slot }}
            </div>
        blade;
    }

<a name="generating-inline-view-components"></a>

#### 產生內嵌 View 元件

若要建立會轉譯內嵌 View 的元件，可以在執行 `make:component` 指令時加上 `inline` 選項：

    php artisan make:component Alert --inline

<a name="anonymous-components"></a>

### 匿名元件

與內嵌元件類似，匿名元件提供了一種能在單一檔案內管理元件的機制。不過，匿名元件使用單一 View 檔案，且沒有相關聯的類別。若要定義匿名元件，只需要將 Blade 樣板放在 `resources/views/components` 目錄內即可。舉例來說，假設有在 `resources/views/components/alert.blade.php` 內定義個了一個元件，則可以輕鬆地像這樣轉譯該元件：

    <x-alert/>

可以使用 `.` 字元來表示該元件是嵌套放在 `components` 目錄下的。舉例來說，假設某個元件是定義在 `resources/views/components/inputs/button.blade.php`，則可以像這樣對其進行轉譯：

    <x-inputs.button/>

<a name="anonymous-index-components"></a>

#### 匿名的 Index 元件

有時候，若我們做了一個由多個 Blade 樣板組成的元件，我們可能會想將給定的元件樣板放在單一目錄內群組化起來。舉例來說，若有個「accordion」元件，並有下列目錄結構：

```none
/resources/views/components/accordion.blade.php
/resources/views/components/accordion/item.blade.php
```

使用這個目錄結構能讓我們將 accordion 元件與其元素依照下列這種方式轉譯：

```html
<x-accordion>
    <x-accordion.item>
        ...
    </x-accordion.item>
</x-accordion>
```

不過，若要使用 `x-accordion` 來轉譯 accordion 元件，則我們必須強制將「index」的 accordion 元件樣板放在 `resources/views/components` 目錄，而不是與其他 accordion 相關的樣板一起放在 `accordion` 目錄下。

好佳在，Blade 能讓我們在元件的樣板目錄內放置一個 `index.blade.php` 檔案。當該元件內有 `index.blade.php` 樣板時，Laravel 會將其轉移為元件的「根節點」。因此，我們可以使用與上方範例相同的 Blade 語法。不過，我們會需要將目錄結構改成如下：

```none
/resources/views/components/accordion/index.blade.php
/resources/views/components/accordion/item.blade.php
```

<a name="data-properties-attributes"></a>

#### 資料屬性

由於匿名元件沒有相關聯的類別，因此你可能像知道該如何判斷那些資料應作為變數傳給元件，而那些屬性應放在元件的 [Attribute Bag](#component-attributes) 內。

可以通過在元件的 Blade 樣板最上方使用 `@props` 指示詞來指定那個屬性應被當作資料變數使用。在元件中，所有其他的屬性都會通過元件的屬性包內可用。若像為某個資料變數設定預設值，則可以指定變數的名稱作為陣列索引鍵，並以預設值作為陣列值：

    <!-- /resources/views/components/alert.blade.php -->
    
    @props(['type' => 'info', 'message'])
    
    <div {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
        {{ $message }}
    </div>

以上方的元件定義為例，我們可能會像這樣來轉譯元件：

    <x-alert type="error" :message="$message" class="mb-4"/>

<a name="accessing-parent-data"></a>

#### 存取上層資料

有時候，我們會想從子元件中存取上層元件的資料。在這種情況下，可以使用 `@aware` 指示詞。舉例來說，假設我們正在建立一個有上層元件 `<x-menu>` 與子元件 `<x-menu.item>` 的複雜選單元件：

    <x-menu color="purple">
        <x-menu.item>...</x-menu.item>
        <x-menu.item>...</x-menu.item>
    </x-menu>

`<x-menu>` 元件可能會有像這樣的實作：

    <!-- /resources/views/components/menu/index.blade.php -->
    
    @props(['color' => 'gray'])
    
    <ul {{ $attributes->merge(['class' => 'bg-'.$color.'-200']) }}>
        {{ $slot }}
    </ul>

由於 `color` 屬性只傳給了上層元件 (`<x-menu>`)，因此該屬性在 `<x-menu.item>` 中將無法存取。不過，若我們使用了 `@aware` 指示詞，就可以讓該屬性也在 `<x-menu.item>` 內可用：

    <!-- /resources/views/components/menu/item.blade.php -->
    
    @aware(['color' => 'gray'])
    
    <li {{ $attributes->merge(['class' => 'text-'.$color.'-800']) }}>
        {{ $slot }}
    </li>

<a name="dynamic-components"></a>

### 動態元件

有時候我們可能會需要轉譯元件，但在執行階段前並不知道要轉譯哪個元件。這種情況，可以使用 Laravel 的內建「dynamic-component」動態元件來依照執行階段的值或變數進行轉譯：

    <x-dynamic-component :component="$componentName" class="mt-4" />

<a name="manually-registering-components"></a>

### 手動註冊元件

> {note} 下列有關手動註冊元件的說明文件主要適用於撰寫包含 View 元件的 Laravel 套件的套件作者。若你並不撰寫套件，則這部分的元件說明文件可能跟你比較沒關係。

在為專案撰寫元件時，元件會在 `app/View/Components` 與 `resources/views/components` 目錄下被 Auto Discover (自動偵測)。

不過，若想製作使用 Blade 元件的套件或將元件放在不符合慣例的目錄內，則需要手動註冊元件類別與其 HTML 標籤別名，以讓 Laravel 知道要在哪裡尋找元件。通常，應在套件的 Service Provider 內的 `boot` 方法中註冊你的元件：

    use Illuminate\Support\Facades\Blade;
    use VendorPackage\View\Components\AlertComponent;
    
    /**
     * Bootstrap your package's services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::component('package-alert', AlertComponent::class);
    }

註冊好元件後，便可使用其標籤別名來轉譯：

    <x-package-alert/>

#### 自動載入套件元件

或者，也可以使用 `componentNamespace` 方法來依照慣例自動載入元件類別。舉例來說，`Nightshade` 套件可能包含了放在 `Package\Views\Components` Namespace 下的 `Calendar` 與 `ColorPicker` 元件：

    use Illuminate\Support\Facades\Blade;
    
    /**
     * Bootstrap your package's services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::componentNamespace('Nightshade\\Views\\Components', 'nightshade');
    }

這樣依賴可以讓套件元件通過其 Vendor Namespace 來使用 `package-name::` 語法：

    <x-nightshade::calendar />
    <x-nightshade::color-picker />

Blade 會通過將元件名稱轉為 Pascal 命名法 (pascal-case) 來自動偵測與這個元件關連的類別。也可以使用「點」語法來支援子目錄。

<a name="building-layouts"></a>

## 製作 Layout

<a name="layouts-using-components"></a>

### 使用元件的 Layout

大多數網站都會在許多頁面間共用同一個相同 Layout (版面配置)。如果我們每建立一個 HTML 都要重寫一整個 Layout，就會變得非常麻煩又難維護。好佳在，我們可以非常輕鬆地把這個 Layout 定義為一個 [Blade 元件](#components)，並在網站中重複利用。

<a name="defining-the-layout-component"></a>

#### 定義 Layout 元件

舉例來說，假設我們正在製作一個「代辦事項」App。我們可能會像這樣定義一個 `layout` 元件：

```html
<!-- resources/views/components/layout.blade.php -->

<html>
    <head>
        <title>{{ $title ?? 'Todo Manager' }}</title>
    </head>
    <body>
        <h1>代辦事項</h1>
        <hr/>
        {{ $slot }}
    </body>
</html>
```

<a name="applying-the-layout-component"></a>

#### 套用 Layout 元件

定義好 `layout` 元件後，我們就可以建立使用該元件的 Blade 樣板。舉例來說，我們可以定義一個用來顯示任務清單的一個簡單的 View：

```html
<!-- resources/views/tasks.blade.php -->

<x-layout>
    @foreach ($tasks as $task)
        {{ $task }}
    @endforeach
</x-layout>
```

請記得，在 `layout` 元件，被插入的內容會被提供給預設的 `$slot` 變數。讀者可能已經注意到，我們的 `layout` 會在有提供 `$title` Slot 時對其進行處理，並在未提供 `$title` 時顯示預設標題。我們也可以通過利用在[元件說明文件](#components)中討論過的方法一樣，在任務清單 View 內通過標準的 Slot 語法來插入自訂標題。

```html
<!-- resources/views/tasks.blade.php -->

<x-layout>
    <x-slot name="title">
        自訂標題
    </x-slot>

    @foreach ($tasks as $task)
        {{ $task }}
    @endforeach
</x-layout>
```

現在我們已經定義好了畫面配置以及任務清單 View 了，接著只需要在路由內回傳 `task` View：

    use App\Models\Task;
    
    Route::get('/tasks', function () {
        return view('tasks', ['tasks' => Task::all()]);
    });

<a name="layouts-using-template-inheritance"></a>

### 使用樣板繼承的版面配置

<a name="defining-a-layout"></a>

#### 定義版面配置

也可以通過「樣板繼承」來製作 Layout。在[元件](#components)功能問世前，我們通常都是使用這個方法來製作網站。

要開始使用樣板繼承的 Layout，我們先來看一個簡單的例子。首先，我們先來看看一個 Layout 定義。由於大多數的 Web App 都會在多個不同的頁面上共用同一個 Layout，因此將這個 Layout 定義為單一 Blade View 比較方便：

```html
<!-- resources/views/layouts/app.blade.php -->

<html>
    <head>
        <title>App Name - @yield('title')</title>
    </head>
    <body>
        @section('sidebar')
            This is the master sidebar.
        @show

        <div class="container">
            @yield('content')
        </div>
    </body>
</html>
```

就像我們可以看到的一樣，這個檔案包含了普通的 HTML 標記。不過，可以注意到 `@section` 與 `@yield` 指示詞。`@section` 指示詞與其名稱代表的意思一樣，是定義一個內容的段落。而 `@yield` 指示詞則用來將給定段落的內容顯示出來。

現在，我們已經定義好要在網站中使用的 Layout 了。讓我們來定義繼承該 Layout 的子頁面。

<a name="extending-a-layout"></a>

#### 繼承 Layout

在定義子 View 時，可以使用 `@extends` Blade 指示詞來指定要「繼承」哪個 Layout。繼承了 Blade 版面配置的 View 可以使用 `@section` 指示詞來將內容插入到 Layout 的段落中。請記得，就像在剛才範例中看到的一樣，這些段落的內容會在 Layout 中通過 `@yield` 來顯示：

```html
<!-- resources/views/child.blade.php -->

@extends('layouts.app')

@section('title', 'Page Title')

@section('sidebar')
    @@parent

    <p>This is appended to the master sidebar.</p>
@endsection

@section('content')
    <p>This is my body content.</p>
@endsection
```

In this example, the `sidebar` section is utilizing the `@@parent` directive to append (rather than overwriting) content to the layout's sidebar. The `@@parent` directive will be replaced by the content of the layout when the view is rendered.

> {tip} 相較於前一個例子，`sidebar` 段落是以 `@endsection` 結束的，而不是 `@show`。`@endsection` 指示詞只會定義一個段落，而 `@show` 則會定義並 **馬上 Yield** 該段落。

`@yield` 指示詞也接受一個預設值作為其第二個參數。這個值會在要 Yield 的段落未定義時被轉譯：

    @yield('content', '預設內容')

<a name="forms"></a>

## 表單

<a name="csrf-field"></a>

### CSRF 欄位

只要是在專案中內定義 HTML 表單，不管是什麼時候，我們都應該要在表單內包含一個隱藏的 CSRF 權杖欄位來讓 [CSRF 保護](/docs/{{version}}/csrf) Middleware 能認證請求。我們可以使用 `@csrf` Blade 指示詞來產生這個權杖欄位：

```html
<form method="POST" action="/profile">
    @csrf

    ...
</form>
```

<a name="method-field"></a>

### 方法欄位

由於 HTML 表單沒辦法建立 `PUT`, `PATCH` 與 `DELETE` 請求，因此會需要加上一個隱藏的 `_method` 欄位來假裝成這些 HTTP 動詞。`@method` Blade 指示詞可以幫你建立這個欄位：

```html
<form action="/foo/bar" method="POST">
    @method('PUT')

    ...
</form>
```

<a name="validation-errors"></a>

### 認證錯誤

可以使用 `@error` 指示詞來快速檢查給定的屬性是否有[驗證錯誤訊息](/docs/{{version}}/validation#quick-displaying-the-validation-errors)。在 `@error` 指示詞內，可以 echo `$message` 變數來顯示錯誤訊息：

```html
<!-- /resources/views/post/create.blade.php -->

<label for="title">貼文標題</label>

<input id="title" type="text" class="@error('title') is-invalid @enderror">

@error('title')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
```

由於 `@error` 指示詞匯被編譯為「if」陳述式，因此我們可以使用 `@else` 指示詞來在屬性沒有錯誤時轉譯特定內容：

```html
<!-- /resources/views/auth.blade.php -->

<label for="email">Email address</label>

<input id="email" type="email" class="@error('email') is-invalid @else is-valid @enderror">
```

可以將[特定錯誤包的名稱](/docs/{{version}}/validation#named-error-bags)傳送給 `@error` 指示詞的第二個參數來在包含多個表單的頁面上取得驗證錯誤訊息：

```html
<!-- /resources/views/auth.blade.php -->

<label for="email">Email 位址</label>

<input id="email" type="email" class="@error('email', 'login') is-invalid @enderror">

@error('email', 'login')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
```

<a name="stacks"></a>

## 堆疊

在 Blade 中可以 Push 到帶名稱的堆疊。這個堆疊可以在另一個 View 或 Layout 內進行轉譯。堆疊特別適合用來指定任何子 View 所需要的 JavaScript 函式庫：

```html
@push('scripts')
    <script src="/example.js"></script>
@endpush
```

一個堆疊可以按照需求 Push 多次。要將完成的堆疊內容轉譯出來，只需要將堆疊名稱傳給 `@stack` 指示詞：

```html
<head>
    <!-- Head 內容 -->

    @stack('scripts')
</head>
```

若想將內容加到堆疊的最前面，可以使用 `@prepend` 指示詞：

```html
@push('scripts')
    這個會是第二個…
@endpush

// 之後…

@prepend('scripts')
    這個會是第一個…
@endprepend
```

<a name="service-injection"></a>

## Service Injection

`@inject` 指示詞可以用來從 Laravel 的 [Service Container](/docs/{{version}}/container) 中取得服務。傳給 `@inject` 的第一個引數是要放置服務的變數名稱，而第二個引數則是要解析服務的類別或介面名稱：

```html
@inject('metrics', 'App\Services\MetricsService')

<div>
    月收入：{{ $metrics->monthlyRevenue() }}.
</div>
```

<a name="extending-blade"></a>

## 擴充 Blade

Blade 中可以通過 `directive` 方法來自訂指示詞。當 Blade 編譯器遇到自訂指示詞的時候，編譯器會呼叫所提供的回呼，並將將該指示詞內包含的運算式提供給該回呼。

下列範例建立了一個 `@datetime($var)` 指示詞，用來將給定的 `$var` 格式化，而 `$var` 應為 `DateTime` 的實體：

    <?php
    
    namespace App\Providers;
    
    use Illuminate\Support\Facades\Blade;
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
            Blade::directive('datetime', function ($expression) {
                return "<?php echo ($expression)->format('m/d/Y H:i'); ?>";
            });
        }
    }

如你所見，我們接著會將 `format` 方法接到傳入指示詞的運算式之後。因此，在這個範例中，這個指示詞最終所產生的指示詞會長這樣：

    <?php echo ($var)->format('m/d/Y H:i'); ?>

> {note} 更新完 Blade 的指示詞邏輯後，會需要刪除所有已快取的 Blade View。可以通過 `view:clear` Artisan 指令來移除已快取的 Blade View。

<a name="custom-echo-handlers"></a>

### 自訂 Echo 處理常式

若有打算在 Blade 中「echo」某個物件，則 Blade 會叫用該物件的 `__toString` 方法。[`__toString`](https://www.php.net/manual/en/language.oop5.magic.php#object.tostring) 方法是 PHP 的其中一個「魔法方法」。不過，有的時候我們可能無法控制給定類別的 `__toString` 方法，如：來自第三方函式庫的類別。

這個時候，Blade 能讓我們針對特定類型的物件註冊自訂的 Echo 處理常式。為此，應叫用 Blade 的 `stringable` 方法。`stringable` 方法接受一個閉包，該閉包應在型別標示中指定要負責轉譯的物件。一般情況下來說，`stringable` 方法應在專案的 `AppServiceProvider` 類別中 `boot` 方法內叫用：

    use Illuminate\Support\Facades\Blade;
    use Money\Money;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::stringable(function (Money $money) {
            return $money->formatTo('en_GB');
        });
    }

定義好自訂的 Echo 處理常式後，就可以直接在 Blade 樣板中 Echo 這個物件：

```html
Cost: {{ $money }}
```

<a name="custom-if-statements"></a>

### 自訂 If 陳述式

為了定義一個簡單的自訂條件陳述式，撰寫一個自訂指示詞有時候搞得很複雜又不必要。為此，Blade 提供了一個 `Blade::if` 方法，可以讓你通過閉包來快速地定義自訂條件指示詞。舉例來說，讓我們來定義一個檢查專案設定的預設「disk」的自訂條件句。我們可以在 `AppServiceProvider` 中的 `boot` 方法內定義：

    use Illuminate\Support\Facades\Blade;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::if('disk', function ($value) {
            return config('filesystems.default') === $value;
        });
    }

定義好自訂條件句後，就可以在樣板中使用這個條件句：

```html
@disk('local')
    <!-- 應用程式使用 local disk… -->
@elsedisk('s3')
    <!-- 應用程式使用 s3 disk… -->
@else
    <!-- 應用程式使用其他的 disk… -->
@enddisk

@unlessdisk('local')
    <!-- 應用程式不使用 local disk… -->
@enddisk
```
