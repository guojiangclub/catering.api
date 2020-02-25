<?php

namespace GuoJiangClub\EC\Catering\Wechat\Server\Wx;

class WXBizDataCrypt
{
    private $appid;
    private $sessionKey;

    public function __construct($appid, $sessionKey)
    {
        $this->appid      = $appid;
        $this->sessionKey = $sessionKey;
    }

    public function decryptData($encryptedData, $iv, &$data)
    {
        if (strlen($this->sessionKey) != 24) {
            return 41001;
        }
        $aesKey = base64_decode($this->sessionKey);

        if (strlen($iv) != 24) {
            return 41002;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encryptedData);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == null) {
            return 41003;
        }
        if ($dataObj->watermark->appid != $this->appid) {
            return 41004;
        }
        $data = $result;

        return 0;
    }
}