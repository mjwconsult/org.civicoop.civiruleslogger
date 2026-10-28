<?php
declare(strict_types = 1);
use CRM_Civiruleslogger_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
final class CRM_Civiruleslogger_Upgrader extends \CRM_Extension_Upgrader_Base {

  /**
   * Convert message from varchar to text
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  public function upgrade_1000(): bool {
    $this->ctx->log->info('Convert message from varchar to text');
    CRM_Core_DAO::executeQuery("ALTER TABLE civirule_civiruleslogger_log MODIFY COLUMN `message` text NOT NULL COMMENT 'Standardised message'");
    return TRUE;
  }

  /**
   * Index timestamp (pruning) and rule_id (the rule's log page, deleting a rule).
   *
   * On a large log, building the indexes takes time and temporary disk space:
   * truncate the table first if its contents are not needed.
   *
   * @return TRUE on success
   */
  public function upgrade_1001(): bool {
    $this->ctx->log->info('Add indexes on timestamp and rule_id to civirule_civiruleslogger_log');
    foreach (['timestamp', 'rule_id'] as $field) {
      E::schema()->createIndex('civirule_civiruleslogger_log', "index_$field", ['fields' => [$field => TRUE]]);
    }
    return TRUE;
  }

}
