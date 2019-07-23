<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace WirecardTest\PaymentSdk\Entity;

use Wirecard\PaymentSdk\Constant\IsoTransactionType;
use Wirecard\PaymentSdk\Constant\RiskInfoAvailability;
use Wirecard\PaymentSdk\Constant\RiskInfoDeliveryTimeFrame;
use Wirecard\PaymentSdk\Constant\RiskInfoReorder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\MerchantRiskIndicator;
use Wirecard\PaymentSdk\Exception\NotImplementedException;

class MerchantRiskIndicatorUTest extends \PHPUnit_Framework_TestCase
{
    public function testSeamlessMappingWithGiftCardCountTooLarge()
    {
        $merchantRiskIndicator = new MerchantRiskIndicator();

        $this->expectException(\InvalidArgumentException::class);

        $merchantRiskIndicator->setGiftCardCount(133);
    }

    public function testSeamlessMappingWithGiftCardCountTooSmall()
    {
        $merchantRiskIndicator = new MerchantRiskIndicator();

        $this->expectException(\InvalidArgumentException::class);

        $merchantRiskIndicator->setGiftCardCount(0);
    }

    public function testSeamlessMappingWithAllFields()
    {
        $merchantRiskIndicator = new MerchantRiskIndicator();
        $giftCardCount         = 13;
        $mail                  = 'max.muster@mail.com';
        $giftAmount            = new Amount(143.78, 'EUR');
        $date                  = new \DateTime();

        $expected = [
            'risk_info_delivery_timeframe'   => RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY,
            'risk_info_delivery_mail'        => $mail,
            'risk_info_reorder_items'        => RiskInfoReorder::FIRST_TIME_ORDERED,
            'risk_info_availability'         => RiskInfoAvailability::MERCHANDISE_AVAILABLE,
            'risk_info_preorder_date'        => $date->format(MerchantRiskIndicator::DATE_FORMAT),
            'risk_info_gift_amount'          => 143,
            'risk_info_gift_amount_currency' => $giftAmount->getCurrency(),
            'risk_info_gift_card_count'      => $giftCardCount,
            'iso_transaction_type'           => IsoTransactionType::CHECK_ACCEPTANCE,
        ];

        $merchantRiskIndicator->setAvailability(RiskInfoAvailability::MERCHANDISE_AVAILABLE);
        $merchantRiskIndicator->setDeliveryEmailAddress($mail);
        $merchantRiskIndicator->setDeliveryTimeFrame(RiskInfoDeliveryTimeFrame::ELECTRONIC_DELIVERY);
        $merchantRiskIndicator->setGiftAmount($giftAmount);
        $merchantRiskIndicator->setGiftCardCount($giftCardCount);
        $merchantRiskIndicator->setIsoTransactionType(IsoTransactionType::CHECK_ACCEPTANCE);
        $merchantRiskIndicator->setPreOrderDate($date);
        $merchantRiskIndicator->setReorderItems(RiskInfoReorder::FIRST_TIME_ORDERED);

        $this->assertEquals($expected, $merchantRiskIndicator->mappedSeamlessProperties());
    }

    public function testMappingNotSupported()
    {
        $cardHolderAccount = new MerchantRiskIndicator();

        $this->expectException(NotImplementedException::class);
        $cardHolderAccount->mappedProperties();
    }
}
