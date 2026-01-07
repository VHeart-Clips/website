## Testing

You can run `sail composer test` to run all tests required for CI/CD compliance.

For now, only the following tests are active:

| Command                     | Description                    | Status   |
|-----------------------------|--------------------------------|----------|
| composer test:unit          | Unit & Feature Tests (Pest)    | Active   |
| composer test:type-coverage | Type Coverage Test (Pest)      | Active   |
| composer test:types         | PHPStan Static Analysis        | Inactive |
| composer test:refactor      | Refactoring Checks (Rector)    | Inactive |
| composer test:lint          | Pint and NPM Formatting Checks | Inactive |

Some tests are inactive for now for CI checks because they will fail currently.

They will get added to the `composer test` command as soon as possible.

These tests will ensure the Quality and Stability of this project.