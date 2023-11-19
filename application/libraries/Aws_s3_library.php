<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

class Aws_s3_library
{
    protected $CI;
    private $credentials;
    private $bucket;
    private $s3_client;

    public function __construct($params)
    {
        $this->CI = & get_instance();
        $this->CI->load->library('email_library');

        $this->bucket = $params['bucket_name'];
        $this->credentials = new Credentials($this->CI->config->item('mm8_aws_access_key_id'), $this->CI->config->item('mm8_aws_secret_access_key'));

        $this->s3_client = new S3Client([
            'version' => 'latest',
            'region' => isset($params['region']) ? $params['region'] : $this->CI->config->item('mm8_aws_region'),
            'credentials' => $this->credentials
        ]);
    }

    public function put_object($filepath, $filename, $description = "", $wait = false)
    {
        try {
            $dataset = [
                'Bucket' => $this->bucket,
                'Key' => $filename,
                'SourceFile' => $filepath,
            ];

            if (!empty($description)) {
                $dataset['Metadata'] = ['description' => $description];
            }

            $result = $this->s3_client->putObject($dataset);


            if ($wait) {
                $this->s3_client->waitUntil('ObjectExists', [
                    'Bucket' => $this->bucket,
                    'Key' => $filename
                ]);
            }

            return $result['ObjectURL'];
        } catch (S3Exception $e) {
            $error_message = "Filepath: " . $filepath . "<br/>";
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message = "Filepath: " . $filepath . "<br/>";
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return false;
    }

    public function get_object_url($filename)
    {
        try {
            //check if object exists
            if ($this->does_object_exists($filename)) {
                return $this->s3_client->getObjectUrl($this->bucket, $filename);
            } else {
                return false;
            }
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function does_object_exists($filename)
    {
        try {
            return $this->s3_client->doesObjectExist($this->bucket, $filename);
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function delete_object($filename)
    {
        try {
            $dataset = [
                'Bucket' => $this->bucket,
                'Key' => $filename
            ];

            $this->s3_client->deleteObject($dataset);
            return true;
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function read_s3_stream($filename)
    {
        try {
            $this->s3_client->registerStreamWrapper();

            $contents = '';
            if ($stream = fopen('s3://' . $this->bucket . '/' . $filename, 'r')) {
                // While the stream is still open
                while (!feof($stream)) {
                    // Read 1,024 bytes from the stream
                    $contents .= fread($stream, 1024);
                }
                // Be sure to close the stream resource when you're done with it
                fclose($stream);
            }
            return $contents;
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function get_object_stream_url($filename)
    {
        try {
            $this->s3_client->registerStreamWrapper();
            //check if object exists
            if ($this->does_object_exists($filename)) {
                return 's3://' . $this->bucket . '/' . $filename;
            } else {
                return false;
            }
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function put_object_stream_to_file($filename, $localfile_name)
    {
        try {
            $fp = fopen($localfile_name, "wb");
            if (($object = $this->s3_client->getObject(['Bucket' => $this->bucket, 'Key' => $filename, 'SaveAs' => $fp])) !== false) {
                return true;
            } else {
                return false;
            }
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
        return false;
    }

    public function list_objects($prefix = null)
    {
        $output = [];
        $error_message = '';
        
        try {
            $params = [
                'Bucket' => $this->bucket,
            ];
            if (!empty($prefix)) {
                $params['Prefix'] = $prefix; // selected directory only
            }
            $results = $this->s3_client->getPaginator('ListObjects', $params);
            foreach ($results as $result) {
                foreach ((array) $result['Contents'] as $object) {
                    $output[] =  $object['Key'];
                }
            }
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . __FILE__ . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . __FILE__ . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return $output;
    } // list objects

    public function list_objects_with_attributes($prefix = null)
    {
        $output = [];
        $error_message = '';
        
        try {
            $params = [
                'Bucket' => $this->bucket,
            ];
            if (!empty($prefix)) {
                $params['Prefix'] = $prefix; // selected directory only
            }
            $results = $this->s3_client->getPaginator('ListObjects', $params);
            foreach ($results as $result) {
                foreach ((array) $result['Contents'] as $object) {
                    $output[] =  $object;
                }
            }
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . __FILE__ . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . __FILE__ . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }

        return $output;
    } // list objects

    public function copy_object($source, $destination)
    {
        $error_message = '';

        try {
            $this->s3_client->copyObject([
                'Bucket'     => $this->bucket,
                'Key'        => $destination,
                'CopySource' => $this->bucket . "/" . $source,
            ]);
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . __FILE__ . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . __FILE__ . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
    } // copy objects

    /*
    *
    * create temporary public access to S3 private bucket object
    * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-presigned-url.html
    *
    * Need to adjust S3 bucket CORS to allow playing video file or render pdf
    * https://docs.aws.amazon.com/AmazonS3/latest/dev/cors.html#how-do-i-enable-cors
    * Bucket >> Permissions >> CORS Configuration
    * <CORSConfiguration>
    *  <CORSRule>
    *    <AllowedOrigin>https://local-utilihub.io</AllowedOrigin>
    *    <AllowedMethod>GET</AllowedMethod>
    *  </CORSRule>
    * </CORSConfiguration>
    */
    public function create_presigned_request($filename, $minutes)
    {
        try {
            $cmd = $this->s3_client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $filename,
            ]);

            $request = $this->s3_client->createPresignedRequest($cmd, "+" . $minutes . " minutes");
            return (string)$request->getUri();
        } catch (S3Exception $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getMessage();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        } catch (AwsException $e) {
            $error_message .= "Filename: " . $filename . "<br/>";
            $error_message .= "Error code: " . $e->getAwsErrorCode() . "<br/>";
            $error_message .= "Error: " . $e->getAwsErrorType();
            $this->CI->email_library->notify_system_failure($error_message, $this->CI->config->item('mm8_api_error_reporting_email'));
        }
    }
}
