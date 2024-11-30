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
  - [Running Tasks on One Server](#running-tasks-on-one-server)
  - [背景任務](#background-tasks)
  - [維護模式](#maintenance-mode)
  - [Schedule Groups](#schedule-groups)
  
- [Running the Scheduler](#running-the-scheduler)
  - [Sub-Minute Scheduled Tasks](#sub-minute-scheduled-tasks)
  - [Running the Scheduler Locally](#running-the-scheduler-locally)
  
- [任務的輸出](#task-output)
- [任務的 Hook](#task-hooks)
- [事件](#events)

<a name="introduction"></a>

## 簡介

以前，我們需要在伺服器上為每個需要排程執行的任務撰寫 Cron 設定。不過，手動設定 Cron 很快就會變得很麻煩，因為這些排程任務不在版本控制裡面，而且我們必須要 SSH 連進伺服器上才能檢視現有的 Cron 項目以及新增新項目。

Laravel's command scheduler offers a fresh approach to managing scheduled tasks on your server. The scheduler allows you to fluently and expressively define your command schedule within your Laravel application itself. When using the scheduler, only a single cron entry is needed on your server. Your task schedule is typically defined in your application's `routes/console.php` file.

<a name="defining-schedules"></a>

## 定義排程

You may define all of your scheduled tasks in your application's `routes/console.php` file. To get started, let's take a look at an example. In this example, we will schedule a closure to be called every day at midnight. Within the closure we will execute a database query to clear a table:

    <?php
    
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schedule;
    
    Schedule::call(function () {
        DB::table('recent_users')->delete();
    })->daily();
除了使用閉包來排程以外，也可以排程執行 [可 Invoke 的物件](https://secure.php.net/manual/en/language.oop5.magic.php#object.invoke)。可 Invoke 的物件只是一個包含 `__invoke` 方法的普通 PHP 類別：

    Schedule::call(new DeleteRecentUsers)->daily();
If you prefer to reserve your `routes/console.php` file for command definitions only, you may use the `withSchedule` method in your application's `bootstrap/app.php` file to define your scheduled tasks. This method accepts a closure that receives an instance of the scheduler:

    use Illuminate\Console\Scheduling\Schedule;
    
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(new DeleteRecentUsers)->daily();
    })
若想檢視目前排程任務的概覽，以及各個任務下次排定的執行時間，可使用 `schedule:list` Artisan 指令：

```bash
php artisan schedule:list
```
<a name="scheduling-artisan-commands"></a>

### 排程執行 Artisan 指令

除了排程執行閉包外，也可以排程執行 [Artisan 指令](/docs/{{version}}/artisan)與系統指令。舉例來說，我們可以使用 `command` 方法來使用指令的名稱或類別名稱來排程執行 Artisan 指令。

若使用指令的類別名稱來排程執行 Artisan 指令時，可傳入一組包含額外指令列引數的陣列，在叫用該指令時會提供這些引數：

    use App\Console\Commands\SendEmailsCommand;
    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('emails:send Taylor --force')->daily();
    
    Schedule::command(SendEmailsCommand::class, ['Taylor', '--force'])->daily();
<a name="scheduling-artisan-closure-commands"></a>

#### Scheduling Artisan Closure Commands

If you want to schedule an Artisan command defined by a closure, you may chain the scheduling related methods after the command's definition:

    Artisan::command('delete:recent-users', function () {
        DB::table('recent_users')->delete();
    })->purpose('Delete recent users')->daily();
If you need to pass arguments to the closure command, you may provide them to the `schedule` method:

    Artisan::command('emails:send {user} {--force}', function ($user) {
        // ...
    })->purpose('Send emails to the specified user')->schedule(['Taylor', '--force'])->daily();
<a name="scheduling-queued-jobs"></a>

### 排程執行放入佇列的 Job

可使用 `job` 方法來排程執行[放入佇列的 Job](/docs/{{version}}/queues)。該方法提供了一個方便的方法能讓我們能排程執行放入佇列的 Job，而不需使用 `call` 方法來定義將該 Job 放入佇列的閉包：

    use App\Jobs\Heartbeat;
    use Illuminate\Support\Facades\Schedule;
    
    Schedule::job(new Heartbeat)->everyFiveMinutes();
`job` 還有可選的第二個引數與第三個引數，可用來指定該 Job 要使用的佇列名稱與佇列連線：

    use App\Jobs\Heartbeat;
    use Illuminate\Support\Facades\Schedule;
    
    // Dispatch the job to the "heartbeats" queue on the "sqs" connection...
    Schedule::job(new Heartbeat, 'heartbeats', 'sqs')->everyFiveMinutes();
<a name="scheduling-shell-commands"></a>

### 排程執行 Shell 指令

可使用 `exec` 指令來在作業系統上執行指令：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::exec('node /home/forge/script.js')->daily();
<a name="schedule-frequency-options"></a>

### 排程的頻率選項

我們已經看到了一些在指定間隔間執行任務的範例。不過，還有其他許多用來指派給任務的排程頻率：

<div class="overflow-auto">
| 方法 | 說明 |
| --- | --- |
| `->cron('* * * * *');` | Run the task on a custom cron schedule. |
| `->everySecond();` | Run the task every second. |
| `->everyTwoSeconds();` | Run the task every two seconds. |
| `->everyFiveSeconds();` | Run the task every five seconds. |
| `->everyTenSeconds();` | Run the task every ten seconds. |
| `->everyFifteenSeconds();` | Run the task every fifteen seconds. |
| `->everyTwentySeconds();` | Run the task every twenty seconds. |
| `->everyThirtySeconds();` | Run the task every thirty seconds. |
| `->everyMinute();` | Run the task every minute. |
| `->everyTwoMinutes();` | Run the task every two minutes. |
| `->everyThreeMinutes();` | Run the task every three minutes. |
| `->everyFourMinutes();` | Run the task every four minutes. |
| `->everyFiveMinutes();` | Run the task every five minutes. |
| `->everyTenMinutes();` | Run the task every ten minutes. |
| `->everyFifteenMinutes();` | Run the task every fifteen minutes. |
| `->everyThirtyMinutes();` | Run the task every thirty minutes. |
| `->hourly();` | Run the task every hour. |
| `->hourlyAt(17);` | Run the task every hour at 17 minutes past the hour. |
| `->everyOddHour($minutes = 0);` | Run the task every odd hour. |
| `->everyTwoHours($minutes = 0);` | Run the task every two hours. |
| `->everyThreeHours($minutes = 0);` | Run the task every three hours. |
| `->everyFourHours($minutes = 0);` | Run the task every four hours. |
| `->everySixHours($minutes = 0);` | Run the task every six hours. |
| `->daily();` | Run the task every day at midnight. |
| `->dailyAt('13:00');` | Run the task every day at 13:00. |
| `->twiceDaily(1, 13);` | Run the task daily at 1:00 & 13:00. |
| `->twiceDailyAt(1, 13, 15);` | Run the task daily at 1:15 & 13:15. |
| `->weekly();` | Run the task every Sunday at 00:00. |
| `->weeklyOn(1, '8:00');` | Run the task every week on Monday at 8:00. |
| `->monthly();` | Run the task on the first day of every month at 00:00. |
| `->monthlyOn(4, '15:00');` | Run the task every month on the 4th at 15:00. |
| `->twiceMonthly(1, 16, '13:00');` | Run the task monthly on the 1st and 16th at 13:00. |
| `->lastDayOfMonth('15:00');` | Run the task on the last day of the month at 15:00. |
| `->quarterly();` | Run the task on the first day of every quarter at 00:00. |
| `->quarterlyOn(4, '14:00');` | Run the task every quarter on the 4th at 14:00. |
| `->yearly();` | Run the task on the first day of every year at 00:00. |
| `->yearlyOn(6, 1, '17:00');` | Run the task every year on June 1st at 17:00. |
| `->timezone('America/New_York');` | Set the timezone for the task. |

</div>
可以組合使用這些方法來增加額外的條件限制，以設定更精確的排程，如在每週某日時執行任務。舉例來說，我們可以排程每週一執行某個指令：

    use Illuminate\Support\Facades\Schedule;
    
    // Run once per week on Monday at 1 PM...
    Schedule::call(function () {
        // ...
    })->weekly()->mondays()->at('13:00');
    
    // Run hourly from 8 AM to 5 PM on weekdays...
    Schedule::command('foo')
              ->weekdays()
              ->hourly()
              ->timezone('America/Chicago')
              ->between('8:00', '17:00');
下表中列出了其他額外的排程條件限制：

<div class="overflow-auto">
| 方法 | 說明 |
| --- | --- |
| `->weekdays();` | Limit the task to weekdays. |
| `->weekends();` | Limit the task to weekends. |
| `->sundays();` | Limit the task to Sunday. |
| `->mondays();` | Limit the task to Monday. |
| `->tuesdays();` | Limit the task to Tuesday. |
| `->wednesdays();` | Limit the task to Wednesday. |
| `->thursdays();` | Limit the task to Thursday. |
| `->fridays();` | Limit the task to Friday. |
| `->saturdays();` | Limit the task to Saturday. |
| `->days(array|mixed);` | Limit the task to specific days. |
| `->between($startTime, $endTime);` | Limit the task to run between start and end times. |
| `->unlessBetween($startTime, $endTime);` | Limit the task to not run between start and end times. |
| `->when(Closure);` | Limit the task based on a truth test. |
| `->environments($env);` | Limit the task to specific environments. |

</div>
<a name="day-constraints"></a>

#### 「日」的條件限制

可使用 `days` 方法來限制任務只在每週的某幾天時執行。舉例來說，我們可以排程執行每週日至週三的每小時執行某個指令：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('emails:send')
                    ->hourly()
                    ->days([0, 3]);
或者，我們也可以使用 `Illuminate\Console\Scheduling\Schedule` 類別中所提供的常數來定義任務要在哪幾天執行：

    use Illuminate\Support\Facades;
    use Illuminate\Console\Scheduling\Schedule;
    
    Facades\Schedule::command('emails:send')
                    ->hourly()
                    ->days([Schedule::SUNDAY, Schedule::WEDNESDAY]);
<a name="between-time-constraints"></a>

#### 時間區間的條件限制

`between` 方法可使用一天中指定的時間來限制任務的執行：

    Schedule::command('emails:send')
                        ->hourly()
                        ->between('7:00', '22:00');
類似地，`unlessBetween` 方法可用讓任務在某一段時間內不要執行：

    Schedule::command('emails:send')
                        ->hourly()
                        ->unlessBetween('23:00', '4:00');
<a name="truth-test-constraints"></a>

#### 真值條件測試的條件顯示

`when` 方法可用來依據給定真值測試的結果來限制任務的執行。換句話說，若給定的閉包回傳 `true`，除非有其他的條件阻止該任務執行，否則就會執行該任務：

    Schedule::command('emails:send')->daily()->when(function () {
        return true;
    });
`skip` 方法相當於 `when` 的相反。若 `skip` 方法回傳 `true`，則排程執行的任務將不被執行：

    Schedule::command('emails:send')->daily()->skip(function () {
        return true;
    });
串接使用 `when` 方法時，只有在 `when` 條件為 `true` 時排程的任務才會被執行。

<a name="environment-constraints"></a>

#### 環境的條件限制

使用 `environments` 方法即可讓該方法只在給定的環境上執行 (即 `APP_ENV` [環境變數](/docs/{{version}}/configuration#environment-configuration) 中所定義的)：

    Schedule::command('emails:send')
                ->daily()
                ->environments(['staging', 'production']);
<a name="timezones"></a>

### 時區

使用 `timezone` 方法，就可以指定要使用給定的時區來解析排程任務的時間：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('report:generate')
             ->timezone('America/New_York')
             ->at('2:00')
If you are repeatedly assigning the same timezone to all of your scheduled tasks, you can specify which timezone should be assigned to all schedules by defining a `schedule_timezone` option within your application's `app` configuration file:

    'timezone' => env('APP_TIMEZONE', 'UTC'),
    
    'schedule_timezone' => 'America/Chicago',
> [!WARNING]  
> 請注意，某些時區會使用日光節約時間。若發生日光節約時間，則某些排程任務可能會執行兩次、甚至是執行多次。因此，我們建議儘可能不要在排程上設定時區。

<a name="preventing-task-overlaps"></a>

### 避免任務重疊

預設情況下，就算之前的任務實體還在執行，也會繼續執行排程的任務。若要避免任務重疊，可使用 `withoutOverlapping` 方法：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('emails:send')->withoutOverlapping();
在這個範例中，若目前沒有在執行 `emails:send` [Artisan 指令](/docs/{{version}}/artisan)，則該指令每分鐘都會執行。若任務會執行非常久的時間，因而無法預期任務要執行多久，就適合使用 `withoutOverlapping` 方法。

若有需要，可指定「withoutOverlapping」的 Lock 最少要過多久才算逾期。預設情況下，該 Lock 會在 24 小時候逾期：

    Schedule::command('emails:send')->withoutOverlapping(10);
其實，`withoutOverlapping` 方法會使用專案的 [Cache](/docs/{{version}}/cache) 來取得鎖定。若有需要的話，可以使用 `schedule:clear-cache` Artisan 指令來清除這些快取鎖定。通常只有在因為未預期的伺服器問題而導致任務當掉時才需要這麼做。

<a name="running-tasks-on-one-server"></a>

### Running Tasks on One Server

> [!WARNING]  
> 若要使用此功能，則專案必須使用 `memcached`、`redis`、`dynamodb`、`database`、`file`、`array` 等其中一個快取 Driver 作為專案的預設快取 Driver。另外，所有的伺服器都必須要連線至相同的中央快取伺服器。

若專案的排程程式在多個伺服器上執行，則可限制排程任務只在單一伺服器上執行。舉例來說，假設我們設定了一個排程任務，每週五晚上會產生新報表。若任務排程程式在三個工作伺服器上執行，則這個排程任務會在這三台伺服器上都執行，且會產生三次報表。這可不好！

若要讓任務只在單一伺服器上執行，可在定義排程任務的時候使用 `onOneServer` 方法。第一個取得該任務的伺服器會先在該 Job 上確保一個 Atomic Lock，以防止其他伺服器在同一時間執行相同的任務：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('report:generate')
                    ->fridays()
                    ->at('17:00')
                    ->onOneServer();
<a name="naming-unique-jobs"></a>

#### 為單一伺服器 Job 命名

有時候，我們需要將排程分派相同的 Job，但使用不同的參數，且又要讓 Laravel 能在單一伺服器上以不同的兩種參數來執行這個 Job。這時，可以使用 `name` 方法來為各個排程定義指定一個不重複的名稱：

```php
Schedule::job(new CheckUptime('https://laravel.com'))
            ->name('check_uptime:laravel.com')
            ->everyFiveMinutes()
            ->onOneServer();

Schedule::job(new CheckUptime('https://vapor.laravel.com'))
            ->name('check_uptime:vapor.laravel.com')
            ->everyFiveMinutes()
            ->onOneServer();
```
類似地，若要在單一伺服器上執行排程的閉包，也必須為這些閉包指定名稱：

```php
Schedule::call(fn () => User::resetApiRequestCount())
    ->name('reset-api-request-count')
    ->daily()
    ->onOneServer();
```
<a name="background-tasks"></a>

### 背景任務

預設情況下，排程在同一個時間的多個任務會依照 `schedule` 方法中定義的順序依序執行。若任務的執行時間很長，則可能會導致接下來的任務比預期的時間還要完開始執行。若想讓任務在背景處理以同步執行多個任務，可使用 `runInBackground` 方法：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('analytics:report')
             ->daily()
             ->runInBackground();
> [!WARNING]  
> `runInBackground` 方法只可用在 `command` 與 `exec` 方法所定義的排程任務上。

<a name="maintenance-mode"></a>

### 維護模式

若網站目前在[維護模式](/docs/{{version}}/configuration#maintenance-mode)下，則專案的排程任務可能不會執行，以避免任務影響伺服器上任何未完成的維護項目。不過，若仍想讓某個任務在維護模式中強制執行，可在定義任務時呼叫 `eventInMaintenanceMode` 方法：

    Schedule::command('emails:send')->evenInMaintenanceMode();
<a name="schedule-groups"></a>

### Schedule Groups

When defining multiple scheduled tasks with similar configurations, you can use Laravel’s task grouping feature to avoid repeating the same settings for each task. Grouping tasks simplifies your code and ensures consistency across related tasks.

To create a group of scheduled tasks, invoke the desired task configuration methods, followed by the `group` method. The `group` method accepts a closure that is responsible for defining the tasks that share the specified configuration:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::daily()
    ->onOneServer()
    ->timezone('America/New_York')
    ->group(function () {
        Schedule::command('emails:send --force');
        Schedule::command('emails:prune');
    });
```
<a name="running-the-scheduler"></a>

## Running the Scheduler

現在，我們已經學會了如何定義排程任務，我們來看看要怎麼樣在伺服器上真正執行這些任務。`schedule:run` Artisan 指令會取得所有的排程任務，並依照目前伺服器上的時間來判斷是否有需要執行這些任務。

因此，在使用 Laravel 的排程任務時，我們只需要在伺服器上新增單一一個 Cron 設定即可，該設定為每分鐘執行一次 `schedule:run` 指令。若讀者不知道如何在伺服器上新增 Cron 項目的話，可使用如 [Laravel Forge](https://forge.laravel.com) 這樣的服務來協助你管理 Cron 設定：

```shell
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```
<a name="sub-minute-scheduled-tasks"></a>

### Sub-Minute Scheduled Tasks

On most operating systems, cron jobs are limited to running a maximum of once per minute. However, Laravel's scheduler allows you to schedule tasks to run at more frequent intervals, even as often as once per second:

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::call(function () {
        DB::table('recent_users')->delete();
    })->everySecond();
When sub-minute tasks are defined within your application, the `schedule:run` command will continue running until the end of the current minute instead of exiting immediately. This allows the command to invoke all required sub-minute tasks throughout the minute.

Since sub-minute tasks that take longer than expected to run could delay the execution of later sub-minute tasks, it is recommended that all sub-minute tasks dispatch queued jobs or background commands to handle the actual task processing:

    use App\Jobs\DeleteRecentUsers;
    
    Schedule::job(new DeleteRecentUsers)->everyTenSeconds();
    
    Schedule::command('users:delete')->everyTenSeconds()->runInBackground();
<a name="interrupting-sub-minute-tasks"></a>

#### Interrupting Sub-Minute Tasks

As the `schedule:run` command runs for the entire minute of invocation when sub-minute tasks are defined, you may sometimes need to interrupt the command when deploying your application. Otherwise, an instance of the `schedule:run` command that is already running would continue using your application's previously deployed code until the current minute ends.

To interrupt in-progress `schedule:run` invocations, you may add the `schedule:interrupt` command to your application's deployment script. This command should be invoked after your application is finished deploying:

```shell
php artisan schedule:interrupt
```
<a name="running-the-scheduler-locally"></a>

### Running the Scheduler Locally

一般來說，我們不會在本機開發機上新增排程程式的 Cron 設定。在本機上，不需要新增 Cron 設定，我們可以使用 `schedule:work` Artisan 指令。該指令會在前景執行，並且會每分鐘叫用排程程式，直到手動停止該指令為止：

```shell
php artisan schedule:work
```
<a name="task-output"></a>

## 任務的輸出

Laravel 的排程程式提供了數種便利的方法可處理排程任務產生的輸出。首先，可使用 `sendOutputTo` 方法來將輸出傳送至檔案中以在稍後檢視：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('emails:send')
             ->daily()
             ->sendOutputTo($filePath);
若想將輸出附加到給定檔案最後，可使用 `appendOutputTo` 方法：

    Schedule::command('emails:send')
             ->daily()
             ->appendOutputTo($filePath);
若使用 `emailOutputTo` 方法，就可以將輸出以電子郵件傳送給指定的 E-Mail 位址。在將任務輸出以電子郵件寄出前，請先設定 Laravel 的[電子郵件服務](/docs/{{version}}/mail)：

    Schedule::command('report:generate')
             ->daily()
             ->sendOutputTo($filePath)
             ->emailOutputTo('taylor@example.com');
若只想在排程的 Artisan 或系統指令以結束代碼 0 以外的狀態退出時以電子郵件傳送輸出，可使用 `emailOutputOnFailure` 方法：

    Schedule::command('report:generate')
             ->daily()
             ->emailOutputOnFailure('taylor@example.com');
> [!WARNING]  
> `emailOutputTo`、`emailOutputOnFailure`、`sendOutputTo`、`appendOutputTo` 等方法只能在 `command` 與 `exec` 方法上使用。

<a name="task-hooks"></a>

## 任務的 Hook

使用 `before` 與 `after` 方法，即可指定要在排程任務執行前後執行的程式碼：

    use Illuminate\Support\Facades\Schedule;
    
    Schedule::command('emails:send')
             ->daily()
             ->before(function () {
                 // The task is about to execute...
             })
             ->after(function () {
                 // The task has executed...
             });
使用 `onSuccess` 與 `onFailure` 方法，就可以指定要在排程任務成功或失敗時要執行的程式碼。「執行失敗」即為該排程的 Artisan 指令或系統指令以結束代碼 0 以外的代碼終止執行：

    Schedule::command('emails:send')
             ->daily()
             ->onSuccess(function () {
                 // The task succeeded...
             })
             ->onFailure(function () {
                 // The task failed...
             });
若指令有輸出，則可在 `after`、`onSuccess`、`onFailure` 等 Hook 上存取這些輸出。只需要在這些 Hook 的閉包定義上將 `$output` 引數型別提示為 `Illuminate\Support\Stringable` 即可：

    use Illuminate\Support\Stringable;
    
    Schedule::command('emails:send')
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

    Schedule::command('emails:send')
             ->daily()
             ->pingBefore($url)
             ->thenPing($url);
`pingBeforeIf` 與 `thenPingIf` 方法可用來只在給定條件為 `true` 時 Ping 給定的網址：

    Schedule::command('emails:send')
             ->daily()
             ->pingBeforeIf($condition, $url)
             ->thenPingIf($condition, $url);
`pingOnSuccess` 與 `pingOnFailure` 方法可用來只在任務執行成功或執行失敗時 Ping 給定的網址。「執行失敗」即為該排程的 Artisan 指令或系統指令以結束代碼 0 以外的代碼終止執行：

    Schedule::command('emails:send')
             ->daily()
             ->pingOnSuccess($successUrl)
             ->pingOnFailure($failureUrl);
<a name="events"></a>

## 事件

Laravel dispatches a variety of [events](/docs/{{version}}/events) during the scheduling process. You may [define listeners](/docs/{{version}}/events) for any of the following events:

<div class="overflow-auto">
| Event Name |
| --- |
| `Illuminate\Console\Events\ScheduledTaskStarting` |
| `Illuminate\Console\Events\ScheduledTaskFinished` |
| `Illuminate\Console\Events\ScheduledBackgroundTaskFinished` |
| `Illuminate\Console\Events\ScheduledTaskSkipped` |
| `Illuminate\Console\Events\ScheduledTaskFailed` |

</div>