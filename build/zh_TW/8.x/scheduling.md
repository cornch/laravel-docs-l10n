---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/147/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 45.96
---

# 任務排程

- [簡介](#introduction)
- [定義排程](#defining-schedules)
  - [排程執行 Artisan 指令](#scheduling-artisan-commands)
  - [排程執行放入佇列的 Job](#scheduling-queued-jobs)
  - [排程執行 Shell 指令](#scheduling-shell-commands)
  - [排程的頻率選項](#schedule-frequency-options)
  - [時區](#timezones)
  - [防止排程任務重疊](#preventing-task-overlaps)
  - [在單一伺服器上執行任務](#running-tasks-on-one-server)
  - [背景任務](#background-tasks)
  - [維護模式](#maintenance-mode)
  
- [執行排程程式](#running-the-scheduler)
  - [在本機上執行排程程式](#running-the-scheduler-locally)
  
- [任務的輸出](#task-output)
- [任務的 Hook](#task-hooks)
- [事件](#events)

<a name="introduction"></a>

## 簡介

以前，我們需要在伺服器上為每個需要排程執行的任務撰寫 Cron 設定。不過，手動設定 Cron 很快就會變得很麻煩，因為這些排程任務不在版本控制裡面，而且我們必須要 SSH 連進伺服器上才能檢視現有的 Cron 項目以及新增新項目。

Laravel 的^[指令排程程式](Command Scheduler)提供了一種全新的方法來在伺服器上管理排程任務。Laravel 的排程程式能讓我們使用流暢與表達性的方法來在 Laravel 專案中定義指令排程。使用 Laravel 的排程程式時，我們只需要在伺服器上設定一個 Cron 項目即可。任務的排程定義在 `app/Console/Kernel.php` 檔案的 `schedule` 方法內。在該方法中已經有定義好了一個簡單的範例設定，可幫助讀者入門。

<a name="defining-schedules"></a>

## 定義排程

我們可以在專案的 `App\Console\Kernel` 類別中 `schedule` 方法內定義所有的排程任務。我們先來看一個入門的範例。在這個範例中，我們會排程在每天午夜呼叫一個閉包。在這個閉包中，我們執行一條資料庫查詢來清除資料表：

    <?php
    
    namespace App\Console;
    
    use Illuminate\Console\Scheduling\Schedule;
    use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
    use Illuminate\Support\Facades\DB;
    
    class Kernel extends ConsoleKernel
    {
        /**
         * Define the application's command schedule.
         *
         * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
         * @return void
         */
        protected function schedule(Schedule $schedule)
        {
            $schedule->call(function () {
                DB::table('recent_users')->delete();
            })->daily();
        }
    }
除了使用閉包來排程以外，也可以排程執行 [可 Invoke 的物件](https://secure.php.net/manual/en/language.oop5.magic.php#object.invoke)。可 Invoke 的物件只是一個包含 `__invoke` 方法的普通 PHP 類別：

    $schedule->call(new DeleteRecentUsers)->daily();
若想檢視目前排程任務的概覽，以及各個任務下次排定的執行時間，可使用 `schedule:list` Artisan 指令：

```nothing
php artisan schedule:list
```
<a name="scheduling-artisan-commands"></a>

### 排程執行 Artisan 指令

除了排程執行閉包外，也可以排程執行 [Artisan 指令](/docs/{{version}}/artisan)與系統指令。舉例來說，我們可以使用 `command` 方法來使用指令的名稱或類別名稱來排程執行 Artisan 指令。

若使用指令的類別名稱來排程執行 Artisan 指令時，可傳入一組包含額外指令列引數的陣列，在叫用該指令時會提供這些引數：

    use App\Console\Commands\SendEmailsCommand;
    
    $schedule->command('emails:send Taylor --force')->daily();
    
    $schedule->command(SendEmailsCommand::class, ['Taylor', '--force'])->daily();
<a name="scheduling-queued-jobs"></a>

### 排程執行放入佇列的 Job

可使用 `job` 方法來排程執行[放入佇列的 Job](/docs/{{version}}/queues)。該方法提供了一個方便的方法能讓我們能排程執行放入佇列的 Job，而不需使用 `call` 方法來定義將該 Job 放入佇列的閉包：

    use App\Jobs\Heartbeat;
    
    $schedule->job(new Heartbeat)->everyFiveMinutes();
`job` 還有可選的第二個引數與第三個引數，可用來指定該 Job 要使用的佇列名稱與佇列連線：

    use App\Jobs\Heartbeat;
    
    // Dispatch the job to the "heartbeats" queue on the "sqs" connection...
    $schedule->job(new Heartbeat, 'heartbeats', 'sqs')->everyFiveMinutes();
<a name="scheduling-shell-commands"></a>

### 排程執行 Shell 指令

可使用 `exec` 指令來在作業系統上執行指令：

    $schedule->exec('node /home/forge/script.js')->daily();
<a name="schedule-frequency-options"></a>

### 排程的頻率選項

我們已經看到了一些在指定間隔間執行任務的範例。不過，還有其他許多用來指派給任務的排程頻率：

| 方法 | 說明 |
| --- | --- |
| `->cron('* * * * *');` | 在自定的 Cron 排程上執行任務 |
| `->everyMinute();` | 每分鐘執行任務 |
| `->everyTwoMinutes();` | 每 2 分鐘執行任務 |
| `->everyThreeMinutes();` | 每 3 分鐘執行任務 |
| `->everyFourMinutes();` | 每 4 分鐘執行任務 |
| `->everyFiveMinutes();` | 每 5 分鐘執行任務 |
| `->everyTenMinutes();` | 每 10 分鐘執行任務 |
| `->everyFifteenMinutes();` | 每 15 分鐘執行任務 |
| `->everyThirtyMinutes();` | 每 30 分鐘執行任務 |
| `->hourly();` | 每小時執行任務 |
| `->hourlyAt(17);` | 每小時的第 17 分鐘執行任務 |
| `->everyTwoHours();` | 每 2 小時執行任務 |
| `->everyThreeHours();` | 每 3 小時執行任務 |
| `->everyFourHours();` | 每 4 小時執行任務 |
| `->everySixHours();` | 每 6 小時執行任務 |
| `->daily();` | 每當午夜時執行任務 |
| `->dailyAt('13:00');` | 每天 13:00 執行任務 |
| `->twiceDaily(1, 13);` | 每天的 1:00 與 13:00 執行任務 |
| `->weekly();` | 每週日 00:00 執行任務 |
| `->weeklyOn(1, '8:00');` | 每週一 8:00 執行任務 |
| `->monthly();` | 每月 1 號的 00:00 執行任務 |
| `->monthlyOn(4, '15:00');` | 每個月 4 號的 15:00 執行任務 |
| `->twiceMonthly(1, 16, '13:00');` | 每個月的 1 號與 16 號的 13:00 執行任務 |
| `->lastDayOfMonth('15:00');` | 每個月最後一天的 15:00 執行該任務 |
| `->quarterly();` | 每一季第一天的 00:00 執行該任務 |
| `->yearly();` | 每年第一天的 00:00 執行該任務 |
| `->yearlyOn(6, 1, '17:00');` | 每年 6 月 1 日的 17:00 執行該任務 |
| `->timezone('America/New_York');` | 為給任務設定時區 |

可以組合使用這些方法來增加額外的條件限制，以設定更精確的排程，如在每週某日時執行任務。舉例來說，我們可以排程每週一執行某個指令：

    // Run once per week on Monday at 1 PM...
    $schedule->call(function () {
        //
    })->weekly()->mondays()->at('13:00');
    
    // Run hourly from 8 AM to 5 PM on weekdays...
    $schedule->command('foo')
              ->weekdays()
              ->hourly()
              ->timezone('America/Chicago')
              ->between('8:00', '17:00');
下表中列出了其他額外的排程條件限制：

| 方法 | 說明 |
| --- | --- |
| `->weekdays();` | 顯示該任務只在工作日執行 |
| `->weekends();` | 顯示該任務只在假日執行 |
| `->sundays();` | 顯示該任務只在週日執行 |
| `->mondays();` | 顯示該任務只在週一執行 |
| `->tuesdays();` | 顯示該任務只在週二執行 |
| `->wednesdays();` | 顯示該任務只在週三執行 |
| `->thursdays();` | 顯示該任務只在週四執行 |
| `->fridays();` | 顯示該任務只在週五執行 |
| `->saturdays();` | 顯示該任務只在週六執行 |
| `->days(array|mixed);` | 顯示該任務只在特定日執行 |
| `->between($startTime, $endTime);` | 限制任務只在開始時間 (`$startTime`) 至結束時間 (`$endTime`) 間執行 |
| `->unlessBetween($startTime, $endTime);` | 限制任務不要在開始時間 (`$startTime`) 至結束時間 (`$endTime`) 間執行 |
| `->when(Closure);` | 使用給定的真值條件測試來限制任務 |
| `->environments($env);` | 限制任務只在特定條件上執行 |

<a name="day-constraints"></a>

#### 「日」的條件限制

可使用 `days` 方法來限制任務只在每週的某幾天時執行。舉例來說，我們可以排程執行每週日至週三的每小時執行某個指令：

    $schedule->command('emails:send')
                    ->hourly()
                    ->days([0, 3]);
或者，我們也可以使用 `Illuminate\Console\Scheduling\Schedule` 類別中所提供的常數來定義任務要在哪幾天執行：

    use Illuminate\Console\Scheduling\Schedule;
    
    $schedule->command('emails:send')
                    ->hourly()
                    ->days([Schedule::SUNDAY, Schedule::WEDNESDAY]);
<a name="between-time-constraints"></a>

#### 時間區間的條件限制

`between` 方法可使用一天中指定的時間來限制任務的執行：

    $schedule->command('emails:send')
                        ->hourly()
                        ->between('7:00', '22:00');
類似地，`unlessBetween` 方法可用讓任務在某一段時間內不要執行：

    $schedule->command('emails:send')
                        ->hourly()
                        ->unlessBetween('23:00', '4:00');
<a name="truth-test-constraints"></a>

#### 真值條件測試的條件顯示

`when` 方法可用來依據給定真值測試的結果來限制任務的執行。換句話說，若給定的閉包回傳 `true`，除非有其他的條件阻止該任務執行，否則就會執行該任務：

    $schedule->command('emails:send')->daily()->when(function () {
        return true;
    });
`skip` 方法相當於 `when` 的相反。若 `skip` 方法回傳 `true`，則排程執行的任務將不被執行：

    $schedule->command('emails:send')->daily()->skip(function () {
        return true;
    });
串接使用 `when` 方法時，只有在 `when` 條件為 `true` 時排程的任務才會被執行。

<a name="environment-constraints"></a>

#### 環境的條件限制

使用 `environments` 方法即可讓該方法只在給定的環境上執行 (即 `APP_ENV` [環境變數](/docs/{{version}}/configuration#environment-configuration) 中所定義的)：

    $schedule->command('emails:send')
                ->daily()
                ->environments(['staging', 'production']);
<a name="timezones"></a>

### 時區

使用 `timezone` 方法，就可以指定要使用給定的時區來解析排程任務的時間：

    $schedule->command('report:generate')
             ->timezone('America/New_York')
             ->at('2:00')
若所有的排程任務都要指派相同的時區，則可在 `App\Console\Kernel` 類別中定義 `scheduleTimezone` 方法。該方法應回傳要指派給所有排程任務的預設時區：

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'America/Chicago';
    }
> [!NOTE]  
> 請注意，某些時區會使用日光節約時間。若發生日光節約時間，則某些排程任務可能會執行兩次、甚至是執行多次。因此，我們建議儘可能不要在排程上設定時區。

<a name="preventing-task-overlaps"></a>

### 避免任務重疊

預設情況下，就算之前的任務實體還在執行，也會繼續執行排程的任務。若要避免任務重疊，可使用 `withoutOverlapping` 方法：

    $schedule->command('emails:send')->withoutOverlapping();
在這個範例中，若目前沒有在執行 `emails:send` [Artisan 指令](/docs/{{version}}/artisan)，則該指令每分鐘都會執行。若任務會執行非常久的時間，因而無法預期任務要執行多久，就適合使用 `withoutOverlapping` 方法。

若有需要，可指定「withoutOverlapping」的 Lock 最少要過多久才算逾期。預設情況下，該 Lock 會在 24 小時候逾期：

    $schedule->command('emails:send')->withoutOverlapping(10);
<a name="running-tasks-on-one-server"></a>

### 在單一伺服器上執行任務

> [!NOTE]  
> 若要使用此功能，則專案必須使用 `memcached`、`redis`、`dynamodb`、`database`、`file`、`array` 等其中一個快取 Driver 作為專案的預設快取 Driver。另外，所有的伺服器都必須要連線至相同的中央快取伺服器。

若專案的排程程式在多個伺服器上執行，則可限制排程任務只在單一伺服器上執行。舉例來說，假設我們設定了一個排程任務，每週五晚上會產生新報表。若任務排程程式在三個工作伺服器上執行，則這個排程任務會在這三台伺服器上都執行，且會產生三次報表。這可不好！

若要讓任務只在單一伺服器上執行，可在定義排程任務的時候使用 `onOneServer` 方法。第一個取得該任務的伺服器會先在該 Job 上確保一個 Atomic Lock，以防止其他伺服器在同一時間執行相同的任務：

    $schedule->command('report:generate')
                    ->fridays()
                    ->at('17:00')
                    ->onOneServer();
<a name="background-tasks"></a>

### 背景任務

預設情況下，排程在同一個時間的多個任務會依照 `schedule` 方法中定義的順序依序執行。若任務的執行時間很長，則可能會導致接下來的任務比預期的時間還要完開始執行。若想讓任務在背景處理以同步執行多個任務，可使用 `runInBackground` 方法：

    $schedule->command('analytics:report')
             ->daily()
             ->runInBackground();
> [!NOTE]  
> `runInBackground` 方法只可用在 `command` 與 `exec` 方法所定義的排程任務上。

<a name="maintenance-mode"></a>

### 維護模式

若網站目前在[維護模式](/docs/{{version}}/configuration#maintenance-mode)下，則專案的排程任務可能不會執行，以避免任務影響伺服器上任何未完成的維護項目。不過，若仍想讓某個任務在維護模式中強制執行，可在定義任務時呼叫 `eventInMaintenanceMode` 方法：

    $schedule->command('emails:send')->evenInMaintenanceMode();
<a name="running-the-scheduler"></a>

## 執行排程程式

現在，我們已經學會了如何定義排程任務，我們來看看要怎麼樣在伺服器上真正執行這些任務。`schedule:run` Artisan 指令會取得所有的排程任務，並依照目前伺服器上的時間來判斷是否有需要執行這些任務。

因此，在使用 Laravel 的排程任務時，我們只需要在伺服器上新增單一一個 Cron 設定即可，該設定為每分鐘執行一次 `schedule:run` 指令。若讀者不知道如何在伺服器上新增 Cron 項目的話，可使用如 [Laravel Forge](https://forge.laravel.com) 這樣的服務來協助你管理 Cron 設定：

    * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
<a name="running-the-scheduler-locally"></a>

## 在本機執行排程程式

一般來說，我們不會在本機開發機上新增排程程式的 Cron 設定。在本機上，不需要新增 Cron 設定，我們可以使用 `schedule:work` Artisan 指令。該指令會在前景執行，並且會每分鐘叫用排程程式，直到手動停止該指令為止：

    php artisan schedule:work
<a name="task-output"></a>

## 任務的輸出

Laravel 的排程程式提供了數種便利的方法可處理排程任務產生的輸出。首先，可使用 `sendOutputTo` 方法來將輸出傳送至檔案中以在稍後檢視：

    $schedule->command('emails:send')
             ->daily()
             ->sendOutputTo($filePath);
若想將輸出附加到給定檔案最後，可使用 `appendOutputTo` 方法：

    $schedule->command('emails:send')
             ->daily()
             ->appendOutputTo($filePath);
若使用 `emailOutputTo` 方法，就可以將輸出以電子郵件傳送給指定的 E-Mail 位址。在將任務輸出以電子郵件寄出前，請先設定 Laravel 的[電子郵件服務](/docs/{{version}}/mail)：

    $schedule->command('report:generate')
             ->daily()
             ->sendOutputTo($filePath)
             ->emailOutputTo('taylor@example.com');
若只想在排程的 Artisan 或系統指令以結束代碼 0 以外的狀態退出時以電子郵件傳送輸出，可使用 `emailOutputOnFailure` 方法：

    $schedule->command('report:generate')
             ->daily()
             ->emailOutputOnFailure('taylor@example.com');
> [!NOTE]  
> `emailOutputTo`、`emailOutputOnFailure`、`sendOutputTo`、`appendOutputTo` 等方法只能在 `command` 與 `exec` 方法上使用。

<a name="task-hooks"></a>

## 任務的 Hook

使用 `before` 與 `after` 方法，即可指定要在排程任務執行前後執行的程式碼：

    $schedule->command('emails:send')
             ->daily()
             ->before(function () {
                 // The task is about to execute...
             })
             ->after(function () {
                 // The task has executed...
             });
使用 `onSuccess` 與 `onFailure` 方法，就可以指定要在排程任務成功或失敗時要執行的程式碼。「執行失敗」即為該排程的 Artisan 指令或系統指令以結束代碼 0 以外的代碼終止執行：

    $schedule->command('emails:send')
             ->daily()
             ->onSuccess(function () {
                 // The task succeeded...
             })
             ->onFailure(function () {
                 // The task failed...
             });
若指令有輸出，則可在 `after`、`onSuccess`、`onFailure` 等 Hook 上存取這些輸出。只需要在這些 Hook 的閉包定義上將 `$output` 引數型別提示為 `Illuminate\Support\Stringable` 即可：

    use Illuminate\Support\Stringable;
    
    $schedule->command('emails:send')
             ->daily()
             ->onSuccess(function (Stringable $output) {
                 // The task succeeded...
             })
             ->onFailure(function (Stringable $output) {
                 // The task failed...
             });
<a name="pinging-urls"></a>

#### Ping 網址

使用 `pingBefore` 與 `thenPing` 方法，就可讓排程程式在任務執行前後自動 Ping 給定的網址。該方法適合用來通知如 [Envoyer](https://envoyer.io) 等的外部服務該排程任務已開始執行或已執行完畢：

    $schedule->command('emails:send')
             ->daily()
             ->pingBefore($url)
             ->thenPing($url);
`pingBeforeIf` 與 `thenPingIf` 方法可用來只在給定條件為 `true` 時 Ping 給定的網址：

    $schedule->command('emails:send')
             ->daily()
             ->pingBeforeIf($condition, $url)
             ->thenPingIf($condition, $url);
`pingOnSuccess` 與 `pingOnFailure` 方法可用來只在任務執行成功或執行失敗時 Ping 給定的網址。「執行失敗」即為該排程的 Artisan 指令或系統指令以結束代碼 0 以外的代碼終止執行：

    $schedule->command('emails:send')
             ->daily()
             ->pingOnSuccess($successUrl)
             ->pingOnFailure($failureUrl);
所有的 Ping 方法都需要使用 Guzzle HTTP 函式庫。一般來說新安裝的 Laravel 專案都已預裝 Guzzle。若有不小心將該套件移除則可能需要手動使用 Composer 套件管理員來將 Guzzle 安裝到專案中：

    composer require guzzlehttp/guzzle
<a name="events"></a>

## 事件

若有需要，可監聽排程程式所分派的的[事件](/docs/{{version}}/events)。一般來說，事件的監聽程式映射應定義在專案的 `App\Providers\EventServiceProvider` 類別中：

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Console\Events\ScheduledTaskStarting' => [
            'App\Listeners\LogScheduledTaskStarting',
        ],
    
        'Illuminate\Console\Events\ScheduledTaskFinished' => [
            'App\Listeners\LogScheduledTaskFinished',
        ],
    
        'Illuminate\Console\Events\ScheduledBackgroundTaskFinished' => [
            'App\Listeners\LogScheduledBackgroundTaskFinished',
        ],
    
        'Illuminate\Console\Events\ScheduledTaskSkipped' => [
            'App\Listeners\LogScheduledTaskSkipped',
        ],
    
        'Illuminate\Console\Events\ScheduledTaskFailed' => [
            'App\Listeners\LogScheduledTaskFailed',
        ],
    ];