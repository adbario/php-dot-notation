<?php
/**
 * Dot - PHP dot notation access to arrays
 *
 * @author  Riku Särkinen <riku@adbar.io>
 * @link    https://github.com/adbario/php-dot-notation
 * @license https://github.com/adbario/php-dot-notation/blob/2.x/LICENSE.md (MIT License)
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
    public function testConstructWithoutValues()
    {
        $dot = new Dot;

        $this->assertSame([], $dot->all());
    }

    public function testConstructWithArray()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertEquals('bar', $dot->get('foo'));
    }

    public function testConstructWithString()
    {
        $dot = new Dot('foobar');

        $this->assertEquals('foobar', $dot->get(0));
    }

    public function testConstructWithDot()
    {
        $dot1 = new Dot(['foo' => 'bar']);
        $dot2 = new Dot($dot1);

        $this->assertEquals('bar', $dot2->get('foo'));
    }

    public function testConstructHelper()
    {
        $dot = dot(['foo' => 'bar']);

        $this->assertInstanceOf(Dot::class, $dot);
        $this->assertEquals('bar', $dot->get('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Add
     * --------------------------------------------------------------
     */

    public function testAddKeyValuePair()
    {
        $dot = new Dot;
        $dot->add('foo.bar', 'baz');

        $this->assertEquals('baz', $dot->get('foo.bar'));
    }

    public function testAddValueToExistingKey()
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->add('foo', 'baz');

        $this->assertEquals('bar', $dot->get('foo'));
    }

    public function testAddArrayOfKeyValuePairs()
    {
        $dot = new Dot(['foobar' => 'baz']);
        $dot->add([
            'foobar' => 'qux',
            'corge' => 'grault'
        ]);

        $this->assertSame(['foobar' => 'baz', 'corge' => 'grault'], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * All
     * --------------------------------------------------------------
     */

    public function testAllReturnsAllItems()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Clear
     * --------------------------------------------------------------
     */

    public function testClearKey()
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->clear('foo.bar');

        $this->assertSame([], $dot->get('foo.bar'));
    }

    public function testClearNonExistingKey()
    {
        $dot = new Dot;
        $dot->clear('foo');

        $this->assertSame([], $dot->get('foo'));
    }

    public function testClearArrayOfKeys()
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);
        $dot->clear(['foo', 'baz']);

        $this->assertSame(['foo' => [], 'baz' => []], $dot->all());
    }

    public function testClearAll()
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->clear();

        $this->assertSame([], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Delete
     * --------------------------------------------------------------
     */

    public function testDeleteKey()
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->delete('foo.bar');

        $this->assertFalse($dot->has('foo.bar'));
    }

    public function testDeleteNonExistingKey()
    {
        $dot = new Dot(['foo' => 'bar']);
        $dot->delete('baz.qux');

        $this->assertSame(['foo' => 'bar'], $dot->all());
    }

    public function testDeleteArrayOfKeys()
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);
        $dot->delete(['foo', 'baz']);

        $this->assertSame([], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Get
     * --------------------------------------------------------------
     */

    public function testGetValueFromKey()
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertEquals('baz', $dot->get('foo.bar'));
    }

    public function testGetValueFromNonExistingKey()
    {
        $dot = new Dot;

        $this->assertNull($dot->get('foo'));
    }

    public function testGetGivenDefaultValueFromNonExistingKey()
    {
        $dot = new Dot;

        $this->assertEquals('bar', $dot->get('foo', 'bar'));
    }

    /*
     * --------------------------------------------------------------
     * Has
     * --------------------------------------------------------------
     */

    public function testHasKey()
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);

        $this->assertTrue($dot->has('foo.bar'));

        $dot->delete('foo.bar');

        $this->assertFalse($dot->has('foo.bar'));
    }

    public function testHasArrayOfKeys()
    {
        $dot = new Dot(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertTrue($dot->has(['foo', 'baz']));

        $dot->delete('foo');

        $this->assertFalse($dot->has(['foo', 'baz']));
    }

    public function testHasWithEmptyDot()
    {
        $dot = new Dot;

        $this->assertFalse($dot->has('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Is empty
     * --------------------------------------------------------------
     */

    public function testIsEmptyDot()
    {
        $dot = new Dot;

        $this->assertTrue($dot->isEmpty());

        $dot->set('foo', 'bar');

        $this->assertFalse($dot->isEmpty());
    }

    public function testIsEmptyKey()
    {
        $dot = new Dot;

        $this->assertTrue($dot->isEmpty('foo.bar'));

        $dot->set('foo.bar', 'baz');

        $this->assertFalse($dot->isEmpty('foo.bar'));
    }

    public function testIsEmptyArrayOfKeys()
    {
        $dot = new Dot;

        $this->assertTrue($dot->isEmpty(['foo', 'bar']));

        $dot->set('foo', 'baz');

        $this->assertFalse($dot->isEmpty(['foo', 'bar']));
    }

    /*
     * --------------------------------------------------------------
     * Merge
     * --------------------------------------------------------------
     */

    public function testMergeArrayWithDot()
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->merge(['foo' => ['bar' => 'qux']]);

        $this->assertEquals('qux', $dot->get('foo.bar'));
    }

    public function testMergeArrayWithKey()
    {
        $dot = new Dot(['foo' => ['bar' => 'baz']]);
        $dot->merge('foo', ['bar' => 'qux']);

        $this->assertEquals('qux', $dot->get('foo.bar'));
    }

    public function testMergeDotWithDot()
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['foo' => ['bar' => 'qux']]);
        $dot1->merge($dot2);

        $this->assertEquals('qux', $dot1->get('foo.bar'));
    }

    public function testMergeDotObjectWithKey()
    {
        $dot1 = new Dot(['foo' => ['bar' => 'baz']]);
        $dot2 = new Dot(['bar' => 'qux']);
        $dot1->merge('foo', $dot2);

        $this->assertEquals('qux', $dot1->get('foo.bar'));
    }

    /*
     * --------------------------------------------------------------
     * Pull
     * --------------------------------------------------------------
     */

    public function testPullKey()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertEquals('bar', $dot->pull('foo'));
        $this->assertFalse($dot->has('foo'));
    }

    public function testPullNonExistingKey()
    {
        $dot = new Dot;

        $this->assertNull($dot->pull('foo'));
    }

    public function testPullNonExistingKeyWithDefaultValue()
    {
        $dot = new Dot;

        $this->assertEquals('bar', $dot->pull('foo', 'bar'));
    }

    public function testPullAll()
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

    public function testPushValue()
    {
        $dot = new Dot;
        $dot->push('foo');

        $this->assertEquals('foo', $dot->get(0));
    }

    public function testPushValueToKey()
    {
        $dot = new Dot(['foo' => [0 => 'bar']]);
        $dot->push('foo', 'baz');

        $this->assertSame(['bar', 'baz'], $dot->get('foo'));
    }

    /*
     * --------------------------------------------------------------
     * Set
     * --------------------------------------------------------------
     */

    public function testSetKeyValuePair()
    {
        $dot = new Dot;
        $dot->set('foo.bar', 'baz');

        $this->assertEquals('baz', $dot->get('foo.bar'));
    }

    public function testSetArrayOfKeyValuePairs()
    {
        $dot = new Dot;
        $dot->set(['foo' => 'bar', 'baz' => 'qux']);

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Set array
     * --------------------------------------------------------------
     */

    public function testSetArray()
    {
        $dot = new Dot;
        $dot->setArray(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $dot->all());
    }

    /*
     * --------------------------------------------------------------
     * Set reference
     * --------------------------------------------------------------
     */

    public function testSetReference()
    {
        $dot = new Dot;
        $items = ['foo' => 'bar'];
        $dot->setReference($items);
        $dot->set('foo', 'baz');

        $this->assertEquals('baz', $items['foo']);
    }

    /*
     * --------------------------------------------------------------
     * ArrayAccess interface
     * --------------------------------------------------------------
     */

    public function testOffsetExists()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertTrue(isset($dot['foo']));

        unset($dot['foo']);

        $this->assertFalse(isset($dot['foo']));
    }

    public function testOffsetGet()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertEquals('bar', $dot['foo']);
    }

    public function testOffsetSet()
    {
        $dot = new Dot;
        $dot['foo.bar'] = 'baz';

        $this->assertEquals('baz', $dot['foo.bar']);
    }

    public function testOffsetSetWithoutKey()
    {
        $dot = new Dot;
        $dot[] = 'foobar';

        $this->assertEquals('foobar', $dot->get(0));
    }

    public function testOffsetUnset()
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

    public function testToJsonAll()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['foo' => 'bar']),
            $dot->toJson()
        );
    }

    public function testToJsonAllWithOption()
    {
        $dot = new Dot(['foo' => "'bar'"]);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['foo' => "'bar'"], JSON_HEX_APOS),
            $dot->toJson(JSON_HEX_APOS)
        );
    }

    public function testToJsonKey()
    {
        $dot = new Dot(['foo' => ['bar' => 'value']]);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['bar' => "value"]),
            $dot->toJson('foo')
        );
    }

    public function testToJsonKeyWithOptions()
    {
        $dot = new Dot(['foo' => ['bar' => "'value'"]]);

        $this->assertEquals(
            json_encode(['bar' => "'value'"], JSON_HEX_APOS),
            $dot->toJson('foo', JSON_HEX_APOS)
        );
    }

    /*
     * --------------------------------------------------------------
     * Countable interface
     * --------------------------------------------------------------
     */

    public function testCount()
    {
        $dot = new Dot([1, 2, 3]);

        $this->assertEquals(3, $dot->count());
    }

    public function testCountable()
    {
        $dot = new Dot([1, 2, 3]);

        $this->assertCount(3, $dot);
    }

    /*
     * --------------------------------------------------------------
     * IteratorAggregate interface
     * --------------------------------------------------------------
     */

    public function testGetIteratorReturnsArrayIterator()
    {
        $dot = new Dot;

        $this->assertInstanceOf(ArrayIterator::class, $dot->getIterator());
    }

    public function testIterationReturnsOriginalValues()
    {
        $dot = new Dot([1, 2, 3]);

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

    public function testJsonEncodingReturnsJson()
    {
        $dot = new Dot(['foo' => 'bar']);

        $this->assertJsonStringEqualsJsonString(
            json_encode(['foo' => 'bar']),
            json_encode($dot)
        );
    }
}
