<?php
namespace FABtotum\CamWebApp;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\ServerException as HttpServerException;

class Client {


    /**
     * @var base url for CAM api calls
     */
    protected $url = 'http://app.fabtotum.com/api/';
    /**
     * @var http client instance
     */
    protected $client;
    /**
     * @var API access token
     */
    protected $access_token;
    /**
     * @var Authorized request headers
     */
    private $headers;
    /**
     * @var Authorized request headers
     */
    private $error_message;
    /**
     * @var Authorized request headers
     */
    private $error_code;

    /**
     * Construct a new CAMWebApp client
     *
     * @param $url API base url. URL must end with a dash '/'
     */
    public function __construct($url = null, $access_token = null)
    {
        if(!is_null($url))
        {
            $this->url = $url;
        }

        $this->access_token = $access_token;
        $this->headers = [];
        $this->error_message = '';
        $this->error_code = 0;
        
        $this->client = new HttpClient([
            'base_uri' => $this->url
        ]);
    }


    /**
     * Login to CAM server an retreive access token
     *
     * @param $fabid        User email
     * @param $password     User password
     * @param $subscription Subscription code
     * @return true on success, false otherwise
     */
    private function __login($fabid, $subscription)
    {
        $response = $this->client->post('token', [
            'form_params' => [
                'grant_type' => 'password',
                'fabid' => $fabid,
                'password' => '***',
                'subscription' => $subscription
            ],
        ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            // Store access token
            $this->access_token = $data['access_token'];
            // Store authorization headers
            $this->headers = [
                'Authorization' => 'Bearer ' . $this->access_token,        
                'Accept'        => 'application/json',
            ];

            return true;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
            $this->error_code = $response->getStatusCode();
        }

        return false;
    }

    /**
     * Forget the access token
     */
    public function logout()
    {
        $this->access_token = '';
        $this->headers = [
            'Accept'        => 'application/json',
        ];
    }

    /**
     * Return the current token string
     *
     * @return access token string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Set the access token
     *
     * @return void
     */
    public function setAccessToken($token)
    {
        $this->access_token = $token;
    }

    /**
     * Return the current token string
     *
     * @return access token string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * Return the current token string
     *
     * @return access token string
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }



    /**
     * Check login status
     *
     * @return true if logged in, false otherwise
     */
    public function isLoggedIn()
    {
        return !is_null($this->access_token);
    }

    /**
     * Get user tasks list
     *
     * @return array of tasks
     */
    private function __getTasks()
    {
        $response = $this->client->request('GET', 'tasks', [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
            $this->error_code = $response->getStatusCode();
        }
        
        return [];
    }

    /**
     * Get user task
     *
     * @return Task
     */
    private function __getTask($taskId)
    {
        $response = $this->client->request('GET', 'tasks/' . $taskId, [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        $this->error_code = $response->getStatusCode();

        return null;
    }

    /**
     * Create new task
     */
    private function __newTask($app_name)
    {
        $response = $this->client->request('POST', 'tasks', [
                'headers' => $this->headers,
                'form_params' => [
                    'application' => $app_name
                ]
            ]);

        if( $response->getStatusCode() == 201 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data['id'];
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
            $this->error_code = $response->getStatusCode();
        }

        return null;
    }

    /**
     * Delete an existing task.
     *
     * @return array
     */
    private function __deleteTask($taskId)
    {
        $response = $this->client->request('DELETE', 'tasks/' . $taskId, [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return true;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        $this->error_code = $response->getStatusCode();

        return false;
    }

    /**
     * Start a task.
     *
     * @return array
     */
    private function __startTask($taskId)
    {
        $response = $this->client->request('POST', 'tasks/'.$taskId.'/start', [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return true;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        $this->error_code = $response->getStatusCode();

        return false;
    }

    /**
     * Abort a task.
     *
     * @return array
     */
    private function __abortTask($taskId)
    {
        $response = $this->client->request('POST', 'tasks/'.$taskId.'/abort', [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return true;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        $this->error_code = $response->getStatusCode();

        return false;
    }

    /**
     * Get list of applications
     *
     * @return array
     */
    private function __getApplications()
    {
        $response = $this->client->request('GET', 'apps', [
                'headers' => $this->headers
            ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    private function __getConfigs($appId)
    {
        $response = $this->client->request('GET', 'apps/'.$appId.'/configs', [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    private function __getConfig($appId, $configId)
    {
        $response = $this->client->request('GET', 'apps/'.$appId.'/configs/'.$configId, [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            //$data = json_decode((string) $response->getBody(), true);
            //return $data;
          return $response->getBody();
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    private function __getSchema($appId)
    {
        $response = $this->client->request('GET', 'apps/'.$appId.'/schema', [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            //$data = json_decode((string) $response->getBody(), true);
            //return $data;
          return $response->getBody();
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    private function __getUISchema($appId)
    {
        $response = $this->client->request('GET', 'apps/'.$appId.'/ui-schema', [
                'headers' => $this->headers
            ]);

        if( $response->getStatusCode() == 200 )
        {
            //$data = json_decode((string) $response->getBody(), true);
            //return $data;
          return $response->getBody();
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    /**
     * Private uploadFile function
     * 
     * @param fileType File type (CONFIG, INPUT, PREVIEW...)
     *
     * @return array
     */
    private function uploadFile($taskId, $filename, $fileType)
    {
        $ctype = 'application/octet-stream';
        if($fileType == 'CONFIG')
        {
            $ctype = 'application/json';
        }

        $response = $this->client->request('POST', 'tasks/'.$taskId.'/files', [
             'headers' => $this->headers,
            //indicates multipart form
            'multipart' => [
              [
                'name'     => $fileType,
                'filename' => basename($filename),
                'contents' => fopen($filename, 'r'),
                'headers'  => [ 'Content-Type' => $ctype],
              ],
            ]
          ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 201 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data['id'];
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    /**
     * Update content of an existing file.
     *
     * @return array
     */
    public function __updateFileContent($taskId, $fileId, $content)
    {

        $response = $this->client->request('POST', 'tasks/'.$taskId.'/files/'. $fileId, [
             'headers' => $this->headers,
            //indicates multipart form
            'multipart' => [
              [
                'name'     => 'UPDATE',
                'contents' => $content,
              ],
            ]
          ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 201 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data['id'];
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }

    /**
     * Update existing file
     * 
     * @param taskId    File owner task
     * @param fileId    File ID
     * @param fileName  Local filename
     * 
     * @return array
     */
    public function __updateFile($taskId, $fileId, $fileName)
    {
        $content = fopen($fileName, 'r');
        return $this->updateFileContent($taskId, $fileId, $content );
    }

    /**
     * Upload a config file to the task
     *
     * @return array
     */
    public function __uploadConfigFile($taskId, $fileName)
    {
        return $this->uploadFile($taskId, $fileName, 'CONFIG');
    }

    /**
     * Upload an input file to the task
     *
     * @return array
     */
    public function __uploadInputFile($taskId, $fileName)
    {
        return $this->uploadFile($taskId, $fileName, 'INPUT');
    }

    /**
     * Get task files.
     *
     * @return array
     */
    public function __getFiles($taskId)
    {
        $response = $this->client->request('GET', 'tasks/'.$taskId.'/files', [
                'headers' => $this->headers
            ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }
        
        return [];
    }

    public function __getFile($taskId, $fileId)
    {
        $response = $this->client->request('GET', 'tasks/'.$taskId.'/files/'.$fileId, [
                'headers' => $this->headers
            ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }
        
        return null;
    }

    /**
     * Delete a task file.
     *
     * @return array
     */
    public function __deleteFile($taskId, $fileId)
    {
        $response = $this->client->request('DELETE', 'tasks/'.$taskId.'/files/'.$fileId, [
                'headers' => $this->headers
            ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $data['id'];
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }
    /**
     * Download a task file.
     *
     * @return array
     */
    public function __downloadFile($taskId, $fileId, $destPath)
    {

        $meta = $this->__getFile($taskId, $fileId);
        if(is_null($meta))
            return null;

        $fileName = $destPath . '/' . $meta['filename'];

        if(!file_exists($destPath))
        {
            mkdir($destPath, 0755, true);
        }
        
        $response = $this->client->request('GET', 'tasks/'.$taskId.'/files/'.$fileId.'/download', [
            'headers' => $this->headers,
            'sink' => $fileName
        ]);

        $this->error_code = $response->getStatusCode();
        $this->error_message = '';

        if( $response->getStatusCode() == 200 )
        {
            $data = json_decode((string) $response->getBody(), true);
            return $fileName;
        }
        else
        {
            $data = json_decode((string) $response->getBody(), true);
            $this->error_message = $data['error'];
        }

        return null;
    }


    private function downloadAll($taskId, $type, $destPath)
    {
        $files = $this->__getFiles($taskId);

        $output = [];

        foreach($files as $file)
        {
            if( $file['type'] == $type )
            {
                $fn = $this->__downloadFile($taskId, $file['id'], $destPath);
                if(is_null($fn)) {
                    return null;
                }

                $output[] = $fn;
            }
        }

        return $output;
    }

    public function __downloadPreview($taskId, $destPath)
    {
        return $this->downloadAll($taskId, 'PREVIEW', $destPath);
    }

    public function __downloadOutput($taskId, $destPath)
    {
        return $this->downloadAll($taskId, 'OUTPUT', $destPath);
    }

    public function __downloadInput($taskId, $destPath)
    {
        return $this->downloadAll($taskId, 'INPUT', $destPath);
    }

    public function __downloadConfig($taskId, $destPath)
    {
        return $this->downloadAll($taskId, 'CONFIG', $destPath);
    }

    /**
     * Magically add HttpClientException try catch to
     * methods that can get Unauthorized exceptions.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $unauthorized_decorator = [
            'login',
            'getApplications',
            'getConfigs',
            'getConfig',
            'getSchema',
            'getUISchema',
            'getTasks',
            'getTask',
            'newTask',
            'deleteTask',
            'startTask',
            'abortTask',
            'uploadConfigFile',
            'uploadInputFile',
            'updateFile',
            'updateFileContent',
            'getFiles',
            'getFile',
            'deleteFile',
            'downloadAll',
            'downloadFile',
            'downloadPreview',
            'downloadOutput',
            'downloadInput',
            'downloadConfig'
        ];

        if (method_exists($this, '__' . $method)) {
            // Method has to use unauthorized decorator
            if( in_array($method, $unauthorized_decorator) )
            {
                try{
                    return call_user_func_array([$this, '__' . $method], $parameters);
                }
                // Client error
                catch(HttpClientException $e)
                {
                    $response = $e->getResponse();
                    $this->error_message = '';
                    try
                    {
                        $body = (string) $response->getBody();
                        $data = json_decode($body, true);
                        $this->error_message = 'server: ' . $data['error'];
                    }
                    catch(Exception $e)
                    {
                        // pass
                        $this->error_message = 'Failed parsing response';
                    }

                    $this->error_code = $response->getStatusCode();
                    //echo 'HTTP Error('. $this->error_code . '):'. $this->error_message . PHP_EOL;
                }
                // Server error
                catch(HttpServerException $e)
                {
                    $this->error_message = 'Server error';
                }
  
                error_log($this->error_message, 0);
            }
            // Normal method, no authorization needed
            else
            {
                return call_user_func_array([$this, '__' . $method], $parameters);   
            }
        }
        throw new BadMethodCallException("Method [$method] does not exist.");
    }

}
