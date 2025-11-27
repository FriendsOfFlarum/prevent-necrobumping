# Tests

This directory contains automated tests for the FoF Prevent Necrobumping extension.

## Setup

Before running tests, you need to set up the test database:

```bash
composer test:setup
```

This command only needs to be run once, or when you need to reset the test database.

## Running Tests

Run all tests:
```bash
composer test
```

Run only unit tests:
```bash
composer test:unit
```

Run only integration tests:
```bash
composer test:integration
```

## Test Structure

- `unit/` - Unit tests for individual classes and methods
  - `UtilTest.php` - Tests for the Util class (tag-specific days calculation)

- `integration/` - Integration tests that test the full extension functionality
  - `api/PreventNecrobumpingTest.php` - Tests for the core necrobumping validation
  - `api/TagSpecificThresholdsTest.php` - Tests for tag-specific necrobumping thresholds (requires flarum/tags)

## Writing Tests

### Integration Tests

Integration tests extend `Flarum\Testing\integration\TestCase` and can:
- Set up database fixtures with `prepareDatabase()`
- Make API requests with `$this->send($this->request(...))`
- Configure settings with `$this->setting('key', 'value')`
- Enable extensions with `$this->extension('extension-id')`

Example:
```php
#[Test]
public function can_reply_to_active_discussion()
{
    $this->extension('fof-prevent-necrobumping');
    $this->setting('fof-prevent-necrobumping.days', 7);

    $this->prepareDatabase([
        Discussion::class => [
            ['id' => 1, 'title' => 'Test', /* ... */],
        ],
    ]);

    $response = $this->send(
        $this->request('POST', '/api/posts', [
            'authenticatedAs' => 2,
            'json' => [/* ... */],
        ])
    );

    $this->assertEquals(201, $response->getStatusCode());
}
```

### Unit Tests

Unit tests extend `PHPUnit\Framework\TestCase` and test individual classes in isolation using mocks.

Example:
```php
#[Test]
public function returns_correct_days()
{
    $settings = m::mock(SettingsRepositoryInterface::class);
    $settings->shouldReceive('get')->andReturn(7);

    $result = Util::getDays($settings, $discussion);

    $this->assertEquals(7, $result);
}
```

## CI/CD

Tests are automatically run on GitHub Actions for all pull requests and commits to main branches.
