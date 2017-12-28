# Example Of Either Monad Test
This results in nasty try catch blocks and many of if statements.

Either Monad shows how we can fail gracefully without breaking the execution chain and making the code more readable.


## Test Example How Array Map Can Be Used
If one of those files does not exist the operation fails gracefully.

```php
// $read :: String -> Either String String
$read = function ($file) {
    return is_file($file) ? Either\Right::of(file_get_contents($file)) : Either\Left::of(sprintf('File "%s" does not exists', $file));
};
// $concat :: (Either String String) (Either String String) (Either String String)
$concat = liftM2(function ($first, $second) {
    return $first . $second;
}, $read(__FILE__), $read('./this-file-does-not-exits'));
assert($concat instanceof Either\Left);
assert($concat->extract() === 'File "./this-file-does-not-exits" does not exists');
```
