---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/185/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:27:00Z'
---

# Process

- [簡介](#introduction)
- [呼叫 Process](#invoking-processes)
   - [Process 選項](#process-options)
   - [Process 的輸出](#process-output)
- [非同步的 Process](#asynchronous-processes)
   - [Process ID 與 Signal](#process-ids-and-signals)
   - [非同步 Process 的輸出](#asynchronous-process-output)
- [併行地 Process](#concurrent-processes)
   - [命名的 Pool Process](#naming-pool-processes)
   - [Pool Process 的 ID 與 Signal](#pool-process-ids-and-signals)
- [測試](#testing)
   - [模擬 Process](#faking-processes)
   - [模擬特定 Process](#faking-specific-processes)
   - [模擬 Process 的順序](#faking-process-sequences)
   - [模擬非同步 Process 的生命週期](#faking-asynchronous-process-lifecycles)
   - [可用的 Assertion](#available-assertions)
   - [避免漏掉的 Process](#preventing-stray-processes)

<a name="introduction"></a>

## 簡介

Laravel 為 [Symfony 的 Process Component](https://symfony.com/doc/current/components/process.html) 提供了一個語意化、極簡的 API，能讓我們方便地在 Laravel 專案中呼叫外部 ^[Process](處理程序)。Laravel 的 Process 功能著重於最常見的使用情境，並提供優秀的開發人員經驗 (Developer Experience)。

<a name="invoking-processes"></a>

## 呼叫 Process

若要呼叫 Process，可以使用 `Process` Facade 提供的 `run` 與 `start` 方法。`run` 方法會呼叫 Process，並等待該 Process 執行完畢。而 `start` 方法會以非同步方式執行 Process。我們將在此文件中詳細討論這兩種方法。首先，我們先來看看如何執行一個基本的同步 Process 並取得其執行結果：

```php
use Illuminate\Support\Facades\Process;

$result = Process::run('ls -la');

return $result->output();
```

當然，`run` 方法回傳的 `Illuminate\Contracts\Process\ProcessResult` 實體還包含了多種可用於檢查 Process 執行結果的實用方法：

```php
$result = Process::run('ls -la');

$result->successful();
$result->failed();
$result->exitCode();
$result->output();
$result->errorOutput();
```

<a name="throwing-exceptions"></a>

#### 擲回 Exception

若在取得 Process 執行結果後，希望能讓終止代碼 (Exit Code) 大於 0 的狀況 (表示執行失敗) 擲回 `Illuminate\Process\Exceptions\ProcessFailedException`，可以使用 `throw` 與 `throwIf` 方法。若 Process 並未執行失敗，則會回傳 Process 執行結果的實體：

```php
$result = Process::run('ls -la')->throw();

$result = Process::run('ls -la')->throwIf($condition);
```

<a name="process-options"></a>

### Process 選項

當然，你可能會需要在呼叫 Process 前自訂該 Process 的行為。在 Laravel 中，可以調整許多 Process 的功能，例如工作目錄 (Working Directory)、逾時與環境變數等。

<a name="working-directory-path"></a>

#### 工作目錄路徑

可以使用 `path` 方法來指定 Process 的工作目錄。若未呼叫此方法，則該 Process 會繼承目前執行 PHP Script 的工作目錄：

```php
$result = Process::path(__DIR__)->run('ls -la');
```

<a name="timeouts"></a>

#### 逾時

預設情況下，Process 會在執行超過 60 秒後擲回 `Illuminate\Process\Exceptions\ProcessTimedOutException` 實體。不過，可以使用 `timeout` 方法來自定此行為：

```php
$result = Process::timeout(120)->run('bash import.sh');
```

或者，若要完全禁用 Process 的逾時，可以呼叫 `forever` 方法：

```php
$result = Process::forever()->run('bash import.sh');
```

`idleTimeout` 方法可用來指定 Process 在不回傳任何輸出下可執行的最大秒數：

```php
$result = Process::timeout(60)->idleTimeout(30)->run('bash import.sh');
```

<a name="environment-variables"></a>

#### 環境變數

可以使用 `env` 方法來提供環境變數給 Process。被呼叫的 Process 也會繼承在系統中所定義的所有環境變數：

```php
$result = Process::forever()
            ->env(['IMPORT_PATH' => __DIR__])
            ->run('bash import.sh');
```

若想從呼叫的 Process 中移除繼承的環境變數，可以提供一個值為 `false` 的環境變數：

```php
$result = Process::forever()
            ->env(['LOAD_PATH' => false])
            ->run('bash import.sh');
```

<a name="tty-mode"></a>

#### TTY 模式

`tty` 方法可用來在 Process 上啟用 TTY 模式。TTY 模式會將 Process 的 Input 與 Output 連結到你的程式的 Input 與 Output，讓你的 Process 能打開如 Vim 或 Nano 之類的 Process：

```php
Process::forever()->tty()->run('vim');
```

<a name="process-output"></a>

### Process 的輸出

剛才提到過，可以使用 `output` (stdout) 與 `errorOutput` (stderr) 方法來在 Process 結果上取得 Process 的輸出：

```php
use Illuminate\Support\Facades\Process;

$result = Process::run('ls -la');

echo $result->output();
echo $result->errorOutput();
```

不過，也可以在呼叫 `run` 方法時傳入一個 Closure 作為第二個引數來即時取得輸出。該 Closure 會收到兩個引數：輸出的「類型 (Type)」(`stdout` 或 `stderr`) 與輸出字串本身：

```php
$result = Process::run('ls -la', function (string $type, string $output) {
    echo $output;
});
```

Laravel 也提供了 `seeInOutput` 與 `seeInErrorOutput` 方法。通過這兩個方法，就可以方便地判斷給定的字串是否包含在該 Process 的輸出中：

```php
if (Process::run('ls -la')->seeInOutput('laravel')) {
    // ...
}
```

<a name="disabling-process-output"></a>

#### 關閉 Process 的輸出

若 Process 會寫入大量不必要的輸出，可以完全關閉取得輸出來減少記憶體使用。若要關閉取得輸出，請在建構 Process 時呼叫 `quietly` 方法：

```php
use Illuminate\Support\Facades\Process;

$result = Process::quietly()->run('bash import.sh');
```

<a name="asynchronous-processes"></a>

## 非同步的 Process

`run` 方法會同步呼叫 Process，而 `start` 方法可用來非同步地呼叫 Process。這樣一來，你的程式就可以繼續執行其他任務，並讓 Process 在背景執行。Process 被呼叫後，可以使用 `running` 方法來判斷該 Process 是否還在執行：

```php
$process = Process::timeout(120)->start('bash import.sh');

while ($process->running()) {
    // ...
}

$result = $process->wait();
```

讀者可能已經注意到，可以通過呼叫 `wait` 方法來等待 Process 完成執行，然後再取得 Process 的結果實體：

```php
$process = Process::timeout(120)->start('bash import.sh');

// ...

$result = $process->wait();
```

<a name="process-ids-and-signals"></a>

### Process 的 ID 與 Signal

`pid` 方法可用來取得正在執行的 Process 由作業系統指派的 Process ID：

```php
$process = Process::start('bash import.sh');

return $process->pid();
```

可以使用 `signal` 方法來向正在執行的 Process 傳送「訊號 (Signal)」。請參考《[PHP 說明文件](https://www.php.net/manual/en/pcntl.constants.php)》以瞭解預先定義的 Signal 常數列表：

```php
$process->signal(SIGUSR2);
```

<a name="asynchronous-process-output"></a>

### 非同步 Process 的輸出

當非同步 Process 正在執行時，可以使用 `output` 與 `errorOutput` 方法來取得該 Process 目前的完整輸出。而 `latestOutput` 與 `latestErrorOutput` 可用來存取自從上一次取得輸出後該 Process 所產生的最新輸出：

```php
$process = Process::timeout(120)->start('bash import.sh');

while ($process->running()) {
    echo $process->latestOutput();
    echo $process->latestErrorOutput();

    sleep(1);
}
```

與 `run` 方法類似，`start` 方法也可以在呼叫時傳入一個 Closure 作為第二個引數來即時取得輸出。該 Closure 會收到兩個引數：輸出的「類型 (Type)」(`stdout` 或 `stderr`) 與輸出字串本身：

```php
$process = Process::start('bash import.sh', function (string $type, string $output) {
    echo $output;
});

$result = $process->wait();
```

<a name="concurrent-processes"></a>

## 併行的 Process

Laravel 也讓管理平行的、非同步的 Process 集區 (Pool) 變的非常容易，讓你能輕鬆的同步執行多個任務。若要執行非同步 Process 集區，請執行 `pool` 方法。請傳入一個 Closure 給 `pool` 方法，該 Closure 會收到 `Illumiante\Process\Pool` 的實體。

在該 Closure 中，可以定義屬於該集區的 Process。使用 `start` 方法開始 Process 集區後，可以使用 `running` 方法來存取一組包含正在執行的 Process 的 [Collection](/docs/{{version}}/collections)：

```php
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

$pool = Process::pool(function (Pool $pool) {
    $pool->path(__DIR__)->command('bash import-1.sh');
    $pool->path(__DIR__)->command('bash import-2.sh');
    $pool->path(__DIR__)->command('bash import-3.sh');
})->start(function (string $type, string $output, int $key) {
    // ...
});

while ($pool->running()->isNotEmpty()) {
    // ...
}

$results = $pool->wait();
```

就像這樣，可以使用 `wait` 方法來等待集區 Process 完成執行並解析這些 Process 的執行結果。`wait` 方法會回傳一個可使用陣列存取的物件 (Array Accessible Object)，讓你能使用其索引鍵來存取集區中各個 Process 的 Process 執行結果實體：

```php
$results = $pool->wait();

echo $results[0]->output();
```

或者，也可以使用方便的 `concurrently` 方法來開始一組非同步 Process 集區，並馬上開始等待其執行結果。當與 PHP 的陣列解構功能搭配使用時，使用此方法就可取得富含表達性的語法：

```php
[$first, $second, $third] = Process::concurrently(function (Pool $pool) {
    $pool->path(__DIR__)->command('ls -la');
    $pool->path(app_path())->command('ls -la');
    $pool->path(storage_path())->command('ls -la');
});

echo $first->output();
```

<a name="naming-pool-processes"></a>

### 命名的 Pool Process

使用數字索引鍵來存取 Process 集區並不是很有表達性。因此，Laravel 可讓你使用 `as` 方法來為集區中的各個 Process 指派一個字串索引鍵。該索引鍵也會傳入提供給 `start` 方法的 Closure，讓你能判斷輸出屬於哪個 Process：

```php
$pool = Process::pool(function (Pool $pool) {
    $pool->as('first')->command('bash import-1.sh');
    $pool->as('second')->command('bash import-2.sh');
    $pool->as('third')->command('bash import-3.sh');
})->start(function (string $type, string $output, string $key) {
    // ...
});

$results = $pool->wait();

return $results['first']->output();
```

<a name="pool-process-ids-and-signals"></a>

### Pool Process 的 ID 與 Signal

由於 Process 集區的 `running` 方法提供了一組包含集區中所有已呼叫 Process 的 Collection，因此你可以輕鬆地存取集區中相應的 Process ID：

```php
$processIds = $pool->running()->each->pid();
```

而且，也可以在 Process 集區上使用 `signal` 方法來方便地傳送 Signal 給集區中的每一個 Process：

```php
$pool->signal(SIGUSR2);
```

<a name="testing"></a>

## 測試

許多 Laravel 的服務都提供了能讓你輕鬆且表達性地撰寫測試的方法，而 Laravel 的 Process 服務也不例外。使用 `Process` Facade 的 `fake` 方法，能讓你指定要 Laravel 在執行 Process 時回傳一組模擬的執行結果。

<a name="faking-processes"></a>

### 模擬 Process

若要瞭解 Laravel 中模擬 Process 的功能，我們先來想像有個 Route 會呼叫一個 Process：

```php
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Route;

Route::get('/import', function () {
    Process::run('bash import.sh');

    return 'Import complete!';
});
```

在測試此 Route 時，我們可以不帶任何引數呼叫 `Process` Facade 上的 `fake` 方法，讓 Laravel 在每一個被呼叫的 Process 上回傳一組模擬的成功 Process 執行結果。此外，我們還可以 [Assert](#available-assertions) 判斷給定的 Process 是否已執行：

```php
<?php

namespace Tests\Feature;

use Illuminate\Process\PendingProcess;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_process_is_invoked(): void
    {
        Process::fake();

        $response = $this->get('/');

        // 簡單的 Process Assertion...
        Process::assertRan('bash import.sh');

        // 或者，也可以檢查 Process 的設定...
        Process::assertRan(function (PendingProcess $process, ProcessResult $result) {
            return $process->command === 'bash import.sh' &&
                   $process->timeout === 60;
        });
    }
}
```

剛才也提到過，在 `Process` Facade 上呼叫 `fake` 方法會讓 Laravel 為每個 Process 回傳沒有輸出的 Process 執行結果。不過，你可以使用 `Process` Facade 的 `result` 方法來輕鬆地指定模擬 Process 的輸出與結束代碼 (Exit Code)：

```php
Process::fake([
    '*' => Process::result(
        output: 'Test output',
        errorOutput: 'Test error output',
        exitCode: 1,
    ),
]);
```

<a name="faking-specific-processes"></a>

### 模擬特定的 Process

讀者可能已經在前一個例子中注意到，通過 `Process` Facade，就可以通過傳入一組陣列給 `fake` 方法來指定各個 Process 的模擬執行結果。

陣列的索引鍵代表要模擬的指令格式，以及其相應的執行結果。可使用 `*` 字元來作為萬用字元。沒有被模擬的 Process 指令會被實際執行。可以使用 `Process` Facade 的 `result` 方法來為這些指令建立模擬的執行結果：

```php
Process::fake([
    'cat *' => Process::result(
        output: 'Test "cat" output',
    ),
    'ls *' => Process::result(
        output: 'Test "ls" output',
    ),
]);
```

若不需要自定模擬 Process 的終止代碼或錯誤輸出，那麼使用字串來指定 Process 的模擬結果可能會更方便：

```php
Process::fake([
    'cat *' => 'Test "cat" output',
    'ls *' => 'Test "ls" output',
]);
```

<a name="faking-process-sequences"></a>

### 模擬 Process 序列

若要測試的程式碼會以相同指令來呼叫多個 Process，則可為各個 Process 呼叫指定不同的 Process 模擬執行結果。若要為各個 Process 呼叫設定各自的執行結果，請使用 `Process` Facade 的 `sequence` 方法：

```php
Process::fake([
    'ls *' => Process::sequence()
                ->push(Process::result('First invocation'))
                ->push(Process::result('Second invocation')),
]);
```

<a name="faking-asynchronous-process-lifecycles"></a>

### 模擬非同步 Process 的生命週期

到目前為止，我們主要針對使用 `run` 方法同步呼叫的 Process 討論要如何進行模擬。不過，若要測試的程式碼中有使用 `start` 來非同步呼叫 Process，就需要使用更複雜的方法來模擬 Process。

舉例來說，假設有下列 Route 會觸發非同步 Process：

```php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/import', function () {
    $process = Process::start('bash import.sh');

    while ($process->running()) {
        Log::info($process->latestOutput());
        Log::info($process->latestErrorOutput());
    }

    return 'Done';
});
```

若要正確模擬此 Process，我們需要能夠描述 `running` 方法要回傳幾次 `true`。此外，我們可能還需要指定要依序回傳的多行輸出。為此，我們可以使用 `Process` Facade 的 `describe` 方法：

```php
Process::fake([
    'bash import.sh' => Process::describe()
            ->output('First line of standard output')
            ->errorOutput('First line of error output')
            ->output('Second line of standard output')
            ->exitCode(0)
            ->iterations(3),
]);
```

讓我們來仔細看看上面的範例。使用 `output` 與 `errorOutput` 方法，我們可以指定要依序回傳的多行輸出。`exitCode` 方法可用來指定模擬 Process 最終的終止代碼。最後，`iterations` 方法可用來指定 `running` 方法要回傳幾次 `true`。

<a name="available-assertions"></a>

### 可用的 Assertion

就像[剛才提到過的](#faking-processes)，Laravel 為功能測試 (Feature Test) 提供了多個 Process 的 ^[Assertion](判斷提示)。我們會在接下來的部分討論這些 Assertion。

<a name="assert-process-ran"></a>

#### assertRan

判斷給定 Process 是否已被呼叫：

```php
use Illuminate\Support\Facades\Process;

Process::assertRan('ls -la');
```

也可傳入一個 Closure 給 `assertRun` 方法。該 Closure 會收到 Process 的實體與 Process 的執行結果，讓你能檢查 Process 上的設定。若讓該 Closure 回傳 `true`，則該 Assertion 就會通過 (Pass)：

```php
Process::assertRan(fn ($process, $result) =>
    $process->command === 'ls -la' &&
    $process->path === __DIR__ &&
    $process->timeout === 60
);
```

傳給 `assertRun` Closure 的 `$process` 是 `Illuminate\Process\PendingProcess` 的實體，而 `$result` 是 `Illuminate\Contracts\Process\ProcessResult` 的實體。

<a name="assert-process-didnt-run"></a>

#### assertDidntRun

判斷給定 Process 是否未被呼叫：

```php
use Illuminate\Support\Facades\Process;

Process::assertDidntRun('ls -la');
```

與 `assertRun` 方法類似，`assertDidntRun` 方法也可被傳入一個 Closure。傳給 `assertDidntRun` 的 Closure 會收到 Process 實體與 Process 的執行結果，讓你能檢查 Process 上的設定。若該 Closure 回傳 `true`，則該 Assertion 就會失敗 (Fail)：

```php
Process::assertDidntRun(fn (PendingProcess $process, ProcessResult $result) =>
    $process->command === 'ls -la'
);
```

<a name="assert-process-ran-times"></a>

#### assertRanTimes

判斷給定 Process 是否被呼叫了給定次數：

```php
use Illuminate\Support\Facades\Process;

Process::assertRanTimes('ls -la', times: 3);
```

也可傳入一個 Closure 給 `assertRanTimes` 方法。該 Closure 會收到 Process 的實體與 Process 的執行結果，讓你能檢查 Process 上的設定。若讓該 Closure 回傳 `true`，且該 Process 被呼叫了給定的次數，則該 Assertion 就會通過 (Pass)：

```php
Process::assertRanTimes(function (PendingProcess $process, ProcessResult $result) {
    return $process->command === 'ls -la';
}, times: 3);
```

<a name="preventing-stray-processes"></a>

### 避免漏掉的 Process

若想在個別測試或整個測試套件中，確保所有呼叫的 Process 都被模擬，則可呼叫 `preventStrayProcesses` 方法。呼叫該方法後，若某個 Process 沒有相對應的模擬結果，該 Process 就不會被執行，而會擲回一個 Exception：

    use Illuminate\Support\Facades\Process;
    
    Process::preventStrayProcesses();
    
    Process::fake([
        'ls *' => 'Test output...',
    ]);
    
    // 回傳模擬的輸出...
    Process::run('ls -la');
    
    // 擲回 Exception...
    Process::run('bash import.sh');
