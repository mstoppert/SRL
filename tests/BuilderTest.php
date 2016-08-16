<?php

namespace Tests;

use SRL\Builder;
use SRL\SRL;

class BuilderTest extends TestCase
{
    public function testSimplePhoneNumberFormat()
    {
        $regex = SRL::startsWith()
            ->literally('+')
            ->number()->between(1, 3)
            ->literally(' ')
            ->number()->between(3, 4)
            ->literally('-')
            ->number()->onceOrMore()
            ->mustEnd()->get();

        $this->assertEquals(1, preg_match($regex, '+49 123-45'));
        $this->assertEquals(1, preg_match($regex, '+492 1235-4'));
        $this->assertEquals(0, preg_match($regex, '+49 123 45'));
        $this->assertEquals(0, preg_match($regex, '49 123-45'));
        $this->assertEquals(0, preg_match($regex, 'a+49 123-45'));
        $this->assertEquals(0, preg_match($regex, '+49 123-45b'));
    }

    public function testSimpleEmailFormat()
    {
        $regex = SRL::startsWith()
            ->eitherOf(function (Builder $query) {
                $query->number()
                    ->letter()
                    ->literally('._%+-');
            })->onceOrMore()
            ->literally('@')
            ->eitherOf(function (Builder $query) {
                $query->number()
                    ->letter()
                    ->literally('.-');
            })->onceOrMore()
            ->literally('.')
            ->letter()->atLeast(2)
            ->mustEnd()
            ->caseInsensitive(); // Not using get to test __toString() method

        $this->assertEquals(1, preg_match($regex, 'sample@example.com'));
        $this->assertEquals(1, preg_match($regex, 'super-He4vy.add+ress@top-Le.ve1.domains'));
        $this->assertEquals(0, preg_match($regex, 'sample.example.com'));
        $this->assertEquals(0, preg_match($regex, 'missing@tld'));
        $this->assertEquals(0, preg_match($regex, 'hav ing@spac.es'));
        $this->assertEquals(0, preg_match($regex, 'no@pe.123'));
        $this->assertEquals(0, preg_match($regex, 'invalid@email.com123'));

        $this->assertTrue($regex->matches('super-He4vy.add+ress@top-Le.ve1.domains'));
        $this->assertFalse($regex->matches('sample.example.com'));
    }
}