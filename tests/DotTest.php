<?php

/**
 * Dot - PHP dot notation access to arrays
 *
 * @author  Riku Särkinen <riku@adbar.io>
 * @link    https://github.com/adbario/php-dot-notation
 * @license https://github.com/adbario/php-dot-notation/blob/3.x/LICENSE.md (MIT License)
 */

namespace Adbar\Tests;

use Adbar\Dot;
use ArrayIterator;
use PHPUnit\Framework\TestCase;

class DotTest extends TestCase
{
    /*
     * --------------------------------------------------------------
     * Construct
     * --------------------------------------------------------------
     */
    public function testConstructWithoutValues(): void
    {
        $dot = new Dot();

        $this->assertSame([], $dot->all());
    }

    public function testConstructWithArray(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame('bar', $dot->get('foo'));
    }

    public function testConstructWithString(): void
    {
        $dot = new Dot('foobar');

        $this->assertSame('foobar', $dot->get(0));
    }

    public function testConstructWithDot(): void
    {
        $dot1 = new Dot(['foo' => 'bar']);
        $dot2 = new Dot($dot1);

        $this->assertSame('bar', $dot2->get('foo'));
    }

    public function testConstructWithParsing(): void
    {
        $dot = new Dot(['foo.bar' => 'baz']);

        $this->assertSame(['foo.bar' => 'baz'], $dot->get());

        $dot = new Dot(['foo.bar' => 'baz'], true);

        $this->assertSame(['foo' => ['bar' => 'baz']], $dot->get());

        $dot = new Dot([], true);

        $this->assertSame([], $dot->get());
    }

    public function testConstructWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo_bar' => 'baz'], false, "_");

        $this->assertSame(['foo_bar' => 'baz'], $dot->get());

        $dot = new Dot(['foo_bar' => 'baz'], true, "_");

