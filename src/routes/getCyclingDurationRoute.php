<?php

$app->post('/api/MapboxDuration/getCyclingDuration', function ($request, $response) {
    /** @var \Slim\Http\Response $response */
    /** @var \Slim\Http\Request $request */
    /** @var \Models\checkRequest $checkRequest */

    $settings = $this->settings;
    $checkRequest = $this->validation;
    $validateRes = $checkRequest->validate($request, ['accessToken', 'coordinates']);
    if (!empty($validateRes) && isset($validateRes['callback']) && $validateRes['callback'] == 'error') {
        return $response->withHeader('Content-type', 'application/json')->withStatus(200)->withJson($validateRes);
    } else {
        $postData = $validateRes;
    }
    $url = $settings['apiUrl'] . '/cycling';

    $params['access_token'] = $postData['args']['accessToken'];

    $json = [];

    if (is_array($postData['args']['coordinates'])) {
        foreach ($postData['args']['coordinates'] as $key => $coordinate) {
            $coordinateArray = explode(',', $coordinate);
            $json['coordinates'][$key]['lng'] = $coordinateArray[0];
            $json['coordinates'][$key]['lat'] = $coordinateArray[1];
        }
    }
    else {
        $json['coordinates'] = array_map(
            function ($item) {
                return array_map('floatval', array_values($item));
            },
            $postData['args']['coordinates']
        );
    }

    try {
        /** @var GuzzleHttp\Client $client */
        $client = $this->httpClient;
        $vendorResponse = $client->post($url, [
            'query' => $params,
            'json' => $json
        ]);

        $vendorResponseBody = $vendorResponse->getBody()->getContents();
        if ($vendorResponse->getStatusCode() == 200) {
            $result['callback'] = 'success';
            $result['contextWrites']['to'] = json_decode($vendorResponse->getBody());
        } else {
            $result['callback'] = 'error';
            $result['contextWrites']['to']['status_code'] = 'API_ERROR';
            $result['contextWrites']['to']['status_msg'] = is_array($vendorResponseBody) ? $vendorResponseBody : json_decode($vendorResponseBody);
        }
    } catch (\GuzzleHttp\Exception\ClientException $exception) {
        $vendorResponseBody = $exception->getResponse()->getBody();
        $result['callback'] = 'error';
        $result['contextWrites']['to']['status_code'] = 'API_ERROR';
        $result['contextWrites']['to']['status_msg'] = json_decode($vendorResponseBody);
    }

    return $response->withHeader('Content-type', 'application/json')->withStatus(200)->withJson($result);
});