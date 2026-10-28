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
  $batchSize = $params['batch_size'] ?? 10000;
  // A set-based delete across the whole table, so SQL rather than a write per
  // row. In batches, each its own statement: a large backlog is removed a
  // piece at a time instead of in one transaction that can outrun the server.
  $deleted = 0;
  do {
    $batch = CRM_Core_DAO::executeQuery(
      "DELETE FROM `civirule_civiruleslogger_log` WHERE `timestamp` < (CURDATE() - INTERVAL %1 DAY) ORDER BY `timestamp`, `id` LIMIT %2",
      [1 => [(int) $retentionDays, 'Integer'], 2 => [(int) $batchSize, 'Positive']]
    )->affectedRows();
    $deleted += $batch;
  } while ($batch >= $batchSize);

  $returnValues = ['retention_days' => $retentionDays, 'deleted' => $deleted];
  return civicrm_api3_create_success($returnValues, $params, 'CivirulesLogger', 'Prunelog');
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
  $params['batch_size'] = [
    'title' => 'Batch size',
    'type' => CRM_Utils_Type::T_INT,
    'api.default' => 10000,
    'description' => 'Delete at most this many entries per query',
  ];
}
