# Example Of Var Dump Test



## Test Var Dump


```php
var_dump([1, 2, 3]);
```


## Test Value Is Injected


```php
var_dump($value);
```


## Test Value Is Injected Second Time


```php
var_dump($value);
```


## Provide Data


```php
return ['random input' => ['$value' => mt_rand()]];
```
