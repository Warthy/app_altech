<?php

namespace Altech\Controller;

use Altech\Model\Entity\User;
use Altech\Model\Repository\UserRepository;
use App\Component\Controller;
use App\KernelFoundation\Request;
use App\KernelFoundation\Security;
use Exception;


class ClientController extends Controller
{
    public function index()
    {
        $filter = $this->getRequest()->get->get("search") ?? "";
        $page = $this->getRequest()->get->get("page") ?? 0;

        /** @var UserRepository $repo */
        $repo = $this->getRepository(UserRepository::class);
        return $this->render('/client/index.php', [
            'clients' => $repo->findClientsWithFilter($filter, $page),
            'page' => $page + 1,
            'count' => $repo->findPageCountWithFilter($filter) + 1,
            'filter' => $filter
        ]);
    }

    public function create()
    {
        $error = "";
        $req = $this->getRequest();

        if ($req->is(Request::METHOD_POST)) {
            $form = $req->form;
            $file = $req->files->get("cgu_approvement");

            if (!empty($form->get("name")) && !empty($form->get("email")) && !empty($file)) {
                $repo = $this->getRepository(UserRepository::class);
                $client = (new User())
                    ->setRole(Security::ROLE_CLIENT)
                    ->setName($form->get("name"))
                    ->setPhone($form->get("phone"))
                    ->setAddress($form->get("address"))
                    ->setCity($form->get("city"))
                    ->setZipCode($form->get("zipcode"))
                    ->setEmail($form->get("email"))
                    ->setRepresentativeLastName($form->get("r_lastname"))
                    ->setRepresentativeFirstName($form->get("r_firstname"))
                    ->setRepresentativeEmail($form->get("r_email"))
                    ->setRepresentativePhone($form->get("r_phone"))
                    ->setRecoverToken(sha1(mt_rand(1, 90000) . Security::SECRET_SALT))
                    ->setPassword(bin2hex(random_bytes(10)));
                //We set a random password to avoid connection while new user hasn't define his password himself

                $client->setCguApprovement(self::checkAndUploadFile($file));
                $repo->insert($client);


                $mailer = $this->getMailer();
                try {
                    $mailer
                        ->to($client->getEmail())
                        ->subject("Création de votre compte client")
                        ->setBody('client-created.php', [
                            'name' => $client->getName(),
                            'token' => $client->getRecoverToken()
                        ]);

                    $mailer->send();
                } catch (Exception $e) {
                    die(var_dump($e));
                    //TODO: handle Exception
                }

                $this->redirect("/client/" . $client->getId());
            }
        }
        return $this->render('/client/form.php', [
            'title' => "Création d'un nouveau client :",
            'client' => new User(),
            'error' => $error
        ]);
    }


    public function edit($id)
    {
        $error = "";
        $req = $this->getRequest();
        $repo = $this->getRepository(UserRepository::class);
        /** @var User $client */
        $client = $repo->findById($id);

        if ($client) {
            if ($req->is(Request::METHOD_POST)) {
                $form = $req->form;
                $file = $req->files->get("cgu_approvement");

                if (!empty($form->get("name")) && !empty($form->get("email"))) {
                    $client = $client
                        ->setName($form->get("name"))
                        ->setPhone($form->get("phone"))
                        ->setAddress($form->get("address"))
                        ->setCity($form->get("city"))
                        ->setZipCode($form->get("zipcode"))
                        ->setEmail($form->get("email"));


                    if ($file["size"] > 0) {
                        //If there was a previous file we delete it
                        if ($client->getCguApprovement()) {
                            unlink(User::UPLOAD_DIR . $client->getCguApprovement());
                        }
                        $client->setCguApprovement(self::checkAndUploadFile($file));
                    }

                    $repo->update($client);
                }
            }
            return $this->render('/client/form.php', [
                'title' => "Modification du client $id:",
                "client" => $client,
                'error' => $error
            ]);
        }
        throw new Exception("invalid client id: $id");
    }

    public function delete($id)
    {
        $repo = $this->getRepository(UserRepository::class);
        $client = $repo->findById($id);

        // We check that the user exists and that he isn't trying to delete his account by himself
        if ($client) {
            if ($client->getPicture())
                unlink(User::PICTURE_DIR . $client->getPicture());
            $repo->remove($client);
            $this->redirect("/client");
        }
        throw new Exception("invalid user id: $id");
    }

    public static function checkAndUploadFile($file): string
    {
        $metadata = pathinfo($file['name']);
        if (in_array(strtolower($metadata['extension']), ['pdf', 'doc', 'docx', 'jpeg', 'png', 'jpg'])) {
            $random = bin2hex(random_bytes(20)) . '.' . $metadata['extension'];
            if (move_uploaded_file($file['tmp_name'], User::UPLOAD_DIR . $random)) {
                return $random;
            }
            throw new Exception("Erreur d'upload du fichier");
        }
        throw new Exception("Fichier invalide");
    }
}