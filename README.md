<div align="center">
    <h1>Laravel Result</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/ivanfuhr/laravel-result"><img src="https://img.shields.io/packagist/v/ivanfuhr/laravel-result.svg?style=flat-square" alt="Latest Version"></a>
    <a href="https://packagist.org/packages/ivanfuhr/laravel-result"><img src="https://img.shields.io/packagist/php-v/ivanfuhr/laravel-result.svg?style=flat-square" alt="PHP Version"></a>
    <a href="https://github.com/ivanfuhr/laravel-result/actions"><img alt="Tests" src="https://img.shields.io/github/actions/workflow/status/ivanfuhr/laravel-result/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/ivanfuhr/laravel-result"><img src="https://img.shields.io/packagist/l/ivanfuhr/laravel-result.svg?style=flat-square" alt="License"></a>
</p>

A typed **success/failure** `Result` for Laravel service and action outcomes. Expected business failures are modeled as **PHP enums**, not thrown exceptions — so invalid email, duplicates, and similar cases stay explicit and easy to branch on.

> **Requires [PHP 8.3+](https://php.net/releases/)** and **[Laravel 12+](https://laravel.com/docs/12.x/)**.

## Installation

```bash
composer require ivanfuhr/laravel-result
```

No configuration or service provider setup is required. Composer autoloads the class and registers global helpers immediately.

## Usage

### Defining failure enums

```php
enum CreateUserError
{
    case InvalidEmail;
    case EmailAlreadyExists;
}
```

Use a dedicated enum per use case (or domain area). Backed enums work well when you need stable codes for APIs or translations.

### Creating results

```php
use IvanFuhr\Result\Result;

$result = success($user);
$result = fail(CreateUserError::InvalidEmail);

$result = Result::success($user);
$result = Result::fail(CreateUserError::InvalidEmail);
```

> **Note:** The `fail()` helper and `Result::fail()` factory exist because PHP does not allow a static `failure()` factory and an instance `failure()` getter on the same class. The getter returns the enum case: `$result->failure()`.

### Checking state

| Method | Description |
|--------|-------------|
| `successful()` | `true` when the result holds a success value |
| `failed()` | `true` when the result holds a failure enum |
| `value()` | Returns the success value (call only when `successful()` is `true`) |
| `valueOr($default)` | Returns the success value, or `$default` on failure |
| `failure()` | Returns the failure `UnitEnum` (call only when `failed()` is `true`) |

```php
if ($result->successful()) {
    $user = $result->value();
}

$guest = $result->valueOr(null);

if ($result->failed()) {
    $error = $result->failure();
}
```

### Fluent handling

Chain handlers on the same result. Only the **first matching** handler runs.

| Method | Description |
|--------|-------------|
| `whenSuccessful(callable $callback)` | Runs only on success; receives the value |
| `whenFailed(UnitEnum $expectedFailure, callable $callback)` | Runs only when the failure enum equals `$expectedFailure` (`===`) |
| `otherwise(callable $callback)` | Runs only if no earlier handler matched |

```php
return $this->createUser->handle($email)
    ->whenSuccessful(fn (User $user) => redirect()->route('users.show', $user))
    ->whenFailed(CreateUserError::InvalidEmail, fn () => back()->withErrors(['email' => 'Invalid email.']))
    ->whenFailed(CreateUserError::EmailAlreadyExists, fn () => back()->withErrors(['email' => 'Email already in use.']))
    ->otherwise(fn () => abort(500));
```

### Service returning a Result

```php
use IvanFuhr\Result\Result;

final readonly class CreateUser
{
    /**
     * @return Result<\App\Models\User, CreateUserError>
     */
    public function handle(string $email): Result
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return fail(CreateUserError::InvalidEmail);
        }

        if (User::query()->where('email', $email)->exists()) {
            return fail(CreateUserError::EmailAlreadyExists);
        }

        return success(User::create(['email' => $email]));
    }
}
```

**PHPStan generics:** annotate actions with `@return Result<YourModel, YourFailureEnum>`. After `successful()` / `failed()`, `value()` and `failure()` are narrowed accordingly.

## Tips

- `Result::fail()` only accepts `UnitEnum` — define one enum per operation or bounded context.
- `whenFailed()` compares enum cases with `===`.
- Use `otherwise()` for unhandled enum cases.
- Handlers are for side effects (redirects, logging, HTTP mapping). Use `value()` / `failure()` when you need the payload after checking state.

## Testing

```bash
composer test
```

This runs Rector (dry-run), Pint, PHPStan (level max + 100% type coverage), Pest type coverage (100%), and the test suite.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Result! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Ivan Führ](https://github.com/ivanfuhr)
- [All Contributors](../../contributors)

## License

Laravel Result is open-sourced software licensed under the [MIT license](LICENSE.md).
