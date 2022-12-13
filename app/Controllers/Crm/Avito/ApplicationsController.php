<?php

namespace App\Controllers\Crm\Avito;

use App\Services\Avito\ApiAvito;
use Classes\AmoCrm\Contact;
use Classes\AmoCrm\Lead;
use Classes\AmoCrm\Note;
use \stdClass;

class ApplicationsController extends \Core\Controllers\Controller 
{
    public $phonecf = 137915;
    public $emailcf = 137917;

    public function __construct()
    {
        $this->logger = logger('crm/App-Controllers-Crm-AvitoApplicationsController.log');
    }

    /**
     * 
     * @return bool
    */
   
    public function listenerWebHookFromAvito() : bool
    {
        $note = $name = $leadCreated = $leadFind = $vacancy = null;

        $applyId = json_decode(file_get_contents('php://input'), true);
        $avitoApi = new ApiAvito;
        $applicationsById = $avitoApi->getByIds($applyId['applyId']);

        if (!empty($applicationsById['applies'][0]['applicant']['resume_id'])) {
            $resume = $avitoApi->getResumeById($applicationsById['applies'][0]['applicant']['resume_id']);
        }

        if (!empty($applicationsById['applies'][0]['applicant']['data'])) {
            $note = $applicationsById['applies'][0]['applicant']['data'];
        }

        if (empty($applicationsById['applies'][0]['contacts']['phones'])) {
            $this->logger->log('нет номера телефона');
            return true;
        }
        
        $phone = !empty($applicationsById['applies'][0]['contacts']['phones'][0]['value']) 
                    ? $applicationsById['applies'][0]['contacts']['phones'][0]['value']
                    : null;

        $note['chat_url'] = !empty($applicationsById['applies'][0]['contacts']['chat']['value']) 
                            ? $applicationsById['applies'][0]['contacts']['chat']['value']
                            : null;

        $vacancy = !empty($applicationsById['applies'][0]['vacancy_id']) 
                    ? $avitoApi->getVacancyById($applicationsById['applies'][0]['vacancy_id']) 
                    : null;

        if (!empty($phone)) {
            $name = $phone;
        } else if (!empty($note['name'])) {
            $name = $note['name'];
        }
  
        $email = null;
        $contactFind = $contactCreated = null;
        $contactObj = new Contact($phone, $this->phonecf, $email, $this->emailcf);
        $leadObj = new Lead;
        $noteObj = new Note;
        $contactCreated = $contactObj->search();
        $data = [
            'phone' => $phone,
            'name' => $name,
            'vacancy' => $vacancy
        ];

        if (empty($contactCreated)) {
            $client = new stdClass;
            $client->name = !empty($name) ? $name : 'Соискатель';
            $client->responsible_user_id = 7350574;
            $contactCreated = $contactObj->create($client);
            $leadCreated = $leadObj->create($contactCreated, $data);
            $noteObj->create($leadCreated, $note);
            return true;
        }

        $leadFind = $leadObj->getActiveLeadInPipeline($contactCreated, 3549604);
        if (!empty($leadFind)) {
            $leadFind->status_id = 46399996;
            $leadFind->save();
            $noteObj->create($leadFind, $note, 'Повторный отклик');
            return true;
        }else{
            $leadCreated = $leadObj->create($contactCreated, $data);
            $noteObj->create($leadCreated, $note);
        }
        return true;
    }


}