# Upgrade to SilverStripe 6

## Dependencies

Update your `composer.json`:
- `silverstripe/framework`: `^5.0` → `^6.0`
- `silverstripe/admin`: `^2.0` → `^3.0`
- `sunnysideup/sswebpack_engine_only`: `5.x-dev` → `^5.0-dev`

## BuildTask Migration

⚠️ **Breaking Change**: Tasks now extend Symfony Console Command pattern instead of the old BuildTask pattern.

### Task Execution
- **Old**: `vendor/bin/sake dev/tasks/remove-orphaned-elements confirm=1`
- **New**: `vendor/bin/sake tasks:remove-orphaned-elements --confirm`

### Configuration Properties
Replace the following in your task class:

```php
// Old
private static $segment = 'task-name';
protected $title = 'Task title';
protected $description = 'Task description';
protected $enabled = true;

// New
protected static string $commandName = 'task-name';
protected string $title = 'Task title';
protected static string $description = 'Task description';
private static bool $is_enabled = true;
```

### Method Signature Changes

⚠️ **Replace `run()` method with `execute()`**:

```php
// Old
public function run($request)
{
    $confirm = $request->getVar('confirm');
    echo "Message";
    DB::alteration_message('Message', 'type');
}

// New
protected function execute(InputInterface $input, PolyOutput $output): int
{
    $confirm = $input->getOption('confirm');
    $output->writeln('Message');
    $output->writeln('<info>Message</info>');
    return Command::SUCCESS;
}
```

### Output Methods
- Replace `echo` with `$output->writeln()` or `$output->write()`
- Replace `DB::alteration_message()` with `$output->writeln()`
- Use `<info>` tags for success messages, `<error>` tags for error messages

### Required Imports
Add to your use statements:
```php
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
```

### Command Options
⚠️ **Implement `getOptions()` method** to define CLI options:

```php
public function getOptions(): array
{
    return [
        new InputOption('confirm', 'c', InputOption::VALUE_NONE, 'Description'),
    ];
}
```

### Return Values
- The `execute()` method must return an integer status code
- Return `Command::SUCCESS` (0) on success
- Return `Command::FAILURE` (1) on failure
