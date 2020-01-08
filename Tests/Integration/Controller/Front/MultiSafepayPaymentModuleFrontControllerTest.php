<?php
require_once __DIR__ . '/../../../../controllers/front/payment.php';

class MultiSafepayPaymentModuleFrontControllerTest extends \PHPUnit\Framework\TestCase
{
    /** @var MultiSafepayPaymentModuleFrontController */
    public $multisafepayPaymentController;
    const SECONDS_IN_A_DAY = 86400;

    protected function setUp()
    {
        $this->multisafepayPaymentController = $this->getMockBuilder(MultiSafepayPaymentModuleFrontController::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getSecondsActive'])
            ->getMock();
    }

    public function testGetSeccondsActiveWithSeconds()
    {
        $this->setConfiguration('MULTISAFEPAY_TIME_ACTIVE', self::SECONDS_IN_A_DAY);
        $this->setConfiguration('MULTISAFEPAY_TIME_UNIT', 'seconds');
        $result = $this->multisafepayPaymentController->getSecondsActive();
        $this->assertEquals(self::SECONDS_IN_A_DAY, $result);
    }

    public function testGetSeccondsActiveWithHours()
    {
        $this->setConfiguration('MULTISAFEPAY_TIME_ACTIVE', 24);
        $this->setConfiguration('MULTISAFEPAY_TIME_UNIT', 'hours');
        $result = $this->multisafepayPaymentController->getSecondsActive();
        $this->assertEquals(self::SECONDS_IN_A_DAY, $result);
    }

    public function testGetSeccondsActiveWithDays()
    {
        $this->setConfiguration('MULTISAFEPAY_TIME_ACTIVE', 1);
        $this->setConfiguration('MULTISAFEPAY_TIME_UNIT', 'days');
        $result = $this->multisafepayPaymentController->getSecondsActive();
        $this->assertEquals(self::SECONDS_IN_A_DAY, $result);
    }

    private function setConfiguration($index, $value)
    {
        Configuration::set($index, $value);
    }
}
