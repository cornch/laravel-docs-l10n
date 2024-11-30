---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/127/en-zhtw'
updatedAt: '2024-06-30T08:27:00Z'
contributors: {  }
progress: 47.73
---

# 頻率限制

- [簡介](#introduction)
  - [快取設定](#cache-configuration)
  
- [基礎用法](#basic-usage)
  - [手動增加嘗試次數](#manually-incrementing-attempts)
  - [清除嘗試次數](#clearing-attempts)
  

<a name="introduction"></a>

## 簡介

Laravel includes a simple to use rate limiting abstraction which, in conjunction with your application's [cache](cache), provides an easy way to limit any action during a specified window of time.

> [!NOTE]  
> 若想對連入 HTTP Request 的頻率限制，請參考 [Rate Limiter Middleware 的說明文件](routing#rate-limiting)。

<a name="cache-configuration"></a>

### 快取設定

一般來說，Rate Limiter 會使用專案中 `cache` 設定檔 `default` 索引鍵上所定義的預設快取。不過，我們可以在專案的 `cache` 設定檔中定義 `limiter` 索引鍵來指定 Rate Limiter 要使用哪個快取 Driver：

    'default' => env('CACHE_STORE', 'database'),
    
    'limiter' => 'redis',
<a name="basic-usage"></a>

## 基礎用法

可通過 `Illuminate\Support\Facades\RateLimiter` Facade 來使用 Rate Limiter。Rate Limiter 所提供的最簡單的方法是 `attempt` 方法，該方法會對給定閉包以給定秒數來做頻率限制。

若該回呼已無法再嘗試，則 `attempt` 方法會回傳 `false`。若還能繼續嘗試，則 `attempt` 會回傳該回呼的執行結果或 `true`。`attempt` 方法的第一個引數為 Rate Limiter 的「索引鍵」，索引鍵可以是任意字串，用來表示要被頻率限制的動作：

    use Illuminate\Support\Facades\RateLimiter;
    
    $executed = RateLimiter::attempt(
        'send-message:'.$user->id,
        $perMinute = 5,
        function() {
            // Send message...
        }
    );
    
    if (! $executed) {
      return 'Too many messages sent!';
    }
如有需要，可以為 `attempt` 方法提供第四個「Decay Rate」引數，或是直到頻率限制被重設前的秒數。例如，我們可以將上面的範例改為每 2 分鐘允許 5 次嘗試：

    $executed = RateLimiter::attempt(
        'send-message:'.$user->id,
        $perTwoMinutes = 5,
        function() {
            // Send message...
        },
        $decayRate = 120,
    );
<a name="manually-incrementing-attempts"></a>

### 手動增加嘗試次數

若想手動使用 Rate Limiter，則還有其他許多能使用的方法。舉例來說，我們可以叫用 `tooManyAttempts` 方法來判斷給定的 Rate Limiter 索引鍵是否已遇到其每分鐘所允許的最大嘗試次數：

    use Illuminate\Support\Facades\RateLimiter;
    
    if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 5)) {
        return 'Too many attempts!';
    }
    
    RateLimiter::increment('send-message:'.$user->id);
    
    // Send message...
Alternatively, you may use the `remaining` method to retrieve the number of attempts remaining for a given key. If a given key has retries remaining, you may invoke the `increment` method to increment the number of total attempts:

    use Illuminate\Support\Facades\RateLimiter;
    
    if (RateLimiter::remaining('send-message:'.$user->id, $perMinute = 5)) {
        RateLimiter::increment('send-message:'.$user->id);
    
        // Send message...
    }
If you would like to increment the value for a given rate limiter key by more than one, you may provide the desired amount to the `increment` method:

    RateLimiter::increment('send-message:'.$user->id, amount: 5);
<a name="determining-limiter-availability"></a>

#### 判斷 Limiter 是否可用

若某個索引鍵已無可用的嘗試次數，則 `availableIn` 方法會回傳距離下次可獲得嘗試次數的剩餘秒數：

    use Illuminate\Support\Facades\RateLimiter;
    
    if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 5)) {
        $seconds = RateLimiter::availableIn('send-message:'.$user->id);
    
        return 'You may try again in '.$seconds.' seconds.';
    }
    
    RateLimiter::increment('send-message:'.$user->id);
    
    // Send message...
<a name="clearing-attempts"></a>

### 清除嘗試次數

可使用 `clear` 方法來重設給定 Rate Limiter 索引鍵的嘗試次數。舉例來說，我們可以在收件人已閱讀某個訊息後重設嘗試次數：

    use App\Models\Message;
    use Illuminate\Support\Facades\RateLimiter;
    
    /**
     * Mark the message as read.
     */
    public function read(Message $message): Message
    {
        $message->markAsRead();
    
        RateLimiter::clear('send-message:'.$message->user_id);
    
        return $message;
    }