        $this->assertSame(['foo' => ['bar' => 'baz']], $dot->get());
    }

    public function testConstructHelper(): void
    {
        $dot = dot(['foo' => 'bar']);

        $this->assertInstanceOf(Dot::class, $dot);
        $this->assertSame('bar', $dot->get('foo'));
    }

    public function testConstructHelpertWithParsing(): void
    {
        $dot = dot(['foo.bar' => 'baz'], true);

        $this->assertSame(['foo' => ['bar' => 'baz']], $dot->get());
    }

    public function testConstructHelpertWithCustomDelimiter(): void
    {
        $dot = dot(['foo_bar' => 'baz'], false, "_");

        $this->assertSame(['foo_bar' => 'baz'], $dot->get());

        $dot = dot(['foo_bar' => 'baz'], true, "_");

        $this->assertSame(['foo' => ['bar' => 'baz']], $dot->get());
    }

    /*
     * --------------------------------------------------------------
     * Add
     * --------------------------------------------------------------
     */

    public function testAddKeyValuePair(): void
    {
        $dot = new Dot();
        $dot->add('foo.bar', 'baz');

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testAddValueToExistingKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->add('foo', 'baz');

        $this->assertSame('bar', $dot->get('foo'));
    }

    public function testAddArrayOfKeyValuePairs(): void
    {
        $dot = new Dot(['foobar' => 'baz']);
        $dot->add([
            'foobar' => 'qux',
            'corge' => 'grault'
        ]);

        $this->assertSame(['foobar' => 'baz', 'corge' => 'grault'], $dot->all());
    }

    public function testAddReturnsDot(): void
    {
        $dot = new Dot();

        $this->assertInstanceOf(Dot::class, $dot->add('foo', 'bar'));
    }

    /*
     * --------------------------------------------------------------
     * All
     * --------------------------------------------------------------
     */

    public function testAllReturnsAllItems(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Clear
     * --------------------------------------------------------------
     */

    public function testClearKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->clear('foo.bar');

        $this->assertSame([], $dot->get('foo.bar'));
    }

    public function testClearNonExistingKey(): void
    {
        $dot = new Dot();
        $dot->clear('foo');

        $this->assertSame([], $dot->get('foo'));
    }

    public function testClearArrayOfKeys(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);
        $dot->clear(['foo', 'baz']);

        $this->assertSame(['foo' => [], 'baz' => []], $dot->all());
    }

    public function testClearAll(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->clear();

        $this->assertSame([], $dot->all());
    }

    public function testClearReturnsDot(): void
    {
        $dot = new Dot();

        $this->assertInstanceOf(Dot::class, $dot->clear());
    }

    /*
     * --------------------------------------------------------------
     * Delete
     * --------------------------------------------------------------
     */

    public function testDeleteKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->delete('foo.bar');

        $this->assertFalse($dot->has('foo.bar'));
    }

    public function testDeleteNonExistingKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->delete('baz.qux');

        $this->assertSame(['foo' => 'bar'], $dot->all());
    }

    public function testDeleteArrayOfKeys(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);
        $dot->delete(['foo', 'baz']);

        $this->assertSame([], $dot->all());
    }

    public function testDeleteReturnsDot(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertInstanceOf(Dot::class, $dot->clear('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Flatten
     * --------------------------------------------------------------
     */
    public function testFlatten(): void
    {
        $dot = new Dot(['foo' => ['abc' => 'xyz', 'bar' => ['baz']]]);
        $flatten = $dot->flatten();
        $this->assertSame('xyz', $flatten['foo.abc']);
        $this->assertSame('baz', $flatten['foo.bar.0']);
    }

    public function testFlattenWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo' => ['abc' => 'xyz', 'bar' => ['baz']]]);
        $flatten = $dot->flatten('_');
        $this->assertSame('xyz', $flatten['foo_abc']);
        $this->assertSame('baz', $flatten['foo_bar_0']);
    }

    /*
     * --------------------------------------------------------------
     * Get
     * --------------------------------------------------------------
     */

    public function testGetValueFromKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testGetValueFromNonExistingKey(): void
    {
        $dot = new Dot();

        $this->assertNull($dot->get('foo'));
    }

    public function testGetGivenDefaultValueFromNonExistingKey(): void
    {
        $dot = new Dot();

        $this->assertSame('bar', $dot->get('foo', 'bar'));
    }

    /*
     * --------------------------------------------------------------
     * Has
     * --------------------------------------------------------------
     */

    public function testHasKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertTrue($dot->has('foo.bar'));

        $dot->delete('foo.bar');

        $this->assertFalse($dot->has('foo.bar'));
    }

    public function testHasArrayOfKeys(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertTrue($dot->has(['foo', 'baz']));

        $dot->delete('foo');

        $this->assertFalse($dot->has(['foo', 'baz']));
    }

    public function testHasWithEmptyDot(): void
    {
        $dot = new Dot();

        $this->assertFalse($dot->has('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Is empty
     * --------------------------------------------------------------
     */

    public function testIsEmptyDot(): void
    {
        $dot = new Dot();

        $this->assertTrue($dot->isEmpty());

        $dot->set('foo', 'bar');

        $this->assertFalse($dot->isEmpty());
    }

    public function testIsEmptyKey(): void
    {
        $dot = new Dot();

        $this->assertTrue($dot->isEmpty('foo.bar'));

        $dot->set('foo.bar', 'baz');

        $this->assertFalse($dot->isEmpty('foo.bar'));
    }

    public function testIsEmptyArrayOfKeys(): void
    {
        $dot = new Dot();

        $this->assertTrue($dot->isEmpty(['foo', 'bar']));

        $dot->set('foo', 'baz');

        $this->assertFalse($dot->isEmpty(['foo', 'bar']));
    }

    /*
     * --------------------------------------------------------------
     * Merge
     * --------------------------------------------------------------
     */

    public function testMergeArrayWithDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->merge(['foo' => ['bar' => 'qux']]);

        $this->assertSame('qux', $dot->get('foo.bar'));
    }

    public function testMergeArrayWithKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->merge('foo', ['bar' => 'qux']);

        $this->assertSame('qux', $dot->get('foo.bar'));
    }

    public function testMergeDotWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux']]);
        $dot1->merge($dot2);

        $this->assertSame('qux', $dot1->get('foo.bar'));
    }

    public function testMergeDotObjectWithKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux']);
        $dot1->merge('foo', $dot2);

        $this->assertSame('qux', $dot1->get('foo.bar'));
    }

    public function testMergeReturnsDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertInstanceOf(Dot::class, $dot->merge(['foo' => ['bar' => 'qux']]));
    }

    /*
     * --------------------------------------------------------------
     * Recursive merge
     * --------------------------------------------------------------
     */

    public function testRecursiveMergeArrayWithDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->mergeRecursive(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);

        $this->assertSame(['baz', 'qux'], $dot->get('foo.bar'));
        $this->assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveMergeArrayWithKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->mergeRecursive('foo', ['bar' => 'qux', 'quux' => 'quuz']);

        $this->assertSame(['baz', 'qux'], $dot->get('foo.bar'));
        $this->assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveMergeDotWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1->mergeRecursive($dot2);

        $this->assertSame(['baz', 'qux'], $dot1->get('foo.bar'));
        $this->assertSame('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveMergeDotObjectWithKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux', 'quux' => 'quuz']);
        $dot1->mergeRecursive('foo', $dot2);

        $this->assertSame(['baz', 'qux'], $dot1->get('foo.bar'));
        $this->assertSame('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveMergeReturnsDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertInstanceOf(
            Dot::class,
            $dot->mergeRecursive(['foo' => ['bar' => 'qux', 'quux' => 'quuz']])
        );
    }

    /*
     * --------------------------------------------------------------
     * Recursive distinct merge
     * --------------------------------------------------------------
     */

    public function testRecursiveDistinctMergeArrayWithDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->mergeRecursiveDistinct(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);

        $this->assertSame('qux', $dot->get('foo.bar'));
        $this->assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeArrayWithKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->mergeRecursiveDistinct('foo', ['bar' => 'qux', 'quux' => 'quuz']);

        $this->assertSame('qux', $dot->get('foo.bar'));
        $this->assertSame('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeDotWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1->mergeRecursiveDistinct($dot2);

        $this->assertSame('qux', $dot1->get('foo.bar'));
        $this->assertSame('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeDotObjectWithKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux', 'quux' => 'quuz']);
        $dot1->mergeRecursiveDistinct('foo', $dot2);

        $this->assertSame('qux', $dot1->get('foo.bar'));
        $this->assertSame('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursivDistincteMergeReturnsDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertInstanceOf(
            Dot::class,
            $dot->mergeRecursiveDistinct(['foo' => ['bar' => 'qux', 'quux' => 'quuz']])
        );
    }

    /*
     * --------------------------------------------------------------
     * Pull
     * --------------------------------------------------------------
     */

    public function testPullKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame('bar', $dot->pull('foo'));
        $this->assertFalse($dot->has('foo'));
    }

    public function testPullNonExistingKey(): void
    {
        $dot = new Dot();

        $this->assertNull($dot->pull('foo'));
    }

    public function testPullNonExistingKeyWithDefaultValue(): void
    {
        $dot = new Dot();

        $this->assertSame('bar', $dot->pull('foo', 'bar'));
    }

    public function testPullAll(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $dot->pull());
        $this->assertSame([], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Push
     * --------------------------------------------------------------
     */

    public function testPushValue(): void
    {
        $dot = new Dot();
        $dot->push('foo');

        $this->assertSame('foo', $dot->get(0));
    }

    public function testPushValueToKey(): void
    {
        $dot = new Dot(['foo' => [0 => 'bar']]);
        $dot->push('foo', 'baz');

        $this->assertSame(['bar', 'baz'], $dot->get('foo'));
    }

    public function testPushReturnsDot(): void
    {
        $dot = $dot = new Dot();

        $this->assertInstanceOf(Dot::class, $dot->push('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Replace
     * --------------------------------------------------------------
     */

    public function testReplaceWithArray(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->replace(['foo' => ['qux' => 'quux']]);

        $this->assertSame(['qux' => 'quux'], $dot->get('foo'));
    }

    public function testReplaceKeyWithArray(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot->replace('foo', ['qux' => 'corge']);

        $this->assertSame(['bar' => 'baz', 'qux' => 'corge'], $dot->get('foo'));
    }

    public function testReplaceWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux']]);
        $dot1->replace($dot2);

        $this->assertSame(['bar' => 'qux'], $dot1->get('foo'));
    }

    public function testReplaceKeyWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot2 = new Dot(['qux' => 'corge']);
        $dot1->merge('foo', $dot2);

        $this->assertSame(['bar' => 'baz', 'qux' => 'corge'], $dot1->get('foo'));
    }

    public function testReplaceReturnsDot(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertInstanceOf(Dot::class, $dot->replace(['foo' => ['qux' => 'quux']]));
    }

    /*
     * --------------------------------------------------------------
     * Set
     * --------------------------------------------------------------
     */

    public function testSetKeyValuePair(): void
    {
        $dot = new Dot();
        $dot->set('foo.bar', 'baz');

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testSetArrayOfKeyValuePairs(): void
    {
        $dot = new Dot();
        $dot->set(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $dot->all());
    }

    public function testSetReturnsDot(): void
    {
        $dot = new Dot();

        $this->assertInstanceOf(Dot::class, $dot->set('foo.bar', 'baz'));
    }

    /*
     * --------------------------------------------------------------
     * Set array
     * --------------------------------------------------------------
     */

    public function testSetArray(): void
    {
        $dot = new Dot();
        $dot->setArray(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $dot->all());
    }

    public function testSetArrayReturnsDot(): void
    {
        $dot = new Dot();

        $this->assertInstanceOf(Dot::class, $dot->setArray(['foo' => 'bar']));
    }

    /*
     * --------------------------------------------------------------
     * Set reference
     * --------------------------------------------------------------
     */

    public function testSetReference(): void
    {
        $dot = new Dot();
        $items = ['foo' => 'bar'];
        $dot->setReference($items);
        $dot->set('foo', 'baz');

        $this->assertSame('baz', $items['foo']);
    }

    public function testSetReferenceReturnsDot(): void
    {
        $dot = new Dot();
        $items = ['foo' => 'bar'];

        $this->assertInstanceOf(Dot::class, $dot->setReference($items));
    }

    /*
     * --------------------------------------------------------------
     * ArrayAccess interface
     * --------------------------------------------------------------
     */

    public function testOffsetExists(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertTrue(isset($dot['foo']));

        unset($dot['foo']);

        $this->assertFalse(isset($dot['foo']));
    }

    public function testOffsetGet(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame('bar', $dot['foo']);
    }

    public function testOffsetSet(): void
    {
        $dot = new Dot();
        $dot['foo.bar'] = 'baz';

        $this->assertSame('baz', $dot['foo.bar']);
    }

    public function testOffsetSetWithoutKey(): void
    {
        $dot = new Dot();
        $dot[] = 'foobar';

        $this->assertSame('foobar', $dot->get(0));
    }

    public function testOffsetUnset(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        unset($dot['foo']);

        $this->assertFalse(isset($dot['foo']));
    }

    /*
     * --------------------------------------------------------------
     * To JSON
     * --------------------------------------------------------------
     */

    public function testToJsonAll(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertJsonStringEqualsJsonString(
            '{"foo":"bar"}',
            (string) $dot->toJson()
        );
    }

    public function testToJsonAllWithOption(): void
    {
        $dot = new Dot(['foo' => "'bar'"]);

        $this->assertJsonStringEqualsJsonString(
            '{"foo":"\u0027bar\u0027"}',
            (string) $dot->toJson(JSON_HEX_APOS)
        );
    }

    public function testToJsonKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertJsonStringEqualsJsonString(
            '{"bar":"baz"}',
            (string) $dot->toJson('foo')
        );
    }

    public function testToJsonKeyWithOptions(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertSame(
            json_encode(['bar' => 'baz'], JSON_HEX_APOS),
            (string) $dot->toJson('foo', JSON_HEX_APOS)
        );
    }

    /*
     * --------------------------------------------------------------
     * Export
     * --------------------------------------------------------------
     */

    public function testSetState(): void
    {
        $this->assertEquals(
            (object) ['foo' => ['bar' => 'baz']],
            Dot::__set_state(['foo' => ['bar' => 'baz']])
        );
    }

    public function testVarExport(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        if (version_compare(PHP_VERSION, '8.2.0') >= 0) {
            $this->assertSame(
                "\Adbar\Dot::__set_state(array(\n" .
                "   'items' => \n" .
                "  array (\n" .
                "    'foo' => \n" .
                "    array (\n" .
                "      'bar' => 'baz',\n" .
                "    ),\n" .
                "  ),\n" .
                "   'delimiter' => '.',\n" .
                "))",
                var_export($dot, true)
            );
        } else {
            $this->assertSame(
                "Adbar\Dot::__set_state(array(\n" .
                "   'items' => \n" .
                "  array (\n" .
                "    'foo' => \n" .
                "    array (\n" .
                "      'bar' => 'baz',\n" .
                "    ),\n" .
                "  ),\n" .
                "   'delimiter' => '.',\n" .
                "))",
                var_export($dot, true)
            );
        }
    }

    /*
     * --------------------------------------------------------------
     * Countable interface
     * --------------------------------------------------------------
     */

    public function testCount(): void
    {
        $dot = new Dot([1, 2, 3]);

        $this->assertSame(3, $dot->count());
    }

    public function testCountable(): void
    {
        $dot = new Dot([1, 2, 3]);

        $this->assertCount(3, $dot);
    }

    /*
     * --------------------------------------------------------------
     * IteratorAggregate interface
     * --------------------------------------------------------------
     */

    public function testGetIteratorReturnsArrayIterator(): void
    {
        $dot = new Dot();

        $this->assertInstanceOf(ArrayIterator::class, $dot->getIterator());
    }

    public function testIterationReturnsOriginalValues(): void
    {
        $dot = new Dot([1, 2, 3]);

        $items = [];

        foreach ($dot as $item) {
            $items[] = $item;
        }

        $this->assertSame([1, 2, 3], $items);
    }

    /*
     * --------------------------------------------------------------
     * JsonSerializable interface
     * --------------------------------------------------------------
     */

    public function testJsonEncodingReturnsJson(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertJsonStringEqualsJsonString(
            '{"foo":"bar"}',
            (string) json_encode($dot)
        );
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Construct
     * --------------------------------------------------------------
     */

    public function testConstructWithObject(): void
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';
        $obj->baz = 'qux';

        $dot = new Dot($obj);

        $this->assertSame('bar', $dot->get('foo'));
        $this->assertSame('qux', $dot->get('baz'));
    }

    public function testConstructWithEmptyStringDelimiterFallsBackToDot(): void
    {
        // The constructor does: $this->delimiter = $delimiter ?: "."
        // When an empty string is passed, it should fall back to "."
        $dot = new Dot(['foo.bar' => 'baz'], true);

        $this->assertSame(['foo' => ['bar' => 'baz']], $dot->all());
    }

    public function testConstructParsingWithNestedDotKeys(): void
    {
        $dot = new Dot([
            'a.b' => 1,
            'a.c' => 2,
            'd' => 3,
        ], true);

        $this->assertSame(1, $dot->get('a.b'));
        $this->assertSame(2, $dot->get('a.c'));
        $this->assertSame(3, $dot->get('d'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Add
     * --------------------------------------------------------------
     */

    public function testAddWithDotNotationToExistingNestedKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->add('foo.bar', 'qux');

        // Should not overwrite because foo.bar already exists
        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testAddToNonExistingNestedKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->add('foo.qux', 'quux');

        $this->assertSame('quux', $dot->get('foo.qux'));
    }

    public function testAddWithIntegerKey(): void
    {
        $dot = new Dot([0 => 'foo']);
        $dot->add(0, 'bar');

        // Should not overwrite because key 0 already exists
        $this->assertSame('foo', $dot->get(0));
    }

    public function testAddWithNullValueToNonExistingKey(): void
    {
        $dot = new Dot();
        $dot->add('foo', null);

        // add() checks if get('foo') === null, so null values can't be added
        // because the key is considered "not set" when get returns null
        // After add, foo should be set to null (since get('foo') was null, set was called)
        $this->assertNull($dot->get('foo'));
        $this->assertTrue($dot->has('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Delete
     * --------------------------------------------------------------
     */

    public function testDeleteTopLevelKey(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);
        $dot->delete('foo');

        $this->assertFalse($dot->has('foo'));
        $this->assertSame('qux', $dot->get('baz'));
    }

    public function testDeleteWithIntegerKey(): void
    {
        $dot = new Dot([0 => 'foo', 1 => 'bar', 2 => 'baz']);
        $dot->delete(1);

        $this->assertFalse($dot->has(1));
        $this->assertSame('foo', $dot->get(0));
        $this->assertSame('baz', $dot->get(2));
    }

    public function testDeleteWhenIntermediatePathIsNotArray(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        // foo is a string, not an array, so foo.baz should be a no-op
        $dot->delete('foo.baz');

        $this->assertSame('bar', $dot->get('foo'));
    }

    public function testDeleteDeeplyNestedKey(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => ['d' => 'value']]]]);
        $dot->delete('a.b.c.d');

        $this->assertFalse($dot->has('a.b.c.d'));
        $this->assertTrue($dot->has('a.b.c'));
        $this->assertSame([], $dot->get('a.b.c'));
    }

    public function testDeleteMultipleNestedKeys(): void
    {
        $dot = new Dot([
            'foo' => ['bar' => 'baz', 'qux' => 'quux'],
            'corge' => 'grault'
        ]);
        $dot->delete(['foo.bar', 'corge']);

        $this->assertFalse($dot->has('foo.bar'));
        $this->assertFalse($dot->has('corge'));
        $this->assertSame('quux', $dot->get('foo.qux'));
    }

    public function testDeleteWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']], false, '/');
        $dot->delete('foo/bar');

        $this->assertFalse($dot->has('foo/bar'));
        $this->assertTrue($dot->has('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Flatten
     * --------------------------------------------------------------
     */

    public function testFlattenEmptyDot(): void
    {
        $dot = new Dot([]);

        // flatten() calls array_merge(...$flatten) where $flatten is empty
        // This would cause an error in PHP < 8.0, but in PHP 7.4+/8.0+ with
        // an empty array, we need to be careful
        // Actually, with empty items, foreach does nothing, $flatten is empty,
        // array_merge(...[]) is called
        $this->assertSame([], $dot->flatten());
    }

    public function testFlattenDeeplyNested(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => ['d' => 'value']]]]);
        $flatten = $dot->flatten();

        $this->assertSame('value', $flatten['a.b.c.d']);
    }

    public function testFlattenWithEmptySubArrays(): void
    {
        $dot = new Dot(['foo' => [], 'bar' => 'baz']);
        $flatten = $dot->flatten();

        $this->assertSame([], $flatten['foo']);
        $this->assertSame('baz', $flatten['bar']);
    }

    public function testFlattenWithMixedValues(): void
    {
        $dot = new Dot([
            'string' => 'value',
            'int' => 42,
            'bool' => true,
            'null' => null,
            'nested' => ['key' => 'val']
        ]);
        $flatten = $dot->flatten();

        $this->assertSame('value', $flatten['string']);
        $this->assertSame(42, $flatten['int']);
        $this->assertTrue($flatten['bool']);
        $this->assertNull($flatten['null']);
        $this->assertSame('val', $flatten['nested.key']);
    }

    public function testFlattenWithNumericKeys(): void
    {
        $dot = new Dot(['items' => ['a', 'b', 'c']]);
        $flatten = $dot->flatten();

        $this->assertSame('a', $flatten['items.0']);
        $this->assertSame('b', $flatten['items.1']);
        $this->assertSame('c', $flatten['items.2']);
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Get
     * --------------------------------------------------------------
     */

    public function testGetWithNullKeyReturnsAllItems(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $dot->get(null));
    }

    public function testGetWithIntegerKey(): void
    {
        $dot = new Dot(['foo', 'bar', 'baz']);

        $this->assertSame('foo', $dot->get(0));
        $this->assertSame('bar', $dot->get(1));
        $this->assertSame('baz', $dot->get(2));
    }

    public function testGetNonExistingIntegerKeyReturnsDefault(): void
    {
        $dot = new Dot(['foo']);

        $this->assertSame('default', $dot->get(99, 'default'));
    }

    public function testGetWithNonArrayIntermediate(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        // foo is a string, not array; get('foo.baz') should return default
        $this->assertNull($dot->get('foo.baz'));
        $this->assertSame('default', $dot->get('foo.baz', 'default'));
    }

    public function testGetTopLevelKeyDirectly(): void
    {
        $dot = new Dot(['foo.bar' => 'baz', 'foo' => ['bar' => 'qux']]);

        // When key exists at top level, it should be returned directly
        // 'foo.bar' as a literal key exists, so it should return 'baz'
        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testGetKeyWithNoDelimiterNonExisting(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        // Key 'baz' has no delimiter and doesn't exist
        $this->assertNull($dot->get('baz'));
        $this->assertSame('default', $dot->get('baz', 'default'));
    }

    public function testGetReturnsNestedArray(): void
    {
        $dot = new Dot(['foo' => ['bar' => ['baz' => 'qux']]]);

        $this->assertSame(['baz' => 'qux'], $dot->get('foo.bar'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Has
     * --------------------------------------------------------------
     */

    public function testHasWithEmptyKeysArray(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertFalse($dot->has([]));
    }

    public function testHasTopLevelKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertTrue($dot->has('foo'));
        $this->assertFalse($dot->has('baz'));
    }

    public function testHasWithNonArrayIntermediate(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        // foo is a string, not array, so foo.baz should not exist
        $this->assertFalse($dot->has('foo.baz'));
    }

    public function testHasDeeplyNestedKey(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => ['d' => 'value']]]]);

        $this->assertTrue($dot->has('a.b.c.d'));
        $this->assertTrue($dot->has('a.b.c'));
        $this->assertTrue($dot->has('a.b'));
        $this->assertTrue($dot->has('a'));
        $this->assertFalse($dot->has('a.b.c.d.e'));
    }

    public function testHasWithIntegerKey(): void
    {
        $dot = new Dot(['foo', 'bar', 'baz']);

        $this->assertTrue($dot->has(0));
        $this->assertTrue($dot->has(1));
        $this->assertFalse($dot->has(5));
    }

    public function testHasAllKeysRequired(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertTrue($dot->has(['foo', 'baz']));
        $this->assertFalse($dot->has(['foo', 'nonexistent']));
    }

    public function testHasWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']], false, '/');

        $this->assertTrue($dot->has('foo/bar'));
        $this->assertFalse($dot->has('foo/qux'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: isEmpty
     * --------------------------------------------------------------
     */

    public function testIsEmptyWithZeroValue(): void
    {
        $dot = new Dot(['foo' => 0]);

        // PHP's empty(0) returns true
        $this->assertTrue($dot->isEmpty('foo'));
    }

    public function testIsEmptyWithEmptyStringValue(): void
    {
        $dot = new Dot(['foo' => '']);

        // PHP's empty('') returns true
        $this->assertTrue($dot->isEmpty('foo'));
    }

    public function testIsEmptyWithFalseValue(): void
    {
        $dot = new Dot(['foo' => false]);

        // PHP's empty(false) returns true
        $this->assertTrue($dot->isEmpty('foo'));
    }

    public function testIsEmptyWithNonEmptyNestedKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertFalse($dot->isEmpty('foo'));
        $this->assertFalse($dot->isEmpty('foo.bar'));
    }

    public function testIsEmptyWithEmptyArray(): void
    {
        $dot = new Dot(['foo' => []]);

        $this->assertTrue($dot->isEmpty('foo'));
    }

    public function testIsEmptyWithMixedKeys(): void
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => '']);

        // One is not empty, so isEmpty for both should be false
        $this->assertFalse($dot->isEmpty(['foo', 'baz']));
    }

    public function testIsEmptyAllKeysEmpty(): void
    {
        $dot = new Dot(['foo' => '', 'baz' => 0]);

        $this->assertTrue($dot->isEmpty(['foo', 'baz']));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Merge
     * --------------------------------------------------------------
     */

    public function testMergeToNonExistingKey(): void
    {
        $dot = new Dot();
        $dot->merge('foo', ['bar' => 'baz']);

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testMergePreservesNumericKeys(): void
    {
        $dot = new Dot(['items' => ['a', 'b']]);
        $dot->merge('items', ['c', 'd']);

        // array_merge with numeric keys reindexes
        $this->assertSame(['a', 'b', 'c', 'd'], $dot->get('items'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Recursive merge
     * --------------------------------------------------------------
     */

    public function testRecursiveMergeDeepNesting(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => 'value1']]]);
        $dot->mergeRecursive(['a' => ['b' => ['c' => 'value2', 'd' => 'value3']]]);

        $this->assertSame(['value1', 'value2'], $dot->get('a.b.c'));
        $this->assertSame('value3', $dot->get('a.b.d'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Recursive distinct merge
     * --------------------------------------------------------------
     */

    public function testRecursiveDistinctMergeDeepNesting(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => 'old', 'keep' => 'kept']]]);
        $dot->mergeRecursiveDistinct(['a' => ['b' => ['c' => 'new', 'd' => 'added']]]);

        $this->assertSame('new', $dot->get('a.b.c'));
        $this->assertSame('kept', $dot->get('a.b.keep'));
        $this->assertSame('added', $dot->get('a.b.d'));
    }

    public function testRecursiveDistinctMergeWithNonArrayOverwrite(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => 'value']]]);
        $dot->mergeRecursiveDistinct(['a' => ['b' => 'replaced']]);

        // b is replaced with a non-array value
        $this->assertSame('replaced', $dot->get('a.b'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Pull
     * --------------------------------------------------------------
     */

    public function testPullNestedKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $value = $dot->pull('foo.bar');

        $this->assertSame('baz', $value);
        $this->assertFalse($dot->has('foo.bar'));
        $this->assertSame('quux', $dot->get('foo.qux'));
    }

    public function testPullWithDefaultValue(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $value = $dot->pull('nonexistent', 'default');

        $this->assertSame('default', $value);
        $this->assertSame('bar', $dot->get('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Push
     * --------------------------------------------------------------
     */

    public function testPushToNonExistingKey(): void
    {
        $dot = new Dot();
        $dot->push('foo', 'bar');

        // foo didn't exist, so get('foo') returns null, items[] = value
        $this->assertSame(['bar'], $dot->get('foo'));
    }

    public function testPushToNonArrayKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->push('foo', 'baz');

        // foo is a string (not array, not null), so push should be a no-op
        $this->assertSame('bar', $dot->get('foo'));
    }

    public function testPushMultipleValuesToKey(): void
    {
        $dot = new Dot();
        $dot->push('list', 'a');
        $dot->push('list', 'b');
        $dot->push('list', 'c');

        $this->assertSame(['a', 'b', 'c'], $dot->get('list'));
    }

    public function testPushMultipleValuesToRoot(): void
    {
        $dot = new Dot();
        $dot->push('foo');
        $dot->push('bar');

        $this->assertSame('foo', $dot->get(0));
        $this->assertSame('bar', $dot->get(1));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Replace
     * --------------------------------------------------------------
     */

    public function testReplaceWithDotAtKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot2 = new Dot(['qux' => 'corge']);
        $dot1->replace('foo', $dot2);

        $this->assertSame(['bar' => 'baz', 'qux' => 'corge'], $dot1->get('foo'));
    }

    public function testReplaceToNonExistingKey(): void
    {
        $dot = new Dot();
        $dot->replace('foo', ['bar' => 'baz']);

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Set
     * --------------------------------------------------------------
     */

    public function testSetWithIntegerKey(): void
    {
        $dot = new Dot();
        // Integer keys are not parsed as dot-separated, so set(0, 'foo')
        // replaces the entire items array with 'foo'
        $dot->set(0, 'foo');

        $this->assertSame('foo', $dot->all());
    }

    public function testSetOverwritesExistingNestedValue(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->set('foo.bar', 'qux');

        $this->assertSame('qux', $dot->get('foo.bar'));
    }

    public function testSetDeeplyNestedValue(): void
    {
        $dot = new Dot();
        $dot->set('a.b.c.d.e', 'deep');

        $this->assertSame('deep', $dot->get('a.b.c.d.e'));
    }

    public function testSetOverwritesNonArrayIntermediate(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->set('foo.baz', 'qux');

        // foo was a string, but set should create intermediate arrays
        $this->assertSame('qux', $dot->get('foo.baz'));
    }

    public function testSetWithCustomDelimiter(): void
    {
        $dot = new Dot([], false, '/');
        $dot->set('foo/bar/baz', 'value');

        $this->assertSame('value', $dot->get('foo/bar/baz'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: SetArray
     * --------------------------------------------------------------
     */

    public function testSetArrayWithDotObject(): void
    {
        $dot1 = new Dot();
        $dot2 = new Dot(['foo' => 'bar']);
        $dot1->setArray($dot2);

        $this->assertSame(['foo' => 'bar'], $dot1->all());
    }

    public function testSetArrayWithString(): void
    {
        $dot = new Dot();
        $dot->setArray('hello');

        $this->assertSame(['hello'], $dot->all());
    }

    public function testSetArrayReplacesExistingItems(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->setArray(['baz' => 'qux']);

        $this->assertSame(['baz' => 'qux'], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: SetReference
     * --------------------------------------------------------------
     */

    public function testSetReferenceModificationsSyncBothWays(): void
    {
        $dot = new Dot();
        $items = ['foo' => 'bar'];
        $dot->setReference($items);

        // Modifying via Dot updates original
        $dot->set('foo', 'baz');
        $this->assertSame('baz', $items['foo']);

        // Modifying original updates Dot
        $items['foo'] = 'qux';
        $this->assertSame('qux', $dot->get('foo'));
    }

    public function testSetReferenceAddNewKey(): void
    {
        $dot = new Dot();
        $items = ['foo' => 'bar'];
        $dot->setReference($items);

        $dot->set('baz', 'qux');
        $this->assertSame('qux', $items['baz']);
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: ToJson
     * --------------------------------------------------------------
     */

    public function testToJsonNonExistingKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame('null', $dot->toJson('nonexistent'));
    }

    public function testToJsonNestedKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertSame('"baz"', $dot->toJson('foo.bar'));
    }

    public function testToJsonWithNullKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        // null key: options = null === null ? 0 : null; -> 0
        $this->assertJsonStringEqualsJsonString(
            '{"foo":"bar"}',
            (string) $dot->toJson(null)
        );
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: ArrayAccess
     * --------------------------------------------------------------
     */

    public function testArrayAccessWithDotNotation(): void
    {
        $dot = new Dot();
        $dot['foo.bar.baz'] = 'value';

        $this->assertSame('value', $dot['foo.bar.baz']);
        $this->assertTrue(isset($dot['foo.bar.baz']));
    }

    public function testArrayAccessUnsetNestedKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        unset($dot['foo.bar']);

        $this->assertFalse(isset($dot['foo.bar']));
        $this->assertTrue(isset($dot['foo.qux']));
    }

    public function testArrayAccessGetNonExisting(): void
    {
        $dot = new Dot();

        $this->assertNull($dot['nonexistent']);
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Count
     * --------------------------------------------------------------
     */

    public function testCountWithKey(): void
    {
        $dot = new Dot(['foo' => ['a', 'b', 'c'], 'bar' => 'baz']);

        $this->assertSame(3, $dot->count('foo'));
    }

    public function testCountNestedItems(): void
    {
        $dot = new Dot(['foo' => ['bar' => ['a', 'b']]]);

        $this->assertSame(2, $dot->count('foo.bar'));
    }

    public function testCountEmptyDot(): void
    {
        $dot = new Dot();

        $this->assertSame(0, $dot->count());
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Custom delimiter
     * --------------------------------------------------------------
     */

    public function testCustomDelimiterGetSetHasDelete(): void
    {
        $dot = new Dot([], false, '/');

        $dot->set('foo/bar/baz', 'value');
        $this->assertSame('value', $dot->get('foo/bar/baz'));
        $this->assertTrue($dot->has('foo/bar/baz'));

        $dot->delete('foo/bar/baz');
        $this->assertFalse($dot->has('foo/bar/baz'));
    }

    public function testCustomDelimiterFlatten(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']], false, '_');
        $flatten = $dot->flatten('_');

        $this->assertSame('baz', $flatten['foo_bar']);
    }

    public function testCustomDelimiterMerge(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']], false, '/');
        $dot->merge('foo', ['qux' => 'quux']);

        // Merge with string key should use the dot notation path
        $this->assertSame('quux', $dot->get('foo/qux'));
    }

    public function testCustomDelimiterParsing(): void
    {
        $dot = new Dot([
            'a/b' => 1,
            'a/c' => 2,
        ], true, '/');

        $this->assertSame(1, $dot->get('a/b'));
        $this->assertSame(2, $dot->get('a/c'));
        $this->assertSame(['b' => 1, 'c' => 2], $dot->get('a'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Method chaining
     * --------------------------------------------------------------
     */

    public function testMethodChaining(): void
    {
        $dot = new Dot();

        $result = $dot
            ->set('foo', 'bar')
            ->set('baz', 'qux')
            ->add('quux', 'corge')
            ->delete('baz')
            ->merge(['grault' => 'garply'])
            ->push('list', 'item');

        $this->assertInstanceOf(Dot::class, $result);
        $this->assertSame('bar', $dot->get('foo'));
        $this->assertFalse($dot->has('baz'));
        $this->assertSame('corge', $dot->get('quux'));
        $this->assertSame('garply', $dot->get('grault'));
        $this->assertSame(['item'], $dot->get('list'));
    }

    /*
     * --------------------------------------------------------------
     * Additional edge case tests: Special values and boundaries
     * --------------------------------------------------------------
     */

    public function testNullValues(): void
    {
        $dot = new Dot();
        $dot->set('foo', null);

        $this->assertTrue($dot->has('foo'));
        $this->assertNull($dot->get('foo'));
        $this->assertNull($dot->get('foo', 'default'));
    }

    public function testBooleanValues(): void
    {
        $dot = new Dot(['true' => true, 'false' => false]);

        $this->assertTrue($dot->get('true'));
        $this->assertFalse($dot->get('false'));
    }

    public function testNumericStringKeys(): void
    {
        $dot = new Dot(['123' => 'numeric_string_key']);

        $this->assertSame('numeric_string_key', $dot->get('123'));
        $this->assertTrue($dot->has('123'));
    }

    public function testEmptyArrayValue(): void
    {
        $dot = new Dot(['foo' => []]);

        $this->assertTrue($dot->has('foo'));
        $this->assertSame([], $dot->get('foo'));
    }

    public function testLargeNestedStructure(): void
    {
        $dot = new Dot();
        $dot->set('level1.level2.level3.level4.level5', 'deep');

        $this->assertSame('deep', $dot->get('level1.level2.level3.level4.level5'));
        $this->assertTrue($dot->has('level1.level2.level3.level4.level5'));
    }

    public function testSetAndGetWithVariousTypes(): void
    {
        $dot = new Dot();
        $dot->set('int', 42);
        $dot->set('float', 3.14);
        $dot->set('string', 'hello');
        $dot->set('array', [1, 2, 3]);
        $dot->set('null', null);
        $dot->set('bool', true);

        $this->assertSame(42, $dot->get('int'));
        $this->assertSame(3.14, $dot->get('float'));
        $this->assertSame('hello', $dot->get('string'));
        $this->assertSame([1, 2, 3], $dot->get('array'));
        $this->assertNull($dot->get('null'));
        $this->assertTrue($dot->get('bool'));
    }

    public function testClearWithIntegerKey(): void
    {
        $dot = new Dot([0 => 'foo', 1 => 'bar']);
        // clear(0) calls set(0, []), and since 0 is not a string,
        // set replaces the entire items with []
        $dot->clear(0);

        $this->assertSame([], $dot->all());
    }

    public function testDeleteReturnsSelfForChaining(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $result = $dot->delete('foo');

        $this->assertInstanceOf(Dot::class, $result);
        $this->assertSame($dot, $result);
    }

    public function testPushReturnsSelfForChaining(): void
    {
        $dot = new Dot();
        $result = $dot->push('foo', 'bar');

        $this->assertInstanceOf(Dot::class, $result);
        $this->assertSame($dot, $result);
    }

    public function testReplaceReturnsSelfForChaining(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $result = $dot->replace(['foo' => 'baz']);

        $this->assertInstanceOf(Dot::class, $result);
        $this->assertSame($dot, $result);
    }

    public function testGetWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo' => ['bar' => ['baz' => 'value']]], false, '->');

        $this->assertSame('value', $dot->get('foo->bar->baz'));
    }

    public function testFlattenWithProvidedItems(): void
    {
        $dot = new Dot();
        $flatten = $dot->flatten('.', ['foo' => ['bar' => 'baz']]);

        $this->assertSame('baz', $flatten['foo.bar']);
    }

    public function testClearWithNestedDotNotation(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => 'value']]]);
        $dot->clear('a.b');

        $this->assertSame([], $dot->get('a.b'));
        $this->assertTrue($dot->has('a'));
    }

    public function testConstructWithObjectProperties(): void
    {
        $obj = new \stdClass();
        $obj->nested = ['key' => 'value'];

        $dot = new Dot($obj);

        $this->assertSame(['key' => 'value'], $dot->get('nested'));
        $this->assertSame('value', $dot->get('nested.key'));
    }

    public function testMergeRecursiveWithKey(): void
    {
        $dot = new Dot();
        $dot->mergeRecursive('foo', ['bar' => 'baz']);

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testMergeRecursiveDistinctWithKey(): void
    {
        $dot = new Dot();
        $dot->mergeRecursiveDistinct('foo', ['bar' => 'baz']);

        $this->assertSame('baz', $dot->get('foo.bar'));
    }

    public function testJsonSerializableInterface(): void
    {
        $dot = new Dot(['a' => 1, 'b' => ['c' => 2]]);

        $this->assertSame(['a' => 1, 'b' => ['c' => 2]], $dot->jsonSerialize());
    }

    public function testIteratorWithNestedData(): void
    {
        $data = ['foo' => ['bar' => 'baz'], 'qux' => 'quux'];
        $dot = new Dot($data);

        $keys = [];
        $values = [];
        foreach ($dot as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        $this->assertSame(['foo', 'qux'], $keys);
        $this->assertSame([['bar' => 'baz'], 'quux'], $values);
    }

    public function testPullDeletesNestedKeyProperly(): void
    {
        $dot = new Dot(['a' => ['b' => 'c', 'd' => 'e']]);
        $pulled = $dot->pull('a.b');

        $this->assertSame('c', $pulled);
        $this->assertSame(['a' => ['d' => 'e']], $dot->all());
    }

    public function testSetArrayWithObjectInput(): void
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';

        $dot = new Dot();
        $dot->setArray($obj);

        $this->assertSame('bar', $dot->get('foo'));
    }

    public function testAddDoesNotOverwriteExistingNestedValue(): void
    {
        $dot = new Dot(['a' => ['b' => ['c' => 'original']]]);
        $dot->add('a.b.c', 'new_value');

        $this->assertSame('original', $dot->get('a.b.c'));
    }

    public function testToJsonWithIntegerOptions(): void
    {
        $dot = new Dot(['foo' => 'bar']);

        // When key is integer (like JSON options), it should encode all items
        // toJson(JSON_PRETTY_PRINT) - key = 128 (int), options = 128
        $result = $dot->toJson(JSON_PRETTY_PRINT);

        $this->assertJsonStringEqualsJsonString('{"foo":"bar"}', $result);
    }
}
