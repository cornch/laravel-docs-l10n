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
  - [自訂 SwiftMailer 訊息](#customizing-the-swiftmailer-message)
  
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

<a name="introduction"></a>

## 簡介

傳送郵件不會很複雜。Laravel 提供簡潔的 API，並由熱門的 [SwiftMailer](https://swiftmailer.symfony.com/) 函式庫驅動。Laravel 與 SwiftMailer 提供使用 SMTP、Mailgun、Postmark、Amazon SES、`sendmail` 等方式寄信的 Driver，可讓我們使用偏好的本機或雲端服務來快速開始傳送郵件。

<a name="configuration"></a>

### 設定

可以使用專案的 `config/mail.php` 設定檔來設定 Laravel 的郵件服務。在這個檔案中，每個 ^[Mailer](%E9%83%B5%E4%BB%B6%E5%82%B3%E9%80%81%E7%A8%8B%E5%BC%8F) 都可以有不同的設定，甚至還可以設定不同的「Transport」設定，這樣我們就可以在程式中使用不同的電子郵件服務來寄送不同的訊息。舉例來說，我們可以使用 Postmark 來寄送交易電子郵件，並使用 Amazon SES 來傳送大量寄送的電子郵件。

在 `mail` 設定檔中，可以看到一個 `mailers` 設定陣列。這個陣列中包含了 Laravel 支援的各個主要郵件 Driver / Transport 範例設定，而其中 `default` 設定值用來判斷專案預設要使用哪個 Mailer 來傳送電子郵件訊息。

<a name="driver-prerequisites"></a>

### Driver / Transport 的前置要求

如 Mailgun 與 Postmark 等基於 API 的 Driver 在寄送郵件時通常會比 SMTP 伺服器來得簡單快速。若可能的話，我們建議你從這幾個 Driver 中選一個使用。這些基於 API 的 Driver 都要求要有 Guzzle HTTP 函式庫，可以通過 Composer 套件管理員來安裝 Guzzle HTTP 函式庫：

    composer require guzzlehttp/guzzle
<a name="mailgun-driver"></a>

#### Mailgun Driver

若要使用 Mailgun Driver，請先安裝 Guzzle HTTP 函式庫。接著，在 `config/mail.php` 設定檔中將 `default` 選項設為 `mailgun`。接著，請確認一下 `config/services.php` 設定檔中是否包含下列選項：

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

若要使用 Postmark Driver，請使用 Composer 安裝 Postmark 的 SwiftMailer Transport：

    composer require wildbit/swiftmailer-postmark
接著，請安裝 Guzzle HTTP 函式庫。然後，在 `config/mail.php` 設定檔中將 `default` 選項設為 `postmark`。最後，請確認一下 `config/services.php` 設定檔中是否包含下列選項：

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

```bash
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
若想定義要讓 Laravel 在寄送郵件時要傳給 AWS SDK 之 `SendRawEmail` 方法的[額外的選項](https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-email-2010-12-01.html#sendrawemail)，可在 `ses` 設定中定義一個 `options` 陣列：

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'options' => [
            'ConfigurationSetName' => 'MyConfigurationSet',
            'Tags' => [
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

    php artisan make:mail OrderShipped
<a name="writing-mailables"></a>

## 撰寫 Mailable

產生好 Mailable 類別後，請打開該類別，我們來看看裡面的內容。首先，可以注意到所有的 Mailable 類別都在 `build` 方法內進行設定。在該方法中，可呼叫如 `form`、`view`、`attach` 等方法來設定 E-Mail 的顯示方式與寄送設定。

> [!TIP]  
> You may type-hint dependencies on the mailable's `build` method. The Laravel [service container](/docs/{{version}}/container) automatically injects these dependencies.

<a name="configuring-the-sender"></a>

### 設定寄件人

<a name="using-the-from-method"></a>

#### 使用 `from` 方法

首先，我們先來看看如何設定寄件人。或者，換句話說，也就是郵件要「^[從](From)」誰那裡寄出。要設定寄件人，有兩種方法。第一種方法，我們可以在 Mailable 類別的 `build` 方法內使用 `from` 方法來設定：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('example@example.com', 'Example')
                    ->view('emails.orders.shipped');
    }
<a name="using-a-global-from-address"></a>

#### 使用全域的 `from` 位址

不過，若你的專案中所有的郵件都使用相同的寄件人位址，在每個產生的 Mailable 類別內都呼叫 `from` 方法會很麻煩。比起在每個 Mailable 內呼叫 `from` 方法，我們可以在 `config/mail.php` 設定檔中指定一個全域的「from」位址。若 Mailable 類別內沒有指定「from」位址，就會使用這個全域的位址：

    'from' => ['address' => 'example@example.com', 'name' => 'App Name'],
​此外，也可以在 `config/mail.php` 設定檔中定義一個全域的「reply_to」位址：

    'reply_to' => ['address' => 'example@example.com', 'name' => 'App Name'],
<a name="configuring-the-view"></a>

### ​設定 View

在 Mailable 類別的 `build` 方法中，可以使用 `view` 方法來指定在轉譯郵件內容時欲使用哪個樣板。由於一般來說大部分郵件都是使用 [Blade 樣板]來轉譯內容的，因此在建立郵件內容時，我們就可以使用 [Blade 樣板引擎](/docs/{{version}}/blade)的完整功能與便利：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.orders.shipped');
    }
> [!TIP]  
> 可以建立一個 `resources/views/emails` 目錄來放置所有的郵件樣板。不過，不一定要放在這個目錄，可以隨意放在 `resources/views` 目錄下。

<a name="plain-text-emails"></a>

#### 純文字郵件

若想為郵件定義純文字版本，可使用 `text` 方法。與 `view` 方法一樣，`text` 方法接受一個用來轉譯郵件內容的樣板名稱。可以同時為郵件定義 HTML 與純文字版本：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.orders.shipped')
                    ->text('emails.orders.shipped_plain');
    }
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
         * Build the message.
         *
         * @return $this
         */
        public function build()
        {
            return $this->view('emails.orders.shipped');
        }
    }
將資料設為公用變數後，在 View 中就自動可以使用該資料。因此在 Blade 樣板中，我們可以像存取其他資料一樣存取這些資料：

    <div>
        Price: {{ $order->price }}
    </div>
<a name="via-the-with-method"></a>

#### 通過 `with` 方法：

若想在資料被傳給樣板前自訂其格式，可使用 `with` 方法來手動傳入資料。一般來說，我們還是會使用 Mailable 類別的 Constroctor 來傳入資料。不過，我們可以將該資料設為 `protected` 或 `private` 屬性，這樣樣板中才不會有這些資料。接著，呼叫 `with` 方法，傳入欲在樣板中使用的資料：

    <?php
    
    namespace App\Mail;
    
    use App\Models\Order;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
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
         * Build the message.
         *
         * @return $this
         */
        public function build()
        {
            return $this->view('emails.orders.shipped')
                        ->with([
                            'orderName' => $this->order->name,
                            'orderPrice' => $this->order->price,
                        ]);
        }
    }
使用 `with` 方法傳入資料後，在 View 中就自動可以使用該資料。因此在 Blade 樣板中，我們可以像存取其他資料一樣存取這些資料：

    <div>
        Price: {{ $orderPrice }}
    </div>
<a name="attachments"></a>

### 附加檔案

若要將檔案附加至 E-Mail，請使用 Mailable 類別 `build` 方法中的 `attach` 方法。`attach` 方法接受檔案的完整路徑作為其第一個引數：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.orders.shipped')
                    ->attach('/path/to/file');
    }
將檔案附加至訊息時，也可傳入一個陣列給 `attach` 方法來指定要顯示的檔案名稱與 / 或 MIME 類型：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.orders.shipped')
                    ->attach('/path/to/file', [
                        'as' => 'name.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
<a name="attaching-files-from-disk"></a>

#### 從 Disk 中附加檔案

若有儲存在[檔案系統 Disk](/docs/{{version}}/filesystem)中的檔案，可使用 `attachFromStorage` 方法來將其附加至郵件中：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->view('emails.orders.shipped')
                   ->attachFromStorage('/path/to/file');
    }
若有需要，可使用 `attachFromStorage` 方法的第三與第四個引數來指定檔案名稱與其他額外的選項：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->view('emails.orders.shipped')
                   ->attachFromStorage('/path/to/file', 'name.pdf', [
                       'mime' => 'application/pdf'
                   ]);
    }
若想指定預設以外的 Disk，可使用 `attachFromStorageDisk` 方法：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->view('emails.orders.shipped')
                   ->attachFromStorageDisk('s3', '/path/to/file');
    }
<a name="raw-data-attachments"></a>

#### 原始資料附加檔案

可使用 `attachData` 方法來以位元組原始字串的形式作為附件附加。舉例來說，我們可能會在記憶體內產生 PDF，然後想在不寫入 Disk 的情況下將其附加到郵件上。`attachData` 方法接受原始資料位元組作為其第一個引數，檔案名稱為其第二個引數，然後是一組選項陣列作為其第三個引數：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.orders.shipped')
                    ->attachData($this->pdf, 'name.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
<a name="inline-attachments"></a>

### 內嵌的附加檔案

一般來說，要把圖片內嵌到郵件裡面是很麻煩的。不過，Laravel 提供了一個方便的方法可以將圖片附加到郵件裡。若要內嵌圖片，請使用郵件樣板內 `$message` 變數中的 `embed` 方法。Laravel 會自動為所有的郵件樣板提供這個 `$message` 變數，所以我們不需要手動傳入：

    <body>
        Here is an image:
    
        <img src="{{ $message->embed($pathToImage) }}">
    </body>
> [!NOTE]  
> `$message` 變數無法在純文字訊息樣板中使用，因為純文字樣板無法使用內嵌的附加檔案。

<a name="embedding-raw-data-attachments"></a>

#### 內嵌原始資料附件

若有欲嵌入到郵件樣板中的原始圖片字串，可呼叫 `$message` 變數上的 `embedData` 方法。呼叫 `embedData` 方法時，請提供一個欲設定給嵌入圖片的檔案名稱：

    <body>
        Here is an image from raw data:
    
        <img src="{{ $message->embedData($data, 'example-image.jpg') }}">
    </body>
<a name="customizing-the-swiftmailer-message"></a>

### 自訂 SwiftMailer 訊息

`Mailable` 基礎類別的 `withSwiftMessage` 方法可讓我們註冊一個閉包，在傳送訊息前會以 SwiftMailer 實體叫用該閉包。這樣我們就有機會在郵件被送出前深度自訂該訊息：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->view('emails.orders.shipped');
    
        $this->withSwiftMessage(function ($message) {
            $message->getHeaders()->addTextHeader(
                'Custom-Header', 'Header Value'
            );
        });
    
        return $this;
    }
<a name="markdown-mailables"></a>

## Markdown 的 Mailer

Markdown Mailer 訊息可讓我們在 Mailable 內使用內建樣板與 [Mail Notification](/docs/{{version}}/notifications#mail-notifications) 的元件。由於使用 Markdown 來撰寫訊息，因此 Laravel 就可為這些郵件轉譯出漂亮的回應式 HTML 樣板，並自動轉譯出純文字版本的郵件。

<a name="generating-markdown-mailables"></a>

### 產生 Markdown 的 Malable

若要產生有對應 Markdown 樣板的 Mailable，請使用 `make:mail` Artisan 指令的 `--markdown` 選項：

    php artisan make:mail OrderShipped --markdown=emails.orders.shipped
接著，在 `build` 方法內設定 Mailable 時，不呼叫 `view` 方法，而是改呼叫 `markdown` 方法。`makrdown` 方法接受 Markdown 樣板的名稱，以及一組用來提供給樣板的可選資料陣列：

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('example@example.com')
                    ->markdown('emails.orders.shipped', [
                        'url' => $this->orderUrl,
                    ]);
    }
<a name="writing-markdown-messages"></a>

### 撰寫 Markdown 訊息

Markdown 的 Markdown 使用 Blade 元件與 Markdown 格式的組合，讓我們能輕鬆地使用 Laravel 內建的 E-Mail UI 元件來建立訊息：

    @component('mail::message')
    # Order Shipped
    
    Your order has been shipped!
    
    @component('mail::button', ['url' => $url])
    View Order
    @endcomponent
    
    Thanks,<br>
    {{ config('app.name') }}
    @endcomponent
> [!TIP]  
> 在撰寫 Markdown 郵件時請不要增加縮排。依據 Markdown 標準，Markdown 解析程式會將縮排的內容轉譯為程式碼區塊。

<a name="button-component"></a>

#### Button 元件

Button 元件轉譯一個置中的按鈕連結。這個元件接受兩個引數，一個是 `url` 網址，另一個則是可選的 `color` 顏色。支援的顏色有 `primary`、`success`、`error`。在訊息中可以加上不限數量的 Button 元件：

    @component('mail::button', ['url' => $url, 'color' => 'success'])
    View Order
    @endcomponent
<a name="panel-component"></a>

#### Panel 元件

Panel 元件將給定的文字區塊轉譯在一個面板中，面板的底色與訊息中其他部分的背景色稍有不同。我們可以使用 Panel 元件來讓給定區塊的文字較為醒目：

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

可以將所有的 Markdown 郵件元件匯出到專案內來自訂這些元件。若要匯出元件，請使用 `vendor:publish` Artisan  指令來^[安裝](Publish) `laravel-mail` 素材標籤：

    php artisan vendor:publish --tag=laravel-mail
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
> [!TIP]  
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
> [!NOTE]  
> [Inline attachments](#inline-attachments) will not be rendered when a mailable is previewed in your browser. To preview these mailables, you should send them to an email testing application such as [MailHog](https://github.com/mailhog/MailHog) or [HELO](https://usehelo.com).

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

Laravel 提供了多種可測試 Mailable 是否包含於其內容的方便方法。這些方法是：`assertSeeInHtml`、`assertDontSeeInHtml`、`assertSeeInText`、`assertDontSeeInText`。

就和預期的一樣，有「HTML」的^ [Assertion](%E5%88%A4%E6%96%B7%E6%8F%90%E7%A4%BA) 判斷 HTML 版本的 Mailable 是否包含給定字串，而「Text」版本的 Assertion 則判斷純文字版本的 Mailable 是否包含給定字串：

    use App\Mail\InvoicePaid;
    use App\Models\User;
    
    public function test_mailable_content()
    {
        $user = User::factory()->create();
    
        $mailable = new InvoicePaid($user);
    
        $mailable->assertSeeInHtml($user->email);
        $mailable->assertSeeInHtml('Invoice Paid');
    
        $mailable->assertSeeInText($user->email);
        $mailable->assertSeeInText('Invoice Paid');
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

#### HELO / Mailtrap / MailHog

或者，也可以使用如 [HELO](https://usehelo.com) 或 [Mailtrap](https://mailtrap.io) 這類服務搭配 `smtp` Driver 來將電子郵件寄送到一個「模擬的」收件夾，並像在真的郵件用戶端一樣檢視這些郵件。這種做法的好處就是可以在 Mailtrap 的訊息檢視工具中實際檢視寄出的郵件。

若使用 [Laravel Sail](/docs/{{version}}/sail),，則可使用 [MailHog](https://github.com/mailhog/MailHog) 來預覽訊息。當 Sail 有在執行時，可在 `http://localhost:8025` 上存取 MailHog 的界面。

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

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Mail\Events\MessageSending' => [
            'App\Listeners\LogSendingMessage',
        ],
        'Illuminate\Mail\Events\MessageSent' => [
            'App\Listeners\LogSentMessage',
        ],
    ];