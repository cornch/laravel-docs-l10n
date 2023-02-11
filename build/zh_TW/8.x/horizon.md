---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/83/en-zhtw
progress: 25
updatedAt: '2023-02-07T11:21:00Z'
---

# Laravel Horizon

- [簡介](#introduction)
- [安裝](#installation)
   - [設定](#configuration)
   - [負載平衡策略](#balancing-strategies)
   - [主控台的權限控制](#dashboard-authorization)
- [升級 Horizon](#upgrading-horizon)
- [執行 Horizon](#running-horizon)
   - [部署 Horizon](#deploying-horizon)
- [Tag](#tags)
- [Notification](#notifications)
- [Metric](#metrics)
- [刪除失敗的 Job](#deleting-failed-jobs)
- [在佇列中清除 Job](#clearing-jobs-from-queues)

<a name="introduction"></a>

## 簡介

> {tip} 在開始深入了解 Laravel Horizon 前，請先熟悉一下 Laravel 中基本的 [Queue 服務](/docs/{{version}}/queues)。Horizon 以 Laravel 的 Queue 為基礎，加上了很多新功能。如果你還不熟悉 Laravel 中基本的 Queue 功能，那麼可能會不太好理解 Horizon 的一些概念。

[Laravel Horizon](https://github.com/laravel/horizon) 提供了一個功能強大的主控台，並且可使用程式碼來調整 Laravel 驅動的 [Redis Queue](/docs/{{version}}/queues) 的設定。使用 Horizon，就能輕鬆的監控佇列系統上的一些關鍵指標，如 Job 吞吐量、執行時間、失敗的 Job。

在使用 Horizon 時，所有 Queue Worker 的設定都保存在簡單且單一的一個設定檔中。只要把專案的 Worker 設定保存在版本控制的檔案中，就能輕鬆地在部署專案時擴增或調整 Queue Worker。

<img src="https://laravel.com/img/docs/horizon-example.png">

<a name="installation"></a>

## 安裝

> {note} 使用 Laravel Horizon 時必須使用 [Redis](https://redis.io) 來驅動 Queue。因此，請確定有在專案的 `config/queue.php` 設定檔中將 Queue 連線設為 `redis`。

可以使用 Composer 套件管理員來將 Horizon 安裝到專案中：

    composer require laravel/horizon

安裝好 Horizon 後，使用 `horizon:install` Artisan 指令來安裝 Horizon 的素材：

    php artisan horizon:install

<a name="configuration"></a>

### 設定

安裝好 Horizon 的素材後，主要設定檔會被放到 `config/horizon.php`。在這個設定檔中，我們可以調整 Queue Worker 的設定。每個設定選項都包含了有關該選項功能的說明，因此建議先仔細看過這個設定檔。

> {note} Horizon 會在內部使用到命名為 `horizon` 的 Redis 連線。這個 Redis 連線名稱為保留字，不可在 `databade.php` 設定檔中將該名稱指派給其他連線，或是在 `horizon.php` 設定檔中設為 `use` 選項的值。

<a name="environments"></a>

#### 環境

安裝好後，我們首先要熟悉的 Horizon 設定是 `environments` 選項。這個設定選項是一組環境的陣列，這些環境是專案會執行的。在這個選項中，要為各個環境定義 Worker 處理程序的設定。預設情況下，`environments` 選項中包含了 `production` 與 `local` 兩個環境。不過，可以按照需求任意加上更多的環境：

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'maxProcesses' => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
            ],
        ],
    
        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 3,
            ],
        ],
    ],

啟動 Horizon 時，Horizon 會使用目前專案所執行的環境所對應的 Worker 設定。一般來說，會從 `APP_ENV` [環境變數](/docs/{{version}}/configuration#determining-the-current-environment)中來判斷專案所在的環境。舉例來說，預設的 `local` Horizon 環境已設定好啟動三個 Worker 處理程序，並且會自動為各個 Queue 負載平衡分配 Worker 處理程序的數量。預設的 `production` 環境設定好啟動 10 個 Worker，並自動負載平衡分配 Worker 數量給各個 Queue。

> {note} 請確保 `horizon` 設定檔中的 `environments` 內有包含所有會執行 Horizon 的 [環境](/docs/{{version}}/configuration#environment-configuration)。

<a name="supervisors"></a>

#### Supervisor

As you can see in Horizon's default configuration file. Each environment can contain one or more "supervisors". By default, the configuration file defines this supervisor as `supervisor-1`; however, you are free to name your supervisors whatever you want. Each supervisor is essentially responsible for "supervising" a group of worker processes and takes care of balancing worker processes across queues.

You may add additional supervisors to a given environment if you would like to define a new group of worker processes that should run in that environment. You may choose to do this if you would like to define a different balancing strategy or worker process count for a given queue used by your application.

<a name="default-values"></a>

#### Default Values

Within Horizon's default configuration file, you will notice a `defaults` configuration option. This configuration option specifies the default values for your application's [supervisors](#supervisors). The supervisor's default configuration values will be merged into the supervisor's configuration for each environment, allowing you to avoid unnecessary repetition when defining your supervisors.

<a name="balancing-strategies"></a>

### Balancing Strategies

Unlike Laravel's default queue system, Horizon allows you to choose from three worker balancing strategies: `simple`, `auto`, and `false`. The `simple` strategy, which is the configuration file's default, splits incoming jobs evenly between worker processes:

    'balance' => 'simple',

The `auto` strategy adjusts the number of worker processes per queue based on the current workload of the queue. For example, if your `notifications` queue has 1,000 pending jobs while your `render` queue is empty, Horizon will allocate more workers to your `notifications` queue until the queue is empty.

When using the `auto` strategy, you may define the `minProcesses` and `maxProcesses` configuration options to control the minimum and the maximum number of worker processes Horizon should scale up and down to:

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 3,
            ],
        ],
    ],

The `balanceMaxShift` and `balanceCooldown` configuration values to determine how quickly Horizon will scale to meet worker demand. In the example above, a maximum of one new process will be created or destroyed every three seconds. You are free to tweak these values as necessary based on your application's needs.

When the `balance` option is set to `false`, the default Laravel behavior will be used, which processes queues in the order they are listed in your configuration.

<a name="dashboard-authorization"></a>

### Dashboard Authorization

Horizon exposes a dashboard at the `/horizon` URI. By default, you will only be able to access this dashboard in the `local` environment. However, within your `app/Providers/HorizonServiceProvider.php` file, there is an [authorization gate](/docs/{{version}}/authorization#gates) definition. This authorization gate controls access to Horizon in **non-local** environments. You are free to modify this gate as needed to restrict access to your Horizon installation:

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            return in_array($user->email, [
                'taylor@laravel.com',
            ]);
        });
    }

<a name="alternative-authentication-strategies"></a>

#### Alternative Authentication Strategies

Remember that Laravel automatically injects the authenticated user into the gate closure. If your application is providing Horizon security via another method, such as IP restrictions, then your Horizon users may not need to "login". Therefore, you will need to change `function ($user)` closure signature above to `function ($user = null)` in order to force Laravel to not require authentication.

<a name="upgrading-horizon"></a>

## Upgrading Horizon

When upgrading to a new major version of Horizon, it's important that you carefully review [the upgrade guide](https://github.com/laravel/horizon/blob/master/UPGRADE.md). In addition, when upgrading to any new Horizon version, you should re-publish Horizon's assets:

    php artisan horizon:publish

To keep the assets up-to-date and avoid issues in future updates, you may add the `horizon:publish` command to the `post-update-cmd` scripts in your application's `composer.json` file:

    {
        "scripts": {
            "post-update-cmd": [
                "@php artisan horizon:publish --ansi"
            ]
        }
    }

<a name="running-horizon"></a>

## Running Horizon

Once you have configured your supervisors and workers in your application's `config/horizon.php` configuration file, you may start Horizon using the `horizon` Artisan command. This single command will start all of the configured worker processes for the current environment:

    php artisan horizon

You may pause the Horizon process and instruct it to continue processing jobs using the `horizon:pause` and `horizon:continue` Artisan commands:

    php artisan horizon:pause
    
    php artisan horizon:continue

You may also pause and continue specific Horizon [supervisors](#supervisors) using the `horizon:pause-supervisor` and `horizon:continue-supervisor` Artisan commands:

    php artisan horizon:pause-supervisor supervisor-1
    
    php artisan horizon:continue-supervisor supervisor-1

You may check the current status of the Horizon process using the `horizon:status` Artisan command:

    php artisan horizon:status

You may gracefully terminate the Horizon process using the `horizon:terminate` Artisan command. Any jobs that are currently being processed by will be completed and then Horizon will stop executing:

    php artisan horizon:terminate

<a name="deploying-horizon"></a>

### Deploying Horizon

When you're ready to deploy Horizon to your application's actual server, you should configure a process monitor to monitor the `php artisan horizon` command and restart it if it exits unexpectedly. Don't worry, we'll discuss how to install a process monitor below.

During your application's deployment process, you should instruct the Horizon process to terminate so that it will be restarted by your process monitor and receive your code changes:

    php artisan horizon:terminate

<a name="installing-supervisor"></a>

#### Installing Supervisor

Supervisor is a process monitor for the Linux operating system and will automatically restart your `horizon` process if it stops executing. To install Supervisor on Ubuntu, you may use the following command. If you are not using Ubuntu, you can likely install Supervisor using your operating system's package manager:

    sudo apt-get install supervisor

> {tip} If configuring Supervisor yourself sounds overwhelming, consider using [Laravel Forge](https://forge.laravel.com), which will automatically install and configure Supervisor for your Laravel projects.

<a name="supervisor-configuration"></a>

#### Supervisor Configuration

Supervisor configuration files are typically stored within your server's `/etc/supervisor/conf.d` directory. Within this directory, you may create any number of configuration files that instruct supervisor how your processes should be monitored. For example, let's create a `horizon.conf` file that starts and monitors a `horizon` process:

    [program:horizon]
    process_name=%(program_name)s
    command=php /home/forge/example.com/artisan horizon
    autostart=true
    autorestart=true
    user=forge
    redirect_stderr=true
    stdout_logfile=/home/forge/example.com/horizon.log
    stopwaitsecs=3600

> {note} You should ensure that the value of `stopwaitsecs` is greater than the number of seconds consumed by your longest running job. Otherwise, Supervisor may kill the job before it is finished processing.

<a name="starting-supervisor"></a>

#### Starting Supervisor

Once the configuration file has been created, you may update the Supervisor configuration and start the monitored processes using the following commands:

    sudo supervisorctl reread
    
    sudo supervisorctl update
    
    sudo supervisorctl start horizon

> {tip} For more information on running Supervisor, consult the [Supervisor documentation](http://supervisord.org/index.html).

<a name="tags"></a>

## Tags

Horizon allows you to assign “tags” to jobs, including mailables, broadcast events, notifications, and queued event listeners. In fact, Horizon will intelligently and automatically tag most jobs depending on the Eloquent models that are attached to the job. For example, take a look at the following job:

    <?php
    
    namespace App\Jobs;
    
    use App\Models\Video;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    
    class RenderVideo implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
        /**
         * The video instance.
         *
         * @var \App\Models\Video
         */
        public $video;
    
        /**
         * Create a new job instance.
         *
         * @param  \App\Models\Video  $video
         * @return void
         */
        public function __construct(Video $video)
        {
            $this->video = $video;
        }
    
        /**
         * Execute the job.
         *
         * @return void
         */
        public function handle()
        {
            //
        }
    }

If this job is queued with an `App\Models\Video` instance that has an `id` attribute of `1`, it will automatically receive the tag `App\Models\Video:1`. This is because Horizon will search the job's properties for any Eloquent models. If Eloquent models are found, Horizon will intelligently tag the job using the model's class name and primary key:

    use App\Jobs\RenderVideo;
    use App\Models\Video;
    
    $video = Video::find(1);
    
    RenderVideo::dispatch($video);

<a name="manually-tagging-jobs"></a>

#### Manually Tagging Jobs

If you would like to manually define the tags for one of your queueable objects, you may define a `tags` method on the class:

    class RenderVideo implements ShouldQueue
    {
        /**
         * Get the tags that should be assigned to the job.
         *
         * @return array
         */
        public function tags()
        {
            return ['render', 'video:'.$this->video->id];
        }
    }

<a name="notifications"></a>

## Notifications

> {note} When configuring Horizon to send Slack or SMS notifications, you should review the [prerequisites for the relevant notification channel](/docs/{{version}}/notifications).

If you would like to be notified when one of your queues has a long wait time, you may use the `Horizon::routeMailNotificationsTo`, `Horizon::routeSlackNotificationsTo`, and `Horizon::routeSmsNotificationsTo` methods. You may call these methods from the `boot` method of your application's `App\Providers\HorizonServiceProvider`:

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    
        Horizon::routeSmsNotificationsTo('15556667777');
        Horizon::routeMailNotificationsTo('example@example.com');
        Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

<a name="configuring-notification-wait-time-thresholds"></a>

#### Configuring Notification Wait Time Thresholds

You may configure how many seconds are considered a "long wait" within your application's `config/horizon.php` configuration file. The `waits` configuration option within this file allows you to control the long wait threshold for each connection / queue combination:

    'waits' => [
        'redis:default' => 60,
        'redis:critical,high' => 90,
    ],

<a name="metrics"></a>

## Metrics

Horizon includes a metrics dashboard which provides information regarding your job and queue wait times and throughput. In order to populate this dashboard, you should configure Horizon's `snapshot` Artisan command to run every five minutes via your application's [scheduler](/docs/{{version}}/scheduling):

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
    }

<a name="deleting-failed-jobs"></a>

## Deleting Failed Jobs

If you would like to delete a failed job, you may use the `horizon:forget` command. The `horizon:forget` command accepts the ID or UUID of the failed job as its only argument:

    php artisan horizon:forget 5

<a name="clearing-jobs-from-queues"></a>

## Clearing Jobs From Queues

If you would like to delete all jobs from your application's default queue, you may do so using the `horizon:clear` Artisan command:

    php artisan horizon:clear

You may provide the `queue` option to delete jobs from a specific queue:

    php artisan horizon:clear --queue=emails
