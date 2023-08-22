<?php

defined('TYPO3') or die();

use Atkins\Pagedoctor\Helpers\TcaHelpers;

TcaHelpers::addCtrlColumns('text');
TcaHelpers::addCtrlColumns('integer');
TcaHelpers::addCtrlColumns('asset');

$GLOBALS['TCA']['tt_content']['ctrl']['label_userFunc'] = Atkins\Pagedoctor\Helpers\TcaHelpers::class . '->addCtrlLabels';