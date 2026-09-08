---
name: laravel-result-development
description: >
  Use IvanFuhr Result (success/fail enums, whenSuccessful/whenFailed/otherwise) in Laravel
  actions, services, and controllers. Trigger when returning or handling Result values,
  modeling business failures as enums, or replacing try/catch for expected domain errors.
license: MIT
metadata:
  author: Ivan Führ
---

# Laravel Result Development

## When to use this skill

Use this skill when integrating `ivanfuhr/laravel-result`, returning `Result` from actions/services, handling success/failure in controllers, or modeling expected business failures as PHP enums.

## Install

```bash
composer require ivanfuhr/laravel-result
```

No service provider setup or config publish is required. Global helpers `success()` and `fail()` are available immediately via Composer file autoload.

## Core API

| API | Role |
| --- | --- |
| `success($value)` / `Result::success($value)` | Wrap a success payload |
| `fail(UnitEnum $case)` / `Result::fail($case)` | Wrap a failure enum case |
| `successful()` / `failed()` | Inspect state (narrows types for PHPStan) |
| `value()` / `valueOr($default)` / `failure()` | Read payload after state checks |
| `whenSuccessful(fn)` | Side-effect handler on success |
| `whenFailed($case, fn)` | Side-effect handler for an exact enum case (`===`) |
| `otherwise(fn)` | Runs only if no earlier handler matched |

## Failure enums

```php
enum CreateUserError
{
    case InvalidEmail;
    case EmailAlreadyExists;
}
```

Use one enum per operation or bounded context. `Result::fail()` accepts only `UnitEnum`.

## Service returning Result

```php
use IvanFuhr\Result\Result;

final readonly class CreateUser
{
    /**
     * @return Result<User, CreateUserError>
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

## Fluent handling (controllers)

```php
return $this->createUser->handle($email)
    ->whenSuccessful(fn (User $user) => redirect()->route('users.show', $user))
    ->whenFailed(CreateUserError::InvalidEmail, fn () => back()->withErrors(['email' => 'Invalid email.']))
    ->whenFailed(CreateUserError::EmailAlreadyExists, fn () => back()->withErrors(['email' => 'Email already in use.']))
    ->otherwise(fn () => abort(500));
```

Only the **first matching** handler runs.

## Rules

- Prefer `Result` over exceptions for expected business failures (validation, duplicates, not-found domain cases).
- Annotate `@return Result<TValue, TFailure>` for PHPStan generics.
- After `successful()` / `failed()`, use `value()` / `failure()`; prefer `valueOr()` when a default is enough.
- Handlers are for side effects; do not put transformation pipelines in `whenSuccessful` when you need a mapped value — read `value()` instead.
- Do not register `Result` in the container; construct via helpers or factories.

## Anti-patterns

- Catching business-rule failures with `try/catch` instead of returning `fail(...)`.
- Calling `value()` without checking `successful()` (throws `LogicException`).
- Passing non-enum failures to `fail()`.
- Documenting package internals in app code comments instead of using the public API above.
