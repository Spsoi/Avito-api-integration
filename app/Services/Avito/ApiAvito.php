<?php 

namespace App\Services\Avito;

use App\Services\Avito\QueryRequest;
use Classes\Avito\Token;

class ApiAvito
{
    const URLAVITO = 'https://api.avito.ru/job';
    const URLSERVER = 'https://test';
    const SECRET = 'secret';

    public function __construct(){
        $this->l = logger('crm/AvitoQueryRequest.log');
    }

    //Подписка на уведомления о создании и обновлении откликов на вакансии Исключение:
    public function subscribeWebHook() : void
    {
        $body = [
            'secret' => SECRET,
            'url' => URLSERVER.'/listener/applications/webhook',
        ];

        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URL.'/v1/applications/webhook', 'GET', null, null, $bearer);
        ajaxSuccess([$response]);
    }

    //Отписка на уведомления о создании и обновлении откликов на вакансии Исключение:
    public function unsubscribeWebHook() : void
    {
        $body = [
            'secret' => SECRET,
            'url' => URLSERVER.'/listener/applications/webhook',
        ];
        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v1/applications/webhook', 'GET', null, null, $bearer);
        ajaxSuccess([$response]);
    }

    public function getByIds(int $id) : array
    {
        $ids = [
            'ids' => [$id]
        ];
        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v1/applications/get_by_ids', 'POST', $ids, null, $bearer);
        return json_decode($response, true);
    }

    public function getVacancyById(int $id) : array
    {
        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v2/vacancies/'.$id, 'GET', null, null, $bearer);
        return json_decode($response, true);
    }

    public function getResumeById(int $id) : array
    {
        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v2/resumes/'.$id, 'GET', null, null, $bearer);
        return json_decode($response, true);
    }

    public function getContactByResumeId(int $id) : array
    {
        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v2/resumes/'.$id.'/contacts/', 'GET', null, null, $bearer);
        return json_decode($response, true);
    }

    //Возвращает постраничный список откликов с фильтрацией по вакансии
    public function listResponse() : void
    {
        $args = [
            'per_page' => 20,
            'page' => 1,
            // 'cursor' => ,
        ];

        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v1/negotiations', 'GET', null, $args, $bearer);
        ajaxSuccess([$response]);
    }

    //Получение отклика по его идентификатору
    public function getNegotiationById(int $id) : array
    {
        [$bearer, $QueryRequest] = $this->checkBearer();
        $response = $QueryRequest->queryAssembly(URLAVITO.'/v1/negotiations/'.$id, 'GET', null, null, $bearer);
        return json_decode($response, true);
    }

    function checkBearer () : array
    {
        $QueryRequest = new QueryRequest;
        $Token = new Token;
        $bearer = $Token->checkAccessToken();
        return [$bearer, $QueryRequest];
    }
}