---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/107/en-zhtw'
updatedAt: '2024-06-30T08:15:00Z'
contributors: {  }
progress: 53.11
---

# Mock

- [簡介](#introduction)
- [Mock 物件](#mocking-objects)
- [Mock Facade](#mocking-facades)
  - [Facade 的 Spy](#facade-spies)
  
- [Bus Fake](#bus-fake)
  - [Job Chain](#bus-job-chains)
  - [批次 Job](#job-batches)
  
- [Event Fake](#event-fake)
  - [限定範圍的 Event Fake](#scoped-event-fakes)
  
- [HTTP Fake](#http-fake)
- [Mail Fake](#mail-fake)
- [Notification Fake](#notification-fake)
- [Queue Fake](#queue-fake)
  - [Job Chain](#job-chains)
  
- [Storage Fake](#storage-fake)
- [處理時間](#interacting-with-time)

<a name="introduction"></a>

## 簡介

在測試 Laravel 專案時，我們有時候會需要「^[Mock](%E6%A8%A1%E6%93%AC)」某部分的程式，好讓執行測試時不要真的執行這一部分程式。舉例來說，在測試會分派 Event 的 Controller 時，我們可能會想 Mock 該 Event 的 Listener，讓這些 Event Listener 在測試階段不要真的被執行。這樣一來，我們就可以只測試 Controller 的 HTTP Response，而不需擔心 Event Listener 的執行，因為這些 Event Listener 可以在其自己的測試例中測試。

Laravel 提供了各種開箱即用的實用方法，可用於 Mock Event、Job、與其他 Facade。這些輔助函式主要提供一個 Mockery 之上的方便層，讓我們不需手動進行複雜的 Mockery 方法呼叫。

<a name="mocking-objects"></a>

## Mock 物件

若要 Mock 一些會被  Laravel [Service Container](/docs/{{version}}/container) 插入到程式中的物件，只需要使用 `instance` 繫結來將 Mock 後的實體繫結到 Container 中。這樣一來，Container 就會使用 Mock 後的物件實體，而不會再重新建立一個物件：

    use App\Service;
    use Mockery;
    use Mockery\MockInterface;
    
    public function test_something_can_be_mocked()
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
         *
         * @return \Illuminate\Http\Response
         */
        public function index()
        {
            $value = Cache::get('key');
    
            //
        }
    }
我們可以使用 `shouldReceive` 方法來 Mock `Cache` Facade 的呼叫。該方法會回傳 [Mockery](https://github.com/padraic/mockery) 的 Mock 實體。由於Facade 會實際上會由 Laravel 的 [Service Container](/docs/{{version}}/container) 來解析與管理，因此比起傳統的靜態類別，Facade 有更好的可測試性。舉例來說，我們來 Mock `Cache` Facade 的 `get` 方法呼叫：

    <?php
    
    namespace Tests\Feature;
    
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Cache;
    use Tests\TestCase;
    
    class UserControllerTest extends TestCase
    {
        public function testGetIndex()
        {
            Cache::shouldReceive('get')
                        ->once()
                        ->with('key')
                        ->andReturn('value');
    
            $response = $this->get('/users');
    
            // ...
        }
    }
> [!NOTE]  
> 請不要 Mock `Request` Facade。在執行測試時，請將要測試的輸入傳給如 `get` 或 `post` 等的 [HTTP 測試方法](/docs/{{version}}/http-tests)。類似地，請不要 Mock `Config` Facade，請在測試中執行 `Config::set` 方法。

<a name="facade-spies"></a>

### Facade 的 Spy

若想 [Spy](http://docs.mockery.io/en/latest/reference/spies.html) 某個 Facade，則可在對應的 Facade 上呼叫 `spy` 方法。Spy 與 Mock 類似；不過，Spy 會記錄所有 Spy 與正在測試的程式碼間的互動，能讓我們在程式碼執行後進行 Assertion：

    use Illuminate\Support\Facades\Cache;
    
    public function test_values_are_be_stored_in_cache()
    {
        Cache::spy();
    
        $response = $this->get('/');
    
        $response->assertStatus(200);
    
        Cache::shouldHaveReceived('put')->once()->with('name', 'Taylor', 10);
    }
<a name="bus-fake"></a>

## Bus Fake

在測試會分派 Job 的程式時，一般來說我們會想判斷給定的 Job 是否有被分派，而不是真的將該 Job 放入 Queue 裡執行。這是因為，Job 的執行一般來說可以在其獨立的測試 Class 中測試。

我們可以使用 `Bus` Facade 的 `fake` 方法來避免 Job 被分派到 Queue 中。接著，在測試中執行程式碼後，我們就可以使用 `assertDispatched` 與 `assertNotDispatched` 方法來檢查程式嘗試分派了什麼 Job：

    <?php
    
    namespace Tests\Feature;
    
    use App\Jobs\ShipOrder;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Bus;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_orders_can_be_shipped()
        {
            Bus::fake();
    
            // Perform order shipping...
    
            // Assert that a job was dispatched...
            Bus::assertDispatched(ShipOrder::class);
    
            // Assert a job was not dispatched...
            Bus::assertNotDispatched(AnotherJob::class);
    
            // Assert that a job was dispatched synchronously...
            Bus::assertDispatchedSync(AnotherJob::class);
    
            // Assert that a job was not dipatched synchronously...
            Bus::assertNotDispatchedSync(AnotherJob::class);
    
            // Assert that a job was dispatched after the response was sent...
            Bus::assertDispatchedAfterResponse(AnotherJob::class);
    
            // Assert a job was not dispatched after response was sent...
            Bus::assertNotDispatchedAfterResponse(AnotherJob::class);
    
            // Assert no jobs were dispatched...
            Bus::assertNothingDispatched();
        }
    }
可以傳入一個閉包給這些可用的方法，來判斷某個 Job 是否通過給定的「真值測試 (Truth Test)」。若分派的 Job 中至少有一個 Job 有通過給真值測試，則 Assertion 會被視為成功。舉例來說，我們可以判斷是否有對某個特定訂單分派 Job：

    Bus::assertDispatched(function (ShipOrder $job) use ($order) {
        return $job->order->id === $order->id;
    });
<a name="bus-job-chains"></a>

### Job Chain

`Bus` Facade 的 `assertChained` 方法可用來判斷是否有分派某個[串聯的 Job](/docs/{{version}}/queues#job-chaining)。`assertChained` 方法接受一組串聯 Job 的陣列作為其第一個引數：

    use App\Jobs\RecordShipment;
    use App\Jobs\ShipOrder;
    use App\Jobs\UpdateInventory;
    use Illuminate\Support\Facades\Bus;
    
    Bus::assertChained([
        ShipOrder::class,
        RecordShipment::class,
        UpdateInventory::class
    ]);
就像上述範例中可看到的一樣，串聯 Job 的陣列就是一組包含 Job 類別名稱的陣列。不過，也可以提供一組實際 Job 實體的陣列。當提供的陣列為 Job 實體的陣列時，Laravel 會確保程式所分派的串聯 Job 都具是相同的類別，且擁有相同的屬性值：

    Bus::assertChained([
        new ShipOrder,
        new RecordShipment,
        new UpdateInventory,
    ]);
<a name="job-batches"></a>

### 批次 Job

`Bus` Facade 的 `assertBatched` 方法可用來判斷是否有分派[Job 批次]。提供給 `assertBatched` 方法的閉包會收到 `Illuminate\Bus\PendingBatch` 的實體，該實體可用來檢查批次中的 Job：

    use Illuminate\Bus\PendingBatch;
    use Illuminate\Support\Facades\Bus;
    
    Bus::assertBatched(function (PendingBatch $batch) {
        return $batch->name == 'import-csv' &&
               $batch->jobs->count() === 10;
    });
<a name="event-fake"></a>

## Event Fake

在測試會分派 Event 的程式時，我們可能會希望 Laravel 不要真的去執行 Event 的 Listener。使用 `Event` Facade 的 `fake` 方法，就可避免執行真正的 Listener，在測試中執行程式碼，然後使用 `assertDispatched`、`assertNotDispatched`、`assertNothingDispatched` 等方法來判斷程式分派了哪些 Event：

    <?php
    
    namespace Tests\Feature;
    
    use App\Events\OrderFailedToShip;
    use App\Events\OrderShipped;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Event;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * Test order shipping.
         */
        public function test_orders_can_be_shipped()
        {
            Event::fake();
    
            // Perform order shipping...
    
            // Assert that an event was dispatched...
            Event::assertDispatched(OrderShipped::class);
    
            // Assert an event was dispatched twice...
            Event::assertDispatched(OrderShipped::class, 2);
    
            // Assert an event was not dispatched...
            Event::assertNotDispatched(OrderFailedToShip::class);
    
            // Assert that no events were dispatched...
            Event::assertNothingDispatched();
        }
    }
可以傳入一個閉包給 `assertDispatched` 或 `assertNotDispatched` 方法，來判斷某個 Event 是否通過給定的「真值測試 (Truth Test)」。若分派的 Event 中至少有一個 Event 通過給定的真值測試，則該 Assertion 會被視為成功：

    Event::assertDispatched(function (OrderShipped $event) use ($order) {
        return $event->order->id === $order->id;
    });
若只想判斷某個 Event Listener 是否有在監聽給定的 Event，可使用 `assertListening` 方法：

    Event::assertListening(
        OrderShipped::class,
        SendShipmentNotification::class
    );
> [!NOTE]  
> 呼叫 `Event::fake()` 後，就不會執行 Event Listener。因此，若有測試使用的 Model Factory 仰賴於 Event，如在 Model 的 `creating` Event 上建立 UUID 等，請在使用完 Factory **之後** 再呼叫 `Event::fake()`。

<a name="faking-a-subset-of-events"></a>

#### Fake 一小部分的 Event

若只想為一部分 Event 來 Fake Event Listener，則可將這些 Event 傳入`fake` 或 `fakeFor` 方法：

    /**
     * Test order process.
     */
    public function test_orders_can_be_processed()
    {
        Event::fake([
            OrderCreated::class,
        ]);
    
        $order = Order::factory()->create();
    
        Event::assertDispatched(OrderCreated::class);
    
        // Other events are dispatched as normal...
        $order->update([...]);
    }
<a name="scoped-event-fakes"></a>

### 限定範圍的 Event Fake

若只想未一部分的測試 Fake Event Listener，則可使用 `fakeFor` 方法：

    <?php
    
    namespace Tests\Feature;
    
    use App\Events\OrderCreated;
    use App\Models\Order;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Support\Facades\Event;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        /**
         * Test order process.
         */
        public function test_orders_can_be_processed()
        {
            $order = Event::fakeFor(function () {
                $order = Order::factory()->create();
    
                Event::assertDispatched(OrderCreated::class);
    
                return $order;
            });
    
            // Events are dispatched as normal and observers will run ...
            $order->update([...]);
        }
    }
<a name="http-fake"></a>

## HTTP Fake

使用 `Http` Facade 的 `fake` 方法，我們就能讓 HTTP 用戶端在建立 Request 時回傳 ^[Stubbed](%E9%A0%90%E5%85%88%E5%A1%AB%E5%85%85%E5%A5%BD%E7%9A%84)、假的 Response。更多有關模擬外連 HTTP Request 的資訊，請參考 [HTTP 用戶端的測試文件](/docs/{{version}}/http-client#testing)。

<a name="mail-fake"></a>

## Mail Fake

可以使用 `Mail` Facade 的 `fake` 方法來避免寄出 Mail。一般來說，寄送 Mail 與實際要測試的程式碼是不相關的。通常，只要判斷 Laravel 是否有接到指示要寄出給定 Mailable 就夠了。

呼叫 `Mail` Facade 的 `fake` 方法後，就可以判斷是否有被要求要將該 [Mailable](/docs/{{version}}/mail) 寄出給使用者，甚至還能判斷 Mailable 收到的資料：

    <?php
    
    namespace Tests\Feature;
    
    use App\Mail\OrderShipped;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Mail;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_orders_can_be_shipped()
        {
            Mail::fake();
    
            // Perform order shipping...
    
            // Assert that no mailables were sent...
            Mail::assertNothingSent();
    
            // Assert that a mailable was sent...
            Mail::assertSent(OrderShipped::class);
    
            // Assert a mailable was sent twice...
            Mail::assertSent(OrderShipped::class, 2);
    
            // Assert a mailable was not sent...
            Mail::assertNotSent(AnotherMailable::class);
        }
    }
若將 Mailable 放在佇列中以在背景寄送，請使用 `assertQueued` 方法，而不是 `assertSent` 方法：

    Mail::assertQueued(OrderShipped::class);
    
    Mail::assertNotQueued(OrderShipped::class);
    
    Mail::assertNothingQueued();
可以傳入一個閉包給 `assertSent`、`assertNotSent`、`assertQueued`、`assertNotQueued` 方法來判斷 Mailable 是否通過給定的「真值測試 (Truth Test)」。若至少有一個寄出的 Mailable 通過給定的真值測試，則該 Assertion 會被視為成功：

    Mail::assertSent(function (OrderShipped $mail) use ($order) {
        return $mail->order->id === $order->id;
    });
呼叫 `Mail` Facade 的 Assertion 方法時，所提供的閉包內收到的 Mailable 實體上有一些實用的方法，可用來檢查 Mailable 的收件者：

    Mail::assertSent(OrderShipped::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email) &&
               $mail->hasCc('...') &&
               $mail->hasBcc('...');
    });
讀者可能已經注意到，總共有兩個方法可用來檢查郵件是否未被送出：`assertNotSent`、`assertNotQueued`。有時候，我們可能會希望判斷沒有任何郵件被寄出，**而且** 也沒有任何郵件被放入佇列。若要判斷是否沒有郵件被寄出或放入佇列，可使用 `assertNothingOutgoing` 與 `assertNotOutgoing` 方法：

    Mail::assertNothingOutgoing();
    
    Mail::assertNotOutgoing(function (OrderShipped $mail) use ($order) {
        return $mail->order->id === $order->id;
    });
<a name="notification-fake"></a>

## Notification Fake

可以使用 `Notification` Facade 的 `fake` 方法來避免送出 Notification。一般來說，送出 Notification 與實際要測試的程式碼是不相關的。通常，只要判斷 Laravel 是否有接到指示要送出給定的 Notification 就夠了。

呼叫 `Notification` Facade 的 `fake` 方法後，就可以判斷是否有被要求要將該 [Notification](/docs/{{version}}/notification) 送出給使用者，甚至還能判斷 Notification 收到的資料：

    <?php
    
    namespace Tests\Feature;
    
    use App\Notifications\OrderShipped;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Notification;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_orders_can_be_shipped()
        {
            Notification::fake();
    
            // Perform order shipping...
    
            // Assert that no notifications were sent...
            Notification::assertNothingSent();
    
            // Assert a notification was sent to the given users...
            Notification::assertSentTo(
                [$user], OrderShipped::class
            );
    
            // Assert a notification was not sent...
            Notification::assertNotSentTo(
                [$user], AnotherNotification::class
            );
        }
    }
可以傳入一個閉包給 `assertSentTo` 或 `assertNotSentTo` 方法，來判斷某個 Notification 是否通過給定的「真值測試 (Truth Test)」。若送出的 Notification 中至少有一個 Notification 通過給定的真值測試，則該 Assertion 會被視為成功：

    Notification::assertSentTo(
        $user,
        function (OrderShipped $notification, $channels) use ($order) {
            return $notification->order->id === $order->id;
        }
    );
<a name="on-demand-notifications"></a>

#### 隨需通知

若要測試的程式碼有傳送[隨需通知](/docs/{{version}}/notifications#on-demand-notifications)，則我們需要判斷該 Notification 是否有傳送給 `Illuminate\Notifications\AnonymousNotifiable` 實體：

    use Illuminate\Notifications\AnonymousNotifiable;
    
    Notification::assertSentTo(
        new AnonymousNotifiable, OrderShipped::class
    );
若在 Notification Assertion 方法的第三個引數上傳入閉包，就能判斷隨需通知是否被送給正確的「^[Route](%E8%B7%AF%E7%94%B1)」位址：

    Notification::assertSentTo(
        new AnonymousNotifiable,
        OrderShipped::class,
        function ($notification, $channels, $notifiable) use ($user) {
            return $notifiable->routes['mail'] === $user->email;
        }
    );
<a name="queue-fake"></a>

## Queue Fake

可以使用 `Queue` Facade 的 `fake` 方法來避免放入佇列的 Job 被推入到佇列中。通常來說，這麼做就可以判斷 Laravel 有被指示要將給定的 Job 推入到佇列中了。因為，我們可以在其獨立的測試 Class 中測試放入佇列的 Job。

呼叫 `Queue` Facade 的 `fake` 方法後，就可以判斷程式是否有嘗試將 Job 推入到佇列中：

    <?php
    
    namespace Tests\Feature;
    
    use App\Jobs\AnotherJob;
    use App\Jobs\FinalJob;
    use App\Jobs\ShipOrder;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Support\Facades\Queue;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_orders_can_be_shipped()
        {
            Queue::fake();
    
            // Perform order shipping...
    
            // Assert that no jobs were pushed...
            Queue::assertNothingPushed();
    
            // Assert a job was pushed to a given queue...
            Queue::assertPushedOn('queue-name', ShipOrder::class);
    
            // Assert a job was pushed twice...
            Queue::assertPushed(ShipOrder::class, 2);
    
            // Assert a job was not pushed...
            Queue::assertNotPushed(AnotherJob::class);
        }
    }
可以傳入一個閉包給 `assertPushed` 或 `assertNotPushed` 方法，來判斷某個 Job 是否通過給定的「真值測試 (Truth Test)」。若被推入的 Job 中至少有一個 Job 通過給定的真值測試，則該 Assertion 會被視為成功：

    Queue::assertPushed(function (ShipOrder $job) use ($order) {
        return $job->order->id === $order->id;
    });
<a name="job-chains"></a>

### Job Chain

`Queue` Facade 的 `assertPushedWithChain` 與 `assertPushedWithoutChain` 方法可用來檢查串聯 Job 或是被推入佇列的 Job。`assertPushedWithChain` 方法的第一個引數未主要的 Job，而第二個引數則為一組包含串聯 Job 的陣列：

    use App\Jobs\RecordShipment;
    use App\Jobs\ShipOrder;
    use App\Jobs\UpdateInventory;
    use Illuminate\Support\Facades\Queue;
    
    Queue::assertPushedWithChain(ShipOrder::class, [
        RecordShipment::class,
        UpdateInventory::class
    ]);
就像上述範例中可看到的一樣，串聯 Job 的陣列就是一組包含 Job 類別名稱的陣列。不過，也可以提供一組實際 Job 實體的陣列。當提供的陣列為 Job 實體的陣列時，Laravel 會確保程式所分派的串聯 Job 都具是相同的類別，且擁有相同的屬性值：

    Queue::assertPushedWithChain(ShipOrder::class, [
        new RecordShipment,
        new UpdateInventory,
    ]);
可以使用 `assertPushedWithoutChain` 方法來判斷 Job 被推入 Queue 但未包含串聯 Job：

    Queue::assertPushedWithoutChain(ShipOrder::class);
<a name="storage-fake"></a>

## Storage Fake

使用 `Storage` Facade 的 `fake` 方法就可輕鬆地產生 Fake Disk。Fake Disk 可以與 `Illuminate\Http\UploadedFile` 類別的檔案產生工具來搭配使用，讓我們能非常輕鬆地測試檔案上傳。舉例來說：

    <?php
    
    namespace Tests\Feature;
    
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Storage;
    use Tests\TestCase;
    
    class ExampleTest extends TestCase
    {
        public function test_albums_can_be_uploaded()
        {
            Storage::fake('photos');
    
            $response = $this->json('POST', '/photos', [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg')
            ]);
    
            // Assert one or more files were stored...
            Storage::disk('photos')->assertExists('photo1.jpg');
            Storage::disk('photos')->assertExists(['photo1.jpg', 'photo2.jpg']);
    
            // Assert one or more files were not stored...
            Storage::disk('photos')->assertMissing('missing.jpg');
            Storage::disk('photos')->assertMissing(['missing.jpg', 'non-existing.jpg']);
        }
    }
更多有關測試檔案上傳的資訊，請參考 [HTTP 測試說明文件中的檔案上傳部分](/docs/{{version}}/http-tests#testing-file-uploads)。

> [!TIP]  
> By default, the `fake` method will delete all files in its temporary directory. If you would like to keep these files, you may use the "persistentFake" method instead.

<a name="interacting-with-time"></a>

## 處理時間

在測試的時候，我們有時候會想要更改如 `now` 或 `Illuminate\Support\Carbon::now()` 等輔助函式所回傳的時間。幸好，Laravel 的基礎功能測試 (Feature Test) Class 中，有包含一個可以更改目前時間的輔助函式：

    public function testTimeCanBeManipulated()
    {
        // Travel into the future...
        $this->travel(5)->milliseconds();
        $this->travel(5)->seconds();
        $this->travel(5)->minutes();
        $this->travel(5)->hours();
        $this->travel(5)->days();
        $this->travel(5)->weeks();
        $this->travel(5)->years();
    
        // Travel into the past...
        $this->travel(-5)->hours();
    
        // Travel to an explicit time...
        $this->travelTo(now()->subHours(6));
    
        // Return back to the present time...
        $this->travelBack();
    }