<?php

namespace App\Utility\Releases;


use App\Utility\Network\Connection;
use arajcany\BackblazeB2Client\BackblazeB2\Client;
use arajcany\ToolBox\Utility\TextFormatter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PhpseclibV3\SftpAdapter;
use League\Flysystem\PhpseclibV3\SftpConnectionProvider;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Throwable;
use Zaxbux\Flysystem\BackblazeB2Adapter;

/**
 * This class facilitates the uploading of Version Releases to a Remote Update Server
 */
class RemoteUpdateServer
{
    public $remote_update_url;

    public $remote_update_unc;

    public $remote_update_sftp_host;
    public $remote_update_sftp_port;
    public $remote_update_sftp_username;
    public $remote_update_sftp_password;
    public $remote_update_sftp_timeout;
    public $remote_update_sftp_path;

    public $remote_update_b2_key_id;
    public $remote_update_b2_key;
    public $remote_update_b2_bucket;

    private $filesystemUnc = null;
    private $filesystemSftp = null;
    private $filesystemB2 = null;

    public function __construct()
    {
        if (!is_file(CONFIG . 'cacert.pem')) {
            $caUrl = 'https://curl.se/ca/cacert.pem';
            $caData = file_get_contents($caUrl);
            @file_put_contents(CONFIG . 'cacert.pem', $caData);
        }

        $defaultData = [
            'remote_update_url' => null,
            'remote_update_unc' => null,

            'remote_update_sftp_host' => null,
            'remote_update_sftp_port' => null,
            'remote_update_sftp_username' => null,
            'remote_update_sftp_password' => null,
            'remote_update_sftp_timeout' => null,
            'remote_update_sftp_path' => null,

            'remote_update_b2_key_id' => null,
            'remote_update_b2_key' => null,
            'remote_update_b2_bucket' => null,
        ];

        $remoteUpdateConfigFile = CONFIG . "remote_update.json";
        $create = false;

        if (!is_file($remoteUpdateConfigFile)) {
            $create = true;
            $contents = null;
        } else {
            $contents = file_get_contents($remoteUpdateConfigFile);
        }

        if (empty($contents) || strlen($contents) === 0) {
            $create = true;
        }

        if ($contents === '{}') {
            $create = true;
        }

        if ($create) {
            $data = $defaultData;
            $contents = json_encode($defaultData, JSON_PRETTY_PRINT);
            file_put_contents($remoteUpdateConfigFile, $contents);
        } else {
            $data = json_decode($contents, JSON_OBJECT_AS_ARRAY);
            if (!$data) {
                $data = $defaultData;
            }
        }

        $this->remote_update_url = $data['remote_update_url'] ? TextFormatter::makeDirectoryTrailingForwardSlash($data['remote_update_url']) : null;

        $this->remote_update_unc = $data['remote_update_unc'] ? TextFormatter::makeDirectoryTrailingBackwardSlash($data['remote_update_unc']) : null;

        $this->remote_update_sftp_host = $data['remote_update_sftp_host'];
        $this->remote_update_sftp_port = $data['remote_update_sftp_port'];
        $this->remote_update_sftp_username = $data['remote_update_sftp_username'];
        $this->remote_update_sftp_password = $data['remote_update_sftp_password'];
        $this->remote_update_sftp_timeout = $data['remote_update_sftp_timeout'];
        $this->remote_update_sftp_path = $data['remote_update_sftp_path'] ? TextFormatter::makeDirectoryTrailingForwardSlash($data['remote_update_sftp_path']) : null;

        $this->remote_update_b2_key_id = $data['remote_update_b2_key_id'];
        $this->remote_update_b2_key = $data['remote_update_b2_key'];
        $this->remote_update_b2_bucket = $data['remote_update_b2_bucket'];
    }

    /**
     * Main function to get a Filesystem object
     *
     * @return Filesystem|false
     */
    public function getRemoteUpdateServer(): bool|Filesystem
    {
        if ($this->checkB2Server()) {
            return $this->filesystemB2;
        } elseif ($this->checkUncServer()) {
            return $this->filesystemUnc;
        } elseif ($this->checkSftpServer()) {
            return $this->filesystemSftp;
        } else {
            return false;
        }
    }

    public function checkUrlServer()
    {
        if (!$this->remote_update_url) {
            return false;
        }

        try {
            $urlPath = TextFormatter::makeDirectoryTrailingForwardSlash($this->remote_update_url);
            return Connection::checkUrlConnection($urlPath);
        } catch (Throwable $exception) {
            return false;
        }
    }

