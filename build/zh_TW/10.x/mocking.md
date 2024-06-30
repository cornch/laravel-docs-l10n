---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/107/en-zhtw
progress: 100
updatedAt: '2024-06-30T08:15:00Z'
---

# Mock

- [簡介](#introduction)
- [Mock 物件](#mocking-objects)
- [Mock Facade](#mocking-facades)
   - [Facade 的 Spy](#facade-spies)
- [處理時間](#interacting-with-time)

<a name="introduction"></a>

## 簡介

在測試 Laravel 專案時，我們有時候會需要「^[Mock](模擬)」某部分的程式，好讓執行測試時不要真的執行這一部分程式。舉例來說，在測試會分派 Event 的 Controller 時，我們可能會想 Mock 該 Event 的 Listener，讓這些 Event Listener 在測試階段不要真的被執行。這樣一來，我們就可以只測試 Controller 的 HTTP Response，而不需擔心 Event Listener 的執行，因為這些 Event Listener 可以在其自己的測試例中測試。

Laravel 提供了各種開箱即用的實用方法，可用於 Mock Event、Job、與其他 Facade。這些輔助函式主要提供一個 Mockery 之上的方便層，讓我們不需手動進行複雜的 Mockery 方法呼叫。

<a name="mocking-objects"></a>

## Mock 物件

若要 Mock 一些會被 Laravel [Service Container](/docs/{{version}}/container) 插入到程式中的物件，只需要使用 `instance` 繫結來將 Mock 後的實體繫結到 Container 中。這樣一來，Container 就會使用 Mock 後的物件實體，而不會再重新建立一個物件：

    use App\Service;
    use Mockery;
    use Mockery\MockInterface;
    
    public function test_something_can_be_mocked(): void
    {
        $this->instance(
            Service::class,
            Mockery::mock(Service::class, function (MockInterface $mock) {
                $mock->shouldReceive('process')->once();
            })
        );
    }

為了讓這個過程更方便，我們可以使用 Laravel 基礎測試例 Class 中的 `mock` 方法。舉例來說，下面這個範例與上一個範例是相等的：

    use App\Service;
    use Mockery\MockInterface;
    
    $mock = $this->mock(Service::class, function (MockInterface $mock) {
        $mock->shouldReceive('process')->once();
    });

若只需要 Mock 某個物件的一部分方法，可使用 `partialMock` 方法。若呼叫了未被 Mock 的方法，則這些方法會正常執行：

    use App\Service;
    use Mockery\MockInterface;
    
    $mock = $this->partialMock(Service::class, function (MockInterface $mock) {
        $mock->shouldReceive('process')->once();
    });

類似的，若我們想 [Spy](http://docs.mockery.io/en/latest/reference/spies.html) 某個物件，Laravel 的基礎測試 Class 中也提供了一個 `spy` 方法來作為 `Mockery::spy` 方法的方便包裝。Spy 與 Mock 類似；不過，Spy 會記錄所有 Spy 與正在測試的程式碼間的互動，能讓我們在程式碼執行後進行 Assertion：

    use App\Service;
    
    $spy = $this->spy(Service::class);
    
    // ...
    
    $spy->shouldHaveReceived('process');

<a name="mocking-facades"></a>

## Mock Facade

與傳統的靜態方法呼叫不同，[Facade] (包含即時 Facade) 是可以被 Mock 的。這樣一來，我們還是能使用傳統的靜態方法呼叫，同時又不會失去傳統相依性插入所帶來的可測試性。在測試時，我們通常會想 Mock 在 Controller 中的某個 Laravel Facade 呼叫。舉例來說，來看看下列 Controller 動作：

    <?php
    
    namespace App\Http\Controllers;
    
    use Illuminate\Support\Facades\Cache;
    
    class UserController extends Controller
    {
        /**
         * Retrieve a list of all users of the application.
         */
        public function index(): array
        {
            $value = Cache::get('key');
    
            return [
                // ...
            ];
        }
    }

我們可以使用 `shouldReceive` 方法來 Mock `Cache` Facade 的呼叫。該方法會回傳 [Mockery](https://github.com/padraic/mockery) 的 Mock 實體。由於Facade 會實際上會由 Laravel 的 [Service Container](/docs/{{version}}/container) 來解析與管理，因此比起傳統的靜態類別，Facade 有更好的可測試性。舉例來說，我們來 Mock `Cache` Facade 的 `get` 方法呼叫：

    <?php
    
    namespace Tests\Feature;
    
    use Illuminate\Support\Facades\Cache;
    use Tests\TestCase;
    
    class UserControllerTest extends TestCase
    {
        public function test_get_index(): void
        {
            Cache::shouldReceive('get')
                        ->once()
                        ->with('key')
                        ->andReturn('value');
    
            $response = $this->get('/users');
    
            // ...
        }
    }

> **Warning** 請不要 Mock `Request` Facade。在執行測試時，請將要測試的輸入傳給如 `get` 或 `post` 等的 [HTTP 測試方法](/docs/{{version}}/http-tests)。類似地，請不要 Mock `Config` Facade，請在測試中執行 `Config::set` 方法。

<a name="facade-spies"></a>

### Facade 的 Spy

若想 [Spy](http://docs.mockery.io/en/latest/reference/spies.html) 某個 Facade，則可在對應的 Facade 上呼叫 `spy` 方法。Spy 與 Mock 類似；不過，Spy 會記錄所有 Spy 與正在測試的程式碼間的互動，能讓我們在程式碼執行後進行 Assertion：

    use Illuminate\Support\Facades\Cache;
    
    public function test_values_are_be_stored_in_cache(): void
    {
        Cache::spy();
    
        $response = $this->get('/');
    
        $response->assertStatus(200);
    
        Cache::shouldHaveReceived('put')->once()->with('name', 'Taylor', 10);
    }

<a name="interacting-with-time"></a>

## 處理時間

在測試的時候，我們有時候會想要更改如 `now` 或 `Illuminate\Support\Carbon::now()` 等輔助函式所回傳的時間。幸好，Laravel 的基礎功能測試 (Feature Test) Class 中，有包含一個可以更改目前時間的輔助函式：

    use Illuminate\Support\Carbon;
    
    public function test_time_can_be_manipulated(): void
    {
        // 時間旅行到未來...
        $this->travel(5)->milliseconds();
        $this->travel(5)->seconds();
        $this->travel(5)->minutes();
        $this->travel(5)->hours();
        $this->travel(5)->days();
        $this->travel(5)->weeks();
        $this->travel(5)->years();
    
        // 時間旅行到過去...
        $this->travel(-5)->hours();
    
        // 時間旅行到一個特定的時間...
        $this->travelTo(now()->subHours(6));
    
        // 回到目前時間...
        $this->travelBack();
    }

也可以提供一個閉包給各個時間旅行方法。呼叫該閉包時，會傳入所凍結的特定時間。執行該閉包後，時間就會恢復正常：

    $this->travel(5)->days(function () {
        // 時間旅行到未來的五天後，並測試某些功能...
    });
    
    $this->travelTo(now()->subDays(10), function () {
        // 在特定的時間測試某些功能...
    });

`freezeTime` 方法可用來凍結目前的時間。類似地，`freezeSecond` 方法會凍結目前時間，並回到目前秒數的開端：

    use Illuminate\Support\Carbon;
    
    // 凍結時間，並在執行 Closure 後恢復正常時間...
    $this->freezeTime(function (Carbon $time) {
        // ...
    });
    
    // 將時間凍結在目前的秒數，並在執行閉包後恢復正常時間...
    $this->freezeSecond(function (Carbon $time) {
        // ...
    })

就像預期的一樣，上方所討論的所有方法主要都適合用來測試與時間相關的程式行為，例如在討論區中鎖定非活躍的貼文：

    use App\Models\Thread;
    
    public function test_forum_threads_lock_after_one_week_of_inactivity()
    {
        $thread = Thread::factory()->create();
        
        $this->travel(1)->week();
        
        $this->assertTrue($thread->isLockedByInactivity());
    }
