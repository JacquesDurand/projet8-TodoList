# How to contribute
First of all, thank you for taking the time to contribute to this project. We've tried to make a stable project and try to fix bugs and add new features continuously. You can help us do more.

## Getting started

As mentioned in [the Readme](README.md), you will have to clone the project first.

## Issues

There might be already documented issues [here](https://github.com/JacquesDurand/projet8-TodoList/issues).  
Some of them might be known bugs, planned or even requested features.  
If you feel you can take one on, do not hesitate !

## Git

The current git flow for this repository is as simple as it can be: 
- First create a new branch issued from main: `git checkout -b my-branch-name`
- Do your work
- Stage it `git add -A`
- Commit it following [Conventional Commit](https://www.conventionalcommits.org/)
- Push it `git push -u origin my-branch-name`
- Open a pull request on main on the repository as it will be suggested
- If you feel your work requires any explanation, do not hesitate to comment your pull request !

### Tests

To be accepted, your contribution will require to be tested, so do not forget to write unit/functional tests
for your feature or your bug fix !


| *Note*: |
|---------|

To run the test-suite from your terminal, just run
```shell
make phpunit
```

(Your containers will have to be up though)

### Code style

Do not forget to run the cs-fixer so that everyone ends up with evenly styled code:

```shell
make php-cs-fixer
```

### Documentation

Every chunk of code that may be hard to understand has some comments above it. If you feel your code
needs to be documented, go for it, but remember that well named functions, variables and classes should be 
enough documentation by themselves.