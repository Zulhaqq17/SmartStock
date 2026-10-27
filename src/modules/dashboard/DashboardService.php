<?php
/**
 * MODULE 6: Inventory Dashboard and Reporting
 * Owner: TODO (name)
 * Reads data produced by the other modules. Use the seed data to build the UI first.
 */

/** TODO: counts for summary cards (total products, total stock items, open alerts, expired batches) */
function getSummaryCounts(): array { return []; }

/** TODO: products where total stock <= min_stock_level */
function getLowStockItems(): array { return []; }

/** TODO: batches expiring within $days days (and already expired), earliest first */
function getExpiringBatches(int $days = 30): array { return []; }

/** TODO: latest N stock_transactions */
function getRecentMovements(int $limit = 10): array { return []; }

/** TODO: rows for the basic inventory report (current stock per product/batch). Add CSV export later. */
function getInventoryReportData(array $filters = []): array { return []; }
