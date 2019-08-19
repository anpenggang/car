<?php
/**
 * @name Action_Sample
 * @desc sample action, 
 * @author
 */

use \Aliyun\OSS\OSSClient as OSSClient;

use Aliyun\OSS\Exceptions\OSSException as OSSException;

use Aliyun\Common\Exceptions\ClientException as ClientException;

use Aliyun\OSS\Models\OSSOptions as OSSOptions;

class Upload_Util{

    public static function createClient($endpoint=''){
        try{
            if(!empty($endpoint)){
                return OSSClient::factory(array(
                    'Endpoint' => $endpoint,
                    'AccessKeyId' => Upload_Conf::$keyId,
                    'AccessKeySecret' => Upload_Conf::$keySecret,
                ));
            }else{
                return OSSClient::factory(array(
                    'AccessKeyId' => Upload_Conf::$keyId,
                    'AccessKeySecret' => Upload_Conf::$keySecret,
                ));
            }
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

    public function listBuckets(OSSClient $client) {
        try{
            $buckets = $client->listBuckets();

            foreach ($buckets as $bucket) {
                echo 'Bucket: ' . $bucket->getName() . "\n";
            }
         } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

    public function createBucket(OSSClient $client, $bucketName){
        try{
                $client->createBucket(array(
                'Bucket' => $bucketName,
            ));   
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }
       
    public function getBucketAcl(OSSClient $client, $bucket){
        try{
            $acl = $client->getBucketAcl(array(
            'Bucket' => $bucket,
            ));
            $grants = $acl->getGrants();
            echo $grants[0];
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

        
    public function deleteBucket(OSSClient $client, $bucket){
        try{
            $result = $client->deleteBucket(array(
            'Bucket' => $bucket,
            ));
            return $result;
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }
         
    public function listObjects(OSSClient $client, $bucket){
        try{
            $result = $client->listObjects(array(
            'Bucket' => $bucket,
            ));
            foreach ($result->getObjectSummarys() as $summary) {
                echo 'Object key: ' . $summary->getKey() . "\n";
            }
            return $result;
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }
    
    public function putStringObject(OSSClient $client, $bucket, $key, $content){
        try{
             $result = $client->putObject(array(
                 'Bucket' => $bucket,
                 'Key' => $key,
                 'Content' => $content,
             ));
             return $result;
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

    public static function putResourceObject(OSSClient $client, $bucket, $key, $content, $size, $contenttype){
        try{
            $result = $client->putObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'Content' => $content,
                'ContentLength' => $size,
                'ContentType' => $contenttype,
                #'ContentDisposition' => $disposition,
            ));
            return $result;
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
error_log("$bucket, $key, $content, $size, $contenttype" . $ex->getMessage());
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

    public function getObject(OSSClient $client, $bucket, $key){
        try{
            $object = $client->getObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
            ));

            echo "Object: " . $object->getKey() . "\n";
            echo (string) $object;
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

    public function deleteObject(OSSClient $client, $bucket, $key){
        try{
            $result = $client->deleteObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
            ));
            
            return $result;
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }
    public function multipartUpload(){
        try{
            $fileName = '/path/to/file';
            $bucket = 'your-bucket-name';
            $key = 'your-object-key';

            $partSize = 5 * 1024 * 1024; // 5M for each part

            $client = OSSClient::factory(array(
                'AccessKeyId' => 'your-access-key-id',
                'AccessKeySecret' => 'your-access-key-secret',
            ));

            // Init multipart upload
            $uploadId = $client->initiateMultipartUpload(array(
                'Bucket' => $bucket,
                'Key' => $key,
            ))->getUploadId();

            // upload parts
            $fileSize = filesize($fileName);
            $partCount = (int) ($fileSize / $partSize);
            if ($fileSize % $partSize > 0) {
                $partCount += 1;
            }

            $partETags = array();
            for ($i = 0; $i < $partCount ; $i++) {
                $uploadPartSize = ($i + 1) * $partSize > $fileSize ? $fileSize - $i * $partSize : $partSize;
                $file = fopen($fileName, 'r');
                fseek($file, $i * $partSize);
                $partETag = $client->uploadPart(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'UploadId' => $uploadId,
                    'PartNumber' => $i + 1,
                    'PartSize' => $uploadPartSize,
                    'Content' => $file,
                ))->getPartETag();
                $partETags[] = $partETag;
            }

            $result =  $client->completeMultipartUpload(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'UploadId' => $uploadId,
                'PartETags' => $partETags,
            ));

            echo "Completed: " . $result->getETag();
        } catch(OSSException $ex){
            echo "OSSException: " . $ex->getErrorCode() . " Message: " . $ex->getMessage();
        } catch (ClientException $ex) {
            echo "ClientExcetpion, Message: " . $ex->getMessage();
        }
    }

}

