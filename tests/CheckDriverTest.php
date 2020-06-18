<?php
use PHPUnit\Framework\TestCase;

/**
 * @covers \AmazeeIO\Health\CheckDriver
 */
class CheckDriverTest extends TestCase
{

    /**
     * @test
     */
    public function it_should_run_applicable_checks()
    {
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("applicable", "", true,
          true));
        $checkDriver->runChecks();
    }

    /**
     * @test
     */
    public function it_should_ignore_nonapplicable_checks()
    {
        $this->expectException(\AmazeeIO\Health\NoApplicableCheckException::class);
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("not_applicable", "",
          false));
        $checkDriver->runChecks();
    }

    /** @test */
    public function it_should_return_a_list_of_checks_that_have_run()
    {
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("applicable_passes",
          "a passing check", true, true));
        $checkDriver->registerCheck($this->generateCheck("applicable_fails",
          "a failing check", true, false));

        $results = $checkDriver->runChecks();

        $this->assertIsArray($results);
        $this->assertArrayHasKey('applicable_passes', $results);
        $this->assertArrayHasKey('applicable_fails', $results);
//        $this->assertEquals($results['applicable_passes']);
//        $this->assertEquals($results['applicable_fails']);
    }

    /** @test */
    public function it_should_throw_an_error_if_attempted_to_run_with_no_applicable_tests()
    {
        $this->expectException(\AmazeeIO\Health\NoApplicableCheckException::class);
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("not_applicable", "",
          false));
        $checkDriver->runChecks();
    }

    /** @test */
    public function it_should_run_checks_and_return_true_when_pass_function_is_called()
    {
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_1", "", true,
          true));
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_2", "", true,
          true));
        $this->assertTrue($checkDriver->pass());
    }

    /** @test */
    public function it_should_run_checks_and_return_false_when_pass_function_is_called_and_at_least_one_check_fails()
    {
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_1", "", true,
          true));
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_2", "", true,
          true));
        $checkDriver->registerCheck($this->generateCheck("applicable_fails_1", "", true,
          false, \AmazeeIO\Health\Check\CheckInterface::STATUS_FAIL));
        $this->assertFalse($checkDriver->pass());
    }

    /** @test */
    public function it_should_run_passing_checks_and_return_a_good_status_when_status_function_is_called()
    {
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_1", "", true,
          true));
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_2", "", true,
          true));
        $this->assertEquals(\AmazeeIO\Health\Check\CheckInterface::STATUS_PASS, $checkDriver->status());
    }

    /** @test */
    public function it_should_run_passing_checks_and_return_a_warning_status_when_at_least_one_check_returns_warning()
    {
        $checkDriver = new \AmazeeIO\Health\CheckDriver();
        $checkDriver->registerCheck($this->generateCheck("applicable_passes_1", "", true,
          true));
        $checkDriver->registerCheck($this->generateCheck("applicable_warning", "", true,
          true, \AmazeeIO\Health\Check\CheckInterface::STATUS_WARN));
        $this->assertEquals(\AmazeeIO\Health\Check\CheckInterface::STATUS_WARN, $checkDriver->status());
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