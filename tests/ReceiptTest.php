<?php

namespace TDD\Test;

require dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PHPUnit\Framework\TestCase;
use TDD\Receipt;

/**
 *
 */
class ReceiptTest extends TestCase
{

  public function setUp()
  {
      $this->Formatter = $this->getMockBuilder('TDD\Formatter')
          ->setMethods(['currencyAmt'])
          ->getMock();
      $this->Formatter->expects($this->any())
          ->method('currencyAmt')
          ->with($this->anything())
          ->will($this->returnArgument(0));
      $this->Receipt = new Receipt($this->Formatter);
  }

  public function tearDown()
  {
    unset($this->Receipt);
  }

  /**
  * @dataProvider provideSubTotal
  */
  public function testSubTotal($items, $expected)
  {
    $coupon = null;
    $output = $this->Receipt->subTotal($items, $coupon);
    $this->assertEquals(
      $expected,
      $output,
      "When summing the total should equal {$expected}"
    );
  }

  public function provideSubTotal()
  {
    return [
      'ints totaling 16' => [[1,2,5,8], 16],
      [[-1,2,5,8], 14],
      [[1,2,8], 11],
    ];
  }

  public function testSubTotalAndCoupon()
  {
    $input = [0,2,5,8];
    $coupon = 0.20;
    $output = $this->Receipt->subTotal($input, $coupon);
    $this->assertEquals(
      12,
      $output,
      'When summing the total should equal 12'
    );
  }

  public function testSubTotalException()
  {
    $input = [0,2,5,8];
    $coupon = 1.20;
    $this->expectException('BadMethodCallException');
    $this->Receipt->subTotal($input, $coupon);
  }

  public function testPostTaxTotal()
  {
    $items = [1,2,5,8];
    $tax = 0.20;
    $coupon = null;
    $Receipt = $this->getMockBuilder('TDD\Receipt')
        ->setMethods(['tax', 'subTotal'])
        ->setConstructorArgs([$this->Formatter])
        ->getMock();
    $Receipt->expects($this->once())
        ->method('subTotal')
        ->with($items, $coupon)
        ->will($this->returnValue(10.00));
    $Receipt->expects($this->once())
        ->method('tax')
        ->with(10.00)
        ->will($this->returnValue(1.00));
    $result = $Receipt->testPostTaxTotal([1,2,5,8], null);
    $this->assertEquals(11.00, $result);
  }

  public function testTax()
  {
    $inputAmount = 10.00;
    $this->Receipt->tax = 0.10;
    $output = $this->Receipt->tax($inputAmount);
    $this->assertEquals(
      1.00,
      $output,
      'The tax calculation should equal 1.00'
    );
  }

}
