---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/9/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:17:00Z'
---

# Artisan 主控台

- [簡介](#introduction)
   - [Tinker (REPL)](#tinker)
- [撰寫指令](#writing-commands)
   - [產生指令](#generating-commands)
   - [指令結構](#command-structure)
   - [閉包指令](#closure-commands)
   - [可隔離的指令](#isolatable-commands)
- [定義預期的輸入](#defining-input-expectations)
   - [引數](#arguments)
   - [選項](#options)
   - [輸入陣列](#input-arrays)
   - [輸入說明](#input-descriptions)
- [指令 I/O](#command-io)
   - [取得輸入](#retrieving-input)
   - [為輸入進行提示](#prompting-for-input)
   - [撰寫輸出](#writing-output)
- [註冊指令](#registering-commands)
- [使用程式碼呼叫指令](#programmatically-executing-commands)
   - [自其他指令內呼叫指令](#calling-commands-from-other-commands)
- [處理訊號 (Signal)](#signal-handling)
- [自訂 Stub](#stub-customization)
- [事件](#events)

<a name="introduction"></a>

## 簡介

Artisan 是 Laravel 內所包含的指令列界面。Artisan 是放在專案根目錄的 `artisan` 工序指令，提供多種實用指令來幫你撰寫你的專案。若要檢視所有可用的 Artisan 指令，可以使用 `list` 指令：

```shell
php artisan list
```

每個指令也包含了一個「help」畫面，用於顯示指令的說明以及可用的引數與選項。若要檢視輔助說明畫面，請在指令名稱的前面加上 `help`：

```shell
php artisan help migrate
```

<a name="laravel-sail"></a>

#### Laravel Sail

若使用 [Laravel Sail](/docs/{{version}}/sail) 作為本機開發環境，請記得使用 `sail` 指令列來叫用 Artisan 指令。Sail 會在專案的 Docker 容器內執行 Artisan 指令。

```shell
./vendor/bin/sail artisan list
```

<a name="tinker"></a>

### Tinker (REPL)

Laravel Tinker 是用於 Laravel 框架的強大 REPL，由 [PsySH](https://github.com/bobthecow/psysh) 套件提供。

<a name="installation"></a>

#### 安裝

所有的 Laravel 專案預設都包含了 Tinker。但若先前曾自專案內移除 Tinker，則可使用 Composer 來安裝：

```shell
composer require laravel/tinker
```

> **Note** 想找個能與你的 Laravel 應用程式互動的圖形化 UI 嗎？試試 [Tinkerwell](https://tinkerwell.app) 吧！

<a name="usage"></a>

#### 使用

Tinker 可讓你在指令列內與完整的 Laravel 專案進行互動，包含 Eloquent Model、任務、事件…等。要進入 Tinker 環境，請執行 `tinker` Artisan 指令：

```shell
php artisan tinker
```

可以通過 `vendor:publish` 指令來安裝 Tinker 的設定檔：

```shell
php artisan vendor:publish --provider="Laravel\Tinker\TinkerServiceProvider"
```

> **Warning** `dispatch` 輔助函式與 `Dispatchable` 類別上的 `dispatch` 方法需要仰賴垃圾回收機制來將任務放進佇列中。因此，在使用 Tinker 時，應使用 `Bus::dispatch` 或 `Queue::push` 來分派任務。

<a name="command-allow-list"></a>

#### 指令允許列表

Tinker 使用一個「allow」清單來判斷哪些 Artisan 指令可在其 Shell 內執行。預設情況下，可以執行 `clear-compiled`, `down`, `env`, `inspire`, `migrate`, `optimize` 以及 `up` 指令。若想允許更多指令，可以將要允許的指令加在 `tinker.php` 設定檔中的 `commands` 陣列內：

    'commands' => [
        // App\Console\Commands\ExampleCommand::class,
    ],

<a name="classes-that-should-not-be-aliased"></a>

#### 不應以別名使用的類別

一般來說，Tinker 會在使用過程中自動為類別加上別名。但有些類別可能不希望被設定別名。可以通過在 `tinker.php` 設定檔中的 `dont_alias` 陣列中列出這些不想被自動別名的類別來達成：

    'dont_alias' => [
        App\Models\User::class,
    ],

<a name="writing-commands"></a>

## 撰寫指令

除了 Artisan 提供的指令外，也可以建制自己的自訂指令。指令通常儲存於 `app/Console/Commands` 目錄內。但是，只要你的自訂指令可以被 Composer 載入，也可以自行選擇儲存位置。

<a name="generating-commands"></a>

### 產生指令

若要建立新指令，可以使用 `make:command` Artisan 指令。該指令會在 `app/Console/Commands` 目錄下建立一個新的指令類別。若你的專案中沒有這個資料夾，請別擔心——第一次執行 `make:command` Artisan 指令的時候會自動建立該資料夾：

```shell
php artisan make:command SendEmails
```

<a name="command-structure"></a>

### 指令結構

產生指令後，應為類別的 `signature` 與 `description` 屬性定義適當的值。當在 `list` 畫面內顯示該指令時，就會用到這些屬性。`signature` 屬性可以用來定義 [指令預期的輸入](#defining-input-expectations)。`handle` 方法會在執行該指令時呼叫。可以將指令的邏輯放在該方法內。

來看看一個範例指令。請注意，我們可以通過指令的 `handle` 方法來要求任意的相依性。Laravel 的 [Service Container](/docs/{{version}}/container) 會自動插入所有在方法簽章內有型別提示的相依性。

    <?php
    
    namespace App\Console\Commands;
    
    use App\Models\User;
    use App\Support\DripEmailer;
    use Illuminate\Console\Command;
    
    class SendEmails extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'mail:send {user}';
    
        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Send a marketing email to a user';
    
        /**
         * Execute the console command.
         *
         * @param  \App\Support\DripEmailer  $drip
         * @return mixed
         */
        public function handle(DripEmailer $drip)
        {
            $drip->send(User::find($this->argument('user')));
        }
    }

> **Note** 為了提升程式碼重複使用率，最好保持主控台指令精簡，並將指令的任務委託給應用程式服務來完成。在上方的例子中，可以注意到我們插入了一個服務類別來處理寄送 E-Mail 的這個「重責大任」。

<a name="closure-commands"></a>

### 閉包指令

基於閉包的指令提供了以類別定義主控台指令外的另一個選擇。就如同使用閉包來定義路由可用來代替控制器一樣，可以將指令閉包想象成是指令類別的代替。在 `app/Console/Kernel.php` 檔中的 `commands` 方法內，Laravel 載入了 `routes/console.php` 檔：

    /**
     * Register the closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

這個檔案並沒有定義 HTTP 路由，而是定義從主控台「路由」進入專案的進入點。在該檔案內，可以通過 `Artisan::command` 方法來定義基於閉包的主控台指令。`command` 方法接受 2 個引數：[指令簽章](#defining-input-expectations)，以及一個用來接收指令引數與選項的閉包：

    Artisan::command('mail:send {user}', function ($user) {
        $this->info("Sending email to: {$user}!");
    });

這裡的閉包有綁定到該指令的基礎類別執行個體，因此可以像在完整的指令類別內一樣存取所有的輔助函式。

<a name="type-hinting-dependencies"></a>

#### 對相依關係進行型別提示

除了接收指令的引數與選項外，指令閉包也可以通過型別提示來向 [Service Container](/docs/{{version}}/container) 解析額外的相依關係。

    use App\Models\User;
    use App\Support\DripEmailer;
    
    Artisan::command('mail:send {user}', function (DripEmailer $drip, $user) {
        $drip->send(User::find($user));
    });

<a name="closure-command-descriptions"></a>

#### 閉包指令描述

在定義基於閉包的指令時，可以使用 `purpose` 方法來為該指令加上描述。這段描述會在執行 `php artisan list` 或 `php artisan help` 指令時顯示：

    Artisan::command('mail:send {user}', function ($user) {
        // ...
    })->purpose('Send a marketing email to a user');

<a name="isolatable-commands"></a>

### 可隔離的指令

> **Warning** 若要使用此功能，則應用程式必須要使用 `memcached`, `redis`, `dynamodb`, `database`, `file` 或 `array` 作為應用程式的預設快取 Driver。另外，所有的伺服器也都必須要連線至相同的中央快取伺服器。

有時候，我們可能需要確保某個指令在同一時間只有一個實體在執行。為此，可以在指令類別上實作 `Illuminate\Contracts\Console\Isolatable` Interface：

    <?php
    
    namespace App\Console\Commands;
    
    use Illuminate\Console\Command;
    use Illuminate\Contracts\Console\Isolatable;
    
    class SendEmails extends Command implements Isolatable
    {
        // ...
    }

將指令標記為 ^[`Isolatable`](可隔離的) 後，Laravel 會自動為該指令加上一個 `--isolated` 選項。使用 `--isolated` 選項呼叫該指令時，Laravel 會確保沒有其他該指令的實體正在執行。Laravel 通過在預設快取 Driver 上取得 ^[Atomic Lock](不可部分完成鎖定) 來確保只有一個實體在執行。若該指令有其他實體在執行，就不會執行該指令。不過，該指令依然會以成功的終止狀態碼結束：

```shell
php artisan mail:send 1 --isolated
```

若想指定該指令無法執行時回傳的終止狀態碼，可使用 `isolated` 選項來設定：

```shell
php artisan mail:send 1 --isolated=12
```

<a name="lock-expiration-time"></a>

#### Lock 的逾期時間

預設情況下，指令完成執行後，獨立指令的 Lock 就會逾時。而如果指令在執行時遭到中斷而無法完成，該 Lock 會在一小時後逾時。不過，你可以在指令中定義一個 `isolationLockExpiresAt` 方法來調整逾時時間：

```php
/**
 * Determine when an isolation lock expires for the command.
 *
 * @return \DateTimeInterface|\DateInterval
 */
public function isolationLockExpiresAt()
{
    return now()->addMinutes(5);
}
```

<a name="defining-input-expectations"></a>

## 定義預期的輸入

在撰寫主控台指令時，常常會通過引數或選項來向使用者取得輸入。Laravel 通過指令的 `signature` 屬性來定義預期從使用者那取得的輸入，讓這個過程變得非常簡單。通過 `signature` 屬性，就能通過類似路由的格式來一次定義名稱、引數，以及選項。非常簡潔有力。

<a name="arguments"></a>

### 引數

所有由使用者提供的引數與選項都以大括號來包裝。在下列範例中的指令定義了一個必要的引數：`user`：

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send {user}';

也可以將引數設為可選，或是定義引數的預設值：

    // 可選引數...
    'mail:send {user?}'
    
    // 有預設值的可選引數...
    'mail:send {user=foo}'

<a name="options"></a>

### 選項

選項就像引數一樣，是另一種形式的使用者輸入。選項在從指令列提供時，會加上兩個減號 (`--`) 作為前綴。有兩種類型的選項：一種可接收值，一種沒有接收值。沒有接收值的選項是一種布林「開關」功能。來看看一個使用這種類型選項的例子：

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send {user} {--queue}';

在這個例子中，呼叫該 Artisan 指令時可以指定 `--queue` 開關。若有傳入 `--queue` 開關，則該選項的值會是 `true`。否則，該值為 `false`：

```shell
php artisan mail:send 1 --queue
```

<a name="options-with-values"></a>

#### 帶值的選項

接下來，來看看有值的選項。若使用者必須為選項指定一個值，則應在選項名稱後方加上 `=` 符號：

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send {user} {--queue=}';

在這個例子中，使用者可以傳入像這樣給選項帶入一個值。若在叫用該指令時未指定該選項，則其值為 `null`：

```shell
php artisan mail:send 1 --queue=default
```

可以通過在選項名稱後方加上預設值來為選項指派一個預設值。若使用者未傳入選項值，將會使用預設值：

    'mail:send {user} {--queue=default}'

<a name="option-shortcuts"></a>

#### 選項捷徑

若要在定義選項時指定捷徑，可以在選項名稱前加上其捷徑名稱，並使用 `|` 字元來區分捷徑名稱與完整的選項名稱：

    'mail:send {user} {--Q|queue}'

在終端機內叫用指令時，應在選項捷徑前加上一個減號：

```shell
php artisan mail:send 1 -Q
```

<a name="input-arrays"></a>

### 輸入陣列

若想要定義預期有多個輸入值的引數或選項，則可以使用 `*` 字元。首先，來看看這樣設定引數的例子：

    'mail:send {user*}'

呼叫這個方法的時候，`user` 引數在指令列中可以按照順序傳入。舉例來說，下列指令會將 `user` 的值設為一個內容為 `1` 與 `2` 的陣列：

```shell
php artisan mail:send 1 2
```

`*` 字元可以與可選引數組合使用來定義，這樣一來可允許有 0 個或多個引數的實體：

    'mail:send {user?*}'

<a name="option-arrays"></a>

#### 選項陣列

定義預期有多個輸入值的選項時，每個傳入指令的選項值都應以選項名稱作為前綴：

    'mail:send {--id=*}'

可以通過傳入多個 `-id` 引數來叫用這樣的指令：

```shell
php artisan mail:send --id=1 --id=2
```

<a name="input-descriptions"></a>

### 輸入描述

可以通過以冒號 (`:`) 區分引數名與描述來為輸入引數或選項指定描述。若需要更多空間來定義指令的話，可以將定義拆分為多行：

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send
                            {user : The ID of the user}
                            {--queue : Whether the job should be queued}';

<a name="command-io"></a>

## 指令 I/O

<a name="retrieving-input"></a>

### 截取輸入

指令執行時，我們通常需要存取這些指令所接收的引數與選項值。要截取這些值，可以使用 `argument` 與 `option` 方法。若引數或選項不存在，則會回傳 `null`：

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userId = $this->argument('user');
    
        //
    }

若要將所有引數截取為陣列，則可呼叫 `arguments` 方法：

    $arguments = $this->arguments();

我們也可像截取引數一樣使用 `option` 方法來輕鬆地截取選項。若要將所有選項截取為陣列，請呼叫 `options` 方法：

    // 取得特定選項...
    $queueName = $this->option('queue');
    
    // 將所有選項作為陣列取得...
    $options = $this->options();

<a name="prompting-for-input"></a>

### 為輸入進行提示

除了顯示輸出外，也可以在執行指令的過程中詢問使用者來提供輸入。`ask` 方法會提示使用者給定的問題，並接受使用者輸入，然後將使用者的輸入回傳至指令：

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->ask('What is your name?');
    }

`secret` 方法與 `ask` 類似，但使用者在指令列輸入的過程中將看不到他們自己的輸入值。這個方法適用於像使用者詢問如密碼等機密資訊的時候：

    $password = $this->secret('What is the password?');

<a name="asking-for-confirmation"></a>

#### 要求確認

若需要使用者回答簡單的「yes / no」的確認問題，可以使用 `confirm` 方法。預設情況下，這個方法會回傳 `false`，但若使用者在提示時輸入 `y` 或 `yes`，則該方法會回傳 `true`。

    if ($this->confirm('Do you wish to continue?')) {
        //
    }

若有必要，也可以通過將 `true` 傳入為 `confirm` 方法的第二個引數來指定讓確認提示預設回傳 `true`：

    if ($this->confirm('Do you wish to continue?', true)) {
        //
    }

<a name="auto-completion"></a>

#### 自動補全

`anticipate` 方法可以用來為可能的選項提供自動補全。不論自動補全提示了什麼，使用者一樣可以提供任意回答：

    $name = $this->anticipate('What is your name?', ['Taylor', 'Dayle']);

另外，也可以將一個閉包傳給 `anticipate` 方法的第二個引數。這個閉包會在每次使用者輸入字元的時候被呼叫。該閉包應接受一個字串參數，其中包含了目前使用者的輸入值，並回傳用於自動補全的選項陣列：

    $name = $this->anticipate('What is your address?', function ($input) {
        // 回傳自動補全的選項...
    });

<a name="multiple-choice-questions"></a>

#### 多重選擇問題

若需要在詢問問題時為提供使用者一組預先定義的選項，可以使用 `choice` 方法。也可以通過將預設選項的陣列索引傳給該方法的第三個參數，來指定沒有選擇任何選項時要回傳的預設值：

    $name = $this->choice(
        'What is your name?',
        ['Taylor', 'Dayle'],
        $defaultIndex
    );

另外，`choice` 方法也接受第 4 個與第 5 個引數，這兩個引數分別是用來判斷選擇有效回答的最大嘗試次數，以及是否允許多重選擇：

    $name = $this->choice(
        'What is your name?',
        ['Taylor', 'Dayle'],
        $defaultIndex,
        $maxAttempts = null,
        $allowMultipleSelections = false
    );

<a name="writing-output"></a>

### 撰寫輸出

若要將輸出傳送至主控台，可以使用 `line`, `info`, `comment`, `question`, `warn` 與 `error` 方法。這幾個方法會依不同目的來使用適當的 ANSI 色彩。舉例來說，我們來顯示一些一般的資訊給使用者看。通常來說，`info` 方法會在主控台上顯示出綠色的文字：

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // ...
    
        $this->info('The command was successful!');
    }

若要顯示錯誤訊息，可以使用 `error` 方法。錯誤訊息文字通常會以紅色顯示：

    $this->error('Something went wrong!');

也可以使用 `line` 方法來顯示未標示色彩的純文字：

    $this->line('Display this on the screen');

可以使用 `newLine` 方法來顯示空行：

    // 寫入一行空行...
    $this->newLine();
    
    // 寫入三行空行...
    $this->newLine(3);

<a name="tables"></a>

#### 表格

通過 `table` 方法可以很輕鬆地正確為多行列資料進行格式化。只需要提供表格的欄位名稱與表格的資料，Laravel 就會自動計算適當的表格寬高：

    use App\Models\User;
    
    $this->table(
        ['Name', 'Email'],
        User::all(['name', 'email'])->toArray()
    );

<a name="progress-bars"></a>

#### 進度列

當有需要長時間執行的任務時，最好顯示一個能告訴使用者目前任務完成度的進度列。使用 `withProgressBar` 方法，Laravel 就會顯示出一個進度列，並在每次迭代過指定的迭代值時增加進度列的進度：

    use App\Models\User;
    
    $users = $this->withProgressBar(User::all(), function ($user) {
        $this->performTask($user);
    });

有時候，我們可能需要手動控制進度列何時需要增加。首先，我們先定義整個過程所需要迭代的次數。接著，在每個項目處理完後增加進度：

    $users = App\Models\User::all();
    
    $bar = $this->output->createProgressBar(count($users));
    
    $bar->start();
    
    foreach ($users as $user) {
        $this->performTask($user);
    
        $bar->advance();
    }
    
    $bar->finish();

> **Note** 有關更進階的選項，請參考 [Symfony Progress Bar 元件說明文件](https://symfony.com/doc/current/components/console/helpers/progressbar.html)。

<a name="registering-commands"></a>

## 註冊指令

所有主控台指令都在 `App\Console\Kernel` 類別內自動註冊。該類別為專案的「主控台核心」。在該類別的 `commands` 方法內，可以看到一個核心 `load` 方法的呼叫。`load` 方法會掃描 `app/Console/Commands` 目錄並自動向 Artisan 註冊其中的各個指令。你也可以在 `load` 方法中加上額外的呼叫來掃描其他目錄中的 Artisan 指令：

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/../Domain/Orders/Commands');
    
        // ...
    }

若有需要的話，也可以通過將指令的類別名稱加至 `App\Console\Kernel` 類別的 `$commands` 屬性來手動註冊指令。若該屬性不存在，你可以手動建立。當 Artisan 啟動時，列在該屬性上的所有指令都會由 [Service Container](/docs/{{version}}/container) 進行解析，並向 Artisan 註冊：

    protected $commands = [
        Commands\SendEmails::class
    ];

<a name="programmatically-executing-commands"></a>

## 通過程式碼執行指令

有時候可能需要在 CLI 以外的地方執行 Artisan 指令。舉例來說，你可能會想在路由或控制器內執行 Artisan 指令。可以使用 `Artisan` Facade 的 `call` 方法來完成這一目標。可以傳入指令的簽章名稱或類別名稱給 `call` 方法的第一個引數，而指令的參數則可以陣列傳為第二個引數。指令的結束代碼（Exit Code）會被回傳：

    use Illuminate\Support\Facades\Artisan;
    
    Route::post('/user/{user}/mail', function ($user) {
        $exitCode = Artisan::call('mail:send', [
            'user' => $user, '--queue' => 'default'
        ]);
    
        //
    });

或者，也可以將整個 Artisan 指令作為字串傳給 `call` 方法：

    Artisan::call('mail:send 1 --queue=default');

<a name="passing-array-values"></a>

#### 傳入陣列值

若指令有定義接受陣列的選項，則可將陣列傳給該選項：

    use Illuminate\Support\Facades\Artisan;
    
    Route::post('/mail', function () {
        $exitCode = Artisan::call('mail:send', [
            '--id' => [5, 13]
        ]);
    });

<a name="passing-boolean-values"></a>

#### 傳入布林值

若有需要為不接受字串值的選項指定值，如 `migrate:refresh` 指令的 `--force` 旗標，則可以為該選項傳入 `true` 或 `false`：

    $exitCode = Artisan::call('migrate:refresh', [
        '--force' => true,
    ]);

<a name="queueing-artisan-commands"></a>

#### 將 Artisan 指令放入佇列

只需要使用 `Artisan` Facade 的 `queue` 方法，就可以將 Artisan 指令放入佇列執行，這樣這個指令就會在 [佇列背景工作角色](/docs/{{version}}/queues) 內背景執行。在使用該方法前，請先確認是否已設定好佇列，且有執行佇列監聽程式：

    use Illuminate\Support\Facades\Artisan;
    
    Route::post('/user/{user}/mail', function ($user) {
        Artisan::queue('mail:send', [
            'user' => $user, '--queue' => 'default'
        ]);
    
        //
    });

可以使用 `onConnection` 與 `onQueue` 方法來指定 Artisan 指令應分派到哪個連線或佇列上：

    Artisan::queue('mail:send', [
        'user' => 1, '--queue' => 'default'
    ])->onConnection('redis')->onQueue('commands');

<a name="calling-commands-from-other-commands"></a>

### 在其他指令內執行指令

有時候可能需要在現有 Artisan 指令內執行另一個指令。可以通過呼叫 `call` 方法來完成。`call` 方法接受指令名稱與指令的引數與選項：

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('mail:send', [
            'user' => 1, '--queue' => 'default'
        ]);
    
        //
    }

若有需要呼叫另一個主控台指令並忽略其所有輸出，則可使用 `callSilently` 方法。`callSilently` 方法的簽章與 `call` 方法相同：

    $this->callSilently('mail:send', [
        'user' => 1, '--queue' => 'default'
    ]);

<a name="signal-handling"></a>

## 處理訊號

讀者可能已經知道，在作業系統中，我們可以傳送訊號 (Signal) 給正在執行的處理程序。舉例來說，作業系統會使用 `SIGTERM` 訊號來要求某個程式停止執行。若想在 Artisan 主控台指令上監聽這些訊號，可使用 `trap` 方法：

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->trap(SIGTERM, fn () => $this->shouldKeepRunning = false);
    
        while ($this->shouldKeepRunning) {
            // ...
        }
    }

若要同時監聽多個訊號，可提供一組訊號的陣列給 `trap` 方法：

    $this->trap([SIGTERM, SIGQUIT], function ($signal) {
        $this->shouldKeepRunning = false;
    
        dump($signal); // SIGTERM / SIGQUIT
    });

<a name="stub-customization"></a>

## 自訂 Stub

Artisan 主控台的 `make` 指令可以用來建立各種類別，如控制器、任務、資料庫遷移，以及測試。這些類別都是使用「Stub (虛設常式)」來產生的，Stub 會依據給定的輸入來填入不同的值。不過，你可能會想對這些 Artisan 產生的檔案做一些微調。要修改這些 Stub，可以通過 `stub:publish` 指令來將這些最常見的 Stub 安裝到專案中，如此一來就能自訂這些 Stub：

```shell
php artisan stub:publish
```

安裝的 Stub 會被放在專案根目錄的 `stubs` 目錄中。對這些 Stub 做出的任何改動都會反應到使用 Artisan 的 `make` 指令所產生的對應類別上。

<a name="events"></a>

## 事件

Artisan 會在執行指令的時候分派三個事件： `Illuminate\Console\Events\ArtisanStarting`, `Illuminate\Console\Events\CommandStarting` 與 `Illuminate\Console\Events\CommandFinished`。`ArtisanStarting` 事件會在 Artisan 開始執行後馬上被分派。接著，`CommandStarting` 事件會在指令開始執行前的瞬間被分派。最後，`CommandFinished` 事件會在指令完成執行後被分派。
