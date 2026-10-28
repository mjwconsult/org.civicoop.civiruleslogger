<?php

/**
 * CivirulesLogger.PruneLog API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws CRM_Core_Exception
 */
function civicrm_api3_civirules_logger_prunelog($params) {
  $retentionDays = $params['retention_days'] ?? 90;
  $debugRetentionDays = $params['debug_retention_days'] ?? 7;
  $batchSize = $params['batch_size'] ?? 10000;
  // A rule with debug logging on writes an entry, with all its trigger data,
  // every time it is evaluated, so those go sooner.
  $deleted = _civicrm_api3_civirules_logger_prunelog_delete($debugRetentionDays, $batchSize, "AND `level` = 'debug'");
  $deleted += _civicrm_api3_civirules_logger_prunelog_delete($retentionDays, $batchSize);

  $returnValues = [
    'retention_days' => $retentionDays,
    'debug_retention_days' => $debugRetentionDays,
    'deleted' => $deleted,
  ];
  return civicrm_api3_create_success($returnValues, $params, 'CivirulesLogger', 'Prunelog');
}

/**
 * Delete entries older than $days, in batches.
 *
 * A set-based delete across the whole table, so SQL rather than a write per
 * row. In batches, each its own statement: a large backlog is removed a piece
 * at a time instead of in one transaction that can outrun the server.
 *
 * @return int
 *   The number deleted.
 */
function _civicrm_api3_civirules_logger_prunelog_delete(int $days, int $batchSize, string $andWhere = ''): int {
  $deleted = 0;
  do {
    $batch = CRM_Core_DAO::executeQuery(
      "DELETE FROM `civirule_civiruleslogger_log` WHERE `timestamp` < (CURDATE() - INTERVAL %1 DAY) $andWhere ORDER BY `timestamp`, `id` LIMIT %2",
      [1 => [$days, 'Integer'], 2 => [$batchSize, 'Positive']]
    )->affectedRows();
    $deleted += $batch;
  } while ($batch >= $batchSize);
  return $deleted;
}

/**
 * CivirulesLogger.PruneLog API spec
 *
 * @param array $params
 */
function _civicrm_api3_civirules_logger_Prunelog_spec(&$params) {
  $params['retention_days'] = [
    'title' => 'Retention (days)',
    'type' => CRM_Utils_Type::T_INT,
    'api.default' => 90,
    'description' => 'Delete civiruleslogger log entries older than this many days',
  ];
  $params['debug_retention_days'] = [
    'title' => 'Debug retention (days)',
    'type' => CRM_Utils_Type::T_INT,
    'api.default' => 7,
    'description' => 'Delete debug-level entries older than this many days',
  ];
  $params['batch_size'] = [
    'title' => 'Batch size',
    'type' => CRM_Utils_Type::T_INT,
    'api.default' => 10000,
    'description' => 'Delete at most this many entries per query',
  ];
}
