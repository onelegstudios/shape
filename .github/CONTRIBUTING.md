# Contribution Guide

Thank you for considering contributing to Shape! Please review the following guidelines before submitting a pull request.

For significant changes, please open an issue first so we can discuss the approach.

## Process

1. Fork the project
2. Create a new branch
3. Code, test, commit, and push
4. Open a pull request detailing your changes, and label it (see [Labels](#labels))

## Guidelines

- Ensure the coding style passes by running `composer lint`.
- Send a coherent commit history, making sure each commit in your pull request is meaningful.
- You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.
- Please remember that we follow [SemVer](http://semver.org/).

## Labels

Release notes are generated from merged pull requests, sorted by label, so please apply one before asking for a review. An unlabelled pull request is filed under "Other Changes", where it is easily missed.

| Label | Use it when |
| --- | --- |
| `breaking` | A consumer has to react when upgrading: a renamed or removed prop, component, command, config key or publish tag; a changed default; a dropped PHP or Laravel version. |
| `enhancement` | New capability, or a new option on an existing one, that current call sites can ignore. |
| `bug` | Shipped behaviour was wrong and now is not. |
| `documentation` | `docs/`, `README.md`, docblocks, previews and examples, where nothing in `src/` or `resources/` behaves differently. |
| `dependencies` | A constraint change in `composer.json` or `package.json`. Dependabot applies this itself. |
| `maintenance` | CI, tests, tooling and refactors, where nothing is observable to someone who installed the package. |
| `skip-changelog` | The change should not appear in the release notes at all. |

A pull request is listed once, under the first category it matches, so a change that is both breaking and an enhancement takes both labels and appears under Breaking Changes.

Some of the calls are less obvious:

- A test or CI fix is `maintenance`, not `bug`. The suite was wrong; the package was not, and nobody on the released version was affected.
- A doc that described the package incorrectly is `documentation`, not `bug`. The code was already right.
- A refactor is `maintenance` even when it edits `resources/views`. What decides it is whether the rendered output moved, not whether component files did.
- `breaking` applies before 1.0 too. SemVer permits a minor to break while the package is on 0.x, but the label is the only thing separating a rename from an addition for somebody upgrading.

## Setup

Clone your fork, then install the dev dependencies:

```bash
composer install
```

## Lint

Lint your code:

```bash
composer lint
```

## Tests

Run all tests:

```bash
composer test
```
