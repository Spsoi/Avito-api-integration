<?php

namespace App\Controllers\Crm\Avito\Oauth;

class Redirect extends \Core\Controllers\Authenticate
{
    public function oauth()
    {
        return view('oauth');
    }

    public function fetchCode()
    {
        $validator = validator($this->request->input(), [
            'code'        => 'default:|string',
            'referer'     => 'default:|string',
            'state'       => 'default:|string',
            'from_widget' => 'optional',
            'error'       => 'optional'
        ]);
        if (!$validator->isValid()) {
            ajaxError('Bad request', 400);
        }
        $data = $validator->data();
        if ($data->error) {
            ajaxError($data->error, 200);
        }    
        $data = app('client')->crm()->fetchAccessToken($data->code);
        ajaxSuccess();
    }

}