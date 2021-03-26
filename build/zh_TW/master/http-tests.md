# HTTP Tests

- [Introduction](#introduction)
- [Making Requests](#making-requests)
    - [Customizing Request Headers](#customizing-request-headers)
    - [Cookies](#cookies)
    - [Session / Authentication](#session-and-authentication)
    - [Debugging Responses](#debugging-responses)
- [Testing JSON APIs](#testing-json-apis)
- [Testing File Uploads](#testing-file-uploads)
- [Testing Views](#testing-views)
    - [Rendering Blade & Components](#rendering-blade-and-components)
- [Available Assertions](#available-assertions)
    - [Response Assertions](#response-assertions)
    - [Authentication Assertions](#authentication-assertions)

<a name="introduction"></a>
## Introduction

Laravel provides a very fluent API for making HTTP requests to your
application and examining the responses. For example, take a look at the
feature test defined below:

    <?php

    namespace Tests\Feature;

    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_a_basic_request()
        {
            $response = $this->get('/');

            $response->assertStatus(200);
        }
    }

The `get` method makes a `GET` request into the application, while the
`assertStatus` method asserts that the returned response should have the
given HTTP status code. In addition to this simple assertion, Laravel also
contains a variety of assertions for inspecting the response headers,
content, JSON structure, and more.

<a name="making-requests"></a>
## Making Requests

To make a request to your application, you may invoke the `get`, `post`,
`put`, `patch`, or `delete` methods within your test. These methods do not
actually issue a "real" HTTP request to your application. Instead, the
entire network request is simulated internally.

Instead of returning an `Illuminate\Http\Response` instance, test request
methods return an instance of `Illuminate\Testing\TestResponse`, which
provides a [variety of helpful assertions](#available-assertions) that allow
you to inspect your application's responses:

    <?php

    namespace Tests\Feature;

    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_a_basic_request()
        {
            $response = $this->get('/');

            $response->assertStatus(200);
        }
    }

> {tip} For convenience, the CSRF middleware is automatically disabled when running tests.

<a name="customizing-request-headers"></a>
### Customizing Request Headers

You may use the `withHeaders` method to customize the request's headers
before it is sent to the application. This method allows you to add any
custom headers you would like to the request:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_interacting_with_headers()
        {
            $response = $this->withHeaders([
                'X-Header' => 'Value',
            ])->post('/user', ['name' => 'Sally']);

            $response->assertStatus(201);
        }
    }

<a name="cookies"></a>
### Cookies

You may use the `withCookie` or `withCookies` methods to set cookie values
before making a request. The `withCookie` method accepts a cookie name and
value as its two arguments, while the `withCookies` method accepts an array
of name / value pairs:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        public function test_interacting_with_cookies()
        {
            $response = $this->withCookie('color', 'blue')->get('/');

            $response = $this->withCookies([
                'color' => 'blue',
                'name' => 'Taylor',
            ])->get('/');
        }
    }

<a name="session-and-authentication"></a>
### Session / Authentication

Laravel provides several helpers for interacting with the session during
HTTP testing. First, you may set the session data to a given array using the
`withSession` method. This is useful for loading the session with data
before issuing a request to your application:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        public function test_interacting_with_the_session()
        {
            $response = $this->withSession(['banned' => false])->get('/');
        }
    }

Laravel's session is typically used to maintain state for the currently
authenticated user. Therefore, the `actingAs` helper method provides a
simple way to authenticate a given user as the current user. For example, we
may use a [model
factory](/docs/{{version}}/database-testing#writing-factories) to generate
and authenticate a user:

    <?php

    namespace Tests\Feature;

    use App\Models\User;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        public function test_an_action_that_requires_authentication()
        {
            $user = User::factory()->create();

            $response = $this->actingAs($user)
                             ->withSession(['banned' => false])
                             ->get('/');
        }
    }

You may also specify which guard should be used to authenticate the given
user by passing the guard name as the second argument to the `actingAs`
method:

    $this->actingAs($user, 'api')

<a name="debugging-responses"></a>
### Debugging Responses

After making a test request to your application, the `dump`, `dumpHeaders`,
and `dumpSession` methods may be used to examine and debug the response
contents:

    <?php

    namespace Tests\Feature;

    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic test example.
         *
         * @return void
         */
        public function test_basic_test()
        {
            $response = $this->get('/');

            $response->dumpHeaders();

            $response->dumpSession();

            $response->dump();
        }
    }

<a name="testing-json-apis"></a>
## Testing JSON APIs

Laravel also provides several helpers for testing JSON APIs and their
responses. For example, the `json`, `getJson`, `postJson`, `putJson`,
`patchJson`, `deleteJson`, and `optionsJson` methods may be used to issue
JSON requests with various HTTP verbs. You may also easily pass data and
headers to these methods. To get started, let's write a test to make a
`POST` request to `/api/user` and assert that the expected JSON data was
returned:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_making_an_api_request()
        {
            $response = $this->postJson('/api/user', ['name' => 'Sally']);

            $response
                ->assertStatus(201)
                ->assertJson([
                    'created' => true,
                ]);
        }
    }

In addition, JSON response data may be accessed as array variables on the
response, making it convenient for you to inspect the individual values
returned within a JSON response:

    $this->assertTrue($response['created']);

> {tip} The `assertJson` method converts the response to an array and utilizes `PHPUnit::assertArraySubset` to verify that the given array exists within the JSON response returned by the application. So, if there are other properties in the JSON response, this test will still pass as long as the given fragment is present.

<a name="verifying-exact-match"></a>
#### Asserting Exact JSON Matches

As previously mentioned, the `assertJson` method may be used to assert that
a fragment of JSON exists within the JSON response. If you would like to
verify that a given array **exactly matches** the JSON returned by your
application, you should use the `assertExactJson` method:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_asserting_an_exact_json_match()
        {
            $response = $this->json('POST', '/user', ['name' => 'Sally']);

            $response
                ->assertStatus(201)
                ->assertExactJson([
                    'created' => true,
                ]);
        }
    }

<a name="verifying-json-paths"></a>
#### Asserting On JSON Paths

If you would like to verify that the JSON response contains the given data
at a specified path, you should use the `assertJsonPath` method:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        /**
         * A basic functional test example.
         *
         * @return void
         */
        public function test_asserting_a_json_paths_value()
        {
            $response = $this->json('POST', '/user', ['name' => 'Sally']);

            $response
                ->assertStatus(201)
                ->assertJsonPath('team.owner.name', 'Darian');
        }
    }

<a name="testing-file-uploads"></a>
## Testing File Uploads

The `Illuminate\Http\UploadedFile` class provides a `fake` method which may
be used to generate dummy files or images for testing. This, combined with
the `Storage` facade's `fake` method, greatly simplifies the testing of file
uploads. For example, you may combine these two features to easily test an
avatar upload form:

    <?php

    namespace Tests\Feature;

    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithoutMiddleware;
    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Storage;
    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        public function test_avatars_can_be_uploaded()
        {
            Storage::fake('avatars');

            $file = UploadedFile::fake()->image('avatar.jpg');

            $response = $this->post('/avatar', [
                'avatar' => $file,
            ]);

            Storage::disk('avatars')->assertExists($file->hashName());
        }
    }

If you would like to assert that a given file does not exist, you may use
the `assertMissing` method provided by the `Storage` facade:

    Storage::fake('avatars');

    // ...

    Storage::disk('avatars')->assertMissing('missing.jpg');

<a name="fake-file-customization"></a>
#### Fake File Customization

When creating files using the `fake` method provided by the `UploadedFile`
class, you may specify the width, height, and size of the image (in
kilobytes) in order to better test your application's validation rules:

    UploadedFile::fake()->image('avatar.jpg', $width, $height)->size(100);

In addition to creating images, you may create files of any other type using
the `create` method:

    UploadedFile::fake()->create('document.pdf', $sizeInKilobytes);

If needed, you may pass a `$mimeType` argument to the method to explicitly
define the MIME type that should be returned by the file:

    UploadedFile::fake()->create(
        'document.pdf', $sizeInKilobytes, 'application/pdf'
    );

<a name="testing-views"></a>
## Testing Views

Laravel also allows you to render a view without making a simulated HTTP
request to the application. To accomplish this, you may call the `view`
method within your test. The `view` method accepts the view name and an
optional array of data. The method returns an instance of
`Illuminate\Testing\TestView`, which offers several methods to conveniently
make assertions about the view's contents:

    <?php

    namespace Tests\Feature;

    use Tests\TestCase;

    class ExampleTest extends TestCase
    {
        public function test_a_welcome_view_can_be_rendered()
        {
            $view = $this->view('welcome', ['name' => 'Taylor']);

            $view->assertSee('Taylor');
        }
    }

The `TestView` class provides the following assertion methods: `assertSee`,
`assertSeeInOrder`, `assertSeeText`, `assertSeeTextInOrder`,
`assertDontSee`, and `assertDontSeeText`.

If needed, you may get the raw, rendered view contents by casting the
`TestView` instance to a string:

    $contents = (string) $this->view('welcome');

<a name="sharing-errors"></a>
#### Sharing Errors

Some views may depend on errors shared in the [global error bag provided by
Laravel](/docs/{{version}}/validation#quick-displaying-the-validation-errors).
To hydrate the error bag with error messages, you may use the
`withViewErrors` method:

    $view = $this->withViewErrors([
        'name' => ['Please provide a valid name.']
    ])->view('form');

    $view->assertSee('Please provide a valid name.');

<a name="rendering-blade-and-components"></a>
### Rendering Blade & Components

If necessary, you may use the `blade` method to evaluate and render a raw
[Blade](/docs/{{version}}/blade) string. Like the `view` method, the `blade`
method returns an instance of `Illuminate\Testing\TestView`:

    $view = $this->blade(
        '<x-component :name="$name" />',
        ['name' => 'Taylor']
    );

    $view->assertSee('Taylor');

You may use the `component` method to evaluate and render a [Blade
component](/docs/{{version}}/blade#components). Like the `view` method, the
`component` method returns an instance of `Illuminate\Testing\TestView`:

    $view = $this->component(Profile::class, ['name' => 'Taylor']);

    $view->assertSee('Taylor');

<a name="available-assertions"></a>
## Available Assertions

<a name="response-assertions"></a>
### Response Assertions

Laravel's `Illuminate\Testing\TestResponse` class provides a variety of
custom assertion methods that you may utilize when testing your
application. These assertions may be accessed on the response that is
returned by the `json`, `get`, `post`, `put`, and `delete` test methods:

<style>
    .collection-method-list > p {
        column-count: 2; -moz-column-count: 2; -webkit-column-count: 2;
        column-gap: 2em; -moz-column-gap: 2em; -webkit-column-gap: 2em;
    }

    .collection-method-list a {
        display: block;
    }
</style>

<div class="collection-method-list" markdown="1">

[assertCookie](#assert-cookie)
[assertCookieExpired](#assert-cookie-expired)
[assertCookieNotExpired](#assert-cookie-not-expired)
[assertCookieMissing](#assert-cookie-missing)
[assertCreated](#assert-created)  [assertDontSee](#assert-dont-see)
[assertDontSeeText](#assert-dont-see-text)
[assertExactJson](#assert-exact-json)  [assertForbidden](#assert-forbidden)
[assertHeader](#assert-header)
[assertHeaderMissing](#assert-header-missing)  [assertJson](#assert-json)
[assertJsonCount](#assert-json-count)
[assertJsonFragment](#assert-json-fragment)
[assertJsonMissing](#assert-json-missing)
[assertJsonMissingExact](#assert-json-missing-exact)
[assertJsonMissingValidationErrors](#assert-json-missing-validation-errors)
[assertJsonPath](#assert-json-path)
[assertJsonStructure](#assert-json-structure)
[assertJsonValidationErrors](#assert-json-validation-errors)
[assertLocation](#assert-location)  [assertNoContent](#assert-no-content)
[assertNotFound](#assert-not-found)  [assertOk](#assert-ok)
[assertPlainCookie](#assert-plain-cookie)
[assertRedirect](#assert-redirect)  [assertSee](#assert-see)
[assertSeeInOrder](#assert-see-in-order)  [assertSeeText](#assert-see-text)
[assertSeeTextInOrder](#assert-see-text-in-order)
[assertSessionHas](#assert-session-has)
[assertSessionHasInput](#assert-session-has-input)
[assertSessionHasAll](#assert-session-has-all)
[assertSessionHasErrors](#assert-session-has-errors)
[assertSessionHasErrorsIn](#assert-session-has-errors-in)
[assertSessionHasNoErrors](#assert-session-has-no-errors)
[assertSessionDoesntHaveErrors](#assert-session-doesnt-have-errors)
[assertSessionMissing](#assert-session-missing)
[assertStatus](#assert-status)  [assertSuccessful](#assert-successful)
[assertUnauthorized](#assert-unauthorized)
[assertViewHas](#assert-view-has)  [assertViewHasAll](#assert-view-has-all)
[assertViewIs](#assert-view-is)  [assertViewMissing](#assert-view-missing)

</div>

<a name="assert-cookie"></a>
#### assertCookie

Assert that the response contains the given cookie:

    $response->assertCookie($cookieName, $value = null);

<a name="assert-cookie-expired"></a>
#### assertCookieExpired

Assert that the response contains the given cookie and it is expired:

    $response->assertCookieExpired($cookieName);

<a name="assert-cookie-not-expired"></a>
#### assertCookieNotExpired

Assert that the response contains the given cookie and it is not expired:

    $response->assertCookieNotExpired($cookieName);

<a name="assert-cookie-missing"></a>
#### assertCookieMissing

Assert that the response does not contains the given cookie:

    $response->assertCookieMissing($cookieName);

<a name="assert-created"></a>
#### assertCreated

Assert that the response has a 201 HTTP status code:

    $response->assertCreated();

<a name="assert-dont-see"></a>
#### assertDontSee

Assert that the given string is not contained within the response returned
by the application. This assertion will automatically escape the given
string unless you pass a second argument of `false`:

    $response->assertDontSee($value, $escaped = true);

<a name="assert-dont-see-text"></a>
#### assertDontSeeText

Assert that the given string is not contained within the response text. This
assertion will automatically escape the given string unless you pass a
second argument of `false`. This method will pass the response content to
the `strip_tags` PHP function before making the assertion:

    $response->assertDontSeeText($value, $escaped = true);

<a name="assert-exact-json"></a>
#### assertExactJson

Assert that the response contains an exact match of the given JSON data:

    $response->assertExactJson(array $data);

<a name="assert-forbidden"></a>
#### assertForbidden

Assert that the response has a forbidden (403) HTTP status code:

    $response->assertForbidden();

<a name="assert-header"></a>
#### assertHeader

Assert that the given header and value is present on the response:

    $response->assertHeader($headerName, $value = null);

<a name="assert-header-missing"></a>
#### assertHeaderMissing

Assert that the given header is not present on the response:

    $response->assertHeaderMissing($headerName);

<a name="assert-json"></a>
#### assertJson

Assert that the response contains the given JSON data:

    $response->assertJson(array $data, $strict = false);

The `assertJson` method converts the response to an array and utilizes
`PHPUnit::assertArraySubset` to verify that the given array exists within
the JSON response returned by the application. So, if there are other
properties in the JSON response, this test will still pass as long as the
given fragment is present.

<a name="assert-json-count"></a>
#### assertJsonCount

Assert that the response JSON has an array with the expected number of items
at the given key:

    $response->assertJsonCount($count, $key = null);

<a name="assert-json-fragment"></a>
#### assertJsonFragment

Assert that the response contains the given JSON data anywhere in the
response:

    Route::get('/users', function () {
        return [
            'users' => [
                [
                    'name' => 'Taylor Otwell',
                ],
            ],
        ];
    });

    $response->assertJsonFragment(['name' => 'Taylor Otwell']);

<a name="assert-json-missing"></a>
#### assertJsonMissing

Assert that the response does not contain the given JSON data:

    $response->assertJsonMissing(array $data);

<a name="assert-json-missing-exact"></a>
#### assertJsonMissingExact

Assert that the response does not contain the exact JSON data:

    $response->assertJsonMissingExact(array $data);

<a name="assert-json-missing-validation-errors"></a>
#### assertJsonMissingValidationErrors

Assert that the response has no JSON validation errors for the given keys:

    $response->assertJsonMissingValidationErrors($keys);

<a name="assert-json-path"></a>
#### assertJsonPath

Assert that the response contains the given data at the specified path:

    $response->assertJsonPath($path, array $data, $strict = true);

For example, if the JSON response returned by your application contains the
following data:

```js
{
    "user": {
        "name": "Steve Schoger"
    }
}
```

You may assert that the `name` property of the `user` object matches a given
value like so:

    $response->assertJsonPath('user.name', 'Steve Schoger');

<a name="assert-json-structure"></a>
#### assertJsonStructure

Assert that the response has a given JSON structure:

    $response->assertJsonStructure(array $structure);

For example, if the JSON response returned by your application contains the
following data:

```js
{
    "user": {
        "name": "Steve Schoger"
    }
}
```

You may assert that the JSON structure matches your expectations like so:

    $response->assertJsonStructure([
        'user' => [
            'name',
        ]
    ]);

<a name="assert-json-validation-errors"></a>
#### assertJsonValidationErrors

Assert that the response has the given JSON validation errors for the given
keys. This method should be used when asserting against responses where the
validation errors are returned as a JSON structure instead of being flashed
to the session:

    $response->assertJsonValidationErrors(array $data);

<a name="assert-location"></a>
#### assertLocation

Assert that the response has the given URI value in the `Location` header:

    $response->assertLocation($uri);

<a name="assert-no-content"></a>
#### assertNoContent

Assert that the response has the given HTTP status code and no content:

    $response->assertNoContent($status = 204);

<a name="assert-not-found"></a>
#### assertNotFound

Assert that the response has a not found (404) HTTP status code:

    $response->assertNotFound();

<a name="assert-ok"></a>
#### assertOk

Assert that the response has a 200 HTTP status code:

    $response->assertOk();

<a name="assert-plain-cookie"></a>
#### assertPlainCookie

Assert that the response contains the given unencrypted cookie:

    $response->assertPlainCookie($cookieName, $value = null);

<a name="assert-redirect"></a>
#### assertRedirect

Assert that the response is a redirect to the given URI:

    $response->assertRedirect($uri);

<a name="assert-see"></a>
#### assertSee

Assert that the given string is contained within the response. This
assertion will automatically escape the given string unless you pass a
second argument of `false`:

    $response->assertSee($value, $escaped = true);

<a name="assert-see-in-order"></a>
#### assertSeeInOrder

Assert that the given strings are contained in order within the
response. This assertion will automatically escape the given strings unless
you pass a second argument of `false`:

    $response->assertSeeInOrder(array $values, $escaped = true);

<a name="assert-see-text"></a>
#### assertSeeText

Assert that the given string is contained within the response text. This
assertion will automatically escape the given string unless you pass a
second argument of `false`. The response content will be passed to the
`strip_tags` PHP function before the assertion is made:

    $response->assertSeeText($value, $escaped = true);

<a name="assert-see-text-in-order"></a>
#### assertSeeTextInOrder

Assert that the given strings are contained in order within the response
text. This assertion will automatically escape the given strings unless you
pass a second argument of `false`. The response content will be passed to
the `strip_tags` PHP function before the assertion is made:

    $response->assertSeeTextInOrder(array $values, $escaped = true);

<a name="assert-session-has"></a>
#### assertSessionHas

Assert that the session contains the given piece of data:

    $response->assertSessionHas($key, $value = null);

<a name="assert-session-has-input"></a>
#### assertSessionHasInput

Assert that the session has a given value in the [flashed input
array](/docs/{{version}}/responses#redirecting-with-flashed-session-data):

    $response->assertSessionHasInput($key, $value = null);

<a name="assert-session-has-all"></a>
#### assertSessionHasAll

Assert that the session contains a given array of key / value pairs:

    $response->assertSessionHasAll(array $data);

For example, if your application's session contains `name` and `status`
keys, you may assert that both exist and have the specified values like so:

    $response->assertSessionHasAll([
        'name' => 'Taylor Otwell',
        'status' => 'active',
    ]);

<a name="assert-session-has-errors"></a>
#### assertSessionHasErrors

Assert that the session contains an error for the given `$keys`. If `$keys`
is an associative array, assert that the session contains a specific error
message (value) for each field (key). This method should be used when
testing routes that flash validation errors to the session instead of
returning them as a JSON structure:

    $response->assertSessionHasErrors(
        array $keys, $format = null, $errorBag = 'default'
    );

For example, to assert that the `name` and `email` field have validation
error messages that were flashed to the session, you may invoke the
`assertSessionHasErrors` method like so:

    $response->assertSessionHasErrors(['name', 'email']);

Or, you may assert that a given field has a particular validation error
message:

    $response->assertSessionHasErrors([
        'name' => 'The given name was invalid.'
    ]);

<a name="assert-session-has-errors-in"></a>
#### assertSessionHasErrorsIn

Assert that the session contains an error for the given `$keys` within a
specific [error bag](/docs/{{version}}/validation#named-error-bags). If
`$keys` is an associative array, assert that the session contains a specific
error message (value) for each field (key), within the error bag:

    $response->assertSessionHasErrorsIn($errorBag, $keys = [], $format = null);

<a name="assert-session-has-no-errors"></a>
#### assertSessionHasNoErrors

Assert that the session has no validation errors:

    $response->assertSessionHasNoErrors();

<a name="assert-session-doesnt-have-errors"></a>
#### assertSessionDoesntHaveErrors

Assert that the session has no validation errors for the given keys:

    $response->assertSessionDoesntHaveErrors($keys = [], $format = null, $errorBag = 'default');

<a name="assert-session-missing"></a>
#### assertSessionMissing

Assert that the session does not contain the given key:

    $response->assertSessionMissing($key);

<a name="assert-status"></a>
#### assertStatus

Assert that the response has a given HTTP status code:

    $response->assertStatus($code);

<a name="assert-successful"></a>
#### assertSuccessful

Assert that the response has a successful (>= 200 and < 300) HTTP status code:

    $response->assertSuccessful();

<a name="assert-unauthorized"></a>
#### assertUnauthorized

Assert that the response has an unauthorized (401) HTTP status code:

    $response->assertUnauthorized();

<a name="assert-view-has"></a>
#### assertViewHas

Assert that the response view contains given a piece of data:

    $response->assertViewHas($key, $value = null);

In addition, view data may be accessed as array variables on the response,
allowing you to convenient inspect it:

    $this->assertEquals('Taylor', $response['name']);

<a name="assert-view-has-all"></a>
#### assertViewHasAll

Assert that the response view has a given list of data:

    $response->assertViewHasAll(array $data);

This method may be used to assert that the view simply contains data
matching the given keys:

    $response->assertViewHasAll([
        'name',
        'email',
    ]);

Or, you may assert that the view data is present and has specific values:

    $response->assertViewHasAll([
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com,',
    ]);

<a name="assert-view-is"></a>
#### assertViewIs

Assert that the given view was returned by the route:

    $response->assertViewIs($value);

<a name="assert-view-missing"></a>
#### assertViewMissing

Assert that the given data key was not made available to the view returned
in the application's response:

    $response->assertViewMissing($key);

<a name="authentication-assertions"></a>
### Authentication Assertions

Laravel also provides a variety of authentication related assertions that
you may utilize within your application's feature tests. Note that these
methods are invoked on the test class itself and not the
`Illuminate\Testing\TestResponse` instance returned by methods such as `get`
and `post`.

<a name="assert-authenticated"></a>
#### assertAuthenticated

Assert that a user is authenticated:

    $this->assertAuthenticated($guard = null);

<a name="assert-guest"></a>
#### assertGuest

Assert that a user is not authenticated:

    $this->assertGuest($guard = null);

<a name="assert-authenticated-as"></a>
#### assertAuthenticatedAs

Assert that a specific user is authenticated:

    $this->assertAuthenticatedAs($user, $guard = null);
