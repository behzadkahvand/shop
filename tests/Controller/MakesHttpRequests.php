<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait MakesHttpRequests
{
    protected function getJson(
        $uri,
        $content = null,
        array $parameters = [],
        array $headers = []
    ): KernelBrowser {
        return $this->sendRequest('GET', $uri, $content, $parameters, $headers);
    }

    protected function postJson(
        $uri,
        $content = null,
        array $parameters = [],
        array $headers = []
    ): KernelBrowser {
        return $this->sendRequest('POST', $uri, $content, $parameters, $headers);
    }

    protected function deleteJson(
        $uri,
        $content = null,
        array $parameters = [],
        array $headers = []
    ): KernelBrowser {
        return $this->sendRequest('DELETE', $uri, $content, $parameters, $headers);
    }

    protected function putJson(
        $uri,
        $content = null,
        array $parameters = [],
        array $headers = []
    ): KernelBrowser {
        return $this->sendRequest('PUT', $uri, $content, $parameters, $headers);
    }

    protected function patchJson(
        $uri,
        $content = null,
        array $parameters = [],
        array $headers = []
    ): KernelBrowser {
        return $this->sendRequest('PATCH', $uri, $content, $parameters, $headers);
    }

    protected function sendRequest(
        $method,
        $uri,
        $content = null,
        array $parameters = [],
        array $headers = []
    ): KernelBrowser {
        $serverParams = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT'  => 'application/json',
        ];

        if ($this->shouldLogin()) {
            $token        = $this->jwtToken ?? $this->getToken($uri);
            $serverParams += ['HTTP_Authorization' => "Bearer {$token}"];
        }

        $this->client->request(
            $method,
            $uri,
            $parameters,
            [],
            array_merge($serverParams, $headers),
            json_encode($content)
        );

        return $this->client;
    }

    protected function sendMultipartRequest(
        $method,
        $uri,
        array $parameters = [],
        array $headers = [],
        array $files = []
    ): KernelBrowser {
        $serverParams = [
            'HTTP_Accept' => 'application/json',
        ];

        if ($this->shouldLogin()) {
            $token        = $this->jwtToken ?? $this->getToken($uri);
            $serverParams += ['HTTP_Authorization' => "Bearer {$token}"];
        }

        $this->client->request(
            $method,
            $uri,
            $parameters,
            $files,
            array_merge($serverParams, $headers)
        );

        return $this->client;
    }
}
