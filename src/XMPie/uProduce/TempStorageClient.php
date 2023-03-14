<?php

namespace App\XMPie\uProduce;

use SoapFault;

class TempStorageClient extends BaseClient
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Uploads a file to the uProduce Server and returns the token
     * Returns the file tokens as string
     *
     * @param $filename
     * @param $data
     * @return false|string
     * @throws SoapFault
     */
    public function uploadToStorageFile($filename, $data): bool|string
    {
        $Request = $this->RequestFabricator->TempStorage_SSP()->CreateFile()->setInFileName($filename);
        $Response = $this->ServiceFabricator->TempStorage_SSP()->CreateFile($Request);
        $token = $Response->getCreateFileResult();

        if (!$token) {
            return false;
        }

        $Request = $this->RequestFabricator->TempStorage_SSP()->AppendFileBinaryStream()->setInFileToken($token)->setInBinaryStream($data);
        $Response = $this->ServiceFabricator->TempStorage_SSP()->AppendFileBinaryStream($Request);
        $result = $Response->getAppendFileBinaryStreamResult();

        if ($result) {
            return $token;
        } else {
            return false;
        }
    }

    /**
     * Uploads a file to the uProduce Server and stores it in a folder
     * If no $folderToken is supplied, will create a new folder
     * Returns the folder and file tokens as an associated array
     *
     * @param $filename
     * @param $data
     * @param null $tokenFolder
     * @return bool|array
     * @throws SoapFault
     */
    public function uploadToStorageFileInFolder($filename, $data, $tokenFolder = null): bool|array
    {
        if (!$tokenFolder) {
            $Request = $this->RequestFabricator->TempStorage_SSP()->CreateFolder();
            $Response = $this->ServiceFabricator->TempStorage_SSP()->CreateFolder($Request);
            $tokenFolder = $Response->getCreateFolderResult();
        }

        if (!$tokenFolder) {
            return false;
        }

        $Request = $this->RequestFabricator->TempStorage_SSP()->AddFileToFolder()->setInFolderToken($tokenFolder)->setInFileName($filename);
        $Response = $this->ServiceFabricator->TempStorage_SSP()->AddFileToFolder($Request);
        $tokenFile = $Response->getAddFileToFolderResult();

        if (!$tokenFile) {
            return false;
        }

        $Request = $this->RequestFabricator->TempStorage_SSP()->AppendFileBinaryStream()->setInFileToken($tokenFile)->setInBinaryStream($data);
        $Response = $this->ServiceFabricator->TempStorage_SSP()->AppendFileBinaryStream($Request);
        $result = $Response->getAppendFileBinaryStreamResult();

        if ($result) {
            return [
                'folder_token' => $tokenFolder,
                'file_token' => $tokenFile,
            ];
        } else {
            return false;
        }
    }

    /**
     * Delete TmpStorage token for a folder
     *
     * @param $token
     * @return bool|null
     * @throws SoapFault
     */
    public function deleteTmpStorageFolder($token): ?bool
    {
        $Request = $this->RequestFabricator->TempStorage_SSP()->DeleteFolder()->setInFolderToken($token);
        $Response = $this->ServiceFabricator->TempStorage_SSP()->DeleteFolder($Request);

        return $Response->getDeleteFolderResult();
    }

    /**
     * Delete TmpStorage token for a file
     *
     * @param $token
     * @return bool|null
     * @throws SoapFault
     */
    public function deleteTmpStorageFile($token): ?bool
    {
        $Request = $this->RequestFabricator->TempStorage_SSP()->DeleteFile()->setInFileToken($token);
        $Response = $this->ServiceFabricator->TempStorage_SSP()->DeleteFile($Request);

        return $Response->getDeleteFileResult();
    }

    /**
     * @param $fileToken
     * @return bool
     */
    public function isValidFileToken($fileToken)
    {
        try {
            $Request = $this->RequestFabricator->TempStorage_SSP()->GetFileBinaryStreamSize()->setInFileToken($fileToken);
            $Response = $this->ServiceFabricator->TempStorage_SSP()->GetFileBinaryStreamSize($Request);
            $filesize = $Response->getGetFileBinaryStreamSizeResult();
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param $folderToken
     * @return bool
     */
    public function isValidFolderToken($folderToken): bool
    {
        try {
            $Request = $this->RequestFabricator->TempStorage_SSP()->GetFolderFiles()->setInFolderToken($folderToken);
            $Response = $this->ServiceFabricator->TempStorage_SSP()->GetFolderFiles($Request);
            $files = $Response->getGetFolderFilesResult();
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}