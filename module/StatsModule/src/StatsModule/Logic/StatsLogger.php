<?php
namespace StatsModule\Logic;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\ResultSet;

class StatsLogger implements ServiceLocatorAwareInterface
{
    protected $_serviceLocator;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    public function getVkSummaryData($adIds, $fromTime, $toTime)
    {
        $adIds    = array_map('intval', $adIds);
        $fromTime = (int) $fromTime;
        $toTime   = (int) $toTime;
        $adIdsStr = implode(', ', $adIds);

        if (empty($adIds)) {
            return [];
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $rc = $dbAdapter->query(
            "SELECT ad_id,
                MAX(impressions) - MIN(impressions) AS impressions,
                MAX(clicks) - MIN(clicks) AS clicks,
                MAX(spent) - MIN(spent) AS spent
             FROM statsLogVk
             WHERE ad_id IN ($adIdsStr) AND (ts BETWEEN $fromTime and $toTime )
             GROUP BY ad_id
            ",
            $dbAdapter::QUERY_MODE_EXECUTE
        );

        $resultSet = new ResultSet;
        $resultSet->initialize($rc);
        return $resultSet;
    }

    public function saveVkData($data)
    {
        $ts = time();
        $valuesStrings = [];

        foreach ($data as $object) {
            if (($object->type === 'ad') && isset($object->stats[0])) {
                $stats = get_object_vars($object->stats[0]);
                $valuesStrings[] = '('
                    . $object->id . ', '
                    . $ts . ', '
                    . (isset($stats['price']) ? $stats['price'] : '0.00') . ', '
                    . (isset($stats['impressions']) ? $stats['impressions'] : 0) . ', '
                    . (isset($stats['clicks']) ? $stats['clicks'] : 0) . ', '
                    . (isset($stats['spent']) ? $stats['spent'] : 0)
                    . ')';
            }
        }

        if (!empty($valuesStrings)) {
            $this->_saveVkDataInDb($valuesStrings);
        }
    }

    private function _saveVkDataInDb(&$valuesStrings)
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $dbAdapter->query(
            "INSERT INTO statsLogVk (ad_id, ts, price, impressions, clicks, spent) VALUES " . implode(', ', $valuesStrings),
            $dbAdapter::QUERY_MODE_EXECUTE
        );
    }
}
