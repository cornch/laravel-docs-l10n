---
contributors:
  14684796:
    avatarUrl: https://crowdin-static.downloads.crowdin.com/avatar/14684796/medium/60f7dc21ec0bf9cfcb61983640bb4809_default.png
    name: cornch
crowdinUrl: https://crowdin.com/translate/laravel-docs/131/en-zhtw
progress: 100
updatedAt: '2023-02-11T10:28:00Z'
---

# HTTP 重新導向

- [建立 Redirect](#creating-redirects)
- [重新導向至命名 Route](#redirecting-named-routes)
- [重新導向至 Controller 動作](#redirecting-controller-actions)
- [重新導向並帶上快閃存入的 Session 資料](#redirecting-with-flashed-session-data)

<a name="creating-redirects"></a>

## 建立 Redirect

^[Redirect](重新導向) Response 是 `Illuminate\Http\RedirectResponse` 類別的實體，Redirect Response 中包含了用來將使用者重新導向到另一個網址所需的一些 ^[Header](標頭)。要產生 `RedirectResponse` 實體有幾個方法。最簡單的方法是使用全域的 `redirect` 輔助函式：

    Route::get('/dashboard', function () {
        return redirect('/home/dashboard');
    });

有時候 (如：使用者送出了無效的表單時)，我們可能會想把使用者重新導向到使用者瀏覽的前一個位置。為此，我們可以使用全域的 `back` 輔助函式。由於這個功能使用了 [Session](/docs/{{version}}/session)，因此請確保呼叫 `back` 函式的 Route 有使用 `web` Middleware 群組：

    Route::post('/user/profile', function () {
        // 驗證 Request...
    
        return back()->withInput();
    });

<a name="redirecting-named-routes"></a>

## 重新導向到命名 Route

呼叫 `redirect` 輔助函式時若沒有帶上任何參數，則會回傳 `Illuminate\Routing\Redirector` 實體，這樣我們就可以呼叫 `Redirect` 實體上的所有方法。舉例來說，若要為某個命名 Route 產生 `RedirectResponse`，可以使用 `route` 方法：

    return redirect()->route('login');

若 Route 有參數，則可將這些 Route 參數作為第二個引數傳給 `route` 方法：

    // 用於下列 URI 的 Route：/profile/{id}
    
    return redirect()->route('profile', ['id' => 1]);

Laravel 也提供了一個方便的全域 `to_route` 函式：

    return to_route('profile', ['id' => 1]);

<a name="populating-parameters-via-eloquent-models"></a>

#### 使用 Eloquent Model 來填充參數

若要重新導向的 Route 中有個可從 Eloquent Model 中填充的「ID」參數，則可傳入 Model。會自動取出 ID：

    // 用於下列 URI 的 Route：/profile/{id}
    
    return redirect()->route('profile', [$user]);

若想自定放入 Route 參數的值，可在 Eloquent Model 上複寫 `getRouteKey` 方法：

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey(): mixed
    {
        return $this->slug;
    }

<a name="redirecting-controller-actions"></a>

## 重新導向到 Controller 動作

也可以產生一個前往 [Controller 動作](/docs/{{version}}/controllers)的重新導向。若要重新導向到 Controller 動作，請將 Controller 與動作名稱傳入 `action` 方法：

    use App\Http\Controllers\HomeController;
    
    return redirect()->action([HomeController::class, 'index']);

若這個 Controller 的 Route 有要求參數，則可將這些參數作為第二個引數傳給 `action` 方法：

    return redirect()->action(
        [UserController::class, 'profile'], ['id' => 1]
    );

<a name="redirecting-with-flashed-session-data"></a>

## 重新導向時帶上快閃存入的 Session 資料

通常，我們在重新導向到新網址的時候，也會[將資料快閃存入 Session]。一般來說，這種情況通常是當某個動作順利進行，而我們將成功訊息寫入 Session 時。為了方便起見，我們可以建立一個 `RedirectResponse` 實體，並以一行流暢的方法串連呼叫來將資料快閃存入 Session：

    Route::post('/user/profile', function () {
        // 更新使用者的個人資料...
    
        return redirect('/dashboard')->with('status', 'Profile updated!');
    });

可以使用 `RedirectResponse` 實體提供的 `withInput` 方法來在將使用者重新導向到新位置前先將目前 Request 的輸入資料快閃存入 Session 中。將輸入資料快閃存入 Session 後，我們就可以在下一個 Request 中輕鬆地[取得這些資料](/docs/{{version}}/requests#retrieving-old-input)：

    return back()->withInput();

使用者被重新導向後，我們就可以從 [Session](/docs/{{version}}/session) 中顯示出剛才快閃存入的資料。舉例來說，我們可以使用 [Blade 語法](/docs/{{version}}/blade)：

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif
