# Swango\SegmentTree

[![Php Version](https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![SegmentTree License](https://img.shields.io/hexpm/l/plug.svg?maxAge=2592000)](https://github.com/swlib/archer/blob/master/LICENSE)

Easy segment tree in PHP. Support multi key-value storage. Written without any global variable. Can be used in all kinds of environment.
### Common segment tree
```php
$tree = Swango\SegmentTree\Tree\Common::newTree(0,100000); // Create a tree with scale of 0~100000;

// Set to use "==" when comparing values. Two objects of different instances that have same content will be considered equal.
$tree->useDoubleEqualSign();
// Set to use "===" when comparing values. Two objects of different instances will be considered not equal no matter their content.
$tree->useTripleEqualSign();

// Accept all kinds of values including string, number, null, bool, array, object, etc. 
$tree->setValue(100, 1000, 'key1', 123);
$tree->setValue(20000, 20400, 'key1', null);
$tree->setValue(21000, 30000, 'key1', false);
$tree->setValue(20005, 21005, 'key1', [1, 2, 3]);
$tree->setValue(50000, 60000, 'key2', new \SplQueue());
$tree->setValue(99999, 100000, 'key3', 'some value');

// Get value of certain position.
var_dump($tree->getValue(20006));

// Delete value of between certain postions.
$tree->delValue(30000, 100000, 'key1');

// Thorws exception when value not found.
var_dump($tree->getValue(70000));

// Get segment arrays.
var_dump($tree->getSegmentsOfGivenKey('key1'));
var_dump($tree->getSegmentsOfGivenKeyAndValue('key1', [1, 2, 3]));

// Remove all redundant nodes in the tree to reduce memory cost.
$tree->optimize();

// Clear all values and child nodes and make it a new tree.
$tree->clear();
```
### Version compressed segment tree
```php
$tree = Swango\SegmentTree\Tree\Version::newTree(
    '1.0.0', '1.0.1', '1.0.2', '1.0.3', '1.1.0', '1.4.1', '2.0.0', '2.0.1-RC1', '3.0.0'
); // Create a tree with versions. These versions will be sorted using version_compare() and remove all duplicated

// All methos are similar with Common tree.
$tree->setValue('1.0.2', '2.0.0', 'key1', $true);
var_dump($tree->getValue('1.1.0'));
```
### Date compressed segment tree
(todo)

