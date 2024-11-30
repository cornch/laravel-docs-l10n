---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/97/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 46.69
---

# 日誌

- [簡介](#introduction)
- [設定](#configuration)
  - [可用的通道 Driver](#available-channel-drivers)
  - [通道的前置需求](#channel-prerequisites)
  - [記錄 Deprecation Warning](#logging-deprecation-warnings)
  
- [建立 Log Stack](#building-log-stacks)
- [撰寫 Log 訊息](#writing-log-messages)
  - [上下文資訊](#contextual-information)
  - [寫入特定通道](#writing-to-specific-channels)
  
- [自訂的 Monolog 通道](#monolog-channel-customization)
  - [為某個通道自訂 Monolog](#customizing-monolog-for-channels)
  - [建立 Monolog Handler 的通道](#creating-monolog-handler-channels)
  - [使用 Factory 建立自定通道](#creating-custom-channels-via-factories)
  

<a name="introduction"></a>

## 簡介

為了協助你瞭解程式中發生的大小事，Laravel 提供了強健的^[日誌](Log)服務，能讓你將訊息紀錄到檔案、系統^[錯誤日誌](Error Log)、甚至是紀錄到 Slack 中來通知整個團隊。

Laravel 的 Log 紀錄是基於「^[通道](Channel)」的。每個通道都代表了一種寫入日誌資訊的特定方法。舉例來說，`single` 通道將日誌寫進單一日誌檔中，而 `slack` 通道則將日誌訊息傳送到 Slack。也可以依據日誌的^[嚴重性](Severity)來將日誌訊息寫到多個通道。

在 Laravel 內部，我們使用了 [Monolog](https://github.com/Seldaek/monolog) 函式庫。Monolog 提供了多種強大的日誌^[處理程式](Handler)。Laravel 還讓我們能輕鬆地設定這些 Monolog 的日誌處理程式，可以混合使用不同處理程式來為我們的程式處理日誌。

<a name="configuration"></a>

## 設定

用於設定程式日誌行為的設定選項都放在 `config/logging.php` 設定檔中。我們可以使用這個檔案來設定專案的日誌通道，因此建議你瞭解一下各個可用的通道與其對應的選項。稍後我們會來討論一些常見的選項。

預設情況下，Laravel 會使用 `stack` 通道來紀錄日誌訊息。`stack` 通道可用來將多個日誌通道彙總到單一通道內。有關建立 Stack 的詳細資訊，請參考[下方的說明文件](#building-log-stacks)。

<a name="configuring-the-channel-name"></a>

#### 設定通道名稱

預設情況下，在初始化 Monologo 時，會使用目前環境的名稱來作為「通道名稱」，如 `production` 或 `local`。若要更改通道名稱，請在通道設定中加上 `name` 選項：

    'stack' => [
        'driver' => 'stack',
        'name' => 'channel-name',
        'channels' => ['single', 'slack'],
    ],
<a name="available-channel-drivers"></a>

### 可用的通道 Driver

每個日誌通道都以一個「Driver」驅動。Driver 會判斷要如何紀錄日誌訊息、以及要將日誌訊息紀錄到哪裡。在所有的 Laravel 應用程式中都可使用下列日誌通道 Driver。在你專案中的 `config/logging.php` 設定檔內已經預先填好下表中大部分的 Driver，因此我們建議你可瞭解一下這個檔案以熟悉其內容：

| 名稱 | 說明 |
| --- | --- |
| `custom` | 會呼叫特定 Factory 建立通道的 Driver |
| `daily` | 會每日^[重置](Rotate)之一個基於 `RotatingFileHandler` 的 Monolog Driver |
| `errorlog` | 基於 `ErrorLogHandler` 的 Monolog Driver |
| `monolog` | 可使用任意支援的 Monolog Handler 之 Monolog Factory Driver |
| `null` | 會取消所有 Log 訊息的 Driver |
| `papertrail` | 基於 `SyslogUdpHandler` 的 Monolog Driver |
| `single` | 基於單一檔案或路徑的 Logger 通道 (`StreamHandler`) |
| `slack` | 基於 `SlackWebhookHandler` 的 Monolog Driver |
| `stack` | 會建立「多通道」通道的包裝 |
| `syslog` | 基於 `SyslogHandler` 的 Monolog Driver |

> [!TIP]  
> 請閱讀[進階的通道客製化](#monolog-channel-customization)以瞭解更多有關 `monolog` 與 `custom` Driver 的資訊。

<a name="channel-prerequisites"></a>

### 通道的前置需求

<a name="configuring-the-single-and-daily-channels"></a>

#### 設定 Single 與 Daily 通道

`single` (單一) 與 `daily` (每日) 通道有三個可選的設定選項：`bubble`、`permission`、`locking`。

| 名稱 | 說明 | 預設 |
| --- | --- | --- |
| `bubble` | 代表該訊息被處理後是否應^[向上傳遞](Bubble)給其他通道 | `true` |
| `locking` | 在寫入 Log 檔前嘗試鎖定該檔案 | `false` |
| `permission` | Log 檔的權限 | `0644` |

<a name="configuring-the-papertrail-channel"></a>

#### 設定 Papertrail 通道

`papertrail` 通道有 `host` 與 `port` 兩個必填的設定選項。可以從 [Papertrail](https://help.papertrailapp.com/kb/configuration/configuring-centralized-logging-from-php-apps/#send-events-from-php-app) 上取得這些值。

<a name="configuring-the-slack-channel"></a>

#### 設定 Slack 通道

`slack` 通道有一個 `url` 必填設定選項。該 URL 應為在 Slack 團隊上設定之[傳入的 Webhook](https://slack.com/apps/A0F7XDUAZ-incoming-webhooks) URL。

預設情況下，Slack 只會接收等級為 `critical` 或以上的日誌。不過，也可以在 `config/logging.php` 設定檔中更改 Slack 日誌通道的 `level` 選項來調整要接收的等級。

<a name="logging-deprecation-warnings"></a>

### 紀錄 Deprecation Warning

PHP、Laravel、或是其他函式庫等，通常會通知使用者其部分功能^[已棄用](Deprecated)，且將在未來的版本中移除這些功能。若想收到這些棄用警告，可在 `config/logging.php` 設定檔中設定要用於記錄 `deprecations` 日誌的通道：

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
    
    'channels' => [
        ...
    ]
或者，也可以定義一個名為 `deprecations` 的日誌通道。若有該名稱的通道，Laravel 會使用該通道來紀錄 Deprecation 日誌：

    'channels' => [
        'deprecations' => [
            'driver' => 'single',
            'path' => storage_path('logs/php-deprecation-warnings.log'),
        ],
    ],
<a name="building-log-stacks"></a>

## 建立日誌 Stack

剛才也提到過，`stack` Driver 能讓我們將多個通道組合為單一日誌通道來更方便地使用。為了說明如何使用日誌的 ^[Stack](%E5%A0%86%E7%96%8A)，我們先來看看下面這個可能出現在正式專案中的範例設定檔：

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['syslog', 'slack'],
        ],
    
        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],
    
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],
    ],
讓我們來逐步分析這個設定檔。首先，可以注意到 `stack` 通道使用 `channels` 選項來彙總了另外兩個通道：`syslog` 與 `slack`。所以，在紀錄日誌訊息時，這兩個頻道都可能會去紀錄該訊息。不過，我們稍後會看到，實際上這兩個通道會依照訊息的嚴重程度 (「^[等級](Level)」) 來判斷是否要紀錄訊息。

<a name="log-levels"></a>

#### 日誌的等級

來看看上述範例中 `syslog` 與 `slack` 通道設定中的 `level` 設定。這個選項用來判斷該通道所要紀錄的最小訊息「^[等級](Level)」。Monolog —— 負責提供 Laravel Log 服務的函式庫 —— 提供了所有在 [RFC 5424 規格](https://tools.ietf.org/html/rfc5424)中定義的所有日誌等級：**emergency**, **alert**, **critical**, **error**, **warning**, **notice**, **info**, 與 **debug**。

所以，假設我們使用 `debug` 方法來紀錄訊息：

    Log::debug('An informational message.');
在我們的設定檔中，`syslog` 通道會將該訊息寫到^[系統日誌](System Log)中。不過，因為這個訊息不是 `critical` 或以上的等級，因此這個訊息不會被傳送到 Slack。不過，若我們紀錄 `emergency` 等級的訊息，則該訊息就會被送到系統日誌與 Slack 兩個地方，因為 `emergency` 等級大於我們為這兩個通道設定的最小等級門檻：

    Log::emergency('The system is down!');
<a name="writing-log-messages"></a>

## 寫入日誌訊息

可以使用 `Log` [Facade] 來將訊息寫入到日誌中。剛才也提到過，日誌程式提供了八個等級，這八個等級定義在 [RFC 5424 規格](https://tools.ietf.org/html/rfc5424)中：**emergency**, **alert**, **critical**, **error**, **warning**, **notice**, **info** 與 **debug**。

    use Illuminate\Support\Facades\Log;
    
    Log::emergency($message);
    Log::alert($message);
    Log::critical($message);
    Log::error($message);
    Log::warning($message);
    Log::notice($message);
    Log::info($message);
    Log::debug($message);
可以呼叫這些方法來以對應等級紀錄訊息。預設情況下，這些訊息會被寫入到 `logging` 設定檔中預設的日誌通道中。

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Models\User;
    use Illuminate\Support\Facades\Log;
    
    class UserController extends Controller
    {
        /**
         * Show the profile for the given user.
         *
         * @param  int  $id
         * @return \Illuminate\Http\Response
         */
        public function show($id)
        {
            Log::info('Showing the user profile for user: '.$id);
    
            return view('user.profile', [
                'user' => User::findOrFail($id)
            ]);
        }
    }
<a name="contextual-information"></a>

### 有上下文的資訊

可以傳入一組包含^[上下文資料](Contextual Data)的陣列給日誌方法。這些上下文資料會被格式化並與日誌訊息一起顯示：

    use Illuminate\Support\Facades\Log;
    
    Log::info('User failed to login.', ['id' => $user->id]);
有時候，我們可能會想讓某些上下文資訊被包含在接下來所有紀錄的日誌中。舉例來說，我們紀錄能關聯上連入 Request 的 Request ID。為此，可呼叫 `Log` Facade 的 `withContext` 方法：

    <?php
    
    namespace App\Http\Middleware;
    
    use Closure;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Str;
    
    class AssignRequestId
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Illuminate\Http\Request  $request
         * @param  \Closure  $next
         * @return mixed
         */
        public function handle($request, Closure $next)
        {
            $requestId = (string) Str::uuid();
    
            Log::withContext([
                'request-id' => $requestId
            ]);
    
            return $next($request)->header('Request-Id', $requestId);
        }
    }
<a name="writing-to-specific-channels"></a>

### 寫入指定的通道

有時候，我們可能會想將訊息寫到預設通道以外的其他通道。可以使用 `Log` Facade 上的 `channel` 方法來取得並紀錄到設定檔中定義的任何通道：

    use Illuminate\Support\Facades\Log;
    
    Log::channel('slack')->info('Something happened!');
若想視需要建立由多個通道組合成的日誌 Stack，可使用 `stack` 方法：

    Log::stack(['single', 'slack'])->info('Something happened!');
<a name="on-demand-channels"></a>

#### 視需要建立的通道

也可以在不寫入設定檔的情況下在^[執行階段](Runtime)視需要建立通道。要建立視需要時建立的通道，請傳入一個設定用陣列給 `Log` Facade 的 `build` 方法：

    use Illuminate\Support\Facades\Log;
    
    Log::build([
      'driver' => 'single',
      'path' => storage_path('logs/custom.log'),
    ])->info('Something happened!');
也可以在視需要建立的日誌 Stack 中包含一個視需要建立的通道。只要在傳給 `stack` 方法的陣列中包含一個視需要建立的通道實體即可：

    use Illuminate\Support\Facades\Log;
    
    $channel = Log::build([
      'driver' => 'single',
      'path' => storage_path('logs/custom.log'),
    ]);
    
    Log::stack(['slack', $channel])->info('Something happened!');
<a name="monolog-channel-customization"></a>

## 自訂 Monolog 通道

<a name="customizing-monolog-for-channels"></a>

### 為通道自訂 Monolog

有時候我們會因為現有通道而需要完整控制 Monolog 的設定方式。舉例來說，我們可能需要為 Laravel 的內建 `single` 通道設定自訂的 Monolog `FormatterInterface` 實作。

要開始自訂 Monolog，請在通道設定中定義一個 `tap` 陣列。`tap` 陣列中應包含一組要用來在 Monolog 實體建立完畢後自訂 (或「^[監聽](Tap)」進) Monologo 實體的類別。對於要將這些類別放在哪裡，Laravel 並沒有相關規範。因此，我們可以隨意在專案內建立目錄來放置這些類別：

    'single' => [
        'driver' => 'single',
        'tap' => [App\Logging\CustomizeFormatter::class],
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],
在通道上設定好 `tap` 選項後，就可以開始定義用來自訂 Monolog 實體的類別了。這個類別只需要有一個方法即可：`__invoke`。該方法會收到 `Illuminate\Log\Logger` 實體，該實體會將所有的方法呼叫代理到底層的 Monolog 實體：

    <?php
    
    namespace App\Logging;
    
    use Monolog\Formatter\LineFormatter;
    
    class CustomizeFormatter
    {
        /**
         * Customize the given logger instance.
         *
         * @param  \Illuminate\Log\Logger  $logger
         * @return void
         */
        public function __invoke($logger)
        {
            foreach ($logger->getHandlers() as $handler) {
                $handler->setFormatter(new LineFormatter(
                    '[%datetime%] %channel%.%level_name%: %message% %context% %extra%'
                ));
            }
        }
    }
> [!TIP]  
> 所有的「Tap」類別都會由 [Service Container](/docs/{{version}}/container) 解析，所以在 ^[Constructor](%E5%BB%BA%E6%A7%8B%E5%87%BD%E5%BC%8F) 中要求的相依性都會自動被插入。

<a name="creating-monolog-handler-channels"></a>

### 建立 Monolog Handler 通道

Monolog has a variety of [available handlers](https://github.com/Seldaek/monolog/tree/main/src/Monolog/Handler) and Laravel does not include a built-in channel for each one. In some cases, you may wish to create a custom channel that is merely an instance of a specific Monolog handler that does not have a corresponding Laravel log driver.  These channels can be easily created using the `monolog` driver.

使用 `monolog` Driver 時，`handler` 設定選項可用來指定要初始化哪個 Handler。然後，也可以選擇性地使用 `with` 設定選項來指定該 Handler 的 ^[Constructor](%E5%BB%BA%E6%A7%8B%E5%87%BD%E5%BC%8F) 所需要的參數：

    'logentries' => [
        'driver'  => 'monolog',
        'handler' => Monolog\Handler\SyslogUdpHandler::class,
        'with' => [
            'host' => 'my.logentries.internal.datahubhost.company.com',
            'port' => '10000',
        ],
    ],
<a name="monolog-formatters"></a>

#### Monolog 格式

使用 `monolog` Driver 時，會使用 Monolog 的 `LineFormatter` 來作為預設的^[格式化工具](Formatter)。不過，我們也可以使用 `formatter` 與 `formatter_with` 設定選項來自訂要傳給該 Handler 的格式化工具：

    'browser' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\BrowserConsoleHandler::class,
        'formatter' => Monolog\Formatter\HtmlFormatter::class,
        'formatter_with' => [
            'dateFormat' => 'Y-m-d',
        ],
    ],
若使用的 Monolog Handler 本身就有提供格式化工具，則可以將 `formatter` 設定選項設為 `default`：

    'newrelic' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\NewRelicHandler::class,
        'formatter' => 'default',
    ],
<a name="creating-custom-channels-via-factories"></a>

### 使用 Factory 來建立自訂通道

若想定義整個自訂通道來完整控制 Monolog 的初始化與設定，則可在 `config/logging.php` 設定檔中使用 `custom` Driver。設定中應包含一個 `via` 選項來包含建立 Monolog 實體時要叫用的 ^[Factory](%E5%B7%A5%E5%BB%A0) 類別名稱：

    'channels' => [
        'example-custom-channel' => [
            'driver' => 'custom',
            'via' => App\Logging\CreateCustomLogger::class,
        ],
    ],
設定好 `custom` Driver 通道後，就可以開始定義用來建立 Monolog 實體的類別了。這個類別只需要有一個 `__invoke` 方法就好了，該方法應回傳 Monolog ^[Logger](%E6%97%A5%E8%AA%8C%E7%A8%8B%E5%BC%8F)的實體。`__invoke` 方法會收到一個引數，即為該通道的設定陣列：

    <?php
    
    namespace App\Logging;
    
    use Monolog\Logger;
    
    class CreateCustomLogger
    {
        /**
         * Create a custom Monolog instance.
         *
         * @param  array  $config
         * @return \Monolog\Logger
         */
        public function __invoke(array $config)
        {
            return new Logger(...);
        }
    }