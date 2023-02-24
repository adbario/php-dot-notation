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

        $this->assertEquals('bar', $dot->get('foo'));
    }

    public function testConstructWithString(): void
    {
        $dot = new Dot('foobar');

        $this->assertEquals('foobar', $dot->get(0));
    }

    public function testConstructWithDot(): void
    {
        $dot1 = new Dot(['foo' => 'bar']);
        $dot2 = new Dot($dot1);

        $this->assertEquals('bar', $dot2->get('foo'));
    }

    public function testConstructWithParsing(): void
    {
        $dot = new Dot(['foo.bar' => 'baz']);

        $this->assertEquals(['foo.bar' => 'baz'], $dot->get());

        $dot = new Dot(['foo.bar' => 'baz'], true);

        $this->assertEquals(['foo' => ['bar' => 'baz']], $dot->get());

        $dot = new Dot([], true);

        $this->assertEquals([], $dot->get());
    }

    public function testConstructWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo_bar' => 'baz'], false, "_");

        $this->assertEquals(['foo_bar' => 'baz'], $dot->get());

        $dot = new Dot(['foo_bar' => 'baz'], true, "_");

        $this->assertEquals(['foo' => ['bar' => 'baz']], $dot->get());
    }

    public function testConstructHelper(): void
    {
        $dot = dot(['foo' => 'bar']);

        $this->assertInstanceOf(Dot::class, $dot);
        $this->assertEquals('bar', $dot->get('foo'));
    }

    public function testConstructHelpertWithParsing(): void
    {
        $dot = dot(['foo.bar' => 'baz'], true);

        $this->assertEquals(['foo' => ['bar' => 'baz']], $dot->get());
    }

    public function testConstructHelpertWithCustomDelimiter(): void
    {
        $dot = dot(['foo_bar' => 'baz'], false, "_");

        $this->assertEquals(['foo_bar' => 'baz'], $dot->get());

        $dot = dot(['foo_bar' => 'baz'], true, "_");

        $this->assertEquals(['foo' => ['bar' => 'baz']], $dot->get());
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

        $this->assertEquals('baz', $dot->get('foo.bar'));
    }

    public function testAddValueToExistingKey(): void
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->add('foo', 'baz');

        $this->assertEquals('bar', $dot->get('foo'));
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
        $this->assertEquals('xyz', $flatten['foo.abc']);
        $this->assertEquals('baz', $flatten['foo.bar.0']);
    }

    public function testFlattenWithCustomDelimiter(): void
    {
        $dot = new Dot(['foo' => ['abc' => 'xyz', 'bar' => ['baz']]]);
        $flatten = $dot->flatten('_');
        $this->assertEquals('xyz', $flatten['foo_abc']);
        $this->assertEquals('baz', $flatten['foo_bar_0']);
    }

    /*
     * --------------------------------------------------------------
     * Get
     * --------------------------------------------------------------
     */

    public function testGetValueFromKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertEquals('baz', $dot->get('foo.bar'));
    }

    public function testGetValueFromNonExistingKey(): void
    {
        $dot = new Dot();

        $this->assertNull($dot->get('foo'));
    }

    public function testGetGivenDefaultValueFromNonExistingKey(): void
    {
        $dot = new Dot();

        $this->assertEquals('bar', $dot->get('foo', 'bar'));
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

        $this->assertEquals('qux', $dot->get('foo.bar'));
    }

    public function testMergeArrayWithKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->merge('foo', ['bar' => 'qux']);

        $this->assertEquals('qux', $dot->get('foo.bar'));
    }

    public function testMergeDotWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux']]);
        $dot1->merge($dot2);

        $this->assertEquals('qux', $dot1->get('foo.bar'));
    }

    public function testMergeDotObjectWithKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux']);
        $dot1->merge('foo', $dot2);

        $this->assertEquals('qux', $dot1->get('foo.bar'));
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

        $this->assertEquals(['baz', 'qux'], $dot->get('foo.bar'));
        $this->assertEquals('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveMergeArrayWithKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->mergeRecursive('foo', ['bar' => 'qux', 'quux' => 'quuz']);

        $this->assertEquals(['baz', 'qux'], $dot->get('foo.bar'));
        $this->assertEquals('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveMergeDotWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1->mergeRecursive($dot2);

        $this->assertEquals(['baz', 'qux'], $dot1->get('foo.bar'));
        $this->assertEquals('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveMergeDotObjectWithKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux', 'quux' => 'quuz']);
        $dot1->mergeRecursive('foo', $dot2);

        $this->assertEquals(['baz', 'qux'], $dot1->get('foo.bar'));
        $this->assertEquals('quuz', $dot1->get('foo.quux'));
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

        $this->assertEquals('qux', $dot->get('foo.bar'));
        $this->assertEquals('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeArrayWithKey(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->mergeRecursiveDistinct('foo', ['bar' => 'qux', 'quux' => 'quuz']);

        $this->assertEquals('qux', $dot->get('foo.bar'));
        $this->assertEquals('quuz', $dot->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeDotWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux', 'quux' => 'quuz']]);
        $dot1->mergeRecursiveDistinct($dot2);

        $this->assertEquals('qux', $dot1->get('foo.bar'));
        $this->assertEquals('quuz', $dot1->get('foo.quux'));
    }

    public function testRecursiveDistinctMergeDotObjectWithKey(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux', 'quux' => 'quuz']);
        $dot1->mergeRecursiveDistinct('foo', $dot2);

        $this->assertEquals('qux', $dot1->get('foo.bar'));
        $this->assertEquals('quuz', $dot1->get('foo.quux'));
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

        $this->assertEquals('bar', $dot->pull('foo'));
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

        $this->assertEquals('bar', $dot->pull('foo', 'bar'));
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

        $this->assertEquals('foo', $dot->get(0));
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

        $this->assertEquals(['qux' => 'quux'], $dot->get('foo'));
    }

    public function testReplaceKeyWithArray(): void
    {
        $dot = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot->replace('foo', ['qux' => 'corge']);

        $this->assertEquals(['bar' => 'baz', 'qux' => 'corge'], $dot->get('foo'));
    }

    public function testReplaceWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux']]);
        $dot1->replace($dot2);

        $this->assertEquals(['bar' => 'qux'], $dot1->get('foo'));
    }

    public function testReplaceKeyWithDot(): void
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz', 'qux' => 'quux']]);
        $dot2 = new Dot(['qux' => 'corge']);
        $dot1->merge('foo', $dot2);

        $this->assertEquals(['bar' => 'baz', 'qux' => 'corge'], $dot1->get('foo'));
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

        $this->assertEquals('baz', $dot->get('foo.bar'));
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

        $this->assertEquals('baz', $items['foo']);
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

        $this->assertEquals('bar', $dot['foo']);
    }

    public function testOffsetSet(): void
    {
        $dot = new Dot();
        $dot['foo.bar'] = 'baz';

        $this->assertEquals('baz', $dot['foo.bar']);
    }

    public function testOffsetSetWithoutKey(): void
    {
        $dot = new Dot();
        $dot[] = 'foobar';

        $this->assertEquals('foobar', $dot->get(0));
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

        $this->assertEquals(
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
            $this->assertEquals(
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
            $this->assertEquals(
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

        $this->assertEquals(3, $dot->count());
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
}
