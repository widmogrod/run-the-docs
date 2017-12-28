# Applicator Lift Test



## Test It Should Sum All From One List With Elements From Second


```php
$listA = Listt::of([1, 2]);
$listB = Listt::of([4, 5]);
// sum <*> [1, 2] <*> [4, 5]
$result = f\liftA2('example\\sum', $listA, $listB);
$this->assertInstanceOf(Listt::class, $result);
$this->assertEquals([5, 6, 6, 7], f\valueOf($result));
```


## Test It Should Sum All From One List With Single Element


```php
// sum <$> [1, 2] <*> [4, 5]
$sum = f\curryN(2, 'example\\sum');
$a = Listt::of([1, 2]);
$b = Listt::of([4, 5]);
$result = f\map($sum, $a)->ap($b);
$this->assertEquals(Listt::of([5, 6, 6, 7]), $result);
```
