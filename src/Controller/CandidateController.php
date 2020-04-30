<?php
namespace Altech\Controller;

use Altech\Model\Entity\Candidate;
use Altech\Model\Repository\CandidateRepository;
use App\Component\Controller;
use App\KernelFoundation\Request;
use Exception;
use PDO;

class CandidateController extends Controller
{

    
    public function index(){
        $repo = $this->getRepository(CandidateRepository::class);
        $page = $this->getRequest()->get->get("page") ?? 0;

        return $this->render('/candidate/index.php', [
            'candidates' => $repo->findAllOfUser($page),
            'page' => $page,
            'totalPage' => $repo->findPageCount()
        ]);
    }

    public function findByName(string $name){
        // TODO :implement findByEmail function
    }

    

    public function create(){
        $req = $this->getRequest();
        $file = $req->files->get("cgu_approvement");
        $form = $req->form;

        if($req->is(Request::METHOD_POST)){

            if(!empty($form->get("firstname")) && !empty($form->get("lastname")) && !empty($form->get("email"))){
                $repo = $this->getRepository(CandidateRepository::class);

                $candidate = (new Candidate())
                    ->setClient($this->getUser())
                    ->setEmail($form->get("email"))
                    ->setPhone($form->get("phone"))
                    ->setFirstname($form->get("firstname"))
                    ->setLastname($form->get("lastname"))
                    ->setSex($form->get("sex"))
                    ->setHeight($form->get("height"))
                    ->setWeight($form->get("weight"));

                $candidate->setCguApprovement(ClientController::checkAndUploadFile($file));

                $repo->insert($candidate);
                $this->redirect("/client/candidate/".$candidate->getId());
            }
        }

        return $this->render('/candidate/form.php', [
            "title" => "Création d'un nouveau candidat",
            "candidate" => new Candidate()
        ]);
    }


    public function edit($id)
    {
        $repo = $this->getRepository(CandidateRepository::class);
        $user = $this->getUser();
        /** @var Candidate $candidate */
        $candidate = $repo->findById($id);

        if($candidate && $user->getId() == $candidate->getClient()->getId()){

            return $this->render('/candidate/form.php', [
                "title" => "Création d'un nouveau candidat",
                "candidate" => $candidate
            ]);
        }
        throw new Exception("invalid candidate id: $id");
    }
}