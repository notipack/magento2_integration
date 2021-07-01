<?php

namespace Notipack\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{

    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection, Context $context)
    {
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function updateApiData($apiKey)
    {
        $conn = $this->resourceConnection->getConnection();
        $table = $conn->getTableName('notipack_data');
        if (!empty($apiKey)) {
            $apiReq = curl_init('https://app.notipack.com/api');
            curl_setopt_array($apiReq, [
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => ['unique_id' => $apiKey],
                CURLOPT_RETURNTRANSFER => 1,
            ]);
            $apiReqOut = curl_exec($apiReq);
            $res = json_decode($apiReqOut, true);
            $this->setOrUpdate('api_key', $apiKey, $conn, $table);
            $this->setOrUpdate('last_change', time(), $conn, $table);
            if (!isset($res['errors'])) {
                $this->setOrUpdate('active', 1, $conn, $table);
                $this->setOrUpdate('pixel_code', $res['data']['pixel_code'], $conn, $table);
                $this->setOrUpdate('notification_urls', base64_encode(json_encode($res['data']['notification_url'])), $conn, $table);
                return 0;
            }
            return 1;
        }
        $this->setOrUpdate('active', 0, $conn, $table);
        $this->setOrUpdate('last_change', time(), $conn, $table);
        return 2;
    }

    public function getOrDefault($key, $default)
    {
        $conn = $this->resourceConnection->getConnection();
        $out = $conn->fetchAll("SELECT * FROM notipack_data WHERE `key` = '".$key."'");
        return count($out) >= 1 ? $out[0]['value'] : $default;
    }

    public function setOrUpdate($key, $value)
    {
        $conn = $this->resourceConnection->getConnection();
        $out = $conn->fetchAll("SELECT * FROM notipack_data WHERE `key` = '".$key."'");
        if (count($out) === 0) {
            $conn->query("INSERT INTO notipack_data (`id`, `key`, `value`) VALUES (NULL, '".$key."', '".$value."')");
        } else {
            $conn->query("UPDATE notipack_data SET `value` = '".$value."' WHERE `key` = '".$key."'");
        }
    }

}
