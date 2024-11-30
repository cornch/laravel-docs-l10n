---
crowdinUrl: 'https://crowdin.com/translate/laravel-docs/29/en-zhtw'
updatedAt: '2024-06-30T08:17:00Z'
contributors: {  }
progress: 39.62
---

# 主控台測試

- [簡介](#introduction)
- [預期成功 / 預期失敗](#success-failure-expectations)
- [預期輸入 / 預期輸出](#input-output-expectations)

<a name="introduction"></a>

## 簡介

在 Laravel 中除了能簡單地測試 HTTP 外，Laravel 還提供了一個簡單的 API 來測試專案的[自訂主控台指令](/docs/{{version}}/artisan)。

<a name="success-failure-expectations"></a>

## 預期成功 / 失敗

若要開始，讓我們先來看看如何針對 Artisan 指令的結束代碼 (Exit Code) 作 Assertion (判斷提示)。為此，我們將使用 `artisan` 方法來在測試中叫用 Artisan 指令。接著，我們會使用 `assertExitCode` 方法來判斷該指令是否以給定的結束代碼完成：

    /**
     * Test a console command.
     *
     * @return void
     */
    public function test_console_command()
    {
        $this->artisan('inspire')->assertExitCode(0);
    }
可以使用 `assertNotExitCode` 方法來判斷該指令是否不以給定結束代碼終止：

    $this->artisan('inspire')->assertNotExitCode(1);
當然，一般來說，所有以狀態碼 `0` 結束的終端機指令通常都代表成功，而非 0 的結束代碼則代表不成功。因此，為了方便起見，我們可以使用 `assertSuccessful` 與 `assertFailed` Assertion 來判斷給定的指令是否以成功結束碼退出：

    $this->artisan('inspire')->assertSuccessful();
    
    $this->artisan('inspire')->assertFailed();
<a name="input-output-expectations"></a>

## 預期的輸入／輸出

Laravel 能讓你輕鬆地通過 `expectsQuestion` 方法來為主控台指令「模擬（Mock）」使用者輸入。此外，還可以通過 `assertExitCode` 與 `expectsOutput` 來指定主控台預期的結束代碼與輸出文字。舉例來說，假設有下列主控台指令：

    Artisan::command('question', function () {
        $name = $this->ask('What is your name?');
    
        $language = $this->choice('Which language do you prefer?', [
            'PHP',
            'Ruby',
            'Python',
        ]);
    
        $this->line('Your name is '.$name.' and you prefer '.$language.'.');
    });
可以通過下列這個使用了 `expectsQuestion`, `expectsOutput`, `doesntExpectOutput`, `expectsOutputToContain`, `doesntExpectOutputToContain` 與 `assertExitCode` 方法的測試來測試該指令：

    /**
     * Test a console command.
     *
     * @return void
     */
    public function test_console_command()
    {
        $this->artisan('question')
             ->expectsQuestion('What is your name?', 'Taylor Otwell')
             ->expectsQuestion('Which language do you prefer?', 'PHP')
             ->expectsOutput('Your name is Taylor Otwell and you prefer PHP.')
             ->doesntExpectOutput('Your name is Taylor Otwell and you prefer Ruby.')
             ->expectsOutputToContain('Taylor Otwell')
             ->doesntExpectOutputToContain('you prefer Ruby')
             ->assertExitCode(0);
    }
<a name="confirmation-expectations"></a>

#### 預期確認

當撰寫預期答案為「yes」或「no」確認的指令時，可以使用 `expectsConfirmation` 方法：

    $this->artisan('module:import')
        ->expectsConfirmation('Do you really wish to run this command?', 'no')
        ->assertExitCode(1);
<a name="table-expectations"></a>

#### 預期表格

若指令使用 Artisan 的 `table` 方法來以表格顯示資訊，則要為整個表格撰寫預期的輸出可能很麻煩。因此可以改用 `expectsTable` 方法來代替。該方法接收表格的標頭作為其第一個引數，以及表格的資料作為其第二引數：

    $this->artisan('users:all')
        ->expectsTable([
            'ID',
            'Email',
        ], [
            [1, 'taylor@example.com'],
            [2, 'abigail@example.com'],
        ]);