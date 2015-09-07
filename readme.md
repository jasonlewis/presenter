A view presenter is an incredibly useful way of decorating objects bound to your views. This allows you to perform view
related logic in a sensible place instead of placing it directly in your views, or worse, your models.

There are [several](https://github.com/laravel-auto-presenter/laravel-auto-presenter) [other](https://github.com/laracasts/Presenter) [libraries](https://github.com/robclancy/presenter) out their that provide similar functionality. This package is merely my preferred implementation as everything is configured in isolation to the object being decorated.

### Install

You can install this package using Composer:

```
$ composer require lewis/presenter
```

Or in your `composer.json`:

```json
{
    "require": {
        "lewis/presenter": "0.1.*"
    }
}
```

Once you've run `composer update` you'll need to register the **service provider** in your `config/app.php` file.

```php
'providers' => [
    Lewis\Presenter\PresenterServiceProvider::class
]
```

### Usage

#### Configuring Presenters

There's several ways to configure your presenters. First, you can utilize the configuration file, which can be published using the following command:

```
$ php artisan vendor:publish --provider="Lewis\Presenter\PresenterServiceProvider"
```

There are no presenters configured by default. The published file merely contains an explanation on how to configure your presenters. You must provide an array of
key/value pairs linking your object to its presenter.

```php
return [

    App\User::class => App\Presenters\UserPresenter::class,
    App\Post::class => App\Presenters\PostPresenter::class

];
```

If you'd prefer you can set an array of presenters directly on the decorator. You might choose do to this from a service provider.

```php
$this->app['decorator']->setBindings([
    \App\User::class => \App\Presenters\UserPresenter::class,
    \App\Post::class => \App\Presenters\PostPresenter::class
]);
```

Lastly, you can configure presenters one at a time using the `register` method, again, from within a provider.

```php
$this->app['decorator']->register(\App\User::class, \App\Presenters\UserPresenter::class);
```

#### Creating Presenters

A presenter should extend from `Lewis\Presenter\AbstractPresenter`, however, it is *NOT* required, but highly recommended, as you'll have access to several
methods and magic methods that provide some useful functionality.

I like to keep my presenters within a `Presenters` folder, however, you may organize things in whichever way you prefer.

```php
namespace App\Presenters;

use Lewis\Presenter\AbstractPresenter;

class UserPresenter extends AbstractPresenter
{

}
```

If you wish to inject dependencies the only requirements are that you name a parameter `$object` so that Laravel can correctly inject the bound object and that
you call the parent constructor.


```php
namespace App\Presenters;

use App\SomeNamespace\SomeClass;
use Lewis\Presenter\AbstractPresenter;

class UserPresenter extends AbstractPresenter
{
    protected $class;

    public function __construct($object, SomeClass $class)
    {
        $this->class = $class;

        parent::__construct($object);
    }
}
```

Your presenter can then define methods to perform logic that can be used in your views.

```php
public function prettySlug()
{
    return '/'.ltrim($this->slug, '/');
}
```

You can reference properties on the wrapped object directly (as above), or by using the `$object` property.

```php
public function prettySlug()
{
    return '/'.ltrim($this->object->slug, '/');
}
```

#### From Within Views

Now that you've configured your presenters and created them, you just need to use them from within your views. It's a simple matter of calling the method or property
as you define it.

```php
{{ $post->prettySlug }}

Or:

{{ $post->prettySlug() }}
```

You can also still access you relations and other model attributes.

```php
{{ $post->title }}

@foreach($post->comments as $comment)
    ...
@endforeach
```

### Enjoy

That's about all there is to it.
