<?php

/**
 * ---------------------------------------------------------
 * Cron Job routes
 * ---------------------------------------------------------.
 *
 * Routes for GET requests to run Cron jobs
 */

/*
 * Database maintenance
 *
 * Sends GET requests to to run database maintenance
 */
$kanso->Router->get('/cron-database-maintenance/', '\app\controllers\get\Cron@dbMaintenance', '\app\models\get\Cron');

/*
 * Check for broken links
 *
 * Sends GET requests to check for broken links
 */
$kanso->Router->get('/cron-email-queue/', '\app\controllers\get\Cron@emailQueue', '\app\models\get\Cron');
