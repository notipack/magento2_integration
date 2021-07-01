<?php

namespace Notipack\Integration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Notipack\Integration\Helper\Data;

class SendOrderData implements ObserverInterface
{
    protected $imageHelper;
    protected $notiData;

    public function __construct(Data $notiData)
    {
        $this->notiData = $notiData;
    }

    public function execute(Observer $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof AbstractModel) {
            if($order->getState() === 'complete' && (int)$this->notiData->getOrDefault('active', 0) === 1) {
                $this->notiData->updateApiData($this->notiData->getOrDefault('api_key', ''));
                $urls = json_decode(base64_decode($this->notiData->getOrDefault('notification_urls', null)), true);
                $product = $order->getAllItems()[0]->getProduct();
                $data = [
                    'thumbnail' => $product->getMediaGalleryImages()->getFirstItem()->getData('url'),
                    'buyer_name' => $order->getBillingAddress()->getFirstname(),
                    'product_name' => $product->getName(),
                    'product_link' => $product->getProductUrl()
                ];
                foreach ($urls as $url) {
                    if ($url['type'] === 'LATEST_CONVERSION') {
                        $this->sendOrderData($url['notification_key'], $data);
                    }
                }
            }
        }
        return $this;
    }

    private function sendOrderData($url, $data)
    {
        $apiReq = curl_init($url);
        curl_setopt_array($apiReq, [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => 1,
        ]);
        curl_exec($apiReq);
    }

}
