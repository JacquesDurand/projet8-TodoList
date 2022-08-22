---
sidebar_position: 1
---

# Quality 

To make sur the application could be (and stay) at modern levels of code quality, we made sure
current and future developers have the right tools to keep the code clean:

## Code Style

To make sure that:
- Everything looks the same for everyone involved 
- Your work environment won't be adding unwanted blank spaces/invisible characters and make
every pull request a nightmare of tons of unnecessary modified files
- Coding standards are respected (in our case, [Symfony's](https://symfony.com/doc/current/contributing/code/standards.html))

we required [PHP-CS-Fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) so that the code style
might be fixed.

To run it, in your terminal:

```shell
make php-cs-fixer
```

Or if you somehow are inside your app container:

```shell
php vendor/bin/php-cs-fixer fix
```

We also made sure that no-one would forget, as it is a required check for every pull request,
as defined in the GitHub action `.github/workflows/sonar.yaml`: 

```yaml
      - name: Code Style
        run: php vendor/bin/php-cs-fixer fix --dry-run
```

which will break if the code style rules have not been respected.

## Static analysis

PHP being an interpreted language, we generally won't have a direct clue until runtime if
we somehow made a mistake while declaring a variable, a function, or forgot a null check,
or mistyping a return.  
This can be a problem in certain cases (if we did not test everything thoroughly for instance),
because waiting for runtime to break is obviously dangerous.  
Some static analysis tools like [PHPStan](https://phpstan.org/) (which is also installed for
this project) will enable us to make some "compile-time" checks on the quality of our code.

To run it, in your terminal:

```shell
make phpstan
```

Or if you somehow are inside your app container:

```shell
php vendor/bin/phpstan analyse -l 9 src/
```

(`-l 9` corresponds to the highest level of analyse available)

It is also obviously a step in the same CI

## Sonar

[SonarCloud](https://sonarcloud.io/) is a set of tools designed to have a complete and visual
analysis of our current (main) and incoming (pull requests) code.

It can show a lot of things, namely security vulnerabilities, bugs, code smells, code
coverage percentage etc...

It is also configurable to work as a step in your actions, with defined quality gates to respect
(for instance, no less than X percent coverage), which we also did.