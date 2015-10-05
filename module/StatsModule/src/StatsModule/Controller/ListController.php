<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/StatsModule for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace StatsModule\Controller;

use StatsModule\Logic\StatsLogic;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ListController extends AbstractActionController
{
    public function indexAction()
    {
        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        $statsLogic = $this->getServiceLocator()->get('StatsModule\Logic\StatsLogic');

        $currentTime = time();
        $curDayOfWeek = date('N', $currentTime);
        $curDayStartTime = mktime(0, 0, 0, date('n', $currentTime), date('j', $currentTime), date('Y', $currentTime));  // Начало текущих суток
        $curWeekStartTime = $curDayStartTime - 86400 * ($curDayOfWeek - 1);

        $timeSets = [
            'last 5 mins'  => [$currentTime - 5 * 60, $currentTime],
            'last 10 mins' => [$currentTime - 10 * 60, $currentTime],
            'last 15 mins' => [$currentTime - 15 * 60, $currentTime],
            'today'        => [$curDayStartTime, $currentTime],
            'yesterday'    => [$curDayStartTime - 86400, $curDayStartTime - 1],
            'current week' => [$curWeekStartTime, $currentTime],
            'last week'    => [$curWeekStartTime - 86400 * 7, $curWeekStartTime - 1],
            'all time'     => [0, $currentTime],
        ];

        $timeSet = $this->params()->fromQuery('timeSet', null);

        if (($timeSet !== null) && isset($timeSets[$timeSet])) {
            $fromTime = $timeSets[$timeSet][0];
            $toTime   = $timeSets[$timeSet][1];
            $selectedSet = $timeSet;
        } else {
            $selectedSet = null;
            $fromTime = (int) $this->params()->fromQuery('fromTime', $curDayStartTime);
            $toTime   = (int) $this->params()->fromQuery('toTime', time());
        }

        return new ViewModel([
            'campaignsTree'   => $statsLogic->getCampaignsTree($userId),
            'curDayStartTime' => $curDayStartTime,
            'timeSets'        => array_keys($timeSets),
            'selectedSet'     => $selectedSet,
            'fromTime'        => $fromTime,
            'toTime'          => $toTime,
        ]);
    }

    public function ajaxAction()
    {
        $request = $this->getRequest();

        $userId = $this->zfcUserAuthentication()->getIdentity()->getId();

        if (!$request->isPost()) {
            return false;
        }

        $postData = $request->getPost();
        $draw = (int) $postData['draw'];
        $sortOrderData = self::_parseSortOrder($postData);
        $filterData = self::_parseFilter($postData);


        /** @var StatsLogic $statsLogic */
        $statsLogic = $this->getServiceLocator()->get('StatsModule\Logic\StatsLogic');
        $data = $statsLogic->getPlainData(
            $userId,
            $filterData['campaigns'],
            $filterData['statuses'],
            $filterData['fromTime'],
            $filterData['toTime']
        );

        $campaignsNames = [];
        $campaignsTree = $statsLogic->getCampaignsTree($userId);
        foreach ($campaignsTree as $cabinet) {
            foreach ($cabinet['childNodes'] as $campaignData) {
                $campaignsNames[$campaignData['id']] = $campaignData['name'];
            }
        }

        $overallGain = $overallIncome = $overallSpent = $overallImpressions = $overallClicks = 0;

        $notFormattedData = $formattedData = [];
        foreach ($data as &$item) {
            $overallGain   += $item['gain'];
            $overallIncome += $item['income'];
            $overallSpent  += $item['spent'];
            $overallImpressions += $item['impressions'];
            $overallClicks += $item['clicks'];

            $status = $item['object']->getStatus();
            $approveStatus = $item['object']->getApproved();

            if ($status == StatsLogic::STATE_RUN) {
                $status = '<i id="ad-status" title="Запущена" class="icon-play"></i>';
            } elseif ($status == StatsLogic::STATE_REMOVED) {
                $status = '<i id="ad-status" title="Архивная" class="icon-trash"></i>';
            } elseif ($approveStatus == StatsLogic::APPROVE_STATUS_APPROVED) {
                $status = '<i id="ad-status" title="Остановлена" class="icon-pause"></i>';
            } elseif ($approveStatus == StatsLogic::APPROVE_STATUS_DECLINED) {
                $status = '<i id="ad-status" data-ban-ad-id="' . $item['object']->getId() . '" title="Отклонена модератором" class="icon-ban-circle"></i>';
            } elseif ($approveStatus == StatsLogic::APPROVE_STATUS_IN_PROCESS) {
                $status = '<i id="ad-status" title="На модерации" class="icon-eye-open"></i>';
            } elseif ($approveStatus == StatsLogic::APPROVE_STATUS_NEW) {
                $status = '<i id="ad-status" title="Не отправлялась на модерацию" class="icon-eye-close"></i>';
            }

            $campaignId = $item['object']->getCampaignId();
            $notFormattedData[] = [
                $campaignId,
                '',
                $campaignsNames[$campaignId],
                $item['id'],
                $item['name'],
                $status,
                $item['ROI'],
                $item['gain'],
                $item['income'],
                $item['spent'],
                $item['CPL'],
                $item['leadsCount'],
                $item['CPC'],
                $item['CTR'],
                $item['clicks'],
                $item['impressions'],
                $item['ROTA'],
            ];

            $formattedData[] = [
                $campaignId,
                '<input data-ad-id="' . $item['id'] . '" type="checkbox" onChange="statTableHelper.checkAdsSettingsButtonStatus(this)"/>',
                $campaignsNames[$campaignId],
                $item['id'],
                '<span title=" " class="ad-preview" data-ad-preview="' . $item['id'] . '">' . $item['name'] . '</span>',
                $status/* . ' / ' . $approveStatus*/,
                self::_formatNumber($item['ROI'] * 100, true) . '%',
                self::_formatNumber($item['gain'], true),
                self::_formatNumber($item['income']),
                self::_formatNumber($item['spent']),
                self::_formatNumber($item['CPL']),
                self::_formatNumber($item['leadsCount'], false, 0),
                self::_formatNumber($item['CPC']),
                self::_formatNumber($item['CTR'] * 100, false, 3),
                self::_formatNumber($item['clicks'], false, 0),
                self::_formatNumber($item['impressions'], false, 0),
                self::_formatNumber($item['ROTA'] * 100, true),
            ];
        }
        unset($item);

        foreach ($sortOrderData as $sortOrderBy => $orderDirection) {
            list($formattedData, $notFormattedData) = self::_sortData($formattedData, $notFormattedData, $sortOrderBy, $orderDirection);
        }

        $result = new JsonModel([
            "draw"            => $draw,
            "recordsTotal"    => count($formattedData),
            "recordsFiltered" => count($formattedData),
            "data"            => $formattedData,
        ]);

        return $result;
    }

    private static function _sortData($data, $notFormattedData, $orderBy, $orderDirection)
    {
        $sortArray = [];

        foreach($notFormattedData as $dataRow) {
            $sortArray[] = $dataRow[$orderBy];
        }

        array_multisort($sortArray, $orderDirection, SORT_REGULAR, $notFormattedData, $data);

        return [$data, $notFormattedData];
    }

    private static function _parseSortOrder($postData)
    {
        $sortOrder = [];

        if (isset($postData['order'])) {
            foreach ($postData['order'] as $data) {
                if ($data['dir'] === 'asc') {
                    $sortOrder[$data['column']] = SORT_ASC;
                } else {
                    $sortOrder[$data['column']] = SORT_DESC;
                }
            }
        }

        return $sortOrder;
    }

    private static function _parseFilter($postData)
    {
        $currentTime = time();
        // Начало текущих суток
        $curDayStartTime = mktime(0, 0, 0, date('n', $currentTime), date('j', $currentTime), date('Y', $currentTime));

        $parsedFilterData = [
            'fromTime'  => $curDayStartTime,
            'toTime'    => $currentTime,
            'campaigns' => [],
            'statuses'  => [],
        ];

        $filterData = Json::decode($postData['columns'][0]['search']['value'], Json::TYPE_ARRAY);

        if (isset($filterData['fromTime']) && ($filterData['fromTime'] >= 0)) {
            $parsedFilterData['fromTime'] = (int) $filterData['fromTime'];
        }

        if (isset($filterData['toTime']) && ($filterData['toTime'] >= 0)) {
            $parsedFilterData['toTime'] = (int) $filterData['toTime'];
        }

        if (isset($filterData['campaigns'])) {
            if (is_array($filterData['campaigns'])) {
                $parsedFilterData['campaigns'] = array_map('intval', $filterData['campaigns']);
            }
        }

        if (isset($filterData['statuses'])) {
            if (is_array($filterData['statuses'])) {
                $parsedFilterData['statuses'] = array_map('intval', $filterData['statuses']);
            }
        }

        return $parsedFilterData;
    }

    private static function _formatNumber($value, $useColor = false, $decimals = 2)
    {
        if ($value >= StatsLogic::VERY_BIG_VALUE) {
            return '-';
        }

        $str = number_format($value, $decimals, ',', ' ');

        if ($useColor) {
            if ($value < 0) {
                $style = 'color:rgb(195, 81, 81)';
            } elseif ($value > 0) {
                $style = 'color:green';
            } else {
                $style = '';
            }
            $str = '<span style="' . $style . '">' . $str . '</span>';
        }

        return $str;
    }
}

