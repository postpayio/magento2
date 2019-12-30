<?php
/**
 * Copyright © 2019 Postpay Technology Limited. All rights reserved.
 */
declare(strict_types=1);

namespace Postpay\Postpay\Model;

use Magento\Framework\Phrase;
use Postpay\Postpay\Exception\PostpayConfigurationException;
use Postpay\PostpayFactory;
use Postpay\Postpay;

/**
 * Class PostpayWrapper
 * @package Postpay\Postpay\Model
 */
class PostpayWrapper implements PostpayWrapperInterface
{
    /**
     * @var Postpay
     */
    private $postpay;

    /**
     * @var PostpayFactory
     */
    private $postpayFactory;

    /**
     * @var ConfigInterface
     */
    private $configInterface;

    /**
     * PostpayWrapper constructor.
     * @param PostpayFactory $postpayFactory
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        PostpayFactory $postpayFactory,
        ConfigInterface $configInterface
    ) {
        $this->postpayFactory = $postpayFactory;
        $this->configInterface = $configInterface;
    }

    /**
     * @return Postpay
     * @throws PostpayConfigurationException
     */
    public function getPostpay(): Postpay
    {
        if(is_null($this->postpay)) {
            $isActive = $this->configInterface->getIsActive();
            $merchantId = $this->configInterface->getMerchantId();
            $secretKey = $this->configInterface->getSecretKey();
            $sandboxSecretKey = $this->configInterface->getSandboxSecretKey();
            $sandbox = $this->configInterface->getIsSandbox();

            if(!$isActive || !$merchantId || !(($secretKey && !$sandbox) || ($sandboxSecretKey && $sandbox)))  {
                throw new PostpayConfigurationException(
                    new Phrase('Unable to initiate Postpay. Please check Postpay configuration in Magento admin.')
                );
            }

            $this->postpay = $this->postpayFactory->create([
                'config' => [
                    'merchant_id' => $merchantId,
                    'secret_key' => $secretKey ? $secretKey : $sandboxSecretKey,
                    'sandbox' => $sandbox,
                    'client_handler' => 'guzzle'
                ]
            ]);
        }

        return $this->postpay;
    }
}