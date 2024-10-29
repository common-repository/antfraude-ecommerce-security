<?php
class ATFR_AntFraudeJwtToken
{
    public function token()
    {
        $key = "vs-w4*gCzDK:{b84";

        // header token
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        $header = json_encode( $header );
        $header = base64_encode( $header );

        // Token Payload
        $payload = [
            'site' => 'https://app.antfraude.com',
            'iss'  => 'antfraude'
        ];
        $payload = json_encode($payload);
        $payload = base64_encode($payload);

        // Token signature
        $signature = hash_hmac('sha256', $header. "." . $payload, $key, true);
        $sign      = base64_encode($signature);
        $token     = $header . '.' . $payload . '.' . $sign;
        
        return $token;    
    }
}