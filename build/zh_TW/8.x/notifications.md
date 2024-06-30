---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/109/en-zhtw
progress: 100
updatedAt: '2024-06-30T07:45:00Z'
---

# 通知 - Notification

- [簡介](#introduction)
- [產生 Notification](#generating-notifications)
- [傳送通知](#sending-notifications)
   - [使用 Notifiable Trait](#using-the-notifiable-trait)
   - [使用 Notification Facade](#using-the-notification-facade)
   - [指定遞送通道](#specifying-delivery-channels)
   - [將 Notification 放入佇列](#queueing-notifications)
   - [隨需通知](#on-demand-notifications)
- [郵件通知](#mail-notifications)
   - [格式化郵件通知](#formatting-mail-messages)
   - [自訂寄件人](#customizing-the-sender)
   - [自訂收件人](#customizing-the-recipient)
   - [自訂主旨](#customizing-the-subject)
   - [自訂 Mailer](#customizing-the-mailer)
   - [自訂樣板](#customizing-the-templates)
   - [附加檔案](#mail-attachments)
   - [使用 Mailable](#using-mailables)
   - [預覽郵件通知](#previewing-mail-notifications)
- [Markdown 的郵件通知](#markdown-mail-notifications)
   - [產生訊息](#generating-the-message)
   - [撰寫訊息](#writing-the-message)
   - [自訂元件](#customizing-the-components)
- [資料庫通知](#database-notifications)
   - [前置要求](#database-prerequisites)
   - [格式化資料庫通知](#formatting-database-notifications)
   - [存取通知](#accessing-the-notifications)
   - [將通知標記為已讀](#marking-notifications-as-read)
- [廣播通知](#broadcast-notifications)
   - [前置要求](#broadcast-prerequisites)
   - [格式化廣播通知](#formatting-broadcast-notifications)
   - [監聽通知](#listening-for-notifications)
- [簡訊通知](#sms-notifications)
   - [前置要求](#sms-prerequisites)
   - [格式化簡訊通知](#formatting-sms-notifications)
   - [格式化 Shortcode 的通知](#formatting-shortcode-notifications)
   - [自訂寄件號碼](#customizing-the-from-number)
   - [加上 Client Reference](#adding-a-client-reference)
   - [路由簡訊通知](#routing-sms-notifications)
- [Slack 通知](#slack-notifications)
   - [前置要求](#slack-prerequisites)
   - [格式化 Slack 通知](#formatting-slack-notifications)
   - [Slack 附件](#slack-attachments)
   - [路由 Slack 通知](#routing-slack-notifications)
- [本土化通知](#localizing-notifications)
- [通知事件](#notification-events)
- [自訂通道](#custom-channels)

<a name="introduction"></a>

## 簡介

除了支援[寄送郵件](/docs/{{version}}/mail)外，Laravel 還支援以各種不同的通道來寄送通知。支援的通道包含電子郵件、簡訊 (使用 [Vonage](https://www.vonage.com/communications-apis/),，前名 Nexmo)、[Slack](https://slack.com) 等。此外，還有許多[社群製作的通知通道](https://laravel-notification-channels.com/about/#suggesting-a-new-channel)，可使用數十種不同的通道來傳送通知！通知也可以儲存在資料庫中以在網頁界面上顯示。

一般來說，通知是簡短的、資訊性的訊息，用來通知使用者我們的程式裡發生的事情。舉例來說，假設我們正在開發一款請款用的程式，我們可以使用電子郵件與簡訊管道來傳送一個「已收到款項」的通知。

<a name="generating-notifications"></a>

## 產生通知

在 Laravel 中，通知以 `app/Notifications` 目錄中的一個類別的形式來呈現。若在專案中沒看到這個目錄，請別擔心 —— 執行 `make:notification` 後就會自動建立該目錄：

    php artisan make:notification InvoicePaid

這個指令會在 `app/Notifications` 目錄下建立一個新的 Notification。每個 Notification 中都包含了一個 `via` 方法與不定數量的訊息建立方法，如 `toMail` 或 `toDatabase`，這些訊息建立方法將通知轉換為特定頻道格式的訊息。

<a name="sending-notifications"></a>

## 傳送通知

<a name="using-the-notifiable-trait"></a>

### 使用 Notifiable Trait

有兩種方法可以傳送通知：使用 `Notifiable` Trait 的 `notify` 方法，或是使用 `Notification` [Facade](/docs/{{version}}/facades)。`Notifiable` Trait 已預設包含在專案的 `App\Models\User` 中：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable
    {
        use Notifiable;
    }

該 Trait 提供的 `notify` 方法預期接收一個通知實體：

    use App\Notifications\InvoicePaid;
    
    $user->notify(new InvoicePaid($invoice));

> {tip} 請記得，任何的 Model 都可以使用 `Notifiable` Trait。不是只有 `User` Model 上才能用。

<a name="using-the-notification-facade"></a>

### 使用 Notification Facade

或者，也可以使用 `Nofication` [Facade] 來傳送通知。若要傳送通知給多個 Notifiable 實體 (如一組 User Collection)，就很適合這種方法。若要使用該 Facade 傳送通知，請將所有 Notifiable 實體與 Notification 實體傳給 `send` 方法：

    use Illuminate\Support\Facades\Notification;
    
    Notification::send($users, new InvoicePaid($invoice));

也可以使用 `sendNow` 方法來馬上傳送通知。即使通知有實作 `ShouldQueue` 介面，該方法也會立即傳送通知：

    Notification::sendNow($developers, new DeploymentCompleted($deployment));

<a name="specifying-delivery-channels"></a>

### 指定傳送通道

每個 Notification 類別都有一個 `via` 方法，用來判斷該通知要在哪些通道上傳送。通知在 `mail`、`database`、`broadcast`、`nexmo`、`slack`等通道上傳送：

> {tip} 若想使用其他通道傳送，如 Telegram 或 Pusher，請參考看看由社群提供的 [Laravel Notification Channels 網站](http://laravel-notification-channels.com)。

`via` 方法會收到一個 `$notifiable` 實體，也就是該通知正在傳給的類別實體。可使用 `$nofiable` 來判斷該通知要在哪些通道上傳送：

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $notifiable->prefers_sms ? ['nexmo'] : ['mail', 'database'];
    }

<a name="queueing-notifications"></a>

### 將通知放入佇列

> {note} 在將通知放入佇列前，請先設定好佇列，並[執行一個 ^[Worker](背景工作角色)](/docs/{{version}}/queues)。

傳送通知可能會需要花費時間，特別是需要使用外部 API 呼叫來傳送通知的頻道。若要加速程式的回應時間，可在通知類別上加入 `ShouldQueue` 介面與 `Queueable` Trait 來讓通知使用佇列。使用 `make:notification` 指令產生的通知中，預設已有匯入該介面與 Trait，因此我們可以直接將其加入通知類別：

    <?php
    
    namespace App\Notifications;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Notification;
    
    class InvoicePaid extends Notification implements ShouldQueue
    {
        use Queueable;
    
        // ...
    }

將 `ShouldQueue` 介面加入通知後，可像平常一樣傳送通知。Laravel 會在偵測到該類別有 `ShouldQueue` 介面後自動以佇列寄送通知：

    $user->notify(new InvoicePaid($invoice));

若想延遲傳送通知，可在通知初始化之後串聯呼叫 `delay` 方法：

    $delay = now()->addMinutes(10);
    
    $user->notify((new InvoicePaid($invoice))->delay($delay));

可傳入陣列給 `delay` 方法來指定特定通道要延遲的時間：

    $user->notify((new InvoicePaid($invoice))->delay([
        'mail' => now()->addMinutes(5),
        'sms' => now()->addMinutes(10),
    ]));

在將通知放入佇列時，Laravel 會為每個收件人與每個通道的組合建立佇列^[任務](Job)。舉例來說，若通知有三個收件人與兩個通道，則會^[派發](Dispatch)六個任務。

<a name="customizing-the-notification-queue-connection"></a>

#### 自訂通知的佇列連線

預設情況下，佇列通知會使用專案的預設佇列連線。若想為特定通知指定不同的連線，可在 Notification 類別上定義一個 `$connection` 屬性：

    /**
     * The name of the queue connection to use when queueing the notification.
     *
     * @var string
     */
    public $connection = 'redis';

<a name="customizing-notification-channel-queues"></a>

#### 自訂通知通道佇列

若想為某個通知所支援的各個通知通道指定特定的佇列，可在通知上定義一個 `viaQueues` 方法。這個方法應會傳一組通知名稱 / 佇列名稱配對的陣列：

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues()
    {
        return [
            'mail' => 'mail-queue',
            'slack' => 'slack-queue',
        ];
    }

<a name="queued-notifications-and-database-transactions"></a>

#### 佇列的通知與資料庫 Transaction

當佇列通知是在資料庫 Transaction 內^[分派](Dispatch)的時候，這個通知可能會在資料庫 Transaction 被 Commit 前就被佇列進行處理了。發生這種情況時，在資料庫 Transaction 期間對 Model 或資料庫記錄所做出的更新可能都還未反應到資料庫內。另外，所有在 Transaction 期間新增的 Model 或資料庫記錄也可能還未出現在資料庫內。若該通知有使用這些 Model 的話，處理該通知的佇列任務時可能會出現未預期的錯誤。

若佇列的 `after_commit` 選項設為 `false`，則我們還是可以通過在傳送通知前呼叫 `afterCommit` 方法來標示出該 Mailable 應在所有資料庫 Transaction 都被 Commit 後才分派：

    use App\Notifications\InvoicePaid;
    
    $user->notify((new InvoicePaid($invoice))->afterCommit());

或者，也可以在 Notification 的 Constructor 上呼叫 `afterCommit` 方法：

    <?php
    
    namespace App\Notifications;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Notification;
    
    class InvoicePaid extends Notification implements ShouldQueue
    {
        use Queueable;
    
        /**
         * Create a new notification instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->afterCommit();
        }
    }

> {tip} 要瞭解更多有關這類問題的解決方法，請參考有關[佇列任務與資料庫 Transaction](/docs/{{version}}/queues#jobs-and-database-transactions) 有關的說明文件。

<a name="determining-if-the-queued-notification-should-be-sent"></a>

#### 判斷是否應送出某個佇列的通知

當佇列通知被派發出去給背景執行後，通常會被傳給佇列 Worker 並進一步傳送給指定的收件人。

不過，在通知要被佇列 Worker 處理時，若我們還想就是否應傳送該佇列通知作最後的決定，可在該 Notification 類別上定義一個 `shouldSend` 方法。若該方法回傳 `false`，就不會傳送這個通知：

    /**
     * Determine if the notification should be sent.
     *
     * @param  mixed  $notifiable
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend($notifiable, $channel)
    {
        return $this->invoice->isPaid();
    }

<a name="on-demand-notifications"></a>

### 隨需通知

有時候，我們會需要將通知傳給不是我們網站使用者的人。只要使用 `Notification` Facade 上的 `route` 方法，就可以在送出通知前指定特別的通知 Route 資訊：

    Notification::route('mail', 'taylor@example.com')
                ->route('nexmo', '5555555555')
                ->route('slack', 'https://hooks.slack.com/services/...')
                ->notify(new InvoicePaid($invoice));

若想在傳送隨需通知時為 `mail` Route 提供收件人名稱，可提供一個索引鍵為郵件位址而值為姓名的陣列：

    Notification::route('mail', [
        'barrett@example.com' => 'Barrett Blair',
    ])->notify(new InvoicePaid($invoice));

<a name="mail-notifications"></a>

## 郵件通知

<a name="formatting-mail-messages"></a>

### 格式化郵件通知

若要支援以電子郵件傳送通知，請在該通知類別上定義一個 `toMail` 方法。這個方法會收到一個 `$notifiable` 實體，而回傳值應為 `Illuminate\Notifications\Messages\MailMessage` 實體。

`MailMessage` 類別包含一些簡單的方法，可以讓我們快速建立^[交易](Transactional)電子郵件訊息。郵件訊息可包含數行的文字，以及「執行動作」。來看看一個範例的 `toMail` 方法：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/invoice/'.$this->invoice->id);
    
        return (new MailMessage)
                    ->greeting('Hello!')
                    ->line('One of your invoices has been paid!')
                    ->action('View Invoice', $url)
                    ->line('Thank you for using our application!');
    }

> {tip} 請注意，在 `toMail` 中，我們使用了 `$this->invoice->id`。我們可以將通知訊息所需要的任何資料傳入該通知的 ^[Constructor](建構函式) 中。

在這個範例中，我們註冊了一個^[招呼語](Greeting)，^[一行文字](Line)，一個^[動作](Action)，然後是又^[一行的文字](Line)。`MailMessage` 物件提供的這些方法讓我們可以簡單快速地格式化簡短的交易電子郵件。Mail 通道會將該這些訊息元件翻譯為漂亮的回應式 HTML 電子郵件樣板與一個回應的純文字版本。下列是 `mail` 通道產生的電子郵件範例：

<img src="https://laravel.com/img/docs/notification-example-2.png">

> {tip} 在傳送郵件通知時，請確保有在 `config/app.php` 設定檔中設定 `name` 設定選項。在郵件通知訊息的頁頭與頁尾中會使用到這個值。

<a name="other-mail-notification-formatting-options"></a>

#### 其他郵件通知的格式化選項

除了在 Notification 類別中定義「line」以外，也可以使用 `view` 方法來指定用來轉譯通知郵件的自訂樣板：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->view(
            'emails.name', ['invoice' => $this->invoice]
        );
    }

可以傳入一個陣列給 `view` 方法，並在該陣列的第二個元素上指定純文字版本的 View 名稱，以為郵件訊息指定純文字版本：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)->view(
            ['emails.name.html', 'emails.name.plain'],
            ['invoice' => $this->invoice]
        );
    }

<a name="error-messages"></a>

#### 錯誤訊息

有些通知是用來通知使用者錯誤的，如付款失敗等。可在建立訊息時呼叫 `error` 方法來標示該郵件訊息是錯誤通知。在郵件訊息上使用 `error` 方法時，動作按鈕會從黑色變成紅色的：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Message
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->error()
                    ->subject('Notification Subject')
                    ->line('...');
    }

<a name="customizing-the-sender"></a>

### 自訂寄件人

預設情況下，郵件的寄送人 / 寄件位址是在 `config/mail.php` 設定檔中定義的。不過，我們也可以使用 `from` 方法來為特定的通知指定寄件位址：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->from('barrett@example.com', 'Barrett Blair')
                    ->line('...');
    }

<a name="customizing-the-recipient"></a>

### 自訂收件人

使用 `mail` 通道傳送通知時，通知系統會自動在 Notifiable 實體上尋找 `email` 屬性。可在該 Notifiable 實體上定義一個 `routeNotificationForMail` 方法來自訂通知要傳送給哪個電子郵件位址：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable
    {
        use Notifiable;
    
        /**
         * Route notifications for the mail channel.
         *
         * @param  \Illuminate\Notifications\Notification  $notification
         * @return array|string
         */
        public function routeNotificationForMail($notification)
        {
            // Return email address only...
            return $this->email_address;
    
            // Return email address and name...
            return [$this->email_address => $this->name];
        }
    }

<a name="customizing-the-subject"></a>

### 自訂主旨

預設情況下，電子郵件的主旨就是 Notification 類別名稱的「Title Case」格式版本。所以，若 Notification 類別的名稱是 `InvoicePaid`，則郵件的主旨就會是 `Invoide Paid`。若想為訊息指定不同的主旨，可在建立郵件時呼叫 `subject` 方法：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Notification Subject')
                    ->line('...');
    }

<a name="customizing-the-mailer"></a>

### 自訂 Mailer

預設情況下，電子郵件通知會使用 `config/mail.php` 設定檔中定義的預設 Mailer。不過，也可以建立郵件時呼叫 `mailer` 來在執行階段使用不同的 Mailer：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->mailer('postmark')
                    ->line('...');
    }

<a name="customizing-the-templates"></a>

### 自訂樣板

可以將^[安裝](Publish) Notification 套件的資源來修改郵件通知所使用的 HTML 樣板與純文字樣板。執行該指令後，郵件通知樣板會被放在 `resources/views/vendor/notifications` 目錄中：

    php artisan vendor:publish --tag=laravel-notifications

<a name="mail-attachments"></a>

### 附加檔案

若要將檔案附加至 E-Mail，請在建立訊息時使用 `attach` 方法。`attach` 方法接受檔案的完整路徑作為其第一個引數：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('Hello!')
                    ->attach('/path/to/file');
    }

將檔案附加至訊息時，也可傳入一個陣列給 `attach` 方法來指定要顯示的檔案名稱與 / 或 MIME 類型：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('Hello!')
                    ->attach('/path/to/file', [
                        'as' => 'name.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }

跟將檔案附加到 Mailable 物件不同，在 Notification 上無法使用 `attachFromStorage` 方法直接將存放 Disk 內的檔案附加到通知上。請使用 `attach` 方法，並提供該存放 Disk 中檔案的絕對路徑。或者，也可以在 `toMail` 方法內回傳一個 [Mailable](/docs/{{version}}/mail#generating-mailables)：

    use App\Mail\InvoicePaid as InvoicePaidMailable;
    
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        return (new InvoicePaidMailable($this->invoice))
                    ->to($notifiable->email)
                    ->attachFromStorage('/path/to/file');
    }

<a name="raw-data-attachments"></a>

#### 原始資料附加檔案

可以使用 `attachData` 方法來將原始的位元組字串作為附加檔案附加到郵件上。呼叫 `attachData` 方法時，請提供要指派給附件的檔案名稱：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->greeting('Hello!')
                    ->attachData($this->pdf, 'name.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }

<a name="using-mailables"></a>

### 使用 Mailable

或者，也可以在 Notification 的 `toMail` 方法中回傳一個完整的 [Mailable 物件](/docs/{{version}}/mail)。若回傳的不是 `MailMessage` 而是 `Mailable` 時，就需要使用 Mailable 物件的 `to` 方法來指定該訊息的收件人：

    use App\Mail\InvoicePaid as InvoicePaidMailable;
    
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        return (new InvoicePaidMailable($this->invoice))
                    ->to($notifiable->email);
    }

<a name="mailables-and-on-demand-notifications"></a>

#### Mailable 與隨需通知

傳送[隨需通知]時，傳給 `toMail` 方法的 `$notifiable` 實體會是 `Illuminate\Notifications\AnonymousNotifiable` 的實體。該實體提供了一個 `routeNotificationFor` 方法，可讓我們取得隨需通知要寄送的電子郵件位址：

    use App\Mail\InvoicePaid as InvoicePaidMailable;
    use Illuminate\Notifications\AnonymousNotifiable;
    
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        $address = $notifiable instanceof AnonymousNotifiable
                ? $notifiable->routeNotificationFor('mail')
                : $notifiable->email;
    
        return (new InvoicePaidMailable($this->invoice))
                    ->to($address);
    }

<a name="previewing-mail-notifications"></a>

### 預覽郵件通知

在設計郵件通知樣板時，若能像普通的 Blade 樣板一樣在瀏覽器中預覽轉譯後的郵件通知該有多方便。所以，在 Laravel 中，可以直接在 Route 閉包或 Controller 中回傳任何的郵件通知。若回傳郵件通知，Laravel 會轉譯該郵件通知並顯示在瀏覽器上，讓我們不需將其寄到真實的電子郵件上也能快速檢視其設計：

    use App\Models\Invoice;
    use App\Notifications\InvoicePaid;
    
    Route::get('/notification', function () {
        $invoice = Invoice::find(1);
    
        return (new InvoicePaid($invoice))
                    ->toMail($invoice->user);
    });

<a name="markdown-mail-notifications"></a>

## Markdown 的郵件通知

Markdown 的郵件通知訊息可讓我們使用郵件通知預先建立好的樣板，可讓我們自由地撰寫更長、客製化程度更高的訊息。由於使用 Markdown 來撰寫訊息，因此 Laravel 就可為這些郵件轉譯出漂亮的回應式 HTML 樣板，並自動轉譯出純文字版本的郵件。

<a name="generating-the-message"></a>

### 產生訊息

若要產生有對應 Markdown 樣板的郵件通知，請使用 `make:notification` Artisan 指令的 `--markdown` 選項：

    php artisan make:notification InvoicePaid --markdown=mail.invoice.paid

與其他郵件通知一樣，使用 Markdown 樣板的通知也應在 Notification 類別定義一個 `toMail` 方法。不過，在 Markdown 郵件通知上，我們不是使用 `line` 與 `action` 方法來建立通知，而是使用 `markdown` 方法來指定要使用的 Markdown 樣板名稱。可傳入一組資料陣列給該方法的第二個引數來將資料提供給該樣板使用：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = url('/invoice/'.$this->invoice->id);
    
        return (new MailMessage)
                    ->subject('Invoice Paid')
                    ->markdown('mail.invoice.paid', ['url' => $url]);
    }

<a name="writing-the-message"></a>

### 撰寫訊息

Markdown 的郵件通知混合使用了 Blade 元件與 Markdown 語法，讓我們能輕鬆地使用 Laravel 內建的通知元件來建立通知：

    @component('mail::message')
    # Invoice Paid
    
    Your invoice has been paid!
    
    @component('mail::button', ['url' => $url])
    View Invoice
    @endcomponent
    
    Thanks,<br>
    {{ config('app.name') }}
    @endcomponent

<a name="button-component"></a>

#### Button 元件

Button 元件用來轉譯一個置中的按鈕連結。這個元件接受兩個引數，一個是 `url` 網址，另一個則是可選的 `color` 顏色。支援的顏色有 `primary`、`green`、`red`。在通知中可以加上不限數量的 Button 元件：

    @component('mail::button', ['url' => $url, 'color' => 'green'])
    View Invoice
    @endcomponent

<a name="panel-component"></a>

#### Panel 元件

Panel 元件將給定的文字區塊轉譯在一個面板中，面板的底色與通知中其他部分的背景色稍有不同。我們可以使用 Panel 元件來讓給定區塊的文字較為醒目：

    @component('mail::panel')
    This is the panel content.
    @endcomponent

<a name="table-component"></a>

#### Table 元件

Table 元件可讓我們將 Markdown 表格轉為 HTML 表格。該元件接受一個 Markdown 表格作為其內容。支援使用預設的 Markdown 表格對其格式來對其表格欄位：

    @component('mail::table')
    | Laravel       | Table         | Example  |
    | ------------- |:-------------:| --------:|
    | Col 2 is      | Centered      | $10      |
    | Col 3 is      | Right-Aligned | $20      |
    @endcomponent

<a name="customizing-the-components"></a>

### 自訂元件

可以將所有的 Markdown 通知元件匯出到專案內來自訂這些元件。若要匯出元件，請使用 `vendor:publish` Artisan 指令來^[安裝](Publish) `laravel-mail` 素材標籤：

    php artisan vendor:publish --tag=laravel-mail

這個指令會將 Markdown 郵件元件安裝到 `resources/views/vendor/mail` 目錄下。`mail` 目錄會包含 `html` 與 `text` 目錄，這些目錄中包含了所有可用元件對應的呈現方式。可以隨意自訂這些元件。

<a name="customizing-the-css"></a>

#### 自訂 CSS

匯出元件後，`resources/views/vendor/mail/html/themes` 目錄下會包含一個 `default.css` 檔案。可以自訂這個檔案內的 CSS。這些樣式在 Markdown 通知的 HTML 呈現上會自動被轉換為內嵌的 CSS 樣式：

若想為 Laravel Markdown 元件製作一個全新的主題，可在 `html/themes` 目錄下放置一個 CSS 檔。命名好 CSS 檔並保存後，請修改專案 `mail` 設定檔中的 `theme` 選項為該新主題的名稱：

若要為個別通知自訂主題，可在建立通知的郵件訊息時呼叫 `theme` 方法。`theme` 方法的引數為傳送通知時要使用的主題名稱：

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->theme('invoice')
                    ->subject('Invoice Paid')
                    ->markdown('mail.invoice.paid', ['url' => $url]);
    }

<a name="database-notifications"></a>

## 資料庫通知

<a name="database-prerequisites"></a>

### 前置要求

`database` 通知通道將通知資訊保存在資料庫資料表中。這個資料表會包含一些資訊，如通知類型，以及描述該通知的 JSON 資料結構。

可以查詢該資料表來將通知顯示在專案的 UI 上。不過，在這麼做之前，我們需要先建立一個用來保存通知的資料表。可以使用 `notifications:table` 指令來產生一個包含適當資料表結構的 [Migration](/docs/{{version}}/migrations) ：

    php artisan notifications:table
    
    php artisan migrate

<a name="formatting-database-notifications"></a>

### 格式化資料庫通知

若要讓某個通知支援保存在資料表中，請在該 Notification 類別上定義一個 `toDatabase` 或 `toArray` 方法。這個方法會收到一個 `$notifiable` 實體，而該方法應回傳一個純 PHP 陣列。回傳的這個陣列會被編碼為 JSON，然後保存在 `notifications` 資料表中的 `data` 欄位。來看看 `toArray` 方法的範例：

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
        ];
    }

<a name="todatabase-vs-toarray"></a>

#### `toDatabase` Vs. `toArray`

`broadcast` 通道也會使用 `toArray` 方法來判斷要將哪些資料廣播到 JavaScript 驅動的前端上。若想讓 `database` 與 `broadcast` 通道有不同的陣列呈現，請不要定義 `toArray` 方法，而是定義 `toDatabase` 方法。

<a name="accessing-the-notifications"></a>

### 存取通知

將通知保存在資料庫後，我們還需要一種方便的方法來在 Notifiable 實體上存取這些通知。`Illuminate\Notifications\Notifiable` Trait —— 也就是 Laravel 預設的 `App\Models\User` Model 中所包含的一個 Trait —— 包含了一個 `notifications` [Eloquent 關聯]，該關聯會回傳該實體的所有通知。若要取得通知，可像其他 Eloquent 關聯一樣存取該方法。預設情況下，會使用 `created_at` 時戳來排序通知，最新的通知會排在 Collection 的最前面：

    $user = App\Models\User::find(1);
    
    foreach ($user->notifications as $notification) {
        echo $notification->type;
    }

若只想取得「未讀」的通知，可使用 `unreadNotifications` 關聯。一樣，這些通知都會使用 `created_at` 來排序，最新的通知會在 Collection 的最前面：

    $user = App\Models\User::find(1);
    
    foreach ($user->unreadNotifications as $notification) {
        echo $notification->type;
    }

> {tip} 若要在 JavaScript 用戶端中存取通知，請定義一個用來為 Notifiable 實體 (如：目前使用者) 回傳通知的 Notification Controller。接著就可以從 JavaScript 用戶端上建立一個 HTTP Request 來連線到該 Controller 的網址。

<a name="marking-notifications-as-read"></a>

### 將通知標記為已讀

使用者檢視過通知後，我們通常會想將這些通知設為「已讀」。`Illuminate\Notifications\Notifiable` Trait 提供了一個 `markAsRead` 方法，該方法會更新通知資料庫記錄上的 `read_at` 欄位：

    $user = App\Models\User::find(1);
    
    foreach ($user->unreadNotifications as $notification) {
        $notification->markAsRead();
    }

不過，我們不需要在每個通知上迴圈，可以直接在一組通知的 Collection 上使用 `markAsRead` 方法：

    $user->unreadNotifications->markAsRead();

也可以使用^[批次更新](Mass-Update)查詢來將所有的通知都列為已讀，而不需要先從資料庫中取出這些通知：

    $user = App\Models\User::find(1);
    
    $user->unreadNotifications()->update(['read_at' => now()]);

也可以使用 `delete` 來從資料表中完全移除該通知：

    $user->notifications()->delete();

<a name="broadcast-notifications"></a>

## 廣播通知

<a name="broadcast-prerequisites"></a>

### 前置要求

廣播通知前，請先設定並熟悉一下 Laravel 的[事件廣播](/docs/{{version}}/broadcasting)服務。使用事件廣播，就可以在 JavaScript 驅動的前端上對伺服器端 Laravel 的事件作出回應。

<a name="formatting-broadcast-notifications"></a>

### 格式化廣播通知

`broadcast` 通道使用的是 Laravel 的[事件廣播](/docs/{{version}}/broadcasting)服務，可讓我們在 JavaScript 驅動的前端即時取得通知。若要讓通知支援廣播，請在該 Notification 類別上定義一個 `toBroadcast` 方法。這個方法會收到一個 `$notifiable` 實體，且該方法應回傳一個 `BroadcastMessage` 實體。若 `toBroadcast` 方法不存在，則會使用 `toArray` 方法來取得要廣播的資料。回傳的資料會被編碼為 JSON，並廣播給 JavaScript 驅動的前端。來看看一個範例的 `toBroadcast` 方法：

    use Illuminate\Notifications\Messages\BroadcastMessage;
    
    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'invoice_id' => $this->invoice->id,
            'amount' => $this->invoice->amount,
        ]);
    }

<a name="broadcast-queue-configuration"></a>

#### 廣播佇列設定

所有的廣播通知都會被放入佇列以供廣播。若想為要用來廣播的佇列連線或佇列名稱，可使用 `BroadcastMessage` 的 `onConnection` 方法與 `onQueue` 方法：

    return (new BroadcastMessage($data))
                    ->onConnection('sqs')
                    ->onQueue('broadcasts');

<a name="customizing-the-notification-type"></a>

#### 自訂通知類型

除了指定的資料外，所有的廣播通知也會包含一個 `type` 欄位，`type` 欄位為該通知的完整類別名稱。若想自訂通知的 `type`，可在該 Notification 類別上定義一個 `broadcastType` 方法：

    use Illuminate\Notifications\Messages\BroadcastMessage;
    
    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType()
    {
        return 'broadcast.message';
    }

<a name="listening-for-notifications"></a>

### 監聽通知

通知會在使用 `{notifiable}.{id}` 這種命名慣例命名的私有頻道上廣播。所以，假設我們要將通知傳送給一個 ID 為 `1` 的 `App\Models\User` 實體，則該通知會被廣播到 `App.Models.User.1` 私有頻道。在使用 [Laravel Echo](/docs/{{version}}/broadcasting#client-side-installation) 時，只要使用 `notification` 方法，就可以輕鬆地在頻道上監聽通知：

    Echo.private('App.Models.User.' + userId)
        .notification((notification) => {
            console.log(notification.type);
        });

<a name="customizing-the-notification-channel"></a>

#### 自訂通知頻道

若想自訂某個 Notifiable 實體的通知要在哪個頻道上廣播，可在 Notifiable 的類別上定義一個 `receivesBroadcastNotificationsOn` 方法：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Broadcasting\PrivateChannel;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable
    {
        use Notifiable;
    
        /**
         * The channels the user receives notification broadcasts on.
         *
         * @return string
         */
        public function receivesBroadcastNotificationsOn()
        {
            return 'users.'.$this->id;
        }
    }

<a name="sms-notifications"></a>

## 簡訊通知

<a name="sms-prerequisites"></a>

### 前置要求

Laravel 的簡訊通知傳送功能由 [Vonage](https://www.vonage.com/) 驅動。(Vonage 前身為 Nexmo)。在使用 Vonage 傳送通知前，需要先安裝 `laravel/nexmo-notification-channel` 與 `nexmo/laravel` Composer 套件：

    composer require laravel/nexmo-notification-channel nexmo/laravel

`nexmo/laravel` 套件包含了[其專屬的設定檔](https://github.com/Nexmo/nexmo-laravel/blob/master/config/nexmo.php)。不過，並不需要將該設定檔安裝到專案中也可以使用該套件。只要將 `NEXMO_KEY` 與 `NEXMO_SECRET` 環境變數設為 Vonage 的公開金鑰與私有金鑰即可。

接著，我們需要在 `config/services.php` 設定檔中新增一個 `nexmo` 設定欄位。可以複製下方的範例設定來用：

    'nexmo' => [
        'sms_from' => '15556666666',
    ],

這個 `sms_from` 選項是要用來傳送簡訊的電話號碼。請在 Vonage 控制面板中為專案產生一個電話號碼：

<a name="formatting-sms-notifications"></a>

### 格式化簡訊通知

若要支援以簡訊傳送通知，請在該通知類別上定義一個 `toNexmo` 方法。這個方法會收到一個 `$notifiable` 實體，而回傳值應為 `Illuminate\Notifications\Messages\ NexmoMessage ` 實體：

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
                    ->content('Your SMS message content');
    }

<a name="unicode-content"></a>

#### Unicode 內容

若有要傳送包含 Unicode 字元的簡訊，請在建立 `NexmoMessage` 實體時呼叫 `unicode` 方法：

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
                    ->content('Your unicode message')
                    ->unicode();
    }

<a name="formatting-shortcode-notifications"></a>

### 格式化 Shortcode 的通知

Laravel 也支援傳送 Shortcode 的通知。Shortcode 就是在 Vonage 帳號上預先定義好的訊息樣板。若要傳送 Shortcode 的簡訊，請在 Notification 類別上定義一個 `toShortcode` 方法。在這個方法中，就可以回傳一個陣列來指定通知的類型 (`alert`、`2fa、`marketing`) 等，以及要填入該樣板的自訂值：

    /**
     * Get the Vonage / Shortcode representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toShortcode($notifiable)
    {
        return [
            'type' => 'alert',
            'custom' => [
                'code' => 'ABC123',
            ],
        ];
    }

> {tip} 跟[簡訊通知的路由](#routing-sms-notifications)一樣，請在 Notifiable Model 上實作 `routeNotificationForShortcode` 方法。

<a name="customizing-the-from-number"></a>

### 自訂「寄件」號碼

若想使用與 `config/services.php` 檔案中所設定之不同的號碼來傳送通知，可在 `NexmoMessage` 實體上呼叫 `from` 方法：

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
                    ->content('Your SMS message content')
                    ->from('15554443333');
    }

<a name="adding-a-client-reference"></a>

### 新增 Client Reference

若想追蹤每位使用者產生的花費，可在通知上新增一個「^[Client Reference](用戶端參照)」。在 Vanage 上我們可以使用這個 Client Reference 來產生報表，以更清楚瞭解特定客戶的簡訊使用量。Client Reference 可以為最多 40 字元的任意字串：

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
                    ->clientReference((string) $notifiable->id)
                    ->content('Your SMS message content');
    }

<a name="routing-sms-notifications"></a>

### 為簡訊通知路由

若要將簡訊通知路由到正確的手機號碼，請在 Notifiable 實體上定義一個 `routeNotificationForNexmo` 方法：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable
    {
        use Notifiable;
    
        /**
         * Route notifications for the Nexmo channel.
         *
         * @param  \Illuminate\Notifications\Notification  $notification
         * @return string
         */
        public function routeNotificationForNexmo($notification)
        {
            return $this->phone_number;
        }
    }

<a name="slack-notifications"></a>

## Slack 通知

<a name="slack-prerequisites"></a>

### 前置要求

在開始使用 Slack 傳送通知前，請先使用 Composer 安裝 Slack 通知通道：

    composer require laravel/slack-notification-channel

此外，也許為 Slack 團隊建立一個 [Slack App](https://api.slack.com/apps?new_app=1)。建立好 App 後，請為該工作空間建立一個「傳入的 WebHook」。建立之後，Slack 會提供一個 WebHook URL，在[為 Slack 通知路由](#routing-slack-notifications)時會使用到該 URL。

<a name="formatting-slack-notifications"></a>

### 格式化 Slack 通知

若要讓通知支援以 Slack 訊息傳送，請在該 Notification 類別上定義一個 `toSlack` 方法。這個方法會收到一個 `$notifiable` 實體，而該方法應回傳 `Illuminate\Notifications\Messages\SlackMessage` 實體。Slack 訊息可以包含文字內容，也可以包含一個「^[Attachment](附件)」。Attachment 就是格式化過的額外文字，或是一組欄位的陣列。讓我們來看看一個基礎的 `toSlack` 範例：

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
                    ->content('One of your invoices has been paid!');
    }

<a name="slack-attachments"></a>

### Slack Attachment

也可以在 Slack 訊息上加入「Attachment」。Attachment 比起簡單的文字訊息，有更豐富的格式可使用。在這個範例中，我們會傳送一個有關程式內發生 Exception 的錯誤訊息，其中包含了一個可用來檢視該 Exception 詳情的連結：

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $url = url('/exceptions/'.$this->exception->id);
    
        return (new SlackMessage)
                    ->error()
                    ->content('Whoops! Something went wrong.')
                    ->attachment(function ($attachment) use ($url) {
                        $attachment->title('Exception: File Not Found', $url)
                                   ->content('File [background.jpg] was not found.');
                    });
    }

使用 Attachment 時也可以指定一組用來顯示給使用者的資料陣列。給定的資料會以表格形式呈現以讓使用者輕鬆閱讀：

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $url = url('/invoices/'.$this->invoice->id);
    
        return (new SlackMessage)
                    ->success()
                    ->content('One of your invoices has been paid!')
                    ->attachment(function ($attachment) use ($url) {
                        $attachment->title('Invoice 1322', $url)
                                   ->fields([
                                        'Title' => 'Server Expenses',
                                        'Amount' => '$1,234',
                                        'Via' => 'American Express',
                                        'Was Overdue' => ':-1:',
                                    ]);
                    });
    }

<a name="markdown-attachment-content"></a>

#### Markdown 的 Attachment 內容

若有 Attachment 欄位包含 Markdown，可使用 `markdown` 方法來讓 Slack 以 Markdown 格式解析並顯示給定的欄位。該方法接受的值有：`pretext`、`text`、`fields`。有關 Slack Attachment 格式的更多資訊，請參考 [Slack API 說明文件](https://api.slack.com/docs/message-formatting#message_formatting)：

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $url = url('/exceptions/'.$this->exception->id);
    
        return (new SlackMessage)
                    ->error()
                    ->content('Whoops! Something went wrong.')
                    ->attachment(function ($attachment) use ($url) {
                        $attachment->title('Exception: File Not Found', $url)
                                   ->content('File [background.jpg] was *not found*.')
                                   ->markdown(['text']);
                    });
    }

<a name="routing-slack-notifications"></a>

### 為 Slack 通知路由

若要將 Slack 通知路由到正確的 Slack 團隊與頻道，請在 Notifiable 實體上定義一個 `routeNotificationForSlack` 方法。該方法應回傳該通知要傳送的 WebHook URL。若要產生 WebHook URL，可在 Slack 團隊中新增一個「傳入的 WebHook」：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    
    class User extends Authenticatable
    {
        use Notifiable;
    
        /**
         * Route notifications for the Slack channel.
         *
         * @param  \Illuminate\Notifications\Notification  $notification
         * @return string
         */
        public function routeNotificationForSlack($notification)
        {
            return 'https://hooks.slack.com/services/...';
        }
    }

<a name="localizing-notifications"></a>

## 本土化通知

在 Laravel 中，可以使用與 Request 中不同的語系設定來傳送通知。通知被放入佇列後依然會使用所設定的語系。

若要設定語系，請使用 `Illuminate\Notifications\Notification` 類別提供的 `locale` 方法來設定要使用的語言。在取得通知內容時，程式會先進入這個語系中，取完內容後再回到之前的語系：

    $user->notify((new InvoicePaid($invoice))->locale('es'));

可以使用 `Notification` Facade 來本地化多個 Notifiable 實體：

    Notification::locale('es')->send(
        $users, new InvoicePaid($invoice)
    );

<a name="user-preferred-locales"></a>

### 使用者偏好的語系

有時候，我們的程式會儲存每個使用者偏好的語言。只要在一個或多個 Notifiable Model 上實作 `HasLocalePreference` Contract，就可以讓 Laravel 在傳送通知時使用這些儲存的語系：

    use Illuminate\Contracts\Translation\HasLocalePreference;
    
    class User extends Model implements HasLocalePreference
    {
        /**
         * Get the user's preferred locale.
         *
         * @return string
         */
        public function preferredLocale()
        {
            return $this->locale;
        }
    }

實作好該介面後，向該 Model 傳送通知或 Mailable 時，Laravel 就會自動使用偏好的語系。因此，使用該介面時不需呼叫 `locale` 方法：

    $user->notify(new InvoicePaid($invoice));

<a name="notification-events"></a>

## 通知事件

<a name="notification-sending-event"></a>

#### 傳送中事件 - NotificationSending

在傳送通知時，通知系統會分派一個 `Illuminate\Notifications\Events\NotificationSending` [事件](/docs/{{version}}/events)。該 Event 中包含了一個「Notifiable」實體，以及通知實體本身。可以在專案的 `EventServiceProvider` 中為該 Event 註冊 Listener：

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Notifications\Events\NotificationSending' => [
            'App\Listeners\CheckNotificationStatus',
        ],
    ];

若 `NotificationSending` 事件的任一監聽程式中 `handle` 方法回傳 `false`，就不會傳送該通知：

    use Illuminate\Notifications\Events\NotificationSending;
    
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Notifications\Events\NotificationSending  $event
     * @return void
     */
    public function handle(NotificationSending $event)
    {
        return false;
    }

在 Event Listener 中，可以在該 Event 上存取 `notifiable`、`notification`、`channel` 等屬性，以取得更多有關通知收件人或通知本身的資訊：

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Notifications\Events\NotificationSending  $event
     * @return void
     */
    public function handle(NotificationSending $event)
    {
        // $event->channel
        // $event->notifiable
        // $event->notification
    }

<a name="notification-sent-event"></a>

#### 已傳送事件 - NotificationSent

在傳送通知時，通知系統會分派一個 `Illuminate\Notifications\Events\NotificationSent` [事件](/docs/{{version}}/events)。該 Event 中包含了一個「Notifiable」實體，以及通知實體本身。可以在專案的 `EventServiceProvider` 中為該 Event 註冊 Listener：

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Notifications\Events\NotificationSent' => [
            'App\Listeners\LogNotification',
        ],
    ];

> {tip} 在 `EventServiceProvider` 中註冊好 Listener 後，可使用 `event:generate` Artisan 指令來快速產生 Listener 類別。

在 Event Listener 中，可以在該 Event 上存取 `notifiable`、`notification`、`channel`、`response` 等屬性，以取得更多有關通知收件人或通知本身的資訊：

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Notifications\Events\NotificationSent  $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        // $event->channel
        // $event->notifiable
        // $event->notification
        // $event->response
    }

<a name="custom-channels"></a>

## 自訂通道

Laravel 中隨附了許多通知通道，不過，我們也可以自行撰寫自訂的 Driver 來使用其他通道傳送通知。在 Laravel 中，要製作自訂 Driver 非常簡單。要開始製作自訂 Driver，請先定義一個包含 `send` 方法的類別。該方法應接收兩個引數：`$notifiable` 與 `$notification`。

在 `send` 方法中，我們可以呼叫 Notification 上的方法，以取得這個頻道能理解的訊息物件，然後再將通知傳送給 `$notifiable` 實體：

    <?php
    
    namespace App\Notifications;
    
    use Illuminate\Notifications\Notification;
    
    class VoiceChannel
    {
        /**
         * Send the given notification.
         *
         * @param  mixed  $notifiable
         * @param  \Illuminate\Notifications\Notification  $notification
         * @return void
         */
        public function send($notifiable, Notification $notification)
        {
            $message = $notification->toVoice($notifiable);
    
            // Send notification to the $notifiable instance...
        }
    }

定義好通知通道後，接著就可以在任何 Notification 類別內的 `via` 方法中回傳我們自訂 Driver 的類別名稱。在這個範例中，Notification 的 `toVoice` 方法可以回傳任何要用來代表^[語音](Voice)訊息的物件。舉例來說，我們可以定義一個自訂的 `VoiceMessage` 類別來代表這些訊息：

    <?php
    
    namespace App\Notifications;
    
    use App\Notifications\Messages\VoiceMessage;
    use App\Notifications\VoiceChannel;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Notifications\Notification;
    
    class InvoicePaid extends Notification
    {
        use Queueable;
    
        /**
         * Get the notification channels.
         *
         * @param  mixed  $notifiable
         * @return array|string
         */
        public function via($notifiable)
        {
            return [VoiceChannel::class];
        }
    
        /**
         * Get the voice representation of the notification.
         *
         * @param  mixed  $notifiable
         * @return VoiceMessage
         */
        public function toVoice($notifiable)
        {
            // ...
        }
    }
