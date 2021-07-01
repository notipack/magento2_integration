<?php

namespace Notipack\Integration\Block;

use Magento\Framework\View\Element\Template;
use Notipack\Integration\Helper\Data;

class Pixel extends Template
{

    protected $notiData;

    public function __construct(Template\Context $context, Data $notiData, array $data = [])
    {
        $this->notiData = $notiData;
        parent::__construct($context, $data);
    }

    public function isActive() {
        return $this->notiData->getOrDefault('active', 0);
    }

    public function getPixelCode() {
        return $this->notiData->getOrDefault('pixel_code', '');
    }

}
