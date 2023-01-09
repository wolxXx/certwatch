<?php

class ResultTest extends \Certwatch\Test\TestBase
{
    public function testInstantiation()
    {
        $instance = new \Certwatch\Result();
        $this->assertInstanceOf(\Certwatch\Result::class, $instance);
        $this->assertEmpty($instance->getErrors());
        $this->assertSame(-1, $instance->getValidUntilDays());
        $this->assertFalse($instance->isValid());
    }


    public function testGetSetDomain()
    {
        $value = 'foo.barfoos.net';
        $instance = new \Certwatch\Result();
        $setter = $instance->setDomain($value);
        $this->assertSame($value, $instance->getDomain());
        $this->assertSame($instance, $setter);
    }


    public function testGetSetValidUntil()
    {
        $value = new DateTime();
        $this->assertSame($value, (new \Certwatch\Result())->setValidUntil($value)->getValidUntil());
    }

    public function testGetSetValidUntilDays()
    {
        $value = 7;
        $this->assertSame($value, (new \Certwatch\Result())->setValidUntilDays($value)->getValidUntilDays());
    }

    public function testGetSetValidFrom()
    {
        $value = new DateTime();
        $this->assertSame($value, (new \Certwatch\Result())->setValidFrom($value)->getValidFrom());
    }

    public function testGetSetIssuer()
    {
        $value = 'test cert issuer';
        $this->assertSame($value, (new \Certwatch\Result())->setIssuer($value)->getIssuer());
    }

    public function testGetSetValid()
    {
        $value = true;
        $this->assertSame($value, (new \Certwatch\Result())->setValid($value)->isValid());
        $value = false;
        $this->assertSame($value, (new \Certwatch\Result())->setValid($value)->isValid());
    }

    public function testErrors()
    {
        $error1 = 'foo';
        $error2 = 'bar';
        $instance = new \Certwatch\Result();
        $this->assertSame([], $instance->getErrors());
        $instance->addError($error1);
        $this->assertSame([$error1], $instance->getErrors());
        $instance->addError($error2);
        $this->assertSame([$error1, $error2], $instance->getErrors());
    }
}
