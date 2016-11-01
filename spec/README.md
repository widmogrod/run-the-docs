# Specification (Draft) 
## Introduction

Lets consider an example file:

```php
use Widmogrod\Functional as f;
use Widmogrod\Monad\Either;

/**
 * In php world, the most popular way of saying that something went wrong is to throw an exception. 
 * This results in nasty try catch blocks and many of if statements. 
 *
 * Either Monad shows how we can fail gracefully without breaking the execution chain and making the code more readable.  
 */
class ExampleOfEitherMonadTest extends PHPUnit 
{
   /**
    * The following example demonstrates combining the contents of two files into one. 
    * If one of those files does not exist the operation fails gracefully.
    */
   public function test_example_how_array_map_can_be_used() 
   {
      $read = function($file) {
       return is_file($file)
         ? Either\Right::of(file_get_contents($file))
         : Either\Left::of(sprintf('File "%s" does not exists', $file));
      };
      
      $concat = f\liftM2(
          $read(__FILE__),
          $read('./this-file-does-not-exits'),
          function ($first, $second) {
              return $first . $second;
          }
      );

      assert($concat instanceof Either\Left);
      assert($concat->extract() === 'File "./this-file-does-not-exits" does not exists');
   }
}
```

After parsing this file by `runthedocs` result in form of `markdown` should look similart to this:
```markdown
# Example Of Either Monad
## Introduction
In php world, the most popular way of saying that something went wrong is to throw an exception. 
This results in nasty try catch blocks and many of if statements. 
 
Either Monad shows how we can fail gracefully without breaking the execution chain and making the code more readable. 

## Example how array map can be used
The following example demonstrates combining the contents of two files into one. 
If one of those files does not exist the operation fails gracefully.

\```php
$read = function($file) {
    return is_file($file)
      ? Either\Right::of(file_get_contents($file))
      : Either\Left::of(sprintf('File "%s" does not exists', $file));
};
$concat = f\liftM2(
    example_read(__FILE__),
    example_read('./this-file-does-not-exits'),
    function ($first, $second) {
      return $first . $second;
    }
);

assert($concat instanceof Either\Left);
assert($concat->extract() === 'File "./this-file-does-not-exits" does not exists');
\```

[Run example](link-somewhere-to-run-the-code)
```


And data structure for one example file should look like this:
```json
{
  "file": "example/ExampleOfEitherMonadTest.php",
  "title": "Example Of Either Monad",
  "description": "In php world, the most popular way of saying that something went wrong is to throw an exception. \nThis results in nasty try catch blocks and many of if statements. \n\nEither Monad shows how we can fail gracefully without breaking the execution chain and making the code more readable.",
  "examples": [
    {
      "id": "test_example_how_array_map_can_be_used",
      "title": "Example how array map can be used",
      "description": "The following example demonstrates combining the contents of two files into one. If one of those files does not exist the operation fails gracefully.",
      "code": "/*...*/"
    }  
  ]
}
```

## Configuration
How to configure `runthedocs`?

In your project root create file `.runthedocs.yml`
```yml
language: php
path: "./example/"

setup:
 - composer install
 
runner: "./vendor/bin/phpunit -c $path"
```
