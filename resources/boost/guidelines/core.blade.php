## Laravel Result

`ivanfuhr/laravel-result` provides a typed success/failure `Result` for service and action outcomes. Expected business failures are modeled as PHP enums, not exceptions.

### Features

- Create results with global helpers or the class API:

@verbatim
<code-snippet name="Create a Result" lang="php">
use IvanFuhr\Result\Result;

$result = success($user);
$result = fail(CreateUserError::InvalidEmail);

$result = Result::success($user);
$result = Result::fail(CreateUserError::InvalidEmail);
</code-snippet>
@endverbatim

- Branch with fluent handlers (first match wins):

@verbatim
<code-snippet name="Handle a Result" lang="php">
return $createUser->handle($email)
    ->whenSuccessful(fn (User $user) => redirect()->route('users.show', $user))
    ->whenFailed(CreateUserError::InvalidEmail, fn () => back()->withErrors(['email' => 'Invalid email.']))
    ->whenFailed(CreateUserError::EmailAlreadyExists, fn () => back()->withErrors(['email' => 'Email already in use.']))
    ->otherwise(fn () => abort(500));
</code-snippet>
@endverbatim

- Annotate returns for PHPStan: `@return Result<YourModel, YourFailureEnum>`.
- No publishable config or Artisan setup is required; helpers autoload via Composer.
- Do not throw for expected business failures — return `fail(YourError::Case)` instead.
- Prefer `whenSuccessful` / `whenFailed` / `otherwise` for side effects; use `value()` / `failure()` after `successful()` / `failed()` checks.
