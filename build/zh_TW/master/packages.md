# 套件開發

- [簡介](#introduction)
    - [有關 Facade 的注意事項](#a-note-on-facades)
- [Package 的 Discovery](#package-discovery)
- [Service Provider](#service-providers)
- [資源](#resources)
    - [設定檔](#configuration)
    - [Migration](#migrations)
    - [Route](#routes)
    - [翻譯](#translations)
    - [View](#views)
    - [View 元件](#view-components)
- [指令](#commands)
- [安裝素材](#public-assets)
- [安裝檔案群組](#publishing-file-groups)

<a name="introduction"></a>
## 簡介

套件是用來給 Laravel 新增功能的主要方法。套件可以是任何東西，有像
[Carbon](https://github.com/briannesbitt/Carbon) 這樣可以方便處理日期的套件、或者是像 Spatie 的
[Laravel Media Library](https://github.com/spatie/laravel-medialibrary)
這樣用來處理與 Eloquent Model 關聯檔案的套件。

套件也有一些不同的類型。有的套件是^[獨立](Stand-alone)套件，這些套件在任何的 PHP 框架上都可使用。舉例來說，Carbon 與
PHPUnit 就是獨立套件。只要在 `composer.json` 檔案中加上這些套件，就可以在 Laravel 中使用。

另一方面，有的套件是特別為了供 Laravel 使用而設計的。這些套件可能會有 Route、Controller、View、組態設定檔等等用來增強
Laravel 程式的功能。本篇指南主要就是在討論有關開發這些專為 Laravel 設計的套件。

<a name="a-note-on-facades"></a>
### 有關 Facade 的注意事項

When writing a Laravel application, it generally does not matter if you use
contracts or facades since both provide essentially equal levels of
testability. However, when writing packages, your package will not typically
have access to all of Laravel's testing helpers. If you would like to be
able to write your package tests as if the package were installed inside a
typical Laravel application, you may use the [Orchestral
Testbench](https://github.com/orchestral/testbench) package.

<a name="package-discovery"></a>
## Package Discovery

In a Laravel application's `config/app.php` configuration file, the
`providers` option defines a list of service providers that should be loaded
by Laravel. When someone installs your package, you will typically want your
service provider to be included in this list. Instead of requiring users to
manually add your service provider to the list, you may define the provider
in the `extra` section of your package's `composer.json` file. In addition
to service providers, you may also list any
[facades](/docs/{{version}}/facades) you would like to be registered:

```json
"extra": {
    "laravel": {
        "providers": [
            "Barryvdh\\Debugbar\\ServiceProvider"
        ],
        "aliases": {
            "Debugbar": "Barryvdh\\Debugbar\\Facade"
        }
    }
},
```

Once your package has been configured for discovery, Laravel will
automatically register its service providers and facades when it is
installed, creating a convenient installation experience for your package's
users.

<a name="opting-out-of-package-discovery"></a>
### Opting Out Of Package Discovery

If you are the consumer of a package and would like to disable package
discovery for a package, you may list the package name in the `extra`
section of your application's `composer.json` file:

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "barryvdh/laravel-debugbar"
        ]
    }
},
```

You may disable package discovery for all packages using the `*` character
inside of your application's `dont-discover` directive:

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "*"
        ]
    }
},
```

<a name="service-providers"></a>
## Service Providers

[Service providers](/docs/{{version}}/providers) are the connection point
between your package and Laravel. A service provider is responsible for
binding things into Laravel's [service
container](/docs/{{version}}/container) and informing Laravel where to load
package resources such as views, configuration, and localization files.

A service provider extends the `Illuminate\Support\ServiceProvider` class
and contains two methods: `register` and `boot`. The base `ServiceProvider`
class is located in the `illuminate/support` Composer package, which you
should add to your own package's dependencies. To learn more about the
structure and purpose of service providers, check out [their
documentation](/docs/{{version}}/providers).

<a name="resources"></a>
## Resources

<a name="configuration"></a>
### Configuration

Typically, you will need to publish your package's configuration file to the
application's `config` directory. This will allow users of your package to
easily override your default configuration options. To allow your
configuration files to be published, call the `publishes` method from the
`boot` method of your service provider:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/courier.php' => config_path('courier.php'),
        ]);
    }

Now, when users of your package execute Laravel's `vendor:publish` command,
your file will be copied to the specified publish location. Once your
configuration has been published, its values may be accessed like any other
configuration file:

    $value = config('courier.option');

> {note} You should not define closures in your configuration files. They can not be serialized correctly when users execute the `config:cache` Artisan command.

<a name="default-package-configuration"></a>
#### Default Package Configuration

You may also merge your own package configuration file with the
application's published copy. This will allow your users to define only the
options they actually want to override in the published copy of the
configuration file. To merge the configuration file values, use the
`mergeConfigFrom` method within your service provider's `register` method.

The `mergeConfigFrom` method accepts the path to your package's
configuration file as its first argument and the name of the application's
copy of the configuration file as its second argument:

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/courier.php', 'courier'
        );
    }

> {note} This method only merges the first level of the configuration array. If your users partially define a multi-dimensional configuration array, the missing options will not be merged.

<a name="routes"></a>
### Routes

If your package contains routes, you may load them using the
`loadRoutesFrom` method. This method will automatically determine if the
application's routes are cached and will not load your routes file if the
routes have already been cached:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }

<a name="migrations"></a>
### Migrations

If your package contains [database
migrations](/docs/{{version}}/migrations), you may use the
`loadMigrationsFrom` method to inform Laravel how to load them. The
`loadMigrationsFrom` method accepts the path to your package's migrations as
its only argument:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

Once your package's migrations have been registered, they will automatically
be run when the `php artisan migrate` command is executed. You do not need
to export them to the application's `database/migrations` directory.

<a name="translations"></a>
### Translations

If your package contains [translation
files](/docs/{{version}}/localization), you may use the
`loadTranslationsFrom` method to inform Laravel how to load them. For
example, if your package is named `courier`, you should add the following to
your service provider's `boot` method:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'courier');
    }

Package translations are referenced using the `package::file.line` syntax
convention. So, you may load the `courier` package's `welcome` line from the
`messages` file like so:

    echo trans('courier::messages.welcome');

<a name="publishing-translations"></a>
#### Publishing Translations

If you would like to publish your package's translations to the
application's `resources/lang/vendor` directory, you may use the service
provider's `publishes` method. The `publishes` method accepts an array of
package paths and their desired publish locations. For example, to publish
the translation files for the `courier` package, you may do the following:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'courier');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/courier'),
        ]);
    }

Now, when users of your package execute Laravel's `vendor:publish` Artisan
command, your package's translations will be published to the specified
publish location.

<a name="views"></a>
### Views

To register your package's [views](/docs/{{version}}/views) with Laravel,
you need to tell Laravel where the views are located. You may do this using
the service provider's `loadViewsFrom` method. The `loadViewsFrom` method
accepts two arguments: the path to your view templates and your package's
name. For example, if your package's name is `courier`, you would add the
following to your service provider's `boot` method:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
    }

Package views are referenced using the `package::view` syntax
convention. So, once your view path is registered in a service provider, you
may load the `dashboard` view from the `courier` package like so:

    Route::get('/dashboard', function () {
        return view('courier::dashboard');
    });

<a name="overriding-package-views"></a>
#### Overriding Package Views

When you use the `loadViewsFrom` method, Laravel actually registers two
locations for your views: the application's `resources/views/vendor`
directory and the directory you specify. So, using the `courier` package as
an example, Laravel will first check if a custom version of the view has
been placed in the `resources/views/vendor/courier` directory by the
developer. Then, if the view has not been customized, Laravel will search
the package view directory you specified in your call to
`loadViewsFrom`. This makes it easy for package users to customize /
override your package's views.

<a name="publishing-views"></a>
#### Publishing Views

If you would like to make your views available for publishing to the
application's `resources/views/vendor` directory, you may use the service
provider's `publishes` method. The `publishes` method accepts an array of
package view paths and their desired publish locations:

    /**
     * Bootstrap the package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/courier'),
        ]);
    }

Now, when users of your package execute Laravel's `vendor:publish` Artisan
command, your package's views will be copied to the specified publish
location.

<a name="view-components"></a>
### View Components

If your package contains [view
components](/docs/{{version}}/blade#components), you may use the
`loadViewComponentsAs` method to inform Laravel how to load them. The
`loadViewComponentsAs` method accepts two arguments: the tag prefix for your
view components and an array of your view component class names. For
example, if your package's prefix is `courier` and you have `Alert` and
`Button` view components, you would add the following to your service
provider's `boot` method:

    use Courier\Components\Alert;
    use Courier\Components\Button;

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewComponentsAs('courier', [
            Alert::class,
            Button::class,
        ]);
    }

Once your view components are registered in a service provider, you may
reference them in your view like so:

```blade
<x-courier-alert />

<x-courier-button />
```

<a name="anonymous-components"></a>
#### Anonymous Components

If your package contains anonymous components, they must be placed within a
`components` directory of your package's "views" directory (as specified by
`loadViewsFrom`). Then, you may render them by prefixing the component name
with the package's view namespace:

```blade
<x-courier::alert />
```

<a name="commands"></a>
## Commands

To register your package's Artisan commands with Laravel, you may use the
`commands` method. This method expects an array of command class names. Once
the commands have been registered, you may execute them using the [Artisan
CLI](/docs/{{version}}/artisan):

    use Courier\Console\Commands\InstallCommand;
    use Courier\Console\Commands\NetworkCommand;

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                NetworkCommand::class,
            ]);
        }
    }

<a name="public-assets"></a>
## Public Assets

Your package may have assets such as JavaScript, CSS, and images. To publish
these assets to the application's `public` directory, use the service
provider's `publishes` method. In this example, we will also add a `public`
asset group tag, which may be used to easily publish groups of related
assets:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/courier'),
        ], 'public');
    }

Now, when your package's users execute the `vendor:publish` command, your
assets will be copied to the specified publish location. Since users will
typically need to overwrite the assets every time the package is updated,
you may use the `--force` flag:

```shell
php artisan vendor:publish --tag=public --force
```

<a name="publishing-file-groups"></a>
## Publishing File Groups

You may want to publish groups of package assets and resources
separately. For instance, you might want to allow your users to publish your
package's configuration files without being forced to publish your package's
assets. You may do this by "tagging" them when calling the `publishes`
method from a package's service provider. For example, let's use tags to
define two publish groups for the `courier` package (`courier-config` and
`courier-migrations`) in the `boot` method of the package's service
provider:

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/package.php' => config_path('package.php')
        ], 'courier-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'courier-migrations');
    }

Now your users may publish these groups separately by referencing their tag
when executing the `vendor:publish` command:

```shell
php artisan vendor:publish --tag=courier-config
```
