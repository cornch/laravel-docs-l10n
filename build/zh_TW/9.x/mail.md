---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/99/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 47.9
---

# 郵件

- [簡介](#introduction)
  - [設定](#configuration)
  - [Driver 前置需求](#driver-prerequisites)
  - [Failover 設定](#failover-configuration)
  
- [產生 Mailable](#generating-mailables)
- [撰寫 Mailable](#writing-mailables)
  - [設定寄件人](#configuring-the-sender)
  - [設定 View](#configuring-the-view)
  - [View 資料](#view-data)
  - [附加檔案](#attachments)
  - [內嵌的附加檔案](#inline-attachments)
  - [可附加的物件](#attachable-objects)
  - [標頭 (Header)](#headers)
  - [Tag 與詮釋資料](#tags-and-metadata)
  - [自訂 Symfony 訊息](#customizing-the-symfony-message)
  
- [Markdown 的 Mailable](#markdown-mailables)
  - [產生 Markdown 的 Mailable](#generating-markdown-mailables)
  - [撰寫 Markdown 訊息](#writing-markdown-messages)
  - [自定元件](#customizing-the-components)
  
- [傳送郵件](#sending-mail)
  - [將郵件放入佇列](#queueing-mail)
  
- [轉譯 Mailable](#rendering-mailables)
  - [在瀏覽器中預覽 Mailable](#previewing-mailables-in-the-browser)
  
- [本土化 Mailable](#localizing-mailables)
- [測試 Mailable](#testing-mailables)
- [郵件與本機開發](#mail-and-local-development)
- [事件](#events)
- [自訂 Transport](#custom-transports)
  - [額外的 Symfony Transport](#additional-symfony-transports)
  

<a name="introduction"></a>

## 簡介

傳送郵件不會很複雜。Laravel 提供簡潔的 API，並由熱門的 [Symfony Mailer](https://symfony.com/doc/6.0/mailer.html/) 驅動。Laravel 與 Symfony Mailer 提供使用 SMTP、Mailgun、Postmark、Amazon SES、`sendmail` 等方式寄信的 Driver，可讓我們使用偏好的本機或雲端服務來快速開始傳送郵件。

<a name="configuration"></a>

### 設定

可以使用專案的 `config/mail.php` 設定檔來設定 Laravel 的郵件服務。在這個檔案中，每個 ^[Mailer](%E9%83%B5%E4%BB%B6%E5%82%B3%E9%80%81%E7%A8%8B%E5%BC%8F) 都可以有不同的設定，甚至還可以設定不同的「Transport」設定，這樣我們就可以在程式中使用不同的電子郵件服務來寄送不同的訊息。舉例來說，我們可以使用 Postmark 來寄送交易電子郵件，並使用 Amazon SES 來傳送大量寄送的電子郵件。

在 `mail` 設定檔中，可以看到一個 `mailers` 設定陣列。這個陣列中包含了 Laravel 支援的各個主要郵件 Driver / Transport 範例設定，而其中 `default` 設定值用來判斷專案預設要使用哪個 Mailer 來傳送電子郵件訊息。

<a name="driver-prerequisites"></a>

### Driver / Transport 的前置要求

如 Mailgun 或 Postmark 等基於 API 的 Driver 與使用 SMTP 伺服器寄送郵件比起來通常會比較簡單快速。若可能的話，我們推薦儘量使用這類 Driver。

<a name="mailgun-driver"></a>

#### Mailgun Driver

若要使用 Mailgun Driver，請使用 Composer 安裝 Symfony 的 Mailgun Mailer Transport：

```shell
composer require symfony/mailgun-mailer symfony/http-client
```
接著，請在 `config/mail.php` 設定檔中將 `default` 選項設為 `mailgun`。設定好預設 Mailer 後，請確認一下 `config/services.php` 設定檔中是否包含下列選項：

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],
若你使用的 [Mailgun 地區](https://documentation.mailgun.com/en/latest/api-intro.html#mailgun-regions)不是美國的話，請在 `services` 設定檔中定義該地區的 Endpoint：

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.eu.mailgun.net'),
    ],
<a name="postmark-driver"></a>

#### Postmark Driver

若要使用 Postmark Driver，請使用 Composer 安裝 Symfony 的 Postmark Mailer Transport：

```shell
composer require symfony/postmark-mailer symfony/http-client
```
接著，請在 `config/mail.php` 設定檔中將 `default` 選項設為 `postmark`。設定好預設 Mailer 後，請確認一下 `config/services.php` 設定檔中是否包含下列選項：

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
若想為給定 Mailer 指定 Postmark ^[訊息串流](Message Stream)，請在該 Mailer 的設定陣列中加上 `message_stream_id` 設定選項。該設定陣列可在 `config/mail.php` 設定檔中找到：

    'postmark' => [
        'transport' => 'postmark',
        'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
    ],
這樣一來，我們就能設定多個 Postmark Mailer，並給不同 Mailer 設定不同的訊息串流。

<a name="ses-driver"></a>

#### SES Driver

若要使用 Amazon SES Driver，必須先安裝 PHP 版的 Amazon SDK。可使用 Composer 套件管理員來安裝這個函式庫：

```shell
composer require aws/aws-sdk-php
```
接著，請在 `config/mail.php` 設定檔中將 `default` 選項設為 `ses`，然後確認一下 `config/services.php` 設定檔中是否包含下列選項：

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
若要通過 Session Token 使用 AWS 的 [Temporary Credential](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_credentials_temp_use-resources.html)，請在專案的 SES 設定中加上 `token` 索引鍵：

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'token' => env('AWS_SESSION_TOKEN'),
    ],
若想定義要讓 Laravel 在寄送郵件時要傳給 AWS SDK 之 `SendEmail` 方法的[額外的選項](https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sesv2-2019-09-27.html#sendemail)，可在 `ses` 設定中定義一個 `options` 陣列：

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'options' => [
            'ConfigurationSetName' => 'MyConfigurationSet',
            'EmailTags' => [
                ['Name' => 'foo', 'Value' => 'bar'],
            ],
        ],
    ],
<a name="failover-configuration"></a>

### Failover 設定

有時候，我們設定要用來寄送郵件的外部服務可能沒辦法用。因為這種情況，所以最好定義一個或多個備用的郵件寄送設定，以免主要寄送 Driver 無法使用。

若要定義備用 Mailer，請在 `mail` 設定檔中定義一個使用 `failover` Transport的 Mailer。`failover` Mailer的設定值呢列應包含一個 `mailers` 的陣列，並在其中參照用來寄送郵件之各個 Driver 的順序：

    'mailers' => [
        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'postmark',
                'mailgun',
                'sendmail',
            ],
        ],
    
        // ...
    ],
定義好 Failover Mailer 後，請將 `mail` 設定檔中的 `default` 設定索引鍵設為該 Failover Mailer 的名稱，以將其設為預設 Mailer。

    'default' => env('MAIL_MAILER', 'failover'),
<a name="generating-mailables"></a>

## 產生 Mailable

在撰寫 Laravel 專案時，程式所寄出的所有郵件都以「Mailable」類別的形式呈現。這些類別保存在 `app/Mail` 目錄中。若沒看到這個目錄，請別擔心。使用 `make:mail` Artisan 指令初次建立 Mailable 類別時會自動產生該目錄：

```shell
php artisan make:mail OrderShipped
```
<a name="writing-mailables"></a>

## 撰寫 Mailable

產生 Mailable 類別後，請先開啟該類別，讓我們來看看該類別的內容。Mailable 類別可通過多個方法來進行設定，包含 `envelope`、`content`、與 `attachments` 方法。

`evelope` 方法回傳 `Illuminate\Mail\Mailables\Envelope` 物件，用來定義標題，而有的時候也會用來定義收件者與訊息。`content` 方法回傳 `Illuminate\Mail\Mailables\Content` 物件，該物件定義用來產生訊息內容的 [Blade 樣板](/docs/{{version}}/blade)。

<a name="configuring-the-sender"></a>

### 設定寄件人

<a name="using-the-envelope"></a>

#### 使用 Evelope

首先，我們先來看看如何設定寄件人。或者，換句話說，也就是郵件要「從 (From)」誰那裡寄出。要設定寄件人，有兩種方法。第一種方法，我們可以在訊息的 Evelope 上指定「from」位址：

    use Illuminate\Mail\Mailables\Address;
    use Illuminate\Mail\Mailables\Envelope;
    
    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            from: new Address('jeffrey@example.com', 'Jeffrey Way'),
            subject: 'Order Shipped',
        );
    }
若有需要的話，可以指定 `replyTo` 位址：

    return new Envelope(
        from: new Address('jeffrey@example.com', 'Jeffrey Way'),
        replyTo: [
            new Address('taylor@example.com', 'Taylor Otwell'),
        ],
        subject: 'Order Shipped',
    );
<a name="using-a-global-from-address"></a>

#### 使用全域的 `from` 位址

不過，若你的專案中所有的郵件都使用相同的寄件人位址，在每個產生的 Mailable 類別內都呼叫 `from` 方法會很麻煩。比起在每個 Mailable 內呼叫 `from` 方法，我們可以在 `config/mail.php` 設定檔中指定一個全域的「from」位址。若 Mailable 類別內沒有指定「from」位址，就會使用這個全域的位址：

    'from' => ['address' => 'example@example.com', 'name' => 'App Name'],
​此外，也可以在 `config/mail.php` 設定檔中定義一個全域的「reply_to」位址：

    'reply_to' => ['address' => 'example@example.com', 'name' => 'App Name'],
<a name="configuring-the-view"></a>

### ​設定 View

在 Mailable 類別的 `content` 方法中，可以定義 `view`，或者，可以說在 `content` 方法中指定轉譯郵件內容時要使用哪個樣板。由於一般來說大部分郵件都是使用 [Blade 樣板]來轉譯內容的，因此在建立郵件內容時，我們就可以使用 [Blade 樣板引擎](/docs/{{version}}/blade)的完整功能與便利：

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.orders.shipped',
        );
    }
> [!NOTE]  
> 可以建立一個 `resources/views/emails` 目錄來放置所有的郵件樣板。不過，不一定要放在這個目錄，可以隨意放在 `resources/views` 目錄下。

<a name="plain-text-emails"></a>

#### 純文字郵件

若想為郵件定義純文字版本，可以在定義訊息的 `Content` 時使用 `text` 方法。與 `view` 參數類似，`text` 參數應為用來轉譯 E-Mail 內容的樣板名稱。可以同時為訊息定義 HTML 與純文字的版本：

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.orders.shipped',
            text: 'emails.orders.shipped-text'
        );
    }
為了讓程式碼更清除，可以使用 `html` 參數。這個參數是 `view` 參數的別名：

    return new Content(
        html: 'emails.orders.shipped',
        text: 'emails.orders.shipped-text'
    );
<a name="view-data"></a>

### View 資料

<a name="via-public-properties"></a>

#### 使用公開屬性

一般來說，在轉譯 HTML 版本的郵件時，我們會需要將資料傳入 View 來在其中使用。要將資料傳入 View 有兩種方法。第一種方法，即是在 Mailable 類別裡的公用變數，在 View 裡面可以直接使用。因此，舉例來說，我們可以將資料傳入 Mailable 類別的 ^[Constructor](%E5%BB%BA%E6%A7%8B%E5%87%BD%E5%BC%8F) 內，然後將資料設為該類別中定義的公用變數：

    <?php
    
    namespace App\Mail;
    
    use App\Models\Order;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Queue\SerializesModels;
    
    class OrderShipped extends Mailable
    {
        use Queueable, SerializesModels;
    
        /**
         * The order instance.
         *
         * @var \App\Models\Order
         */
        public $order;
    
        /**
         * Create a new message instance.
         *
         * @param  \App\Models\Order  $order
         * @return void
         */
        public function __construct(Order $order)
        {
            $this->order = $order;
        }
    
        /**
         * Get the message content definition.
         *
         * @return \Illuminate\Mail\Mailables\Content
         */
        public function content()
        {
            return new Content(
                view: 'emails.orders.shipped',
            );
        }
    }
將資料設為公用變數後，在 View 中就自動可以使用該資料。因此在 Blade 樣板中，我們可以像存取其他資料一樣存取這些資料：

    <div>
        Price: {{ $order->price }}
    </div>
<a name="via-the-with-parameter"></a>

#### 通過 `with` 參數：

若想在資料被傳給樣板前自訂其格式，可使用 `Content` 定義的 `with` 參數來手動將資料傳給 View。一般來說，我們還是會使用 Mailable 類別的 Constroctor 來傳入資料。不過，我們可以將該資料設為 `protected` 或 `private` 屬性，這樣這些資料才不會被自動暴露到樣板中：

    <?php
    
    namespace App\Mail;
    
    use App\Models\Order;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Queue\SerializesModels;
    
    class OrderShipped extends Mailable
    {
        use Queueable, SerializesModels;
    
        /**
         * The order instance.
         *
         * @var \App\Models\Order
         */
        protected $order;
    
        /**
         * Create a new message instance.
         *
         * @param  \App\Models\Order  $order
         * @return void
         */
        public function __construct(Order $order)
        {
            $this->order = $order;
        }
    
        /**
         * Get the message content definition.
         *
         * @return \Illuminate\Mail\Mailables\Content
         */
        public function content()
        {
            return new Content(
                view: 'emails.orders.shipped',
                with: [
                    'orderName' => $this->order->name,
                    'orderPrice' => $this->order->price,
                ],
            );
        }
    }
使用 `with` 方法傳入資料後，在 View 中就自動可以使用該資料。因此在 Blade 樣板中，我們可以像存取其他資料一樣存取這些資料：

    <div>
        Price: {{ $orderPrice }}
    </div>
<a name="attachments"></a>

### 附加檔案

若要將附件加到 E-Mail 中，可以在訊息的 `attachments` 方法所回傳的陣列內加上附件。首先，我們需要將附件的檔案路徑提供給 `Attachment` 類別的 `fromPath` 方法來加上附件：

    use Illuminate\Mail\Mailables\Attachment;
    
    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromPath('/path/to/file'),
        ];
    }
將檔案附加至訊息時，也可以使用 `as` 與 `withMime` 方法來指定附件的顯示名稱與／或 MIME 型別：

    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromPath('/path/to/file')
                    ->as('name.pdf')
                    ->withMime('application/pdf'),
        ];
    }
<a name="attaching-files-from-disk"></a>

#### 從 Disk 中附加檔案

若有儲存在[檔案系統 Disk](/docs/{{version}}/filesystem)中的檔案，可使用 `fromStorage` 方法來將其附加至郵件中：

    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromStorage('/path/to/file'),
        ];
    }
當然，也可以指定附件的名稱與 MIME 型別：

    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromStorage('/path/to/file')
                    ->as('name.pdf')
                    ->withMime('application/pdf'),
        ];
    }
若想指定非預設的 Disk，可使用 `fromStorageDisk` 方法：

    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromStorageDisk('s3', '/path/to/file')
                    ->as('name.pdf')
                    ->withMime('application/pdf'),
        ];
    }
<a name="raw-data-attachments"></a>

#### 原始資料附加檔案

可使用 `fromData` 方法來將位元組原始字串 (Raw String of Bytes) 形式的值作為附件附加。舉例來說，我們可能會在記憶體內產生 PDF，然後想在不寫入 Disk 的情況下將其附加到郵件上。`fromData` 方法需傳入一個閉包，Laravel 會使用該閉包用來取得原始資料字串，以及附加檔案的名稱：

    /**
     * Get the attachments for the message.
     *
     * @return \Illuminate\Mail\Mailables\Attachment[]
     */
    public function attachments()
    {
        return [
            Attachment::fromData(fn () => $this->pdf, 'Report.pdf')
                    ->withMime('application/pdf'),
        ];
    }
<a name="inline-attachments"></a>

### 內嵌的附加檔案

一般來說，要把圖片內嵌到郵件裡面是很麻煩的。不過，Laravel 提供了一個方便的方法可以將圖片附加到郵件裡。若要內嵌圖片，請使用郵件樣板內 `$message` 變數中的 `embed` 方法。Laravel 會自動為所有的郵件樣板提供這個 `$message` 變數，所以我們不需要手動傳入：

```blade
<body>
    Here is an image:

    <img src="{{ $message->embed($pathToImage) }}">
</body>
```
> [!WARNING]  
> `$message` 變數無法在純文字訊息樣板中使用，因為純文字樣板無法使用內嵌的附加檔案。

<a name="embedding-raw-data-attachments"></a>

#### 內嵌原始資料附件

若有欲嵌入到郵件樣板中的原始圖片字串，可呼叫 `$message` 變數上的 `embedData` 方法。呼叫 `embedData` 方法時，請提供一個欲設定給嵌入圖片的檔案名稱：

```blade
<body>
    Here is an image from raw data:

    <img src="{{ $message->embedData($data, 'example-image.jpg') }}">
</body>
```
<a name="attachable-objects"></a>

### 可附加的物件

雖然一般來說，以簡單的字串路徑來將檔案附加到訊息上通常就夠了。但很多情況下，在專案中，可附加的物件都是以類別形式存在的。舉例來說，若要將照片附加到訊息中，則專案內可能有一個用來代表該照片的 `Photo` Model`。 這時，若可以直接將 `Photo`Model 附加到`attach` 方法上不是很方便嗎？使用可附加的物件，就可以輕鬆達成。

若要開始定義可附加物件，請在要被附加到訊息的物件上實作 `Illuminate\Contracts\Mail\Attachable` 介面。該介面會要求這個類別定義 `toMailAttachment`，且該方法應回傳 `Illuminate\Mail\Attachment` 實體：

    <?php
    
    namespace App\Models;
    
    use Illuminate\Contracts\Mail\Attachable;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Mail\Attachment;
    
    class Photo extends Model implements Attachable
    {
        /**
         * Get the attachable representation of the model.
         *
         * @return \Illuminate\Mail\Attachment
         */
        public function toMailAttachment()
        {
            return Attachment::fromPath('/path/to/file');
        }
    }
定義好可附加的物件後，就可以在建立 E-Mail 訊息時從 `attachments` 方法中回傳該物件的實體：

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [$this->photo];
    }
當然，要附加的資料也可能存放在如 Amazon S3 之類的遠端檔案儲存服務上。因此，在 Laravel 中，我們可以從存放在專案[檔案系統磁碟](/docs/{{version}}/filesystem)上的資料來產生附件實體：

    // Create an attachment from a file on your default disk...
    return Attachment::fromStorage($this->path);
    
    // Create an attachment from a file on a specific disk...
    return Attachment::fromStorageDisk('backblaze', $this->path);
此外，也可以使用記憶體中的資料來建立附件實體。若要從記憶體中建立，請傳入一個閉包給 `fromData` 方法。該閉包應回傳代表該附件的原始資料：

    return Attachment::fromData(fn () => $this->content, 'Photo Name');
Laravel 也提供了一些額外的方法，讓我們可以自訂附件。舉例來說，可以使用 `as` 與 `withMime` 方法來自訂檔案名稱與 MIME 型別：

    return Attachment::fromPath('/path/to/file')
            ->as('Photo Name')
            ->withMime('image/jpeg');
<a name="headers"></a>

### 標頭 (Header)

有時候，我們會需要在連外訊息中加上額外的標頭。舉例來說，我們可能會需要設定自定的 `Message-Id` 或其他任意的文字標頭。

若要設定標頭，請在 Mailable 內定義一個 `headers` 方法。`headers` 方法應回傳 `Illuminate\Mail\Mailables\Headers` 實體。該類別接受 `messageId`、`references`、與 `text` 參數。當然，我們只需要提供該訊息所需要的參數即可：

    use Illuminate\Mail\Mailables\Headers;
    
    /**
     * Get the message headers.
     *
     * @return \Illuminate\Mail\Mailables\Headers
     */
    public function headers()
    {
        return new Headers(
            messageId: 'custom-message-id@example.com',
            references: ['previous-message@example.com'],
            text: [
                'X-Custom-Header' => 'Custom Value',
            ],
        );
    }
<a name="tags-and-metadata"></a>

### Tag 與詮釋資料

有的第三方 E-Mail 提供商，如 Mailgun 或 Postmark 等，支援訊息的「Tag」與「詮釋資料 (Metadata)」，使用 Tag 與詮釋資料，就可以對專案所送出的 E-Mail 進行分組與追蹤。可以通過 `Evelope` 定義來為 E-Mail 訊息加上 Tag 與詮釋資料：

    use Illuminate\Mail\Mailables\Envelope;
    
    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Order Shipped',
            tags: ['shipment'],
            metadata: [
                'order_id' => $this->order->id,
            ],
        );
    }
若使用 Mailgun Driver，請參考 Mailgun 說明文件中有關 [Tag](https://documentation.mailgun.com/en/latest/user_manual.html#tagging-1) 與[詮釋資料](https://documentation.mailgun.com/en/latest/user_manual.html#attaching-data-to-messages)的更多資訊。同樣地，也請參考 Postmark 說明文件中有關 [Tag](https://postmarkapp.com/blog/tags-support-for-smtp) 與[詮釋資料](https://postmarkapp.com/support/article/1125-custom-metadata-faq)的更多資料。

若使用 Amazon SES 來寄送 E-Mail，則可使用 `metadata` 方法來將  [SES「Tag」](https://docs.aws.amazon.com/ses/latest/APIReference/API_MessageTag.html)附加到訊息上。

<a name="customizing-the-symfony-message"></a>

### 自訂 Symfony Message

Laravel 的郵件是使用 Symfony Mailer 驅動的。在 Laravel 中，我們可以註冊一個在寄送訊息前會被呼叫的回呼，該回呼會收到 Symfony Message 實體。這樣，我們就能在郵件被寄送前深度自定訊息。若要註冊這個回呼，可以在 `Evelope` 實體上定義一個 `using` 參數：

    use Illuminate\Mail\Mailables\Envelope;
    use Symfony\Component\Mime\Email;
    
    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Order Shipped',
            using: [
                function (Email $message) {
                    // ...
                },
            ]
        );
    }
<a name="markdown-mailables"></a>

## Markdown 的 Mailer

Markdown Mailer 訊息可讓我們在 Mailable 內使用內建樣板與 [Mail Notification](/docs/{{version}}/notifications#mail-notifications) 的元件。由於使用 Markdown 來撰寫訊息，因此 Laravel 就可為這些郵件轉譯出漂亮的回應式 HTML 樣板，並自動轉譯出純文字版本的郵件。

<a name="generating-markdown-mailables"></a>

### 產生 Markdown 的 Malable

若要產生有對應 Markdown 樣板的 Mailable，請使用 `make:mail` Artisan 指令的 `--markdown` 選項：

```shell
php artisan make:mail OrderShipped --markdown=emails.orders.shipped
```
接著，在 `content` 方法中設定 Mailable 的 `Content` 定義時，請將 `view` 參數改成 `markdown` 參數：

    use Illuminate\Mail\Mailables\Content;
    
    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'emails.orders.shipped',
            with: [
                'url' => $this->orderUrl,
            ],
        );
    }
<a name="writing-markdown-messages"></a>

### 撰寫 Markdown 訊息

Markdown 的 Markdown 使用 Blade 元件與 Markdown 格式的組合，讓我們能輕鬆地使用 Laravel 內建的 E-Mail UI 元件來建立訊息：

```blade
<x-mail::message>
# Order Shipped

Your order has been shipped!

<x-mail::button :url="$url">
View Order
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
```
> [!NOTE]  
> 在撰寫 Markdown 郵件時請不要增加縮排。依據 Markdown 標準，Markdown 解析程式會將縮排的內容轉譯為程式碼區塊。

<a name="button-component"></a>

#### Button 元件

Button 元件轉譯一個置中的按鈕連結。這個元件接受兩個引數，一個是 `url` 網址，另一個則是可選的 `color` 顏色。支援的顏色有 `primary`、`success`、`error`。在訊息中可以加上不限數量的 Button 元件：

```blade
<x-mail::button :url="$url" color="success">
View Order
</x-mail::button>
```
<a name="panel-component"></a>

#### Panel 元件

Panel 元件將給定的文字區塊轉譯在一個面板中，面板的底色與訊息中其他部分的背景色稍有不同。我們可以使用 Panel 元件來讓給定區塊的文字較為醒目：

```blade
<x-mail::panel>
This is the panel content.
</x-mail::panel>
```
<a name="table-component"></a>

#### Table 元件

Table 元件可讓我們將 Markdown 表格轉為 HTML 表格。該元件接受一個 Markdown 表格作為其內容。支援使用預設的 Markdown 表格對其格式來對其表格欄位：

```blade
<x-mail::table>
| Laravel       | Table         | Example  |
| ------------- |:-------------:| --------:|
| Col 2 is      | Centered      | $10      |
| Col 3 is      | Right-Aligned | $20      |
</x-mail::table>
```
<a name="customizing-the-components"></a>

### 自訂元件

可以將所有的 Markdown 郵件元件匯出到專案內來自訂這些元件。若要匯出元件，請使用 `vendor:publish` Artisan  指令來^[安裝](Publish) `laravel-mail` 素材標籤：

```shell
php artisan vendor:publish --tag=laravel-mail
```
這個指令會將 Markdown 郵件元件安裝到 `resources/views/vendor/mail` 目錄下。`mail` 目錄會包含 `html` 與 `text` 目錄，這些目錄中包含了所有可用元件對應的呈現方式。可以隨意自訂這些元件。

<a name="customizing-the-css"></a>

#### 自訂 CSS

匯出元件後，`resources/views/vendor/mail/html/themes` 目錄下會包含一個 `default.css` 檔案。可以自訂這個檔案內的 CSS。這些樣式在 Markdown 郵件訊息的 HTML 呈現上會自動被轉換為內嵌的 CSS 樣式：

若想為 Laravel Markdown 元件製作一個全新的主題，可在 `html/themes` 目錄下放置一個 CSS 檔。命名好 CSS 檔並保存後，請修改專案 `config/mail.php` 設定檔中的 `theme` 選項為該新主題的名稱：

若要為個別 Mailable 自訂主題，可在 Mailable 類別上將 `$theme` 屬性設為傳送該 Mailable 時要使用的主題名稱：

<a name="sending-mail"></a>

## 傳送郵件

若要傳送郵件，請使用 `Mail` Facade`上的`to`方法。可傳入電子郵件位址、使用者實體、或是一組包含使用者的 Collection 給`to`方法。若傳入物件或一組包含物件的 Collection，則 Mailer 在判斷收件人時會自動使用這些物件的`email`與`name`屬性來判斷。因此，請確認這些物件上是否有這兩個屬性。指定好收件人後，就可傳入 Mailable 類別的實體給`send` 方法：

    <?php
    
    namespace App\Http\Controllers;
    
    use App\Http\Controllers\Controller;
    use App\Mail\OrderShipped;
    use App\Models\Order;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Mail;
    
    class OrderShipmentController extends Controller
    {
        /**
         * Ship the given order.
         *
         * @param  \Illuminate\Http\Request  $request
         * @return \Illuminate\Http\Response
         */
        public function store(Request $request)
        {
            $order = Order::findOrFail($request->order_id);
    
            // Ship the order...
    
            Mail::to($request->user())->send(new OrderShipped($order));
        }
    }
傳送訊息時，除了「to」方法能用來指定收件人外，還可以指定「^[CC](%E5%89%AF%E6%9C%AC)」與「^[BCC](%E5%AF%86%E4%BB%B6%E5%89%AF%E6%9C%AC)」收件人。可將「to」、「cc」、「bcc」等方法串聯使用，以指定這些方法對應的收件人：

    Mail::to($request->user())
        ->cc($moreUsers)
        ->bcc($evenMoreUsers)
        ->send(new OrderShipped($order));
<a name="looping-over-recipients"></a>

#### 在收件人中迴圈

有時候，我們會需要迭代一組收件人或 E-Mail 位址的陣列來將 Mailable 傳送給多個收件人。不過，因為 `to` 方法會將 E-Mail 位址加到 Mailable 的收件人列表上，因此每次循環都會將該郵件再傳送給之前的收件人一次。所以，每個收件人都需要重新建立一個新的 Mailable 實體：

    foreach (['taylor@example.com', 'dries@example.com'] as $recipient) {
        Mail::to($recipient)->send(new OrderShipped($order));
    }
<a name="sending-mail-via-a-specific-mailer"></a>

#### 使用指定的 Mailer 來傳送郵件

預設情況下，Laravel 會使用專案 `mail` 設定中設為 `default` 的 Mailaer 來寄送郵件。不過，也可以使用 `mailer` 方法來特定的 Mailer 設定傳送訊息：

    Mail::mailer('postmark')
            ->to($request->user())
            ->send(new OrderShipped($order));
<a name="queueing-mail"></a>

### 將郵件放入佇列

<a name="queueing-a-mail-message"></a>

#### 將郵件訊息放入佇列

由於傳送郵件訊息可能對程式的 Response 時間造成負面影響，因此許多開發人員都選擇將郵件訊息放入陣列來在背景執行。在 Laravel 中，使用內建的[統一佇列 API](/docs/{{version}}/queues)，就能輕鬆地將郵件放入佇列。若要將郵件訊息放入佇列，請在指定好收件人後使用 `Mail` Facade 的 `queue` 方法：

    Mail::to($request->user())
        ->cc($moreUsers)
        ->bcc($evenMoreUsers)
        ->queue(new OrderShipped($order));
這個方法會自動將任務推入佇列，這樣訊息就會在背景傳送。在使用這個功能前，會需要先[設定佇列](/docs/{{version}}/queues)。

<a name="delayed-message-queueing"></a>

#### 延遲訊息佇列

若想延遲傳送某個佇列訊息，可使用 `later` 方法。`later` 方法的第一個引數是 `DateTime` 實體，用來表示該訊息何時寄出：

    Mail::to($request->user())
        ->cc($moreUsers)
        ->bcc($evenMoreUsers)
        ->later(now()->addMinutes(10), new OrderShipped($order));
<a name="pushing-to-specific-queues"></a>

#### 推入指定的佇列

由於所有使用 `make:mail` 指令產生的 Mailable 類別都使用 `Illiminate\Bus\Queuable` Trait，因此我們可以在任何一個 Mailable 類別實體上呼叫 `onQueue` 與 `onConnection` 方法，可讓我們指定該訊息要使用的佇列名稱：

    $message = (new OrderShipped($order))
                    ->onConnection('sqs')
                    ->onQueue('emails');
    
    Mail::to($request->user())
        ->cc($moreUsers)
        ->bcc($evenMoreUsers)
        ->queue($message);
<a name="queueing-by-default"></a>

#### 預設佇列

若有想要永遠放入佇列的 Mailable 類別，可在該類別上實作 `ShouldQueue` Contract。接著，即使使用 `send` 方法來寄送郵件，由於該 Mailable 有實作 `ShouldQueue` Contract，因此還是會被放入佇列：

    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class OrderShipped extends Mailable implements ShouldQueue
    {
        //
    }
<a name="queued-mailables-and-database-transactions"></a>

#### 佇列的 Mailable 與資料庫 Transaction

當佇列 Mailable 是在資料庫 Transaction 內^[分派](Dispatch)的時候，這個 Mailable 可能會在資料庫 Transaction 被 Commit 前就被佇列進行處理了。發生這種情況時，在資料庫 Transaction 期間對 Model 或資料庫記錄所做出的更新可能都還未反應到資料庫內。另外，所有在 Transaction 期間新增的 Model 或資料庫記錄也可能還未出現在資料庫內。若 Mailable 有使用這些 Model 的話，在處理該佇列 Mailable 的任務時可能會出現未預期的錯誤。

若佇列的 `after_commit` 選項設為 `false`，則我們還是可以通過在寄送郵件訊息前呼叫 `afterCommit` 方法來表示出該 Mailable 應在所有資料庫 Transaction 都被 Commit 後才分派：

    Mail::to($request->user())->send(
        (new OrderShipped($order))->afterCommit()
    );
或者，也可以在 Mailable 的 Constructor 上呼叫 `afterCommit` 方法：

    <?php
    
    namespace App\Mail;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Mail\Mailable;
    use Illuminate\Queue\SerializesModels;
    
    class OrderShipped extends Mailable implements ShouldQueue
    {
        use Queueable, SerializesModels;
    
        /**
         * Create a new message instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->afterCommit();
        }
    }
> [!NOTE]  
> 要瞭解更多有關這類問題的解決方法，請參考有關[佇列任務與資料庫 Transaction](/docs/{{version}}/queues#jobs-and-database-transactions) 有關的說明文件。

<a name="rendering-mailables"></a>

## 轉譯 Mailable

有時候我們會想在不寄送 Mailable 的情況下截取其 HTML 內容。若要截取其內容，可呼叫 Mailable 的 `render` 方法。該方法會以字串回傳該 Mailable 的 HTML 取值內容：

    use App\Mail\InvoicePaid;
    use App\Models\Invoice;
    
    $invoice = Invoice::find(1);
    
    return (new InvoicePaid($invoice))->render();
<a name="previewing-mailables-in-the-browser"></a>

### 在瀏覽器內預覽 Mailable

在設計 Mailable 樣板時，若能像普通的 Blade 樣板一樣在瀏覽器中預覽轉譯後的 Mailable 該有多方便。因為這樣，在 Laravel 中，可以直接在 Route 閉包或 Controller 中回傳任何的 Mailable。若回傳 Mailable，則會轉譯該 Mailable 並顯示在瀏覽器上，讓我們不需將其寄到真實的電子郵件上也能快速檢視其設計：

    Route::get('/mailable', function () {
        $invoice = App\Models\Invoice::find(1);
    
        return new App\Mail\InvoicePaid($invoice);
    });
> [!WARNING]  
> 在瀏覽器中預覽 Mailable 時，不會轉譯[內嵌的附件](#inline-attachments)。若要瀏覽有內嵌附件的 Mailable，請將郵件傳送到如 [Mailpit](https://github.com/axllent/mailpit) 或 [HELO](https://usehelo.com) 之類的郵件測試程式。

<a name="localizing-mailables"></a>

## 本土化 Mailable

在 Laravel 中，可以使用與 Request 中不同的語系設定來傳送郵件，且在郵件被放入佇列後依然會使用所設定的語系。

若要設定語系，請使用 `Mail` Facade 提供的 `locale` 方法來設定要使用的語言。在轉譯 Mailable 樣板時，程式會先進入這個語系中，轉譯完畢後再回到之前的語系：

    Mail::to($request->user())->locale('es')->send(
        new OrderShipped($order)
    );
<a name="user-preferred-locales"></a>

### 使用者偏好的語系

有時候，我們的程式會儲存每個使用者偏好的語言。只要在一個或多個 Model 上實作 `HasLocalePreference` Contract，就可以讓 Laravel 在寄送郵件時使用這些儲存的語系：

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
實作好該介面後，向該 Model 寄送 Mailable 或通知時，Laravel 會自動使用偏好的語系。因此，使用該介面時不需呼叫 `locale` 方法：

    Mail::to($request->user())->send(new OrderShipped($order));
<a name="testing-mailables"></a>

## 測試 Mailable

Laravel 提供了各種可用來檢查 Mailable 結構的方法。此外，Laravel 還提供了多種方便的方法，可讓你測試 Mailable 是否包含預期的內容。這些測試方法有：`assertSeeInHtml`, `assertDontSeeInHtml`, `assertSeeInOrderInHtml`, `assertSeeInText`, `assertDontSeeInText`, `assertSeeInOrderInText`, `assertHasAttachment`, `assertHasAttachedData`, `assertHasAttachmentFromStorage`, 與 `assertHasAttachmentFromStorageDisk`。

就和預期的一樣，有「HTML」的^ [Assertion](%E5%88%A4%E6%96%B7%E6%8F%90%E7%A4%BA) 判斷 HTML 版本的 Mailable 是否包含給定字串，而「Text」版本的 Assertion 則判斷純文字版本的 Mailable 是否包含給定字串：

    use App\Mail\InvoicePaid;
    use App\Models\User;
    
    public function test_mailable_content()
    {
        $user = User::factory()->create();
    
        $mailable = new InvoicePaid($user);
    
        $mailable->assertFrom('jeffrey@example.com');
        $mailable->assertTo('taylor@example.com');
        $mailable->assertHasCc('abigail@example.com');
        $mailable->assertHasBcc('victoria@example.com');
        $mailable->assertHasReplyTo('tyler@example.com');
        $mailable->assertHasSubject('Invoice Paid');
        $mailable->assertHasTag('example-tag');
        $mailable->assertHasMetadata('key', 'value');
    
        $mailable->assertSeeInHtml($user->email);
        $mailable->assertSeeInHtml('Invoice Paid');
        $mailable->assertSeeInOrderInHtml(['Invoice Paid', 'Thanks']);
    
        $mailable->assertSeeInText($user->email);
        $mailable->assertSeeInOrderInText(['Invoice Paid', 'Thanks']);
    
        $mailable->assertHasAttachment('/path/to/file');
        $mailable->assertHasAttachment(Attachment::fromPath('/path/to/file'));
        $mailable->assertHasAttachedData($pdfData, 'name.pdf', ['mime' => 'application/pdf']);
        $mailable->assertHasAttachmentFromStorage('/path/to/file', 'name.pdf', ['mime' => 'application/pdf']);
        $mailable->assertHasAttachmentFromStorageDisk('s3', '/path/to/file', 'name.pdf', ['mime' => 'application/pdf']);
    }
<a name="testing-mailable-sending"></a>

#### 測試 Mailable 的寄送

在測試郵件是否有寄給特定使用者時，我們建議與 Mailable 的內容分開測試。若要瞭解如何測試郵件是否有寄出，請參考有關 [Mail 模擬](/docs/{{version}}/mocking#mail-fake)的說明文件。

<a name="mail-and-local-development"></a>

## 郵件與本機開發

在開發有寄送郵件的程式時，我們通常都不會想實際將郵件寄到真實的 E-Mail 位址上。Laravel 提供了數種數種方法來在本機上開發時「禁用」郵件的實際傳送。

<a name="log-driver"></a>

#### Log Driver

`log` 郵件 Driver 不會實際寄送電子郵件，而是將所有電子郵件訊息寫入日誌檔以供檢查。一般來說，Log Driver 只會在開發環境上使用。有關一找不同環境設定專案的方法，請參考[設定的說明文件](/docs/{{version}}/configuration#environment-configuration)。

<a name="mailtrap"></a>

#### HELO / Mailtrap / Mailpit

或者，也可以使用如 [HELO](https://usehelo.com) 或 [Mailtrap](https://mailtrap.io) 這類服務搭配 `smtp` Driver 來將電子郵件寄送到一個「模擬的」收件夾，並像在真的郵件用戶端一樣檢視這些郵件。這種做法的好處就是可以在 Mailtrap 的訊息檢視工具中實際檢視寄出的郵件。

若使用 [Laravel Sail](/docs/{{version}}/sail),，則可使用 [Mailpit](https://github.com/axllent/mailpit) 來預覽訊息。當 Sail 有在執行時，可在 `http://localhost:8025` 上存取 Mailpit  的界面。

<a name="using-a-global-to-address"></a>

#### 使用全域的 `to` 位址

最後一種方法，就是我們可以叫用 `Mail` Facade 提供的 `alwaysTo` 方法指定一個全域的「to」位址。一般來說，應在專案的其中一個 Service Provider 內 `boot` 方法中呼叫這個方法：

    use Illuminate\Support\Facades\Mail;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('local')) {
            Mail::alwaysTo('taylor@example.com');
        }
    }
<a name="events"></a>

## 事件

在處理郵件訊息寄送時，Laravel 會觸發兩個事件。`MessageSending` 事件會在寄出郵件前觸發，而`MessageSent` 事件則會在訊息寄出後觸發。請記得，這些事件都是在 *寄送* 郵件的時候出發的，而不是在放入佇列時觸發。可以在 `App\Providers\EventServiceProvider` Service Provider 上為這些 Event 註冊 Listener：

    use App\Listeners\LogSendingMessage;
    use App\Listeners\LogSentMessage;
    use Illuminate\Mail\Events\MessageSending;
    use Illuminate\Mail\Events\MessageSent;
    
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        MessageSending::class => [
            LogSendingMessage::class,
        ],
    
        MessageSent::class => [
            LogSentMessage::class,
        ],
    ];
<a name="custom-transports"></a>

## 自訂 Transport

Laravel 中包含了許多的 Mail Transport。不過，有時候我們可能會需要撰寫自己的 Transport 來使用 Laravel 預設未支援的其他服務來寄送郵件。要開始撰寫 Transport，請先定義一個繼承了`Symfony\Component\Mailer\Transport\AbstractTransport` 的類別。接著，請在該 Transport 上實作 `doSend` 與 `__toString()` 方法：

    use MailchimpTransactional\ApiClient;
    use Symfony\Component\Mailer\SentMessage;
    use Symfony\Component\Mailer\Transport\AbstractTransport;
    use Symfony\Component\Mime\MessageConverter;
    
    class MailchimpTransport extends AbstractTransport
    {
        /**
         * The Mailchimp API client.
         *
         * @var \MailchimpTransactional\ApiClient
         */
        protected $client;
    
        /**
         * Create a new Mailchimp transport instance.
         *
         * @param  \MailchimpTransactional\ApiClient  $client
         * @return void
         */
        public function __construct(ApiClient $client)
        {
            parent::__construct();
            
            $this->client = $client;
        }
    
        /**
         * {@inheritDoc}
         */
        protected function doSend(SentMessage $message): void
        {
            $email = MessageConverter::toEmail($message->getOriginalMessage());
    
            $this->client->messages->send(['message' => [
                'from_email' => $email->getFrom(),
                'to' => collect($email->getTo())->map(function ($email) {
                    return ['email' => $email->getAddress(), 'type' => 'to'];
                })->all(),
                'subject' => $email->getSubject(),
                'text' => $email->getTextBody(),
            ]]);
        }
    
        /**
         * Get the string representation of the transport.
         *
         * @return string
         */
        public function __toString(): string
        {
            return 'mailchimp';
        }
    }
定義好自訂 Transport 後，就可以使用 `Mail` Facade 的 `extend` 方法來註冊這個 Transport。一般來說，應在 `AppServiceProvider` Service Provider 中 `boot` 方法內註冊這個 Transport。傳給 `extend` 方法的閉包會收到一個 `$config` 引數。這個引數中會包含在專案 `config/mail.php` 內定義給該方法的設定陣列：

    use App\Mail\MailchimpTransport;
    use Illuminate\Support\Facades\Mail;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Mail::extend('mailchimp', function (array $config = []) {
            return new MailchimpTransport(/* ... */);
        });
    }
定義並註冊好自訂 Transport 後，就可以在專案 `config/mail.php` 設定檔內建立一個使用這個新 Transport 的 Mailer 定義：

    'mailchimp' => [
        'transport' => 'mailchimp',
        // ...
    ],
<a name="additional-symfony-transports"></a>

### 額外的 Symfony Transport

Laravel 支援一些像是 Mailgun 與 Postmark 等現有 Symfony 維護的 Mail Transport。不過，有時候我們可能會需要讓 Laravel 也支援其他由 Symfony 維護的 Transport。若要讓 Laravel 支援這些 Transport，只要使用 Composer 安裝這些 Symfony Mailer，然後再向 Laravel 註冊這個 Transport。舉例來說，我們可以安裝並註冊「Sendinblue」Symfony Mailer：

```none
composer require symfony/sendinblue-mailer symfony/http-client
```
安裝好 Sendinblue Mailer 套件後，就可以在專案的 `services` 設定檔中加上 Sendinblue 的 API 認證：

    'sendinblue' => [
        'key' => 'your-api-key',
    ],
接著，使用 `Mail` Facade 的 `extend` 方法來向 Laravel 註冊這個 Transport。一般來說，應在某個 Service Provider 內註冊一個 `boot` 方法：

    use Illuminate\Support\Facades\Mail;
    use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
    use Symfony\Component\Mailer\Transport\Dsn;
    
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Mail::extend('sendinblue', function () {
            return (new SendinblueTransportFactory)->create(
                new Dsn(
                    'sendinblue+api',
                    'default',
                    config('services.sendinblue.key')
                )
            );
        });
    }
註冊好 Transport 後，就可以在專案的 config/mail.php 設定檔中建立一個使用這個新 Transport 的 Mailer 定義：

    'sendinblue' => [
        'transport' => 'sendinblue',
        // ...
    ],