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

> [!TIP]  
> 若想對連入 HTTP Request 的頻率限制，請參考 [Rate Limiter Middleware 的說明文件](routing#rate-limiting)。

<a name="cache-configuration"></a>

### 快取設定

一般來說，Rate Limiter 會使用專案中 `cache` 設定檔 `default` 索引鍵上所定義的預設快取。不過，我們可以在專案的 `cache` 設定檔中定義 `limiter` 索引鍵來指定 Rate Limiter 要使用哪個快取 Driver：

    'default' => 'memcached',
    
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
<a name="manually-incrementing-attempts"></a>

### 手動增加嘗試次數

若想手動使用 Rate Limiter，則還有其他許多能使用的方法。舉例來說，我們可以叫用 `tooManyAttempts` 方法來判斷給定的 Rate Limiter 索引鍵是否已遇到其每分鐘所允許的最大嘗試次數：

    use Illuminate\Support\Facades\RateLimiter;
    
    if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 5)) {
        return 'Too many attempts!';
    }
或者，也可以使用 `remaining` 方法來取得給定索引鍵剩下的嘗試次數。若給定的索引鍵還有可嘗試的次數，則可叫用 `hit` 方法來增加總嘗試次數：

    use Illuminate\Support\Facades\RateLimiter;
    
    if (RateLimiter::remaining('send-message:'.$user->id, $perMinute = 5)) {
        RateLimiter::hit('send-message:'.$user->id);
    
        // Send message...
    }
<a name="determining-limiter-availability"></a>

#### 判斷 Limiter 是否可用

若某個索引鍵已無可用的嘗試次數，則 `availableIn` 方法會回傳距離下次可獲得嘗試次數的剩餘秒數：

    use Illuminate\Support\Facades\RateLimiter;
    
    if (RateLimiter::tooManyAttempts('send-message:'.$user->id, $perMinute = 5)) {
        $seconds = RateLimiter::availableIn('send-message:'.$user->id);
    
        return 'You may try again in '.$seconds.' seconds.';
    }
<a name="clearing-attempts"></a>

### 清除嘗試次數

可使用 `clear` 方法來重設給定 Rate Limiter 索引鍵的嘗試次數。舉例來說，我們可以在收件人已閱讀某個訊息後重設嘗試次數：

    use App\Models\Message;
    use Illuminate\Support\Facades\RateLimiter;
    
    /**
     * Mark the message as read.
     *
     * @param  \App\Models\Message  $message
     * @return \App\Models\Message
     */
    public function read(Message $message)
    {
        $message->markAsRead();
    
        RateLimiter::clear('send-message:'.$message->user_id);
    
        return $message;
    }