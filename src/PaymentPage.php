<?php

namespace gtxpoint;

use Exception;

/**
 * Payment page URL Builder
 */
class PaymentPage
{
    const PATH_PAYMENT = 'payment';

    /**
     * Signature Handler
     *
     * @var SignatureHandler $signatureHandler
     */
    private $signatureHandler;

    /**
     * Encryptor
     *
     * @var Encryptor $encryptor
     */
    private $encryptor;

    /**
     * @param SignatureHandler $signatureHandler
     */
    public function __construct(SignatureHandler $signatureHandler)
    {
        $this->signatureHandler = $signatureHandler;
    }

    /**
     * @param Encryptor $encryptor
     * @return $this
     */
    public function setEncryptor(Encryptor $encryptor): self
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    /**
     * Get full URL for payment
     *
     * @param string $baseUrl
     * @param Payment $payment
     *
     * @return string
     * @throws Exception
     */
    public function getUrl(string $baseUrl, Payment $payment): string
    {
        $query = http_build_query($payment->getParams());
        $signature = urlencode($this->signatureHandler->sign($payment->getParams()));
        $pathWithQuery = self::PATH_PAYMENT . '?'. $query . '&signature=' . $signature;

        if ($this->encryptor) {
            $pathWithQuery = $payment->getProjectId() . '/' . $this->encryptor->safeEncrypt($pathWithQuery);
        }

        return $this->getNormalizedBaseURL($baseUrl) . $pathWithQuery;
    }

    /**
     * @param string $baseUrl
     * @return string
     */
    private function getNormalizedBaseURL(string $baseUrl): string
    {
        $regexp = sprintf('/\/%s$/', self::PATH_PAYMENT);

        if (preg_match($regexp, $baseUrl)) {
            $baseUrl = preg_replace($regexp, '/', $baseUrl);
        }

        return $baseUrl;
    }
}
