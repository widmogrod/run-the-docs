# ExampleOfVarDumpTest
/**
 * This example set aims to teach you how `var` dump represents different values
 */


## test_var_dump
/**
     * Given example demonstrates how `var_dump` result will looks like.
     */

```php

        var_dump([1, 2, 3]);
    
```


## test_value_is_injected
/**
     * @dataProvider provideData
     */

```php

        var_dump($value);
    
```


## test_value_is_injected_second_time
/**
     * @dataProvider provideData
     */

```php

        var_dump($value);
    
```


## provideData
/**
     */

```php

        return [
            'random input' => [
                '$value' => mt_rand(),
            ],
        ];
    
```
