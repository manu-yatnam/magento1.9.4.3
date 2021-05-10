<?php
/**
 * Created by PhpStorm.
 * User: joel
 * Date: 20/12/2016
 * Time: 21:25
 */
namespace Magenest\Worldpay\Model\Source;

use Magenest\Worldpay\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

class ProfileStatus extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    public static function getOptionArray()
    {
        return [
            Config::STATUS_ACTIVE => __('Active'),
            Config::STATUS_CANCELLED => __('Cancelled'),
            Config::STATUS_EXPIRED => __('Expired')
        ];
    }

    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
