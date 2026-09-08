<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('IvanFuhr\Result')
    ->toUseStrictTypes();

arch('Result is final')
    ->expect(IvanFuhr\Result\Result::class)
    ->toBeFinal();
