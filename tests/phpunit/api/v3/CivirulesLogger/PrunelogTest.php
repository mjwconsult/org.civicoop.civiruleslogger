<?php

use Civi\Api4\CivirulesLog;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
class api_v3_CivirulesLogger_PrunelogTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless(): \Civi\Test\CiviEnvBuilder {
    return \Civi\Test::headless()
      ->install(['org.civicoop.civirules'])
      ->installMe(__DIR__)
      ->apply();
  }

  public function testPruneDeletesEntriesOlderThanRetentionAcrossBatches(): void {
    $this->addLogs(5, '-100 days');
    $recent = $this->addLogs(3, '-10 days');

    $result = civicrm_api3('CivirulesLogger', 'prunelog', ['retention_days' => 90, 'batch_size' => 2]);

    $this->assertEquals(5, $result['values']['deleted']);
    $this->assertEquals($recent, $this->remainingIds());
  }

  /**
   * @return int[]
   *   The new entries' ids.
   */
  private function addLogs(int $count, string $age, string $level = 'info'): array {
    $records = array_fill(0, $count, [
      'message' => 'test',
      'level' => $level,
      'timestamp' => date('Y-m-d H:i:s', strtotime($age)),
    ]);
    return CivirulesLog::save(FALSE)->setRecords($records)->execute()->column('id');
  }

  /**
   * @return int[]
   */
  private function remainingIds(): array {
    return CivirulesLog::get(FALSE)->addSelect('id')->addOrderBy('id')->execute()->column('id');
  }

}
