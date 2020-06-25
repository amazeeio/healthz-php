<?php
use PHPUnit\Framework\TestCase;

/**
 * @covers \AmazeeIO\Health\JsonFormat
 */
class PrometheusFormatTest extends TestCase
{

    /** @test */
    public function it_should_return_a_prometheus_formatted_string()
    {
        $applicableCheck = $this->generateCheck('test_check_1', 'First check', true, true);
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($applicableCheck);

        $formatter = new \AmazeeIO\Health\Format\PrometheusFormat($checkDriver);

        $formatterOutput = $formatter->formattedResults();
        $this->assertStringContainsString("test_check_1_info 1", $formatterOutput);
    }

    protected function generateCheck(
      $shortName,
      $description = "",
      $applies = true,
      $passes = true,
      $status = \AmazeeIO\Health\Check\CheckInterface::STATUS_PASS
    ) {
        $check = $this->createMock(\AmazeeIO\Health\Check\CheckInterface::class);
        $check->method('shortName')->willReturn($shortName);
        $check->method('description')->willReturn($description);
        $check->expects($this->atLeastOnce())
          ->method('appliesInCurrentEnvironment')
          ->willReturn($applies);
        $check->method('result')
          ->willReturn($passes);
        $check->method('status')->willReturn($status);
        return $check;
    }
}