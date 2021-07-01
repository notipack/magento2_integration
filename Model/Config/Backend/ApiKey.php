<?php

namespace Notipack\Integration\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Notipack\Integration\Helper\Data;
use Notipack\Integration\Model\DataFactory;

class ApiKey extends Value
{

    protected $messageManager;
    protected $notiData;

    public function __construct(Context $context,
                                Registry $registry,
                                ScopeConfigInterface $config,
                                TypeListInterface $cacheTypeList,
                                AbstractResource $resource = null,
                                AbstractDb $resourceCollection = null,
                                Data $notiData,
                                ManagerInterface $messageManager,
                                $data = []
    )
    {
        $this->notiData = $notiData;
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $postApiKey = $this->getData()['fieldset_data']['api_key'];
        switch ($this->notiData->updateApiData($postApiKey)) {
            case 0:
                $this->messageManager->addSuccessMessage(__('Klucz jest poprawny'));
                break;
            case 1:
                $this->messageManager->addErrorMessage(__('Klucz jest niepoprawny'));
                break;
            case 2:
                $this->messageManager->addErrorMessage(__('Integracja została wyłączona z powodu braku klucza'));
                break;
        }
        return parent::beforeSave();
    }

}
