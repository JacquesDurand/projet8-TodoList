---
sidebar_position: 2
---

# Tests

Tests are a huge part of an application, to make sure everything works as intended before
shipping anything.

We will try to show here how we can produce "some" numbers to verify that the quantity
and quality of our tests might be enough.

## Coverage

The code coverage corresponds to the percentage of our code that has been executed by our tests.

For instance, if I have the following service:

```php
class ExampleService
{
     public function isStrictlyOverTwo(int $number): bool
     {
         if ($number > 2) {
            return true;
         }
         return false;
     }
}
```

and the following test:

```php
class ExampleServiceTest extends \PHPUnit\Framework\TestCase
{
    public function __construct(private readonly ExampleService $exampleService)
    {
    }

    public function testIsStrictlyOverTwo(): void
    {
        $result = $this->exampleService->isStrictlyOverTwo(5);
        $this->assertTrue($result);
    }
}
```

Then we won't be covering the whole "isStrictlyOverTwo" function because we did not test
the case where `$number <= 2`  
So our coverage for this service won't be 100%, which will show us that we might have forgotten
some cases to test !

To execute your tests with a coverage report:

```shell
make phpunit-coverage
```

Then go to ` coverage ` and open ` index.html ` in your favorite browser

## Mutation score

Sometimes, when we test a function, or even a complete feature, we might forget some cases that
could happen in a real life environment for known or unknown reasons.  
Mutation testing, via [Infection](https://infection.github.io/) in our case, implies mutating our 
code in small ways (pass an argument to null, remove an element from an array ...) and run our tests
to see if anything breaks.

The mutation score is computed by our CI and shown on the Readme.

Keep in mind that we do not necessary need to kill *every* mutant found by Infection. For instance,
we haven't here tested that every label coming from our FormTypes is actually shown in the page,
and Infection will tell us so. But we see them nonetheless and it would be tedious to test
every little thing.

It can provide us with great info sometimes though, do not hesitate to run it after adding a new feature.
