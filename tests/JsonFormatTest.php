<?php
use PHPUnit\Framework\TestCase;

/**
 * @covers \AmazeeIO\Health\JsonFormat
 */
class JsonFormatTest extends TestCase
{

    /** @test */
    public function it_should_return_json_of_applicable_checks()
    {
        $applicableCheck = $this->generateCheck('test_check_1', 'First check', true, true);
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($applicableCheck);

        $jsonFormatter = new \AmazeeIO\Health\Format\JsonFormat($checkDriver);

        $formatterOutput = $jsonFormatter->formattedResults();
        $this->assertJson($formatterOutput);
        $formatterOutputAsArray = json_decode($formatterOutput, true);
        $this->assertIsArray($formatterOutputAsArray);
        $this->assertArrayHasKey('test_check_1', $formatterOutputAsArray);
        $this->assertEquals(\AmazeeIO\Health\Check\CheckInterface::STATUS_PASS, $formatterOutputAsArray['test_check_1']);
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