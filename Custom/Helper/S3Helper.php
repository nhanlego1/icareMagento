<?php
/**
 * Copyright (c) 2016
 * Created by: icare-baonq
 */

/**
 * Created by PhpStorm.
 * User: baonguyen
 * Date: 9/19/16
 * Time: 2:53 PM
 */

namespace Icare\Custom\Helper;


use Aws\S3\Exception\S3Exception;
use Magento\Framework\App\Helper\Context;

class S3Helper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_variables;
    protected $_logger;

    protected $_s3Client;

    public function __construct(Context $context,
                                \Magento\Variable\Model\Variable $variables)
    {
        parent::__construct($context);
        $this->_variables = $variables;
        $this->_s3Client = \Aws\S3\S3Client::factory([
            'version' => 'latest',
            'region' => $this->getRegion(),
            'credentials' => [
                'key' => $this->getAccessKey(),
                'secret' => $this->getSecretKey()
            ]
        ]);
    }

    public function uploadFile($fileKey, $fileContent) {
        try {
            $result = $this->_s3Client->putObject([
                'Bucket' => $this->getBucket(),
                'Key'    => $fileKey,
                'Body'   => $fileContent,
                'ACL'    => 'public-read'
            ]);
            return $result['ObjectURL'];
        } catch (S3Exception $ex) {
            $this->_logger->error($ex);
        }
        return null;
    }

    public function deleteFile($key) {
        try {
            $this->_s3Client->deleteObject([
                'Bucket' => $this->getBucket(),
                'Key' =>  $key
            ]);
        } catch (S3Exception $ex) {
            $this->_logger->error($ex);
            return false;
        }
        return true;
    }

    public function getAccessKey()
    {
        return $this->_variables->loadByCode('aws_access_key')->getPlainValue();
    }
    public function getSecretKey()
    {
        return $this->_variables->loadByCode('aws_secret_key')->getPlainValue();
    }
    public function getRegion()
    {
        return $this->_variables->loadByCode('aws_s3_region')->getPlainValue();
    }
    public function getBucket()
    {
        return $this->_variables->loadByCode('aws_s3_bucket')->getPlainValue();
    }

    public function isS3Usage() {
        if ($this->_variables->loadByCode('aws_s3_is_used')->getPlainValue() == '1') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determines whether an S3 region code is valid.
     *
     * @param string $regionInQuestion
     * @return bool
     */
    public function isValidRegion($regionInQuestion)
    {
        foreach ($this->getRegions() as $currentRegion) {
            if ($currentRegion['value'] == $regionInQuestion) {
                return true;
            }
        }
        return false;
    }
    public function getRegions()
    {
        return [
            [
                'value' => 'us-east-1',
                'label' => 'US East (N. Virginia)'
            ],
            [
                'value' => 'us-west-2',
                'label' => 'US West (Oregon)'
            ],
            [
                'value' => 'us-west-1',
                'label' => 'US West (N. California)'
            ],
            [
                'value' => 'eu-west-1',
                'label' => 'EU (Ireland)'
            ],
            [
                'value' => 'eu-central-1',
                'label' => 'EU (Frankfurt)'
            ],
            [
                'value' => 'ap-southeast-1',
                'label' => 'Asia Pacific (Singapore)'
            ],
            [
                'value' => 'ap-northeast-1',
                'label' => 'Asia Pacific (Tokyo)'
            ],
            [
                'value' => 'ap-southeast-2',
                'label' => 'Asia Pacific (Sydney)'
            ],
            [
                'value' => 'ap-northeast-2',
                'label' => 'Asia Pacific (Seoul)'
            ],
            [
                'value' => 'sa-east-1',
                'label' => 'South America (Sao Paulo)'
            ]
        ];
    }
}