    public function checkUncServer()
    {
        if ($this->filesystemUnc) {
            return $this->filesystemUnc;
        }

        if (!$this->remote_update_unc) {
            return false;
        }

        //read test
        try {
            $adapter = new LocalFilesystemAdapter($this->remote_update_unc);
            $this->filesystemUnc = new Filesystem($adapter);
            $path = '';
            $listing = $this->filesystemUnc->listContents($path, false)->sortByPath();
        } catch (Throwable $exception) {
            $this->filesystemUnc = null;
            return false;
        }

        //write/delete test
        try {
            $rndFilename = mt_rand() . ".txt";
            $this->filesystemUnc->write($rndFilename, date("Ymd His"));
            $this->filesystemUnc->delete($rndFilename);
        } catch (Throwable $exception) {
            $this->filesystemUnc = null;
            return false;
        }

        if ($listing) {
            return $this->filesystemUnc;
        } else {
            return false;
        }
    }


    public function checkB2Server()
    {
        if ($this->filesystemB2) {
            return $this->filesystemB2;
        }

        if (!$this->remote_update_b2_key_id) {
            return false;
        }

        //BackblazeB2 Client options
        $config = [
            'applicationKeyId' => $this->remote_update_b2_key_id,
            'applicationKey' => $this->remote_update_b2_key,
            'authorizationCache' => false, //uncomment to stop using the AuthorizationCache, but why would you?
        ];

        //Guzzle options
        $guzzleConfig = [
            'verify' => CONFIG . "cacert.pem"
        ];

        $client = new Client($config, $guzzleConfig);
        $adapter = new BackblazeB2Adapter($client, $this->remote_update_b2_bucket);
        $this->filesystemB2 = new Filesystem($adapter);
        $baseDir = '';
        $recursive = false;

        //read test
        try {
            $listing = $this->filesystemB2->listContents($baseDir, $recursive);

            /**
             * @var StorageAttributes $item
             */
            $paths = [];
            foreach ($listing as $item) {
                $paths[] = $item->path();
            }
        } catch (Throwable $exception) {
            $this->filesystemB2 = null;
            $listing = false;
        }

        //write/delete test
        try {
            $rndFilename = mt_rand() . ".txt";
            $this->filesystemB2->write($rndFilename, date("Ymd His"));
            $this->filesystemB2->delete($rndFilename);
        } catch (Throwable $exception) {
            $this->filesystemB2 = null;
            return false;
        }


        if ($listing) {
            return $this->filesystemB2;
        } else {
            return false;
        }
    }

    public function checkSftpServer()
    {
        if ($this->filesystemSftp) {
            return $this->filesystemSftp;
        }

        if (!$this->remote_update_sftp_host) {
            return false;
        }

        // host (required)
        $host = $this->remote_update_sftp_host;
        // username (required)
        $username = $this->remote_update_sftp_username;
        // password (optional, default: null) set to null if privateKey is used
        $password = $this->remote_update_sftp_password;
        // private key (optional, default: null) can be used instead of password, set to null if password is set
        $privateKey = null;
        // passphrase (optional, default: null), set to null if privateKey is not used or has no passphrase
        $passphrase = null;
        // port (optional, default: 22)
        $port = $this->remote_update_sftp_port ?: 22;
        // use agent (optional, default: false)
        $useAgent = false;
        // timeout (optional, default: 10)
        $timeout = $this->remote_update_sftp_timeout ?: 4;
        // max tries (optional, default: 4)
        $maxTries = 4;
        // host fingerprint (optional, default: null),
        $hostFingerprint = null;
        // connectivity checker (must be an implementation of 'League\Flysystem\PhpseclibV2\ConnectivityChecker' to check if a connection can be established (optional, omit if you don't need some special handling for setting reliable connections)
        $connectivityChecker = null;
        //root path to cwd to
        $root = $this->remote_update_sftp_path;

        //read test
        try {
            $adapter = new SftpAdapter(
                new SftpConnectionProvider(
                    $host,
                    $username,
                    $password,
                    $privateKey,
                    $passphrase,
                    $port,
                    $useAgent,
                    $timeout,
                    $maxTries,
                    $hostFingerprint,
                    $connectivityChecker
                ),
                $root,
                PortableVisibilityConverter::fromArray([
                    'file' => [
                        'public' => 0640,
                        'private' => 0604,
                    ],
                    'dir' => [
                        'public' => 0740,
                        'private' => 7604,
                    ],
                ])
            );
            $this->filesystemSftp = new Filesystem($adapter);

            $path = '';
            $listing = $this->filesystemSftp->listContents($path, false)->sortByPath();

        } catch (Throwable $exception) {
            $this->filesystemSftp = null;
            return false;
        }

        //write/delete test
        try {
            $rndFilename = mt_rand() . ".txt";
            $this->filesystemSftp->write($rndFilename, date("Ymd His"));
            $this->filesystemSftp->delete($rndFilename);
        } catch (Throwable $exception) {
            $this->filesystemSftp = null;
            return false;
        }

        if ($listing) {
            return $this->filesystemSftp;
        } else {
            return false;
        }
    }


}
