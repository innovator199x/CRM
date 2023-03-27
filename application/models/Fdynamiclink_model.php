<?php

class Fdynamiclink_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    private function getFDynamicLink_ApiKey($country_id) {
        if ((int) $country_id === 1) {
            return "AIzaSyB88Wb3cS0dxCVED3a7T5pj_Sf1vfvHYlY";
        } elseif ((int) $country_id === 2) {
            return "AIzaSyA9EQfCyG6NprM6ws1JXoh83DDKBkWoBjY";
        }
    }

    private function getFDynamicLink_DomainUriPrefix($country_id) {
        if ((int) $country_id === 1) {
            return "https://url.sats.com.au";
        } elseif ((int) $country_id === 2) {
            return "https://url.sats.co.nz";
        }
    }

    private function getFDynamicLink_DomainUriPrefix_v2($country_id) {
        if ((int) $country_id === 1) {
            return "http://link.sats.com.au";
        } elseif ((int) $country_id === 2) {
            return "http://link.sats.co.nz";
        }
    }

    public function getFDynamicLink($link) {
        $country_id = $this->config->item('country');

        $params = [
            "dynamicLinkInfo" => [
                "domainUriPrefix" => $this->getFDynamicLink_DomainUriPrefix($country_id),
                "link" => $link
            ]
        ];

        $url = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=" . $this->getFDynamicLink_ApiKey($country_id);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        // Set HTTP Header for POST request 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params))
        );
        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

        $dynamic_link = json_decode($result, true);
        if (isset($dynamic_link['shortLink'])) {
            return $dynamic_link['shortLink'];
        } else {
            return false;
        }
    }

    public function getFDynamicLink_v2($link) {
        $country_id = $this->config->item('country');

        $params = [
            "dynamicLinkInfo" => [
                "domainUriPrefix" => $this->getFDynamicLink_DomainUriPrefix_v2($country_id),
                "link" => $link
            ]
        ];

        $url = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=" . $this->getFDynamicLink_ApiKey($country_id);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        // Set HTTP Header for POST request 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params))
        );
        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

        $dynamic_link = json_decode($result, true);
        if (isset($dynamic_link['shortLink'])) {
            return $dynamic_link['shortLink'];
        } else {
            return false;
        }
    }

}
